<?php  
include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');
include('../../Functions/f.php'); 
date_default_timezone_set("Asia/Thimphu");

$check = $dbh->prepare('SELECT a.institution_id from adm_institution a, adm_participants b,users c WHERE c.participant_code=b.participant_code and b.institution_id = a.institution_id AND c.username=:un');
$check->bindParam(':un', $username);
$check->execute();
$res = $check->fetch();
$institution_id = $res['institution_id'];
$sys_date_time = date("Y-m-d H:i:s"); 

if (isset($_POST['save__rights__auction'])) {
		$cdcode = $_POST['cdcode'];
		$cid = $_POST['cid'];
		$symbol_id = $_POST['symbol_id'];
		$type = $_POST['options'];
		$bidPrice = $_POST['bidPrice'];
		$bidVol = $_POST['bidVol'];
		$total_bid_amount = $_POST['total_bid_amount'];
		$closing_date = $_POST['closing_date'];
		$status = 0;
		$message = '';

		if ($sys_date_time > $closing_date) {
			$message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Rights Auction Has Ended.</div>';
			echo $message;
			exit();
		}

		$stmt = $dbh->prepare("SELECT 1 FROM rights_issue r WHERE r.symbol_id = ? AND r.`type` = ? AND r.cid_no = ? AND r.`status` = 0");
		$stmt->execute([$symbol_id, $type, $cid]);
		$row = $stmt->fetchColumn();

		if ($row) {
			$message = '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Rights Bid already existed.</div>';
			echo $message;
			exit();
		}

		if ($bidPrice < 11 || round($bidPrice * 100) % 5 != 0 || $bidPrice > 50.27) {
			$message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button> 
				Bid price cannot be less than Nu. 11 <br>
				Bid price cannot be greater than Nu. 50.27 <br>
				Bid price must be multiple of 0.05
			</div>';
			echo $message;
			exit();
		}

		if (($bidVol < 100) || ($bidVol % 10 != 0 || filter_var($bidVol, FILTER_VALIDATE_INT) === false || $bidVol <= 0)) {
			$message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
				Volume cannot be less than 100<br>
				Volume must be multiple of 10<br>
				Bid volume must be a natural number (positive integer).
			</div>';
			echo $message;
			exit();
		}

		// get total_amount
		$stmt = $dbh->prepare("SELECT SUM(r.amount) AS total_amt FROM rights_finance r WHERE r.cd_code = ?");
		$stmt->execute([$cdcode]);
		$cash = $stmt->fetchColumn();
		if ($cash < $total_bid_amount) {
			$message = '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button> No enough cash.</div>';
			echo $message;
			exit();
		}

		try {
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbh->beginTransaction();

			$save = $dbh->prepare("INSERT INTO rights_issue(type, cd_code, order_size, symbol_id, bid_price, rights_issued, total_amount, user_name, cid_no, status) VALUES(?, ?, ?, ?, ?, 0, ?, ?, ?, ?)");
			$save->execute([$type, $cdcode, $bidVol, $symbol_id, $bidPrice, $total_bid_amount, $username, $cid, $status]);

			$message = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully Submitted.</div>';

			$dbh->commit();
			$dbh = null;

		} catch(PDOException $e) {
			$dbh->rollBack();
			error_log("Error ==> " . $e->getMessage());
			$message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an exception. Please contact RSEB support.</div>';
		}
		echo $message;
		exit();
}

?>