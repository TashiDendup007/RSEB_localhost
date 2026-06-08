<?php
   /*// define database related variables
   $database = 'cms2';
   $host = '192.168.10.100';
   $user = 'root';
   $pass = 'MkmCsop@289';
   $port='3306';
   try
   {
      $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   }
   catch(PDOException $e){
      die();
   }*/

   // Get the current server IP
   $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostbyname(gethostname());

   // Define database credentials based on the current host IP
   if ($serverIp === '192.168.20.100') { 
      $database = 'cm2';
      /*$host = '192.168.10.100';
      $user = 'root';
      $pass = 'MkmCsop@289';*/
   } else {
      $database = 'cm2';
      $host = 'localhost';
      $user = 'root';
      $pass = 'root';
   } 

   $port = '3306';

   // Try to connect to the database
   try {
      $dbh = new PDO(
        "mysql:dbname={$database};host={$host};port={$port}",
        $user,
        $pass,
        array(
            PDO::MYSQL_ATTR_LOCAL_INFILE => true // ✅ Enable LOCAL INFILE support
        )
    );
   } catch (PDOException $e) {
      die("Database connection failed: " . $e->getMessage());
   }


?>