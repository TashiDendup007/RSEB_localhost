<?php  
// ═══════════════════════════════════════════════════════════════════════════════
//  MATCHING ENGINE
//  Must be called inside an active transaction.
//  Returns ['traded' => bool, 'trade_vol' => int, 'trade_price' => float]
// ═══════════════════════════════════════════════════════════════════════════════
function try_match_bond_trade(PDO $dbh, array $o): array
{
    $no_trade = ['traded' => false, 'trade_vol' => 0, 'trade_price' => 0.0];
    $opposite = ($o['order_side'] === 'B') ? 'S' : 'B';
    $remaining = (int) $o['vol'];

    // ── Find the best resting order on the opposite side ─────────────────────
    // Buy  aggressor → match cheapest sell  where sell_price <= buy_price
    // Sell aggressor → match dearest  buy   where buy_price  >= sell_price
    // Tie-break: oldest order first (lowest flag_id = time priority)
    if ($o['order_side'] === 'B') {
        $price_clause = 'price <= :price';
        $price_sort   = 'ASC';
        $vol_col      = 'sell_vol';
    } else {
        $price_clause = 'price >= :price';
        $price_sort   = 'DESC';
        $vol_col      = 'buy_vol';
    }

    $stmt = $dbh->prepare("
        SELECT cd_code, participant_code, price, {$vol_col} AS remaining_vol, order_date, flag_id, member_broker, order_entry, acc_intrt, dirty_price, ytm, order_type 
        FROM bond_orders
        WHERE symbol_id         = :symbol_id
           AND side             = :opposite
           AND {$price_clause}
           AND {$vol_col}       > 0
           AND cd_code         != :cd_code
		ORDER BY price {$price_sort}, order_date ASC 
		LIMIT 1
		FOR UPDATE
    ");
    $stmt->execute([
        ':symbol_id' => $o['symbol_id'],
        ':opposite'  => $opposite,
        ':price'     => $o['price'],
        ':cd_code'   => $o['cd_code'],
    ]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$match) {
        return $no_trade;          // order rests in the book — no immediate match
    }

    // ── Calculate fill volume ─────────────────────────────────────────────────
    $trade_vol   = min((int) $o['vol'], (int) $match['remaining_vol']);
    $trade_price = (float) $match['price'];   // always fill at the resting (maker) price

    $buyer_flag_id  = ($o['order_side'] === 'B') ? $o['flag_id']    : $match['flag_id'];
    $seller_flag_id = ($o['order_side'] === 'S') ? $o['flag_id']    : $match['flag_id'];
    $buyer_cd       = ($o['order_side'] === 'B') ? $o['cd_code']    : $match['cd_code'];
    $seller_cd      = ($o['order_side'] === 'S') ? $o['cd_code']    : $match['cd_code'];
    $buyer_member   = ($o['order_side'] === 'B') ? $o['participant_code'] : $match['participant_code'];
    $seller_member  = ($o['order_side'] === 'S') ? $o['participant_code'] : $match['participant_code'];

    $trade_amount     = $trade_price * $trade_vol;
    $buyer_commission = $trade_amount * BROKER_COMMIS_PCT / 100;
    $buyer_gst        = $buyer_commission * GST_RATE;
    $buyer_total      = $trade_amount + $buyer_commission + $buyer_gst;

    $seller_commission = $trade_amount * BROKER_COMMIS_PCT / 100;
    $seller_gst        = $seller_commission * GST_RATE;
    $seller_net        = $trade_amount - $seller_commission - $seller_gst;

    $cd_code 	=  $match['cd_code'];
    $participant_code 	=  $match['participant_code'];
    $member_broker 		=  $match['member_broker'];
    $order_entry 	    =  $match['order_entry'];
    $flag_id 	        =  $match['flag_id'];
    $acc_intrt 	        =  $match['acc_intrt'];
    $dirty_price 	    =  $match['dirty_price'];
    $ytm 	            =  $match['ytm'];
    $order_type 	    =  $match['order_type'];

    // ── 1. Record the trade ───────────────────────────────────────────────────
    // Assumes a bond_trades table:
    // (flag_id, symbol_id, buy_order_flag_id, sell_order_flag_id, buyer_cd_code, seller_cd_code, trade_vol, trade_price, trade_date)
    $stmt = $dbh->prepare("
        INSERT INTO bond_executed_orders(
        	cd_code, participant_code, sub_user, member_broker, order_date, symbol_id, order_exe_price, lot_size_execute, status, side, lot_check, flag_id, dirty_price, accur_rate, ytm)
        	-- flag_id, symbol_id, buy_order_flag_id, sell_order_flag_id, buyer_cd_code, seller_cd_code, buyer_member, seller_member, trade_vol, trade_price, trade_date
        VALUES(:flag_id, :symbol_id, :buy_flag, :sell_flag, :buyer_cd, :seller_cd, :buyer_member, :seller_member, :trade_vol, :trade_price, NOW())
    ");
    $stmt->execute([
        ':flag_id'       => $trade_flag_id,
        ':symbol_id'     => $o['symbol_id'],
        ':buy_flag'      => $buyer_flag_id,
        ':sell_flag'     => $seller_flag_id,
        ':buyer_cd'      => $buyer_cd,
        ':seller_cd'     => $seller_cd,
        ':buyer_member'  => $buyer_member,
        ':seller_member' => $seller_member,
        ':trade_vol'     => $trade_vol,
        ':trade_price'   => $trade_price,
    ]);

    // ── 2. Reduce remaining volume on both orders ─────────────────────────────
    // If fully filled, remaining vol hits 0 (order naturally disappears from book)
    $stmt = $dbh->prepare("
        UPDATE bond_orders
           	SET buy_vol  = GREATEST(buy_vol  - :vol, 0),
               sell_vol = GREATEST(sell_vol - :vol, 0)
     	WHERE flag_id = :flag_id
    ");
    foreach ([$buyer_flag_id, $seller_flag_id] as $fid) {
        $stmt->execute([':vol' => $trade_vol, ':flag_id' => $fid]);
    }

    // ── 3. Deliver shares to buyer ────────────────────────────────────────────
    // INSERT … ON DUPLICATE KEY handles first-ever purchase of this symbol
    $stmt = $dbh->prepare("
        INSERT INTO cds_holding (cd_code, symbol_id, volume, pending_out_vol) VALUES (:cd_code, :symbol_id, :vol, 0)
        ON DUPLICATE KEY UPDATE volume = volume + :vol
    ");
    $stmt->execute([
        ':cd_code'   => $buyer_cd,
        ':symbol_id' => $o['symbol_id'],
        ':vol'       => $trade_vol,
    ]);

    // ── 4. Release seller's pending volume ────────────────────────────────────
    $stmt = $dbh->prepare("
        UPDATE cds_holding
           SET pending_out_vol = GREATEST(pending_out_vol - :vol, 0)
         WHERE cd_code = :cd_code AND symbol_id = :symbol_id
    ");
    $stmt->execute([
        ':vol'       => $trade_vol,
        ':cd_code'   => $seller_cd,
        ':symbol_id' => $o['symbol_id'],
    ]);

    // ── 5. Finance: debit buyer (net cost) ───────────────────────────────────
    $buy_remarks  = "Trade executed — bought {$trade_vol} {$o['symbol_id']} @ Nu. {$trade_price}";
    $sell_remarks = "Trade executed — sold  {$trade_vol} {$o['symbol_id']} @ Nu. {$trade_price}";

    $stmt = $dbh->prepare("
        INSERT INTO bbo_finance
            (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount)
        VALUES (:cd_code, :user_name, :remarks, 1, :institution_id, :flag_id, 1, :amount)
    ");

    $stmt->execute([                           // buyer debit
        ':cd_code'        => $buyer_cd,
        ':user_name'      => $buyer_member,
        ':remarks'        => $buy_remarks,
        ':institution_id' => $o['institution_id'],
        ':flag_id'        => $trade_flag_id,
        ':amount'         => -$buyer_total,    // negative = cash out
    ]);

    $stmt->execute([                           // seller credit
        ':cd_code'        => $seller_cd,
        ':user_name'      => $seller_member,
        ':remarks'        => $sell_remarks,
        ':institution_id' => $o['institution_id'],
        ':flag_id'        => $trade_flag_id,
        ':amount'         => $seller_net,      // positive = cash in
    ]);

    return [
        'traded'      => true,
        'trade_vol'   => $trade_vol,
        'trade_price' => $trade_price,
    ];
}
?>