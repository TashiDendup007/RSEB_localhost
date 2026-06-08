<?php 
    session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="6")
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
<body class="hold-transition skin-yellow sidebar-mini"><div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
<!-- Site wrapper -->
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Reports</a></li>      
      </ol>
    </section>
    <!-- Main content -->
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Daily Transaction</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
            <br>
            <div class="col-xs-3">
              <label>Symbol</label>
              <input type="text" class="form-control" name="symbol" id="symbol" onclick="getState2(this.value);" required>
            </div>
            <div id="cd"></div>
            <br><br>
            <div id="details"></div><br><br><br>    
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>  

<script type="text/javascript">
function getState2(val) 
{
  $.ajax({ type: "POST", url: "load.php", data:'symbol='+val,success: function(data){ $("#cd").html(data);} });
}
</script>
</body>
</html>