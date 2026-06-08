<?php
  include('sessionStartFile_cdscss.php');
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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Account</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Account Registration</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="" method="POST">
            <div class="box-body">
              <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <label>Account Type</label>
                    <select class="form-control" id="atype" name="atype" onChange="getState2(this.value);">
                      <option value="">--Select an account type--</option>
                      <option value="I">Individual</option>
                      <option value="J">Corporate</option>
                      <option value="J">Association</option>
                    </select>
                  </div>
                  <div id="cd"></div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6 col-sm-12">
                <button type="buton" style="display:none" class="btn btn-primary" id="save_client" name="save_client" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-save"></i> Save </button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Search Account</h4>
              </div>
              <div class="box-body">
                <form action="" method="POST">
                  <div class="col-lg-6 col-md-6">
                    <div class="input-group margin">
                      <input type="text" class="form-control" name="search_cid" id="search_cid" placeholder="Enter CID/ CD Code/ Name">
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-info btn-flat" id="serach_id"><i class="fa fa-search"></i> Search</button>
                        </span>
                    </div>
                    <span id="searchErr" style="color: red;"></span>
                  </div>
                </form>
                <div id="account_detail"></div>
              </div>
            </div>
          </div>

          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border"><h5 class="box-title">Generate List</h5></div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <label>From Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                  </div>
                  <span id="f_dateErr" style="color: red;"></span>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
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
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <button type="button" class="btn btn-success" id="accs" name="accs" value=""><i class="fa fa-list"></i>  List </button>
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
  $("#save_client").click( function () {
    showLoading();
    debugger;
    var acc_type = $("#atype").val();
    var cd_code = (acc_type == 'I') ? $("#cdcode").val() : $("#cdCodeAC").val();
    var cid = $("#id").val();
    var title = $("#title").val();
    var first_name = $("#fn").val();
    var last_name = $("#ln").val();
    var occupation = $("#occupation").val();
    var nationality = $("#nat").val();
    var dzongkhag = $("#dz").val();
    var tpn_no = $("#tpn").val();
    var phone_no = $("#phone").val();
    var email = $("#email").val();
    var bank = $("#bank").val();
    var bank_acc_no = $("#accno").val();
    var bank_acc_type = $("#bankAccType").val();
    var license_no = $("#licenseNo").val();
    var address = $("#add").val();
    var user_name = $("#save_client").val();
    var commisssion = 0;
    var operation = 'save_client_dtls';

    if (acc_type === '' || cd_code === '' || cid === '' || first_name === '' || dzongkhag === '' || phone_no === '' || bank === '0' || bank_acc_no === '' || bank_acc_type === '' || address === '') {
      $("#message").html('<div class="col-lg-12 col-sm-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Fill all mandatory fields.</div></div>');
      showMessage();
      hideloading();
      return false;
    }

    if ((acc_type === 'I' && (title === '' || occupation === '' || nationality === '')) || (acc_type === 'J' && (license_no === '' || last_name === ''))) {
      $("#message").html('<div class="col-lg-12 col-sm-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Fill all mandatory fields.</div></div>');
      showMessage();
      hideloading();
      return false;
    }

    var data = {
      atype: acc_type,
      cdcode: cd_code,
      title: title,
      fn: first_name,
      ln: last_name,
      occupation: occupation,
      nat: nationality,
      id: cid,
      dz: dzongkhag,
      tpn: tpn_no,
      phone: phone_no,
      email: email,
      bank: bank,
      accno: bank_acc_no,
      bankAccType: bank_acc_type,
      add: address,
      username: user_name,
      commission: commisssion,
      licenseNo: license_no,
      save_client_dtls: operation,
    };

    $.ajax({ 
      type: "POST", 
      url: "../PROCESS/process.php", 
      data: data, 
      dataType: 'html',
      success: function(response) { 
        hideloading();
        $("#message").html(response);
        showMessage();
      } 
    });
  });

  function getState2 (val) {
    $('#save_client').hide();
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'val='+val,
      dataType: "html",
      success: function(response){  
        $("#cd").html(response);
      } 
    });
  }

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

  function getState(val) {
    $.ajax({
      type: "POST", 
      url: "e-cds-css.php",
      data:'edit_cli='+val, 
      success: function(response) { 
        $("#myModal").modal('show');
        $("#myModal").html(response);
      }
    });
  }

  function fun(io) {
    var val = document.getElementById('delete_cli'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      return true;
    } else {
      return false;
    }
  }

  $('#accs').click(function(){
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
