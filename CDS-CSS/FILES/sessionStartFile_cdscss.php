<?php
  session_start();
  // define('ROLE_CDS', 3);
  define('SESSION_TIMEOUT', 1500);

  function check_session_timeout() {
    if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > SESSION_TIMEOUT)) {
        echo "Reached at check_session_timeout";
        header("Location: ../../Authentication/Logout.php");
        // header("Location: localhost/RSEB_NEW/Authentication/Logout.php");
        exit;
    }

      $_SESSION['last_activity'] = time();
  }

  session_regenerate_id(true);

  $username = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : 0;
  $role = isset($_SESSION['sess_userrole']) ? $_SESSION['sess_userrole'] : 0;
  
  if (in_array($role, ['3', '9'], true)) {
      header('Location: ../../access.php?err=2');
      die();
  }
  check_session_timeout();
?>
