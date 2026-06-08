<?php
    if (isset($_POST['execute_offer_rfq'])) {

    header('Content-Type: application/json');

    try {

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $buyer_cdcode  = trim($_POST['buyer_cdcode']);
        $seller_cdcode = trim($_POST['seller_cdcode']);
        $symbol_id     = trim($_POST['symbol_id']);
        $flag_id       = trim($_POST['flag_id']);
        $sell_vol      = (int)$_POST['sell_vol'];

        if ($sell_vol % 10 != 0) {
            throw new Exception("Order should be multiple of 10.");
        }

        if (empty($buyer_cdcode) || empty($seller_cdcode)) {
            throw new Exception("Buyer and Seller CD Code required.");
        }

        /*
        |--------------------------------------------------------------------------
        | FETCH BUY ORDER
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT *
            FROM bond_orders
            WHERE cd_code = ?
            AND symbol_id = ?
            AND side = 'B'
            LIMIT 1
            FOR UPDATE
        ";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([$buyer_cdcode, $symbol_id]);

        $buy = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$buy) {
            throw new Exception("Buyer order not found.");
        }

        /*
        |--------------------------------------------------------------------------
        | FETCH SELL ORDER
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT *
            FROM bond_orders
            WHERE cd_code = ?
            AND symbol_id = ?
            AND flag_id = ?
            AND side = 'S'
            LIMIT 1
            FOR UPDATE
        ";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([$seller_cdcode, $symbol_id, $flag_id]);

        $sell = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sell) {
            throw new Exception("Seller order not found.");
        }

        /*
        |--------------------------------------------------------------------------
        | EXECUTION CALCULATION
        |--------------------------------------------------------------------------
        */

        $exec_vol = min($buy['order_size'], $sell['order_size']);

        $buy_balance  = $buy['order_size'] - $exec_vol;
        $sell_balance = $sell['order_size'] - $exec_vol;

        $buy_status  = ($buy_balance == 0) ? 'EXECUTED' : 'PENDING';
        $sell_status = ($sell_balance == 0) ? 'EXECUTED' : 'PENDING';

        $exec_price       = $sell['price'];
        $exec_dirty_price = $sell['dirty_price'];
        $exec_ytm         = $sell['ytm'];
        $exec_acc_intrt   = $sell['acc_intrt'];

        /*
        |--------------------------------------------------------------------------
        | UPDATE ORDERS
        |--------------------------------------------------------------------------
        */

        $upd = $dbh->prepare("
            UPDATE bond_orders
            SET
                order_size = ?,
                status     = ?,
                exe_vol    = ?,
                exe_price  = ?,
                lot_check  = ?
            WHERE id = ?
        ");

        // BUY UPDATE
        $upd->execute([
            $buy_balance,
            $buy_status,
            $exec_vol,
            $exec_price,
            $exec_vol,
            $buy['id']
        ]);

        // SELL UPDATE
        $upd->execute([
            $sell_balance,
            $sell_status,
            $exec_vol,
            $exec_price,
            $exec_vol,
            $sell['id']
        ]);

        /*
        |--------------------------------------------------------------------------
        | ORDER AUDIT
        |--------------------------------------------------------------------------
        */
        $auditSql = "INSERT INTO bond_order_audits(bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status) SELECT id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status FROM bond_orders WHERE id = ?";
        $auditStmt = $dbh->prepare($auditSql);
        $auditStmt->execute([$buy['id']]);
        $auditStmt->execute([$sell['id']]);

        /*
        |--------------------------------------------------------------------------
        | EXECUTION ENTRIES
        |--------------------------------------------------------------------------
        */

        $execSql = "
            INSERT INTO bond_executed_orders (
                cd_code,
                participant_code,
                sub_user,
                member_broker,
                order_date,
                symbol_id,
                order_exe_price,
                lot_size_execute,
                status,
                side,
                lot_check,
                flag_id,
                dirty_price,
                accur_rate,
                ytm
            )
            VALUES (
                :cd_code,
                :participant_code,
                :sub_user,
                :member_broker,
                :order_date,
                :symbol_id,
                :order_exe_price,
                :lot_size_execute,
                0,
                :side,
                :lot_check,
                :flag_id,
                :dirty_price,
                :accur_rate,
                :ytm
            )
        ";

        $execStmt = $dbh->prepare($execSql);

        foreach ([$buy, $sell] as $order) {

            $execStmt->execute([
                ':cd_code'          => $order['cd_code'],
                ':participant_code' => $order['participant_code'],
                ':sub_user'         => $order['order_entry'],
                ':member_broker'    => $order['member_broker'],
                ':order_date'       => $order['order_date'],
                ':symbol_id'        => $symbol_id,
                ':order_exe_price'  => $exec_price,
                ':lot_size_execute' => $exec_vol,
                ':side'             => $order['side'],
                ':lot_check'        => $exec_vol,
                ':flag_id'          => $order['flag_id'],
                ':dirty_price'      => $exec_dirty_price,
                ':accur_rate'       => $exec_acc_intrt,
                ':ytm'              => $exec_ytm
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | FINANCE ENTRIES
        |--------------------------------------------------------------------------
        */

        $gst_register      = 'Y';
        $broker_commission = 1; // %

        $exec_amount = $exec_price * $exec_vol;
        $comm_fee    = ($exec_amount * $broker_commission) / 100;
        $gst_fee     = ($gst_register == 'Y') ? ($comm_fee * 0.05) : 0;

        $financeSql = "
            INSERT INTO bbo_finance (
                cd_code,
                amount,
                user_name,
                remarks,
                flag,
                institution_id,
                flag_id
            )
            VALUES (
                :cd_code,
                :amount,
                :user_name,
                :remarks,
                :flag,
                :institution_id,
                :flag_id
            )
        ";

        $financeStmt = $dbh->prepare($financeSql);

        /*
        |--------------------------------------------------------------------------
        | HELPER FUNCTION
        |--------------------------------------------------------------------------
        */

        $insertFinance = function(
            $cd_code,
            $amount,
            $user_name,
            $remarks,
            $flag,
            $flag_id
        ) use ($financeStmt, $institution_id) {

            $financeStmt->execute([
                ':cd_code'       => $cd_code,
                ':amount'        => $amount,
                ':user_name'     => $user_name,
                ':remarks'       => $remarks,
                ':flag'          => $flag,
                ':institution_id'=> $institution_id,
                ':flag_id'       => $flag_id
            ]);
        };

        /*
        |--------------------------------------------------------------------------
        | BUYER FINANCE
        |--------------------------------------------------------------------------
        */

        // BUY AMOUNT
        $insertFinance(
            $buyer_cdcode,
            -abs($exec_amount),
            $buy['member_broker'],
            "Bond purchase amount for {$exec_vol} units @ Nu. {$exec_price}",
            3,
            $buy['flag_id']
        );

        // BUY COMMISSION
        $insertFinance(
            $buyer_cdcode,
            -abs($comm_fee),
            $buy['member_broker'],
            "Commission for {$exec_vol} units @ Nu. {$exec_price}",
            4,
            $buy['flag_id']
        );

        // BUY GST
        if ($gst_fee > 0) {

            $insertFinance(
                $buyer_cdcode,
                -abs($gst_fee),
                $buy['member_broker'],
                "GST for {$exec_vol} units @ Nu. {$exec_price}",
                5,
                $buy['flag_id']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SELLER FINANCE
        |--------------------------------------------------------------------------
        */

        // SELL AMOUNT
        $insertFinance(
            $seller_cdcode,
            abs($exec_amount),
            $sell['member_broker'],
            "Bond sell amount for {$exec_vol} units @ Nu. {$exec_price}",
            2,
            $sell['flag_id']
        );

        // SELL COMMISSION
        $insertFinance(
            $seller_cdcode,
            -abs($comm_fee),
            $sell['member_broker'],
            "Commission for {$exec_vol} units @ Nu. {$exec_price}",
            4,
            $sell['flag_id']
        );

        // SELL GST
        if ($gst_fee > 0) {

            $insertFinance(
                $seller_cdcode,
                -abs($gst_fee),
                $sell['member_broker'],
                "GST for {$exec_vol} units @ Nu. {$exec_price}",
                5,
                $sell['flag_id']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE FULLY EXECUTED ORDERS
        |--------------------------------------------------------------------------
        */

        $deleteStmt = $dbh->prepare("
            DELETE FROM bond_orders
            WHERE id = ?
        ");

        if ($buy_balance == 0) {
            $deleteStmt->execute([$buy['id']]);
        }

        if ($sell_balance == 0) {
            $deleteStmt->execute([$sell['id']]);
        }

        $dbh->commit();

        echo json_encode([
            'success' => true,
            'message' => 'RFQ order executed successfully.'
        ]);

    } catch (Exception $e) {

        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit();
}
/*// for buy
$upd_buy = $dbh->prepare("UPDATE bond_orders SET order_size = ?, buy_vol = ?, status = ?, exe_vol = exe_vol + ?, exe_price = ?, lot_check = lot_check + ? WHERE id = ?");
// for sell
$upd_sell = $dbh->prepare("UPDATE bond_orders SET order_size = ?, sell_vol = ?, status = ?, exe_vol = exe_vol + ?, exe_price = ?, lot_check = lot_check + ? WHERE id = ?");
// BUY UPDATE
$upd->execute([ $buy_balance, $buy_balance, $buy_status, $exec_vol, $exec_price, $exec_vol, $buy['id'] ]);
// SELL UPDATE
$upd_sell->execute([ $sell_balance, $sell_balance, $sell_status, $exec_vol, $exec_price, $exec_vol, $sell['id'] ]);*/
?>