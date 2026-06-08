<?php 
	include('CONNECTIONS/db.php');

	die();

	$sql = "SELECT * from rights_issue_online_temp where type='AC' and bfs_code='00' and client_acc_check=0 ";
	$select=$dbh->prepare($sql);
	$select->execute();

	$i=1;

	foreach($select as $res)
	{
		$getDtls=$dbh->prepare("SELECT p.cd_code, p.name cidNo, p.email, p.phone, p.details FROM rights_issue_online_temp p WHERE p.bfs_orderid=:bfsOrderNo AND p.type='AR' AND p.details IS NOT NULL LIMIT 1");
		$getDtls->bindParam(':bfsOrderNo', $res['bfs_orderid']);
		$getDtls->execute();
		$row = $getDtls->fetch();

		if(isset($row['cd_code']))
		{
			$id = $row['cidNo'];
			$phone = $row['phone'];
			$email = $row['email'];
			$cdCode = $row['cd_code'];
			$details = $row['details'];
			$splitDtls = explode("|", $details);

			$name=''; $bankName=''; $bankAccNo='';
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
			}

			$checkSql = $dbh->prepare("SELECT * FROM client_account c WHERE c.cd_code=:cdCode");
			$checkSql->bindParam(':cdCode', $cdCode);
			$checkSql->execute();
			if($checkSql->rowCount())
			{
				echo'CD Code already existed = '.$cdCode.'<br>';
			}else{
				$insertSql = $dbh->prepare("INSERT INTO client_account(acc_type, cd_code, f_name, nationality, ID, DzongkhagID, phone, user_name, email, bank_id, bank_account, bro_comm_id, address, institution_id, occupation, bank_account_type) 
					VALUES ('I','$cdCode','$name','Bhutanese','$id','1308','$phone','EMPRSEB009','$email', '0', '$bankAccNo', '0', 'NULL', '1', '0', 'Saving Account')");
				$insertSql->execute();
				
				$updateSql=$dbh->prepare("UPDATE rights_issue_online_temp p SET p.client_acc_check=1 WHERE p.cd_code=:cd_code1");
				$updateSql->bindParam(":cd_code1", $cdCode);
				$updateSql->execute();

				echo'Updated = '.$cdCode.'<br>';
			}
		}
		$i++;


	}

	echo $i.'records';

?>