<!-- <?php
   // define database related variables
   $database = 'cms2';
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

   $project = $dbh->prepare("SELECT * from cds_holding where volume < 0");
          $project->execute();
          
          foreach($project as $pro){
          	$id=$pro['cds_holding_id'];
          	$new_pld=$pro['pledge_volume']+$pro['volume'];
            $re=$pro['remarks'].'- Scripts ran after inconsistency from buy back';
          	  $save = $dbh->prepare("UPDATE cds_holding set volume=0,pledge_volume=:new,remarks=:re where cds_holding_id=:id");
          	  $save->bindParam(':new',$new_pld);
          	   $save->bindParam(':id',$id);
               $save->bindParam(':re',$re);
               $save->execute();
          }

?>
 -->