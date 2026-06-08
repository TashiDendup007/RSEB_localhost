<?php 
date_default_timezone_set("Asia/Thimphu");
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
<?php 
/*  date_default_timezone_set('Asia/Thimphu');
  $dateselect=date("Y-m-d H:i:s");
  if('2021-06-15 09:00:00' > $dateselect){
  	echo "The Auction will open on : 2021-06-15 09:00:00";
  }
  else if('2021-07-15 17:00:00' < $dateselect){
  	echo "The Registration for this Auction has ended.";
  	die();
  }
  else{*/
?>
<div class="container">
	<div class="content-wrapper">
		<div class="row">
			<?php

			echo'
		<table class="table table-striped table-bordered>
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
		WHERE p.bfs_orderid='CSE' AND p.payment_status='PE'");
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


			?>
			
	</div>
</div>
</div>
<?php 
//}
?>
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