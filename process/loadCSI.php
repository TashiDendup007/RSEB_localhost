<?php
date_default_timezone_set("Asia/Thimphu");
include('../CONNECTIONS/db.php');

if(isset($_POST["submitShareAuctionCSI"]))
{
	$cidNo = $_POST["cid1"];
	$sysTime = date("YmdHis");
	$cd_code = "CS".substr($sysTime, -8);

	$bankAccNo = $_POST["bankAccNo1"];
	$bank = $_POST["bank1"];
	$volume = $_POST["volume1"];
	$price = $_POST["price1"];
	$name = $_POST["name1"];
	$phoneNo = $_POST["phoneNo1"];
	$bfs_remitterEmail = $_POST["email1"];
	$symbol_id = $_POST["symbolId1"];
	$CSIEmpId = $_POST["cse"];
	$total = $volume * $price;
	$commission = $total*1/100;
	$totalNew = $total+$commission;
	$details = $name."|".$bank."|".$bankAccNo."|".$phoneNo."|".$bfs_remitterEmail."|".$CSIEmpId."|".$cidNo;
	$totalAmt = $totalNew;
	$status =0;
	$type='SA';

	$checkCDCode=substr($cd_code, 0, 2);

	if($checkCDCode == 'CS')
	{
		/*$check= $dbh->prepare("SELECT c.order_size FROM rights_issue c WHERE c.cd_code=:cdCode AND c.symbol_id=:symId AND c.status=0");
    	$check->bindParam(':cdCode',$cd_code);
    	$check->bindParam(':symId',$symbol_id);
    	$check->execute();
    	$state=$check->fetch();
		if($check->rowCount() > 0)
		{
			header("location: ../shareAuctionCSI.php?id=300");
			die();
		}*/

		$getRecord=$dbh->prepare("SELECT * FROM rights_issue_online_temp p WHERE p.details LIKE '%$cidNo%' AND p.symbol_id=:symId");
    	$getRecord->bindParam(':symId',$symbol_id);
    	$getRecord->execute();
    	if($getRecord->rowCount() > 0)
    	{
    		header("location: ../shareAuctionCSI.php?id=300");
			die();
    	}
	}

	$saveBidTemp = $dbh->prepare("INSERT INTO rights_bid_temp(cidNo, symbol_id, cd_code, bid_price, bid_vol, Secret, type) 
		VALUES ('$cidNo','$symbol_id','$cd_code','$price', '$volume','0000','Bid')");
	$saveBidTemp->execute();

	$savetemp = $dbh->prepare("INSERT INTO rights_issue_online_temp(bfs_orderid, cd_code, symbol_id, amount, vol_applied, type, name, email, phone, price, details) 
		VALUES ('CSE','$cd_code','$symbol_id','$totalAmt','$volume','CS','$CSIEmpId','$bfs_remitterEmail','$phoneNo', '$price', '$details')");
	$savetemp->execute();

	$save = $dbh->prepare("INSERT INTO rights_issue(type,cd_code,renounce_cd_code,order_size,symbol_id,rights_issued,face_value, total_amount,bid_price,available_rights,user_name,status) 
		 	VALUES ('$type','$cd_code','$cd_code','$volume','$symbol_id','0','10','$totalAmt','$price','$volume','$CSIEmpId','0')");
    if($save->execute()){
    	header("location: ../shareAuctionCSI.php?id=2");
		die();
    }else{
    	header("location: ../shareAuctionCSI.php?id=400");
		die();
    }
}
else if(isset($_POST["loadDetails"]))
{
	$cidNo = $_POST["cidNo"];
	$symId = $_POST["symId"];

	//$cd_code = "CS".substr($cidNo, -8);

	$symbolName='';
	if($symId == 18){
		$symbolName = 'RICB';
	}else{
		$symbolName = 'BNBL';
	}

	$sql="SELECT r.cd_code, r.user_name, t.details, t.phone, t.email, r.bid_price, r.order_size, t.symbol_id, r.total_amount, t.amount 
		FROM rights_issue r 
		LEFT JOIN rights_issue_online_temp t ON r.cd_code = t.cd_code 
		WHERE r.symbol_id=t.symbol_id AND t.symbol_id=:symId AND t.details LIKE '%$cidNo%' -- AND r.user_name LIKE 'CC%'";
	$getDetails = $dbh->prepare($sql);
	$getDetails->bindParam(':symId', $symId);
	$getDetails->execute();
	$res = $getDetails->fetch();

	$getAmt=$dbh->prepare("SELECT SUM(p.amount) amt FROM rights_issue_online_temp p WHERE p.cd_code=:cdId AND p.symbol_id=:symId");
	$getAmt->bindParam(':cdId', $res['cd_code']);
	$getAmt->bindParam(':symId', $symId);
	$getAmt->execute();
	$amtRes=$getAmt->fetch();

	$totAmt_CSI=$amtRes['amt'];

	if($getDetails->rowCount() > 0){
		echo'
		<form action="process/loadCSI.php" method="POST" class="form-horizontal" id="csiFormDetlsId" style="font-size: 12px;" onsubmit="showLoading()">
			<div class="form-group row">
				<div class="col-md-12">
		    		<label for="cid">Details</label>
		    		<input type="text" class="form-control" style="background-color: #FFC1AD;" name="details" id="details" value="'.$res['details'].'" readonly>
		    	</div>
		    	<div class="col-md-6">
		    		<label for="cid">CID No</label>
		    		<input type="hidden" class="form-control" name="user_name" id="user_name" value="'.$res['user_name'].'">
		    		<input type="number" class="form-control" style="background-color: #FFC1AD;" name="cid" id="cid" value="'.$cidNo.'" readonly>
		    	</div>
		    	<div class="col-md-6">
		    		<label for="cdCode">CD Code</label>
				    <input type="text" class="form-control" style="background-color: #FFC1AD;" name="cdCode" id="cdCode" value="'.$res['cd_code'].'" readonly>
		    	</div>
		    	<div class="col-md-6">
		    		<label for="amt">Amount</label>
		    		<input type="number" class="form-control" style="background-color: #FFC1AD;" name="amt" id="amt" value="'.$totAmt_CSI.'" readonly>
		    	</div>
		    	<div class="col-md-6">
		    		<label for="symbolId">Symbol</label>
				    <input type="hidden" class="form-control" name="symbolId" id="symbolId" value="'.$symId.'">
				    <input type="text" class="form-control" style="background-color: #FFC1AD;" name="symbolName" id="symbolName" value="'.$symbolName.'" readonly>
		    	</div>
		    	<div class="col-md-6">
		    		<label for="price">Price</label>
				    <input type="number" class="form-control" name="price" id="price" value="'.$res['bid_price'].'" max="200" step="0.5">
		    	</div>
		    	<div class="col-md-6">
		    		<label for="symbolId">Volume (No. of Shares)</label>
				    <input type="number" class="form-control" name="vol" id="vol" value="'.$res['order_size'].'">
		    	</div>
		  	</div>
			<div class="form-group row">
				<div class="col-sm-12 text-center"><br>
					<button type="submit" class="btn btn-primary btn-lg" name="updateCSIDetails" id="updateCSIDetails" onclick="checkAmount()">Update</button>&nbsp;&nbsp;
					<button type="reset" class="btn btn-warning btn-lg"> Reset</button>&nbsp;&nbsp;
					<button type="button" class="btn btn-danger btn-lg" onclick="cancelFun22()"> Cancel</button>
				</div>
			</div>
		</form>
		<script type="text/javascript">
			function showLoading()
		  	{
		    	document.getElementById("loadingmsg").style.display = "block";
		    	document.getElementById("loadingover").style.display = "block";
		  	}

		  	function checkAmount()
		  	{
				var initialAmt = $("#amt").val();
				var price = $("#price").val();
				var vol = $("#vol").val();

				var total = Number(price) * Number(vol);

				var commision = total*1/100;
				var finalTotal = (commision+total).toFixed(2);

				var diffAmt = 0;
				if(finalTotal > initialAmt){
					diffAmt=finalTotal-initialAmt;
					if(confirm("You have to pay Nu. "+diffAmt.toFixed(2)+" (inclusive of 1% commission), Do you want to continue?")){
						return true;
					}else{
						event.preventDefault();
		    			return false;
					}
				}else{
					if(confirm("Do you want to continue?")){
						return true;
					}else{
						event.preventDefault();
		    			return false;
					}
				}
			}
		</script>';
	}else{
		echo'400';
		die();
	}
}
else if(isset($_POST["updateCSIDetails"]))
{
	$usrName = $_POST["user_name"];
	$cidNo = $_POST["cid"];
	$cdCode = $_POST["cdCode"];
	$symId = $_POST["symbolId"];
	$price = $_POST["price"];
	$vol = $_POST["vol"];
	$initialAmt = $_POST["amt"];

	$amt = $price * $vol;
	$commission = $amt*1/100;
	$totAmt = $amt+$commission;

	if($totAmt > $initialAmt)
	{
		$diffAmt=$totAmt-$initialAmt;

		$saveBidTemp0 = $dbh->prepare("INSERT INTO rights_bid_temp(cidNo, symbol_id, cd_code, bid_price, bid_vol, Secret, type) 
			VALUES ('$cidNo','$symId','$cdCode','$price', '$vol','0000','UPDATE')");
		$saveBidTemp0->execute();

		$insertOnlTemp0 = $dbh->prepare("INSERT INTO rights_issue_online_temp(bfs_orderid, cd_code, symbol_id, amount, payment_status, type, name, vol_applied, price) 
			VALUES ('CSE','$cdCode','$symId','$diffAmt', 'PU','CS','$usrName','$vol', '$price')");
		$insertOnlTemp0->execute();

		$insertRigIssCsi0 = $dbh->prepare("UPDATE rights_issue r SET r.order_size=:vol, r.bid_price=:pri, r.total_amount=:amt, r.available_rights=:vol1 WHERE r.cd_code=:cdCode AND r.symbol_id=:symId");
		$insertRigIssCsi0->bindParam(':vol', $vol);
		$insertRigIssCsi0->bindParam(':pri', $price);
		$insertRigIssCsi0->bindParam(':amt', $totAmt);
		$insertRigIssCsi0->bindParam(':vol1', $vol);
		$insertRigIssCsi0->bindParam(':cdCode', $cdCode);
		$insertRigIssCsi0->bindParam(':symId', $symId);
		if($insertRigIssCsi0->execute()){
			header("location: ../shareAuctionCSIUpdate.php?id=2");
			die();
		}else{
			header("location: ../shareAuctionCSIUpdate.php?id=400");
			die();
		}
	}else{
		$saveBidTemp = $dbh->prepare("INSERT INTO rights_bid_temp(cidNo, symbol_id, cd_code, bid_price, bid_vol, Secret, type) 
			VALUES ('$cidNo','$symId','$cdCode','$price', '$vol','0000','UPDATE')");
		$saveBidTemp->execute();

		/*$insertOnlTemp = $dbh->prepare("UPDATE rights_issue_online_temp r SET r.vol_applied=:vol, r.price=:pri, r.amount=:amt WHERE r.cd_code=:cdCode AND r.symbol_id=:symId");
		$insertOnlTemp->bindParam(':vol', $vol);
		$insertOnlTemp->bindParam(':pri', $price);
		$insertOnlTemp->bindParam(':amt', $totAmt);
		$insertOnlTemp->bindParam(':cdCode', $cdCode);
		$insertOnlTemp->bindParam(':symId', $symId);
		$insertOnlTemp->execute();*/

		$insertRigIssCsi = $dbh->prepare("UPDATE rights_issue r SET r.order_size=:vol, r.bid_price=:pri, r.total_amount=:amt, r.available_rights=:vol1 WHERE r.cd_code=:cdCode AND r.symbol_id=:symId");
		$insertRigIssCsi->bindParam(':vol', $vol);
		$insertRigIssCsi->bindParam(':pri', $price);
		$insertRigIssCsi->bindParam(':amt', $totAmt);
		$insertRigIssCsi->bindParam(':vol1', $vol);
		$insertRigIssCsi->bindParam(':cdCode', $cdCode);
		$insertRigIssCsi->bindParam(':symId', $symId);
		if($insertRigIssCsi->execute()){
			header("location: ../shareAuctionCSIUpdate.php?id=2");
			die();
		}else{
			header("location: ../shareAuctionCSIUpdate.php?id=400");
			die();
		}
	}
}
else
{
	header("location: ../shareAuctionCSI.php?id=1000");
	die();
}
?>