<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="7")
  {
    header('Location: ../../access.php?err=2');
  }
  $inactive = 1500;
  //check to Configure if $_SESSION['timeout'] is set
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
<body class="hold-transition skin-yellow sidebar-mini">
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
        <li><a href="custodial_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
      <?php 
        $errors = array(1=>"Operation Successfully Completed.",2=>"Oops Sorry! There was an error while operation.",3=>"Record Updated Successfully.",4=>"Record Already Exists.",5=>"Record Deleted Successfully.",6=>"The Client .");
        $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
        if ($error_id == 1) 
        { 
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
        }
        else if ($error_id == 2) 
        {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
        }
        else if ($error_id == 3) 
        {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
        }
        else if ($error_id == 4) 
        {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
        }
        else if ($error_id == 5) 
        {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
        }
        else if ($error_id == 6) 
        {
          $cd=$_GET['cd'];
          $e_vol=$_GET['e_vol'];
          $sy=$_GET['sy'];
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].','.$cd. ' has only '.$e_vol.' , of '.$sy.'</div></div></div>';
        }
      ?>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Custodial Service Dashboard</h4>

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
              <!-- <div class="col-lg-2 col-xs-4">
                <div class="small-box bg-yellow">
                  <div class="inner">
                    <p>SEARCH ACCOUNTS.</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-user"></i>
                  </div>
                  <a href="../FILES/acc-reg.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div>
              <div class="col-lg-2 col-xs-4">
                <div class="small-box bg-yellow">
                  <div class="inner">
                    <p>INDIVIDUAL REPORT</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-users"></i>
                  </div>
                  <a href="../REPORTS/individual_report.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div>
              <div class="col-lg-2 col-xs-4">
                <div class="small-box bg-yellow">
                  <div class="inner">
                    <p>DETAILED TRADE DETAILS</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-bank"></i>
                  </div>
                  <a href="../REPORTS/detailedtradedetails.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div> -->
              <div class="col-lg-2 col-xs-4">
                <div class="small-box bg-yellow">
                  <div class="inner">
                    <p>GENERAL SHAREHOLDER LIST</p>
                  </div>
                  <div class="icon">
                    <i class=""></i>
                  </div>
                  <a href="../REPORTS/generalshareholder_list.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                </div>
              </div>
          </div>
        </div>
      </div>
    </section>
  </div>
<?php include('../NAV/footer.php') ?>  
</body>
</html>
