<?php
   // define database related variables
   $database = 'rseb_site_2016';
   $host = 'localhost';
   $user = 'root';
   $pass = 'MkmCsop@289';
   $port='3306';
   // try to connect to database
  $dbh4 = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   if(!$dbh4)
   {
      echo "unable to connect to database";
   }

?>
