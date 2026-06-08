<?php
if (!isset($_POST['placing__bond__order'])) {
    exit();
}

// ── Constants ────────────────────────────────────────────────────────────────
const BROKER_COMMIS_PCT = 1.0;   // 1 %
const GST_RATE          = 0.05;  // 5 %

// ── Sanitise & validate inputs ───────────────────────────────────────────────
$cd_code    = trim($_POST['cd_code']    ?? '');
$symbol_id  = trim($_POST['symbol_id'] ?? '');
$order_type = trim($_POST['order_type'] ?? '');
$order_side = trim($_POST['side']       ?? '');
$accur_int  = trim($_POST['accur_int']  ?? '');
$dirty_price= trim($_POST['dirty_price'] ?? '');
$ytm        = trim($_POST['ytm_id']     ?? '');

$vol   = filter_var($_POST['volume'] ?? '', FILTER_VALIDATE_INT);
$price = filter_var($_POST['price']  ?? '', FILTER_VALIDATE_FLOAT);

if (!$cd_code || !$symbol_id || $vol === false || $vol <= 0 || $price === false || $price <= 0 || !in_array($order_side, ['B', 'S'], true)) {
    echo renderAlert('danger', 'Invalid order parameters.');
    exit();
}

// ── Derived values ───────────────────────────────────────────────────────────
$usr_name  = $username;           // from outer scope
$amount    = $price * $vol;
$commission= $amount * BROKER_COMMIS_PCT / 100;
$gst       = $commission * GST_RATE;
$total_amt = $amount + $commission + $gst;

$text_wrd  = ($order_side === 'B') ? 'Buy' : 'Sell';
$remarks   = "{$text_wrd} order by {$usr_name} (member {$participant_code})" . " — vol {$vol} @ Nu. {$price}/share";

// Unique flag: microsecond precision removes same-second collisions
$flag_id = uniqid('', true);

// ── Transaction ──────────────────────────────────────────────────────────────
try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    // 1. Duplicate order guard
    if (check_bond_pending_orders($cd_code, $symbol_id, $order_side, $participant_code) === 1) {
        throw new Exception('An order already exists. Consider updating it.');
    }

    // 2. Buy — check available cash balance
    if ($order_side === 'B') {
        $stmt = $dbh->prepare("SELECT COALESCE(SUM(amount), 0) FROM bbo_finance WHERE cd_code = ? AND status = 1");
        $stmt->execute([$cd_code]);
        $avail_balance = (float) $stmt->fetchColumn();

        if ($total_amt > $avail_balance) {
            throw new Exception('Insufficient cash. Available: ' . number_format($avail_balance, 2));
        }
    }

    // 3. Sell — check unencumbered holding volume
    if ($order_side === 'S') {
        $stmt = $dbh->prepare("SELECT COALESCE(volume - pending_out_vol, 0) FROM cds_holding  WHERE cd_code = ? AND symbol_id = ?");
        $stmt->execute([$cd_code, $symbol_id]);
        $free_vol = (float) $stmt->fetchColumn();

        if ($vol > $free_vol) {
            throw new Exception('Insufficient shares. Available: ' . number_format($free_vol, 2));
        }
    }

    // 4. Audit trail
    $audit_ok = order_bond_audit( $dbh, $cd_code, $participant_code, $usr_name, $vol, $vol, $symbol_id, $price, $order_side, BROKER_COMMIS_PCT, $flag_id, $usr_name,$order_type, $accur_int, $dirty_price, $ytm, '');
    if (!$audit_ok) {
        throw new Exception('Failed to write order audit record.');
    }

    // 5. Insert bond order
    $stmt = $dbh->prepare("
        INSERT INTO bond_orders(cd_code, participant_code, order_entry, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker, sell_vol, buy_vol, acc_intrt, dirty_price, ytm, order_type)
        VALUES(:cd_code, :participant_code, :order_entry, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker, :sell_vol, :buy_vol, :acc_intrt, :dirty_price, :ytm, :order_type)
    ");
    $stmt->execute([
        ':cd_code'          => $cd_code,
        ':participant_code' => $participant_code,
        ':order_entry'      => $usr_name,
        ':order_size'       => $vol,
        ':symbol_id'        => $symbol_id,
        ':price'            => $price,
        ':side'             => $order_side,
        ':commis_amt'       => $commission,
        ':flag_id'          => $flag_id,
        ':member_broker'    => $usr_name,
        ':sell_vol'         => ($order_side === 'S') ? $vol : 0,
        ':buy_vol'          => ($order_side === 'B') ? $vol : 0,
        ':acc_intrt'        => $accur_int,
        ':dirty_price'      => $dirty_price,
        ':ytm'              => $ytm,
        ':order_type'       => $order_type,
    ]);

    // 6. Finance ledger entry  (negative = cash out for buys, positive = proceeds for sells)
    $finance_amount = ($order_side === 'S') ? $total_amt : -$total_amt;

    $stmt = $dbh->prepare("
    	INSERT INTO bbo_finance (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount) 
    	VALUES(:cd_code, :user_name, :remarks, 0, :institution_id, :flag_id, 0, :amount)
    ");
    $stmt->execute([
        ':cd_code'        => $cd_code,
        ':user_name'      => $usr_name,
        ':remarks'        => $remarks,
        ':institution_id' => $institution_id,
        ':flag_id'        => $flag_id,
        ':amount'         => $finance_amount,
    ]);

    // 7. Sell — move volume from available to pending outgoing
    if ($order_side === 'S') {
        $stmt = $dbh->prepare("
            UPDATE cds_holding
               	SET volume = volume - :vol, pending_out_vol = pending_out_vol + :vol
         	WHERE cd_code = :cd_code AND symbol_id = :symbol_id
        ");
        $stmt->execute([
            ':vol'       => $vol,
            ':cd_code'   => $cd_code,
            ':symbol_id' => $symbol_id,
        ]);
    }

    $dbh->commit();
    echo renderAlert('success', "{$text_wrd} Order Placed Successfully.");

} catch (Exception $e) {
    if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }
    error_log("Bond order failed: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");
    echo renderAlert('danger', htmlspecialchars($e->getMessage()));

} finally {
    $dbh = null;
}
exit();





?>	