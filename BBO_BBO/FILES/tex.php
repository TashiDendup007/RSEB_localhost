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
          <li><a href="#">Cancle Order</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
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
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                  $wc = $dbh->prepare("SELECT 
                      a.order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id 
                    FROM symbol b
                    INNER JOIN orders a ON a.symbol_id = b.symbol_id
                    WHERE a.order_entry = :un 
                    ORDER BY order_date DESC
                  ");
                  $wc->bindParam(':un', $username);
                  $wc->execute();
                  $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
                  $i = 1;
                  foreach ($rows as $res) {
                    $background_color = $res['side'] == 'S' ? '#eb8292' : '#bac2cb';
                    $side = ($res['side'] == 'S') ? 'SELL' : 'BUY';
                    echo'
                    <tr data-id="'.$i.'" style="background-color:'.$background_color.'">
                      <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
                      <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
                      <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
                      <input type="hidden" value="'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'" id="v'.$i.'">
                      <input type="hidden" value="'.$res['flag_id'].'" id="fid'.$i.'">
                      <input type="hidden" value="'.$res['side'].'" id="side'.$i.'">
                      <td>'.$i.'</td>
                      <td>'.$res['symbol'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td>'.$res['price'].'</td>
                      <td>'.$res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'].'</td>
                      <td>'.$side.'</td>
                      <td>'.$res['order_date'].'</td>
                      <td>
                        <button type="button" class="btn btn-danger" name="del_or" id="del_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');" data-toggle="tooltip" data-placement="top" title="Click Here To Delete Order for '.$res['cd_code'].', '.$res['symbol'].' Symbol"><i class="fa fa-trash-o"></i> Delete</button>
                      </td>
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

  function fun(i) {
    showLoading();
    var val = document.getElementById('del_or'+i).value;
    var symbol = document.getElementById('sy'+i).value;
    var cd_code = document.getElementById('cd_code'+i).value;
    var fid = document.getElementById('fid'+i).value;
    var vol = document.getElementById('v'+i).value;
    var side = document.getElementById('side'+i).value;
    var sy_id = document.getElementById('sy_id'+i).value;
    var data = {
      cancle_id: val,
      v: vol,
      fid: fid,
      side: side,
      cd_code: cd_code,
      sy_id: sy_id
    };
    if (confirm("Are you sure you want to delete this order of "+ cd_code + ', of '+symbol+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        // dataType: 'html',
        dataType: 'json',
        success: function(response){
          hideloading();
          $("#message").html(response.message);
          showMessage();
          if (response.status == 1) {
            $(`tr[data-id="${i}"]`).remove();
          }
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
</script>
</html>
