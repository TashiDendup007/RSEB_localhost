<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Order</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-body table-responsive">
                <?php 
                  function compare($symbol_id, $buy_total_order, $sell_total_order) {
                    include ('../../CONNECTIONS/db.php');
                    $val = 0;
                    $pric = 0;
                    $btotal = $buy_total_order;
                    $stotal = $sell_total_order;

                    $save = $dbh->prepare('SELECT DISTINCT price FROM orders WHERE symbol_id=:sy AND order_size > 0 ORDER BY price DESC');
                    $save->bindParam(':sy', $symbol_id);
                    $save->execute();

                    foreach($save as $price) {
                      // fetch order of the symbol at specific price
                      $total = $dbh->prepare("
                        SELECT 
                          sum(CASE WHEN sell_vol > 0 AND price > :p THEN sell_vol ELSE 0 END) as total_sell,
                          sum(CASE WHEN buy_vol > 0 AND price < :p THEN buy_vol ELSE 0 END) as total_buy
                        FROM orders WHERE symbol_id=:sy
                      ");
                      $total->bindParam(':sy', $symbol_id);
                      $total->bindParam(':p', $price['price']);
                      $total->execute();
                      $row = $total->fetch();
                      $total_sell = $row['total_sell'];
                      $total_buy = $row['total_buy'];

                      $bt = $btotal - $total_buy;
                      $st= $stotal - $total_sell;

                      if($st <= $bt){
                        $t = $st;
                      } elseif ($bt <= $st){
                        $t = $bt;
                      }
                      $val_n = $t;
                      $pric_n = $price['price'];

                      if($val_n > $val){
                          $val = $val_n;
                          $pric = $pric_n;
                      }elseif($val_n = $val){
                        if($pric > $pric_n){
                          $val = $val;
                          $pric = $pric;
                        }elseif($pric < $pric_n){
                          $val = $val_n;
                          $pric = $pric_n;
                        }elseif($pric = $pric_n){
                          $val = $val_n;
                          $pric = $pric_n;
                        }
                      }else{
                        $val = $val;
                        $pric = $pric; 
                      }
                    }
                    return $pric; 
                  }

                $stmt = $dbh->prepare('SELECT DISTINCT a.symbol_id, b.symbol, b.symbol_id FROM orders a, symbol b WHERE a.symbol_id = b.symbol_id');
                $stmt->execute();
                $symbols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($symbols as $symbol) {
                  // fetch total buy and sell order of the symbol
                  $order_totals = $dbh->prepare('SELECT 
                        sum(CASE WHEN buy_vol > 0 THEN buy_vol ELSE 0 END) as buy_totals,
                        sum(CASE WHEN sell_vol > 0 THEN sell_vol ELSE 0 END) as sell_totals
                      FROM orders 
                      WHERE symbol_id = :sy
                  ');
                  $order_totals->bindParam(':sy', $symbol['symbol_id']);
                  $order_totals->execute();
                  $totals = $order_totals->fetch();
                  $buy_total_order = $totals['buy_totals'];
                  $sell_total_order = $totals['sell_totals'];

                  $total = $dbh->prepare('SELECT sum(buy_vol) as buy_vol_total, sum(sell_vol) as sell_vol_total FROM orders WHERE symbol_id = :sy');
                  $total->bindParam(':sy', $symbol['symbol_id']);
                  $total->execute();
                  $res= $total->fetch();
                  echo"
                  <table class='table' style='background-color:#0d3c55; color: white;'>
                    <thead>
                      <tr>
                        <td><b>TOTAL BID</b></td>
                        <td><b>BID VOL</b></td>
                        <td><b>PRICE</b></td>
                        <td><b>OFFER VOL</b></td>
                        <td><b>TOTAL OFFER</b></td>
                        <td><b>TRADABLE VOL</b></td>
                      </tr>
                    </thead>
                    <tbody>";
                    $save = $dbh->prepare('SELECT DISTINCT price FROM orders WHERE symbol_id = :sy and order_size > 0 ORDER BY price DESC');
                    $save->bindParam(':sy', $symbol['symbol_id']);
                    $save->execute();
                    foreach($save as $price){
                      // fetch total orders at specific price
                      $total = $dbh->prepare('
                          SELECT 
                            sum(CASE WHEN buy_vol > 0 AND price < :p THEN buy_vol ELSE 0 END) as total_buy,
                            sum(CASE WHEN sell_vol > 0 AND price > :p THEN sell_vol ELSE 0 END) as total_sell
                          FROM orders WHERE symbol_id=:sy
                      ');
                      $total->bindParam(':p', $price['price']);
                      $total->bindParam(':sy',$symbol['symbol_id']);
                      $total->execute();
                      $orders =  $total->fetch();
                      $total_buy=$orders['total_buy'];
                      $total_sell=$orders['total_sell'];

                      $bt = $buy_total_order - $total_buy;
                      $st = $sell_total_order - $total_sell;

                      if ($st <= $bt) {
                        $t = $st;
                      } elseif($bt <= $st) {
                        $t = $bt;
                      }

                      $symbolt = $symbol['symbol_id'];
                      $symbolname = $symbol['symbol'];
                      // to compare the prices
                      $pric = compare($symbolt, $buy_total_order, $sell_total_order);

                      if($price['price'] == $pric){
                        $save = $dbh->prepare('SELECT sum(buy_vol) as buy_vol_total FROM orders WHERE side="B" AND price=:p AND symbol_id=:a');
                        $save->bindParam(':a', $symbol['symbol_id']);
                        $save->bindParam(':p', $price['price']);
                        $save->execute();
                        $row = $save->fetch();
                        echo"
                        <tr class='blink' style=' background-color: #c8c8c8;'>
                          <td style=' color: black;'><b>".$bt."</b></td>
                          <td style=' color: black;'><b>".$row['buy_vol_total']."</td>
                          <td style=' color: black;' ><b><i><p >".$price['price']."</p></i></b></td>";

                          $save = $dbh->prepare('SELECT sum(sell_vol) as sell_vol_total FROM orders WHERE side="S" AND price=:p AND symbol_id=:a');
                          $save->bindParam(':a', $symbol['symbol_id']);
                          $save->bindParam(':p', $price['price']);
                          $save->execute();
                          while($row1 = $save->fetch()){
                          echo"
                          <td style=' color: black;'><b>".$row1['sell_vol_total']."</td>
                          <td style=' color: black;'><b>".$st."</b></td>
                          <td style=' color: black;'><b>".$t."</b></td>";
                          } 
                        echo'
                        </tr>';
                      }else{
                        $save = $dbh->prepare('SELECT  sum(buy_vol) as buy_vol_total from orders where side="B" AND price=:p AND symbol_id=:a');
                        $save->bindParam(':a', $symbol['symbol_id']);
                        $save->bindParam(':p', $price['price']);
                        $save->execute();
                        $row = $save->fetch();
                        echo"
                        <tr>
                          <td style=' color: white;'><b>".$bt."</b></td>";
                          if($price['price'] > $pric && $row['buy_vol_total'] > 0){
                            echo"<td style=' color: LightSkyBlue;'>".$row['buy_vol_total']."</td>";
                          }else{
                            echo"<td style=' color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>";
                          }
                        echo"
                          <td style=' color: Lime;'><b><i>".$price['price']."</i></b></td>";
                          $save = $dbh->prepare('SELECT sum(sell_vol) as sell_vol_total from orders where side="S" AND price=:p AND symbol_id=:a');
                          $save->bindParam(':a', $symbol['symbol_id']);
                          $save->bindParam(':p', $price['price']);
                          $save->execute();
                          while( $row1= $save->fetch()){
                            if($price['price'] < $pric && $row1['sell_vol_total'] > 0 ){
                              echo"<td  style=' color: black;  background-color: #c8c8c8;'>".$row1['sell_vol_total']."</td>";
                            }else{
                              echo"<td style=' color: Magenta; '><b>".$row1['sell_vol_total']."</td>";
                            }
                            echo"
                            <td style=' color: white; '><b>".$st."</b></td>
                            <td style=' color: white;'><b>".$t."</b></td>";
                          }
                        echo"
                        </tr>";                      
                      }                    
                    }
                    echo'<font style="font-weight: 900;"">'.$symbolname.'</font>';
                    echo"
                    </tbody>
                  </table>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
</html>
