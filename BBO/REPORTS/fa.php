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
         <li><a href="#">Finance Activity</a></li>      
      </ol>
    </section>
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Finance Activity</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="col-lg-4 col-md-4">
            <label>CD Code<font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-user"></i>
              </div>
              <input type="text" class="form-control pull-right" maxlength="10" name="cdcode" id="cdcode"  required>
            </div>
            <span id="cd_CodeErr" style="color: red;"></span>
          </div>
          <div class="col-lg-4 col-md-4">
            <label>From Date<font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date1" id="from_date1" onChange="return checkDate1();" required>
            </div>
            <span id="f_dateErr" style="color: red;"></span>
          </div>
          <div class="col-lg-4 col-md-4">
            <label>To Date<font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date1" id="to_date1" required>
            </div>
            <span id="t_dateErr" style="color: red;"></span>
          </div>
        </div>
        <div class="box-footer">
          <div class="col-lg-4 col-md-4">          
              <button type="button" class="btn btn-success" id="financeActivity" name="financeActivity"><i class="fa fa-bars"></i> Generate</button>
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
  $('#financeActivity').click(function() {
    showLoading();
    var cdcodeFld = $("#cdcode").val();
    var fromDate1Fld = $("#from_date1").val();
    var toDate1Fld = $("#to_date1").val();
    var operation = 'fin_conf';

    if(cdcodeFld == ''){
      hideloading();
      $("#cd_CodeErr").html("CD Code required");
      return false;
    }
    if(fromDate1Fld == ''){
      hideloading();
      $("#f_dateErr").html("Select From date");
      return false;
    }
    if(toDate1Fld == ''){
      hideloading();
      $("#t_dateErr").html("Select To date");
      return false;
    }

    var data = {
      cdcode: cdcodeFld,
      fromDate1: fromDate1Fld,
      toDate1: toDate1Fld,
      fin_conf: operation,
    };

    $.ajax({
      type: "POST",
      url: "load_bbo_report.php",
      data: data,
      dataType: 'html',
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });

  function checkDate1(){
    var f= document.getElementById("to_date1").value;
    var from= new Date(f);
    var t= document.getElementById("from_date1").value;
    var to= new Date(t);
    
    if (from < to){
         alert("To date should be greater than From date ");
         return false;
    } else{
         return true;
    }
  }

  $('#cdcode').click(function() {
    $("#cd_CodeErr").html("");
  });
  $('#from_date1').click(function() {
    $("#f_dateErr").html("");
  });

  $('#to_date1').click(function() {
    $("#t_dateErr").html("");
  });
</script>
</html>