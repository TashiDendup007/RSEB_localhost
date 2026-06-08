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
				echo"
				<div class='table-responsive-lg' >
			    <table id='exampleffff' class='table table-bordered table-striped' style='font-size:14px;'>
			      	<thead>
				        <tr style='font-weight:bold;'>
				          <td>cd_code</td>
				          <td>Symbol</td>
				          <td>Prices</td>
				          <td>Volume</td>
				          <td>Value</td>
				          <td>Amount</td>
				          <td>Commission</td>
				          <td>username</td>
				          <td>Date</td>
				        </tr>
			      	</thead>
			      	<tbody>";

						$sql="SELECT r.symbol_id,
								r.cd_code, r.bid_price, r.order_size, (r.bid_price * r.order_size)t_Value, r.total_amount, ROUND((r.bid_price * r.order_size) * 0.01, 2) commission,
								r.user_name, r.order_date
								FROM rights_issue r 
								WHERE r.user_name LIKE 'MEMBPCL%'";
							$save = $dbh->prepare($sql);
							$save->execute();
							foreach ($save as $row){
						   	echo"<tr style='color: #FFFFFF;'>";                           
		           	echo"<td>".$row['cd_code']."</td>";
		           	echo"<td>".$row['symbol_id']."</td>";
		           	echo"<td ><i>".number_format($row['bid_price'], 2)."</i></td>";
		           	echo"<td >".number_format($row['order_size'], 2)."</td>";
		           	echo"<td >".number_format($row['t_Value'], 2)."</td>";
		           	echo"<td >".number_format($row['total_amount'], 2)."</td>";
		           	echo"<td >".number_format($row['commission'], 2)."</td>";
		           	echo"<td>".$row['user_name']."</td>";
		           	echo"<td>".$row['order_date']."</td>";
		           	echo"</tr>"; 
						}
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