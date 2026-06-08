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
          <h4 class="box-title">BOND Subscription</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>CD CODE <font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-user"></i>
              </div>
              <input type="text" class="form-control pull-right" maxlength="10" name="cdcode" id="cdcode"  required>
            </div>
          </div>
          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>From Date <font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date1" id="from_date1" required>
            </div>
          </div>
          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>To Date <font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date1" id="to_date1" onChange="return checkDate1();"  required>
            </div>
          </div>
        </div>
        <div class="box-footer">
          <div class="col-lg-4 col-md-4 col-sm-12">
            <button type="button" class="btn btn-success" id="subscription" name="subscription" value="">  Generate </button>
          </div>
        </div>
        <div id="details"></div>
      </div>   
    </section>
  </div>
</div>
<?php include('../NAV/footer.php'); ?>
<script type="text/javascript">
    function checkDate1(){
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

  $('#subscription').click(function(){
    showLoading();
    var toDate1 = $("#to_date1").val();
    var fromDate1 = $("#from_date1").val();
    var cdcode = $("#cdcode").val();
    var trade_confirmation = 'subscription';

    var data = {
      toDate1: toDate1, 
      fromDate1: fromDate1,
      subscription: trade_confirmation,
      cdcode: cdcode,
    };

    $.ajax({
      type: "POST",
      url: "bond_load.php",
      data: data,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });
</script>
</body>
</html>