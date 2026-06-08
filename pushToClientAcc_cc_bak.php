<?php 
	include('CONNECTIONS/db.php');

	die();
	
	$getdtls_cc=$dbh->prepare("SELECT p.cd_code, p.name, p.email, p.phone, p.details FROM rights_issue_online_temp p WHERE p.cd_code LIKE 'CS%' AND p.type='CS' AND p.payment_status='PE' AND p.client_acc_check=0 ORDER BY p.cd_code ASC  limit 600");
	$getdtls_cc->execute();
	$i=1;
	
	foreach($getdtls_cc as $row)
	{
		$cdCode = $row['cd_code'];
		$phone = $row['phone'];
		$email = $row['email'];
		$username = $row['name'];
		$details = $row['details'];
		$splitDtls = explode("|", $details);

		$name=''; $bankName=''; $bankAccNo=0; $cidNo=0;
		for($i=0; $i<sizeof($splitDtls); $i++)
		{
			if($i==0){
				$name=$splitDtls[$i];
			}
			if($i==1){
				$bankName=$splitDtls[$i];
			}
			if($i==2){
				$bankAccNo=$splitDtls[$i];
			}
			if($i==6){
				$cidNo=$splitDtls[$i];
			}
		}

		$checkSql = $dbh->prepare("SELECT * FROM client_account c WHERE c.cd_code=:cdCode");
		$checkSql->bindParam(':cdCode', $cdCode);
		$checkSql->execute();
		if($checkSql->rowCount())
		{
			echo'CD Code already existed = '.$cdCode.'<br>';
		}else{
			$insertSql = $dbh->prepare("INSERT INTO client_account(acc_type, cd_code, f_name, nationality, ID, DzongkhagID, phone, user_name, email, bank_id, bank_account, bro_comm_id, address, institution_id, occupation, bank_account_type) 
				VALUES ('I','$cdCode','$name','Bhutanese','$cidNo','1308','$phone','$username','$email', '0', '$bankAccNo', '0', 'NULL', '1', '0', 'Saving Account')");
			$insertSql->execute();
			
			$updateSql=$dbh->prepare("UPDATE rights_issue_online_temp p SET p.client_acc_check=1 WHERE p.cd_code=:cd_code1");
			$updateSql->bindParam(":cd_code1", $cdCode);
			$updateSql->execute();

			echo'Updated = '.$cdCode.'<br>';
		}
		$i++;
	}

	echo $i.'records';

?>