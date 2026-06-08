<?php
//die();
date_default_timezone_set("Asia/Thimphu");
   $database = 'cms2';
   $host = '192.168.10.100';
   $user = 'root';
   $pass = 'MkmCsop@289';
   $port='3306';
  $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   if(!$dbh)
   {
      echo "unable to connect to database";
   }


 	$wc= $dbh->prepare("SELECT * FROM rights_issue_online_temp WHERE type='AR'");
    $wc->bindParam(":symbol",$symbol);
    $wc->execute();
    $i=1;
    foreach($wc as $row){
$i++;
    	$wc= $dbh->prepare("UPDATE rights_issue_online_temp SET name=:name,cd_code=:cd, amount=:amt, symbol_id=:sid, vol_applied=:vol, price=:price WHERE bfs_orderid=:bfsid AND type='AC'");
    	$wc->bindParam(":sid",$row['symbol_id']);
    	$wc->bindParam(":cd",$row['cd_code']);
    	$wc->bindParam(":amt",$row['amount']);
      $wc->bindParam(":vol",$row['vol_applied']);
      $wc->bindParam(":price",$row['price']);
    	$wc->bindParam(":bfsid",$row['bfs_orderid']);
      $wc->bindParam(":name",$row['name']);
    	$wc->execute();
    }
    echo 'updated'.$i.'rows';
?>