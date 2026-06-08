<?php
   // define database related variables
   /*$database = 'cms2';
   $host = 'localhost';
   $user = 'root';
   $pass = 'R00t@*2o2E';
   $port='3306';
   // try to connect to database
  $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   if(!$dbh)
   {
      echo "unable to connect to database";
   }*/
?>

<?php
   // Get the current server IP
   $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostbyname(gethostname());

   // Define database credentials based on the current host IP
   if ($serverIp === '192.168.20.100') {
      $database = 'cms2';
      $host = '192.168.10.100';
      $user = 'root';
      $pass = 'MkmCsop@289';
   } else {
      $database = 'cms2';
      $host = 'localhost';
      $user = 'root';
      $pass = 'root';
   }

   // Try to connect to the database
   try {
      $dbh = new PDO("mysql:dbname={$database};host={$host};port=3306", $user, $pass);
   } catch (PDOException $e) {
      die("Database connection failed: " . $e->getMessage());
   }
?>

