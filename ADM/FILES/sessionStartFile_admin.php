<?php 
session_start();

define('ROLE_ADMIN', 1);
define('SESSION_TIMEOUT', 1500);

function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        header("Location: ../../Authentication/Logout.php");
        die();
    }

    $_SESSION['last_activity'] = time();
}

session_regenerate_id(true);

$role = isset($_SESSION['sess_userrole']) ? $_SESSION['sess_userrole'] : 0;
$user_name = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : 0;

if ($role != ROLE_ADMIN) {
    header('Location: ../../access.php?err=2');
    die();
}

check_session_timeout();
?>