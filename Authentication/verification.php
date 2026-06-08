<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    date_default_timezone_set("Asia/Thimphu");
    $year = date("Y"); 
    include('../CONNECTIONS/db.php');

    if (!isset($_POST['username']) || empty($_POST['username']) || !isset($_POST['password']) || empty($_POST['password'])) {
        header('Location: ../access.php?err=2');
        exit();
    }

    // Sanitize user input to prevent SQL injection
    $username = htmlspecialchars($_POST['username']);
    $user_input_pwd = htmlspecialchars($_POST['password']);
    
    // Validate username and password
    if (empty($username) || empty($user_input_pwd)) {
        header('Location: ../access.php?err=1');
        exit;
    }

    // Query the database
    $stmt = $dbh->prepare("SELECT name, username, email, password, temp_password, is_bcrypt, participant_code, cid, address, phone, status, role_id, log_check, cd_code, isNRB, created_at, DATEDIFF(CURDATE(), created_at) AS dayCount 
        FROM users 
        WHERE username = :usr_name
    ");
    $stmt->bindParam(':usr_name', $username);
    $stmt->execute();
    // Check if the user exists and the password is correct
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['status'] == 0) {
            header('Location: ../access.php?err=4'); // inactive user
            exit();
        }

        if ($user['role_id'] == 4 && $user['dayCount'] > 365 ) {
            header('Location: ../access.php?err=4'); // expire account
            exit();
        }

        $hashedPwdFromDb = $user['password'];
        $isBcrypt = $user['is_bcrypt'];
        $logCheck = $user['log_check'];
        $roleId = $user['role_id'];

        // Verify the current password
        $passwordVerified = $isBcrypt ? password_verify($user_input_pwd, $hashedPwdFromDb) : (md5($user_input_pwd) == $hashedPwdFromDb);

        if ($passwordVerified) {
            initializeSession($user);

            if (!$isBcrypt) {
                // Hash the password using bcrypt
                $hashedPassword = password_hash($user_input_pwd, PASSWORD_BCRYPT);

                // Update hashed password
                $updateStmt = $dbh->prepare("UPDATE users SET password = :password, is_bcrypt = 1 WHERE username = :username");
                $updateStmt->bindParam(':password', $hashedPassword);
                $updateStmt->bindParam(':username', $username);
                $updateStmt->execute();
            }
            
            // To check if a password is securely set
            if ($logCheck == 1) {
                header('Location: ../CONNECTIONS/pwc.php');
                exit;
            } elseif ($username == $user_input_pwd) {
                header('Location: ../access.php?err=6');
                exit;
            } elseif ($roleId != 4 && !validatePassword($user_input_pwd)) {
                header('Location: ../access.php?err=7');
                exit;
            } /*elseif ($user_input_pwd == "adMin@$year") {
                // strcasecmp($user_input_pwd, "adMin@$year") === 0
                header('Location: ../access.php?err=8');
                exit;
            }*/ else {
                redirectToRolePage($roleId);
            }

        } else {
            header('Location: ../access.php?err=1'); 
            die();
        }

    } else {
        header('Location: ../access.php?err=1'); 
        die();
    }

    // Initilise user session
    function initializeSession($user) {
        session_regenerate_id();
        $_SESSION['sess_user_id'] = $user['cid'];
        $_SESSION['sess_part_code'] = $user['participant_code'];
        $_SESSION['sess_username'] = $user['username'];
        $_SESSION['sess_userrole'] = $user['role_id'];
        $_SESSION['sess_log_check'] = $user['log_check'];
        $_SESSION['isNRB'] = $user['isNRB'];
        $_SESSION['timeout'] = time();
        session_write_close();
    }

    // function to check password strength
    function validatePassword($password) {
        // Define the pattern for a strong password
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_\-]{8,}$/';
        
        // Check if the password matches the pattern
        return preg_match($pattern, $password) === 1;
    }

    // Redirect landing page
    function redirectToRolePage($roleId) {
        switch ($roleId) {
            case 1:
                header('Location: ../ADM/FILES/Admin-dashboard.php');
                break;
            case 2:
                header('Location: ../BBO/FILES/bbo-landing.php');
                break;
            case 3:
                header('Location: ../CDS-CSS/FILES/cds-css-landing.php');
                break;
            case 4:
                header('Location: ../TE/FILES/te-landing.php');
                break;
            case 5:
                header('Location: ../IPO/FILES/ipo-landing.php');
                break;
            case 6:
                header('Location: ../PTRS/FILES/ptrs_landing.php');
                break;
            case 7:
                header('Location: ../CustodialService/FILES/custodial_landing.php');
                break;
            case 8:
                header('Location: ../BBO/FILES/bbo-landing.php');
                break;
            case 9:
                header('Location: ../CDS-CSS/FILES/cds-css-landing.php');
                break;
            case 12:
                header('Location: ../DEALER/FILES/landing.php');
                break;
            default:
                header('Location: ../access.php?err=1');
                break;
        }
        exit;
    }

?>
