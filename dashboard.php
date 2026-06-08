<?php 
  date_default_timezone_set('Asia/Thimphu');
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
		.error {
			color:red;
			text-align: center;
			font-size: 20px;
		}
		
		.backgroud {
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
<div class="container">
	<div class="content-wrapper">
		<div class="row">
			<div class="col-sm-2">&nbsp;</div>
			<div class="col-sm-8">
				<a href="dashboard.php">
					<h3 style="text-align: center; color: #ffffff; font-size: 1.7em!important;" class="backgroud" id="container">GDIC BOND SUBCRIPTION</h3>
				</a> 
			</div>
			<div class="col-sm-12" style="color: #FFF; font-size:12px;">
				<div class='table-responsive-lg' >
					<?php
						$tableData = [
						    ['user_name' => 'NOT LIKE \'MEM%\'', 'type' => 'BOND', 'style' => '#087B30', 'label' => 'RSEB Online'],
						    ['user_name' => 'LIKE \'MEMRICB%\'', 'type' => 'BOND', 'style' => '#8691A7', 'label' => 'RICB'],
						    ['user_name' => 'LIKE \'MEMBNBL%\'', 'type' => 'BOND', 'style' => '#810987', 'label' => 'BNBL'],
						    ['user_name' => 'LIKE \'MEMBOBL%\'', 'type' => 'BOND', 'style' => '#840C46', 'label' => 'BOBL'],
						    ['user_name' => 'LIKE \'MEMBDBL%\'', 'type' => 'BOND', 'style' => '#A39229', 'label' => 'BDBL'],
						    ['user_name' => 'LIKE \'MEMDKLT%\'', 'type' => 'BOND', 'style' => '#8691A7', 'label' => 'DKBANK'],
						    /*['user_name' => 'LIKE \'MEMDSBP%\'', 'type' => 'B', 'style' => '#F07E12', 'label' => 'DSBP'],
						    ['user_name' => 'LIKE \'MEMLDSB%\'', 'type' => 'B', 'style' => '#138239', 'label' => 'LDSB'],
						    ['user_name' => 'LIKE \'MEMSERS%\'', 'type' => 'B', 'style' => '#1EA6A5', 'label' => 'SERS'],
						    ['user_name' => 'LIKE \'MEMBPCL%\'', 'type' => 'B', 'style' => '#CE1D08', 'label' => 'BPCL'],
						    ['user_name' => 'LIKE \'MEMRINS%\'', 'type' => 'B', 'style' => '#E38112', 'label' => 'RINS'],*/
						];

						echo "
						<table id='exampleffff' class='table table-bordered table-striped' style='font-size:14px;'>
			        <thead>
			            <tr style='font-weight:bold; background-color: #181F67;'>
			                <td>Channel</td>
			                <td>Symbol</td>
			                <td>Total Client</td>
			                <td>Face Value</td>
			                <td>Volume</td>
			                <td>Total Amount</td>
			            </tr>
			        </thead>
			        <tbody>";

						$totalVol = 0;
						$grandTotAmt = 0;
						$totBidder = 0;

						foreach ($tableData as $data) {
						    $sql = "SELECT r.symbol_id,
						                'GNBB001' AS symbol, r.face_value,
						                COUNT(DISTINCT r.cd_code) AS cd_code, 
						                SUM(r.order_size) AS vol, 
						                SUM(r.bid_price * r.order_size) AS tValue,
						                SUM(r.total_amount) AS totalAmt
						            FROM bond r 
						            WHERE r.user_name {$data['user_name']} AND r.symbol_id = 118 AND r.type = '{$data['type']}' 
						            GROUP BY r.symbol_id";

						    $save = $dbh->prepare($sql);
						    $save->execute();
						    
						    foreach ($save as $row) {
						        $totalVol += $row['vol'];
						        $grandTotAmt += $row['tValue'];
						        $totBidder += $row['cd_code'];

						        echo "
			        			<tr style='color: #FFFFFF; background-color: {$data['style']};'>
				                <td>{$data['label']}</td>
				                <td>{$row['symbol']}</td>
				                <td>{$row['cd_code']}</td>
				                <td><i>" . number_format($row['face_value'], 2) . "</i></td>
				                <td>" . number_format($row['vol']) . "</td>
				                <td>" . number_format($row['tValue'], 2) . "</td>
				            </tr>";
						    }
						}

						echo "
									<tr style='color:#FFFFFF; background-color:#110909;'>
						        <td colspan='2' style='text-align:right;'><strong>Total</strong></td>
						        <td>$totBidder</td>
						        <td></td>
						        <td>" . number_format($totalVol) . "</td>
						        <td>" . number_format($grandTotAmt, 2) . "</td>
						    </tr>
						  </tbody>
						</table>";
						?>
			</div>
		</div>
	</div>
</div>
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