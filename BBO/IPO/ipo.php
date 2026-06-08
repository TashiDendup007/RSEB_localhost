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
			<div class="contact1-pic js-tilt" data-tilt>

						<p style="color: green; text-align: center; font-size: 18px;"><b>
					Initiative to help Bhutanese communities living aboard.</b>	
				</p>
				<img src="images/1.jpg" alt="IMG">
					
			</div>
			<form class="contact1-form validate-form">

				<span class="contact1-form-title">

					Sherza Venture's IPO
					<p style="color: red;"><b>
					Notification : Please note that you should be above 18 years of age to invest.</b>	
				</p>
				</span>
				<div class="wrap-input1 validate-input" data-validate = "Name is required">
					<input class="input1" type="text" name="name" id="name" placeholder="Name">
					<span class="shadow-input1"></span>
				</div>
				<div class="wrap-input1 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
					<input class="input1" type="text" name="email" id="email" placeholder="Email">
					<span class="shadow-input1"></span>
				</div>
				<div class="wrap-input1 validate-input" data-validate = "Subject is required">
					<input class="input1" type="number" name="cid" id="cid" placeholder="CID">
					<span class="shadow-input1"></span>
				</div>
				<div class="wrap-input1 validate-input" data-validate = "Subject is required" >
					<input class="input1" type="number" name="amount" id="amount" placeholder="Amount(Nu)" onchange="check(this.value)">
					<span class="shadow-input1"></span>
				</div>
				<div class="wrap-input1 validate-input" data-validate = "Subject is required">
					<label>Please use mPay or mBoB to transfer the money in this account:<b>100902539</b>(BOB's A/c)</label>
					<input class="input1" type="text" name="ref" id="ref" placeholder="Transaction reference No/Journal No">
					<span class="shadow-input1"></span>
				</div>
				<div class="wrap-input1 validate-input" data-validate = "Subject is required">
					<input class="input1" type="text" name="addrs" id="addrs" placeholder="Present Address">
					<span class="shadow-input1"></span>
				</div>
				<div class="container-contact1-form-btn">
					<button class="contact1-form-btn" id="submit" style="display:none;">
						<span>
							Subscribe
							<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
						</span>
					</button>
				</div>
				<div class="container-contact1-form-btn">
					<p>Powered by RSEB <img src="images/icon.png" width="20px;"></p>
				</div>
			</form>
		</div>
	</div>
	
<script type="text/javascript">
	function check(val){
		
		if(val % 13 != 0 ){
			alert("Please enter amount in the multiples of 13");
			$("#submit").hide(); 
		}
		else{
		$("#submit").show();
		}

	}
</script>

	<script type="text/javascript">
		$(document).ready(function(){
			$("#submit").click(function(){

				var Name = $("#name").val();
				var Email = $("#email").val();
				var CID = $("#cid").val();
				var Amount = $("#amount").val();
				var ref = $("#ref").val();
				var Addrs = $("#addrs").val();				
				var operation = "subscribe";
				var dataString = 'Name='+ Name + '&Email='+ Email +'&CID='+ CID +
				'&Amount='+ Amount +'&addrs='+ Addrs +'&ref='+ ref  +'&subscribe='+ operation;
				if(Name==''|| Email==''|| CID =='' ||  Amount =='' || Addrs=='' || ref =='')
				{
					alert("Please Fill All Mandatory Fields");

				}

				else
				{ 
					if (confirm("Are you sure you want to Subscribe?"))
					{

						$.ajax({
							type: "POST",
							url: "process.php",
							data: dataString ,
							success: function(data){

								if(data == 'Message! Successfully Subscribed.')
								{
									alert(data);

								}
								else
								{
									location.reload();
									alert(data);
								}


							}
						});
					}
					else
					{
						return false;
					}
				}
				return false;
			});
		});
	</script>


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
