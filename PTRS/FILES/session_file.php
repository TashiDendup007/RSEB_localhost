<?php
  session_start();
  define('ROLE_PTRS', 6);
  define('SESSION_TIMEOUT', 1500);

  function check_session_timeout() {
    if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > SESSION_TIMEOUT)) {
        header("Location: ../../Authentication/Logout.php");
        exit;
    }

      $_SESSION['last_activity'] = time();
  }

  session_regenerate_id(true);

  $username = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : 0;
  $role = isset($_SESSION['sess_userrole']) ? $_SESSION['sess_userrole'] : 0;
  if ($role != ROLE_PTRS) {
      header('Location: ../../access.php?err=2');
      die();
  }
  check_session_timeout();
?>
