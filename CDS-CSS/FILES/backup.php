<?php 
    include('sessionStartFile_cdscss.php');
    include ('../../CONNECTIONS/db.php');
    date_default_timezone_set("Asia/Thimphu");
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
           <li><a href="#">BackUp</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Back Up</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="post">
          <div class="box-body">
            <div class="row">
              <div class="col-lg-6 col-md-6">
                <label>Date</label>
               <input type="text" class="form-control pull-right" name="cdate" id="cdate" value=<?php echo date("d-m-Y h:i:s"); ?> readonly>
             </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" class="btn btn-primary" name="backup" id="backup"><i class="fa fa-circle-o"></i> Backup</button>
            </div>
          </div>
          </form>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  $("#backup").click( function() { 
    showLoading();
    var cdate = $("#cdate").val();;
    var operation = "backup";
    var dataString = 'backup='+ operation + '&cdate=' + cdate;

    if (confirm("Do you want to backup file?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#message").html(response);
          showMessage();
        }
      });
    } else {
      hideloading();
      return false;
    }
  });
</script>
</html>

