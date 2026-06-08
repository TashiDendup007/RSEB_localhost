<?php 
	include('CONNECTIONS/db.php');
	
	die();

	/*$getdtls_cc=$dbh->prepare("SELECT  DISTINCT p.cd_code, p.email, p.phone, p.name cid
			FROM rights_issue_online_temp p 
			WHERE p.cd_code NOT LIKE 'SA%' AND p.cd_code NOT LIKE 'CS%' AND p.type='AR' AND p.email !='' AND p.phone != '' AND p.cd_code != ''
			GROUP BY p.cd_code");*/

	$getdtls_cc=$dbh->prepare("SELECT r.cd_code, r.cid_no FROM rights_issue r where r.user_name not LIKE 'CC%' AND r.user_name not LIKE 'MEM%' AND r.email_status=0");
	$getdtls_cc->execute();
	$i=1;
	foreach($getdtls_cc as $row)
	{

		$getDtlsFromTemp=$dbh->prepare("SELECT p.cd_code, p.phone, p.email, 
			SUBSTRING_INDEX(SUBSTRING_INDEX(p.details, '|', 2), '|', -1) AS bank_id,
			SUBSTRING_INDEX(SUBSTRING_INDEX(p.details, '|', 3), '|', -1) AS acc_no 
			FROM rights_issue_online_temp p where p.email != '' and p.details != '' and p.cd_code=:cdCode");
		$getDtlsFromTemp->bindParam('cdCode', $row['cd_code']);
		$getDtlsFromTemp->execute();
		$res = $getDtlsFromTemp->fetch();

		$cdCode = $res['cd_code'];
		$phone = $res['phone'];
		$email = $res['email'];
		$bank_acc = $res['acc_no'];
		//$cidNo = $res['cid'];

		$bankId=0;
		if($res['bank_id']==1010){//bob
			$bankId=2;
		}else if($res['bank_id']==1020){ //bnb
			$bankId=1;
		}else if($res['bank_id']==1030){//dpnb
			$bankId=4;
		}else if($res['bank_id']==1040){//tbank
			$bankId=5;
		}else if($res['bank_id']==1050){//bdb
			$bankId=3;
		}

		$updateClientAcc = $dbh->prepare("UPDATE client_account a SET a.email=:email, a.phone=:phone, a.bank_id=:bankId, a.bank_account=:bank_acc WHERE a.cd_code=:cdCode");
		$updateClientAcc->bindParam(':email', $email);
		$updateClientAcc->bindParam(':phone', $phone);
		$updateClientAcc->bindParam(':bankId', $bankId);
		$updateClientAcc->bindParam(':bank_acc', $bank_acc);
		$updateClientAcc->bindParam(':cdCode', $cdCode);
		$updateClientAcc->execute();
		
		echo $i.') Updated = '.$cdCode.'<br>';
		$i++;
	}

?>