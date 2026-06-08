<?php 
  include('sessionStartFile_admin.php'); 
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Market</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body">
                <?php 
                function compare($symbol_id)
                {
                  include ('../../CONNECTIONS/db.php');
                  $val = 0;
                  $pric = 0;
                  
                  $stmt = $dbh->prepare('SELECT price, SUM(sell_vol) AS stotal, SUM(buy_vol) AS btotal,
                      SUM(CASE WHEN price < :p THEN buy_vol ELSE 0 END) AS total_buy,
                      SUM(CASE WHEN price > :p THEN sell_vol ELSE 0 END) AS total_sell
                      FROM orders WHERE symbol_id=:sy AND order_size > 0
                      GROUP BY price ORDER BY price DESC');
                  $stmt->bindParam(':sy', $symbol_id);
                  
                  foreach ($stmt as $row) {
                      $bt = $row['btotal'] - $row['total_buy'];
                      $st = $row['stotal'] - $row['total_sell'];
                      if ($st <= $bt) {
                          $t = $st;
                      } else {
                          $t = $bt;
                      }
                      $val_n = $t;
                      $pric_n = $row['price'];
                      if ($val_n > $val) {
                          $val = $val_n;
                          $pric = $pric_n;
                      } elseif ($val_n == $val) {
                          if ($pric > $pric_n) {
                              $val = $val;
                              $pric = $pric;
                          } else {
                              $val = $val_n;
                              $pric = $pric_n;
                          }
                      } else {
                          $val = $val;
                          $pric = $pric;
                      }
                  }
                  return $pric;
                }

              $save = $dbh->prepare('SELECT distinct a.symbol_id , b.symbol, b.symbol_id from orders a,symbol b where a.symbol_id=b.symbol_id ');
              $save->execute();
               foreach($save as $symbol){
                $total = $dbh->prepare('SELECT 
                  sum(buy_vol) as buy_vol_total, sum(sell_vol) as sell_vol_total from orders where symbol=:sy');
                  $total->bindParam(':sy',$symbol['symbol']);
                  $total->execute();
                  $res= $total->fetch();
                    echo" <table class='table' style='background-color:#0d3c55; color: white;'>
                    <td><b>TOTAL BID</b></td>             
                    <td><b>BID VOL</b></td>
                    <td><b>PRICE</b></td>
                    <td><b>OFFER VOL</b></td>
                    <td><b>TOTAL OFFER</b></td>
                    <td><b>TRADABLE VOL.</b></td>";
                  $save = $dbh->prepare('SELECT distinct price from orders where symbol_id=:sy and order_size > 0 order by price DESC');
                  $save->bindParam(':sy',$symbol['symbol_id']);
                  $save->execute();
                  foreach($save as $price){
                    $q = $dbh->prepare('SELECT 
                        SUM(CASE WHEN buy_vol > 0 THEN buy_vol ELSE 0 END) AS buy_total,
                        SUM(CASE WHEN sell_vol > 0 THEN sell_vol ELSE 0 END) AS sell_total,
                        SUM(CASE WHEN buy_vol > 0 AND price < :p THEN buy_vol ELSE 0 END) AS total_buy,
                        SUM(CASE WHEN sell_vol > 0 AND price > :p THEN sell_vol ELSE 0 END) AS total_sell
                    FROM orders 
                    WHERE symbol_id = :sy');
                    $q->bindParam(':sy', $symbol['symbol_id']);
                    $q->bindParam(':p', $price['price']);
                    $q->execute();
                    $result = $q->fetch();
                    $btotal = $result['buy_total'];
                    $stotal = $result['sell_total'];
                    $total_buy = $result['total_buy'];
                    $total_sell = $result['total_sell'];

                    $bt = $btotal - $total_buy;
                    $st = $stotal - $total_sell;
                    if($st <= $bt){
                      $t = $st;
                    }elseif($bt <= $st){
                      $t=$bt;
                    }
                    $symbolt = $symbol['symbol_id'];
                    $symbolname = $symbol['symbol'];
                    $pric = compare($symbolt);
                    echo $pric;
                    echo $price['price'];
                    if($price['price']==$pric){
                      $save = $dbh->prepare(' SELECT  sum(buy_vol) as buy_vol_total from orders where side="B" and price=:p and symbol_id=:a');
                      $save->bindParam(':a', $symbol['symbol_id']);
                      $save->bindParam(':p', $price['price']);
                      $save->execute();
                      $row = $save->fetch();
                      echo"<tr class='blink' style=' background-color: #c8c8c8 ;'>";
                      echo"<td style=' color: black;'><b>".$bt."</b></td>";
                      echo"<td style=' color: black;'><b>".$row['buy_vol_total']."</td>";
                      echo"<td style=' color: black;' ><b><i><p >".$price['price']."</p></i></b></td>";

                      $save = $dbh->prepare(' SELECT sum(sell_vol) as sell_vol_total from orders where side="S" and price=:p and symbol_id=:a');
                      $save->bindParam(':a', $symbol['symbol_id']);
                      $save->bindParam(':p', $price['price']);
                      $save->execute();
                      while( $row1= $save->fetch()){
                        echo"<td style=' color: black;'><b>".$row1['sell_vol_total']."</td>";
                        echo"<td style=' color: black;'><b>".$st."</b></td>";
                        echo"<td style=' color: black;'><b>".$t."</b></td>";
                      } 
                      echo'</tr>';
                   }
                   else{
                   $save = $dbh->prepare('SELECT  sum(buy_vol) as buy_vol_total from orders where side="B" and price=:p and symbol_id=:a');
                   $save->bindParam(':a', $symbol['symbol_id']);
                   $save->bindParam(':p', $price['price']);
                   $save->execute();
                   $row = $save->fetch();
                   echo"<tr >";
                       echo"<td style=' color: white;'><b>".$bt."</b></td>";
                       if($price['price']>$pric && $row['buy_vol_total'] >0){
                       echo"<td style=' color: LightSkyBlue;'>".$row['buy_vol_total']."</td>";
                       }
                       else{
                        echo"<td style=' color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>";
                       }
                   echo"<td style=' color: Lime;'><b><i>".$price['price']."</i></b></td>";
                   $save = $dbh->prepare('SELECT sum(sell_vol) as sell_vol_total from orders where side="S" and price=:p and symbol_id=:a');
                   $save->bindParam(':a', $symbol['symbol_id']);
                   $save->bindParam(':p', $price['price']);
                   $save->execute();
                      while( $row1= $save->fetch()){
                       if($price['price']<$pric && $row1['sell_vol_total'] > 0 ){
                      echo"<td  style=' color: black;  background-color: #c8c8c8  ;'>".$row1['sell_vol_total']."</td>";
                       }
                       else{
                        echo"<td style=' color: Magenta; '><b>".$row1['sell_vol_total']."</td>";
                       }
                       echo"<td style=' color: white; '><b>".$st."</b></td>";
                       echo"<td style=' color: white;'><b>".$t."</b></td>";
                     }echo "</tr>";                      
                   }
                 }
                echo '<font style="font-weight: 900;""> '.$symbolname.'</font>';
                echo"</table>";
               }
              ?>
              </div>
            </div>
        </div>
      </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?> 
</body>
</html>
