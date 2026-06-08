<?php 
  include('../FILES/session_file.php');
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
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Historical Date of the Company(Script)</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-4">
                <label>Symbol: <font color="red">*</font></label>
                <select name="symbol" id="symbol" class="form-control" required>
                  <option value="">--Select--</option>
                  <?php 
                    $stmt = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE status = 1 AND trsstatus = 1 AND security_type='OS'");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $key => $value) {
                      echo'<option value="'.$value['symbol_id'].'">'.$value['symbol'].'</option>';
                    }
                  ?>
                </select>
            </div>
            <div class="col-lg-4">
                <label>Start: Date <font color="red">*</font></label>
                <input type="date" class="form-control" name="from_date" id="from_date" required>
            </div>
            <div class="col-lg-4">
                <label>End Date: <font color="red">*</font></label>
                <input type="date" class="form-control" name="to_date" id="to_date" required>
            </div>
            <div class="col-lg-12">
              <div id="errorMsg" style="color: red;"></div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="button" class="btn btn-success" id="generateReport" name="generateReport"><i class="fa fa-list"></i>  Generate </button>
            </div>
          </div>
          <hr>
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
  $('#generateReport').click( function () {
      showLoading();
      var symbol = $("#symbol").val();
      var fromDate = $("#from_date").val();
      var toDate = $("#to_date").val();
      var op = 'generate_historical_data';

      if (symbol === '' || fromDate === '' || toDate === '') {
        hideloading();
        $("#errorMsg").html("All Fields Required");
        return false;
      } else {
          $.ajax({
            type: "POST",
            url: "loadReport.php",
            data: 'generate_historical_data='+ op +'&toDate='+toDate +'&fromDate='+fromDate +'&symbol='+symbol,
            success: function(data){
              hideloading();
              $("#details").html(data);
            }
          });
      }
  });

  $("#symbol").click(function () {
    $("#errorMsg").html("");
  });
  
  $("#from_date").click(function () {
    $("#errorMsg").html("");
  });

  $("#to_date").click(function () {
    $("#errorMsg").html("");
  });
</script>
</html>