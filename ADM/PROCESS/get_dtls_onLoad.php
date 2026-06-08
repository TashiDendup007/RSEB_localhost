<?php
	include('../FILES/sessionStartFile_admin.php');
	include ('../../CONNECTIONS/db.php');

	if (isset($_POST['get_count_dtls'])) {
		$stmt = $dbh->prepare("SELECT count(*) FROM symbol s WHERE s.security_type='OS'");
		$stmt->execute();
		$symbol_count = $stmt->fetchColumn();

		$stmt1 = $dbh->prepare("SELECT count(*) FROM users");
		$stmt1->execute();
		$user_register_count = $stmt1->fetchColumn();

		$stmt2 = $dbh->prepare("SELECT count(*) FROM users u WHERE u.role_id=4");
		$stmt2->execute();
		$terminal_user_count = $stmt2->fetchColumn();

		$response = array(
		  'symbol_count' => $symbol_count,
		  'user_register_count' => $user_register_count,
		  'terminal_user_count' => $terminal_user_count,
		);
		echo json_encode($response);

		$dbh = null;
		die();
	} 
	elseif (isset($_POST['get_dtls_charts'])) {
		// get traded volume year wise
		// $data = array();
		$chart_data = array();
		$stmt = $dbh->prepare("
			SELECT 
				trade_year, 
				SUM(case when CHAR_LENGTH(sub_user) = 10 then lot_size_execute ELSE 0 END) AS trade_broker,
				SUM(case when CHAR_LENGTH(sub_user) = 18 then lot_size_execute ELSE 0 END) AS trade_online
			FROM (
			    SELECT 
			    EXTRACT(YEAR FROM order_date) AS trade_year,
			    sub_user,
			    lot_size_execute
			    FROM executed_orders
			    WHERE side = 'B'
			) AS subquery
			GROUP BY trade_year
			ORDER BY trade_year ASC
		");
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
		    $chart_data[] = array(
		      'year' => $row["trade_year"],
		      'broker' => $row["trade_broker"],
		      'online' => $row["trade_online"]
		    );
		}

		// foreach ($rows as $row) {
		// 	$chart_data .= "{ year:'".$row["trade_year"]."', broker:".$row["trade_broker"].", online:".$row["trade_online"]." }, ";
		// }
    	// $chart_data = substr($chart_data, 0, -2);

  		// while($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
    	// 	$data[] = $row;
    	// }

	  	// get acitve trading user
	  	$active_user = array();
	  	$sql = $dbh->prepare("SELECT 
				COUNT(DISTINCT e.sub_user) AS sub_user, YEAR(e.order_date) AS year_trade
			FROM executed_orders e 
			WHERE 
				CHAR_LENGTH(e.sub_user) = 18
			GROUP BY 
				YEAR(e.order_date)");
		$sql->execute();
		$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
		    $active_user[] = array(
		      'year' => $row["year_trade"],
		      'user' => $row["sub_user"]
		    );
		}

		// get daily trade of symbols
		$volume = array();
		$select = $dbh->prepare("SELECT s.symbol, SUM(e.lot_size_execute) AS volume, DATE(e.order_date) AS trade_date
			FROM executed_orders e 
			JOIN symbol s ON e.symbol_id = s.symbol_id
			WHERE 
				e.side = 'B'
			GROUP BY 
				s.symbol_id, trade_date");
		$select->execute();
		$result = $select->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $res) {
		    $volume[] = array(
		      'symbol' => $res["symbol"],
		      'volume' => $res["volume"],
		      'trade_date' => $res["trade_date"]
		    );
		}
			
		$response = array(
		  'chart_data' => $chart_data,
		  'active_user' => $active_user,
		  'daily_trade' => $volume,
		);
		echo json_encode($response);

		$dbh = null;
		die();
	} 
	elseif(isset($_POST['get_symbol_list'])) {
		echo'
		<div class="modal-dialog modal-lg">
		    <div class="modal-content">
		        <div class="modal-header">
		          <button type="button" class="close" data-dismiss="modal">&times;</button>
		          <h4 class="modal-title">Symbol List</h4>
		        </div>
		        <div class="modal-body">
		          <div class="box-body">
		            <div class="row table-responsive">
		              	<table class="table table-bordered" id="symbolListTable">
						  	<thead>
						    	<tr>
						      		<th scope="col">Symbol Id</th>
						      		<th scope="col">Symbol</th>
						      		<th scope="col">Name</th>
						      		<th scope="col">Paid Up Share</th>
						      		<th scope="col">Sector</th>
						      		<th scope="col">Status</th>
						      		<th scope="col">Trs-Status</th>
					    		</tr>
						  	</thead>
						  	<tbody>';
						  	$sql = $dbh->prepare("SELECT symbol_id, symbol, name, paid_up_shares, sector, status, trsstatus FROM symbol s WHERE s.security_type='OS'");
							$sql->execute();
							$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
							foreach ($rows as $row) {
								echo'
							    <tr>
							      	<td>'.$row['symbol_id'].'</td>
							      	<td>'.$row['symbol'].'</td>
							      	<td>'.$row['name'].'</td>
							      	<td>'.number_format($row['paid_up_shares']).'</td>
							      	<td>'.$row['sector'].'</td>
							      	<td>'.$row['status'].'</td>
							      	<td>'.$row['trsstatus'].'</td>
							    </tr>';
							}
						    echo'
						  	</tbody>
						</table>
		            </div>
		          </div>
		        </div>
		        <div class="modal-footer">
		          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
		        </div>
			</div>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#symbolListTable").dataTable();
			});
		</script>';
	} 
	elseif(isset($_POST['get_terminaluser_list'])) {
		echo'
		<div class="modal-dialog modal-lg">
		    <div class="modal-content">
		        <div class="modal-header">
		          <button type="button" class="close" data-dismiss="modal">&times;</button>
		          <h4 class="modal-title">Terminal User List</h4>
		        </div>
		        <div class="modal-body">
		          <div class="box-body">
		            <div class="row table-responsive">
		              	<table class="table table-bordered" id="symbolListTable">
						  	<thead>
						    	<tr>
						      		<th scope="col">#</th>
						      		<th scope="col">CID</th>
						      		<th scope="col">CD Code</th>
						      		<th scope="col">Name</th>
						      		<th scope="col">Phone</th>
						      		<th scope="col">Email</th>
						      		<th scope="col">Particpate Code</th>
						      		<th scope="col">Status</th>
						      		<th scope="col">Created Date</th>
					    		</tr>
						  	</thead>
						  	<tbody>';
						  	$i=1;
						  	$sql = $dbh->prepare("SELECT cid, cd_code, name, phone, email, participant_code, status, created_at FROM users WHERE role_id=4");
							$sql->execute();
							$rows = $sql->fetchAll(PDO::FETCH_ASSOC);
							foreach ($rows as $row) {
								echo'
							    <tr>
							      	<td>'.$i.'</td>
							      	<td>'.$row['cid'].'</td>
							      	<td>'.$row['cd_code'].'</td>
							      	<td>'.$row['name'].'</td>
							      	<td>'.$row['phone'].'</td>
							      	<td>'.$row['email'].'</td>
							      	<td>'.$row['participant_code'].'</td>
							      	<td>'.$row['status'].'</td>
							      	<td>'.$row['created_at'].'</td>
							    </tr>';
							    $i++;
							}
						    echo'
						  	</tbody>
						</table>
		            </div>
		          </div>
		        </div>
		        <div class="modal-footer">
		          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
		        </div>
			</div>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#symbolListTable").dataTable();
			});
		</script>';
	} 
	else {
		echo 'Wrong Method';
		die();
	}

?>