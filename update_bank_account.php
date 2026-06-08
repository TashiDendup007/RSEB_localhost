<?php 
	include('CONNECTIONS/db.php');
	
	$getdtls_cc = $dbh->prepare("SELECT 
		a.cd_code, a.ID
		FROM client_account a 
		where a.bank_account is null or a.bank_account = ''
	");
	$getdtls_cc->execute();
	$i=1;
	foreach($getdtls_cc as $row)
	{

		$getDtlsFromTemp=$dbh->prepare("
			SELECT 
			a.cd_code, a.ID, a.bank_id, a.bank_account
			FROM client_account a 
			where a.ID = :id
			and a.bank_account != '' and a.bank_account is not null
			order by a.client_id desc limit 1 
		");
		$getDtlsFromTemp->bindParam(':id', $row['ID']);
		$getDtlsFromTemp->execute();
		$res = $getDtlsFromTemp->fetch();

		if($res){
			$updateClientAcc = $dbh->prepare("UPDATE client_account a SET a.bank_account = :bank_acc, a.bank_id = :bank_id where a.ID = :id");
			$updateClientAcc->bindParam(':bank_acc', $res['bank_account']);
			$updateClientAcc->bindParam(':bank_id', $res['bank_id']);
			$updateClientAcc->bindParam(':id', $res['ID']);
			$updateClientAcc->execute();
		}

		echo $i.') Updated = '.$i++;
		echo'<br>';
	}

?>