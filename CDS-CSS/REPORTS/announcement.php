<?php 
  include('../FILES/sessionStartFile_cdscss.php');
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
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Reports</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Announcement List</h4>

            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-12 col-md-12">
              <label>Status</label>
            </div>
            <div class="col-lg-2 col-md-2">
              <input type="radio"  name="status" id="status" value="0" onclick="getState(this.value);" required>
              <label>Completed</label>              
            </div>
            <div class="col-lg-2 col-md-2">
              <input type="radio"  name="status" id="status" value="1" onclick="getState(this.value);" required>
              <label>Pending</label>              
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-success" style='display: none;' id="annList" name="annList" value="">  Generate </button>
            </div>
          </div>
          <div id="details"></div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>
  </div>
</body>
<script type="text/javascript">
  function getState(val) { 
    var status = val;
    var op = 'announcementList';
    var data = {
      announcementList: op,
      status: status,
    };
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: data,
      dataType: "html",
      success: function (response) {
        $("#details").html(response);
      }
    });
  }
</script>
</html>