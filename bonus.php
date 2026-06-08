<?php
   // die();
   // define database related variables
   date_default_timezone_set("Asia/Thimphu");
   $database = 'cms2';
   $host = 'localhost';
   $user = 'root';
   $pass = 'root';
   $port='3306';
   
   /* $database = 'cms3';
   $host = 'localhost';
   $user = 'root';
   $pass = 'root';
   $port='3306';*/
   // try to connect to database
   $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   if(!$dbh) {
      echo "unable to connect to database"; 
      die();
   }

   //NEED TO ENTER THE SYMBOL ID MANUALLY
   $orders= $dbh->prepare('SELECT DISTINCT s.sdh_id, h.cd_code, s.volume, s.ribon_volume 
      FROM spot_date_holding s, client_account c, cds_holding h 
      where s.symbol_id = 1
      and s.announcement_type = 2
      and s.volume != 0 
      and s.status = 1
      and s.client_id = c.client_id
      and c.cd_code = h.cd_code
      and s.corp_announcement_id = 103
   ');
   $orders->execute();
   $orders = $orders->fetchAll(PDO::FETCH_ASSOC);
   $old = 0;
   $new = 0;
   foreach ($orders as $copy) {
      //echo $copy['cd_code'] . '- '. $copy['ribon_volume']. '- '. $copy['volume'].'</br>';   
      $shares= $dbh->prepare('UPDATE cds_holding set volume=volume+:ribon WHERE cd_code=:cd and symbol_id = 1');
      //$shares->bindParam(':sdhid',$copy['sdh_id']);
      $shares->bindParam(':ribon',$copy['ribon_volume']);
      $shares->bindParam(':cd',$copy['cd_code']);
      $shares->execute();
      $old += $copy['volume'];
      $new += $copy['ribon_volume'];
   }
   $updated = $old + $new;
   echo 'old PUS - '.$old.'</br>';
   echo 'bonus PUS - '.$new.'</br>';
   echo '</br> new PUS - '.$updated;
   echo "success";

   // update status as 0 
   $update = $dbh->prepare("UPDATE spot_date_holding h SET h.status = 0 WHERE h.corp_announcement_id = 103 and h.symbol_id = 1");
   $update->execute();

?>