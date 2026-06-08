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
          <li><a href="#">Role</a></li>
        </ol>
        <?php 
          $errors = array(1=>"Created Role Successfully.",2=>"Oops Sorry! There was an error while operation.",3=>"Update Role Successfully.");
          $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
          if ($error_id == 1 || $error_id == 3) 
          { 
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          else if ($error_id == 2) 
          {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
        ?>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Role Creation</h4>
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
                  <label for="name">Role Name <span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="name" id="name" required>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label for="status">Status</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="1">Active</option>
                    <option value="0">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="submit" class="btn btn-primary" name="save_role" id="save_role" value="Submit"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Role List</h4>
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
  $(document).ready( function () {
    showLoading();
    var op = 'get_role_list';
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_role_list='+op,
      cache: false,
      success: function(data){
        hideloading();
        $('#tableList').html(data);
      }
    });
  });

  function editRole(id) {
    $.ajax({
      type: "POST",
      url: "edit.php",
      data:'edit_role='+id+'&id='+id,
      success: function(data){
        $("#myModal").html(data);
        $("#myModal").modal('show');
      }
    });
  }

  function deleteRole(id, name) {
    showLoading();
    if (confirm("Are you sure you want to delete the "+ name + ' role?')){
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data:'delete_role='+id+'&id='+id+'&name='+name,
        cache: false,
        success: function(data){
          hideloading();
          $("#del_row" + id).fadeOut('slow');
          $("#message").html(data);
          setTimeout(function(){ $('#message').fadeOut();}, 6000); 
        }
      });
    } else {
      hideloading();
      return false;
    }
  }

  function confirmation() {
    showLoading();
    if(confirm("Are you sure want to update role?")) {
      return true;
    } else {
      hideloading();
      event.preventDefault();
      return false;
    }
  }
</script>
</html>