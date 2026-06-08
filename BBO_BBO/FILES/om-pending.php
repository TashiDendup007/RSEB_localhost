<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');

  $check = $dbh->prepare('SELECT a.institution_id, c.participant_code 
    FROM adm_institution a, adm_participants b,users c 
    WHERE c.participant_code = b.participant_code AND b.institution_id=a.institution_id AND c.username=:un');
  $check->bindParam(':un',$username);
  $check->execute();
  $res = $check->fetch();
  // $institution_id=$res['institution_id'];
  $participant_code = $res['participant_code'];
  $res = null;
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
          <li><a href="#">Pending Order</a></li>
        </ol>
      </section>
      <section class="content">
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Working Orders</h3>
              </div>
              <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Symbol</th>
                      <th>CD Code</th>
                      <th>Price</th>
                      <th>Volume</th>
                      <th>Side</th>
                      <th>Time</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $wc= $dbh->prepare("SELECT a.cd_code, a.price, a.sell_vol, a.buy_vol, a.order_date, a.side, b.symbol, b.symbol_id 
                      FROM symbol b
                      INNER JOIN orders a ON a.symbol_id = b.symbol_id
                      WHERE a.participant_code=:pc 
                      ORDER BY order_date DESC");
                    $wc->bindParam(':pc', $participant_code);
                    $wc->execute();
                    $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
                    $i = 1;
                    foreach ($rows as $res) {
                      $background_color = $res['side'] == 'S' ? '#eb8292' : '#bac2cb';
                      $side = $res['side'] == 'S' ? 'SELL' : 'BUY';

                      echo'
                      <tr style="background-color:'.$background_color.'">
                        <td>'.$i.'</td>
                        <td>'.$res['symbol'].'</td>
                        <td>'.$res['cd_code'].'</td>
                        <td>'.$res['price'].'</td>
                        <td>'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'</td>
                        <td>'.$side.'</td>
                        <td>'.$res['order_date'].'</td>
                      </tr>'; 
                      $i++;
                    }
                    $dbh = null;
                    $rows = null;
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
  $( function () {
    $("#example1").DataTable();
  });
</script>
</html>
