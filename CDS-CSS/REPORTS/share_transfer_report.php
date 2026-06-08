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
            <h4 class="box-title">Share Transfer</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">

            <div class="col-lg-4 col-md-4">
              <label>From Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
              </div>
            </div>

            <div class="col-lg-4 col-md-4">
              <label>To Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
              </div>
            </div>

            <div class="col-lg-4 col-md-4">
              <label>Type</label>
              <select name="tr_type" id="tr_type" class="form-control" required>
                <option value="">--Select Type--</option>
                <option value="ALL">ALL</option>
                <option value="TR">Intra Transfer (Posting)</option>
                <option value="ST">Inter Transfer (Inheritance)</option>
              </select>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-success" id="generate__list__id" name="generate__list__id"><i class="fa fa-list"></i> Generate</button>
            </div>
          </div>
        </div>

        <div id="details"></div>
        
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>  
  </div>
</body>
<script type="text/javascript">
  $('#generate__list__id').click( function() {
    showLoading();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    var tr_type = $("#tr_type").val();
    var op = 'get_share_transfer_report';

    if (tr_type == '') {
      hideloading();
      alert("Select Type");
      return false;
    }
    
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'toDate=' + toDate + '&fromDate=' + fromDate + '&get_share_transfer_report=' + op + '&tr_type=' + tr_type,
      dataType: "html",
      success: function( data ) {
        hideloading();
        $("#details").show().html(data);
      }
    });
  });

  function checkDate() {
    var f = document.getElementById("from_date").value;
    var from = new Date(f);
    var t = document.getElementById("to_date").value;
    var to = new Date(t);
    if (from > to) {
      alert("To date should be greater than From date ");
      return false;
    } else {
      return true;
    }
  }
</script>
</html>