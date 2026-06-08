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
  <div id="cidModal"></div>
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
          <li><a href="#">link</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
          <form action="" method="POST" onsubmit="showLoading();">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Link Users</h4>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body">
                <div class="row">
                   <div class="col-lg-4 col-md-4">
                    <label>CID</label>
                    <input type="number" class="form-control" maxlength="11" name="cid" id="cid" onchange="getState(this.value);" required>
                  </div>
                  <div id="cd"></div>
                </div>
              </div>
              <div class="box-footer">
                 <div class="col-lg-4 col-md-4">
                  <button class="btn btn-primary" id="save_linkusers" type="button" style="display:none;"><i class="fa fa-save"></i> Submit</button>
                 </div> 
              </div>
            </div>
         </form>
         <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header">
                <h4 class="box-title">Search Link User</h4>
              </div>
              <form action="" method="POST">
                <div class="box-body">
                  <div class="row" ng-app="">
                    <div class="col-lg-4 col-md-4">
                      <label for="searchCidNo">CID No</label>
                      <input type="text" class="form-control" name="searchCidNo" id="searchCidNo">
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <label for="searchPCode">Participant code</label>
                      <input type="text" class="form-control" name="searchPCode" id="searchPCode">
                    </div>
                    <div class="col-lg-4 col-md-4">
                      <label for="searchCdcode">CD Code</label>
                      <input type="text" class="form-control" name="searchCdcode" id="searchCdcode">
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
  $("#save_linkusers").click(function(){
    showLoading();
    var pCodeFld = $("#pcode");
    var clientAccountFld = $("#ct");
    var unFld = $("#un");
    var bunFld = $("#bun");

    var data = {
      pcode: pCodeFld.val(),
      ct: clientAccountFld.val(),
      un: unFld.val(),
      bun: bunFld.val(),
      save_linkusers: "save_linkusers",
    };

    if(pCodeFld.val() === '' || clientAccountFld.val() === ''){
      alert("Please select the fields!");
      hideloading();
      return false;
    }else{
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data ,
        dataType: 'html',
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }
  });

  $("#search_id").on("click", function() {
    showLoading();
    var $cidNoField = $("#searchCidNo");
    var $pCodeField = $("#searchPCode");
    var $cdCodeField = $("#searchCdcode");
    
    var data = {
      cid_no: $cidNoField.val(),
      part_code: $pCodeField.val(),
      cd_code: $cdCodeField.val(),
      search_linkusers: "search_linkusers"
    };

    $.ajax({ 
      type: "POST", 
      url: "searchItem.php",
      data: data , 
      dataType: 'html',
      success: function(response){ 
        hideloading(); 
        $('#search_details').html(response); 
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Error: "+textStatus+' ,'+errorThrown);
      }
    });
  });

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'pcode_link_user='+val,
      dataType: 'html',
      success: function(response){
        $("#cd").html(response);
        $("#save_linkusers").show();
      }
    });
  }

  function delete_account(io) {
    var val = document.getElementById('delete_linkuser'+io).value;
    if (confirm("Are you sure you want to delete record Id # "+ val + '?')) {
      showLoading();
      const operation = "delete_link_users";
      const data = { delete_link_user: val, delete_link_users: operation };

      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: $.param(data),
        dataType: "html",
        success: function(response){
          hideloading();
          var data = JSON.parse(response);
          $("#message").html(data.message);
          showMessage();
          if(data.status == 200){
            $(`tr[data-id="${val}"]`).remove();
          }
        }
      });
    }else{
      return false;
    }
  }

  function reset_pass(io) {
    var val= document.getElementById('reset_pass'+io).value;
    if (confirm("Are you sure you want to reset the password of record Id # "+ val + '?')) {
      showLoading();
      const operation = "reset_pass";
      const data = { reset_pass_val: val, reset_pass: operation };

      $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: $.param(data),
          dataType: "html",
          success: function(response){
              hideloading();
              $("#message").html(response);
              showMessage();
          }
      });
    }else{
       return false;
    }
  }

  function funCIDNo(i){
    var id = $('#update_cid'+i).val();
    var op = 'getCID';
    var dataString = 'cdCod='+id+'&getCID='+op;
    
    $.ajax({
      type: 'POST',
      url: 'load.php',
      data: dataString,
      success: function(data){
        $('#cidModal').html(data);
        $('#cidModalTarget').modal('show');
      }
    });
  }

  function unlock_account(io) {
    var val = document.getElementById('unlock'+io).value;
    if (confirm("Are you sure you want to unlock account of # "+ val + '?')) {
      showLoading();                  
      const operation = "unlock_account";
      const data = { username: val, unlock_account: operation };

      $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: $.param(data),
          dataType: "html",
          success: function(response){
            hideloading();
            $("#message").html(response);
            showMessage();
          }
        });
    }else{
      return false;
    }
  }
</script>
</html>
