<?php 
  include ('../FILES/session_start_file.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
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
           <li><a href="#">Report</a></li>      
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">BOND Trade Confirmation</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-3 col-md-3 col-sm-12">
              <label>CD CODE <font color="red">*</font></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-user"></i>
                </div>
                <input type="text" class="form-control pull-right" maxlength="10" name="cdcode" id="cdcode"  required>
              </div>
              <span id="cd_code_err" style="color: red;"></span>
            </div>

            <div class="col-lg-3 col-md-3 col-sm-12">
              <label>Symbol <font color="red">*</font></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-list-ul"></i>
                </div>
                <select class="form-control" name="symbol_id" id="symbol_id" required>
                  <option value="">--Select Symbol--</option>
                <?php
                  $getSymbol = $dbh->prepare("SELECT DISTINCT b.symbol_id,  s.symbol
                        FROM bond b 
                        JOIN symbol s ON b.symbol_id = s.symbol_id 
                        LEFT JOIN assign_broker a ON s.symbol_id = a.symbol
                        WHERE a.username = ?
                        ORDER BY s.symbol ASC
                  ");
                  $getSymbol->execute([$username]);
                  $rows = $getSymbol->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $key) {
                    echo'<option value="'.$key['symbol_id'].'">'.$key['symbol'].'</option>';
                  }
                ?>
                </select>
              </div>
              <span id="symbol_err" style="color: red;"></span>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12">
              <label>From Date <font color="red">*</font></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="from_date1" id="from_date1" required>
              </div>
              <span id="from_date_err" style="color: red;"></span>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12">
              <label>To Date <font color="red">*</font></label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="to_date1" id="to_date1" onChange="return checkDate1();" required>
              </div>
              <span id="to_date_err" style="color: red;"></span>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6 col-sm-12">          
              <button type="button" class="btn btn-success" id="ipotradeConfirmation" name="ipotradeConfirmation" value="">  Generate </button>
            </div>
          </div>
          <div id="details"></div>
        </div>    
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>
</body>
<script type="text/javascript">
  function checkDate1() {
    var f= document.getElementById("to_date1").value;
    var from= new Date(f);
    var t= document.getElementById("from_date1").value;
    var to= new Date(t);
    if (from < to){
         alert("To date should be greater than From date ");
         return false;
    } else {
        return true;
    }
  }

  $('#ipotradeConfirmation').click(function() {
    var fromDate1 = $("#from_date1").val();
    var toDate1 = $("#to_date1").val();
    var cdcode = $("#cdcode").val();
    var symbol_id = $("#symbol_id").val();
    var trade_confirmation = 'ipotradeConfirmation';

    if (cdcode == "") {
      $("#cd_code_err").html("Enter CD Code");
      return false;
    }

    if (symbol_id == "") {
      $("#symbol_err").html("Select Symbol");
      return false;
    }

    if (fromDate1 == "") {
      $("#from_date_err").html("Enter From Date");
      return false;
    }

    if (toDate1 == "") {
      $("#to_date_err").html("Enter To Date");
      return false;
    }
    showLoading();
    $.ajax({
      type: "POST",
      url: "bond_load.php",
      data: { toDate1: toDate1, fromDate1: fromDate1, ipotradeConfirmation: trade_confirmation, cdcode: cdcode, symbol_id: symbol_id },
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });

  $("#cdcode").click( function (){
    $("#cd_code_err").html("");
  });

  $("#symbol_id").click( function (){
    $("#symbol_err").html("");
  });
  
  $("#to_date1").click( function (){
    $("#to_date_err").html("");
  });

  $("#from_date1").click( function (){
    $("#from_date_err").html("");
  });
</script>
</html>