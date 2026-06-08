<?php
	echo "Please check the code before migrate to CDS Holding";
	die();

	//since migration is done from first issue , we are assuming that there is no record of this symbol in cds_holding , therefore we are directly inserting. else we might have to add to the volume
	date_default_timezone_set("Asia/Thimphu");
	include ('../../CONNECTIONS/db.php');

	$all = $dbh->prepare('
		SELECT 
			i.cd_code, i.allocated_size, i.user_name, p.institution_id, i.symbol_id 
		FROM bond i, adm_participants p 
		WHERE SUBSTRING(i.user_name,1,7) = p.participant_code 
		AND i.allocated_size != 0 
		AND i.status = 0
		AND i.symbol_id = 80
		ORDER BY i.allocated_size DESC
	');
	$all->execute();
	$n = 0;
	
	foreach($all as $srb) {
		$cd_code = $srb['cd_code'];
		$vol = $srb['allocated_size'];
		$username = $srb['user_name'];
		$institution_id = $srb['institution_id'];
		$symbol_id = $srb['symbol_id'];
		$type='Record First entered via BOND IPO of, '.$vol.' number of shares';
		//echo $cd_code, $vol, $username, $institution_id, $symbol_id, $type;

		$save = $dbh->prepare("INSERT into cds_holding(cd_code, volume, user_name, institution_id, symbol_id, remarks) VALUES('$cd_code','$vol','$username','$institution_id','$symbol_id','$type')");
		$save->execute();

		$status = $dbh->prepare('UPDATE bond set status=2 where status=1  and symbol_id=80 and cd_code=:cd');
		$status->bindParam(':cd', $cd_code);
		$status->execute();

	}

	echo "Yes done la";

?>