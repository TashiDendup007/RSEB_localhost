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
          <li><a href="#">Bond Allocation</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h4>Bond Offer List</h4>
              </div>
              <div class="box-body table-responsive">
                <table class="table table-bordered" id="tableId">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Symbol</th>
                      <th scope="col">Name</th>
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
                          b.id, s.symbol, s.name as symbol_name, b.symbol_id, b.start_bond_at, b.end_bond_at, b.status, s.face_value, s.coupon_rates 
                        FROM bond_offers b 
                        JOIN symbol s ON b.symbol_id = s.symbol_id 
                        WHERE b.status = 1 
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
                          <input type="hidden" name="rate'.$i.'" id="rate'.$i.'" value="'.$row['coupon_rates'].'">

                          <input type="hidden" name="end_date'.$i.'" id="end_date'.$i.'" value="'.$row['end_bond_at'].'">
                          <input type="hidden" name="sys_date'.$i.'" id="sys_date'.$i.'" value="'.$sysDate.'">
                        </td>
                        <td>'.$row['symbol'].'</td>
                        <td>'.$row['symbol_name'].'</td>
                        <td>'.$row['start_bond_at'].'</td>
                        <td>'.$row['end_bond_at'].'</td>
                        <td>'.$status.'</td>
                        <td>
                          <button type="button" class="btn btn-primary" onclick="process('.$i.')" data-toggle="tooltip" data-placement="top" title="Click here to process"><i class="fa fa-refersh"></i> Process</button>
                          <button type="button" class="btn btn-warning" onclick="push_data('.$i.')" data-toggle="tooltip" data-placement="top" title="Click here to migrate data to CDS Holding"><i class="fa fa-arrow-right"></i> Migrate Data</button>
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
    // check bond end date
    var end_date = $("#end_date"+i).val();
    var sys_date = $("#sys_date"+i).val();

    if (end_date > sys_date) {
      hideloading();
      $("#message").html("<div class='alert alert-warning' role='alert'>Bond subscription has not ended.</div>");
      showMessage();
      return false;
    }

    if (confirm("Do you want to continue processing?")) {
      showLoading();
      var op = 'bond_allocation';
      var sym_id = $("#symbol_id"+i).val();
      var sym = $("#symbol"+i).val();
      var f_value = $("#face_value"+i).val();
      var r_value = $("#rate"+i).val();

      var data = {
        bond_allocation: op,
        symbol_id: sym_id,
        symbol: sym,
        face_value: f_value,
        rate: r_value,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_allocation_pro_rata.php",
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

  function push_data(i) {
    // check bond end date
    var end_date = $("#end_date"+i).val();
    var sys_date = $("#sys_date"+i).val();

    if (end_date > sys_date) {
      hideloading();
      $("#message").html("<div class='alert alert-warning' role='alert'>Bond subscription has not ended.</div>");
      showMessage();
      return false;
    }

    if (confirm("Do you want to continue pushing data to CDS Holding?")) {
      showLoading();
      var op = 'push_bond_allocated_data';
      var sym_id = $("#symbol_id"+i).val();
      
      var data = {
        push_bond_allocated_data: op,
        symbol_id: sym_id,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/allocated_data_holding.php",
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
