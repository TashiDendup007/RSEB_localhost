<?php 
  date_default_timezone_set('Asia/Thimphu');
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
        <h1><small></small></h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Order</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <?php 
                $dateselect = date("Y-m-d H:i:s");
                $stmt = $dbh->prepare("SELECT b.id, b.symbol_id, b.start_bond_at, b.end_bond_at, b.status, 
                    DATE_FORMAT(b.start_bond_at, '%W %M %e, %Y %h:%i %p') AS start_at_format,
                    DATE_FORMAT(b.end_bond_at, '%W %M %e, %Y %h:%i %p') AS end_at_format, s.paid_up_shares, s.coupon_rates, s.face_value
                    FROM bond_offers b
                    JOIN symbol s ON b.symbol_id = s.symbol_id 
                    WHERE b.status = 1 
                    ORDER BY b.id DESC
                ");
                $stmt->execute();
                $result = $stmt->fetch();
                if ($stmt->rowcount() < 1) {
                  echo"<div class='box-body'><h3>There are no active Bond offer</h3></div>"; 
                  die();
                }

                if ($result['start_bond_at'] > $dateselect) {
                  echo"<div class='box-body'><h3> The Bond Market will be available on : <b>".$result['start_at_format']."</b></h3></div>";
                  die();
                }
              ?>
              <div class="box-body">
                <?php         
                  $target = $result['paid_up_shares'];
                  $price = 0;
                  $bond_symbol_id = $result['symbol_id'];

                  $priceq = $dbh->prepare("SELECT DISTINCT bid_price FROM bond WHERE status = 0 AND symbol_id = ? ORDER BY bid_price ASC");
                  $priceq->bindparam(1, $bond_symbol_id);
                  $priceq->execute();
                  foreach ($priceq as $volume) {
                      $sum = $dbh->prepare("SELECT SUM(order_size) AS total FROM bond WHERE status = 0 AND bid_price <= ? AND symbol_id = ?");
                      $sum->bindParam(1, $volume['bid_price']);
                      $sum->bindParam(2, $bond_symbol_id);
                      $sum->execute();
                      $res = $sum->fetch();
                      $totalVoldis = $res['total']; 

                      if ($totalVoldis >= $target) {
                        $price = $volume['bid_price'];
                        $volume = $res['total'];
                        break;
                      } else {
                        //echo "Price couldnt not be discovered";
                      }
                  }
                  echo'Available Units :<b> '.number_format($target).' </b> Probable Cut Off Rate : '.number_format($price, 2);
                  echo"
                  <table class='table' style='background-color:#0d3c55; color: white;'>
                    <thead>
                      <tr>
                        <th><b>TOTAL BID VOL</b></th>
                        <th><b>PRICE</b></th>
                        <th><b>TOTAL AMOUNT</b></th>
                        <th><b>CUMULATIVE AMOUNT</b></th>
                      </tr>
                    </thead>
                    <tbody>";
                    $CUMULATIVE = 0;
                    $getData = $dbh->prepare("SELECT DISTINCT bid_price FROM bond WHERE status = 0 AND symbol_id = ? ORDER BY bid_price ASC");
                    $getData->bindParam(1, $bond_symbol_id);
                    $getData->execute();
                    $data = $getData->fetchAll();
                    
                    foreach ($data as $row) {
                      $save = $dbh->prepare("SELECT SUM(total_amount) AS total_amount, bid_price, order_size 
                        FROM bond 
                        WHERE status = 0 AND bid_price = ? AND symbol_id = ?
                      ");
                      $save->bindParam(1, $row['bid_price']);
                      $save->bindParam(2, $bond_symbol_id);
                      $save->execute();
                      while($row = $save->fetch()) {
                        $CUMULATIVE += $row['total_amount'];
                        $color = ($price === $row['bid_price']) ? '#524a6b' : '#0d3c55';
                        echo"
                        <tr style='background-color : ".$color."''>
                          <td style='color: LightSkyBlue;'><b>".number_format($row['order_size'])."</td>
                          <td style='color: Lime;'><b><i>".$row['bid_price']."</i></b></td>
                          <td style='color: Lime;'><b><i>".number_format($row['total_amount'])."</i></b></td>
                          <td style='color: Lime;'><b><i>".number_format($CUMULATIVE)."</i></b></td>
                        </tr>"; 
                      }
                    }
                    echo"
                  </tbody>
                </table>";
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