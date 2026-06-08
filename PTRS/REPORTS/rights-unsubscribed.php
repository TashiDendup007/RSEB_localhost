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
          <h4 class="box-title">Rights Unsubscribed List</h4>
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
            <select name="symbol_id" id="symbol_id" class="form-control" onchange="getRecordDates(this.value);" required>
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

          <div class="col-lg-6">
            <label>Record Date<font color="red">*</font></label>
            <select name="corp_annou_id" id="corp_annou_id" class="form-control" required>
              <option value="">--Select--</option>
            </select>
          </div>

          <!-- <div class="clearfix"></div>
          <p style="padding-left: 18px; font-weight: bold; margin-top: 11px; color: #0296a7;">Subscription Date</p>
          <hr style="margin-top: 7px!important; margin-bottom: 0px!important;"> -->

          <div class="col-lg-6">
            <label>Subscription From Date<font color="red">*</font></label>
            <input type="date" name="from_date" id="from_date" class="form-control" required>
          </div>

          <div class="col-lg-6">
            <label>To Date<font color="red">*</font></label>
            <input type="date" name="to_date" id="to_date" class="form-control" required>
          </div>

          <!-- <div class="col-xs-3">
            <label>Symbol</label>
            <input type="text" class="form-control"  name="symbol" id="symbol" onChange="getState2(this.value);" required>
          </div>
          <div id="cd"></div>  -->

        </div>
        <div class="box-footer">
            <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" id="gen_unsubscribe_rights" name="gen_unsubscribe_rights"><i class="fa fa-list"></i> Generate</button>
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
  function getState2(val) {
    $("#gen_unsubscribe_rights").hide();
    $("#details").hide();
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'symbolGSholdList='+val,
      success: function(data) {
        $("#cd").html(data);
      } 
    });
  }

  $('#gen_unsubscribe_rights').click( function () { 
    showLoading();
    // var symbol = $("#symbol").val();
    var symbol = $("#symbol_id").val();
    var corporate_id = $("#corp_annou_id").val();
    var from_date = $("#from_date").val();
    var to_date = $("#to_date").val();
    var op = 'unsubscribed_list';

    if (symbol == '' || corporate_id == '') {
      hideloading();
      alert("All Fields are Mandatory");
      return false;
    } else {
      $.ajax({
        type: "POST",
        url: "loadReport.php",
        data: 'unsubscribed_list=' + op + '&symbol=' + symbol + '&corp_ann_id=' + corporate_id + '&from_date=' + from_date + '&to_date=' + to_date,
        dataType: "html",
        success: function(data){
          hideloading();
          $("#details").show().html(data);
        }
      });
    }
  });

  function getRecordDates(id) {
    if (id == '') {
      $("#corp_annou_id").html('<option value="">--Select--</option>');
    } else {
      $.ajax({
        type: "POST", 
        url: "load.php", 
        data: {getRecordDateList: 'getRecordDateList', symbol_id: id},
        dataType: "html",
        success: function(response) {
          $("#corp_annou_id").html(response);
        } 
      });
    }
  }
</script>
</html>