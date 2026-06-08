<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
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
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Assign Broker</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Assign Broker</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="" method="POST" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row">
                <div class="col-lg-4 col-md-4">
                  <label>Participant</label>
                  <select name="broker" id="participant" class="form-control" onchange="getState(this.value);">
                    <option value="">--Select Broker--</option>
                    <?php
                      // $wc= $dbh->prepare("SELECT DISTINCT participant_code FROM users where username like 'MEM%'");
                      $wc= $dbh->prepare("SELECT p.participant_code
                        FROM adm_participants p
                        ORDER BY p.participant_code asc
                      ");
                      $wc->execute();
                      while ($res = $wc->fetch()) {
                      echo '<option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>';
                      }
                    ?>
                  </select>
                </div>
                
                <div id="cd"></div>
                
                <div class="col-lg-4 col-md-4">
                  <label>Type</label>
                  <select class="form-control" name="type" id="type" onchange="getState1(this.value);">
                    <option value="">--Select Type--</option>
                    <option value="IPO">IPO</option>
                    <option value="RIGHTS">RIGHTS</option>
                    <option value="BOND">BOND</option>
                  </select>
                </div>  
                <div id="cd1"></div>            
                <div class="col-lg-4 col-md-4">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="1">Active</option>
                    <option value="2">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" id="save_broker"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Search Assign Broker</h4>
              </div>
              <div class="box-body">
                <form action="" method="POST">
                  <div class="box-body">
                    <div class="row" ng-app="">
                      <div class="col-lg-6 col-md-6">
                        <label for="searchPCode">Participant code</label>
                        <input type="text" class="form-control" name="searchPCode" id="searchPCode">
                      </div>
                      <div class="col-lg-6 col-md-6">
                        <label for="searchUsrName">User Name</label>
                        <input type="text" class="form-control" name="searchUsrName" id="searchUsrName">
                      </div>
                    </div>
                  </div>
                  <div class="box-footer">
                    <div class="col-lg-6 col-md-6">
                      <button type="button" class="btn btn-primary" id="search_id"><i class="fa fa-search"></i> Search</button>
                    </div>
                  </div>
                </form>
              </div>
              <div class="box-body">
                <div id="search_details"></div>
              </div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<style type="text/css">
  .errorClass { background:  #FADBD8  ; }
</style>
<script type="text/javascript">
  $("#save_broker").click(function() { 
    showLoading();
    var participantFld = $("#participant").val();
    var brokerFld = $("#broker").val();
    var typeFld = $("#type").val();
    var rateFld = $("#rate").val();
    var statusFld = $("#status").val();
    var symbolFld = $("#sy").val();
    var operation = "save_broker";

    var data = {
      participant: participantFld,
      broker: brokerFld,
      type: typeFld,
      rate: rateFld,
      status: statusFld,
      symbol: symbolFld,
      save_broker: operation,
    };

    if(brokerFld == ''|| typeFld == '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/ipo_process.php",
        data: data,
        dataType: 'html',
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }
    return false;
  });

  $("#search_id").on("click", function() {
    var $pCodeField = $("#searchPCode");
    var $usrNameField = $("#searchUsrName");
    
    var data = {
      participant_code: $pCodeField.val(),
      usr_name: $usrNameField.val(),
      search_assign_broker: "search_assign_broker"
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php",
      data: data , 
      dataType: 'html',
      success: function(data){ 
        hideloading(); 
        $('#search_details').html(data); 
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Error: "+textStatus+' ,'+errorThrown);
      }
    });
  });


  function getAssignBrokerDtls(val) {
    $.ajax({
      type: "POST",
      url: "bbo-adm.php",
      data:{ edit_assign_broker: val },
      dataType: 'html',
      success: function(data){
        $("#myModal").html(data);
      }
    });
  }

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "load.php",
      data: { load_brokers: val },
      dataType: 'html',
      success: function(data){
        $("#cd").html(data);
      }
    });
  }

  function getState1(val) {
    $.ajax({
      type: "POST",
      url: "load.php",
      data: { load_rate: val },
      dataType: 'html',
      success: function(data){
        $("#cd1").html(data);
      }
    });
  }

  function deleteAssignBrokerDtls(val) {
    showLoading();
    if (confirm("Are you sure you want to delete record Id # " + val + "?")) {
      const operation = "delete_assign_broker";
      const data = { id: val, delete_assign_broker: operation };
      $.ajax({
        type: "POST",
        url: "../PROCESS/ipo_process.php",
        data: $.param(data),
        dataType: "html",
        success: function (response) {
          hideloading();
          var data = JSON.parse(response);
          const statusMsg = $('<div>').addClass('alert alert-success alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
              $('<i>').addClass('icon fa fa-check'), data.message
          );

          $("#message").html(statusMsg);
          showMessage();

          if(data.status == 200){
            $(`tr[data-id="${val}"]`).remove();
          }
        },
        error: function () {
          hideloading();
          const statusMsg = $('<div>').addClass('alert alert-danger alert-dismissible').append(
            $('<button>').addClass('close').attr({ type: 'button', 'data-dismiss': 'alert', 'aria-hidden': 'true' }).html('&times;'),
            $('<i>').addClass('icon fa fa-check'),
            ' Message! Oops sorry! There was an error while operation.'
          );

          $("#message").html(statusMsg);
          showMessage();
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
</script>
</html>
