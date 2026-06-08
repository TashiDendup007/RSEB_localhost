<?php
die("Do not run the script");
//script to affect buyback holding in cds_hoilding
// define database related variables
   $database = 'cms2';
$host = '192.168.10.100';
$user = 'root';
$pass = 'MkmCsop@289';
$port='3306';


// try to connect to database
$dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
if (!$dbh) {
	echo "unable to connect to database";
}

$project = $dbh->prepare("SELECT c.cd_code,s.ribon_volume, cds.volume,cds.pledge_volume from spot_date_holding s, client_account c , cds_holding cds
where s.corp_announcement_id=71 and c.client_id=s.client_id and cds.cd_code=c.cd_code and s.symbol_id=cds.symbol_id and s.ribon_volume > 0 and s.status=1");
$project->execute();
$flag = 0;
$i = 1;

foreach ($project as $pro) {
	// condition for client having enough volume
	if ($pro['volume'] > $pro['ribon_volume']) {
		$flag = 1;

		$new_volume = $pro['volume'] - $pro['ribon_volume'];
		$remarks = 'buyback of from Vol ' . date('Y-m-d') . ' - ' . $pro['ribon_volume'];

		$save = $dbh->prepare("UPDATE cds_holding set volume=:new_volume,remarks=:remarks where cd_code=:cd and symbol_id=9");
		$save->bindParam(':new_volume', $new_volume);
		$save->bindParam(':cd', $pro['cd_code']);
		$save->bindParam(':remarks', $remarks);
		$save->execute();
	}

	if ($pro['volume'] < $pro['ribon_volume'] AND $pro['pledge_volume'] > $pro['ribon_volume']) {
		$flag = 1;

		$new_volume = $pro['pledge_volume'] - $pro['ribon_volume'];
		$remarks = 'buyback of from pledgeVol ' . date('Y-m-d') . ' - ' . $pro['ribon_volume'];

		$save = $dbh->prepare("UPDATE cds_holding set pledge_volume=:new_volume,remarks=:remarks where cd_code=:cd and symbol_id=9");
		$save->bindParam(':new_volume', $new_volume);
		$save->bindParam(':cd', $pro['cd_code']);
		$save->bindParam(':remarks', $remarks);
		$save->execute();

	}

	if ($pro['volume'] < $pro['ribon_volume'] AND $pro['pledge_volume'] < $pro['ribon_volume'] AND $pro['volume'] > 0 AND $pro['pledge_volume'] > 0) {
		$flag = 1;

		$new_volume = $pro['volume'] - $pro['ribon_volume'];
		if ($new_volume < 0) {
			$negative_adjustment = $new_volume * -1;
			$new_volume = 0;
			$pledge_volume = $pro['pledge_volume'] - $negative_adjustment;
		}

		if ($pledge_volume < 0) {
			echo 'Issues with this accounts: ' . $pro['cd_code'] . 'Vol : ' . $pro['volume'] . ' / Pl_vol : ' . $pro['pledge_volume'] . '</br>';
		}

		$remarks = 'buyback of from pledgeVol/Vol ' . date('Y-m-d') . ' - ' . $pro['ribon_volume'];

		$save = $dbh->prepare("UPDATE cds_holding set pledge_volume=:pledge_volume,volume=0,remarks=:remarks where cd_code=:cd and symbol_id=9");
		$save->bindParam(':pledge_volume', $pledge_volume);
		$save->bindParam(':cd', $pro['cd_code']);
		$save->bindParam(':remarks', $remarks);
		$save->execute();
	}

	if ($flag == 1) {
		echo 'Issues with this accounts: ' . $pro['cd_code'] . '</br>';
	}

	$flag = 0;
	$i++;

}

// UPDATE spot_date_holding s set status=0 where  s.corp_announcement_id=70 and symbol_id=13 
echo $i . 'Records';

?>