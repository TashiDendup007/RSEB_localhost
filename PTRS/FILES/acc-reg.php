<?php 
  include('session_file.php'); 
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-yellow sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="ptrs_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Account</a></li>      
        </ol>
      </section> 
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Search Account</h4>
          </div>
          <div class="box-body">
            <form action="" method="POST">
              <div class="input-group margin">
                <input type="text" class="form-control" name="search_cid" id="search_cid" placeholder="Enter CID/ CD Code/ Name">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-info btn-flat" id="serach_id"><i class="fa fa-search"></i> Search</button>
                  </span>
              </div>
              <span id="searchErr" style="color: red;"></span>
            </form>
            <div id="account_detail"></div>
          </div>
          <div class="box-footer"></div>
        </div>
         
        <div class="box">
          <div class="box-header with-border"></div>
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
              <label>From Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
              </div>
              <span id="f_dateErr" style="color: red;"></span>
            </div>
            <div class="col-lg-6 col-md-6">
              <label>To Date</label>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
              </div>
              <span id="t_dateErr" style="color: red;"></span>
            </div>
          </div>
          <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" id="accs" name="accs"><i class="fa fa-list"></i> List </button>
              </div>
          </div>
          <div id="details"></div>
        </div>

      </section>
    </div>
    <?php include('../NAV/footer.php') ?> 
  </div>
</body>
<script type="text/javascript">

  $("#serach_id").click(function() {
    var cidNoField = $("#search_cid");
    var operation = "search_accounts";

    if (cidNoField.val() == "") {
      $("#searchErr").html("Field Required");
      return false;
    }

    var data = {
      cid_number: cidNoField.val(),
      search_accounts: operation,
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php", 
      data: data, 
      dataType: 'html',
      success: function(response){ 
        $("#account_detail").html(response);
      } 
    });
  });

  $('#search_cid').click(function() {
    $("#searchErr").html("");
  });

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "e-cds-css.php",
      data:'edit_cli='+val, 
      dataType: "html",
      success: function(data){ 
        $("#myModal").modal('show');
        $("#myModal").html(data);
      }
    });
  }
  
  $('#accs').click( function() {
    showLoading();
    var fromDateFld = $("#from_date").val();
    var toDateFld = $("#to_date").val();
    var operation = 'accs';

    if(fromDateFld == '') {
      hideloading();
      $("#f_dateErr").html("Select From date");
      return false;
    }
    if(toDateFld == '') {
      hideloading();
      $("#t_dateErr").html("Select To date");
      return false;
    }

    var data = {
      fromDate: fromDateFld,
      toDate: toDateFld,
      accs: operation,
    };

    $.ajax({
      type: "POST",
      url: "load.php",
      data: data,
      dataType: 'html',
      success: function(response) {
        hideloading();
        $("#details").html(response);
      }
    });
  });

  $('#from_date').click(function() {
    $("#f_dateErr").html("");
  });

  $('#to_date').click(function() {
    $("#t_dateErr").html("");
  });
</script>
</html>