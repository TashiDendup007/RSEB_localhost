<?php
// include('../../Functions/bond_com_function.php'); 

function try_match_bond_trade(PDO $dbh, array $o): array
{
    $opposite  = ($o['order_side'] === 'B') ? 'S' : 'B';
    $fills     = [];
    $remaining = (int) $o['vol'];
    $symbol_id = (int) $o['symbol_id'];

    error_log("o array_____________");
    error_log(print_r($o, true));

    if ($o['order_side'] === 'B') {
        $price_clause = 'price <= :price';
        $price_sort   = 'ASC';
        $vol_col      = 'sell_vol';
    } else {
        $price_clause = 'price >= :price';
        $price_sort   = 'DESC';
        $vol_col      = 'buy_vol';
    }

    while ($remaining > 0) {
        // ── Find + lock next best resting order ───────────────────────────────
        // FIX 1: removed duplicate FROM clause
        // FIX 2: subquery isolates FOR UPDATE to bond_orders only; joins happen after
        $stmt = $dbh->prepare("
            SELECT o.cd_code, o.participant_code, o.price, o.{$vol_col} AS remaining_vol, o.order_date, o.flag_id, o.member_broker, o.order_entry, o.acc_intrt, o.dirty_price, o.ytm, o.order_type, o.side,
                   a.institution_id, b.gst_register
              FROM (
                    SELECT * FROM bond_orders
                     WHERE symbol_id  = :symbol_id
                       AND side       = :opposite
                       AND {$price_clause}
                       AND {$vol_col} > 0
                       AND cd_code   != :cd_code 
                       AND order_type = 'OTC' 
                     ORDER BY price {$price_sort}, order_date ASC
                     LIMIT 1
                     FOR UPDATE
              ) o
              LEFT JOIN adm_participants a ON o.participant_code = a.participant_code
              LEFT JOIN adm_institution  b ON a.institution_id  = b.institution_id
        ");
        $stmt->execute([
            ':symbol_id' => $symbol_id,
            ':opposite'  => $opposite,
            ':price'     => $o['price'],
            ':cd_code'   => $o['cd_code'],
        ]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$match) {
            error_log("______No Match Order__________");
            break;
        }
        
        error_log("match array_____________");
        error_log(print_r($match, true));

        // ── Fill amounts ──────────────────────────────────────────────────────
        $fill_vol     = min($remaining, (int) $match['remaining_vol']);
        $trade_price  = (float) $match['price'];
        
        $match_gst_register = $match['gst_register'] ?? '';
        $o_gst_register     = $o['gst_register']     ?? '';

        $trade_amount = round($match['dirty_price'] * $fill_vol, 2); // trade amount should be calculate base on dirty price
        $commission   = calculateCommission($trade_price * $fill_vol); // commission should be calculate base on clean price
        
        error_log("fill_vol => {$fill_vol} , trade_price => {$trade_price}");

        // FIX 3 & 5: use $remaining for new-order balance; use $match['remaining_vol'] for resting
        $new_order_after   = $remaining - $fill_vol;
        $match_order_after = (int) $match['remaining_vol'] - $fill_vol;
        
        error_log("new_order_after => {$new_order_after}, match_order_after => {$match_order_after}");

        if ($o['order_side'] === 'B') {
            $buy_status  = ($new_order_after   === 0) ? 'EXECUTED' : 'PENDING';
            $sell_status = ($match_order_after === 0) ? 'EXECUTED' : 'PENDING';

            $buyer_gst_register  = $o_gst_register;
            $seller_gst_register = $match_gst_register;
        } else {
            $sell_status = ($new_order_after   === 0) ? 'EXECUTED' : 'PENDING';
            $buy_status  = ($match_order_after === 0) ? 'EXECUTED' : 'PENDING';

            $buyer_gst_register  = $match_gst_register;
            $seller_gst_register = $o_gst_register;
        }

        // gst calculation
        $buyer_gst  = ($buyer_gst_register  === 'Y') ? round($commission * GST_RATE, 2) : 0.0;
        $seller_gst = ($seller_gst_register === 'Y') ? round($commission * GST_RATE, 2) : 0.0;

        error_log("sell_status => {$sell_status}, buy_status => {$buy_status}");

        // ── Step 1: Record executed orders (both sides) ───────────────────────
        // FIX 11: added exe_date column to match the extra NOW() placeholder
        $exec_stmt = $dbh->prepare("
            INSERT INTO bond_executed_orders(cd_code, participant_code, sub_user, member_broker, order_date, symbol_id, order_exe_price, lot_size_execute, status, side, lot_check, flag_id, dirty_price, accur_rate, ytm, order_type)
            VALUES(:cdcode, :part_code, :subuser, :member, :orderdate, :symbid, :exec_price, :exec_vol, 0, :side, :lot, :flagid, :dirt_prc, :acc_rate, :ytm, :ord_type)
        ");

        // Incoming order row
        $exec_stmt->execute([
            ':cdcode'    => $o['cd_code'],
            ':part_code' => $o['participant_code'],
            ':subuser'   => $o['order_entry'],
            ':member'    => $o['order_entry'],
            ':orderdate' => date('Y-m-d H:i:s'), // $o['order_date']
            ':symbid'    => $symbol_id,
            ':exec_price'=> $trade_price,
            ':exec_vol'  => $fill_vol,
            ':side'      => $o['order_side'],
            ':lot'       => $fill_vol,
            ':flagid'    => $o['flag_id'],
            ':dirt_prc'  => $match['dirty_price'],
            ':acc_rate'  => $match['acc_intrt'],
            ':ytm'       => $match['ytm'],
            ':ord_type'  => $match['order_type'],
        ]);

        // Resting (matched) order row
        $exec_stmt->execute([
            ':cdcode'    => $match['cd_code'],
            ':part_code' => $match['participant_code'],
            ':subuser'   => $match['order_entry'],
            ':member'    => $match['order_entry'],
            ':orderdate' => date('Y-m-d H:i:s'), // $match['order_date']
            ':symbid'    => $symbol_id,
            ':exec_price'=> $trade_price,
            ':exec_vol'  => $fill_vol,
            ':side'      => $match['side'],
            ':lot'       => $fill_vol,
            ':flagid'    => $match['flag_id'],
            ':dirt_prc'  => $match['dirty_price'],
            ':acc_rate'  => $match['acc_intrt'],
            ':ytm'       => $match['ytm'],
            ':ord_type'  => $match['order_type'],
        ]);

        // ── Step 2: Reduce resting order volume ───────────────────────────────
        // FIX 3: was $match['orde_side'] (typo)
        $stmt = $dbh->prepare("
            UPDATE bond_orders
                SET order_size = order_size - :ord_size, {$vol_col} = GREATEST({$vol_col} - :vol, 0), exe_vol = exe_vol + :exec_vol, exe_price = :exec_price, status = :status
            WHERE flag_id = :flag_id AND symbol_id = :sym_id
        ");
        $stmt->execute([
            ':ord_size'    => $fill_vol,
            ':vol'    => $fill_vol,
            ':exec_vol'=> $fill_vol,
            ':exec_price'=> $trade_price,
            ':status' => ($match['side'] === 'B') ? $buy_status : $sell_status,
            ':flag_id'=> $match['flag_id'],
            ':sym_id' => $symbol_id,
        ]);

        // ── Step 3: Reduce incoming order volume ──────────────────────────────
        // FIX 4: was $o['orde_side'] (typo)
        $new_vol_col = ($o['order_side'] === 'B') ? 'buy_vol' : 'sell_vol';
        $stmt = $dbh->prepare("
            UPDATE bond_orders
               SET order_size = order_size - :ord_size, {$new_vol_col} = GREATEST({$new_vol_col} - :vol, 0), exe_vol = exe_vol + :exec_vol, exe_price = :exec_price, status = :status
             WHERE flag_id = :flag_id AND symbol_id = :sym_id
        ");
        $stmt->execute([
            ':ord_size'    => $fill_vol,
            ':vol'    => $fill_vol,
            ':exec_vol'=> $fill_vol,
            ':exec_price'=> $trade_price,
            ':status' => ($o['order_side'] === 'B') ? $buy_status : $sell_status,
            ':flag_id'=> $o['flag_id'],
            ':sym_id' => $symbol_id,
        ]);

        // ── Step 4: Audit trail for both updated orders ───────────────────────
        // FIX 6: was WHERE id = ? — flag_id is the natural key available in scope
        $audit_stmt = $dbh->prepare("
            INSERT INTO bond_order_audits (bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, side, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status)
            SELECT id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, side, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status
            FROM bond_orders
            WHERE flag_id = ?
        ");
        $audit_stmt->execute([$o['flag_id']]);
        $audit_stmt->execute([$match['flag_id']]);

        // ── Step 5: Buyer — credit pending_in_vol (settled later via PIV) ────
        $check_holding = $dbh->prepare("SELECT cds_holding_id FROM cds_holding WHERE cd_code = ? AND symbol_id = ?");
        $update_holding = $dbh->prepare("UPDATE cds_holding SET pending_in_vol = pending_in_vol + :vol WHERE cd_code = :cd_code AND symbol_id = :symbol_id");
        $insert_holding = $dbh->prepare("INSERT INTO cds_holding (cd_code, symbol_id, pending_in_vol)VALUES (:cd_code, :symbol_id, :vol)");

        // Determine which side is the buyer
        $buyer_cd = ($o['order_side'] === 'B') ? $o['cd_code'] : $match['cd_code'];

        $check_holding->execute([$buyer_cd, $symbol_id]);
        $holding_exists = $check_holding->fetchColumn();

        if ($holding_exists) {
            $update_holding->execute([
                ':vol'       => $fill_vol,
                ':cd_code'   => $buyer_cd,
                ':symbol_id' => $symbol_id,
            ]);
        } else {
            $insert_holding->execute([
                ':cd_code'   => $buyer_cd,
                ':symbol_id' => $symbol_id,
                ':vol'       => $fill_vol,
            ]);
        }

        // ── Step 6: Pending_out_vol released at settlement — intentionally skipped

        // ── Step 7: Finance ledger ────────────────────────────────────────────
        // FIX 7: was $o['order_size'] (wrong key) — corrected to $o['order_side']
        // FIX 8: seller block was calling $stmt->execute() — corrected to $bbo_stmt
        // FIX 9+10: commission/GST rows were missing ':flag' and had ':flag_id' => 4/5 (hardcoded wrong)
        $bbo_stmt = $dbh->prepare("
            INSERT INTO bbo_finance(cd_code, user_name, remarks, flag, institution_id, flag_id, amount, symbol_id)
            VALUES(:cd_code, :user_name, :remarks, :flag, :institution_id, :flag_id, :amount, :symb_id)
        ");
        // Determine buyer/seller identities
        if ($o['order_side'] === 'B') {
            $buyer_cd   = $o['cd_code'];           $seller_cd   = $match['cd_code'];
            $buyer_mbr  = $o['order_entry'];       $seller_mbr  = $match['member_broker'];
            $buyer_inst = $o['institution_id'];    $seller_inst = $match['institution_id'];
            $buyer_fid  = $o['flag_id'];           $seller_fid  = $match['flag_id'];
        } else {
            $buyer_cd   = $match['cd_code'];       $seller_cd   = $o['cd_code'];
            $buyer_mbr  = $match['member_broker']; $seller_mbr  = $o['order_entry'];
            $buyer_inst = $match['institution_id'];$seller_inst = $o['institution_id'];
            $buyer_fid  = $match['flag_id'];       $seller_fid  = $o['flag_id'];
        }

        // Buyer: trade amount (flag 3)
        $bbo_stmt->execute([
            ':cd_code'        => $buyer_cd,
            ':user_name'      => $buyer_mbr,
            ':remarks'        => "Bond purchase — {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
            ':flag'           => 3,
            ':institution_id' => $buyer_inst,
            ':flag_id'        => $buyer_fid,
            ':amount'         => -$trade_amount,
            ':symb_id'         => $symbol_id,
        ]);
        // Buyer: commission (flag 4)
        $bbo_stmt->execute([
            ':cd_code'        => $buyer_cd,
            ':user_name'      => $buyer_mbr,
            ':remarks'        => "Commission — {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
            ':flag'           => 4,
            ':institution_id' => $buyer_inst,
            ':flag_id'        => $buyer_fid,
            ':amount'         => -$commission,
            ':symb_id'         => $symbol_id,
        ]);
        // Buyer: GST (flag 5) — only if GST registered
        if ($buyer_gst_register  === 'Y') {
            $bbo_stmt->execute([
                ':cd_code'        => $buyer_cd,
                ':user_name'      => $buyer_mbr,
                ':remarks'        => "GST — {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
                ':flag'           => 5,
                ':institution_id' => $buyer_inst,
                ':flag_id'        => $buyer_fid,
                ':amount'         => -$buyer_gst,
                ':symb_id'         => $symbol_id,
            ]);
        }

        // Seller: trade amount (flag 2)
        $bbo_stmt->execute([
            ':cd_code'        => $seller_cd,
            ':user_name'      => $seller_mbr,
            ':remarks'        => "Bond sale — {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
            ':flag'           => 2,
            ':institution_id' => $seller_inst,
            ':flag_id'        => $seller_fid,
            ':amount'         => $trade_amount,
            ':symb_id'         => $symbol_id,
        ]);
        // Seller: commission (flag 4)
        $bbo_stmt->execute([
            ':cd_code'        => $seller_cd,
            ':user_name'      => $seller_mbr,
            ':remarks'        => "Commission — {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
            ':flag'           => 4,
            ':institution_id' => $seller_inst,
            ':flag_id'        => $seller_fid,
            ':amount'         => -$commission,
            ':symb_id'         => $symbol_id,
        ]);
        // Seller: GST (flag 5)
        if ($seller_gst_register  === 'Y') {
            $bbo_stmt->execute([
                ':cd_code'        => $seller_cd,
                ':user_name'      => $seller_mbr,
                ':remarks'        => "GST — {$fill_vol} units of {$symbol_id} @ Nu. {$trade_price}",
                ':flag'           => 5,
                ':institution_id' => $seller_inst,
                ':flag_id'        => $seller_fid,
                ':amount'         => -$seller_gst,
                ':symb_id'         => $symbol_id,
            ]);
        }

        // ── Step 8: Remove fully-filled orders from the book ──────────────────
        // Deletes whichever order(s) reached zero remaining volume.
        // Three conditions prevent accidental cross-symbol deletes.
        $del_stmt = $dbh->prepare("DELETE FROM bond_orders WHERE flag_id = :flag_id AND symbol_id = :symbol_id AND cd_code = :cd_code");
        $del_fin = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = :flag_id AND status = 0 AND cd_code = :cd_code");

        if ($new_order_after === 0) {
            $del_stmt->execute([
                ':flag_id'   => $o['flag_id'],
                ':symbol_id' => $symbol_id,
                ':cd_code'   => $o['cd_code'],
            ]);
            $del_fin->execute([
                ':flag_id'   => $o['flag_id'],
                ':cd_code'   => $o['cd_code'],
            ]);
        }

        if ($match_order_after === 0) {
            $del_stmt->execute([
                ':flag_id'   => $match['flag_id'],
                ':symbol_id' => $symbol_id,
                ':cd_code'   => $match['cd_code'],
            ]);
            $del_fin->execute([
                ':flag_id'   => $match['flag_id'],
                ':cd_code'   => $match['cd_code'],
            ]);
        }

        // ── Step 9: Update bond_trade_prices + history ────────────────────────
        // Lock existing price row if present (within the same transaction).
        $stmt = $dbh->prepare("
            SELECT symbol_id, exec_price, exec_qty, created_at
              FROM bond_trade_prices
             WHERE symbol_id = ?
             FOR UPDATE
        ");
        $stmt->execute([$symbol_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Archive current price row into history before overwriting
            $dbh->prepare("
                INSERT INTO bond_price_histories (symbol_id, exec_price, exec_qty, last_price, last_qty, last_date, created_at)
                SELECT symbol_id, exec_price, exec_qty, last_price, last_qty, last_date, created_at
                  FROM bond_trade_prices
                WHERE symbol_id = ?
            ")->execute([$symbol_id]);

            // Roll current → last, write new execution as current
            $dbh->prepare("
                UPDATE bond_trade_prices
                   SET last_price  = exec_price,
                       last_qty    = exec_qty,
                       last_date   = created_at,
                       exec_price  = ?,
                       exec_qty    = ?,
                       created_at  = NOW()
                 WHERE symbol_id   = ?
            ")->execute([$trade_price, $fill_vol, $symbol_id]);

        } else {
            // First-ever trade for this symbol
            $dbh->prepare("
                INSERT INTO bond_trade_prices (symbol_id, exec_price, exec_qty, created_at)
                VALUES (?, ?, ?, NOW())
            ")->execute([$symbol_id, $trade_price, $fill_vol]);
        }

        // ── Advance loop ──────────────────────────────────────────────────────
        $fills[]   = ['vol' => $fill_vol, 'price' => $trade_price];
        $remaining -= $fill_vol;
        error_log("remaining => {$remaining}");
    } // end while

    if (empty($fills)) {
        return ['traded' => false, 'fills' => [], 'total_traded' => 0, 'remaining' => $remaining];
    }

    return [
        'traded'       => true,
        'fills'        => $fills,
        'total_traded' => array_sum(array_column($fills, 'vol')),
        'remaining'    => $remaining,
    ];
}