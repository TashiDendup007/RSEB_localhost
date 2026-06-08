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
  <div id="cidModal"></div>
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
          <li><a href="#">CID Update</a></li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <form action="" method="POST">
          <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">CID Update</h4>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                  <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                  <i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="row">
                 <div class="col-lg-6 col-md-6">
                  <label>CD Code:<font color="red">*</font></label>
                  <input type="text" class="form-control" maxlength="10" name="cdCode" id="cdCode" onchange="getDtls(this.value);" required>
                </div>
                <div id="getCD"></div>
              </div>
            </div>
            <div class="box-footer">
               <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" id="update_cid" style="display:none;"><i class="fa fa-save"></i> Save</button>
               </div> 
            </div>
          </div>
        </form>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  $("#update_cid").click(function() {
    showLoading();
    var cdField = $("#cdCode").val();
    var cidNoField = $("#newcid").val();
    var nameField = $("#name").val();
    var oldcidField = $("#cid").val();
    var remarkField = $("#remark").val();
    var operation = "update_cid";

    var data = {
      cdCode: cdField, 
      cidNo: cidNoField,
      name: nameField, 
      oldcid: oldcidField,
      update_cid: operation,
      remark: remarkField,
    };

    if (cidNoField == "") {
      $("#cidError").html("Enter new cid number");
      hideloading();
      return false;
    } 

    if (remarkField == "") {
      $("#remarkError").html("Enter remark");
      hideloading();
      return false;
    }

    if (confirm("Do you want to continue?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: 'html',
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    }else{ 
      hideloading();
      return false;
    }
  });

  function getDtls(val) {
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'getCDCodeDetls='+val,
      dataType: 'html',
      success: function(response){
        $("#getCD").html(response);
        $("#update_cid").show();
      }
    });
  }
</script>
</html>
