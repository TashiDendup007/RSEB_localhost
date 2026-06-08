<?php
  //define database related variables
  $database = 'officesite';
  $host = '192.168.10.5';
  $user = 'root';
  $pass = 'MkmCsop@289123';
  $port='3306';
  // try to connect to database
  try
  {
    $dbh_site = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
  }
  catch(PDOException $e)
  {
    die();
  }
?>
