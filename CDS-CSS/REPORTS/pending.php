<?php 
  include('../FILES/sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');

  $check= $dbh->prepare("SELECT a.institution_id, c.participant_code 
    from adm_institution a 
    JOIN adm_participants b ON a.institution_id = b.institution_id
    JOIN users c ON b.participant_code = c.participant_code
    WHERE c.username = :un
  ");
  $check->bindParam(':un',$username);
  $check->execute();
  $res=$check->fetch();
  $institution_id=$res['institution_id'];
  $participant_code=$res['participant_code'];
?>
<!DOCTYPE html>
<html>
<style>
  .blink {
    animation-duration: 5s;
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
</style>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Cancle Order</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-body">
               <div class="table-responsive">
                 <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>SYMBOL</th>
                    <th>CD CODE</th>
                    <th>BROKER</th>
                    <th>ENTRY</th>
                    <th>PRICE</th>
                    <th>VOLUME</th>
                    <th>SIDE</th>
                    <th>TIME</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $wc= $dbh->prepare("SELECT a.*, b.symbol,b.symbol_id from symbol b,orders a where a.symbol_id=b.symbol_id order by order_date desc");
                    // $wc->bindParam(':un',$username);
                    $wc->execute();
                    $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
                    $i = 1;
                    foreach ($rows as $res) {
                      $cap_name = 'CAP';
                      $market_price = market_price($res['symbol_id']); 
                      $cap = circuit($cap_name);
                      $cap_value = cap_compute($market_price,$cap);
                      $low_p = $market_price - $cap_value;
                      $high_p = $market_price + $cap_value;

                      $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
                      if($res['price'] <  substr($low_p, 0, 5) || $res['price'] >  substr($high_p, 0, 5)) {
                        echo'
                        <tr class="" style="background-color:red;">
                          <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
                          <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
                          <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
                          <input type="hidden" value="'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'"  id="v'.$i.'">
                          <input type="hidden" value="'.$res['flag_id'].'" id="fid'.$i.'">
                          <input type="hidden" value="'.$res['side'].'" id="side'.$i.'">
                          <td>'.$res['symbol'].'</td>
                          <td>'.$res['cd_code'].'</td>
                          <td>'.$res['member_broker'].'</td>
                          <td>'.$res['order_entry'].'</td>
                          <td>'.$res['price'].'</td>
                          <td>'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'</td>
                          <td>'.$side.'</td>
                          <td>'.$res['order_date'].'</td>
                        </tr>';
                      } else {
                        $bg_color = $res['side'] == 'S' ? '#e8d4d7;' : '#dce2e9;';
                        echo'
                        <tr style="background-color:'.$bg_color.'">
                          <input type="hidden" value="'.$res['symbol'].'"  id="sy'.$i.'">
                          <input type="hidden" value="'.$res['symbol_id'].'"  id="sy_id'.$i.'">
                          <input type="hidden" value="'.$res['cd_code'].'"  id="cd_code'.$i.'">
                          <input type="hidden" value="'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'"  id="v'.$i.'">
                          <input type="hidden" value="'.$res['flag_id'].'"  id="fid'.$i.'">
                          <input type="hidden" value="'.$res['side'].'"  id="side'.$i.'">
                          <td>'.$res['symbol'].'</td>
                          <td>'.$res['cd_code'].'</td>
                          <td>'.$res['member_broker'].'</td>
                          <td>'.$res['order_entry'].'</td>
                          <td>'.$res['price'].'</td>
                          <td>'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'</td>
                          <td>'.$side.'</td>
                          <td>'.$res['order_date'].'</td>
                        </tr>'; 
                      }
                      $i++;                 
                    }
                    ?>
                  </tbody>
                </table>
               </div> 
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>
  </div>
</body>
<script type="text/javascript">
  $(function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>
</html>
