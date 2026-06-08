<?php 
date_default_timezone_set("Asia/Thimphu");
//include ('../../CONNECTIONS/function-sanitize.php');
include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php'); 
include('../../Functions/bond_com_function.php'); 
include ('../../CONNECTIONS/trading_hours.php');
include('../../Functions/newton_raphson_function.php');

$check = $dbh->prepare("SELECT a.institution_id, c.participant_code, c.cd_code, a.gst_register 
		FROM users c
		JOIN adm_participants b ON c.participant_code = b.participant_code
		JOIN adm_institution a ON b.institution_id = a.institution_id
		WHERE c.username = ?
");
$check->execute([$username]);
$res = $check->fetch(PDO::FETCH_ASSOC);
$institution_id = $res['institution_id'];
$participant_code = $res['participant_code'];
$gst_register = $res['gst_register'];

// -------- Check trading hour---------------
$trading_hours = array(
	array('start' => '09:00:00', 'end' => '13:00:00'),
	array('start' => '14:00:00', 'end' => '15:00:00'),
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
	$cd_code = $_POST['cd_code'];
	$usr_name = $username;
	$vol = $_POST['volume'];
	$symbol_id = $_POST['symbol_id'];
	$order_side = $_POST['side'];

	$price = ($order_side === 'S') ? $_POST['price'] : 0;

	$order_type = $_POST['order_type'];
	$accur_int = ($_POST['accur_int'] !== '') ? $_POST['accur_int'] : 0;
	$dirty_price = ($_POST['dirty_price'] !== '') ? $_POST['dirty_price'] : 0;
	$ytm = ($_POST['ytm_id'] !== '') ? $_POST['ytm_id'] : 0;
	$financestatus = 0;

	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();
		
		if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		$commission = 0;
		$total_amt = 0;
		if ($order_side !== 'B') {
			$amout = $price * $vol;
			$commission= calculateCommission($amout);
			$gst = round($commission * 0.05, 2);
			$total_amt = round($amout + $commission + $gst, 2);
		}

		// check if order already exists
		$find_existing_order = check_bond_pending_orders($cd_code, $symbol_id, $order_side, $participant_code);
		if ($find_existing_order == 1) {
			throw new Exception('An order already exists. Consider updating it.');
		}

		// check available balance
		// dont have to check exposure amount for the dealer
		/*if ($order_side == 'B') {
		  	$stmt = $dbh->prepare("SELECT SUM(m.amount) AS total_amount FROM bbo_finance m WHERE m.cd_code = ? -- AND m.status = 1");
			$stmt->execute([$cd_code]);
			$avail__holding__amount = $stmt->fetchColumn();
			if ($total_amt > $avail__holding__amount) {
				$avail__holding__amount = isset($avail__holding__amount) ? $avail__holding__amount : 0;
				throw new Exception('Insufficient cash. Available: ' . number_format($avail__holding__amount, 2));
			}
	  	}*/

	  	// check if real volume available
		if ($order_side == 'S') {
			$stmt = $dbh->prepare("SELECT h.volume FROM cds_holding h WHERE h.cd_code = ? AND h.symbol_id = ?");
			$stmt->execute([$cd_code, $symbol_id]);
			$holding__vol = $stmt->fetchColumn();
			if ($vol > $holding__vol) {
				throw new Exception('Insufficient shares.');
			}
		}

		$flag_id = date("ymdhis");
		$financestatus = 0;

		// $flag = match ($order_side) { 'B' => 3, 'S' => 2, default => 0, };
		$flag = 0;

		$text_wrd = match ($order_side) {
		    'B' => 'Buy',
		    'S' => 'Sell',
		    default => '',
		};

		$remarks = $text_wrd.' Order entry by user '.$usr_name.' of member '.$participant_code.' of volume '.$vol.' @ Nu. '.$price.'/share';

	  	// enter into order audit
		$order_audit = order_bond_audit($dbh, $cd_code, $participant_code, $usr_name, $vol, $vol, $symbol_id, $price, $order_side, date('Y-m-d H:i:s'), $commission, $flag_id, $usr_name, $order_type, 'OPEN', $accur_int, $dirty_price, $ytm, '');

		// Insert into bond orders
		$stmt = $dbh->prepare("
				INSERT INTO bond_orders (cd_code, participant_code, order_entry, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker, sell_vol, buy_vol, acc_intrt, dirty_price, ytm, order_type) 
				VALUES (:cd_code, :participant_code, :order_entry, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker, :sell_vol, :buy_vol, :ac_in, :dir_price, :ytm, :or_type)
		");
		$sellVol = ($order_side === 'S') ? $vol : 0;
		$buyVol  = ($order_side === 'B') ? $vol : 0;

		$stmt->execute([
		    ':cd_code'         => $cd_code,
		    ':participant_code'=> $participant_code,
		    ':order_entry'     => $usr_name,
		    ':order_size'      => $vol,
		    ':symbol_id'       => $symbol_id,
		    ':price'           => $price,
		    ':side'            => $order_side,
		    ':commis_amt'      => $commission,
		    ':flag_id'         => $flag_id,
		    ':member_broker'   => $usr_name,
		    ':sell_vol'        => $sellVol,
		    ':buy_vol'         => $buyVol,
		    ':ac_in'           => $accur_int,
		    ':dir_price'       => $dirty_price,
		    ':ytm'             => $ytm,
		    ':or_type'         => $order_type,
		]);

		// insert into bbo finance
		$stmt = $dbh->prepare("
				INSERT INTO bbo_finance (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount, symbol_id) 
				VALUES (:cd_code, :user_name, :remarks, :flag, :institution_id, :flag_id, :financestatus, :amount, :sy_id)
		");

		// Compute amount in PHP (faster & clearer than SQL IF)
		$finalAmount = ($order_side === 'S') ? $total_amt : -$total_amt;

		$stmt->execute([
		    ':cd_code'         => $cd_code,
		    ':user_name'       => $usr_name,
		    ':remarks'         => $remarks,
		    ':flag'            => $flag,
		    ':institution_id'  => $institution_id,
		    ':flag_id'         => $flag_id,
		    ':financestatus'   => $financestatus,
		    ':amount'          => $finalAmount,
		    ':sy_id'           => $symbol_id,
		]);

		if ($order_side == 'S') {
				$cds_update_stmt = $dbh->prepare("
					UPDATE cds_holding 
				    SET volume = volume - :vol, pending_out_vol = pending_out_vol + :vol 
				    WHERE cd_code = :cdcode AND symbol_id = :sy_id
				");
				$cds_update_stmt->bindParam(':vol', $vol);
				$cds_update_stmt->bindParam(':cdcode', $cd_code);
				$cds_update_stmt->bindParam(':sy_id', $symbol_id);
				$cds_update_stmt->execute();
		}

		$dbh->commit();
		$dbh = null;

		echo'
		<div class="col-lg-12 col-xs-12">
			<div class="alert alert-success alert-dismissible">
				'.$text_wrd.' Order Placed Successfully.
			</div>
		</div>';
		exit();
	} catch (Exception $e) {
		if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
	    // error_log("Transaction failed ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
	    echo '
	    <div class="col-lg-12 col-xs-12">
	        <div class="alert alert-danger alert-dismissible">
	            ' . htmlspecialchars($e->getMessage()) . '
	        </div>
	    </div>';
		exit();
	}
	exit();
}
elseif (isset($_POST['change_bond_order'])) {
	$order_id = $_POST['order_id'];
	$symbol_id = $_POST['symbol_id'];
	$cd_code = $_POST['cd_code'];
	$flag_id = $_POST['flag_id'];
	$side = $_POST['side'];
	
	$new_price = ($side === 'S') ? $_POST['new_price'] : 0;
	
	$new_order_vol = $_POST['new_vol'];
	$ex_price = $_POST['ex_price'];
	$ex_order_vol = $_POST['ex_vol'];

	header('Content-Type: application/json');
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		if ($new_order_vol % 10 != 0) {
		    throw new Exception('Vol should be multiple of 10');
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
		// $gst_register = $res['gst_register'];

		if ($ex_price == $new_price && $ex_order_vol == $new_order_vol) {
			throw new Exception('No change in price and volume.');
		}

		// If there is a change in price for seller, generate new ytm, accured interest, dirty price
		$ytm_data = [];
		if ($side === 'S' && $ex_price !== $new_price) {
			$ytm_data_raw = calculate_yield_to_maturity($dbh, $symbol_id, $new_price);
			$ytm_data = json_decode($ytm_data_raw, true);

			// replace with new details
			$dirty_price = $ytm_data['dirtyPrice'];
			$accured_int = $ytm_data['accrued'];
			$ytm = $ytm_data['ytm'];
		}

		// Amount Calculation
		$amout = $new_price * $new_order_vol;
		$commission = ($side === 'S') ? calculateCommission($amout) : 0;
		$gst = round($commission * 0.05, 2);
		$total_amt = round($amout + $commission, 2);
		$final_total_amt = ($gst_register === 'Y') ? ($total_amt + $gst) : $total_amt;

		// check if real volume available
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
			$update->execute([$new_pending_out, $new__holding, $cd_code, $symbol_id]);
		}

		$term = ($side === 'B') ? 'BUY' : 'SELL';
		$remarks = $term.' Order entry by user '.$username.' of member '.$participant_code.', of volume '.$new_order_vol.' @ Nu. '.$new_price.'/share';

		// update bbo finance
		$final_total_amt = ($side === 'B') ? ($final_total_amt * -1) : $final_total_amt;

		$bbo_update = $dbh->prepare("UPDATE bbo_finance SET remarks = ?, amount = ? WHERE flag_id = ?");
		$bbo_update->execute([$remarks, $final_total_amt, $flag_id]);

		// update bond order audit
		$order_audit = order_bond_audit($dbh, $cd_code, $participant_code, $username, $new_order_vol, $new_order_vol, $symbol_id, $new_price, $side, date('Y-m-d H:i:s'), $commission, $flag_id, $username, $order_type, 'UPDATED', $accured_int, $dirty_price, $ytm, '');

		// update order 
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
	            WHERE id       = :id
      	");
		$order_update->execute([':new_sell_vol' => $new_order_vol, ':new_buy_vol' => $new_order_vol, ':new_price' => $new_price, ':new_commis_amt' => $commission, ':acc_int' => $accured_int, ':dirt_price' => $dirty_price, ':ytm' => $ytm, ':id' => $order_id]);

		$dbh->commit();
		$dbh = null;
		echo json_encode([
		    "message" => '
		        <div class="col-lg-12 col-xs-12">
			        <div class="alert alert-success alert-dismissible">
			            '. $term .' order updated successfully.
			        </div>
			    </div>
		    '
		]);
	} catch (Exception $e) {
		if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
	    error_log("message => {$e->getMessage()}, line => {$e->getLine()}");
		echo json_encode([
		    "message" => '
		        <div class="col-lg-12 col-xs-12">
			        <div class="alert alert-danger alert-dismissible">
			            ' . htmlspecialchars($e->getMessage()) . '
			        </div>
			    </div>
		    '
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
		try {
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbh->beginTransaction();

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

			// get existing order from the order table
			$stmt = $dbh->prepare("SELECT order_size FROM bond_orders WHERE id = ? AND order_size - {$vol_col} = 0");
			$stmt->execute([$id]);
			$ord_vol = $stmt->fetchColumn();

			if (!$ord_vol) {
				throw new Exception('Issue with order size. Please contact RSEB support.');
			}

			// Update the cds_holding 
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

			// update DELETE status
			$stmt = $dbh->prepare("UPDATE bond_orders SET status = 'DELETED' WHERE flag_id = ? AND id = ? AND symbol_id = ? AND cd_code = ?");
			$stmt->execute([$fid, $id, $sy_id, $cd_code]);

			// insert from bond orders to bond order audits
			$audit_stmt = $dbh->prepare("
					INSERT INTO bond_order_audits (bond_order_id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, side, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status, flag, user_name)
					SELECT id, symbol_id, cd_code, participant_code, member_broker, order_size, order_entry, buy_vol, sell_vol, flag_id, side, price, exe_vol, exe_price, lot_check, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status, 'C', ? 
					FROM bond_orders
					WHERE id = ?
			");
			$audit_stmt->execute([ $username, $id ]);

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
				"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"> Order Deleted Successfully </div></div>'
			]);

		} catch(Exception $e) {
			if ($dbh->inTransaction()) {
				$dbh->rollBack();
			}
			error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

			echo json_encode([ 
				"status" => 2, 
				"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"> ' . htmlspecialchars($e->getMessage()) . ' </div></div>' 
			]);
		} 
		exit();
}
elseif (isset($_POST['submit_offer_rfq'])) {
	$cd_code = $_POST['cd_code'];
	$order_type = $_POST['order_type'];
	$symbol_id = $_POST['symbol_id'];
	$order_side = $_POST['side'];
	$offer_vol = $_POST['offer_vol'];

	$offer_price = ($order_side === 'S') ? $_POST['offer_price'] : 1;
	
	$accur_int = $_POST['accur_int'];
	$dirty_price = $_POST['dirty_price'];
	$ytm = $_POST['ytm_id'];
	$buyer_code = $_POST['buyer_code'];
	$usr_name = $username;

	header('Content-Type: application/json');
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		if ($offer_vol % 10 != 0) {
		    throw new Exception('Order should be multiple of 10');
		}

		$commission = 0;
		$total_amt = 0;
		if ($order_side === 'S') {
			$amout = $offer_price * $offer_vol;
			$commission = calculateCommission($amout);
			$gst = ($gst_register === 'Y') ? round($commission * 0.05, 2) : 0;
			$total_amt = round($amout - $commission - $gst, 2);
		}

		// no circuit breaker for bond trade
		// $cap_name = 'CAP';
		// $market_price = market_price($symbol_id); 
		// $cap = circuit($cap_name);
		// $cap_value = cap_compute($market_price, $cap);
		
		// $up = round($market_price + $cap_value, 2);
		// $dw = round($market_price - $cap_value, 2);

		// if ($offer_price > $up || $offer_price < $dw) {
		// 	throw new Exception('Price must be between ' . number_format($up, 2) . ' & ' . number_format($dw, 2));
		// }

        // check if any order existed for the RFQ
		$find_existing_order = check_bond_pending_orders($cd_code, $symbol_id, $order_side, $participant_code);
		if ($find_existing_order == 1) {
			throw new Exception('An order already exists. Consider updating it.');
		}

		// check volume
		$stmt = $dbh->prepare("SELECT volume FROM cds_holding WHERE cd_code = ? AND symbol_id = ?");
		$stmt->execute([$cd_code, $symbol_id]);
		$holding_vol = $stmt->fetchColumn();
		if ($holding_vol < $offer_vol) {
			throw new Exception('Insufficient shares');
		}

		$flag_id = date("ymdhis");
		$financestatus = 0;

		// $flag = match ($order_side) { 'B' => 3, 'S' => 2, default => 0, };
		$flag = 0;

		$text_wrd = match ($order_side) {
		    'B' => 'Buy',
		    'S' => 'Sell',
		    default => '',
		};

		$remarks = $text_wrd.' Order entry by user '.$usr_name.' of member '.$participant_code.' of volume '.$offer_vol.' @ Nu. '.$offer_price.'/share';

	  	// enter into order auit
		$order_audit = order_bond_audit($dbh, $cd_code, $participant_code, $usr_name, $offer_vol, $offer_vol, $symbol_id, $offer_price, $order_side, date('Y-m-d H:i:s'), $commission, $flag_id, $usr_name, $order_type, 'OPEN', $accur_int, $dirty_price, $ytm, $buyer_code);

		if ($order_audit != 1) {
			throw new Exception('Failed to submit order.');
		}

		// Insert into bond orders
		$stmt = $dbh->prepare("
			INSERT INTO bond_orders (cd_code, participant_code, order_entry, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker, sell_vol, buy_vol, acc_intrt, dirty_price, ytm, order_type, quoted_to) 
			VALUES (:cd_code, :participant_code, :order_entry, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker, :sell_vol, :buy_vol, :ac_in, :dir_price, :ytm, :or_type, :buyer_cd_code)
		");
		$sellVol = ($order_side === 'S') ? $offer_vol : 0;
		$buyVol  = ($order_side === 'B') ? $offer_vol : 0;

		$stmt->execute([
		    ':cd_code'         => $cd_code,
		    ':participant_code'=> $participant_code,
		    ':order_entry'     => $usr_name,
		    ':order_size'      => $offer_vol,
		    ':symbol_id'       => $symbol_id,
		    ':price'           => $offer_price,
		    ':side'            => $order_side,
		    ':commis_amt'      => $commission,
		    ':flag_id'         => $flag_id,
		    ':member_broker'   => $usr_name,
		    ':sell_vol'        => $sellVol,
		    ':buy_vol'         => $buyVol,
		    ':ac_in'           => $accur_int,
		    ':dir_price'       => $dirty_price,
		    ':ytm'             => $ytm,
		    ':or_type'         => $order_type,
		    ':buyer_cd_code'   => $buyer_code,
		]);

		// insert into bbo finance
		$stmt = $dbh->prepare("
				INSERT INTO bbo_finance (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount, symbol_id) 
				VALUES (:cd_code, :user_name, :remarks, :flag, :institution_id, :flag_id, :financestatus, :amount, :sy_id)
		");

		// Compute amount in PHP (faster & clearer than SQL IF)
		$finalAmount = ($order_side === 'S') ? $total_amt : -$total_amt;

		$stmt->execute([
		    ':cd_code'         => $cd_code,
		    ':user_name'       => $usr_name,
		    ':remarks'         => $remarks,
		    ':flag'            => $flag,
		    ':institution_id'  => $institution_id,
		    ':flag_id'         => $flag_id,
		    ':financestatus'   => $financestatus,
		    ':amount'          => $finalAmount,
		    ':sy_id'           => $symbol_id,
		]);

		if ($order_side == 'S') {
				$cds_upd_stmt = $dbh->prepare("
					UPDATE cds_holding 
				    SET volume = volume - :vol, pending_out_vol = pending_out_vol + :vol 
				    WHERE cd_code = :cdcode AND symbol_id = :sy_id
				");
				$cds_upd_stmt->bindParam(':vol', $offer_vol);
				$cds_upd_stmt->bindParam(':cdcode', $cd_code);
				$cds_upd_stmt->bindParam(':sy_id', $symbol_id);
				$cds_upd_stmt->execute();
		}

		$dbh->commit();
		$dbh = null;

		echo json_encode([
		    "message" => 'Order placed successfully'
		]);
	} catch (Exception $e) {
		if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
		echo json_encode([
		    "message" => htmlspecialchars($e->getMessage()),
		]);
	}
	exit();
}
elseif (isset($_POST['execute_offer_rfq'])) {
	// This block can be implemented similarly to the order execution logic, ensuring to handle all necessary checks and updates for both buyer and seller.
	header('Content-Type: application/json');
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		$buyer_cdcode  = trim($_POST['buyer_cdcode']);
	  	$seller_cdcode = trim($_POST['seller_cdcode']);
	  	$symbol_id     = trim($_POST['symbol_id']);
	  	$flag_id       = trim($_POST['flag_id']);
	  	$sell_vol      = (int)$_POST['sell_vol'];

	  	// error_log(print_r($_POST, true));

	  	if ($holiday || !$market_open || in_array($dayOfWeek, [0, 6])) {
		    throw new Exception('Market Closed.');
		}

		if ($sell_vol % 10 != 0) {
			throw new Exception('Order should be multiple of 10');
		}

		if (empty($buyer_cdcode) || empty($seller_cdcode)) {
			throw new Exception('Both the buyer and seller CD code are required.');
		}

		/*
	    |--------------------------------------------------------------------------
	    | FETCH BUY ORDER
	    |--------------------------------------------------------------------------
	    */
	    $sql = "SELECT * FROM bond_orders WHERE cd_code = ? AND symbol_id = ? AND side = 'B' LIMIT 1 FOR UPDATE";
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
        $sql = "SELECT * FROM bond_orders WHERE cd_code = ? AND symbol_id = ? AND flag_id = ? AND side = 'S' LIMIT 1 FOR UPDATE";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$seller_cdcode, $symbol_id, $flag_id]);
        $sell = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sell) {
            throw new Exception("Seller order not found.");
        }

        // get institue id
        /*$insti_stmt = $dbh->prepare("SELECT participant_code, institution_id FROM adm_participants WHERE participant_code IN (?, ?)");
		$insti_stmt->execute([$buy['participant_code'], $sell['participant_code']]);
		$institutes = array_column($insti_stmt->fetchAll(PDO::FETCH_ASSOC), 'institution_id', 'participant_code');
		$buy_institute_id  = $institutes[$buy['participant_code']]  ?? null;
		$sell_institute_id = $institutes[$sell['participant_code']] ?? null;*/

		$insti_stmt = $dbh->prepare("
		    SELECT a.participant_code, a.institution_id, b.gst_register
		    FROM adm_participants a
		    JOIN adm_institution b ON a.institution_id = b.institution_id
		    WHERE a.participant_code IN (?, ?)
		");
		$insti_stmt->execute([$buy['participant_code'], $sell['participant_code']]);
		$rows = $insti_stmt->fetchAll(PDO::FETCH_ASSOC);

		$institutes = array_column($rows, null, 'participant_code');

		$buy_institute_id  = $institutes[$buy['participant_code']]['institution_id'] ?? null;
		$sell_institute_id = $institutes[$sell['participant_code']]['institution_id'] ?? null;

		$buy_gst  = $institutes[$buy['participant_code']]['gst_register'] ?? null;
		$sell_gst = $institutes[$sell['participant_code']]['gst_register'] ?? null;

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
        // BUY statement
		$upd_buy = $dbh->prepare("UPDATE bond_orders SET order_size = ?, buy_vol = ?, status = ?, exe_vol = exe_vol + ?, exe_price = ?, lot_check = lot_check + ? WHERE id = ?");
		// SELL statement
		$upd_sell = $dbh->prepare("UPDATE bond_orders SET order_size = ?, sell_vol = ?, status = ?, exe_vol = exe_vol + ?, exe_price = ?, lot_check = lot_check + ? WHERE id = ?");

		$executeUpdate = function($stmt, $orderId, $balance, $status) use ($exec_vol, $exec_price) {
		    $stmt->execute([
		        $balance,
		        $balance,
		        $status,
		        $exec_vol,
		        $exec_price,
		        $exec_vol,
		        $orderId
		    ]);
		};
		// BUY
		$executeUpdate($upd_buy, $buy['id'], $buy_balance, $buy_status);
		// SELL
		$executeUpdate($upd_sell, $sell['id'], $sell_balance, $sell_status);

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
            INSERT INTO bond_executed_orders (cd_code, participant_code, sub_user, member_broker, order_date, symbol_id, order_exe_price, lot_size_execute, status, side, lot_check, flag_id, dirty_price, accur_rate, ytm, order_type)
            VALUES (:cd_code, :participant_code, :sub_user, :member_broker, :order_date, :symbol_id, :order_exe_price, :lot_size_execute, 0, :side, :lot_check, :flag_id, :dirty_price, :accur_rate, :ytm, :ord_typ)
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
                ':ytm'              => $exec_ytm,
                ':ord_typ'          => $order['order_type'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | FINANCE ENTRIES
        |--------------------------------------------------------------------------
        */
        $exec_amount = $exec_price * $exec_vol;
        $comm_fee 	 = calculateCommission($exec_amount);
        $buy_gst_fee     = ($buy_gst == 'Y') ? round($comm_fee * 0.05, 2) : 0;
        $sell_gst_fee     = ($sell_gst == 'Y') ? round($comm_fee * 0.05, 2) : 0;

        $financeSql = "
            INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id, symbol_id)
            VALUES (:cd_code, :amount, :user_name, :remarks, :flag, :institution_id, :flag_id, :sym_id)
        ";
        $financeStmt = $dbh->prepare($financeSql);

        /*
        |--------------------------------------------------------------------------
        | HELPER FUNCTION
        |--------------------------------------------------------------------------
        */
        $insertFinance = function($cd_code, $amount, $user_name, $remarks, $flag, $flag_id, $institution_id, $symbol_id) use ($financeStmt) {
					$financeStmt->execute([
			            ':cd_code'        => $cd_code,
			            ':amount'         => $amount,
			            ':user_name'      => $user_name,
			            ':remarks'        => $remarks,
			            ':flag'           => $flag,
			            ':institution_id' => $institution_id,
			            ':flag_id'        => $flag_id,
			            ':sym_id'         => $symbol_id,
			        ]);
        };

        /*
        |--------------------------------------------------------------------------
        | BUYER FINANCE
        |--------------------------------------------------------------------------
        */
        // BUY AMOUNT
        $insertFinance($buyer_cdcode, -abs($exec_amount), $buy['member_broker'], "Bond purchase amount for {$exec_vol} units @ Nu. {$exec_price}", 3, $buy['flag_id'], $buy_institute_id, $symbol_id);
        // BUY COMMISSION
        $insertFinance($buyer_cdcode, -abs($comm_fee), $buy['member_broker'], "Commission for {$exec_vol} units @ Nu. {$exec_price}", 4, $buy['flag_id'], $buy_institute_id, $symbol_id);
        // BUY GST
        if ($buy_gst_fee > 0) {
            $insertFinance($buyer_cdcode, -abs($buy_gst_fee), $buy['member_broker'], "GST for {$exec_vol} units @ Nu. {$exec_price}", 5, $buy['flag_id'], $buy_institute_id, $symbol_id);
        }

        /*
        |--------------------------------------------------------------------------
        | SELLER FINANCE
        |--------------------------------------------------------------------------
        */
        // SELL AMOUNT
        $insertFinance($seller_cdcode, abs($exec_amount), $sell['member_broker'], "Bond sell amount for {$exec_vol} units @ Nu. {$exec_price}", 2, $sell['flag_id'], $sell_institute_id, $symbol_id);
        // SELL COMMISSION
        $insertFinance($seller_cdcode, -abs($comm_fee), $sell['member_broker'], "Commission for {$exec_vol} units @ Nu. {$exec_price}", 4, $sell['flag_id'], $sell_institute_id, $symbol_id);
        // SELL GST
        if ($sell_gst_fee > 0) {
            $insertFinance($seller_cdcode, -abs($sell_gst_fee), $sell['member_broker'], "GST for {$exec_vol} units @ Nu. {$exec_price}", 5, $sell['flag_id'], $sell_institute_id, $symbol_id);
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE FULLY EXECUTED ORDERS
        |--------------------------------------------------------------------------
        */
        $deleteStmt = $dbh->prepare("DELETE FROM bond_orders WHERE id = ?");
        $delete_bbo = $dbh->prepare("DELETE FROM bbo_finance WHERE cd_code = ? AND flag_id = ? AND flag = 0");
        if ($buy_balance == 0) {
            $deleteStmt->execute([$buy['id']]);
            $delete_bbo->execute([$buyer_cdcode, $buy['flag_id']]);
        }
        if ($sell_balance == 0) {
            $deleteStmt->execute([$sell['id']]);
            $delete_bbo->execute([$seller_cdcode, $sell['flag_id']]);
        }

        /*
				|--------------------------------------------------------------------------
        | UPDATE PRICE IN MARKET PRICE TABLE
        |--------------------------------------------------------------------------
        */
        // Lock existing record if present
	    $stmt = $dbh->prepare("SELECT symbol_id, exec_price, exec_qty, created_at FROM bond_trade_prices WHERE symbol_id = ? FOR UPDATE");
	    $stmt->execute([$symbol_id]);
	    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
	    if ($existing) {
	        /*
	        |--------------------------------------------------------------------------
	        | INSERT CURRENT RECORD INTO HISTORY TABLE
	        |--------------------------------------------------------------------------
	        */
	        $history = $dbh->prepare("
	            INSERT INTO bond_price_histories (symbol_id, exec_price, exec_qty, last_price, last_qty, last_date, created_at)
	            SELECT symbol_id, exec_price, exec_qty, last_price, last_qty, last_date, created_at
	            FROM bond_trade_prices WHERE symbol_id = ?
	        ");
	        $history->execute([$symbol_id]);
	        /*
	        |--------------------------------------------------------------------------
	        | UPDATE MARKET PRICE
	        |--------------------------------------------------------------------------
	        */
	        $update = $dbh->prepare("UPDATE bond_trade_prices SET last_price = exec_price, last_qty = exec_qty, last_date = created_at, exec_price = ?, exec_qty = ?, created_at = NOW() WHERE symbol_id = ?");
	        $update->execute([$exec_price, $exec_vol, $symbol_id]);
	    } else {
	        /*
	        |--------------------------------------------------------------------------
	        | INSERT NEW MARKET PRICE
	        |--------------------------------------------------------------------------
	        */
	        $insert = $dbh->prepare("INSERT INTO bond_trade_prices (symbol_id, exec_price, exec_qty, created_at) VALUES (?, ?, ?, NOW())");
	        $insert->execute([$symbol_id, $exec_price, $exec_vol]);
	    }

		$dbh->commit();
		echo json_encode([
			"success" => true,
			"message" => "RFQ order executed successfully."
		]);
	} catch (\Exception $e) {
		if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }
    error_log("Exception => " . $e->getMessage() . ", line => " . $e->getLine());
		http_response_code(400);
		echo json_encode([
			"success" => false,
		    "message" => htmlspecialchars($e->getMessage()),
		]);
	}
	exit();
}
else {
	echo "Function not found";
	exit;
}
?>