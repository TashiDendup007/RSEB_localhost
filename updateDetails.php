<?php
date_default_timezone_set("Asia/Thimphu");
include ('CONNECTIONS/db.php');
$orders = $dbh->prepare('SELECT * from rights_issue_online_temp where type="AR" AND client_acc_check=0');
$orders->execute();

   foreach($orders as $row){
      echo $row['bfs_orderid'].'/'.$row['email'].'/'.$row['name'].'/'.$row['symbol_id'].'</br>';

    $wc= $dbh->prepare("UPDATE rights_issue_online_temp SET name=:name,cd_code=:cd, symbol_id=:sid,email=:email WHERE bfs_orderid=:bfsid AND type='AC'");
      $wc->bindParam(":sid",$row['symbol_id']);
      $wc->bindParam(":cd",$row['cd_code']);
      $wc->bindParam(":bfsid",$row['bfs_orderid']);
      $wc->bindParam(":name",$row['name']);
      $wc->bindParam(":email",$row['email']);
      $wc->execute();


       $wc= $dbh->prepare(" UPDATE rights_issue_online_temp set client_acc_check=1 where type = 'AR' ");
       $wc->execute();
    }