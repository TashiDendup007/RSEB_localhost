<?php 
date_default_timezone_set("Asia/Thimphu");
include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php'); 
include('../../Functions/bond_com_function.php'); 
include ('bond_trade_algorithm.php');
include('../../Functions/newton_raphson_function.php');

$check = $dbh->prepare("SELECT a.institution_id, c.participant_code, a.gst_register FROM users c JOIN adm_participants b ON c.participant_code = b.participant_code JOIN adm_institution a ON b.institution_id = a.institution_id WHERE c.username = ?");
$check->execute([$username]);
$res = $check->fetch(PDO::FETCH_ASSOC);
$institution_id = $res['institution_id'];
$participant_code = $res['participant_code'];
$gst_register = $res['gst_register'];

// -------- Check trading hour---------------
$trading_hours = array(
	array('start' => '09:00:00', 'end' => '17:00:00'),
	// array('start' => '14:00:00', 'end' => '15:00:00'),
);

$check = date("H:i:s");
$dayOfWeek = date('w');

$market_open = false;
foreach ($trading_hours as $hour) {
    if ($check >= $hour['start'] && $check <= $hour['end']) {
        $market_open = true;
        break;
    }
}

// check whether working day is off
$stmt = $dbh->prepare("SELECT 1 FROM holiday WHERE holiday_date = ?");
$stmt->execute([date("Y-m-d")]);
$holiday = $stmt->fetchColumn();

if (isset($_POST['placing__bond__order'])) {

	// ── Constants ────────────────────────────────────────────────────────────────
	// define('BROKER_COMMIS_PCT', 1.0);   // 1 %
	define('GST_RATE',          0.05);  // 5 %

	// ── Sanitise & validate inputs ───────────────────────────────────────────────
	$cd_code    = trim($_POST['cd_code']    ?? '');
	$symbol_id  = trim($_POST['symbol_id']  ?? '');
	$order_type = trim($_POST['order_type'] ?? '');
	$order_side = trim($_POST['side']       ?? '');
	$accur_int  = trim($_POST['accur_int']  ?? 0);
	$dirty_price= trim($_POST['dirty_price'] ?? 0);
	$ytm        = trim($_POST['ytm_id']     ?? 0);
	$acc_type   = trim($_POST['acc_type']   ?? '');

	$vol   = filter_var($_POST['volume'] ?? '', FILTER_VALIDATE_INT);
	$price = filter_var($_POST['price']  ?? '', FILTER_VALIDATE_FLOAT);

	// check if wrong cd_code
	$stmt = $dbh->prepare("SELECT b.institution_id FROM client_account a JOIN adm_participants b ON SUBSTR(a.user_name, 1, 7) = b.participant_code WHERE a.cd_code = ?");
	$stmt->execute([$cd_code]);
	$cdcode_instId = $stmt->fetchColumn();
	
	header('Content-Type: application/json');
	// ── Transaction ──────────────────────────────────────────────────────────────
	try {
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $dbh->beginTransaction();

	    if ($cdcode_instId != $institution_id) {
	    	throw new Exception('Invalid CD CODE.');
	    }

	    if (substr($cd_code, 0, 3) === 'NRB') {
	    	throw new Exception('BLA not allowed to trade.');
	    }

	    if (in_array($acc_type, ['J', 'R', 'A']) && $symbol_id == 118) {
	    	throw new Exception('Institutions are not allowed to trade GNBB Bond.');
	    }

	    // check holiday, trading hour and weekend
	    if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		if (!$cd_code || !$symbol_id || $vol === false || $vol <= 0 || $price === false || $price <= 0 || !in_array($order_side, ['B', 'S'], true)) {
		    throw new Exception('Invalid order parameters.');
		}

		if ($vol % 10 != 0) {
			throw new Exception('Volume should be multiple of 10.');
		}

		if (!preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
		    throw new Exception('Price should be at most 2 decimal places.');
		}

	    // get commission
	    $trade_value = $price * $vol;

	    // ── Derived values ───────────────────────────────────────────────────────────
		$usr_name  = $username; // from outer scope
		$amount    = $price * $vol;
		$commission = calculateCommission($trade_value);
		$gst       = ($gst_register == 'Y') ? round($commission * GST_RATE, 2) : 0;
		$total_amt = round($amount + $commission + $gst, 2);

		$text_wrd  = ($order_side === 'B') ? 'Buy' : 'Sell';
		$remarks   = "{$text_wrd} order by {$usr_name} (member {$participant_code})" . " - vol {$vol} @ Nu. {$price}/share";

		// Unique flag: microsecond precision removes same-second collisions
		$flag_id = (int) date('YmdHis');

	    // 1. Duplicate order guard
	    if (check_bond_pending_orders($cd_code, $symbol_id, $order_side, $participant_code) === 1) {
	        throw new Exception('An order already exists. Consider updating it.');
	    }

	    // checking if oppsite side of same symbol and cd code exist
	    if (check_bond_orders_same_symbol_opposite_side($cd_code, $symbol_id, $participant_code) === 1) {
	        throw new Exception('Cannot place both Buy and Sell orders for the same symbol simultaneously.');
	    }

	    // 2. Buy — check available cash balance
	    if ($order_side === 'B') {
	        $stmt = $dbh->prepare("SELECT COALESCE(SUM(amount), 0) FROM bbo_finance WHERE cd_code = ? -- AND status = 1");
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
	    $audit_ok = order_bond_audit( $dbh, $cd_code, $participant_code, $usr_name, $vol, $vol, $symbol_id, $price, $order_side, date('Y-m-d H:i:s'), $commission, $flag_id, $usr_name, $order_type, 'OPEN', $accur_int, $dirty_price, $ytm, '');
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
	    	INSERT INTO bbo_finance (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount, symbol_id) 
	    	VALUES(:cd_code, :user_name, :remarks, 0, :institution_id, :flag_id, 0, :amount, :sym_id)
	    ");
	    $stmt->execute([
	        ':cd_code'        => $cd_code,
	        ':user_name'      => $usr_name,
	        ':remarks'        => $remarks,
	        ':institution_id' => $institution_id,
	        ':flag_id'        => $flag_id,
	        ':amount'         => $finance_amount,
	        ':sym_id'         => $symbol_id,
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

	    // 8. Immediate matching — trade executes inside the same transaction
        $trade_result = try_match_bond_trade($dbh, [
            'flag_id'          => $flag_id,
            'cd_code'          => $cd_code,
            'participant_code' => $participant_code,
            'order_entry'      => $usr_name,
            'order_date'       => date('Y-m-d H:i:s'),
            'symbol_id'        => $symbol_id,
            'order_side'       => $order_side,
            'price'            => $price,
            'vol'              => $vol,
            'institution_id'   => $institution_id,
            'usr_name'         => $usr_name,
            'gst_register'         => $gst_register,
        ]);
        error_log("trade_result_____________");
        error_log(print_r($trade_result, true));

	    $dbh->commit();
	    // echo renderAlert('success', "{$text_wrd} Order Placed Successfully.");
	    $msg = "{$text_wrd} Order Placed Successfully.";
        if ($trade_result['traded']) {
	            $total = $trade_result['total_traded'];
			    $remaining = $trade_result['remaining'];

			    // Build per-fill breakdown: "200 @ Nu. 1,040.00 + 300 @ Nu. 1,042.00"
			    $fill_summary = implode(' + ', array_map(
			        fn($f) => "{$f['vol']} @ Nu. " . number_format($f['price'], 2),
			        $trade_result['fills']
			    ));

			    $msg .= " Traded {$total} unit(s): {$fill_summary}.";

			    if ($remaining > 0) {
			        $msg .= " {$remaining} unit(s) remain open in the order book.";
			    }
        }
        
        echo json_encode([
		    "status" => "success",
		    "message" => renderAlert('success', $msg),
		]); 

	} catch (Exception $e) {
	    if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
	    error_log("Bond order failed: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");

	    echo json_encode([
		    "status" => "error",
		    "message" =>renderAlert('danger', htmlspecialchars($e->getMessage())),
		]); 

	} finally {
	    $dbh = null;
	}
	exit();
}
elseif (isset($_POST['change_bond_order'])) {
	$order_id = $_POST['order_id'];
	$symbol_id = $_POST['symbol_id'];
	$cd_code = $_POST['cd_code'];
	$flag_id = $_POST['flag_id'];
	$side = $_POST['side'];
	$new_price = $_POST['new_price'];
	$new_order_vol = $_POST['new_vol'];
	
	$ex_price = $_POST['ex_price'];
	$ex_order_vol = $_POST['ex_vol'];

	// ── Constants ────────────────────────────────────────────────────────────────
	define('GST_RATE', 0.05);  // 5 %
	
	header('Content-Type: application/json');
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		// check holiday, trading hour and weekend
	    if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		if ($new_order_vol % 10 != 0) {
		    throw new Exception('Volume should be multiple of 10');
		}

		if (!preg_match('/^\d+(\.\d{1,2})?$/', $new_price)) {
		    throw new Exception('Price should be at most 2 decimal places.');
		}

        // get previous vol and price
        $stmt = $dbh->prepare("
		    SELECT o.price, o.order_size, o.acc_intrt, o.dirty_price, o.ytm, o.order_type, b.gst_register
		    FROM bond_orders o 
		    LEFT JOIN adm_participants a ON o.participant_code = a.participant_code 
			LEFT JOIN adm_institution b ON a.institution_id = b.institution_id
		    WHERE o.id = ? AND o.flag_id = ?
		");
		$stmt->execute([$order_id, $flag_id]);
		$res = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$res) {
			throw new Exception('No order existed.');
		}

		$ex_price = $res['price'];
		$ex_order_vol = $res['order_size'];
		$accured_int = $res['acc_intrt'];
		$dirty_price = $res['dirty_price'];
		$ytm = $res['ytm'];
		$order_type = $res['order_type'];
		$gst_register = $res['gst_register'];

		// 1. check if there is any changes in vol and price
		if ($ex_price == $new_price && $ex_order_vol == $new_order_vol) {
			throw new Exception('No change in price and volume.');
		}

		// If there is a change in price, generate new ytm, accured interest, dirty price
		$ytm_data = [];
		if ($ex_price !== $new_price) {
			$ytm_data_raw = calculate_yield_to_maturity($dbh, $symbol_id, $new_price);
			$ytm_data = json_decode($ytm_data_raw, true);

			// replace with new details
			$dirty_price = $ytm_data['dirtyPrice'];
			$accured_int = $ytm_data['accrued'];
			$ytm = $ytm_data['ytm'];
		}

		// Amount Calculation
		$amout = $new_price * $new_order_vol;
		$commission = calculateCommission($amout);
		$gst = round($commission * GST_RATE, 2);
		$total_amt = round($amout + $commission, 2);
		$final_total_amt = ($gst_register === 'Y') ? ($total_amt + $gst) : $total_amt;

		// 2. check amount of the buyer (if any)
		if ($side === 'B') {
			$stmt = $dbh->prepare("SELECT SUM(m.amount) AS total_amount FROM bbo_finance m WHERE m.cd_code = ? AND m.flag_id != ? -- AND m.status = 1");
			$stmt->execute([$cd_code, $flag_id]);
			$avail__holding__amount = $stmt->fetchColumn();
			if ($avail__holding__amount < $final_total_amt) {
				$avail__holding__amount = isset($avail__holding__amount) ? $avail__holding__amount : 0;
				throw new Exception('Insufficient cash. Available: ' . number_format($avail__holding__amount, 2));
			}
		}

		// 3. check holding available of seller
		if ($side === 'S') {
			$stmt = $dbh->prepare("SELECT h.volume, h.pending_out_vol FROM cds_holding h WHERE h.cd_code = ? AND h.symbol_id = ?");
			$stmt->execute([$cd_code, $symbol_id]);
			$res = $stmt->fetch(PDO::FETCH_ASSOC);

			$holding_vol = $res['volume'];
			$pending_out = $res['pending_out_vol'];

			$available_vol = $holding_vol + $ex_order_vol;

			// Validate existing reservation (pending out vol should not be less than old order size)
			if ($pending_out < $ex_order_vol) {
		        throw new Exception('Inconsistent pending out volume.');
		    }

		    // validate new order
		    if ($available_vol < $new_order_vol) {
		        throw new Exception('Insufficient shares available');
		    }

		    // release old order
		    $adjusted_pending_out = $pending_out - $ex_order_vol;
		    $new__holding = $available_vol - $new_order_vol;
		    $new_pending_out = $adjusted_pending_out + $new_order_vol;

		    // update holding of the seller (if any):
			$update = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = ?, volume = ? WHERE cd_code = ? AND symbol_id = ?");
			$update->execute([
				$new_pending_out, $new__holding, $cd_code, $symbol_id
			]);
		}

		$term = ($side === 'B') ? 'BUY' : 'SELL';
		$remarks   = "{$term} order by {$username} (member {$participant_code})" . " — vol {$new_order_vol} @ Nu. {$new_price}/share";

		// 4. update bbo finance
		$final_total_amt = ($side === 'B') ? ($final_total_amt * -1) : $final_total_amt;
		$bbo_update = $dbh->prepare("UPDATE bbo_finance SET remarks = ?, amount = ? WHERE flag_id = ? AND flag = 0");
		$bbo_update->execute([$remarks, $final_total_amt, $flag_id]);

		// 5. update bond order audits
		$order_audit = order_bond_audit($dbh, $cd_code, $participant_code, $username, $new_order_vol, $new_order_vol, $symbol_id, $new_price, $side, date('Y-m-d H:i:s'), $commission, $flag_id, $username, $order_type, 'UPDATED', $accured_int, $dirty_price, $ytm, '');

		// 6. update order 
		$order_update = $dbh->prepare("
				UPDATE bond_orders 
	            SET sell_vol   = CASE WHEN side = 'S' THEN :new_sell_vol ELSE sell_vol END,
	                buy_vol    = CASE WHEN side = 'B' THEN :new_buy_vol ELSE buy_vol END,
	                order_size = CASE WHEN side = 'S' THEN :new_sell_vol ELSE :new_buy_vol END,
	                price      = :new_price, 
	                commis_amt = :new_commis_amt,  
	                acc_intrt  = :acc_int, 
	               dirty_price = :dirt_price, 
	                ytm        = :ytm, 
	                status     = 'UPDATED'
	            WHERE id = :id
      	");
		$order_update->execute([':new_sell_vol' => $new_order_vol, ':new_buy_vol' => $new_order_vol, ':new_price' => $new_price, ':new_commis_amt' => $commission, ':acc_int' => $accured_int, ':dirt_price' => $dirty_price, ':ytm' => $ytm, ':id' => $order_id]);

		// 7. find matching order and trade execution when updates
		$trade_result = try_match_bond_trade($dbh, [
            'flag_id'          => $flag_id,
            'cd_code'          => $cd_code,
            'participant_code' => $participant_code,
            'order_entry'      => $username,
            'order_date'       => date('Y-m-d H:i:s'),
            'symbol_id'        => $symbol_id,
            'order_side'       => $side,
            'price'            => $new_price,
            'vol'              => $new_order_vol,
            'institution_id'   => $institution_id,
            'usr_name'         => $username,
        ]);
        error_log("trade_result_____________");
        error_log(print_r($trade_result, true));

		$dbh->commit();
		$dbh = null;
		
		$msg = "{$term} Order Updated Successfully.";

        if ($trade_result['traded']) {
	            $total = $trade_result['total_traded'];
			    $remaining = $trade_result['remaining'];

			    // Build per-fill breakdown: "200 @ Nu. 1,040.00 + 300 @ Nu. 1,042.00"
			    $fill_summary = implode(' + ', array_map(
				        fn($f) => "{$f['vol']} @ Nu. " . number_format($f['price'], 2),
				        $trade_result['fills']
			    ));

			    $msg .= " Traded {$total} unit(s): {$fill_summary}.";

			    if ($remaining > 0) {
			        $msg .= " {$remaining} unit(s) remain open in the order book.";
			    }
        }

        echo json_encode([
		    "message" => renderAlert('success', $msg),
		]);
	} catch (Exception $e) {
		if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
		echo json_encode([
		    "message" => renderAlert('danger', $e->getMessage()),
		]);
	}
	exit();
}
elseif(!empty($_POST["cancle_bond_id"])) {
	$id = $_POST["cancle_bond_id"];	// order id
	$fid = $_POST["fid"]; // flag id
	$side = $_POST["side"];
	$cd_code = $_POST["cd_code"];
	$sy_id = $_POST["sy_id"]; // symbol id

	// check if the market is close
	header('Content-Type: application/json');
	$message = '';
	$status = 0;
	$data = [];

	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

	    // check holiday, trading hour and weekend
	    if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		$vol_col = ($side === 'S') ? 'sell_vol' : 'buy_vol';

		// check order
		$stmt = $dbh->prepare("SELECT b.id, b.order_size, b.{$vol_col} AS vol_col, b.cd_code, b.symbol_id FROM bond_orders b WHERE b.symbol_id = :sym_id AND b.side = :side AND b.flag_id = :fid");
		$stmt->execute([
			':sym_id' => $sy_id, ':side' => $side, ':fid' => $fid, 
		]);
		$rows = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$rows) {
			throw new Exception('Order id not found.');
		}

		// get existing order from order table
		$stmt = $dbh->prepare("SELECT order_size FROM bond_orders WHERE id = ? AND order_size - {$vol_col} = 0");
		$stmt->execute([$id]);
		$ord_vol = $stmt->fetchColumn();

		if (!$ord_vol) {
			throw new Exception('Issue with order size. Please contact RSEB support.');
		}

		// Update the cds_holding table
		if ($side == 'S') {
    		// get vol and pov to check negative error
			$get_val = $dbh->prepare("SELECT pending_out_vol, volume FROM cds_holding WHERE symbol_id = ? AND cd_code = ?");
			$get_val->bindParam(1, $sy_id);
			$get_val->bindParam(2, $cd_code);
			$get_val->execute();
			$row = $get_val->fetch(PDO::FETCH_ASSOC);

			$old_pov = $row['pending_out_vol'];
			$old_volume = $row['volume'];

			$upd_pending_out_vol = $old_pov - $ord_vol;
			$upd_volume = $old_volume + $ord_vol;

			if ($upd_pending_out_vol < 0) {
				throw new Exception('Negative Error. Please contact RSEB support.');
			}

			$cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = ?, volume = ? WHERE cd_code = ? AND symbol_id = ?");
			$cds_acc->bindParam(1, $upd_pending_out_vol);
			$cds_acc->bindParam(2, $upd_volume);
			$cds_acc->bindParam(3, $cd_code);
			$cds_acc->bindParam(4, $sy_id);
			$result = $cds_acc->execute();
			if (!$result) {
				throw new Exception('Operation exception. Please contact RSEB support.');
			}
		}

		// update delete status
		$stmt = $dbh->prepare("UPDATE bond_orders SET status = 'DELETED' WHERE flag_id = ? AND id = ? AND symbol_id = ? AND cd_code = ?");
		$stmt->execute([$fid, $id, $sy_id, $cd_code]);

		// insert from bond orders to bond order audits
		$audit_stmt = $dbh->prepare("
            INSERT INTO bond_order_audits (bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, side, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status, flag, user_name)
            SELECT id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, side, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status, 'C', ? 
            FROM bond_orders
            WHERE id = ?
        ");
        $audit_stmt->execute([$username, $id]);

    	// Delete the order from the orders table
		$del_order = $dbh->prepare("DELETE FROM bond_orders WHERE id = :id");
		$del_order->bindParam(':id', $id);
		$del_order->execute();

    	// Delete from bbo_finance table
		$del_bbo = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = :fid AND cd_code = :cdcode AND flag = 0");
		$del_bbo->bindParam(':fid', $fid);
		$del_bbo->bindParam(':cdcode', $cd_code);
		$del_bbo->execute();

		// Commit the transaction
		$dbh->commit();
		// close the database connection
		$dbh = null;

		echo json_encode([
			"status" => 1,
        	"message" => renderAlert('success', 'Order Deleted Successfully'),
		]);
	} catch(Exception $e) {
		if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
		error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
		
		echo json_encode([
			"status" => 2,
        	"message" => renderAlert('danger', htmlspecialchars($e->getMessage())),
		]);
	} 
	exit();
}

// ── Helper ───────────────────────────────────────────────────────────────────
function renderAlert(string $type, string $message): string
{
    return <<<HTML
    <div class="col-lg-12 col-xs-12">
        <div class="alert alert-{$type} alert-dismissible">{$message}</div>
    </div>
    HTML;
}

?>