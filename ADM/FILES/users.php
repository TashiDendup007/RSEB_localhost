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
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Users</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">User Creation</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="POST">
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-3 col-md-3">
                  <label for="name">First/Last Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="name" id="name"  required>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Role</label>
                  <select class="form-control" name="role" id="role">
                    <option value="0">- Select a Role -</option>
                    <?php
                      $sql = $dbh->prepare("SELECT id, role_name FROM role_masters WHERE status = 1");
                      $sql->execute();
                      $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
                      foreach ($rows as $row) {
                        echo'<option value="'.$row['id'].'">'.$row['role_name'].'</option>';
                      }
                    ?>
                  </select>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Participants</label>
                  <?php
                    $wc = $dbh->prepare("SELECT DISTINCT participant_code FROM adm_participants");
                    $wc->execute();
                    echo'
                    <select name="pcode" id="pcode" ng-model="pcode" class="form-control">
                      <option value="">--Select Participant--</option>';
                      while($res = $wc->fetch(PDO::FETCH_ASSOC)) {
                        echo'<option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>';
                      }
                      echo'
                    </select>';
                  ?>
                </div>
                 <div class="col-lg-3 col-md-3">
                  <label for="cid">CODE / CID<span style="color:red;">*</span></label>
                  <input type="text" ng-model="cid" class="form-control" name="cid" id="cid" onKeyPress="if(this.value.length==11) return false;">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="email">
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="phone">Phone No</label>
                  <input type="number" class="form-control" onKeyPress="if(this.value.length==8) return false;" name="phone" id="phone" >
                  <span id="errln" style="color:red;display:none;">*Please enter numbers only</span>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="1">Active</option>
                    <option value="2">InActive</option>
                  </select>
                </div>
                <div class="col-lg-3 col-md-3">
                  <label for="un">Username</label>
                  <input type="text" class="form-control" name="un" id="un" value="{{pcode}}{{cid}}" readonly>
                </div>
                <div class="col-lg-12">
                  <label for="add">Address<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="add" id="add" required>
                </div>
              </div>
              <br>
              NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" id="save_users"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Search User</h4>
              </div>
              <form action="" method="POST">
                <div class="box-body">
                  <div class="row" ng-app="">
                    <div class="col-lg-3 col-md-3">
                      <label for="searchname">Name</label>
                      <input type="text" class="form-control" name="searchname" id="searchname">
                    </div>
                    <div class="col-lg-3 col-md-3">
                      <label for="searchcid">CID</label>
                      <input type="text" class="form-control" name="searchcid" id="searchcid">
                    </div>
                    <div class="col-lg-3 col-md-3">
                      <label for="searchphone">Phone No</label>
                      <input type="number" class="form-control" name="searchphone" id="searchphone">
                    </div>
                    <div class="col-lg-3 col-md-3">
                      <label for="searchemail">Email</label>
                      <input type="email" class="form-control" name="searchemail" id="searchemail">
                    </div>
                    <div class="col-lg-3 col-md-3">
                      <label for="searchcd_code">Cd Code</label>
                      <input type="text" class="form-control" name="searchcd_code" id="searchcd_code">
                    </div>
                    <div class="col-lg-9 col-md-9">
                      <label for="searchaddress">Address</label>
                      <input type="text" class="form-control" name="searchaddress" id="searchaddress">
                    </div>
                  </div>
                </div>
                <div class="box-footer">
                  <div class="col-lg-6 col-md-6">
                    <button type="button" class="btn btn-primary" id="search_id"><i class="fa fa-search"></i> Search</button>
                  </div>
                </div>
              </form>
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
<script type="text/javascript"> 
  $("#save_users").click( function(event) {
    event.preventDefault();
    var $name = $("#name");
    var $role = $("#role");
    var $participantCode = $("#pcode");
    var $cid = $("#cid");
    var $email = $("#email");
    var $phone = $("#phone");
    var $status = $("#status");
    var $userName = $("#un");
    var $address = $("#add");
    
    var operation = "save_users";
    var dataString = 'name='+ $name.val() +'&role='+ $role.val() + '&pcode='+ $participantCode.val() +'&cid='+ $cid.val() + '&phone='+ $phone.val() + '&email='+ $email.val() + '&status='+ $status.val() + '&un='+ $userName.val() + '&add='+ $address.val() + '&save_users='+ operation; 

    if ($name.val() === '' || $cid.val() === '' || $address.val() === '' || $role.val() === '') {
      alert("Please Fill All Mandatory Fields");
      return;
    }
    
    showLoading();
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString,
      success: function(response) {
        hideloading();
        $("#message").html(response);
        showMessage();
      }
    });
  });

  $("#search_id").on("click", function() {
    showLoading();
    var $nameField = $("#searchname");
    var $cidField = $("#searchcid");
    var $phoneField = $("#searchphone");
    var $emailField = $("#searchemail");
    var $cdCodeField = $("#searchcd_code");
    var $addressField = $("#searchaddress");
    
    var data = {
      name: $nameField.val(),
      cid: $cidField.val(),
      phone: $phoneField.val(),
      email: $emailField.val(),
      address: $addressField.val(),
      cdCode: $cdCodeField.val(),
      address: $addressField.val(),
      search_users_details: "search_users_details"
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

  function getState(val) { 
    $.ajax({ 
      type: "POST",  
      url: "bbo-adm.php",  
      data:'edit_user='+val,  
      success: function(data){ 
        $("#myModal").html(data);  
      }
    });
  }

  function delete_user(val) {
    const confirmMsg = `Are you sure you want to delete record Id # ${val}?`;

    if (confirm(confirmMsg)) { 
      const $statusMsg = $('.statusMsg6');

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/process.php", 
        data: `delete_usr=${val}&delete_user=delete_user`, 
        beforeSend: function() {
          // $statusMsg.show().html('Deleting...');
          showLoading(); 
        },
        success: function(response) {
          // $statusMsg.html(data);
          // setTimeout(() => $statusMsg.fadeOut(5000), 3000);

          // Remove the deleted record from the DOM
          $(`tr[data-id="${val}"]`).remove();

          $("#message").html(response);
          showMessage();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $statusMsg.html('Error deleting record.');
        },
        complete: function() {
          hideloading(); 
          // $statusMsg.hide();
        }
      });
    }
  }

  $("#cid").keyup('input', function() {
    var cid = $("#cid").val(); 
    var flag=/^[0-9]+$/.test(cid); 
    if (!flag) { 
      $("#errCid").show();
      $("#cid").addClass("errorClass");
    } else {  
      $("#errCid").hide(10); 
      $("#cid").removeClass("errorClass"); 
    } 
  });

  $("#phone").keyup('input', function() {
    var phoneLength = $("#phone").val(); 
    var flag=/^[0-9]+$/.test(phoneLength);
    if(!flag) {
      $("#errln").show();
      $("#phone").addClass("errorClass");
    } else {
      $("#errln").hide(10);
      $("#phone").removeClass("errorClass");
    } 
  });
</script>
<style type="text/css">  .errorClass { background:  #FADBD8  ; }</style>
</html>