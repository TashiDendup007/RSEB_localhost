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
           <li><a href="#">BBO Finance</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Finance</h4>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
                </div>
              </div>
              <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
                <div class="box-body">
                  <div class="row">  
                    <div class="col-lg-3">
                      <label>CD Code<font color="red">*</font></label>
                      <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="getState23(this.value);" required>
                    </div>  
                    <div class="col-lg-3">
                      <label>Amount<font color="red">*</font></label>
                      <input type="number" class="form-control" name="amt" id="amt" min="1">
                    </div>         
                    <div id="cd"></div>
                    <span id="errorMsg" style="color: red;"></span>
                  </div>
                </div>
                <div class="box-footer">
                  <div class="col-lg-12">
                    <button type="button" class="btn btn-success" style="display: none; margin-right: 20px;" id="cre" name="cre" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Credit </button> 
                    <button type="button" class="btn btn-warning" style="display: none;" id="deb" name="deb" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-minus"></i>  Debit </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">Finance List</div>
              <div class="box-body">
                <div class="col-lg-6">
                  <label>From Date<font style="color: red;">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                  </div>
                  <span id="fromDateErr" style="color: red;"></span>
                </div>
                <div class="col-lg-6">
                  <label>To Date<font style="color: red;">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                  </div>
                  <span id="toDateErr" style="color: red;"></span>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-lg-6">          
                  <button type="button" class="btn btn-primary" id="fin" name="fin" value=""><i class="fa fa-list"></i> List </button>
                </div>
              </div>
              <div id="details"></div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  function getState23(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'fin='+val,
      success: function(data) { 
        $("#cd").html(data);
      } 
    });
  }

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "b-edit.php",
      data:'edit_fin='+val, 
      success: function(data){ 
        $("#myModal").html(data);
      }
    });
  }

  function fun(io) {
    var val= document.getElementById('delete_fin'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?'))
    {
      return true;
    }else{
      return false;
    }
  }

  $("#deb").click(function(){ 
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "deb";
    var dataString = 'cdcode='+ cdcode + '&amt='+ amount +'&rm='+ remark + '&deb='+ operation;
    if(amount==''|| remark=='')
    {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString ,
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }
    return false;
  });

  $("#cre").click( function() {
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "cre";
    var dataString = 'cdcode='+ cdcode + '&amt='+ amount +'&rm='+ remark + '&cre='+ operation;
    if (amount==''|| remark=='') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
      return false;
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString ,
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }
  });

 function checkDate() {
  var f= document.getElementById("to_date").value;
  var from= new Date(f);
  var t= document.getElementById("from_date").value;
  var to= new Date(t);
    if (from < to)
    {
      alert("To date should be greater than From date ");
      return false;
    }else{
      return true;
    }
 }

  $('#fin').click(function() {
    showLoading();
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();
    var op = 'finance';

    if (fromDate == '') {
      hideloading();
      $("#fromDateErr").html("Select from date");
      return false;
    }

    if (toDate == '') {
      hideloading();
      $("#toDateErr").html("Select to date");
      return false;
    }

    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&finance='+ op,
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });

  $('#from_date').click(function() {
    $("#fromDateErr").html("");
  });

  $('#to_date').click(function() {
    $("#toDateErr").html("");
  });
</script>
</html>