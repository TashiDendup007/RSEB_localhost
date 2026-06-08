<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  
  $check= $dbh->prepare("SELECT a.institution_id, c.participant_code FROM adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un");
  $check->bindParam(':un', $username);
  $check->execute();
  $res = $check->fetch();
  $institution_id = $res['institution_id'];
  $participant_code = $res['participant_code'];
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
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
        <li><a href="#">Cancle Order</a></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>SYMBOL</th>
                    <th>CD CODE</th>
                    <th>PRICE</th>
                    <th>VOLUME</th>
                    <th>TIME</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                  $wc= $dbh->prepare("SELECT s.symbol, b.symbol_id, b.cd_code, b.order_size, b.type, b.bid_price, b.order_date, b.order_id
                    FROM bond b
                    JOIN symbol s ON b.symbol_id = s.symbol_id 
                    where b.symbol_id = s.symbol_id AND b.status = 0 AND b.user_name=:un 
                    ORDER BY b.order_Date DESC
                  ");
                  $wc->bindParam(':un',$username);
                  $wc->execute();
                  $i = 1;
                  foreach ($wc as $res) {
                    echo'
                    <tr data-id="'.$i.'" style="background-color:#e8d4d7;">
                      <input type="hidden" value="'.$res['symbol'].'" id="sy'.$i.'">
                      <input type="hidden" value="'.$res['symbol_id'].'" id="sy_id'.$i.'">
                      <input type="hidden" value="'.$res['cd_code'].'" id="cd_code'.$i.'">
                      <input type="hidden" value="'.$res['order_size'].'" id="v'.$i.'">
                      <td>'.$i.'</td>
                      <td>'.$res['symbol'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td>'.$res['bid_price'].'</td>
                      <td>'.$res['order_size'].'</td>
                      <td>'.$res['order_date'].'</td>
                      <td><button type="button" class="btn btn-danger" name="del_or" id="del_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');"><i class="fa fa-trash-o"></i> Delete</button></td>
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
<script>
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
    var val = document.getElementById('del_or'+i).value;
    var val1 = document.getElementById('sy'+i).value;
    var cd_code = document.getElementById('cd_code'+i).value;
    var vol = document.getElementById('v'+i).value;
    var sy_id = document.getElementById('sy_id'+i).value;
    
    if (confirm("Are you sure you want to delete this order of "+ cd_code + ', of '+val1+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_process.php",
        data: { cancle_id: val, v: vol, cd_code: cd_code, sy_id: sy_id },
        dataType: "html",
        success: function(response) {
          var data = JSON.parse(response);

          const statusMsg = $('<div>').addClass('alert alert-success alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
              $('<i>').addClass('icon fa fa-check'), data.message
          );

          $("#message").html(statusMsg);
          showMessage();

          if (data.status == 200) {
            $(`tr[data-id="${i}"]`).remove();
          }
        }
      });
    } else {
      return false;
    }
 }
</script>
</html>
