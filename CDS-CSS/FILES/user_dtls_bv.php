<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  
  $id = isset($_GET['id']) ? $_GET['id'] : 0;
  $pass_code = $_SESSION['sess_part_code'];
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
        <div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Online Terminal</a></li>
        </ol>
      </section>
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">User Details</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i> </button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
          <div class="box-body">
            <div class="box-body">
              <div class="row">
                <?php 
                  $sql = $dbh->prepare("SELECT * FROM api_online_terminal a WHERE a.user_online_id=:uId AND a.status= 'SUB'");
                  $sql->bindParam(':uId', $id);
                  $sql->execute();
                  $res=$sql->fetch();
                  echo'
                  <input type="hidden" value="'.$username.'" name="username" id="username">
                  <input type="hidden" value="'.$id.'" name="onlineUsrId" id="onlineUsrId">
                  <div class="col-xs-4">
                    <label>CID No</label>
                    <input type="text" class="form-control" name="cid" id="cid" value="'.$res['cid'].'" readonly>
                  </div>
                  <div class="col-xs-4">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" id="name" value="'.$res['name'].'" readonly>
                  </div>
                  <div class="col-xs-4">
                    <label>Phone No</label>
                    <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" required>
                  </div>
                  <div class="col-xs-4">
                    <label>Email</label>
                    <input type="text" class="form-control" name="email" id="email" value="'.$res['email'].'" required>
                  </div>
                  <div class="col-xs-4">
                    <label>Participant</label>
                    <input type="text" class="form-control" name="pCode" id="pCode" value="'.$res['participant_code'].'" readonly>
                  </div>
                  <div class="col-xs-4">
                    <label>CD Code</label>
                    <input type="text" class="form-control" name="cdCode" id="cdCode" value="'.$res['cd_code'].'" readonly>
                  </div>
                  <div class="col-xs-12">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" id="address" value="'.$res['address'].'" readonly>
                  </div>';
                ?>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="submit" class="btn btn-success btn-lg" id="online_terminal_verification" name="online_terminal_verification" value="BV"> Verify</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <!-- <button type="submit" class="btn btn-danger btn-lg" id="online_terminal_verification" name="online_terminal_verification" value="B_REJ"> Reject</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
              <button type="button" class="btn btn-warning btn-lg" onclick="returnBack();"> Cancel</button>
            </div>
          </div>
          </form><br>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
<script type="text/javascript">
  function showLoading() {
      document.getElementById('loadingmsg').style.display = 'block';
      document.getElementById('loadingover').style.display = 'block';
  }
  
  function returnBack(){
    window.location.replace("user_list_bv.php");
  }
</script>
</html>
