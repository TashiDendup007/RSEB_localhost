<?php
	echo "update from IPO allocation at Process menu";
	die();
	include ('../../CONNECTIONS/db.php');

	$all = $dbh->prepare("SELECT i.cd_code, i.allocated_size, i.user_name, p.institution_id, i.symbol_id 
		FROM ipo i 
		JOIN adm_participants p ON SUBSTRING(i.user_name,1,7) = p.participant_code 
		WHERE 
			i.allocated_size != 0 
			AND i.status = 1 
		ORDER BY 
			i.allocated_size DESC
	");
	$all->execute();
	$n = 0;

	foreach($all as $srb) {
		$cd_code = $srb['cd_code'];
		$vol = $srb['allocated_size'];
		$username = $srb['user_name'];
		$institution_id = $srb['institution_id'];
		$symbol_id = $srb['symbol_id'];
		$remark = 'Record First entered via IPO of, '.$vol.' number of shares';

		$save = $dbh->prepare("INSERT INTO cds_holding(cd_code, volume, user_name, institution_id, symbol_id, remarks) VALUES(?, ?, ?, ?, ?, ?)");
		$save->bindParam(1, $cd_code);
		$save->bindParam(2, $vol);
		$save->bindParam(3, $username);
		$save->bindParam(4, $institution_id);
		$save->bindParam(5, $symbol_id);
		$save->bindParam(6, $remark);
	    $save->execute();

	}
	echo "Yes done la";
?>