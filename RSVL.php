<?php
die();
$database = 'cms2';
$host = '192.168.10.100';
$user = 'root';
$pass = 'MkmCsop@289';
$port = '3306';
// try to connect to database
/*  $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
if(!$dbh)
{
echo "unable to connect to database";
}*/
try
{
	$dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
} catch (PDOException $e) {
	//echo $e->getMessage();
	//echo "<h2> Hi, There seems to be an issue with the Application. Please contact RSEB.</h2>";
	die();
}

$sql = $dbh->prepare("SELECT sdh_id,sum(ribon_volume) as TOTAL,volume,a.ID,a.cd_code,a.f_name,a.bank_account
						FROM  spot_date_holding s, client_account a 
						WHERE s.corp_announcement_id=66 
						AND s.ribon_volume > 0
						AND s.client_id=a.client_id
						Group BY a.ID order by TOTAL desc");
$sql->execute();
$i = 1;
foreach ($sql as $res) {
	$sql1 = $dbh->prepare("SELECT sum(order_size) as done,cd_code FROM rights_issue r WHERE r.symbol_id=63 AND  cid_no=:cd and r.type in ('S','R') group by cid_no");
	$sql1->bindParam(':cd',$res['ID']);
	$sql1->execute();
	echo $i.','.$res['f_name'].','.$res['ID'].','.$res['cd_code'] . ',' . $res['TOTAL'] . ',' . $res['bank_account'] .',';
	 if($count = $sql1->rowCount()){
	 	foreach($sql1 as $row){
	 		echo $row['done'].'</br>';
	 	}
	 }else{
	 	echo '0 </br>';
	 }
	

	$i++;
}


?>