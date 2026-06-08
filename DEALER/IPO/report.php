<!DOCTYPE html>
<html lang="en">
<head>
	<title>RSEB_IPO</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script><script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>
<body>

	<div class="contact1">
		<div class="container-contact1">

			<section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
				<!-- Default box -->
				<div class="box">
					<div class="box-header with-border">
						<h4 class="box-title">Detailed Trade Details</h4>

						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
								<i class="fa fa-minus"></i></button>
								<button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
									<i class="fa fa-times"></i></button>
								</div>
							</div>
							<div class="box">
								<div class="col-xs-4">
									<label>From Date</label>
									<div class="input-group date">
										<div class="input-group-addon">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="date" class="form-control pull-right" name="from_date1" id="from_date1" required>
									</div>
								</div>
								<div class="col-xs-4">
									<label>To Date</label>
									<div class="input-group date">
										<div class="input-group-addon">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="date" class="form-control pull-right" name="to_date1" id="to_date1" required>
									</div>
								</div><br><br><br><br>
								<div class="box-footer">
									<div class="col-xs-4">          
										<button type="button" class="btn btn-success" id="dTradeDetails" name="dTradeDetails" value="">  Generate </button>
									</div>
								</div>
								<div id="details">
								</div>
							</div>    
						</section>
					</div>
				</div>






				<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
				<script src="vendor/bootstrap/js/popper.js"></script>
				<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
				<script src="vendor/select2/select2.min.js"></script>
				<script src="vendor/tilt/tilt.jquery.min.js"></script>
				<script >
					$('.js-tilt').tilt({
						scale: 1.1
					})
				</script>
				<script type="text/javascript">
					
					$('#dTradeDetails').click(function(){				

						var toDate1 = $("#to_date1").val();
						var fromDate1 = $("#from_date1").val();
						var dTradeDetails = 'dTradeDetails';
						$.ajax({
							type: "POST",
							url: "reportProcess.php",
							data: 'toDate1='+toDate1 +'&fromDate1='+fromDate1 +'&dTradeDetails='+ dTradeDetails,
							success: function(data){

							//	hideloading();
								$("#details").html(data);
							}
						});
					});
				</script>



















				<!-- Global site tag (gtag.js) - Google Analytics -->
				<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
				<script>
					window.dataLayer = window.dataLayer || [];
					function gtag(){dataLayer.push(arguments);}
					gtag('js', new Date());

					gtag('config', 'UA-23581568-13');
				</script>

				<!--===============================================================================================-->
				<script src="js/main.js"></script>

			</body>
			</html>
