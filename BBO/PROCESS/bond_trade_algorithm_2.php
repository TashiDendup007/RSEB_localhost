<?php
function try_match_bond_trade(PDO $dbh, array $o): array
{
    $opposite  = ($o['order_side'] === 'B') ? 'S' : 'B';
    $fills     = [];        // every partial fill recorded here
    $remaining = (int) $o['vol'];
    $symbol_id = (int) $o['symbol_id'];

    if ($o['order_side'] === 'B') {
        $price_clause = 'price <= :price';
        $price_sort   = 'ASC';     // cheapest sell first
        $vol_col      = 'sell_vol';
    } else {
        $price_clause = 'price >= :price';
        $price_sort   = 'DESC';    // dearest buy first
        $vol_col      = 'buy_vol';
    }

    // ── Walk the book ─────────────────────────────────────────────────────────
    // Each iteration locks and consumes the single best resting order.
    // Loop exits when: (a) new order fully filled, or (b) no more matches exist.
    while ($remaining > 0) {

        // Lock the next best resting order for this iteration only
        $stmt = $dbh->prepare("
               SELECT o.cd_code, o.participant_code, o.price, o.{$vol_col} AS remaining_vol, o.order_date, o.flag_id, o.member_broker, o.order_entry, o.acc_intrt, o.dirty_price, o.ytm, o.order_type, o.side, a.institution_id, b.gst_register
               FROM bond_orders o
               LEFT JOIN adm_participants a ON o.participant_code = a.participant_code
               LEFT JOIN adm_institution b ON a.institution_id = b.institution_id 
               FROM bond_orders
               WHERE symbol_id  = :symbol_id
               AND side         = :opposite
               AND {$price_clause}
               AND {$vol_col}   > 0
               AND cd_code      != :cd_code
               ORDER BY price {$price_sort}, order_date ASC -- flag_id
               LIMIT 1
               FOR UPDATE
        ");
        $stmt->execute([
            ':symbol_id' => $symbol_id,
            ':opposite'  => $opposite,
            ':price'     => $o['price'],
            ':cd_code'   => $o['cd_code'],
        ]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$match) {
            break;      // book exhausted — remaining volume rests as a new order
        }

        // ── Fill volume for this match ────────────────────────────────────────
        $fill_vol     = min($remaining, (int) $match['remaining_vol']);
        $trade_price  = (float) $match['price'];   // maker's price
        $gst_register = $match['gst_register'] ?? 'N';
        $trade_amount = round($trade_price * $fill_vol, 2);
        $commission   = round($trade_amount * BROKER_COMMIS_PCT / 100, 2);
        $gst          = ($gst_register === 'Y') ? round($commission * GST_RATE, 2) : 0;

        // $buyer_total = $trade_amount + $commission + $gst;
        // $seller_net  = $trade_amount - $commission - $gst;
        // $buyer_cd    = ($o['order_side'] === 'B') ? $o['cd_code']          : $match['cd_code'];
        // $seller_cd   = ($o['order_side'] === 'S') ? $o['cd_code']          : $match['cd_code'];
        // $buyer_mbr   = ($o['order_side'] === 'B') ? $o['participant_code'] : $match['participant_code'];
        // $seller_mbr  = ($o['order_side'] === 'S') ? $o['participant_code'] : $match['participant_code'];
        // $buyer_flag  = ($o['order_side'] === 'B') ? $o['flag_id']          : $match['flag_id'];
        // $seller_flag = ($o['order_side'] === 'S') ? $o['flag_id']          : $match['flag_id'];

        // check balance
        $buy_balance = ($o['order_side'] === 'B') ? ($o['vol'] - $fill_vol) : ($match['order_size'] - $fill_vol);
        $sell_balance = ($o['order_side'] === 'S') ? ($o['vol'] - $fill_vol) : ($match['order_size'] - $fill_vol);

        $buy_status  = ($buy_balance == 0) ? 'EXECUTED' : 'PENDING';
        $sell_status = ($sell_balance == 0) ? 'EXECUTED' : 'PENDING';

        // Unique flag per fill — offset by fill count to avoid collisions
        // $trade_flag_id = (int)(microtime(true) * 1000) + count($fills) + 1;

        // 1. Record this fill
        // (flag_id, symbol_id, buy_order_flag_id, sell_order_flag_id, buyer_cd_code, seller_cd_code, buyer_member, seller_member, trade_vol, trade_price, trade_date)
        $stmt = $dbh->prepare("
            INSERT INTO bond_executed_orders(cd_code, participant_code, sub_user, member_broker, order_date, symbol_id, order_exe_price, lot_size_execute, status, side, lot_check, flag_id, dirty_price, accur_rate, ytm, order_type)
            VALUES(:cdcode, :part_code, :subuser, :member, :orderdate, :symbid, :exec_price, :exec_vol, 0, :side, :lot, :flagid, :dirt_prc, :acc_rate, :ytm, :ord_type)
        ");
        // insert o variable details (dirtyprice, accured interest, ytm from order table since execute at same price)
        $stmt->execute([
            ':cdcode'    => $o['cd_code'],         ':part_code' => $o['participant_code'], ':subuser'    => $o['order_entry'], ':member'  => $o['order_entry'],
            ':orderdate' => $o['order_date'],      ':symbid'    => $symbol_id,             ':exec_price' => $trade_price,      ':exec_vol'    => $fill_vol,
            ':side'      => $o['order_side'],      ':lot'       => $fill_vol,              ':flagid'     => $o['flag_id'],
            ':dirt_prc'  => $match['dirty_price'], ':acc_rate'  => $match['acc_intrt'],     ':ytm'       => $match['ytm'],     ':ord_type'    => $match['order_type'],
        ]);
        // insert row of opposite side
        $stmt->execute([
            ':cdcode'    => $match['cd_code'],    ':part_code' => $match['participant_code'], ':subuser' => $match['order_entry'], ':member' => $match['order_entry'],
            ':orderdate' => $match['order_date'], ':symbid'    => $symbol_id,              ':exec_price' => $trade_price,     ':exec_vol'    => $fill_vol,
            ':side'      => $match['side'],       ':lot'       => $fill_vol,                ':flagid'    => $match['flag_id'],
            ':dirt_prc'  => $match['dirty_price'], ':acc_rate' => $match['acc_intrt'],       ':ytm'      => $match['ytm'],    ':ord_type'    => $match['order_type'],
        ]);

        // 2. Reduce resting order's remaining volume
        $stmt = $dbh->prepare("
            UPDATE bond_orders
               SET order_size = order_size - :vol, {$vol_col} = GREATEST({$vol_col} - :vol, 0), status = :status
             WHERE flag_id = :flag_id AND symbol_id = :sym_id
        ");
        $stmt->execute([':vol' => $fill_vol, ':status' => ($match['orde_side'] === 'B') ? $buy_status : $sell_status, ':flag_id' => $match['flag_id'], ':sym_id' => $symbol_id]);

        // 3. Reduce incoming order's remaining volume
        $new_vol_col = ($o['order_side'] === 'B') ? 'buy_vol' : 'sell_vol';
        $stmt = $dbh->prepare("
            UPDATE bond_orders
               SET order_size = order_size - :vol, {$new_vol_col} = GREATEST({$new_vol_col} - :vol, 0), status = :status
             WHERE flag_id = :flag_id AND symbol_id = :sym_id
        ");
        $stmt->execute([':vol' => $fill_vol, ':status' => ($o['orde_side'] === 'B') ? $buy_status : $sell_status, ':flag_id' => $o['flag_id'], ':sym_id' => $symbol_id]);

        // 4. Right after updating bond orders table, insert into bond orders audit for audit trail
        $auditSql = "INSERT INTO bond_order_audits(bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status) SELECT id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status FROM bond_orders WHERE id = ?";
        $auditStmt = $dbh->prepare($auditSql);
        $auditStmt->execute([$o['flag_id']]);
        $auditStmt->execute([$match['flag_id']]);

        // 5. Deliver shares to buyer, keep in pending in vol, only after settlement vol will be updated to volume column from PIV
        // if o['order_side'] == 'B'
        if ($o['order_side'] == 'B') {
            $stmt = $dbh->prepare("INSERT INTO cds_holding (cd_code, symbol_id, pending_in_vol)  VALUES (:cd_code, :symbol_id, :vol)");
            $stmt->execute([':cd_code' => $o['cd_code'], ':symbol_id' => $symbol_id, ':vol' => $fill_vol]);
        }
        // if match['order_side'] == 'B'
        if ($match['side'] == 'B') {
            $stmt = $dbh->prepare("INSERT INTO cds_holding (cd_code, symbol_id, pending_in_vol)  VALUES (:cd_code, :symbol_id, :vol)");
            $stmt->execute([':cd_code' => $match['cd_code'], ':symbol_id' => $symbol_id, ':vol' => $fill_vol]);
        }

        // 6. Release seller's pending volume. Cannot release during trading, as it will be settled during settlement.
        // $stmt = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = GREATEST(pending_out_vol - :vol, 0) WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
        // $stmt->execute([':vol' => $fill_vol, ':cd_code' => $seller_cd, ':symbol_id' => $o['symbol_id']]);

        // 7. Finance entries for this fill. keep seperate row record for amount, commission, gst of buyer and seller
         $bbo_stmt = $dbh->prepare("
            INSERT INTO bbo_finance(cd_code, user_name, remarks, flag, institution_id, flag_id, amount) 
            VALUES (:cd_code, :user_name, :remarks, :flag, :institution_id, :flag_id, :amount)
         ");
         // buyer side
         if ($o['order_size'] === 'B' || $match['side'] === 'B') {
            $buyer_cdcode = ($o['order_size'] === 'B') ? $o['cd_code'] : $match['cd_code'];
            $fin_inst_id = ($o['order_size'] === 'B') ? $o['institution_id'] : $match['institution_id'];
            $member_broker = ($o['order_size'] === 'B') ? $o['usr_name'] : $match['member_broker'];
            $trade_flag_id = ($o['order_size'] === 'B') ? $o['flag_id'] : $match['flag_id'];

            $bbo_stmt->execute([
               ':cd_code'        => $buyer_cdcode,
               ':user_name'      => $member_broker,
               ':remarks'        => "Bond purchase for {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
               ':institution_id' => $fin_inst_id,
               ':flag_id'        => $trade_flag_id,
               ':flag'           => 3,
               ':amount'         => -$trade_amount,
            ]);
            $bbo_stmt->execute([
               ':cd_code'        => $buyer_cdcode,
               ':user_name'      => $member_broker,
               ':remarks'        => "Commission for {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
               ':institution_id' => $fin_inst_id,
               ':flag_id'        => 4,
               ':amount'         => -$commission,
            ]);
            if ($gst_register === 'Y') {
               $bbo_stmt->execute([
                  ':cd_code'        => $buyer_cdcode,
                  ':user_name'      => $member_broker,
                  ':remarks'        => "GST for {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
                  ':institution_id' => $fin_inst_id,
                  ':flag_id'        => 5,
                  ':amount'         => -$gst,
               ]);
            }
         }
         // seller side
         if ($o['order_size'] === 'S' || $match['side'] === 'S') {
            $seller_cdcode = ($o['order_size'] === 'S') ? $o['cd_code'] : $match['cd_code'];
            $fin_inst_id = ($o['order_size'] === 'S') ? $o['institution_id'] : $match['institution_id'];
            $member_broker = ($o['order_size'] === 'S') ? $o['usr_name'] : $match['member_broker'];
            $trade_flag_id = ($o['order_size'] === 'S') ? $o['flag_id'] : $match['flag_id'];

            $stmt->execute([
               ':cd_code'        => $seller_cdcode,
               ':user_name'      => $member_broker,
               ':remarks'        => "Bond sell for {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
               ':institution_id' => $fin_inst_id,
               ':flag_id'        => $trade_flag_id,
               ':flag'           => 2,
               ':amount'         => $trade_amount,
            ]);
            $bbo_stmt->execute([
               ':cd_code'        => $seller_cdcode,
               ':user_name'      => $member_broker,
               ':remarks'        => "Commission for {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
               ':institution_id' => $fin_inst_id,
               ':flag_id'        => 4,
               ':amount'         => -$commission,
            ]);
            if ($gst_register === 'Y') {
               $bbo_stmt->execute([
                  ':cd_code'        => $seller_cdcode,
                  ':user_name'      => $member_broker,
                  ':remarks'        => "GST for {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
                  ':institution_id' => $fin_inst_id,
                  ':flag_id'        => 5,
                  ':amount'         => -$gst,
               ]);
            }
         }
        
        // ── Advance the loop ──────────────────────────────────────────────────
        $fills[]   = ['vol' => $fill_vol, 'price' => $trade_price];
        $remaining -= $fill_vol;

      } // end while

      if (empty($fills)) {
        return ['traded' => false, 'fills' => [], 'total_traded' => 0, 'remaining' => $remaining];
      }

      return [
         'traded'        => true,
         'fills'         => $fills,          // each individual match
         'total_traded'  => array_sum(array_column($fills, 'vol')),
         'remaining'     => $remaining,      // 0 = fully filled, >0 = partial
      ];
}

?>