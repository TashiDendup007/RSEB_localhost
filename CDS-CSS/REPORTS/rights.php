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
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Rights Audit</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-6">
              <label>Symbol<font color="red">*</font></label>
              <select name="symbol_id" id="symbol_id" class="form-control" required>
                <option value="">--Select--</option>
                <?php
                  $stmt = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE status = 1 AND security_type = 'OS' ORDER BY symbol ASC");
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $key => $value) {
                    echo'<option value="'.$value['symbol_id'].'">'.$value['symbol'].'</option>';
                  }
                ?>
              </select>
            </div>
            <div class="clearfix"></div>
            <div class="col-lg-6">
                <label>From Date<font color="red">*</font></label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                </div>
            </div>
            <div class="col-lg-6">
                <label>To Date<font color="red">*</font></label>
                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="button"  class="btn btn-success" id="rightsaudit" name="rightsaudit" value=""><i class="fa fa-list"></i>  Generate </button>
            </div>
          </div>
         </div>
        <div id="details"></div>         
      </section>
    </div>
    <?php include('../NAV/footer.php') ?>  
  </div>
</body>
<script type="text/javascript">
  $('#rightsaudit').click( function() {
    showLoading();
    var syml_id = $("#symbol_id").val();
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();
    var op = 'rightsaudit';

    if (fromDate == '' || toDate == '' ||syml_id == '' ) {
      hideloading();
      alert("All Fields are Mandatory");
      return false;
    } else {
        $.ajax({
          type: "POST",
          url: "loadReport.php",
          data: 'toDate=' + toDate + '&fromDate=' + fromDate + '&rightsaudit=' + op + '&symbol_id=' + syml_id,
          success: function(data){
            hideloading();
            $("#details").show().html(data);
          }
        });
    }
  });

  function checkDate() {
    var f = document.getElementById("to_date").value;
    var from = new Date(f);
    var t = document.getElementById("from_date").value;
    var to = new Date(t);
    if (from < to) {
      alert("To date should be greater than From date ");
      return false;
    } else {
      return true;
    }
  }
</script>
</html>