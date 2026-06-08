<?php 
	include('CONNECTIONS/db.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>RSEB</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="images/icons/favicon.ico"/>

	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

	<style type="text/css">
		.error{
			color:red;
			text-align: center;
			font-size: 20px;
		}
		.backgroud{
			background-image:url('img/rseb_logo2.png');
			background-repeat: no-repeat;
			background-position: top center;
			opacity: 0.9;
		}
		#container{
			height: 123px;
			margin: 0 auto;
			padding-top: 64px;
		}
		.loadingmsg {
		    display: none;
		    position: fixed;
		    top: 0;
		    left: 0;
		    right: 0;
		    bottom: 0;
		    width: 100%;
		    background: rgba(0,0,0,0.75) url(img/300.gif) no-repeat center center;
		    z-index: 10000;
		    background-size: 180px 180px;
		  }
	</style>
</head>
<body style="background-color: #D6D6D7; background-image: url('img/banner.jpg'); color: #ffffff;">
<div id="loadingover" style="display: none;"><div id="loadingmsg" class="loadingmsg" style="display: none;"></div></div>
<?php 
  date_default_timezone_set('Asia/Thimphu');
  $dateselect=date("Y-m-d H:i:s");
  if('2021-06-15 09:00:00' > $dateselect){
  	echo "The Auction will open on : 2021-06-15 09:00:00";
  }
  else if('2021-07-15 17:00:00' < $dateselect){
  	echo "The Registration for this Auction has ended.";
  }
  else{
?>
<div class="container">
	<div class="content-wrapper">
		<div class="row">
			<div class="col-sm-2">&nbsp;</div>
			<div class="col-sm-8">
				<a href="dashboard.php">
					<h3 style="text-align: center; color: #ffffff;font-size: 1.7em!important;" class="backgroud" id="container">OFFER FOR SALE OF SHARES</h3>
				</a> 
			</div>
			<div class="col-sm-12" style="color: #FFF; font-size:12px;">
				<?php
				$sqlClient=$dbh->prepare("SELECT COUNT(DISTINCT r.cd_code) grandTotClient FROM rights_issue r");
				$sqlClient->execute();
				$res=$sqlClient->fetch();

				echo"
				<div class='table-responsive-lg' >
			    <table id='exampleffff' class='table table-bordered table-striped' style='font-size:14px;'>
			      	<thead>
				        <tr style='font-weight:bold;'>
				          <td>Agent</td>
				          <td>Symbol</td>
				          <td>Total Client</td>
				          <td>Volume</td>
				          <td>Value</td>
				          <td>Total Amount</td>
				        </tr>
			      	</thead>
			      	<tbody>";
			      	$totalVol=0;
			      	$grandTotAmt=0;
			      	$grandTotComm=0;
					    $sql="SELECT r.symbol_id,
									case 
										WHEN r.symbol_id=5 THEN 'BNB'
										WHEN r.symbol_id=18 THEN 'RICB'
									END symbol,
									COUNT(DISTINCT r.cd_code) cd_code, 
									ROUND(AVG(r.bid_price)) price,
									SUM(r.order_size) vol, 
									SUM(r.bid_price * r.order_size) tValue,
									SUM(r.total_amount) totalAmt,
									SUM((r.bid_price * r.order_size)*0.01) tolCom
									FROM rights_issue r 
									WHERE r.user_name NOT LIKE 'MEM%' AND r.user_name NOT LIKE 'CC%'
									GROUP BY r.symbol_id";
							$save = $dbh->prepare($sql);
							$save->execute();
							foreach ($save as $row){
								$totalVol=$totalVol+$row['vol'];
								$grandTotAmt=$grandTotAmt+$row['totalAmt'];
								
						   	echo"<tr style='color: #FFFFFF;'>";                           
		           	echo"<td rowspan=''>RSEB Online</td>";
		           	echo"<td>".$row['symbol']."</td>";
		           	echo"<td>".$row['cd_code']."</td>";
		           	echo"<td >".number_format($row['vol'])."</td>";
		           	echo"<td >".number_format($row['tValue'], 2)."</td>";
		           	echo"<td >".number_format($row['totalAmt'], 2)."</td>";
		           	
		           	echo"</tr>"; 
						}

						$sql="SELECT r.symbol_id,
									case 
										WHEN r.symbol_id=5 THEN 'BNB'
										WHEN r.symbol_id=18 THEN 'RICB'
									END symbol,
									COUNT(DISTINCT r.cd_code) cd_code, 
									ROUND(AVG(r.bid_price)) price,
									SUM(r.order_size) vol, 
									SUM(r.bid_price * r.order_size) tValue,
									SUM(r.total_amount) totalAmt,
									SUM((r.bid_price * r.order_size)*0.01) tolCom
									FROM rights_issue r 
									WHERE r.user_name LIKE 'MEM%'
									GROUP BY r.symbol_id";
							$save = $dbh->prepare($sql);
							$save->execute();
							foreach ($save as $row){
								$totalVol=$totalVol+$row['vol'];
								$grandTotAmt=$grandTotAmt+$row['totalAmt'];
								
						   	echo"<tr style='color: #FFFFFF;'>";                           
		           	echo"<td>Brokers</td>";
		           	echo"<td>".$row['symbol']."</td>";
		           	echo"<td>".$row['cd_code']."</td>";
		           	echo"<td >".number_format($row['vol'])."</td>";
		           	echo"</tr>"; 
						}

						$sql="SELECT r.symbol_id,
									case 
										WHEN r.symbol_id=5 THEN 'BNB'
										WHEN r.symbol_id=18 THEN 'RICB'
									END symbol,
									COUNT(DISTINCT r.cd_code) cd_code, 
									ROUND(AVG(r.bid_price)) price,
									SUM(r.order_size) vol, 
									SUM(r.bid_price * r.order_size) tValue,
									SUM(r.total_amount) totalAmt,
									SUM((r.bid_price * r.order_size)*0.01) tolCom
									FROM rights_issue r 
									WHERE r.user_name LIKE 'CC%'
									GROUP BY r.symbol_id";
							$save = $dbh->prepare($sql);
							$save->execute();
							foreach ($save as $row){
								$totalVol=$totalVol+$row['vol'];
								$grandTotAmt=$grandTotAmt+$row['totalAmt'];

						   	echo"<tr style='color: #FFFFFF;'>";
		           	echo"<td>Community Centers</td>";
		           	echo"<td>".$row['symbol']."</td>";
		           	echo"<td>".$row['cd_code']."</td>";
		           	echo"<td>".number_format($row['vol'])."</td>";
		           	echo"</tr>"; 
						}
						echo'
						<tr>
							<td colspan="2" style="text-align:right;"><strong>Total</strong>
							<p>Price Rang: (BNB: 33-200), (RICB: 70-200)</P>
							</td>
							<td>'.$res['grandTotClient'].'</td>
							<td>'.number_format($totalVol).'</td>
							<td></td>
							<td>'.number_format($grandTotAmt,2).'</td>
						</tr>';
     			echo"</tbody>
     			</table>"; 
				?>
			</div>
		</div>
	</div>
</div>
<?php 
}
?>
</body>

<script type="text/javascript">
	/*$(document).ready(function() {
	    $('#exampleffff').DataTable( {
	        "pagingType": "full_numbers"
	    });
	});*/
</script>

<script src="vendor/bootstrap/js/popper.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="vendor/select2/select2.min.js"></script>
<script src="vendor/main.js"></script>
</html>