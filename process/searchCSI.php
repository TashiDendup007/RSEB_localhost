
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<?php
date_default_timezone_set("Asia/Thimphu");
include('../CONNECTIONS/db.php');


if(isset($_POST["loadDetails"]))
{
	$cidNo = $_POST["cidNo"];

	echo'
		<table class="table table-striped table-bordered">
		<thead>
			<th>EMPID</th>
			<th>Price</th>
			<th>Volume</th>
			<th>Amount</th>
			<th>Symbol</th>
			<th>Phone</th>
			<th>Client Details</th>
		</thead>
		<tbody>
	';	

	$sql=$dbh->prepare("SELECT p.name, p.phone, p.details, p.symbol_id, p.cd_code
		FROM rights_issue_online_temp p 
		WHERE p.bfs_orderid='CSE' AND p.payment_status='PE' AND p.details LIKE '%$cidNo%'");
	$sql->execute();

	foreach ($sql as $val) {
		$sqlRightsIssue=$dbh->prepare("SELECT r.cd_code, r.symbol_id, r.order_size, r.bid_price, r.total_amount, r.user_name, r.order_date 
			FROM rights_issue r 
			WHERE r.cd_code=:cdCode AND r.symbol_id=:symId");
		$sqlRightsIssue->bindparam(':cdCode', $val['cd_code']);
		$sqlRightsIssue->bindparam(':symId', $val['symbol_id']);
		$sqlRightsIssue->execute();
		$res = $sqlRightsIssue->fetch();
		
		$sName='';
		if($val['symbol_id']==5){
			$sName='BNBL';
		}else{
			$sName='RICB';
		}
		echo'
		<tr>
			<td>'.$res['user_name'].'</td>
			<td>'.$res['bid_price'].'</td>
			<td>'.$res['order_size'].'</td>	
			<td>'.(($res['bid_price'] * $res['order_size'])+($res['bid_price'] * $res['order_size'])*0.01).'</td>
			<td>'.$sName.'</td>
			<td>'.$val['phone'].'</td>
			<td>'.$val['details'].'</td>
		</tr>
		';
	}
	echo'
	</tbody>
	</table>
	<a href="shareAuctionCSI.php">
		<button type="button" class="btn btn-primary btn-lg btn-block">Go Back</button>
	</a>
	';
}
else{
	echo' <h2><span class="text-center"> No orders placed for this client!!! </span></h2>';
	die();
}
?>