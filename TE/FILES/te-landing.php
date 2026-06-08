<?php
  include ('sessionStartFile_client.php');
  include ('../../CONNECTIONS/db.php');
  // include('../../Functions/f.php');

  // $username = $_SESSION['sess_username'];
  /*$cdcode = find_link_user_cd_code($username);
  $list =  ins_id($username);
  $ins_id = $list[0];
  $p_code = $list[1];*/
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
    <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php') ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Dashboard</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php'); ?>
        <div class="box">
          <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12">
              <div class="box-header with-border">
                <h3 class="box-title">Watch List</h3>
              </div>
              <div class="box-body table-responsive">
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
                        FROM symbol s
                        JOIN market_price mp ON mp.symbol_id = s.symbol_id
                        WHERE s.security_type = 'OS' AND s.status = 1 AND s.trsstatus = 1
                    ");

                    $wc->execute();
                    $results = $wc->fetchAll();
                    $price_movement = 0;
                    foreach ($results as $res) {
                      switch (true) {
                        case $res['cp'] == 0:
                          $class = 'red';
                          $price_movement = number_format(0,2);
                          break;
                        case $res['cp'] > 0:
                          $class = 'green';
                          $price_movement = '+'.$res['cp'];
                          break;
                        case $res['cp'] < 0:
                          $class = 'black';
                          $price_movement = $res['cp'];
                          break;
                      }

                      echo '
                      <tr>
                        <td><img src="' . $res['logo'] . '" height=30></td>
                        <td><a href="">' . $res['symbol'] . '</a></td>
                        <td>' . number_format($res['paid_up_shares']) . '</td>
                        <td>' . $res['market_price'] . ' (<b style="color:' . $class . '">' . $price_movement . '</b>)</td>
                        <td>
                          <a href="" data-toggle="modal" data-target="#myModal" onclick="GetSymbolModal(' . $res['symbol_id'] . ');">
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
              <div class="col-lg-6 col-md-12 col-sm-12">
                  <div class="box-header">
                    <h3 class="box-title">Share Holding Profile</h3>
                    <h5>CD Code: <strong><?php echo $cdcode; ?></strong></h5>
                  </div>
                  <div class="box-body table-responsive">
                    <table class="table table-condensed table-sm" >
                      <tr>
                        <th>symbol</th>
                        <th>pledged</th>
                        <th>blocked</th>
                        <th>pending-in</th>
                        <th>pending-out</th>
                        <th>total</th>
                      </tr>

                      <?php
                          // Use prepared statements with bound parameters for security
                          $wc = $dbh->prepare("SELECT c.symbol, c.logo, c.name, c.paid_up_shares, a.pledge_volume, a.block_volume, a.pending_in_vol, a.pending_out_vol, a.volume
                                               FROM cds_holding a
                                               INNER JOIN symbol c ON a.symbol_id = c.symbol_id
                                               WHERE a.cd_code = :cd");
                          $wc->bindParam(':cd', $cdcode);
                          $wc->execute();
                          if ($wc->rowCount() > 0) {
                            foreach ($wc as $res) {
                              if ($res['pledge_volume'] + $res['block_volume'] + $res['pending_out_vol'] + $res['volume']  == 0) {

                              } else {
                                echo'<tr>
                                     <td>'.$res['symbol'].'</td>
                                     <td>'.number_format($res['pledge_volume']).'</td>
                                     <td>'.number_format($res['block_volume']).'</td>
                                     <td>'.number_format($res['pending_in_vol']).'</td>
                                     <td>'.number_format($res['pending_out_vol']).'</td>
                                     <td>'.number_format($res['pledge_volume'] + $res['block_volume'] + $res['pending_out_vol'] + $res['volume']).'</td>
                                     </tr>';
                              }
                            }
                          }
                          else {
                            echo'<tr>
                                 <td>-</td>
                                 <td>-</td>
                                 <td>-</td>
                                 <td>-</td>
                                 <td>-</td>
                                 <td>-</td>
                                 </tr>';
                          }

                          // Use prepared statements with bound parameters for security
                          $wc = $dbh->prepare("SELECT SUM(a.amount) AS tot
                                               FROM bbo_finance a
                                               INNER JOIN client_account b ON a.cd_code = b.cd_code
                                               WHERE a.cd_code = :cd AND a.status = 1");
                          $wc->bindParam(':cd', $cdcode);
                          $wc->execute();
                          $res = $wc->fetch();
                          $total_amt = isset($res['tot']) ? $res['tot'] : 0;

                          echo "<hr><code>Available Exposure to buy  : </code> Nu. ".number_format($total_amt)."<br/>";
                          ?>
                    </table>
                  </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      <?php include('../NAV/footer.php') ?>
    </div>
  </body>

</html>
