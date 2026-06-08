<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="te-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">profile</a></li>
        </ol>
      </section>

      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Profile</h4>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                  <i class="fa fa-times"></i>
                </button>
            </div>
          </div>
          <div class="box-body">
            <div class="row">
              <section class="content">
                <div class="row">
                  <div class="col-md-3">
                    <!-- Profile Image -->
                    <div class="box box-primary">
                      <div class="box-body box-profile">
                        <img class="profile-user-img img-responsive img-circle" src="../../dist/img/avatar5.png" alt="User profile picture">
                        <h3 class="profile-username text-center"><?php echo $qq['name'];?></h3>
                        <p class="text-muted text-center"><?php echo $qq['address'];?></p>
                        <ul class="list-group list-group-unbordered">
                          <li class="list-group-item">
                            <b>Organization</b> <a class="pull-right">RSEB</a>
                          </li>
                          <li class="list-group-item">
                            <b>Phone</b> <a class="pull-right"><?php echo $qq['phone'];?></a>
                          </li>
                          <li class="list-group-item">
                            <b>eMail</b> <a class="pull-right"><?php echo $qq['email'];?></a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-9">
                    <div class="nav-tabs-custom">
                      <ul class="nav nav-tabs">
                        <li class="active"><a href="#Password" data-toggle="tab">Change Password</a></li>
                      </ul>
                      <div class="tab-content">
                        <div class="active tab-pane" id="Password">
                          
                          <form class="form-horizontal" name="frmChange" method="post" action="" onSubmit="return validatePassword();">
                           <div class="form-group"><label for="inputName" class="col-sm-4 control-label">Current Password</label>
                            <div class="col-sm-8"><input class="form-control" for="focusedInput" type="password" name="currentPassword" id="currentPassword" required /></div>
                           </div>
                            <div class="form-group"><label for="inputName" class="col-sm-4 control-label">New Password </label>
                            <div class="col-sm-8">
                              <input  class="form-control" type="password"  for="focusedInput" name="newPassword" id="newPassword" required />
                              <span id="newPwdMsg" style="color: red;"></span>
                            </div>
                           </div>
                            <div class="form-group"><label for="inputName" class="col-sm-4 control-label">Confirm Password</label>
                            <div class="col-sm-8"><input class="form-control" type="password"  for="focusedInput" name="confirmPassword" id="confirmPassword" required /></div>
                           </div>
                           <div class="form-group">
                              <div class="col-sm-offset-4 col-sm-4">
                              <button class="btn btn-primary" for="focusedInput" type="submit" name="password_change" value="password_change" class="btnSubmit"><i class="fa fa-check"></i> Change</button>
                              </div>
                            </div>  
                          </form>
                          
                          <script type="text/javascript">
                            function validatePassword() {
                            var newPwd = $("#newPassword").val();
                            var conPwd = $("#confirmPassword").val();

                            $("#newPwdMsg").html("");

                            if (newPwd !== conPwd) {
                              $("#newPwdMsg").html("New and Confirm Password doesn't match.");
                              return false;
                            } 
                            return true;
                            }

                            $("#newPassword").click(function() {
                              $("#newPwdMsg").html("");
                            });
                            $("#confirmPassword").click(function() {
                              $("#newPwdMsg").html("");
                            });
                          </script>

                          <?php
                            if(count($_POST)>0)
                            {
                              if (isset($_POST['password_change']) && $_POST['password_change'] == 'password_change') {
                                  $cur_password = $_POST['currentPassword'];
                                  $new_password = $_POST['newPassword'];
                                  $con_password = $_POST['confirmPassword'];

                                  /*if (!validatePassword($new_password)) {
                                    echo"<div class='alert alert-warning alert-dismissible'><i class='icon fa fa-warning'></i>Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.</div>";
                                    exit;
                                  } */
                                  // check current password and update new password
                                  $stmt = $dbh->prepare("SELECT password, temp_password, is_bcrypt FROM users WHERE username = ?");
                                  $stmt->execute(array($username));
                                  $row = $stmt->fetch(PDO::FETCH_ASSOC);

                                  $isBcrypt = $row['is_bcrypt'];
                                  $old_pwd = $row['password'];

                                  $passwordVerified = $isBcrypt ? password_verify($cur_password, $old_pwd) : (md5($cur_password) == $old_pwd);

                                  if ($passwordVerified) {
                                      // hash password
                                      $hashedPwd = password_hash($new_password, PASSWORD_BCRYPT);

                                      // Update hashed password
                                      $updateStmt = $dbh->prepare("UPDATE users SET password = ?, is_bcrypt = 1 WHERE username = ?");
                                      $updateStmt->execute(array($hashedPwd, $username));

                                      echo"<div class='alert alert-info alert-dismissible'><i class='icon fa fa-check'></i>Password Changed</div>";
                                      exit;
                                  } else {
                                      echo"<div class='alert alert-danger alert-dismissible'><i class='icon fa fa-ban'></i> Current Password Not Matching</div>";
                                  }
                              }
                            }
                          ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php') ?>
</body>
</html>
