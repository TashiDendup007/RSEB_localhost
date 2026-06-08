<?php
include('../CONNECTIONS/db.php');
include('components.php');
session_start();

if(count($_POST) > 0) {
    $sql = "SELECT * from users WHERE username = '" . $_SESSION['sess_username'] . "' " ;
    $save = $dbh->prepare($sql);
    $save->execute();
    $row = $save->fetch();
    if(md5($_POST['currentPassword']) == $row['password']) {	
        $query="UPDATE users set password = '" . md5($_POST['newPassword']) . "',log_check= '0'  WHERE username='" . $_SESSION['sess_username'] . "'";
        $query = $dbh->prepare($query);
        $query->execute();
        header('Location: ../access.php?err=3');
    } 
    else $message = "Your old password  Does not match!";
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="styles.css" />
<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript">
    $(function () {
        $("#btnSubmit").click(function () {
            var password = $("#txtPassword").val();
            var confirmPassword = $("#txtConfirmPassword").val();
            if (password != confirmPassword) {
                alert("New and Confirm Passwords do not match.");
                return false;
            }
            return true;
        });
    });
</script>
</head>
<body>
<form name="frmChange" method="POST" action="" >
    <section class="content">
    <div class="box-body">
    <div class="row">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Message!</h4>
            <?php if(isset($message)) { echo $message; } ?>
        </div>
       <div class="col-xs-4">
           <label>Current Password</label>
           <input class="form-control" for="focusedInput" type="password" name="currentPassword" required />
       </div>
       <div class="col-xs-4">
           <label>New Password </label>
           <input  class="form-control" type="password"  for="focusedInput" name="newPassword" id="txtPassword" required />
       </div>
       <div class="col-xs-4">
           <label>Confirm Password</label>
           <input class="form-control" type="password"  for="focusedInput" name="confirmPassword" id="txtConfirmPassword" required />
       </div>
    </div>
    </div>
        <div class="box-footer" >
            <button class="btn btn-primary" for="focusedInput" type="submit" name="submit" id="btnSubmit" value="Submit" class="btnSubmit">CHANGE</button>
        </div>
    </section>
</form>
</body>
</html>