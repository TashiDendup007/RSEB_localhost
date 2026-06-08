<?php 
    date_default_timezone_set("Asia/Thimphu");
    include('../FILES/session_file.php');
    include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
<!-- Site wrapper -->
<div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="ptrs_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
              <i class="fa fa-times"></i></button>
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
                            <b>Email</b> <a class="pull-right"><?php echo $qq['email'];?></a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-9">
                    <div class="nav-tabs-custom">
                      <ul class="nav nav-tabs">
                        <li class="active"><a href="#Password" data-toggle="tab">Change Password</a></li>
                        <li><a href="#settings" data-toggle="tab">Settings</a></li>
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
                        </div>

                        <div class="tab-pane" id="settings">
                          <form class="form-horizontal" action="" method="POST">
                            <div class="form-group">
                              <label for="f_name" class="col-sm-2 control-label">First Name <font color="red">*</font></label>
                              <div class="col-sm-10">
                                <input type="text" class="form-control" id="f_name" name="f_name" placeholder="First Name" required>
                              </div>
                            </div>

                            <div class="form-group">
                              <label for="l_name" class="col-sm-2 control-label">Last Name</label>
                              <div class="col-sm-10">
                                <input type="text" class="form-control" id="l_name" name="l_name" placeholder="Last Name">
                              </div>
                            </div>

                            <!-- <div class="form-group">
                              <label for="cid_no" class="col-sm-2 control-label">CID <font color="red">*</font></label>
                              <div class="col-sm-10">
                                <input type="text" class="form-control" id="cid_no" name="cid_no" placeholder="CID" required>
                              </div>
                            </div> -->

                            <div class="form-group">
                              <label for="email" class="col-sm-2 control-label">Email <font color="red">*</font></label>
                              <div class="col-sm-10">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                              </div>
                            </div>

                            <div class="form-group">
                              <label for="phone" class="col-sm-2 control-label">Phone <font color="red">*</font></label>
                              <div class="col-sm-10">
                                <input type="number" class="form-control" id="phone" name="phone" placeholder="Phone" required>
                              </div>
                            </div>

                            <div class="form-group">
                              <label for="address" class="col-sm-2 control-label">Address <font color="red">*</font></label>
                              <div class="col-sm-10">
                                <textarea class="form-control" id="address" name="address" placeholder="Address" required></textarea>
                              </div>
                            </div>

                            <div class="form-group">
                              <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-primary" value="update_info" name="update_info"><i class="fa fa-check"></i> Update</button>
                              </div>
                            </div>
                           </form>
                        </div>

                        <?php
                        if(count($_POST) > 0) {
                            if (isset($_POST['password_change']) && $_POST['password_change'] == 'password_change') {
                                $cur_password = $_POST['currentPassword'];
                                $new_password = $_POST['newPassword'];
                                $con_password = $_POST['confirmPassword'];

                                if (!validatePassword($new_password)) {
                                  echo"<div class='alert alert-warning alert-dismissible'><i class='icon fa fa-warning'></i>Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.</div>";
                                  exit;
                                } 
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
                            elseif (isset($_POST['update_info']) && $_POST['update_info'] == 'update_info') {
                                $f_name = $_POST['f_name'];
                                $l_name = $_POST['l_name'];
                                // $cid_no = $_POST['cid_no'];
                                $email = $_POST['email'];
                                $phone = $_POST['phone'];
                                $address = $_POST['address'];
                                $full_name = $f_name . ' ' . $l_name;

                                if (!empty($f_name) && !empty($email) && !empty($phone) && !empty($address)) {
                                    $stmt = $dbh->prepare("UPDATE users u 
                                        SET u.name = ?, -- u.cid = ?, 
                                        u.email = ?, u.phone = ?, u.address = ? 
                                        WHERE u.username = ?
                                    ");
                                    $result = $stmt->execute(array($full_name, $email, $phone, $address, $username));
                                    if ($result) {
                                        echo"<div class='alert alert-success alert-dismissible'><i class='icon fa fa-check'></i> Updated Successfully.</div>";
                                    } else {
                                        echo"<div class='alert alert-warning alert-dismissible'><i class='icon fa fa-ban'></i> Error. Please contact RSEB for Support.</div>";
                                    }
                                    exit;
                                } else {
                                    echo"<div class='alert alert-warning alert-dismissible'><i class='icon fa fa-warning'></i> Required All Fields</div>";
                                    exit;
                                }
                            }
                        }
                        ?>

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
</div>
<?php include('../NAV/footer.php') ?>  
</body>
</html>
