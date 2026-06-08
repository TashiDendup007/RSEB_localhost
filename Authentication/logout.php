<?php
	// echo "Reached at logout.php";
	session_start();
	session_unset();
	session_regenerate_id(true);
	session_destroy();
	setcookie(session_name(), '', 0, '/');
	session_write_close();
	
	header('Location: ../access.php');
	exit();
?>
