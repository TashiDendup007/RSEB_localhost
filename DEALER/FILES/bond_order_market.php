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
          <li><a href="#">Watch List</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="box">
              <div class="box-header with-border"><h4>Bond Watch List</h4></div>
              <div class="box-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Symbol</th>
                      <th>Paid up shares</th>
                      <th>Market Price</th>
                      <th>View</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $wc = $dbh->prepare("
                          SELECT s.symbol_id, s.paid_up_shares, s.symbol, s.logo, s.name, s.sector, s.face_value, mp.market_price, mp.ex_market_price, (mp.market_price - mp.ex_market_price) AS cp
                          FROM bond_orders o 
                          LEFT JOIN symbol s ON o.symbol_id = s.symbol_id
                          JOIN market_price mp ON mp.symbol_id = s.symbol_id
                          WHERE s.security_type IN ('CB', 'GB') 
                          AND s.status = 1 
                          AND s.trsstatus = 1
                          AND o.order_type = 'OTC'
                          GROUP BY o.symbol_id
                    ");
                    $wc->execute();
                    $results = $wc->fetchAll(PDO::FETCH_ASSOC);
                    $price_movement = 0;
                    foreach ($results as $res) {
                      switch (true) {
                        case $res['cp'] == 0:
                          $class = 'black';
                          $price_movement = number_format(0, 2);
                          break;
                        case $res['cp'] > 0:
                          $class = 'green';
                          $price_movement = '+'.$res['cp'];
                          break;
                        case $res['cp'] < 0:
                          $class = 'red';
                          $price_movement = $res['cp'];
                          break;
                      }
                      echo '
                      <tr>
                        <td><img src="' . $res['logo'] . '" height=30></td>
                        <td>' . $res['symbol'] . '</td>
                        <td>' . number_format($res['paid_up_shares']) . '</td>
                        <td>' . $res['market_price'] . ' (<b style="color:' . $class . '">' . $price_movement . '</b>)</td>
                        <td>
                          <a href="" data-toggle="modal" data-target="#myModal" onclick="get_bond_market_book(' . $res['symbol_id'] . ');">
                            <input type="hidden" value="' . $res['symbol'] . '" id="symbolName' . $res['symbol_id'] . '">
                            <span class="badge bg-blue">view</span>
                          </a>
                        </td>
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
    <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
<script>
  function get_bond_market_book(val) {
    var operation = "load_bond_live_market";

    $.ajax({
      type: "POST",
      url: "bond_load_function.php",
      data:'load_bond_live_market=' + operation + '&symbol_id=' + val,
      dataType: "html",
      success: function(response){
        $("#myModal").html(response);
      }
    });
  }
</script>
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