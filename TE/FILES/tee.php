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
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="te-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">executed</a></li>
        </ol>
      </section>
      <section class="content">
        <?php include('../NAV/orderNav.php') ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">Executed Orders</h3>
              </div>
              <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th></th>
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
                    $wc = $dbh->prepare("SELECT a.exe_id, a.side, a.cd_code, a.order_exe_price, a.lot_size_execute, a.order_date, b.symbol, b.symbol_id 
                      FROM symbol b 
                      JOIN executed_orders a ON a.symbol_id = b.symbol_id
                      WHERE a.sub_user = :un 
                      ORDER BY order_id DESC");
                    $wc->bindParam(':un',$_SESSION['sess_username']);
                    $wc->execute();
                    $wc = $wc->fetchALL(PDO::FETCH_ASSOC);
                    $i = 1;
                    foreach ($wc as $res) {
                      $background_color = $res['side'] == 'S' ? '#e8d4d7' : '#dce2e9';
                      $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
                      
                      echo'
                      <tr style="background-color:'.$background_color.';">
                        <td>'.$i.'</td>
                        <td>'.$res['symbol'].'</td>
                        <td>'.$res['cd_code'].'</td>
                        <td>'.$res['order_exe_price'].'</td>
                        <td>'.$res['lot_size_execute'].'</td>
                        <td>'.$side.'</td>
                        <td>'.$res['order_date'].'</td>
                      </tr>';
                      $i++;
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
</html>
