<?php 
	include('CONNECTIONS/db.php');

	die();

	$sql = "SELECT DISTINCT p.bfs_orderid FROM rights_issue_online_temp p WHERE p.type='AC' AND p.bfs_code='00' AND p.bfs_orderid LIKE 'SA%' 
		AND p.symbol_id=63
		AND p.client_acc_check=0 ORDER BY p.name ASC -- limit 10";
	$select=$dbh->prepare($sql);
	$select->execute();

	$n=0; $m=0;
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

			$name=''; $bankName=''; $bankAccNo=''; $bfs_bank_id=0; $bank_id=0;
			for($i=0; $i<sizeof($splitDtls); $i++)
			{
				if($i==0){
					$name=$splitDtls[$i];
				}
				if($i==1){
					//$bankName=$splitDtls[$i];
					$bfs_bank_id=$splitDtls[$i];
					if($bfs_bank_id==1010){//BOBL
						$bank_id=2;
					}else if($bfs_bank_id==1020){//BNBL
						$bank_id=1;
					}else if($bfs_bank_id==1030){//DPNB
						$bank_id=4;
					}else if($bfs_bank_id==1040){//TBank
						$bank_id=5;
					}else if($bfs_bank_id==1050){//DBDL
						$bank_id=3;
					}
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
				$updateAcc_Check=$dbh->prepare("UPDATE rights_issue_online_temp p SET p.client_acc_check=1 WHERE p.bfs_orderid=:bfs_orderid");
				$updateAcc_Check->bindParam(":bfs_orderid", $res['bfs_orderid']);
				$updateAcc_Check->execute();

				echo'rowCount='.$n++.' ,[CD Code already existed = '.$cdCode.']<br>';
			}else{
				$insertSql = $dbh->prepare("INSERT INTO client_account(acc_type, cd_code, f_name, nationality, ID, DzongkhagID, phone, user_name, email, bank_id, bank_account, bro_comm_id, address, institution_id, occupation, bank_account_type) 
					VALUES ('I','$cdCode','$name','Bhutanese','$id','1308','$phone','EMPRSEB009','$email', '$bank_id', '$bankAccNo', '37', '', '1', '101', 'Saving Account')");
				$insertSql->execute();
				
				$updateSql=$dbh->prepare("UPDATE rights_issue_online_temp p SET p.client_acc_check=1 WHERE p.bfs_orderid=:bfs_orderid22");
				$updateSql->bindParam(":bfs_orderid22", $res['bfs_orderid']);
				$updateSql->execute();

				echo'rowCount='.$m++.' ,[Updated = '.$cdCode.']<br>';
			}
		}

	}

?>