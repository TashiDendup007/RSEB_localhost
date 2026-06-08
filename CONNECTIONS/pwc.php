<?php
    include('../CONNECTIONS/db.php');
    include('components.php');

    session_start();
    if(!isset($_SESSION['sess_username']) || empty($_SESSION['sess_username'])){
      header('location: ../access.php?err=2');
    }

    if (isset($_POST['submit'])) {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        $user_name = $_SESSION['sess_username'];

        $stmt = $dbh->prepare("SELECT password, is_bcrypt, role_id, cid, phone, email, status FROM users WHERE username = ?");
        $stmt->execute([$user_name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $message = '';

        if (!$row) {
            $message = generateAlert("No Data found.");
        } elseif ($row['role_id'] != 4 && !validatePassword($newPassword)) {
            $message = generateAlert("Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.");
        } else {
            // Verify current password
            $passwordVerified = $row['is_bcrypt'] 
                ? password_verify($currentPassword, $row['password']) 
                : (md5($currentPassword) === $row['password']);

            if (!$passwordVerified) {
                $message = generateAlert("Your old password does not match.");
            } else {
                // Hash new password and update
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateStmt = $dbh->prepare("UPDATE users SET password = ?, is_bcrypt = 1, log_check = 0 WHERE username = ?");
                $updateStmt->execute([$hashedPassword, $user_name]);

                header('Location: ../access.php?err=3');
                exit();
            }
        }
    }

    // commented on 11-02-2025
    /*if (md5($currentPassword) == $row['password']) {
        $encrypt_newPassword = md5($newPassword);
        $stmt = $dbh->prepare("UPDATE users SET password = ?, log_check = '0' WHERE username = ?");
        $stmt->execute([$encrypt_newPassword, $_SESSION['sess_username']]);
        header('Location: ../access.php?err=3');
        exit();
    } else {
        $message = '
        <div class="alert alert-danger alert-dismissible" >
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h4><i class="icon fa fa-ban"></i> Message!</h4> Your old password does not match.
        </div>';
    }*/

    function generateAlert($text) {
        return '
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> </h4> ' . htmlspecialchars($text) . '
        </div>';
    }

    // function to check password strength
    function validatePassword($password) {
        // Define the pattern for a strong password
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$/';
        
        // Check if the password matches the pattern
        if (preg_match($pattern, $password)) {
            return true;
        } else {
            return false;
        }
    }
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <script type="text/javascript" src="jquery.min.js"></script>
    <style type="text/css">
        .body-class {
            background: radial-gradient(#5D3FD3, #301934);
        }
    </style>
</head>
<body class="body-class">
    <div class="login-logo">
      <a><b style="color:white;">RSEB-CaMS</b></a>
    </div>
    <div class="container">
        <?php if (isset($_SESSION['common_name_cred']) && $_SESSION['common_name_cred'] == 1): ?>
            <div class="row">
                <div class="col-lg-8 text-center">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Your password cannot be the same as your username. Please change it.
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <?php if (isset($_SESSION['password_at_risk']) && $_SESSION['password_at_risk'] == 1): ?>
            <div class="row">
                <div class="col-lg-8 text-center">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="row">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <div class="box">
                    <div class="box-header with-border text-center" style="background-color: #14b186;">
                        <div class="box-title"><strong>Change Your Password</strong></div>
                    </div>
                    <form name="frmChange" method="POST" action="">
                        <div class="box-body">
                            <?php if(isset($message)) { echo $message; } ?>
                            <div class="mb-3">
                                <label>Current Password</label>
                                <input class="form-control" for="focusedInput" type="password" name="currentPassword" required />
                            </div>
                            <div class="mb-3">
                                <label>New Password </label>
                                <input class="form-control" type="password"  for="focusedInput" name="newPassword" id="txtPassword" required />
                            </div>
                            <div class="mb-3">
                                <label>Confirm Password</label>
                                <input class="form-control" type="password"  for="focusedInput" name="confirmPassword" id="txtConfirmPassword" required />
                            </div>
                        </div>
                        <div class="footer text-center">
                            <button class="btn btn-primary" for="focusedInput" type="submit" name="submit" id="btnSubmit" value="Submit" class="btnSubmit"> Change</button>
                            <button type="reset" class="btn btn-warning"> Reset</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-2"></div>
        </div>
    </div>

</body>
<script type="text/javascript">
$( function () {
    $("#btnSubmit").click(function () {
        var password = $("#txtPassword").val();
        var confirmPassword = $("#txtConfirmPassword").val();
        if (password !== confirmPassword) {
            alert("New and Confirm Passwords do not match.");
            return false;
        }
        return true;
    });
});
</script>
</html>
