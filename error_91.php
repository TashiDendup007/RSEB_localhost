<?php
error_reporting(1);
date_default_timezone_set('Asia/Thimphu');
include('CONNECTIONS/db.php'); 

$holdingbackup=$dbh->prepare('SELECT * FROM  pg_error_91_share_auction WHERE status="0"');
$holdingbackup->execute();
foreach($holdingbackup as $row){
	$bfs_orderNo=$row['bfs_orderNo'];
	$bfs_txnAmount=$row['bfs_txnAmount'];
	$bfs_msgType=$row['bfs_msgType'];
	$bfs_benfId=$row['bfs_benfId'];
	$bfs_debitAuthCode=$row['bfs_debitAuthCode'];

	$holdingbackup=$dbh->prepare("SELECT * FROM  rights_issue_online_temp WHERE bfs_orderid=:bfs_orderNo and bfs_code='91'");
	$holdingbackup->bindParam(':bfs_orderNo',$bfs_orderNo);
    $holdingbackup->execute();
    $data=$holdingbackup->fetch();
    if($data['bfs_orderid']==$bfs_orderNo){
    	        //$url = "https://192.168.20.100/api/v1/rseb_resource.php";
				$url = "https://bhutancrowdfunding.rsebl.org.bt/CFDP/INVESTOR/PRO/pg_91.php";
			    $headers = array(
			        'Authorization: ThisIsAStaticToken123@123'
			    );

			    $curl = curl_init($url);
			    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			    curl_setopt($curl, CURLOPT_POST, 1);
			    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			    curl_setopt($curl, CURLOPT_POSTFIELDS, "bfs_orderNo=".$data['bfs_orderid']."&bfs_txnAmount=".$bfs_txnAmount."&bfs_msgType=".$bfs_msgType."&bfs_debitAuthCode=".$bfs_debitAuthCode);
			    //curl_setopt($curl, CURLOPT_HEADER, true);
			    $curl_response = curl_exec($curl);
			    $http_code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			    curl_close($curl);
			    //echo $curl_response;
			    if ($http_code == 400) {
			    	echo $http_code;
			    } else if ($http_code == 200) {
			    	echo "updated:--".$i++.'--->'.$data['bfs_orderid'];
			    	$holdingbackup=$dbh->prepare("UPDATE pg_error_91_share_auction SET status='1' WHERE bfs_orderNo=:bfs_orderNo");
					$holdingbackup->bindParam(':bfs_orderNo',$data['bfs_orderid']);
				    $holdingbackup->execute();
			    }
    }		   
}
?>