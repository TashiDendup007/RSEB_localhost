<?php 
	include('CONNECTIONS/db.php'); 
	if(isset($_GET['id']))
	{ 
		$id = $_GET['id'];
    }
    error_reporting(E_ALL);
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

<div class="container">
	<div class="content-wrapper">
		<div class="row">
			<div class="col-sm-2">&nbsp;</div>
			<div class="col-sm-8">
				<a href="#">
					<h3 style="text-align: center; color: #ffffff;font-size: 1.7em!important;" class="backgroud" id="container">
						OFFER FOR SALE OF SHARES - CSI Portal 
					</h3>
				</a> 
				<!-- <p style="color:#0770AD;">
					<strong>[* mark fields are mandatory]</strong> -----------------------------------------
					<a class="btn" href="shareAuctionCSIUpdate.php" style="color:#0770AD;"> <strong>[ Click here to Update Bids ]</strong></a>
				</p> -->

				<!-- <div class="alert alert-danger alert-dismissible fade show" role="alert" id="mtdErrorMsg" style="display: none;">
				  Sorry, No method to be found. Try again later.
				  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				    <span aria-hidden="true">&times;</span>
				  </button>
				</div>
				<div class="alert alert-success alert-dismissible fade show" role="alert" id="successMsg" style="display: none;">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				    	<span aria-hidden="true">&times;</span>
				  	</button>
				 	Your Bid was successful. Thank you.
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
				<form action="process/loadCSI.php" method="POST" class="form-horizontal" id="shareAuctionForm" style="font-size: 12px;" onsubmit="confirmsub();">
					<div class="form-group row">
				    	<label for="cid2" class="col-sm-4 col-form-label">CID No <font color="red">*</font></label>

				    	<div class="col-sm-8">
				    		<input type="number" class="form-control" name="cid2" id="cid2" minlength="11" maxlength="11" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==11) return false;" required>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="cid1" class="col-sm-4 col-form-label">Confirm CID No <font color="red">*</font></label>
				    	<div class="col-sm-8">
				    		<input type="number" class="form-control" name="cid1" id="cid1" minlength="11" maxlength="11" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==11) return false;" required>
				    	</div>
				  	</div>
					<div class="form-group row">
				    	<label for="name1" class="col-sm-4 col-form-label">Full Name <font color="red">*</font> </label>
				    	<div class="col-sm-8">
				    		<input type="text" class="form-control" name="name1" id="name1" required>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="phoneNo1" class="col-sm-4 col-form-label">Phone No <font color="red">*</font> </label>
				    	<div class="col-sm-8">
				    		<input type="number" class="form-control" name="phoneNo1" id="phoneNo1" required  onsubmit="phoneLength();">
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="email1" class="col-sm-4 col-form-label">Email</label>
				    	<div class="col-sm-8">
				    		<input type="email" class="form-control" name="email1" id="email1">
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="bank1" class="col-sm-4 col-form-label">Bank  <font color="red">*</font> </label>
				    	<div class="col-sm-8"> -->
				    		<!-- <input type="text" class="form-control" name="bank1" id="bank1" required>
				    		<select class="form-control" name="bank1" id="bank1"  required>
						    	<option value="">--Select--</option>
						    	<option value="BOBL">BOBL</option>
						    	<option value="BNBL">BNBL</option>
						     	<option value="BDBL">BDBL</option>
						      	<option value="DPNB">DPNB</option>
						      	<option value="TBANK">T-BANK</option>
						   	</select>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="bankAccNo1" class="col-sm-4 col-form-label">Bank Account No <font color="red">*</font> </label>
				    	<div class="col-sm-8">
				    		<input type="number" class="form-control" name="bankAccNo1" id="bankAccNo1" required>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="symbolId1" class="col-sm-4 col-form-label">Symbol <font color="red">*</font> </label>
				    	<div class="col-sm-8">
						    <select class="form-control" name="symbolId1" id="symbolId1" onchange="setPrice1(this.value)" required>
						    <option value="">--Select--</option>
						    <option value="5">BNBL</option>
						    <option value="18">RICB</option>
						   </select>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="price1" class="col-sm-4 col-form-label">Bid Price <font color="red">*</font> </label>
				    	<div class="col-sm-8">
				    		<input type="number" class="form-control" name="price1" id="price1" step="0.5" max="200" required>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="volume1" class="col-sm-4 col-form-label">Volume <font color="red">*</font> </label>
				    	<div class="col-sm-8">
				    		<input type="number" min="10" class="form-control" name="volume1" id="volume1" required>
				    	</div>
				  	</div>
				  	<div class="form-group row">
				    	<label for="cse" class="col-sm-4 col-form-label">CSE Employee ID <font color="red">*</font> </label>
				    	<div class="col-sm-8">
				    		<input type="text" class="form-control" name="cse" id="cse" required>
				    	</div>
				  	</div>
				  	<br>
				  	<div class="form-check form-check-inline" style="padding-left: 80px;">
					  	<input class="form-check-input" type="checkbox" name="declaration1" id="declaration1" value="1" required>
					  	I declare that, the information stated above are true to the best of my knowledge & belief and I agree to the terms and conditions of RSEB for Participating in  Online Bid of shares.
					</div>
					<div class="form-group row">
						<div class="col-sm-12 text-center"><br>
							<button type="submit" class="btn btn-primary btn-lg" name="submitShareAuctionCSI" id="submitShareAuctionCSI" onClick="checkCID();">Submit</button>&nbsp;&nbsp;
							<button type="button" class="btn btn-warning btn-lg" onclick="cancelFun();"> Cancel</button>
						</div>
					</div>
				</form>
			</div> -->
			<div class="col-sm-2">&nbsp;</div>
			<div class="col-sm-12 text-center">
				<a class="btn" href="shareAuction_csiDtls.php" style="color:white;" target="_blank"> <strong><h3>[ Click here to View Details ] </h3></strong></a>
			</div>
	</div>
</div>

</body>

<script type="text/javascript">
$(document).ready(function() {
    $('#exampleffff').DataTable( {
        "pagingType": "full_numbers"
    } );
} );
</script>

<script src="vendor/bootstrap/js/popper.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="vendor/select2/select2.min.js"></script>
<script src="vendor/main.js"></script>
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
	function confirmsub(){
		var cid2 = $("#cid2").val();
		var cid1 = $("#cid1").val();

		var price = $("#price1").val();
		var volume = $("#volume1").val();
		var total = Number(price) * Number(volume);

		var commision = total*1/100;
		var finalTotal = (commision+total).toFixed(2);

		if(cid2.length < 11){
			alert("CID Number should be 11 digits");
			event.preventDefault();
    		return false;
		}

		if(cid2 != cid1){
			alert("CID does not match. Please check.");
			event.preventDefault();
    		return false;
		}else{
			if(confirm("Total is Nu. "+finalTotal+" (inclusive of 1% commission), Do you want to continue?")){
				return true;
			}else{
				event.preventDefault();
    			return false;
			}
		}
	}
	
	function phoneLength(){
		var phone =$("#phoneNo1").val();
		if(phone.length >8){
			alert("Phone number lenght must be less or equal to 8 digits");
			event.preventDefault();
    		return false;
		}
	}


	function cancelFun(){
		window.location="shareAuctionCSI.php";
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