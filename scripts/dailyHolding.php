<?php
//TODO : 1. Change production DB 2. Scheduelar to run at 11PM monday to friday
// DONE : 1. table has already been created in production
$database = 'cms2';
$host = '192.168.10.100';
$user = 'root';
$pass = 'MkmCsop@289';
$port='3306';

try
{
	$dbh= new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
}
catch(PDOException $e){

	die();
}
$date=date('Y/m/d');

$fetchSymbol = $dbh->prepare("SELECT distinct(symbol_id)  as ID,symbol,security_type FROM symbol where status NOT IN (2,0) and security_type IN ('OS','CB','GB','CP')");
$fetchSymbol->execute();
$result = $fetchSymbol->fetchALL();

foreach ($result as  $value) {
	$query = $dbh->prepare("SELECT count(cd_code) as count, symbol_id FROM cds_holding WHERE symbol_id=:id AND volume+pending_in_vol+pending_out_vol+pledge_volume+block_volume != 0 ");
	$query->BindParam('id',$value['ID']);
	if($query->execute()){ 
		$res = $query->fetchAll();
		$counter =$res[0]['count'];
		$symbol=$value['symbol'];
		$security_type=$value['security_type'];
		//$symbol =$res[0]['symbol_id'];
		
		$sql = $dbh->prepare("INSERT INTO daily_share_holding_pattern(symbol,security_type,numbers,holding_date) VALUES('$symbol', '$security_type','$counter','$date')");
		$sql->execute();	
	}
	else{
		echo "No";
	}


}

//completed
?>