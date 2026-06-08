<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="7")
  {
    header('Location: ../../access.php?err=2');
  }
  $inactive = 1500;
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
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
                      <li><a href="#settings" data-toggle="tab">Settings</a></li>
                    </ul>
                    <div class="tab-content">
                      <div class="active tab-pane" id="Password">
                        <form  class="form-horizontal" name="frmChange" method="post" action="" onSubmit="return validatePassword()">
                         <div class="form-group"><label for="inputName" class="col-sm-4 control-label">Current Password</label>
                          <div class="col-sm-8"><input class="form-control" for="focusedInput" type="password" name="currentPassword" required /></div>
                         </div>
                          <div class="form-group"><label for="inputName" class="col-sm-4 control-label">New Password </label>
                          <div class="col-sm-8"><input  class="form-control" type="password"  for="focusedInput" name="newPassword" required /></div>
                         </div>
                          <div class="form-group"><label for="inputName" class="col-sm-4 control-label">Confirm Password</label>
                          <div class="col-sm-8"><input class="form-control" type="password"  for="focusedInput" name="confirmPassword" required /></div>
                         </div>
                         <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-4">
                            <button class="btn btn-primary" for="focusedInput" type="submit" name="submit" value="Submit" class="btnSubmit">CHANGE</button>
                            </div>
                          </div>  
                        </form>
                        <?php
                          if(count($_POST)>0)
                          {
                            $sql= "SELECT * FROM users WHERE username='".$_SESSION['sess_username']."' " ;
                            $save = $dbh->prepare($sql);
                            $save->execute();
                            $row = $save->fetch();
                            if(md5($_POST['currentPassword']) == $row['password'])
                            { 
                              $query="UPDATE users SET password='".md5($_POST['newPassword'])."' WHERE username='".$_SESSION['sess_username']."'";
                              $query = $dbh->prepare($query);
                              if($query->execute())
                              {
                                echo "<div class='alert alert-info alert-dismissible'><i class='icon fa fa-check'></i>Password Changed</div>";
                              }
                            } 
                            else{
                              echo "<div class='alert alert-danger alert-dismissible'><i class='icon fa fa-ban'></i> Currnent Password Not Matching</div>";
                            }
                          }
                        ?>
                      </div>
                      <div class="tab-pane" id="settings">
                        <form class="form-horizontal">
                          <div class="form-group">
                            <label for="inputName" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-10">
                              <input type="email" class="form-control" id="name" placeholder="Name">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="inputEmail" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-10">
                              <input type="email" class="form-control" id="email" placeholder="Email">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="inputName" class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="phone" placeholder="Name">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="inputExperience" class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-10">
                              <textarea class="form-control" id="address" placeholder="Experience"></textarea>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                              <button type="submit" class="btn btn-danger">Update</button>
                            </div>
                          </div>
                         </form> 
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
