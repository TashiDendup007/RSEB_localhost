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
           <li><a href="#">Bond Subscription</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bond Subscription Report</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../REPORTS/loadReport.php" method="post" onsubmit="showLoading();">
            <div class="box-body">
              <div class="col-lg-4 col-md-4">
                <label>Symbol</label>
                <select class="form-control" id="symbol_id" name="symbol_id" onchange="getSymbol(this.value);" required>
                  <option value="">--Select Symbol--</option>
                  <?php 
                    $sql = $dbh->prepare("SELECT s.symbol_id, s.symbol FROM symbol s WHERE s.security_type = 'CB' AND s.status = 1 ORDER BY s.symbol_id DESC ");
                    $sql->execute();
                    foreach ($sql as $res) {
                      echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                    }
                  ?>
                </select>
                <span id="brokerErrorMsg" style="color: red;"></span>
              </div>

              <div class="col-lg-8 col-md-8" id="symbol_name"></div>

            </div>
            <div class="box-footer">
              <!-- <div class="col-lg-12">
                <span style="color: red; font-size: 18px;">Note: Report generation is temporarily disabled. Please generate the report at the end of the bond subscription period.</span>
              </div> -->
              <div class="col-lg-4 col-md-4">
                <button type="button" class="btn btn-success" id="genearte_report" name="genearte_report"><i class="fa fa-list"></i> Generate</button>
              </div>
            </div>
          </form>

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
  $('#genearte_report').click( function () {
    showLoading();
    var symbol_id = $("#symbol_id").val();
    var op = 'generate_bond_subscription_list';

    if (symbol_id == '') {
      hideloading();
      $('#brokerErrorMsg').html('Select Symbol');
    } else {
      $.ajax({
        type: "POST",
        url: "loadReport.php",
        data: 'symbol_id=' + symbol_id + '&generate_bond_subscription_list=' + op,
        dataType: "html",
        success: function(data) {
          hideloading();
          $("#details").show().html(data);
        }
      });
    }
  });

  function getSymbol(id) {
      $.ajax({
        type: "POST",
        url: "load.php",
        data: 'symbol_id='+id+'&get_symbol_name=get_symbol_name',
        dataType: "html",
        success: function(data){
          hideloading();
          $("#symbol_name").show().html(data);
        }
      });
  }
  
  $('#symbol_id').click( function() {
    $('#brokerErrorMsg').html('');
  });
</script>
</html>