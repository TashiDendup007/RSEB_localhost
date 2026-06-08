<?php 
    include('sessionStartFile_admin.php');
    include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-purple sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php 
      include('../NAV/navigation.php'); 
      include ('../../CONNECTIONS/confirmationMessage.php'); 
    ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Password Reset</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">User Password Reset</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="POST">
            <div class="box-body">
              <div class="row">
                <div class="col-lg-6 col-md-6">
                  <label for="user_name">Username<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="user_name" id="user_name" placeholder="Please enter username" required>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" id="get_user_dtls"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>

          <div class="box-body">
            <div id="user_details"></div>
          </div>

        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript"> 

  $("#get_user_dtls").on("click", function() {
    var $usrField = $("#user_name");

    if ($usrField.val() == '') {
      alert("Required username.");
      return false;
    }
    
    showLoading();
    var data = {
      usr_name: $usrField.val(),
      get_user_details: "get_user_details"
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php",
      data: data , 
      dataType: 'html',
      success: function(data){ 
        hideloading(); 
        $('#user_details').html(data); 
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Error: "+textStatus+' ,'+errorThrown);
      }
    });
  });

  function reset_pwd_adm(val) {
    const confirmMsg = `Are you sure you want to reset password?`;

    if (confirm(confirmMsg)) { 
      const $statusMsg = $('.statusMsg6');

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/process.php", 
        data: `user_pri_id=${val}&reset_pwd_from_adm=reset_pwd_from_adm`, 
        beforeSend: function() {
          showLoading(); 
        },
        success: function(response) {
          // Remove the deleted record from the DOM
          $("#message").html(response);
          showMessage();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $statusMsg.html('Error.');
        },
        complete: function() {
          hideloading(); 
        }
      });
    }
  }

  function inactive_user(val) {
    const confirmMsg = `Are you sure you want to set user as inactive?`;

    if (confirm(confirmMsg)) { 
      const $statusMsg = $('.statusMsg6');

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/process.php", 
        data: `user_pri_id=${val}&set_user_inactive=set_user_inactive`, 
        beforeSend: function() {
          showLoading(); 
        },
        success: function(response) {
          // Remove the deleted record from the DOM
          $("#message").html(response);
          showMessage();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $statusMsg.html('Error.');
        },
        complete: function() {
          hideloading(); 
        }
      });
    }
  }

  function active_user(val) {
    const confirmMsg = `Are you sure you want to set user as active?`;

    if (confirm(confirmMsg)) { 
      const $statusMsg = $('.statusMsg6');

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/process.php", 
        data: `user_pri_id=${val}&set_user_active=set_user_active`, 
        beforeSend: function() {
          showLoading(); 
        },
        success: function(response) {
          // Remove the deleted record from the DOM
          $("#message").html(response);
          showMessage();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $statusMsg.html('Error.');
        },
        complete: function() {
          hideloading(); 
        }
      });
    }
  }
</script>
<style type="text/css">  .errorClass { background:  #FADBD8  ; }</style>
</html>