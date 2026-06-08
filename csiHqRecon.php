<?php

 $database = 'cms2';
   $host = '192.168.10.100';
   $user = 'root';
   $pass = 'MkmCsop@289';
   $port='3306';
   // try to connect to database
/*  $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
   if(!$dbh)
   {
      echo "unable to connect to database";
   }*/
       try
     {
        $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
    }
    catch(PDOException $e){
          //echo $e->getMessage();
           // echo "<h2> Hi, There seems to be an issue with the Application. Please contact RSEB.</h2>";
            die();
    }


?>
<!DOCTYPE html>  
<html>  
<head>  
  <title>RSEB| RECON</title>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />  
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>  
</head>  
<body>  
 <br /><br />  
 <div class="container">  
  <h3 align="center">RECON CC Centers</h3><br />  
  <div class="table-responsive" id="pagination_data">  
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
            <th scope="col">SI</th>
          <th scope="col">EMP ID</th>
          <th scope="col">SYMBOL</th>
          <th scope="col">PRICE</th>
          <th scope="col">VOL</th>
          <th scope="col">Details</th>
        </tr>
      </thead>
      <tbody>

        <?php
        $query = $dbh->prepare("SELECT rights_issue_online_temp.details,rights_issue_online_temp.name, rights_issue.bid_price,
          rights_issue.order_size,rights_issue.symbol_id
          FROM rights_issue INNER JOIN rights_issue_online_temp ON
          rights_issue_online_temp.cd_code=rights_issue.cd_code AND rights_issue_online_temp.type='CS' 
          AND rights_issue_online_temp.name LIKE '%CC%'
          ");
        $query->execute();
       
        $i=1;
        foreach ($query as  $value) {

          if($value['symbol_id']==5){
              $symbol ='BNBL';
          }else{
          $symbol ='RICB';

          }
          echo '<tr class="text-center">
           <th>'.$i++.'</th>
          <th>'.$value["name"].'</th>
          <th>'. $symbol.'</th>
          <th>'.$value["bid_price"].'</th>
          <th>'.$value["order_size"].'</th>
              <th>'.$value["details"].'</th>
          </tr>';
        }

        ?>


      </tbody>
    </table>

  </div>  
</div>  
</body>  
</html>  
