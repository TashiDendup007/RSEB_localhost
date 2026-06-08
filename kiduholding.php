<?php
	date_default_timezone_set('Asia/Thimphu');
include('CONNECTIONS/db.php'); 

echo '</br> KIDU > </br>';
$wc= $dbh->prepare("SELECT cd_code,f_name FROM client_account WHERE f_name like '%kidu%'");
	    $wc->execute();


	    foreach($wc as $row){
	    	$wc= $dbh->prepare("SELECT * from cds_holding c, symbol s where cd_code =:cd_code and s.symbol_id=c.symbol_id and status=1 and trsstatus=1");
		    $wc->bindParam(':cd_code',$row['cd_code']);
		    $wc->execute();
		    foreach($wc as $val){

		     echo '<strong>'.$row['f_name'].'</strong> :SYMBOL -  '.$val['symbol']. ' : '.number_format($val['volume']).'</br>'; 
		    }
	    }

echo '</br> Sungchob > </br>';
	    $wc= $dbh->prepare("SELECT cd_code,f_name FROM client_account WHERE  f_name like '%sungchob%'");
	    $wc->execute();

	    foreach($wc as $row){
	    	$wc= $dbh->prepare("SELECT * from cds_holding c, symbol s where cd_code =:cd_code and s.symbol_id=c.symbol_id");
		    $wc->bindParam(':cd_code',$row['cd_code']);
		    $wc->execute();
		    $val=$wc->fetch();
		     echo '<strong>'.$row['f_name'].'</strong> :SYMBOL -  '.$val['symbol']. ' : '.number_format($val['volume']).'</br>'; 
	    }



?>