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
          <li><a href="#">Sector</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Sector Creation</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="POST" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-6 col-md-6">
                  <label for="name">Sector Name <span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="name" id="name" required>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="1">Active</option>
                    <option value="0">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" name="save_sector" id="save_sector"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Sector List</h4>
              </div>
              <div class="box-body">
                <div id="tableList"></div>
              </div>
              <div class="box-footer"></div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  $(document).ready( function() {
    showLoading();
    var op = 'get_sector_list';
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_sector_list='+op,
      cache: false,
      success: function(data){
        hideloading();
        $('#tableList').html(data);
      }
    });
  });

  $("#save_sector").click(function(event) { 
    event.preventDefault();
    showLoading();

    var nameField = $("#name").val();
    var statusField = $("#status").val();
    var operation = "save_sector";
    
    var data = {
      name: nameField,
      status: statusField,
      save_sector: operation
    };

    if(nameField === '' || statusField === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#message").html(response);
          showMessage();

          // reload the table
          $.ajax({
            type: "POST",
            url: "load.php",
            data:'get_sector_list=get_sector_list',
            cache: false,
            success: function(data){
              hideloading();
              $('#tableList').html(data);
            }
          });

        },
        error: function(xhr, status, error) {
          hideloading();
          console.log(error);
        }
      });
    }
  });

  function editSector(id) {
    $.ajax({
      type: "POST",
      url: "edit.php",
      data:{ edit_sector: id },
      success: function(data){
        $("#myModal").html(data);
        $("#myModal").modal();
      }
    });
  }

  function deleteSector(id, name) {
    showLoading();
    if (confirm("Are you sure you want to delete " + name + " sector?")) {
      const operation = "delete_sector";
      const data = { id: id, delete_sector: operation };
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
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
            $(`tr[data-id="${id}"]`).remove();
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

  function confirmation(){
    showLoading();
    if(confirm("Are you sure want to update role?")){
      return true;
    }else{
      hideloading();
      event.preventDefault();
      return false;
    }
  }
</script>
</html>