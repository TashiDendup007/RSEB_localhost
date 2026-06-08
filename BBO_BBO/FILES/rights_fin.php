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
            <h4 class="box-title">RIGHTS Finance</h4>
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
              <div class="col-lg-3 col-md-3">
                <label>CD Code<font color="red">*</font></label>
                <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="getState23(this.value);" required>
              </div>  
                        
               <div class="col-lg-3 col-md-3">
                <label>Amount<font color="red">*</font></label>
                <input type="number" class="form-control" name="amt" id="amt" min="1">
              </div>         
              <div id="cd"></div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-success" style="display:none;" id="cre" name="cre" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Credit </button> 
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <button type="button" class="btn btn-warning" style="display:none;" id="deb" name="deb" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-minus"></i>  Debit </button>
            </div>
          </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header"></div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <label>From Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                  </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
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
                <div class="col-lg-6"> 
                  <button type="button" class="btn btn-primary" id="fin" name="fin"><i class="fa fa-list"></i>  List</button>
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
    data:'rights_fin='+val, 
    dataType: "html",
    success: function(response) { 
      $("#cd").html(response);
    } 
  });
}

function getState(val) {
  $.ajax({
    type: "POST",
    url: "b-edit.php", 
    data:'edit_fin='+val, 
    dataType: "html",
    success: function(response) { 
      $("#myModal").html(response);
    }
  });
}

  function fun(io) {
    var val= document.getElementById('delete_fin'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
    } else {
      event.preventDefault();
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
    if(amount == ''|| remark == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/right_issue_process.php",
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

  $("#cre").click( function() {
    showLoading();
    var cdcode = $("#cdcode").val();
    var amount = $("#amt").val();
    var remark = $("#rm").val();
    var operation = "cre";
    var dataString = 'cdcode='+ cdcode + '&amt='+ amount +'&rm='+ remark + '&cre='+ operation;
    if(amount == ''|| remark == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/right_issue_process.php",
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

  $('#fin').click( function() {
    showLoading();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    var op = 'financerights';
    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&financerights='+ op,
      dataType: "html",
      success: function(response){
        hideloading();
        $("#details").html(response);
      }
    });
  });
</script>
</html>