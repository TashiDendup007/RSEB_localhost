<?php
  include ('sessionStartFile_client.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="te-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">working</a></li>
        </ol>
      </section>
      <section class="content">
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">Working Orders</h3>
              </div>
              <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped" width="100%">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>SYMBOL</th>
                      <th>CD CODE</th>
                      <th>PRICE</th>
                      <th>VOLUME</th>
                      <th>SIDE</th>
                      <th>TIME</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $wc = $dbh->prepare("SELECT a.order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id 
                        FROM symbol b 
                        JOIN orders a ON a.symbol_id = b.symbol_id 
                        WHERE a.order_entry = :un 
                        ORDER BY order_id DESC
                      ");
                      $wc->bindParam(':un', $username);
                      $wc->execute();
                      $wc = $wc->fetchALL(PDO::FETCH_ASSOC);
                      $i = 1;
                      foreach ($wc as $res) {
                        $background_color = $res['side'] == 'S' ? '#eca0ab' : '#dce2e9';
                        $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
                        echo'
                        <tr style="background-color:'.$background_color.';">
                          <td>'.$i++.'</td>
                          <td>'.$res['symbol'].'</td>
                          <td>'.$res['cd_code'].'</td>
                          <td>'.$res['price'].'</td>
                          <td>'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'</td>
                          <td>'.$side.'</td>
                          <td>'.$res['order_date'].'</td>
                        </tr>';
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $("#example1").DataTable();
});
</script>
</html>
