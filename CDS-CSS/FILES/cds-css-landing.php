<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">CDS - CSS Dashboard</h4>
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
                <?php if ($role == 3): ?>
                <div class="col-lg-2 col-xs-4">
                  <div class="small-box bg-green">
                    <div class="inner">
                      <p>ACCOUNT REG.</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-user"></i>
                    </div>
                    <a href="../FILES/account_reg.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>

                <div class="col-lg-2 col-xs-4">
                  <div class="small-box bg-red">
                    <div class="inner">
                      <p>Demat / Remat</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-users"></i>
                    </div>
                    <a href="../FILES/deposit.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>

                <div class="col-lg-2 col-xs-4">
                  <div class="small-box bg-yellow">
                    <div class="inner">
                      <p>Transfer</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bank"></i>
                    </div>
                    <a href="../FILES/transfer.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>

                <div class="col-lg-2 col-xs-4">
                  <div class="small-box bg-black">
                    <div class="inner">
                      <p>Pledge</p>
                    </div>
                    <div class="icon">
                      <i class=""></i>
                    </div>
                    <a href="../FILES/pledge.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>

                <div class="col-lg-2 col-xs-4">
                  <div class="small-box bg-blue">
                    <div class="inner">
                      <p>Corporate Action</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bank"></i>
                    </div>
                    <a href="../FILES/corporate_action.php" class="small-box-footer">Configure <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>
                <?php endif ?>
                
                <?php if ($role == '9'): ?>
                <div class="col-lg-2 col-xs-4">
                  <div class="small-box bg-blue">
                    <div class="inner">
                      <p>Pledge Report</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bank"></i>
                    </div>
                    <a href="../REPORTS/rma_pledge_report.php" class="small-box-footer">Report <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>
                <?php endif ?>

              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
</html>
