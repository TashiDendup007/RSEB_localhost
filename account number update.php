<?php
  die();
   // define database related variables
    date_default_timezone_set("Asia/Thimphu");
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

    //NEED TO ENTER THE SYMBOL ID MANUALLY
    $orders = $dbh->prepare('SELECT BANKSAVINGACCOUNT,IDENTIFICATION,BANK from accounts');
    $orders->execute();
    $i=0;
    $bank = 0;
    foreach($orders as $copy)
    {
    
      //$bnk = $copy['BANK'];
      if(trim($copy['BANK']) == "Bank of Bhutan")
      { 
        $bank = 2;
      }
      else if(trim($copy['BANK']) == "Bhutan National Bank")
      {
        $bank = 1;
      }
      else if(trim($copy['BANK']) == "Druk PNB Bank")
      {
        $bank = 4;
      }
      else if(trim($copy['BANK']) == "T Bank Ltd" || trim($copy['BANK']) =='T Bank Limited')
      {
        $bank = 5;
      }
      else
      {
        $bank = 3;
      }


      $account = $copy['BANKSAVINGACCOUNT'];
      $cid= $copy['IDENTIFICATION'];
      $i++;
      $shares = $dbh->prepare('UPDATE client_account set bank_id=:bankid,bank_account=:ba where ID=:cid');
      $shares->bindParam(':bankid',$bank);
      $shares->bindParam(':ba',$account);
      $shares->bindParam(':cid',$cid);
      $shares->execute();

      
    }
    echo 'success'+$i;


?>