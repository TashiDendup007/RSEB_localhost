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
<body class="hold-transition skin-green sidebar-mini"><div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
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
         <li><a href="#">Upload</a></li>      
      </ol>
    </section> 
    <!-- Main content -->
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Upload</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="../PROCESS/process.php" method="post" enctype="multipart/form-data" id="myForm"  onsubmit="showLoading();"> 
          <div class="box-body">
          <?php 
            $msg = array(
            1=>"Successful Operation!",
            2=>"Error While Operation! ",
            3=>"Data Has been Already uploaded! ",
            4=>"Already uploaded twice. Please try changing the year! ",
            );
              $msg_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
              if ($msg_id == 1) 
              {
              echo'<div class="alert alert-success">'.$msg[$msg_id].'</div>';
              //echo'<p class="alert alert-success">'.$msg[$msg_id].'</p>';
              }
              elseif ($msg_id == 2) 
              {
                echo '<div class="alert alert-error">'.$msg[$msg_id].' </div>';
                //  echo '<p class="alert alert-danger">'.$msg[$msg_id].'</p>';
              }
              elseif ($msg_id == 3) 
              {
                echo '<div class="alert alert-error">'.$msg[$msg_id].' </div>';
                //  echo '<p class="alert alert-danger">'.$msg[$msg_id].'</p>';
              }
              elseif ($msg_id == 4) 
              {
                echo '<div class="alert alert-error">'.$msg[$msg_id].' </div>';
                //  echo '<p class="alert alert-danger">'.$msg[$msg_id].'</p>';
              }
            ?>
              <div class="row"> 
                <div class="col-xs-3">
                  <div class="form-group">
                  <label class="custom-file-label" for="customFile">Upload file</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input form-control" name="vasplus_multiple_files" onchange="show_button()">
                  </div>
                  </div>
                </div>
              </div>
              <div id="cd">                  
              </div>
            </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <div class="col-xs-4">
                <button id="button" type="submit" style="display:none" class="btn btn-primary" id="excelUpload" name="excelUpload" value="<?php echo $_SESSION['sess_username'];?>"> SAVE </button>
            </div>
          </div>
        </form>
        
      </div>
        <!-- /.box-footer-->
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>  
  <script type="text/javascript">
    $(document).ready(function() {
      
          $('.alert-success').delay(2000).fadeOut('slow');
          $('.alert-error').delay(2000).fadeOut('slow');
    });

    function show_button()
    {
      $('#button').show();
    }
  </script>
</body>
</html>