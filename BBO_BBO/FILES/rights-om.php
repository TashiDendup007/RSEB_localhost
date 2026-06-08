<?php 
  date_default_timezone_set("Asia/Thimphu");
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
      <section class="content-header">
        <div class="modal fade" id="myModal" role="dialog"></div>
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
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body table-responsive">
                <table class='table' style='background-color:#0d3c55; color: white;'>
                  <thead>
                    <tr>
                      <th>BID VOL</th>
                      <th>PRICE</th>
                      <th>TOTAL BID</th>
                      <th>TRADABLE VOL</th>
                    </tr>
                  </thead>
                  <tbody>
                <?php
                  $totalVol = $dbh->prepare("SELECT sum(ribon_volume) AS total FROM spot_date_holding WHERE announcement_type = 1 and status = 0");
                  $totalVol->execute();
                  $vol = $totalVol->fetch();

                  $orders = $dbh->prepare("SELECT sum(order_size) AS orders FROM rights_issue WHERE type IN ('S', 'R') AND status = 0");
                  $orders->execute();
                  $ord = $orders->fetch();
                  $TOTALAVLVOL = $vol['total'] - $ord['orders'];

                  $ords = $dbh->prepare("SELECT sum(order_size) AS orders FROM rights_issue WHERE type = 'B' and status = 0");
                  $ords->execute();
                  $ord = $ords->fetch();
                  $TOTALORDERS = $ord['orders'];
                  $TV = $TOTALAVLVOL + 6;
                  
                  if($TOTALORDERS < $TV) {
                    $save = $dbh->prepare("SELECT * FROM rights_issue WHERE type='B' AND status = 0 GROUP BY bid_price ORDER BY bid_price DESC");
                    $save->execute();
                    $totalbid = 0;
                    $i = 0;
                    foreach ($save as $price) {
                      $save = $dbh->prepare("SELECT sum(order_size) AS buy_vol_total FROM rights_issue WHERE type = 'B' AND bid_price=:p AND status=0");
                      $save->bindParam(':p', $price['bid_price']);
                      $save->execute();
                      $row = $save->fetch();
                      if($i == 0) {
                        $totalbid = $row['buy_vol_total'];
                        echo"
                        <tr>
                          <td style='color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>
                          <td style='color: Lime;'><b><i>".$price['bid_price']."</i></b></td>
                          <td style='color: white;'><b>".$totalbid."</b></td>
                          <td style='color: black;'><b><i></i></b></td>
                        </tr>"; 
                      } else {
                        $totalbid = $row['buy_vol_total'] + $totalbid;
                        echo"
                        <tr>
                          <td style='color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>
                          <td style='color: Lime;'><b><i>".$price['bid_price']."</i></b></td>
                          <td style='color: white;'><b>".$totalbid."</b></td>
                          <td style='color: black;'><b><i></i></b></td>
                        </tr>"; 
                      }
                      $i++;
                    }
                    echo'<font style="font-weight: 900;"></font>
                    </tbody>
                  </table>';
                  } else {
                    $price = $dbh->prepare('SELECT DISTINCT(bid_price) FROM rights_issue WHERE type = "B" and status = 0 ORDER BY bid_price DESC');
                    $price->execute();
                    foreach ($price as $volume) {
                      $sum = $dbh->prepare('SELECT sum(order_size) AS total FROM rights_issue WHERE type="B" and bid_price >= :price and status = 0');
                      $sum->bindParam(':price',$volume['bid_price']);
                      $sum->execute();
                      $res = $sum->fetch();
                      $totalVoldis = $res['total']; 
                      if ($totalVoldis >= $TV) {
                        $priced = $volume['bid_price'];
                        break;
                      } else {
                      //echo "Price couldnt not be discovered";
                      }
                    }
                    $save = $dbh->prepare("SELECT * FROM rights_issue WHERE type='B' AND status = 0 GROUP BY bid_price ORDER BY bid_price DESC");
                    $save->execute();
                    $totalbid = 0;
                    $i = 0;
                    foreach ($save as $price) {
                      $save = $dbh->prepare('SELECT sum(order_size) AS buy_vol_total FROM rights_issue WHERE type="B" AND status=0 AND bid_price=:p');
                      $save->bindParam(':p', $price['bid_price']);
                      $save->execute();
                      $row = $save->fetch();
                      if($i == 0) {
                        $totalbid = $row['buy_vol_total'];
                        if($priced <= $price['bid_price']) {
                          echo"
                          <tr class='blink' style=' background-color: #c8c8c8 ;'>
                            <td style=' color: black;'><b>".$row['buy_vol_total']."</td>";
                          if($priced == $price['bid_price']) {
                            echo"<td style='color: red;'><b><i>".$price['bid_price']."</i></b></td>";
                          } else {
                            echo"<td style='color: black;'><b><i>".$price['bid_price']."</i></b></td>";
                          }
                          echo"<td style='color: black;'><b>".$totalbid."</b></td>";
                          if($priced == $price['bid_price']) {
                            echo"<td style='color: black;'><b><i>".$TV."</i></b></td>";
                          } else {
                            echo"<td style='color: black;'><b><i></i></b></td>";
                          }
                          echo"</tr>"; 
                        } else {
                          echo"
                          <tr>
                            <td style=' color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>
                            <td style=' color: Lime;'><b><i>".$price['bid_price']."</i></b></td>
                            <td style=' color: white;'><b>".$totalbid."</b></td>
                            <td style=' color: Lime;'><b><i></i></b></td>
                          </tr>"; 
                        }
                      } else {
                        $totalbid = $row['buy_vol_total'] + $totalbid;
                        if($priced <= $price['bid_price']) {
                          echo"
                          <tr class='blink' style=' background-color: #c8c8c8 ;'>
                            <td style=' color: black;'><b>".$row['buy_vol_total']."</td>";
                          if($priced == $price['bid_price']) {
                            echo"<td style=' color: red;'><b><i>".$price['bid_price']."</i></b></td>";
                          } else {
                            echo"<td style=' color: black;'><b><i>".$price['bid_price']."</i></b></td>";
                          }
                          echo"<td style=' color: black;'><b>".$totalbid."</b></td>";
                          if($priced == $price['bid_price']) {
                            echo"<td style=' color: black;'><b><i>".$TV."</i></b></td>";
                          } else {
                            echo"<td style=' color: black;'><b><i></i></b></td>";
                          }
                          echo"</tr>"; 
                        } else {
                          echo"
                          <tr>
                            <td style=' color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>
                            <td style=' color: Lime;'><b><i>".$price['bid_price']."</i></b></td>
                            <td style=' color: white;'><b>".$totalbid."</b></td>
                            <td style=' color: Lime;'><b><i></i></b></td>
                          </tr>"; 
                        }
                      }
                      $i++;
                    }
                  }
                  echo'<font style="font-weight: 900;"></font>
                  </tbody>
                </table>';
                ?>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
<style>
  /* @group Blink */
  .blink {
  animation-duration: 1s;
      animation-name: blink;
      animation-iteration-count: infinite;
      animation-direction: alternate;
      animation-timing-function: ease-in-out;
  }
  @keyframes blink {
    from {
        opacity: 1;
    }
    to {
        opacity: 0.2;
    }
  }
  /* @end */
</style>
</html>