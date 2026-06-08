<?php
   // define database related variables
/*   $database = 'cms2';
   $host = '192.168.10.100';
   $user = 'root';
   $pass = 'MkmCsop@289';
   $port='3306';
   // try to connect to database
  $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   if(!$dbh)
   {
      echo "unable to connect to database";
   }


    $orders = $dbh->prepare('SELECT * from cds_holding_copy where symbol_id=13 order by cds_holding_id DESC');
    $orders->execute();
    foreach($orders as $copy)
    {
      $shares = $dbh->prepare('UPDATE cds_holding set volume=:v,pledge_volume=:p,block_volume=:b,pending_in_vol=:pi,pending_out_vol=:po where cds_holding_id=:cd');
      //$shares = $dbh->prepare('INSERT into cds_holding set (volume,pledge_volume,block_volume,pending_in_vol,pending_out_vol) values (:v,:p,:b,:pi,:po)');
      $shares->bindParam(':v',$copy['volume']);
      $shares->bindParam(':p',$copy['pledge_volume']);
      $shares->bindParam(':b',$copy['block_volume']);
      $shares->bindParam(':pi',$copy['pending_in_vol']);
      $shares->bindParam(':po',$copy['pending_out_vol']);
      $shares->bindParam(':cd',$copy['cds_holding_id']);
      $shares->execute();
    }
    echo "sucess";*/
