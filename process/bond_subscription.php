<?php  
	include('../CONNECTIONS/db.php');

	if (isset($_POST['get__bond__dtls'])) {
		$id = $_POST['id'];
		$type = $_POST['type'];

		$sql = "SELECT d.bfs_order_no, d.bfs_code, d.bfs_msg_type, d.amount, b.cd_code, b.order_size, b.bid_price, b.buy_vol, b.face_value, b.total_amount, b.cid_no, d.phone, d.email, d.details, d.name, d.created_at, d.vol_applied, d.price 
				FROM bond_ipo_temp_dtls d 
				LEFT JOIN bond b ON d.cd_code = b.cd_code
				WHERE ";
		if ($type == 'orderno_type') {
			$sql .= "d.bfs_order_no = ?";
		} else {
			$sql .= "d.name = ?";
		}

		$stmt = $dbh->prepare($sql);
		$stmt->execute([$id]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		echo'
		<div class="table-responsive mt-4">
	        <table class="table table-bordered table-striped">
	            <thead class="table-dark">
	                <tr>
	                    <th>Order No.</th>
	                    <th>BFS Code</th>
	                    <th>Unit Applied</th>
	                    <th>Bid Price</th>
	                    <th>BFS Amount</th>
	                    <th>CD Code</th>
	                    <th>Unit Subscribed</th>
	                    <th>Total Amount</th>
	                    <th>CID</th>
	                    <th>Date</th>
	                    <th>Phone</th>
	                    <th>Email</th>
	                    <th>Details</th>
	                </tr>
	            </thead>
	            <tbody>';
				foreach ($rows as $key => $value) {
					echo'
					<tr>
	                    <td>'.$value['bfs_order_no'].'</td>
	                    <td>'.$value['bfs_code'].'</td>
	                    <td>'.$value['price'].'</td>
	                    <td>'.$value['vol_applied'].'</td>
	                    <td>'.number_format($value['amount']).'</td>
	                    
	                    <td style="color: red;">'.$value['cd_code'].'</td>
	                    <td style="color: red;">'.$value['order_size'].'</td>
	                    <td style="color: red;">'.number_format(isset($value['total_amount']) ? $value['total_amount'] : 0).'</td>
	                    <td style="color: red;">'.$value['cid_no'].'</td>

	                    <td>'.$value['created_at'].'</td>
	                    <td>'.$value['phone'].'</td>
	                    <td>'.$value['email'].'</td>
	                    <td>'.$value['details'].'</td>
	                </tr>';
				}
				echo'
	            </tbody>
	        </table>
	    </div>';
	    exit;
	}
	elseif (isset($_POST['submit__successful__transaction'])) {
		$bfs_order_no = $_POST['bfs_order_no'];

		// get cid no
		$get = $dbh->prepare("SELECT name FROM bond_ipo_temp_dtls WHERE bfs_order_no = ? AND bfs_code = '00'");
		$get->execute([$bfs_order_no]);
		$cid_no = $get->fetchColumn();

		if (empty($cid_no)) {
			echo'Payment is not successful of the given order no.';
			exit;
		}

		// check if order exist
		$check = $dbh->prepare("SELECT 1 FROM bond WHERE cid_no = ? AND symbol_id = 118");
		$check->execute([$cid_no]);
		$row = $check->fetchColumn();

		if ($row) {
			echo'Order Already Existed in Bond.';
		} else {
			try {
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbh->beginTransaction();

				$bid_type = 'BOND';
				$status = 0;

				// fetch details from bond details table and insert into bond table
				$stmt = $dbh->prepare("SELECT 
							d.bfs_order_no, d.bfs_code, d.cd_code, d.vol_applied, d.price, d.amount, d.email, d.phone, d.symbol_id, d.name AS cid_no, d.created_at
							FROM bond_ipo_temp_dtls d 
							WHERE d.bfs_order_no = ?
				");
				$stmt->execute([$bfs_order_no]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);

				$total_amt = $row['vol_applied'] * $row['price'];
				$save = $dbh->prepare("INSERT INTO bond(type, cd_code, order_size, symbol_id, bid_price, buy_vol, face_value, total_amount, user_name, cid_no, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	            $save->execute([$bid_type, $row['cd_code'], $row['vol_applied'], $row['symbol_id'], $row['price'], $row['vol_applied'], $row['price'], $total_amt, $row['cid_no'], $row['cid_no'], $status]);

	            $dbh->commit();

	            $symbol = 'GNBB001';
	            $vol = $row['vol_applied'];
	            $email = $row['email'];
	            $phone = $row['phone'];

	            // send sms notification
                $token = 'rsebsms@2021#Dec!';
                $url = "https://cms.rsebl.org.bt/api/v1/rseb_sms_gateway.php";
                $message = 'Subscription Confirmed: You have successfully subscribed to '.$vol.' units of GNBB001 Bond. Thank you for participating.'; // Ensure message is set

                // Initialize cURL
                $curl = curl_init($url);

                // Set cURL options
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_POSTFIELDS => http_build_query([
                        'phoneNo' => $phone,
                        'message' => $message,
                        'token' => $token
                    ]),
                ]);
                // Execute cURL request
                $curl_response = curl_exec($curl);

	            // send email notification
	            include("bond_confirmation_mail.php");

	            echo "Done Transaction";
			} catch (Exception $e) {
				$dbh->rollBack();
				echo "Exception occurred.";
			}
		}
		exit;
	}
	if (isset($_POST['get__auction__dtls'])) {
		$id = $_POST['id'];
		$type = $_POST['type'];

		$sql = "SELECT d.bfs_orderid, d.bfs_code, d.type, d.amount, b.cd_code, b.order_size, b.bid_price, b.buy_vol, b.face_value, b.total_amount, b.cid_no, d.phone, d.email, d.details, d.name, d.dateentry, d.vol_applied, d.price 
				FROM rights_issue_online_temp d 
				LEFT JOIN rights_issue b ON d.cd_code = b.cd_code AND b.symbol_id = d.symbol_id
				WHERE d.symbol_id = 20 
				AND b.status = 0
				AND b.type = 'SA' AND ";
		if ($type == 'orderno_type') {
			$sql .= "d.bfs_orderid = ?";
		} else {
			$sql .= "d.name = ?";
		}

		$stmt = $dbh->prepare($sql);
		$stmt->execute([$id]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		echo'
		<div class="table-responsive mt-4">
	        <table class="table table-bordered table-striped">
	            <thead class="table-dark">
	                <tr>
	                    <th>Order No.</th>
	                    <th>BFS Code</th>
	                    <th>Unit Applied</th>
	                    <th>Price</th>
	                    <th>BFS Amount</th>
	                    <th>CD Code</th>
	                    <th>Bid Price</th>
	                    <th>Unit Subscribed</th>
	                    <th>Total Amount</th>
	                    <th>CID</th>
	                    <th>Date</th>
	                    <th>Phone</th>
	                    <th>Email</th>
	                    <th>Details</th>
	                </tr>
	            </thead>
	            <tbody>';
				foreach ($rows as $key => $value) {
					echo'
					<tr>
	                    <td>'.$value['bfs_orderid'].'</td>
	                    <td>'.$value['bfs_code'].'</td>
	                    <td>'.$value['vol_applied'].'</td>
	                    <td>'.$value['price'].'</td>
	                    <td>'.$value['amount'].'</td>
	                    
	                    <td style="color: red;">'.$value['cd_code'].'</td>
	                    <td style="color: red;">'.$value['bid_price'].'</td>
	                    <td style="color: red;">'.$value['order_size'].'</td>
	                    <td style="color: red;">'.number_format(isset($value['total_amount']) ? $value['total_amount'] : 0).'</td>
	                    <td style="color: red;">'.$value['cid_no'].'</td>

	                    <td>'.$value['dateentry'].'</td>
	                    <td>'.$value['phone'].'</td>
	                    <td>'.$value['email'].'</td>
	                    <td>'.$value['details'].'</td>
	                </tr>';
				}
				echo'
	            </tbody>
	        </table>
	    </div>';
	    exit;
	}
	elseif (isset($_POST['submit__successful__transaction_auction'])) {
		$bfs_order_no = $_POST['bfs_order_no'];

		// get cid no
		$get = $dbh->prepare("SELECT name FROM rights_issue_online_temp WHERE bfs_orderid = ? AND bfs_code = '00'");
		$get->execute([$bfs_order_no]);
		$cid_no = $get->fetchColumn();

		if (empty($cid_no)) {
			echo'Payment is not successful of the given order no.';
			exit;
		}

		// check if order exist
		$check = $dbh->prepare("SELECT 1 FROM rights_issue WHERE cid_no = ? AND symbol_id = 20 and status = 0");
		$check->execute([$cid_no]);
		$row = $check->fetchColumn();

		if ($row) {
			echo'Order Already Existed in rights_issue.';
		} else {
			try {
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbh->beginTransaction();

				$bid_type = 'SA';
				$status = 0;

				// fetch details from bond details table and insert into bond table
				$stmt = $dbh->prepare("SELECT 
							d.bfs_orderid, d.bfs_code, d.cd_code, d.vol_applied, d.price, d.amount, d.email, d.phone, d.symbol_id, d.name AS cid_no, d.dateentry
							FROM rights_issue_online_temp d 
							WHERE d.bfs_orderid = ?
				");
				$stmt->execute([$bfs_order_no]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);

				$total_amt = ($row['vol_applied'] * $row['price']) + ($row['vol_applied'] * $row['price'] * 0.02);

				$save = $dbh->prepare("INSERT INTO rights_issue(type, cd_code, order_size, symbol_id, bid_price, buy_vol, face_value, total_amount, user_name, cid_no, status) VALUES (?, ?, ?, ?, ?, ?, 10, ?, ?, ?, ?)");
	            $save->execute([$bid_type, $row['cd_code'], $row['vol_applied'], $row['symbol_id'], $row['price'], $row['vol_applied'], $total_amt, $row['cid_no'], $row['cid_no'], $status]);

	            $dbh->commit();

	            $symbol = 'TBL';
	            $vol = $row['vol_applied'];
	            $email = $row['email'];
	            $phone = $row['phone'];

	            // send sms notification
                $token = 'rsebsms@2021#Dec!';
                $url = "https://cms.rsebl.org.bt/api/v1/rseb_sms_gateway.php";
                $message = 'Auction BID Confirmed: You have successfully place a bid of '.$vol.' vol of TBL. Thank you for participating.'; 

                // Initialize cURL
                $curl = curl_init($url);

                // Set cURL options
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_POSTFIELDS => http_build_query([
                        'phoneNo' => $phone,
                        'message' => $message,
                        'token' => $token
                    ]),
                ]);
                // Execute cURL request
                $curl_response = curl_exec($curl);

	            // send email notification
	            include("auctoin_bid_successful.php");

	            echo "Done Transaction";
			} catch (Exception $e) {
				$dbh->rollBack();
				echo "Exception occurred.";
			}
		}
		exit;
	}
?>