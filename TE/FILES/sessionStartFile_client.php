<?php
    session_start();
    define('ROLE_ADMIN', 4);
    define('SESSION_TIMEOUT', 1500);

    function check_session_timeout() {
        if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > SESSION_TIMEOUT)) {
            header("Location: ../../Authentication/Logout.php");
            exit;
        }

        $_SESSION['last_activity'] = time();
    }

    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    // Check user role
    $username = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : '';
    $role = isset($_SESSION['sess_userrole']) ? $_SESSION['sess_userrole'] : 0;

    if (!$username) {
        header('Location: ../../access.php?err=2');
        die();
    }

    if ($role != ROLE_ADMIN) {
        header('Location: ../../access.php?err=2');
        die();
    }

    // Check session timeout
    check_session_timeout();
?>