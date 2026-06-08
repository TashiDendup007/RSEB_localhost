<?php 
	die();
	include('CONNECTIONS/db.php');
	$getdtls_cc=$dbh->prepare("SELECT p.cd_code, p.name, p.email, p.phone, p.details, p.employee_id FROM rights_issue_online_temp p WHERE p.employee_id LIKE 'CC%' AND p.type='CS' AND p.client_acc_check=0 ORDER BY p.cd_code ASC -- limit 3");
	$getdtls_cc->execute();
	
	foreach($getdtls_cc as $row) {
		$cdCode = $row['cd_code'];
		$phone = $row['phone'];
		$email = $row['email'];
		$cidNo = $row['name'];
		$username = $row['employee_id'];
		$details = $row['details'];
		$splitDtls = explode("|", $details);

		$name=''; $bankName=''; $bankAccNo=0; $bfs_bank_id=0; $bank_id=0;
		for($i=0; $i<sizeof($splitDtls); $i++) {
			if ($i == 0) {
				$name=$splitDtls[$i];
			}
			if ($i == 1) {
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
			if ($i == 2) {
				$bankAccNo=$splitDtls[$i];
			}
		}

		$checkSql = $dbh->prepare("SELECT * FROM client_account c WHERE c.cd_code=:cdCode");
		$checkSql->bindParam(':cdCode', $cdCode);
		$checkSql->execute();
		if ($checkSql->rowCount()) {
			$updateAcc_Check=$dbh->prepare("UPDATE rights_issue_online_temp p SET p.client_acc_check=1 WHERE p.cd_code=:cd_code2");
			$updateAcc_Check->bindParam(":cd_code2", $cdCode);
			$updateAcc_Check->execute();

			echo'CD Code already existed = '.$cdCode.'<br>';
		} else {
			$insertSql = $dbh->prepare("INSERT INTO client_account(acc_type, cd_code, f_name, nationality, ID, DzongkhagID, phone, user_name, email, bank_id, bank_account, bro_comm_id, address, institution_id, occupation, bank_account_type) 
				VALUES ('I','$cdCode','$name','Bhutanese','$cidNo','1308','$phone','$username','$email', '$bank_id', '$bankAccNo', '37', '', '1', '101', 'Saving Account')");
			$insertSql->execute();
			
			$updateSql=$dbh->prepare("UPDATE rights_issue_online_temp p SET p.client_acc_check=1 WHERE p.cd_code=:cd_code1");
			$updateSql->bindParam(":cd_code1", $cdCode);
			$updateSql->execute();

			echo'Updated = '.$cdCode.'<br>';
		}
	}

?>