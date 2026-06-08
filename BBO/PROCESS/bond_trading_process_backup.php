<?php 
date_default_timezone_set("Asia/Thimphu");
//include ('../../CONNECTIONS/function-sanitize.php');
include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php'); 
include_once 'Functions/bond_com_function.php';
// include ('../../CONNECTIONS/trading_hours.php');

$check = $dbh->prepare("SELECT a.institution_id, c.participant_code
			FROM users c
			JOIN adm_participants b ON c.participant_code = b.participant_code
			JOIN adm_institution a ON b.institution_id = a.institution_id
			WHERE c.username = ?
");
$check->execute([$username]);
$res = $check->fetch(PDO::FETCH_ASSOC);

$institution_id = $res['institution_id'];
$participant_code = $res['participant_code'];

if (isset($_POST['placing__bond__order'])) {
	/*error_log(print_r($_POST, true));
	exit();*/

	$cd_code = $_POST['cd_code'];
	$usr_name = $username;
	$vol = $_POST['volume'];
	$symbol_id = $_POST['symbol_id'];
	$price = $_POST['price'];
	$order_side = $_POST['side'];
	$financestatus = 0;

	$order_type = $_POST['order_type'];
	$accur_int = $_POST['accur_int'];
	$dirty_price = $_POST['dirty_price'];
	$ytm = $_POST['ytm_id'];
	// $broker_commis = 1;

	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		$amount = $price * $vol;
		$commission = calculateCommission($amount);
		error_log("commission -> {$commission}");
		$gst = round($commission * 0.05, 2);
		$total_amt = round($amount + $commission + $gst, 2);

		// check if order already exists
		$find_existing_order = check_bond_pending_orders($cd_code, $symbol_id, $order_side, $participant_code);
		if ($find_existing_order == 1) {
			throw new Exception('An order already exists. Consider updating it.');
		}

		// check available balance
		if ($order_side == 'B') {
		  	$stmt = $dbh->prepare("SELECT SUM(m.amount) AS total_amount FROM bbo_finance m WHERE m.cd_code = ? -- AND m.status = 1");
			$stmt->execute([$cd_code]);
			$avail__holding__amount = $stmt->fetchColumn();
			if ($total_amt > $avail__holding__amount) {
				$avail__holding__amount = isset($avail__holding__amount) ? $avail__holding__amount : 0;
				throw new Exception('Insufficient cash. Available: ' . number_format($avail__holding__amount, 2));
			}
	  	}

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

		/*$flag = match ($order_side) {
		    'B' => 3,
		    'S' => 2,
		    default => 0,
		};*/
		$flag = 0;

		$text_wrd = match ($order_side) {
		    'B' => 'Buy',
		    'S' => 'Sell',
		    default => '',
		};

		$remarks = $text_wrd.' Order entry by user '.$usr_name.' of member '.$participant_code.' of volume '.$vol.' @ Nu. '.$price.'/share';

	  	// enter into order auit
		$order_audit = order_bond_audit($dbh, $cd_code, $participant_code, $usr_name, $vol, $vol, $symbol_id, $price, $order_side, $broker_commis, $flag_id, $usr_name, $order_type, $accur_int, $dirty_price, $ytm, '');

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
				INSERT INTO bbo_finance (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount) 
				VALUES (:cd_code, :user_name, :remarks, :flag, :institution_id, :flag_id, :financestatus, :amount)
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
		]);


		if ($order_side == 'S') {
				$cds_update_stmt = $dbh->prepare("UPDATE cds_holding 
				    SET volume = volume - :vol, pending_out_vol = pending_out_vol + :vol 
				    WHERE cd_code = :cdcode AND symbol_id = :sy_id"
				);
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
	    error_log("Transaction failed ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
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
	$new_price = $_POST['new_price'];
	$new_order_vol = $_POST['new_vol'];
	
	$ex_price = $_POST['ex_price'];
	$ex_order_vol = $_POST['ex_vol'];
	$broker_commis = 1;

	header('Content-Type: application/json');
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		if ($new_order_vol % 10 != 0) {
		    throw new Exception('Order should be multiple of 10');
		}

		$cap_name = 'CAP';
		$market_price = market_price($symbol_id); 
		$cap = circuit($cap_name);
		$cap_value = cap_compute($market_price, $cap);
		
		$up = round($market_price + $cap_value, 2);
		$dw = round($market_price - $cap_value, 2);

		if ($new_price > $up || $new_price < $dw) {
			throw new Exception('Price must be between ' . number_format($up, 2) . ' & ' . number_format($dw, 2));
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

		if ($ex_price == $new_price && $ex_order_vol == $new_order_vol) {
			throw new Exception('No change in price and volume.');
		}

		// Amount Calculation
		$amout = $new_price * $new_order_vol;
		$commission = $amout * $broker_commis / 100;
		$gst = $commission * 0.05;
		$total_amt = $amout + $commission;
		$final_total_amt = ($gst_register === 'Y') ? ($total_amt + $gst) : $total_amt;

		// check amount of the buyer
		if ($side === 'B') {
			$stmt = $dbh->prepare("SELECT SUM(m.amount) AS total_amount FROM bbo_finance m WHERE m.cd_code = ? AND m.flag_id != ? -- AND m.status = 1");
			$stmt->execute([$cd_code, $flag_id]);
			$avail__holding__amount = $stmt->fetchColumn();
			if ($avail__holding__amount < $final_total_amt) {
				$avail__holding__amount = isset($avail__holding__amount) ? $avail__holding__amount : 0;
				throw new Exception('Insufficient cash. Available: ' . number_format($avail__holding__amount, 2));
			}
		}

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
			$update->execute([
				$new_pending_out, $new__holding, $cd_code, $symbol_id
			]);
		}

		$term = ($side === 'B') ? 'BUY' : 'SELL';
		$remarks = $term.' Order entry by user '.$username.' of member '.$participant_code.', of volume '.$new_order_vol.' @ Nu. '.$new_price.'/share';

		// update bbo finance
		$final_total_amt = ($side === 'B') ? ($final_total_amt * -1) : $final_total_amt;
		$bbo_update = $dbh->prepare("UPDATE bbo_finance SET remarks = ?, amount = ? WHERE flag_id = ?");
		$bbo_update->execute([$remarks, $final_total_amt, $flag_id]);

		// update bond order audit
		$order_audit = order_bond_audit($dbh, $cd_code, $participant_code, $username, $new_order_vol, $new_order_vol, $symbol_id, $new_price, $side, $commission, $flag_id, $username, $order_type, $accured_int, $dirty_price, $ytm);

		// update order 
		$order_update = $dbh->prepare("
				UPDATE bond_orders 
	            SET sell_vol = CASE WHEN side = 'S' THEN :new_sell_vol ELSE sell_vol END,
	                buy_vol = CASE WHEN side = 'B' THEN :new_buy_vol ELSE buy_vol END,
	                order_size = CASE WHEN side = 'S' THEN :new_sell_vol ELSE :new_buy_vol END,
	                price = :new_price,
	                commis_amt = :new_commis_amt
	            WHERE id = :id
      	");
		$order_update->execute([':new_sell_vol' => $new_order_vol, ':new_buy_vol' => $new_order_vol, ':new_price' => $new_price, ':new_commis_amt' => $commission, ':id' => $order_id]);

		$dbh->commit();
		$dbh = null;
		echo json_encode([
		    "message" => '
		        <div class="col-lg-12 col-xs-12">
			        <div class="alert alert-success alert-dismissible">
			            Updated order Successfully.
			        </div>
			    </div>
		    '
		]);
	} catch (Exception $e) {
		if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
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