<?php
	echo "Please click on Corporate Action Process to update Rights Issue in CDS Holding";
	die();

	include ('../../CONNECTIONS/db.php');

	$all = $dbh->prepare("SELECT * FROM rights_issue where symbol_id=18 where status=1");
	$all->execute();
	$n = 0;
	$vol_migrated = 0;

	foreach ($all as $srb) {
		$allcds = $dbh->prepare('SELECT c.cd_code,c.volume,c.symbol_id from cds_holding c where c.cd_code=:cd and c.symbol_id=:sid');
		$allcds->bindParam(':cd',$srb['cd_code']);
		$allcds->bindParam(':sid',$srb['symbol_id']);
		$allcds->execute();
		$ree = $allcds->fetch();

		$cd_code = $srb['cd_code'];
		$allocated = $srb['allocated_size'];
		$username = $srb['user_name'];
		$institution_id = $srb['institution_id'];
		$symbol_id = $srb['symbol_id'];
		$remarks = 'Record First entered via Offer of sale, '.$vol.' number of shares';
			
		if($allcds->rowCount() > 0)
		{
			$save = $dbh->prepare("UPDATE cds_holding SET temporary_volume=:temporary_volume  where cd_code=:cd_code and symbol_id=:sym_id ");
			$save->bindParam(':temporary_volume',$allocated);
			$save->bindParam(':cd_code', $cd_code);
			$save->bindParam(':sym_id', $symbol_id);
			$save->execute();
		} else {
			$save = $dbh->prepare("INSERT INTO cds_holding(cd_code,temporary_volume,user_name,institution_id,symbol_id, remarks) VALUES(?, ?, ?, ?, ?, ?)");
			$save->bindParam(1, $cd_code);
			$save->bindParam(2, $vol);
			$save->bindParam(3, $username);
			$save->bindParam(4, $institution_id);
			$save->bindParam(5, $symbol_id);
			$save->bindParam(6, $remarks);
			$save->execute();
		}

		$updateri = $dbh->prepare("UPDATE rights_issue SET status=0 where symbol_id=:sym_id and order_id=:order ");
        $updateri->bindParam(':sym_id', $symbol_id);
        $updateri->bindParam(':order', $srb['order_id']);
        $updateri->execute();

        echo $n++." : Yes done la</br>";
        $vol_migrated =$vol_migrated+$srb['allocated_size'];
	}

	echo $vol_migrated;
?>