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
      <div id="message"></div>
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Change Order</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>SYMBOL</th>
                      <th>CD CODE</th>
                      <th>PRICE</th>
                      <th>VOLUME</th>
                      <th>TYPE</th>
                      <th>TIME</th>
                      <th>ACTION</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                  $i = 1;
                  $wc = $dbh->prepare("SELECT s.symbol, r.symbol_id, r.cd_code, r.order_size, r.type, r.bid_price, r.order_date, r.order_id 
                    FROM ipo r 
                    JOIN symbol s ON r.symbol_id = s.symbol_id 
                    WHERE r.type = 'IPO' AND r.status = 0 AND r.user_name = :un
                  ");
                  $wc->bindParam(':un', $username);
                  $wc->execute();
                  foreach ($wc as $res) {
                  echo'
                  <tr style="background-color:#dce2e9;">
                    <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
                    <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
                    <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
                    <input type="hidden" value="'.$res['order_size'].'" id="v'.$i.'">
                    <input type="hidden" value="'.$res['type'].'" id="side'.$i.'">
                    <input type="hidden" value="'.$res['bid_price'].'" id="e_p'.$i.'">
                    <td>'.$res['symbol'].'</td>
                    <td>'.$res['cd_code'].'</td>
                    <td>'.$res['bid_price'].' </td>
                    <td>
                      <input type="text" size="8" value="'.$res['order_size'].'" id="e_v'.$i.'" class="form-control">
                    </td>
                    <td>BUY</td>
                    <td>'.$res['order_date'].'</td>
                    <td>
                      <button type="button" class="btn btn-primary" name="chg_or" id="chg_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');" data-toggle="tooltip" data-placement="top" title="Click Here to Change For '.$res['cd_code'].'"><i class="fa fa-refresh"></i> Change</button>
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
    <?php include('../NAV/footer.php'); ?>  
  </div>
</body>
<script>
  $( function () {
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
    var val = document.getElementById('chg_or'+i).value;
    var val1 = document.getElementById('sy'+i).value;
    var cd_code = document.getElementById('cd_code'+i).value;
    var vol = document.getElementById('v'+i).value;
    var side = document.getElementById('side'+i).value;
    var e_p = document.getElementById('e_p'+i).value;
    var e_v = document.getElementById('e_v'+i).value;
    var sy_id = document.getElementById('sy_id'+i).value;

    if (confirm("Are you sure you want to Change this order of "+ cd_code + ', of '+val1+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/ipo_process.php",
        data:'change_id='+val+'&v='+vol+'&side='+side+'&cd_code='+cd_code+'&e_p='+e_p+'&e_v='+e_v+'&sy_id='+sy_id,
        dataType: "html",
        success: function(response){
          $("#message").html(response);
          showMessage();
        }
      });
    } else {
      return false;
    }
  }
</script>
</html>
