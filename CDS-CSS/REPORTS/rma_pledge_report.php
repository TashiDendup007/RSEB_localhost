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
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Reports</a></li>      
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Pledge Report To RMA</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
              <div class="col-lg-6 col-md-6 col-sm-12">
                <label>From Date</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                </div>
              </div>

              <div class="col-lg-6 col-md-6 col-sm-12">
                <label>To Date</label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                </div>
              </div>
          </div>    
          <div class="box-footer">
            <div class="col-lg-6">
                <button type="button" class="btn btn-success" id="get_pledge_report" name="get_pledge_report"><i class="fa fa-list"></i>  Generate </button>
            </div>

            <div class="col-lg-12 text-center" id="errorMsg" style="color: red;"></div>

          </div>
          <div class="box-body">
            <div id="details"></div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>  
  </div>
</body>
<script type="text/javascript">
  $('#get_pledge_report').click( function () {
        showLoading();
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();
        
        if (from_date == '' || to_date == '') {
          hideloading();
          $("#errorMsg").html("Required both From and To Date");
          return false;
        } else {
            $.ajax({
              type: "POST",
              url: "load.php",
              data: 'to_date=' + to_date + '&from_date=' + from_date + '&get_pledge_report_rma=get_pledge_report_rma',
              dataType: 'html',
              success: function(data){
                hideloading();
                $("#details").html(data);
              }
            });
        }
    });

    $("#from_date").click(function () {
      $("#errorMsg").html("");
    });

    $("#to_date").click(function () {
      $("#errorMsg").html("");
    });
</script>
</html>