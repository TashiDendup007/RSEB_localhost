<?php 
    session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="3")
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
<body class="hold-transition skin-green sidebar-mini">
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
        <li class="active">Participants</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Participants Registration</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="box-body">
              <div class="row">
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Code</label>
                  <input type="text" class="form-control" name="code" id="code" required>
                </div>
                <div class="col-xs-4">
                  <label>Type</label>
                  <select class="form-control">
                    <option>Corporate</option>
                    <option>Individual</option>
                    <option>Government</option>
                  </select>
                </div>
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Name</label>
                  <input type="text" class="form-control" name="name" id="name" required>
                </div>
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Contact Person</label>
                  <input type="text" class="form-control" name="cp" id="cp" required>
                </div>
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Address</label>
                  <input type="text" class="form-control" name="add" id="add" required>
                </div>
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Country</label>
                  <input type="text" class="form-control" name="country" id="country" required>
                </div>
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Email address</label>
                  <input type="email" class="form-control" name="email" id="email">
                </div>
              </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
        <div class="col-xs-4">
                <button type="submit" class="btn btn-primary">Submit</button>
        </div>
        </div>
        <!-- /.box-footer-->
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>  
</body>
</html>
