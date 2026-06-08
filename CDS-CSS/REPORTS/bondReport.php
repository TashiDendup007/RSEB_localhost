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
        <h1><small></small></h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Bond Report</a></li>      
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bond Report</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
              <label>Select Report Type:</label>
              <select name="reportType" id="reportType" class="form-control" onchange="getReportType(this.value)">
                <option value="0">--Select--</option>
                <option value="1">Coupon Payment Calendar</option>
                <option value="2">Coupon Payment List</option>
                <option value="3">Redemption</option>
              </select>
            </div>
            <div class="col-lg-6" id="symbolDivId" style="display: none;">
              <label>Date</label>
              <select name="symbolId" id="symbolId" class="form-control">
                <option value="0"> Select </option>
                <?php
                $wc = $dbh->prepare("SELECT s.symbol_id, s.symbol, a.date
                  FROM coupon_payable_date a
                  LEFT JOIN symbol s ON a.symbol_id = s.symbol_id 
                  WHERE a.status = 1
                ");
                $wc->execute();
                while ($res= $wc->fetch()) {
                  echo '<option value="'.$res['symbol_id'].'">'.$res['date'].' ('.$res['symbol'].')</option>';
                }
                ?>
              </select>
              <span id="symbolErrorId" style="color: red;"></span>
            </div>
            <div class="col-lg-6 col-md-6" id="symbolDivId22" style="display: none;">
              <label>Symbol:</label>
              <select name="symbolId22" id="symbolId22" class="form-control" onchange="getMatDate(this.value)">
                <option value="0"> Select </option>
                <?php
                  $wc= $dbh->prepare("SELECT
                    s.name, s.symbol_id, s.symbol
                    FROM symbol s 
                    WHERE s.security_type IN ('GB', 'CP', 'CB') AND s.status=1 AND s.trsstatus IN (1, 2) 
                    ORDER BY s.symbol_id DESC");
                  $wc->execute();
                  while($res= $wc->fetch())
                  {
                    echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                  }
                ?>
                </select>
              <span id="symbol22ErrorId" style="color: red;"></span>
            </div>
            <div class="col-lg-6 col-md-6" id="maturityDateDiv" style="display: none;">
              <label>Maturity Date (mm/dd/yyyy):</label>
              <input type="text" name="maturityDate" id="maturityDate" class="form-control" readonly="true">
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-12" style="display: none;" id="generateDivId">          
              <button type="button" class="btn btn-success" id="generateId"><i class="fa fa-list"></i> Generate</button>
            </div>
          </div> 
          <div id="details"></div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>  
  </div>
</body>
<script type="text/javascript">
  function getMatDate(symbolId) {
    showLoading();
    var op = 'getMaturityDate';
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'getMaturityDate='+op+'&symbolId='+symbolId,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#maturityDate").val(data);
      }
    });
  }

  function getReportType(id) {
    if (id == 1) {
      $('#generateDivId').show();
      $('#symbolDivId').hide();
      $('#symbolDivId22').hide();
      $('#maturityDateDiv').hide();
    } else if (id == 2) {
      $('#generateDivId').show();
      $('#symbolDivId').show();
      $('#symbolDivId22').hide();
      $('#maturityDateDiv').hide();
    } else if(id == 3) {
      $('#generateDivId').show();
      $('#symbolDivId22').show();
      $('#maturityDateDiv').show();
      $('#symbolDivId').hide();
    } else {
      $('#generateDivId').hide();
      $('#symbolDivId').hide();
      $('#symbolDivId22').hide();
      $('#maturityDateDiv').hide();
    }
  }

  $('#symbolId').click(function(){
    $('#symbolErrorId').html('');
  });
  $('#symbolId22').click(function(){
    $('#symbol22ErrorId').html('');
  });

  $('#generateId').click( function() {
    showLoading();
    var reportType = $('#reportType').val();
    var symbolId = $("#symbolId").val();
    var symbol22 = $("#symbolId22").val();
    
    if(reportType == 2) {
      if(symbolId == 0){
        $('#symbolErrorId').html('Select Symbol');
        hideloading();
        return false;
      }
    }

    if(reportType == 3) {
      if(symbol22 == 0){
        $('#symbol22ErrorId').html('Select Symbol');
        hideloading();
        return false;
      }
    }
    var op = 'bondReport';
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'reportType='+reportType+'&symbolId='+symbolId+'&bondReport='+op+'&symbol22='+symbol22,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").show().html(data);
      }
    });
  });

  // function gefun(i){
  //   if (confirm("Are you sure you want to generate ?")){
  //     return true;
  //   }
  //   else{
  //     return false;
  //   }
  // }
</script>
</html>