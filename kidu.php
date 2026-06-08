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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

	<style type="text/css">
	.error{
		color:red;
		text-align: center;
		font-size: 20px;
	}
	.backgroud{
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
	.line{
		border-radius: 15px;
	}



	#barChart{
		border-radius: 10px;
	}
</style>
</head>
<body style="background-color: #D6D6D7; background-image: url('img/banner.jpg'); color: #ffffff;">
	
	<?php 
	date_default_timezone_set('Asia/Thimphu');
	$dateselect=date("Y-m-d H:i:s");
	if('2021-06-15 09:00:00' > $dateselect){
		echo "The Auction will open on : 2021-06-15 09:00:00";
	}
	else if('2022-07-15 17:00:00' < $dateselect){
		echo "The Registration for this Auction has ended.";
	}
	else{
		?>
		<div class="container">
			<div class="content-wrapper">
				<div class="row">
					<div class="col-sm-12">
						<h4 style="text-align: center; color: #ffffff;font-size: 1.7em!important;" class="backgroud" id="container">Dashboard</h4>
					</div>
					<div class="col-sm-12" style="color: #FFF; font-size:10px;">
						<?php
						$sqlClient=$dbh->prepare("SELECT COUNT(*) grandTotClient FROM rights_issue r WHERE r.order_size > 0");
						$sqlClient->execute();
						$res=$sqlClient->fetch();

						$color1="teal";
						$color2="indigo";

						echo"
						<div class='row'>
						<div class='table-responsive-lg col-sm-12 col-lg-12 col-md-12' >
						<table id='exampleffff' class='table table-sm' style='font-size:10px;'>
						<thead>
						<tr style='font-weight:bold;'>
						<td>Channel</td>
						<td>Symbol</td>
						<td>Total Bids/Orders</td>
						<td>Volume</td>
						</tr>
						</thead>
						<tbody>";
						$totalVol=0;
						$totalVolBNB=0;
						$totalVolRICB=0;
						$grandTotAmt=0;
						$grandTotComm=0;
						$totBNB=0;
						$totRICB=0;
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
						WHERE r.order_size > 0 AND r.user_name NOT LIKE 'MEM%' AND r.user_name NOT LIKE 'CC%' AND r.user_name NOT LIKE 'p001%' 
						GROUP BY r.symbol_id";
						$save = $dbh->prepare($sql);
						$save->execute();
						$j=0;
						foreach ($save as $row){
							if($row['symbol']=='BNB'){
								$co=$color1;
								$totalVolBNB=$totalVolBNB+$row['vol'];
								$totBNB=$totBNB+$row['cd_code'];
							}else{
								$co=$color2;
								$totalVolRICB=$totalVolRICB+$row['vol'];
								$totRICB=$totRICB+$row['cd_code'];
							}
							$totalVol=$totalVol+$row['vol'];
							$grandTotAmt=$grandTotAmt+$row['totalAmt'];

							echo"<tr style='color: #FFFFFF;' >";  
							/*if($j==0){
								echo"<td rowspan='2'>RSEB Online</td>";
							}*/   

							echo"<td rowspan='1'>RSEB Online</td>";                    

							echo"<td style='background-color:".$co."'>".$row['symbol']."</td>";
							echo"<td style='background-color:".$co."'>".$row['cd_code']."</td>";
							echo"<td style='background-color:".$co."'>".number_format($row['vol'])."</td>";

							echo"</tr>";
							$j++; 
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
						WHERE r.order_size > 0 AND r.user_name LIKE 'MEM%'
						GROUP BY r.symbol_id";
						$save = $dbh->prepare($sql);
						$save->execute();
						$i=0;
						foreach ($save as $row){
							if($row['symbol']=='BNB'){
								$co=$color1;
								$totalVolBNB=$totalVolBNB+$row['vol'];
								$totBNB=$totBNB+$row['cd_code'];
							}else{
								$co=$color2;
								$totalVolRICB=$totalVolRICB+$row['vol'];
								$totRICB=$totRICB+$row['cd_code'];
							}
							$totalVol=$totalVol+$row['vol'];
							$grandTotAmt=$grandTotAmt+$row['totalAmt'];

							echo"<tr style='color: #FFFFFF;' >";  
							/*if($i==0){
								echo"<td rowspan='2'>Brokers</td>";
							}*/

							echo"<td rowspan='1'>Brokers</td>";

							echo"<td style='background-color:".$co."'>".$row['symbol']."</td>";
							echo"<td style='background-color:".$co."'>".$row['cd_code']."</td>";
							echo"<td style='background-color:".$co."'>".number_format($row['vol'])."</td>";
							echo"</tr>"; 
							$i++;
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
						WHERE r.order_size > 0 AND r.user_name LIKE 'CC%'
						GROUP BY r.symbol_id";
						$save = $dbh->prepare($sql);
						$save->execute();
						$ii=0;
						foreach ($save as $row){
							if($row['symbol']=='BNB'){
								$co=$color1;
								$totalVolBNB=$totalVolBNB+$row['vol'];
								$totBNB=$totBNB+$row['cd_code'];
							}else{
								$co=$color2;
								$totalVolRICB=$totalVolRICB+$row['vol'];
								$totRICB=$totRICB+$row['cd_code'];
							}
							$totalVol=$totalVol+$row['vol'];
							$grandTotAmt=$grandTotAmt+$row['totalAmt'];

							echo"<tr style='color: #FFFFFF;' >";  
							/*if($ii==0){
								echo"<td rowspan='2'>Community Centers</td>";
							}
							*/
							echo"<td rowspan='1'>Community Centers</td>";

							echo"<td style='background-color:".$co."'>".$row['symbol']."</td>";
							echo"<td style='background-color:".$co."'>".$row['cd_code']."</td>";
							echo"<td style='background-color:".$co."'>".number_format($row['vol'])."</td>";
							echo"</tr>"; 
							$ii++;
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
						WHERE r.user_name LIKE 'P00%'
						GROUP BY r.symbol_id";
						$save = $dbh->prepare($sql);
						$save->execute();
						$ii=0;
						foreach ($save as $row){
							if($row['symbol']=='BNB'){
								$co=$color1;
								$totalVolBNB=$totalVolBNB+$row['vol'];
								$totBNB=$totBNB+$row['cd_code'];
							}else{
								$co=$color2;
								$totalVolRICB=$totalVolRICB+$row['vol'];
								$totRICB=$totRICB+$row['cd_code'];
							}
							$totalVol=$totalVol+$row['vol'];
							$grandTotAmt=$grandTotAmt+$row['totalAmt'];

							echo"<tr style='color: #FFFFFF;' >";  
							/*if($ii==0){
								echo"<td rowspan='2'>RSEB Walk-In</td>";
							}*/
							echo"<td rowspan='1'>RSEB Walk-In</td>";


							echo"<td style='background-color:".$co."'>".$row['symbol']."</td>";
							echo"<td style='background-color:".$co."'>".$row['cd_code']."</td>";
							echo"<td style='background-color:".$co."'>".number_format($row['vol'])."</td>";
							echo"</tr>"; 
							$ii++;
						}

						echo'
						<tr>
						<td colspan="1"  style="text-align:left;"><strong>Total</strong></td>
						<td></td>
						<td style="background-color:#0f52ba"> <b>'.$res['grandTotClient'].'</b></td>
						<td style="background-color:#0f52ba"> <b>'.number_format($totalVol).'</b></td>
						</tr>';
						$bnb=35240867;
						$ricb=15640000;
						$bnbp=($totalVolBNB/$bnb)*100;
						$ricbp=($totalVolRICB/$ricb)*100;
						echo'<tr>
						
						<td colspan="4" style="background-color:indigo">RICB Total Volume Subscribed :  <b> '.number_format($totalVolRICB).'&nbsp; / Percentage Subscribed : '.number_format($ricbp).' %</b></td>
						</tr>';

						echo'<tr>
						<td colspan="4" style="background-color:indigo">RICB Total Bidders :  <b> '.number_format($totRICB).'</b></td>
						</tr>';


						echo"</tbody>
						</table> </div>
						
						</div>"; 
						?>
						<div class="container" style="background-color:#CCD1D1;">
							<br />
							<div class="row">
								<!-- <div class="col-lg-6 ">
									<canvas id="barChart" id="favorite-colors"></canvas>
								</div> -->
								<div class="col-lg-8">
									<div class="row" style="color:#000000;">
										<div class="col-lg-3 col-sm-12">
											<div class="card">
												<div class="card-body">
													<h6 class="card-title">Highest Volume</h6>
													<?php
													$sqlClient=$dbh->prepare("SELECT distinct(symbol_id) FROM rights_issue");
													$sqlClient->execute();
													foreach ($sqlClient as $row){
														if($row['symbol_id']==5){
															$symbol='BNBL';
														}else{
															$symbol='RICB';
														}
														$s=$dbh->prepare("SELECT max(order_size) as p from rights_issue where symbol_id=:sid");
														$s->bindParam(':sid',$row['symbol_id']);
														$s->execute();
														$ss=$s->fetch();
														echo '<span style="color:green;"><b>'.$symbol.'</b> : '.number_format($ss['p']).'</span> &nbsp;&nbsp;&nbsp;';
													}
													?>
												</div>
											</div>
										</div>
										<div class="col-lg-3 col-sm-12">
											<div class="card">
												<div class="card-body">
													<h6 class="card-title">Highest Price</h6>
													<?php
													$sqlClient=$dbh->prepare("SELECT distinct(symbol_id) FROM rights_issue");
													$sqlClient->execute();
													foreach ($sqlClient as $row){
														if($row['symbol_id']==5){
															$symbol='BNBL';
														}else{
															$symbol='RICB';
														}
														$s=$dbh->prepare("SELECT max(bid_price) as p from rights_issue where symbol_id=:sid");
														$s->bindParam(':sid',$row['symbol_id']);
														$s->execute();
														$ss=$s->fetch();
														echo '<span style="color:green;"><b>'.$symbol.'</b>  Nu : '.$ss['p'].'</span>&nbsp;&nbsp;&nbsp;';
													}
													?>
												</div>
											</div>
										</div>
										<div class="col-lg-3 col-sm-12">
											<div class="card">
												<div class="card-body">
													<h6 class="card-title">Lowest Price</h6>
													<?php
													$sqlClient=$dbh->prepare("SELECT distinct(symbol_id) FROM rights_issue");
													$sqlClient->execute();
													foreach ($sqlClient as $row){
														if($row['symbol_id']==5){
															$symbol='BNBL';
														}else{
															$symbol='RICB';
														}
														$s=$dbh->prepare("SELECT min(bid_price) as p from rights_issue where symbol_id=:sid");
														$s->bindParam(':sid',$row['symbol_id']);
														$s->execute();
														$ss=$s->fetch();
														echo '<span style="color:red;"><b>'.$symbol.'</b>  Nu : '.$ss['p'].'</span>&nbsp;&nbsp;&nbsp;';
													}
													?>

												</div>
											</div>
										</div>
										<div class="col-lg-3 col-sm-12">
											<div class="card">
												<div class="card-body">
													<h6 class="card-title">Highest Vol. at price</h6>
													<?php
													$sqlClient=$dbh->prepare("SELECT distinct(symbol_id) FROM rights_issue");
													$sqlClient->execute();
													$price=0;
													$newhighvol=0;
													$i=0;
													foreach ($sqlClient as $row){
														if($row['symbol_id']==5){
															$symbol='BNBL';
														}else{
															$symbol='RICB';
														}
														$s=$dbh->prepare("SELECT DISTINCT(bid_price) as p from rights_issue where symbol_id=:sid");
														$s->bindParam(':sid',$row['symbol_id']);
														$s->execute();
														foreach($s as $ss){

															$sql="SELECT sum(order_size) as pp from rights_issue where symbol_id=".$row['symbol_id']." and bid_price=".$ss['p']."";
															$s=$dbh->prepare($sql);
															$s->execute();
															$sss=$s->fetch();
															$vol1=$sss['pp'];

															if($vol1 > $newhighvol){
																$newhighvol = $vol1;
																$price = $ss['p'];
															}else{

															}
														}		


														echo '<span style="color:purple;">'.$symbol.' : Price '.$price.' : Volume : '.number_format($newhighvol).'</span>&nbsp;&nbsp;&nbsp;</br>';
														$vol=0;
														$newhighvol=$vol;

													}
													?>

												</div>
											</div>
										</div>						
									</div>
								</div>

								<div class="col-lg-4 " >
									<canvas id="barChart2"  ></canvas>
								</div>

								
							</div>			
						</div>
						<br/>

						


					</div>
				</div>
				<br/>
				<div class="row">
					<div class=' line col-lg-12 col-md-12 col-sm-12' style='background-color: white;'>

						<canvas id='graphCanvas1' style='color:black;  bottom:10px;'></canvas>
					</div>
					<div class=' line col-lg-12 col-md-12 col-sm-12' style='background-color: white;' >
						<canvas id="graphCanvas"   style="color:black;"></canvas>
					</div>
				</div>
			</div>
		</div>

		<?php 
	}
	?>
</body>
<script type="text/javascript">
	$(document).ready(function () {
		showGraph();
		showGraph1();
	});


	function showGraph()
	{
		{

			$.post("kidudata.php",
				function (data)
				{
					console.log(data);
					var dates = [];
					var val = [];

					for (var i in data) {
						val.push(data[i].val);
						dates.push(data[i].dates);
					}

					var chartdata = {

						labels: dates,
						datasets: [
						{
							label: 'Daily Bids',		
							color: 'rgb(0, 0, 0)',						
							borderColor: 'rgb(0, 51, 204)',	
							backgroundColor: '#79D1CF',					
								//hoverBorderColor: '#000000',
								data: val,
								lineTension: '0.1',
								pointRadius: '0',
								borderWidth: '1',
								pointBackgroundColor:'black',
							}
							]

						};
						

						var graphTarget = $("#graphCanvas");
						var barGraph = new Chart(graphTarget, {
							type: 'line',
							data: chartdata,
							options: {

								responsive: true,
								legend: {
									position: 'bottom',
									display: true,
									labels: {
										fontColor: 'rgb(0, 0, 0)',
										fontStyle: "bold",
									}
								},



								"animation": {
									"duration": 1,
									"onComplete": function() {
										var chartInstance = this.chart,
										ctx = chartInstance.ctx;

										ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily,Chart.defaults.global.defaultFontColor = "#000");
										ctx.textAlign = 'center';
										ctx.textBaseline = 'bottom';

										this.data.datasets.forEach(function(dataset, i) {
											var meta = chartInstance.controller.getDatasetMeta(i);
											meta.data.forEach(function(bar, index) {
												var data = dataset.data[index];
												ctx.fillText(data, bar._model.x - 0, bar._model.y - 8);
											});
										});
									}
								},
								title: {
									display: false,
								},
							}

						});
					});
		}
	}


	function showGraph1()
	{
		{

			$.post("kidudatavol.php",
				function (data)
				{
					console.log(data);
					var dates = [];
					var val = [];

					for (var i in data) {
						val.push(data[i].val);
						dates.push(data[i].dates);
					}

					var chartdata = {

						labels: dates,
						datasets: [
						{
							label: 'Daily Vol',		
							color: 'rgb(0, 0, 0)',						
							borderColor: 'rgb(0, 51, 204)',	
							backgroundColor: '#79D1CF',					
								//hoverBorderColor: '#000000',
								data: val,
								lineTension: '0.1',
								pointRadius: '0',
								borderWidth: '1',
								pointBackgroundColor:'black',
							}
							]

						};
						

						var graphTarget = $("#graphCanvas1");
						var barGraph = new Chart(graphTarget, {
							type: 'line',
							data: chartdata,
							options: {

								responsive: true,
								scales:{
									xAxes:[{
										display:true,

									}],
									yAxes: [{

										gridLines: {
											color: "rgba(0, 0, 0, 0)",
										}   
									}]
								},


								legend: {
									position: 'bottom',
									display: true,
									labels: {
										fontColor: 'rgb(0, 0, 0)',
										fontStyle: "bold",
									},

								},

								"animation": {
									"duration": 1,
									"onComplete": function() {
										var chartInstance = this.chart,
										ctx = chartInstance.ctx;

										ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily,Chart.defaults.global.defaultFontColor = "#000");
										ctx.textAlign = 'center';
										ctx.textBaseline = 'bottom';

										this.data.datasets.forEach(function(dataset, i) {
											var meta = chartInstance.controller.getDatasetMeta(i);
											meta.data.forEach(function(bar, index) {
												var data = dataset.data[index];
												ctx.fillText(data, bar._model.x - 0, bar._model.y - 8);
											});
										});
									}
								},
								title: {
									display: false,
								},
							}

						});
					});
		}
	}
</script>

</script>

<!-- <script>
	var bnb = <?php echo $totalVolBNB; ?>;
	var canvas = document.getElementById("barChart");
	var ctx = canvas.getContext('2d');

	var bnbto = 35240867;
	var over = bnb-bnbto;

	var rembnb = parseInt(bnbto)-parseInt(bnb);
	

// Global Options:
Chart.defaults.global.defaultFontColor = 'black';
Chart.defaults.global.defaultFontSize = 10;

var data = {
	labels: ["Subscribed: "+bnb.toLocaleString("en-US"), "Remaining : 0"],
	datasets: [
	{
		fill: true,
		backgroundColor: [
		'red',
		'#7FB3D5'],
		data: [bnb,0],
// Notice the borderColor 
borderColor:	['white', 'green'],
borderWidth: [2,2]
}
]
};

// Notice the rotation from the documentation.

var options = {
	responsive: false,
	title: {
		display: true,
		text: 'BNB - Oversubscribed by : '+ over.toLocaleString("en-US")+ ' shares',
		//text: 'BNB ',
		position: 'top'
	},
	hover: {mode: null},
	tooltips: {enabled: false},
	rotation: -0.7 * Math.PI
};


// Chart declaration:
var myBarChart = new Chart(ctx, {
	type: 'pie',
	data: data,
	options: options
});       
</script> -->

<script>
	var ricb = <?php echo $totalVolRICB; ?>;
	var canvas = document.getElementById("barChart2");
	var ctx = canvas.getContext('2d');
	var ricbto = 15640000;
	var rem = parseInt(ricbto)-parseInt(ricb);

	var over1 = ricb-ricbto;

// Global Options:
Chart.defaults.global.defaultFontColor = 'black';
Chart.defaults.global.defaultFontSize = 10;

var data = {
	labels: ["Remaining: "+rem.toLocaleString("en-US"), "Subscribed :"+ricb.toLocaleString("en-US")],
	datasets: [
	{
		fill: true,
		backgroundColor: [
		'red',
		'#7FB3D5'],
		data: [ricb, rem ],
		// Notice the borderColor 
		borderColor:	['white', 'green'],
		borderWidth: [2,2]
	}
	]
};

// Notice the rotation from the documentation.

var options = {
	responsive: false,
	title: {
		display: true,
		//text: 'RICB - Oversubscribed by : '+ over1.toLocaleString("en-US")+ ' shares',
		text: 'RICB - Offer Volume : '+ ricbto,
		position: 'top'
	},
	hover: {mode: null},
	tooltips: {enabled: false},
	rotation: -0.7 * Math.PI,
};


// Chart declaration:
var myBarChart = new Chart(ctx, {
	type: 'pie',
	data: data,
	options: options
});       
</script>


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