<?php
	include ('CONNECTIONS/db.php');

    $sqlSelect="SELECT s.symbol_id, s.symbol, s.maturity_period, s.maturity_date, s.date_of_listing, s.status, s.trsstatus, s.security_type, s.coupon_payable, s.date_of_issue 
        FROM symbol s 
        WHERE s.status=1 AND s.trsstatus IN (1, 2) AND s.security_type IN ('GB', 'CB', 'CP') AND s.coupon_payable IS NOT NULL AND s.coupon_payable != 0 
        ORDER BY s.symbol_id";
    $sqlExe = $dbh->prepare($sqlSelect);
    $sqlExe->execute();
    //$res = $sqlExe->fetch();
    foreach ($sqlExe as $res) {
      $symbolId = $res['symbol_id'];
      $symbolName = $res['symbol'];
      $matPeriod=$res['maturity_period'];
      $cpnPayable=$res['coupon_payable'];
      $issueDate = $res['date_of_issue'];

      $couponDate = '';
      for($i=1; $i<=$matPeriod; $i++)
      {
        date_default_timezone_set("Asia/Thimphu");
        $date = new DateTime($issueDate);
        if($cpnPayable==1)
        {
          if($i==1){
            $date->modify('+1 year');
            $date->modify('-1 day');
          }else{
            $date->modify('+1 year');
          }
        }
        else if($cpnPayable == 2)
        {
          $date->modify('+6 month');
          if($i==1){
            $date->modify('-1 day');
          }else{
            if($i%2==0){
              $date->modify('+1 day');
            }else{
              $date->modify('-1 day');
            }
          }
        }
        
        /*if($cpnPayable==1)
        {
          $date->modify('+1 year');
        }
        else if($cpnPayable == 2)
        {
          $date->modify('+6 month');
        }
        else if($cpnPayable == 3)
        {
          $date->modify('+3 month');
        }
        $date->modify('-1 day');*/

        $couponDate = $date->format('Y-m-d');
        $issueDate = $couponDate;
        
        $insertSql = "INSERT INTO coupon_payable_date(symbol_id, symbol_name, payment_schedule, date, status) 
          VALUES('$symbolId', '$symbolName', '$i', '$couponDate', '0')";
        $insert = $dbh->prepare($insertSql);
        if($insert->execute())
        {
          echo 'INSERTED';
        }
        else{
          echo 'FAILED';
        }
      }
    }
?>