<?php 
  include('sessionStartFile_cdscss.php');
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
          <li><a href="#">CID-Update</a></li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <form action="" method="POST" onsubmit="showLoading();">
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
                <label>CD Code:</label>
                <input type="text" class="form-control" maxlength="10" name="cdCode" id="cdCode" onchange="getState(this.value);" required>
              </div>
              <div id="getCD"></div>
            </div>
          </div>
          <div class="box-footer">
             <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-primary" id="update_cid" type="button" style="display:none;"><i class="fa fa-database"></i> Submit</button>
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
  $("#update_cid").click( function () {
    showLoading();
    var cd = $("#cdCode").val();
    var new_cidNo = $("#newcid").val();
    var name = $("#name").val();
    var oldcid = $("#cid").val();
    var remark = $("#remark").val();
    var part_code = $("#participate_code").val();
    var institute_id = $("#institute_id").val();
    var operation = "update_cid";

    if (oldcid === new_cidNo) {
      alert("The new and old CID numbers cannot be the same.");
      hideloading();
      return false;
    }

    var dataString = 'cdCode=' + cd + '&new_cidNo=' + new_cidNo + '&name=' + name + '&oldcid=' + oldcid + '&update_cid=' + operation + '&remark=' + remark + '&part_code=' + part_code + '&institute_id=' + institute_id;

    if(new_cidNo === "" || remark === "" || cd === "") {
      alert("Completion of all fields is required.");
      hideloading();
      return false;
    }
    else {
      if (confirm("Are you sure you want to update?")) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: dataString,
          dataType: "html",
          success: function(data) {
            hideloading();
            $("#cdCode").val("");
            $("#message").html(data);
            showMessage();
            // setTimeout(function(){ location.reload(); }, 4000);
          }
        });
      } else { 
          hideloading();
          return false;
      }

    }
  });

  function getState(val) {
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'getCDCodeDetls='+val,
      success: function(response){
        $("#getCD").html(response);
        $("#update_cid").show();
      }
    });
  }
</script>
</html>
