<?php 
	include('CONNECTIONS/db.php'); 
	if(isset($_GET['id']))
	{ 
		$id = $_GET['id'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>RSEB</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
  	echo "The Update for share Auction has ended.";
  	die();
  }
  else{
?>
<div class="container">
	<div class="content-wrapper">
		<div class="row">
			<div class="col-sm-2">&nbsp;</div>
			<div class="col-sm-8">
				<a href="shareAuctionCSI.php">
					<h3 style="text-align: center; color: #ffffff;font-size: 1.7em!important;" class="backgroud" id="container">
						OFFER FOR SALE OF SHARES - CSI Portal
					</h3>
				</a> 

				<div class="alert alert-danger alert-dismissible fade show" role="alert" id="mtdErrorMsg" style="display: none;">
				  Sorry, No method to be found. Try again later.
				  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				    <span aria-hidden="true">&times;</span>
				  </button>
				</div>
				
				<div class="alert alert-success alert-dismissible fade show" role="alert" id="successMsg" style="display: none;">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				    	<span aria-hidden="true">&times;</span>
				  	</button>
				 	Your Update was successful. Thank you.
				</div>
				<div class="alert alert-warning alert-dismissible fade show" role="alert" id="errorMsg" style="display: none;">
				 	<strong>OOPS! You cancelled the transaction</strong>
				  	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					  </button>
				</div>
				<div class="alert alert-warning alert-dismissible fade show" role="alert" id="errorMsg1" style="display: none;">
				 	<strong>OOPS! Transaction was not successful</strong>
				  	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					  </button>
				</div>
				<div class="alert alert-warning alert-dismissible fade show" role="alert" id="errorMsg2" style="display: none;">
				 	<strong>It seems you have already placed a BId. Consider updating it.</strong>
				  	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					  </button>
				</div>
				<div class="alert alert-warning alert-dismissible fade show" role="alert" id="errorMsg3" style="display: none;">
				 	<strong>There was an error. Please, try again later!!!</strong>
				  	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					  </button>
				</div>
				<div class="alert alert-warning alert-dismissible fade show text-center" role="alert" id="warningMsgId" style="display: none;">
				 	<strong>No Details to be found</strong>
				  	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					</button>
				</div>

				<form action="process/loadCSI.php" method="POST" class="form-horizontal" id="csiFormId" style="font-size: 12px;">
					<div class="form-group row">
				    	<div class="col-md-6">
				    		<label for="cid">CID No</label>
				    		<input type="number" class="form-control" name="cid" id="cid" minlength="11" maxlength="11" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==11) return false;" placeholder="Enter CID Number" required>
				    	</div>
				  	
				    	<div class="col-md-6">
				    		<label for="symbolId">Symbol</label>
						    <select class="form-control" name="symbolId" id="symbolId" onchange="setPrice1(this.value)" required>
							    <option value="">--Select--</option>
							    <option value="5">BNBL</option>
							    <option value="18">RICB</option>
						   </select>
				    	</div>
				  	</div>
					<div class="form-group row">
						<div class="col-sm-12 text-center"><br>
							<button type="button" class="btn btn-primary btn-lg" name="getCSIDetails" id="getCSIDetails">Submit</button>&nbsp;&nbsp;
							<button type="reset" class="btn btn-warning btn-lg"> Reset</button>&nbsp;&nbsp;
							<button type="button" class="btn btn-success btn-lg" onclick="cancelFun()"> Back to CSI Form</button>
						</div>
					</div>
				</form>

				<div id="detailsId"></div>
			</div>
			<div class="col-sm-2">&nbsp;</div>
		</div>
		
	</div>
</div>
<?php 
}
?>
</body>
<script src="vendor/bootstrap/js/popper.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="vendor/select2/select2.min.js"></script>
<script src="vendor/main.js"></script>
<script type="text/javascript">
	function selectFormType(val){
		if(val == 1){
			$("#formId").show();
			$("#formUpdateId").hide();
		}else if(val == 2){
			$("#formUpdateId").show();
			$("#formId").hide();
		}else{
			$("#formUpdateId").hide();
			$("#formId").hide();
		}
	}
</script>
<script type="text/javascript">
	$( document ).ready(function() {
		var d = "<?php echo $id; ?>";
		if(d == 1){
			$("#existedMsg").show();
			setTimeout(function() { $('#existedMsg').fadeOut('fast'); }, 15000); 
		}else if (d ==2) {
			$("#successMsg").show();
			setTimeout(function() { $('#successMsg').fadeOut('fast'); }, 15000);			
		}else if (d ==100) {
			$("#errorMsg").show();
			setTimeout(function() { $('#errorMsg').fadeOut('fast'); }, 15000); 
		}else if (d ==5) {
			$("#emailMsg").show();
			setTimeout(function() { $('#emailMsg').fadeOut('fast'); }, 15000);
		}else if (d ==3) {
			$("#successMsgUpdt").show();
			setTimeout(function() { $('#successMsgUpdt').fadeOut('fast'); }, 15000);
		}else if (d ==200) {
			$("#errorMsg1").show();
			setTimeout(function() { $('#errorMsg1').fadeOut('fast'); }, 15000); 
		}else if (d ==300) {
			$("#errorMsg2").show();
			setTimeout(function() { $('#errorMsg2').fadeOut('fast'); }, 15000); 
		}else if (d ==400) {
			$("#errorMsg3").show();
			setTimeout(function() { $('#errorMsg3').fadeOut('fast'); }, 15000); 
		}else if (d ==1000) {
			$("#mtdErrorMsg").show();
			setTimeout(function() { $('#mtdErrorMsg').fadeOut('fast'); }, 15000); 
		}
	});
</script>
<script type="text/javascript">
    document.addEventListener('contextmenu', event => event.preventDefault());
	document.onkeydown = function(e) {
    if(event.keyCode == 123) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
        return false;
    }
    if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
        return false;
    }
}
</script>
<script type="text/javascript">
  function showLoading(){
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
  }
  function hideloading(){
    document.getElementById('loadingmsg').style.display = 'none';
    document.getElementById('loadingover').style.display = 'none';
  }
</script>


<script type="text/javascript">
	$("#getCSIDetails").click(function(){
		showLoading();
		var cidNo = $("#cid").val();
		var symbolId = $("#symbolId").val();
		op = "loadDetails";

		if(cidNo == ''){
			alert("Please enter CID Number");
			hideloading();
			event.preventDefault();
			return false;
		}

		if(symbolId == ''){
			alert("Please select symbol");
			hideloading();
			event.preventDefault();
			return false;
		}

		$.ajax({
			type: "POST",
				url: "process/loadCSI.php",
				data:'cidNo='+cidNo+'&loadDetails='+op+'&symId='+symbolId,
				success: function(response){
					if(response == 400){
						$("#warningMsgId").show();
						$("#csiFormId").show();
						setTimeout(function() { $('#warningMsgId').fadeOut('fast'); }, 9000);
					}else{
						$("#csiFormId").hide();
						$("#detailsId").html(response);
					}
					hideloading();
				}
			});
	});

	function cancelFun(){
		window.location="shareAuctionCSI.php";
		return false;
	}

	function cancelFun22(){
		window.location="shareAuctionCSIUpdate.php";
		return false;
	}

	function setPrice1(symId){
		if(symId == 5){
			$("#price1").attr({"min" : 33 });
		}else if(symId == 18){
			$("#price1").attr({"min" : 70 });
		}else{
			$("#price1").attr({"min" : 10 });
		}
	}
</script>
</html>