<?php 
  include('sessionStartFile_admin.php'); 
  include ('../../CONNECTIONS/db.php');

  date_default_timezone_set("Asia/Thimphu"); 
  $sysDate = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
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
          <li><a href="#">IPO Allocation</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h4>IPO Offer List</h4>
              </div>
              <div class="box-body table-responsive">
                <table class="table table-bordered" id="tableId">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Symbol</th>
                      <th scope="col">Start At</th>
                      <th scope="col">End At</th>
                      <th scope="col">Status</th>
                      <th scope="col">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $i = 1;
                    $sql = $dbh->prepare("SELECT 
                        i.id, i.symbol_id, s.symbol, i.start_at, i.end_at, i.status, s.face_value 
                        FROM ipo_offers i
                        JOIN symbol s ON i.symbol_id = s.symbol_id 
                        WHERE i.status = 1 
                    ");
                    $sql->execute();
                    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                      $status = ($row['status'] == 1) ? 'Active' : 'In-Active';
                      echo'
                      <tr data-id='.$i.'>
                        <td>'.$i.'
                          <input type="hidden" name="id'.$i.'" id="id'.$i.'" value="'.$row['id'].'">
                          <input type="hidden" name="symbol_id'.$i.'" id="symbol_id'.$i.'" value="'.$row['symbol_id'].'">
                          <input type="hidden" name="symbol'.$i.'" id="symbol'.$i.'" value="'.$row['symbol'].'">
                          <input type="hidden" name="face_value'.$i.'" id="face_value'.$i.'" value="'.$row['face_value'].'">
                        </td>
                        <td>'.$row['symbol'].'</td>
                        <td>'.$row['start_at'].'</td>
                        <td>'.$row['end_at'].'</td>
                        <td>'.$status.'</td>
                        <td>
                          <button type="button" class="btn btn-primary" onclick="process('.$i.')" data-toggle="tooltip" data-placement="top" title="Click here to process"><i class="fa fa-refresh"></i> Process</button>
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
  $(document).ready(function() {
    $("#tableId").dataTable();
  });

  function process(i) {
    if (confirm("Do you want to continue processing?")) {
      showLoading();
      var op = 'ipo_allocation';
      var sym_id = $("#symbol_id"+i).val();
      var sym = $("#symbol"+i).val();
      var f_value = $("#face_value"+i).val();
      
      var data = {
        ipo_allocation: op,
        symbol_id: sym_id,
        symbol: sym,
        face_value: f_value,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/ipo_allocation.php",
        data: data,
        dataType: "html",
        success: function(response) {
          $("#message").html(response);
          showMessage();
        }
      });
      hideloading();
    } else {
      hideloading();
      return false;
    }
  }

</script>
</html>
