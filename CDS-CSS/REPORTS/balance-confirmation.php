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
            <h4 class="box-title">Balance Confirmation Report</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../REPORTS/loadReport.php" method="post" onsubmit="showLoading();">
            <div class="box-body">
              <div class="col-lg-3 col-md-3">
                <label>CID</label>
                <input type="text" class="form-control" name="cid" id="cid" onChange="getState2(this.value);">
              </div>
              <div class="col-lg-3 col-md-3">
                <label>RATE</label>
                <input type="text" class="form-control" name="rate" id="rate" >
              </div>

              <div class="col-lg-3 col-md-3">
                <label>Date (For Company)</label>
                <input type="date" class="form-control" name="date" id="date">
              </div>
            
              <div class="col-lg-3 col-md-3">
                <label>Currency</label>
                <select class="form-control" id="currency" name="currency" >
                  <option value="">CURRENCY </option>
                  <option value="AUD">AUD</option>
                  <option value="CAD">CAD</option>
                  <option value="EUR">EUR</option>
                  <option value="GBP">GBP</option>
                  <option value="USD">USD</option>
                  <option value="NZD">NZD</option>
                  <option value="CHF">CHF</option>
                  <option value="YEN">YEN</option>
                  <option value="BTN">Company Balance Confirmation</option>
                </select>
              </div>
              <div class="col-lg-12 col-md-12" id="cd"></div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" style="display:none;" class="btn btn-success" style='display: none;' id="individual" name="individual" value=""><i class="fa fa-list"></i>  Generate </button>
              </div>
            </div> 
          </form>
        </div> 
        <div id="details"></div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?> 
</body>
<script type="text/javascript">
  function getState2(val) {
    $("#individual").hide();
    $("#details").hide();
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'individual='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  $('#individual').click(function(){
    showLoading();
    var cid = $("#cid").val();
    var rate = $("#rate").val();
    var currency = $("#currency").val();
    var op = 'BalanceConfirmation';
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'cid='+cid +'&BalanceConfirmation='+ op+'&rate='+ rate+'&currency='+ currency,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").show().html(data);
      }
    });
  });
</script>
</html>