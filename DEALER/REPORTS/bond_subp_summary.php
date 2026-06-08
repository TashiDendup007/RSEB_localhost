<?php 
  include ('../FILES/session_start_file.php');
  include ('../../CONNECTIONS/db.php');

  $stmt = $dbh->prepare("SELECT s.symbol, s.symbol_id
          FROM assign_broker a 
          JOIN symbol s ON a.symbol = s.symbol_id 
          WHERE a.username = ? 
          AND a.status = 1
  ");
  $stmt->execute([$username]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
          <h4 class="box-title">BOND Subscription Summary</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">

          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>Symbol <font color="red">*</font></label>
            <select name="symbol_id" id="symbol_id" class="form-control" required>
              <option value="">-- Select --</option>
              <?php 
                foreach ($rows as $key => $value) {
                  echo'<option value="'.$value['symbol_id'].'">'.$value['symbol'].'</option>';
                }
              ?>
            </select>
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
          <div class="col-lg-5 col-md-5 col-sm-12">
            <button type="button" class="btn btn-success" id="subscription_summary" name="subscription_summary"><i class="fa fa-list"></i>  Generate </button>
          </div>
        </div>
      </div>

      <div class="box">
        <div class="box-body">
          <div id="details"></div>
        </div>
      </div> 
      
    </section>
  </div>
</div>
<?php include('../NAV/footer.php'); ?>
</body>
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

  $('#subscription_summary').click( function () {
    showLoading();
    var symbol_id = $("#symbol_id").val();
    var fromDate1 = $("#from_date1").val();
    var toDate1 = $("#to_date1").val();
    var operation = 'bond__subscription__summary';

    if (symbol_id == '' || toDate1 == '' || fromDate1 == '') {
      hideloading();
      alert("Required all Fields");
      return false;
    }

    var data = {
      symbol_id: symbol_id, 
      toDate1: toDate1, 
      fromDate1: fromDate1,
      bond__subscription__summary: operation,
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
</html>