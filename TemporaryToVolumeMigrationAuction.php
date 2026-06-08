<?php

   die();

   include('CONNECTIONS/db.php'); 
   /*echo "start".time();*/ 
   //UPDATE VOLUME FROM TEMPORARY

    $all = $dbh->prepare("SELECT * FROM cds_holding where symbol_id=63 AND temporary_volume > 0");
    $all->execute();
    $n=0;
    $vol_migrated=0;
    foreach($all as $srb)
    {

       $save = $dbh->prepare("UPDATE cds_holding SET volume=volume+:temporary_volume, temporary_volume=0  where cd_code=:cd_code and symbol_id=:sym_id ");
       $save->bindParam(':temporary_volume',$srb['temporary_volume']);
       $save->bindParam(':cd_code', $srb['cd_code']);
       $save->bindParam(':sym_id', $srb['symbol_id']);
       $save->execute();
           
       echo $n++." : Yes done la</br>";
       $vol_migrated =$vol_migrated+$srb['temporary_volume'];
    }
    echo $vol_migrated;
?>