<?php
   // define database related variables
   $database = 'cms2bk';
   $host = '192.168.10.100';
   $user = 'root';
   $pass = 'MkmCsop@289';
   $port='3306';

   // try to connect to database
   try
   {
      $dbh1 = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   }
   catch(PDOException $e){
      error_log("error ==>> ".$e->getMessage());
      die();
   }
?>
