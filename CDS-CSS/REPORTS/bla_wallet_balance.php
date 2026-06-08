<?php 
  include('../FILES/sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
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
        <li><a href="#">BLA</a></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">BLA mCaMS Wallet Balance</h4>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                  <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                  <i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-lg-6 col-md-6" id="fd_div_id">
                  <label>From Date <font color="red">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="date" class="form-control pull-right" name="fromDate" id="fromDate" required>
                  </div>
                </div>

                <div class="col-lg-6 col-md-6">
                  <label>To Date <font color="red">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    <input type="date" class="form-control pull-right" name="toDate" id="toDate" required>
                  </div>
                </div>
              </div>

            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" id="generate_balance_id" name="generate_balance_id"><i class="fa fa-tasks"></i> Generate</button>
              </div>
            </div>
            <div id="report_result"></div>
          </div> 
        </div>
      </div>
    </section>
  </div>
<?php include('../NAV/footer.php') ?>  
</body>
<script type="text/javascript">
  $(document).ready(function() {
      $('#listTableId').DataTable();
  });

  $('#generate_balance_id').click( function() {
    showLoading();
    const fromDate = $("#fromDate").val();
    const toDate   = $("#toDate").val();
    const op = 'generate_wallet_balance';

    $.ajax({
      type: "POST",
      url: "load.php",
      data: { fromDate: fromDate, toDate: toDate, generate_wallet_balance: op },
      dataType: "html",
      success: function(data){
        hideloading();
        $("#report_result").html(data);
      }
    });
  });
</script>
</html>
