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
          <li><a href="#">Cancel Order</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border text-center">
                <h4 class="box-title"><strong>Cancel Bond Orders</strong></h4>
              </div>
              <div class="box-body table-responsive">
                  <table id="order_tbl_id" class="table table-bordered table-striped">
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
                    $wc = $dbh->prepare("
                        SELECT a.id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id 
                        FROM bond_orders a 
                        INNER JOIN symbol b ON a.symbol_id = b.symbol_id
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
                          <button type="button" class="btn btn-danger" id="del_or'.$i.'" value="'.$res['id'].'" onclick="return del_fun('.$i.');" data-toggle="tooltip" data-placement="top" title="Click here to delete order of '.$res['cd_code'].', '.$res['symbol'].' Symbol"><i class="fa fa-trash-o"></i> Delete</button>
                        </td>
                      </tr>';
                      $i++; 
                    }
                    ?>
                    </tbody>
                  </table>
              </div>
              <div class="box-footer"></div>
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
    $("#order_tbl_id").DataTable();
  });

  function get__order__list(val) {
    const operation = 'get_order_list_to_delete';
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_order_list_to_delete=' + operation + '&sec_type=' + val + '&usernmae=<?php echo addslashes($username); ?>',
      cache: false,
      success: function(response) {
        hideloading();
        $('#table_pending_orders').html(response);
      }
    });
  }

  function del_fun(i) {
    showLoading();
    var val = document.getElementById('del_or' + i).value;
    var cd_code = document.getElementById('cd_code' + i).value;
    var fid = document.getElementById('fid' + i).value;
    var vol = document.getElementById('v' + i).value;
    var side = document.getElementById('side' + i).value;
    var sy_id = document.getElementById('sy_id' + i).value;
    
    var data = {
      cancle_bond_id: val,
      // v: vol,
      fid: fid,
      side: side,
      cd_code: cd_code,
      sy_id: sy_id
    };
    
    if (confirm("Are you sure you want to delete the order of "+ cd_code + '?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_trading_process.php",
        data: data,
        dataType: 'JSON',
        success: function(response){
          hideloading();
          $("#message").html(response.message);
          showMessage();

          if (response.status == 1) {
            $(`tr[data-id="${i}"]`).remove();
          }

          // reload
          setTimeout(() => {
              $("#message").html('Reloading, please wait...').css('color', '#f0a500');
              showMessage();
              setTimeout(() => location.reload(), 2000);
          }, 2000);
          
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
</script>
</html>