<?php 
  include ('session_start_file.php');
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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Finance</a></li> 
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">IPO Finance</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="post">
            <div class="box-body">
              <div class="row">  
                <div class="col-lg-3 col-md-3">
                  <label>CD Code</label>
                  <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="getState23(this.value);" required>
                </div>  
                <div class="col-lg-3 col-md-3">
                  <label>Amount</label>
                  <input type="number" class="form-control" name="amt" id="amt" min="1">
                </div>         
                <div id="cd"></div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-success" style="display:none;" id="cre" name="cre" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Credit </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-warning" style="display:none;" id="deb" name="deb" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-minus"></i>  Debit </button>
              </div>
            </div>
          </form>
        </div>

        <div class="box">
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
              <label>From Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <label>To Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
              </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-success" id="ipo_fin_list" name="ipo_fin_list"><i class="fa fa-list"></i> List </button>
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
  function getState23(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'rights_fin='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  function fun(io) {
    var val= document.getElementById('delete_fin'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
    } else {
      return false;
    }
  }

  $("#deb").click( function() { 
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "deb";
    var dataString = 'cdcode='+ cdcode + '&amt='+ amount +'&rm='+ remark + '&deb='+ operation;
    if(amount === '' || remark === '' ) {
      $("#message").html('<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Please Fill All Mandatory Fields.</div></div></div>');
      showMessage();
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/ipo_process.php",
        data: dataString,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }
    return false;
  });

  $("#cre").click(function(){ 
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "cre";
    var dataString = 'cdcode='+ cdcode + '&amt='+ amount +'&rm='+ remark + '&cre='+ operation;
    if(amount === '' || remark === '') {
      hideloading();
      $("#message").html('<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Please Fill All Mandatory Fields.</div></div></div>');
      showMessage();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/ipo_process.php",
        data: dataString,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
          
        }
      });
    }
    return false;
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

  $('#ipo_fin_list').click( function() {
    showLoading();
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();
    var op = 'ipo_finance';
    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&ipo_finance='+ op,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });
</script>
</html>