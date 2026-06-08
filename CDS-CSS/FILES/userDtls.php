<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  $id = isset($_GET['id']) ? $_GET['id'] : 0;
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
          <li><a href="#">mCaMS</a></li>
        </ol>
      </section>
      <section class="content">
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
                  $sql = $dbh->prepare("SELECT a.cid, a.name, a.participant_code, a.phone, a.email, a.cd_code, a.address 
                    FROM api_online_terminal a 
                    WHERE a.user_online_id = :uId AND a.status = 'BV'
                  ");
                  $sql->bindParam(':uId', $id);
                  $sql->execute();
                  $res = $sql->fetch();
                  echo'
                  <input type="hidden" value="'.$username.'" name="username" id="username">
                  <input type="hidden" value="'.$id.'" name="onlineUsrId" id="onlineUsrId">
                  <div class="col-lg-4 col-md-4">
                    <label>CID No</label>
                    <input type="text" class="form-control" name="cid" id="cid" value="'.$res['cid'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" id="name" value="'.$res['name'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                  <label>Role</label>';
                      $get = $dbh->prepare("SELECT id, role_name FROM role_masters WHERE status = 1");
                      $get->execute();
                      $options = '';
                      while ($rows = $get->fetch()) {
                        $selected = '';
                        if ($rows['id'] == 4) {
                          $selected = 'selected';
                        }
                        $options .= '<option value="'.$rows['id'].'" '.$selected.'>'.$rows['role_name'].'</option>';
                      }
                    echo'<select name="role" id="role" class="form-control"> '.$options.' </select>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Participant</label>
                    <input type="text" class="form-control" name="pCode" id="pCode" value="'.$res['participant_code'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Phone No</label>
                    <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Email</label>
                    <input type="text" class="form-control" name="email" id="email" value="'.$res['email'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Status</label>
                    <select class="form-control" name="status" id="status">
                      <option value="1" selected>Active</option>
                      <option value="2">InActive</option>
                    </select>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label for="online_un">Username</label>
                    <input type="text" class="form-control" name="online_un" id="online_un" value="'.$res['participant_code'].''.$res['cid'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label for="cdCode">Cd Code</label>
                    <input type="text" class="form-control" name="cdCode" id="cdCode" value="'.$res['cd_code'].'" readonly>
                  </div>
                  <div class="col-lg-12">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" name="address" id="address" value="'.$res['address'].'" readonly>
                  </div>';
                ?>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-12">
              <button type="submit" class="btn btn-primary btn-lg" id="admApprove" name="admApprove" value="AP"><i class="fa fa-check"></i> Approve</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <!-- <button type="submit" class="btn btn-danger btn-lg" id="admReject" name="admReject" value="A_REJ"> Reject</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
              <button type="button" class="btn btn-warning btn-lg" onclick="returnBack();"><i class="fa fa-times"></i> Cancel</button>
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
  function returnBack(){
    window.location.replace("userList.php");
  }

  function returnfun(){
    if(confirm('Are you sure to submit')){
      return true;
    }else{
      return false;
    }
  }
</script>
</html>
