<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="1")
  {
    header('Location: ../../access.php?err=2');
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); 
    }
  }
  $_SESSION['timeout'] = time();
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
  <div id="cidModal"></div>
  <div class="wrapper">
  <?php include('../NAV/navigation.php') ?>
    <div class="content-wrapper">
      <div class="box-body" id="message" style='display: none;'></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">KiduAction</a></li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <form action="" method="POST" onsubmit="showLoading();">
          <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">View Share Details</h4>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                  <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                  <i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-6">
                  <label>CID Number:</label>
                  <input type="number" class="form-control" maxlength="11" name="cidNo" id="cidNo" required>
                  <br>
                  <input type="button" class="btn btn-primary" id="loadDtlsId" value="Load From CID Number">
                </div>

                <div class="col-xs-6">
                  <label>Email:</label>
                  <input type="text" class="form-control" name="emailId" id="emailId" required>
                  <br>
                  <input type="button" class="btn btn-primary" id="loadDtlsMailId" value="Load From Email">
                </div>

              </div>
            </div>
            <div class="box-footer"></div>
          </div>

          <div class="box" style="display: none;" id="boxId">
            <div class="box-body">
              <div class="row">
                <div class="col-sm-12 table-responsive">
                  <div id="loadDtls"></div>
                </div>
              </div>
            </div>
          </div>

        </form>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?> 
</body>
<script type="text/javascript">
function showLoading() {
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
}
function hideloading() {
    document.getElementById('loadingmsg').style.display = 'none';
    document.getElementById('loadingover').style.display = 'none';
}
</script>
<script language=JavaScript>
  $('#loadDtlsId').click(function (){
    var cidId = $('#cidNo').val();
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'loadShareAucDtls='+cidId,
      success: function(data){
        $("#boxId").show();
        $("#loadDtls").html(data);
      }
    });
  });

  $('#loadDtlsMailId').click(function (){
    var emailId = $('#emailId').val();
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'loadAucDtlsFromMail='+emailId,
      success: function(data){
        $("#boxId").show();
        $("#loadDtls").html(data);
      }
    });
  });
</script>
</html>
