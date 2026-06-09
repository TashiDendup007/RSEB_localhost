<?php 
    error_reporting(1);
    // define database related variables
    $database = 'cms2';
    $host = '192.168.10.100';
    $user = 'root';
    $pass = 'MkmCsop@289';
    $port='3306';
    // try to connect to database
    try
    {
      $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
    }
    catch(PDOException $e){
      echo $e->getMessage();
      echo "<h2> Hi, There seems to be an issue with the Application. Please contact RSEB.</h2>";
      die();
    }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'self';">
  
  <title>RSEB | CapitalMarketSolution</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
</head> 
<body class="hold-transition login-page" style="background-color:white;">
<div class="wrapper" style="margin-top: -10px;">
<div class="d-flex p-2">
  <div class="login-logo">
    <b style="color:black; font-size: 12px;"></b>
  </div>
  <div class="">
    <table  class="table table-sm table-dark table-bordered">
      <thead >
        <tr style='font-size:8px;'>
          <th scope="col">Sym</th>
          <th scope="col"> Vol</th>
          <th scope="col">Last Trade </th>
          <th scope="col">Closing Price</th>
          <th scope="col">Market Price</th>
          <th scope="col">Change</th>
          <th scope="col">Value</th>
        </tr>
      </thead>                  
      <tbody>
      <?php
      $dateselect=date("Y-m-d");
      $data=$dbh->prepare("
          SELECT s.symbol_id,s.symbol,SUBSTRING(mp.date,1,10) as date,mp.market_price,mp.market_price-mp.ex_market_price as diff 
          FROM market_price mp
          LEFT JOIN symbol s ON mp.symbol_id=s.symbol_id
          WHERE s.security_type = 'OS' 
          AND s.status = 1 AND s.trsstatus = 1 
          ORDER BY symbol ASC
      ");
        $data->execute();
          foreach ($data as $value){
            if($value['diff'] > 0)
            {
              $tr = 'bg-success';
              $icon = '<svg class="bi bi-caret-up-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 00.753-1.659l-4.796-5.48a1 1 0 00-1.506 0z"/>
              </svg>';
            }
            elseif($value['diff'] == 0)
            {
              $tr  = 'bg-secondary';
              $icon = '';
            }
            elseif($value['diff'] < 0)
            {
              $tr = 'bg-danger';
              $icon = '
                  <svg class="bi bi-caret-down-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 01.753 1.659l-4.796 5.48a1 1 0 01-1.506 0z"/>
                  </svg>';
            }
            else
            {

            }

            $date = latestTrade($value['symbol_id']);
            if($date == date("Y-m-d")){
              $bg_r = 'white';
              $f_c = 'black';
            }
            else{
              $bg_r = '';

            }

            echo "
            <tr class=".$tr." style='font-size:8px;'>
                <td>".$value['symbol']."</td>
                <td>".number_format($vol=volumeTraded($value['symbol_id'],$date))."</td>
                <td style='background-color:".$bg_r.";color:".$f_c.";''>".$date ."</td>
                <td>".$value['market_price']."</td>
                <td>".$value['market_price']."</td>
                <td>".$icon.$value['diff']."</td>
                <td>".number_format($vol = valueTraded($value['symbol_id'], $date))."</td>
            </tr>";
          }
      ?>  
      </tbody>
    </table>
  </div>
</div>
</div>
</body>
</html> 
<?php
function volumeTraded($sym_id,$date)
{
  global $dbh; 
  $getvolumetraded=$dbh->prepare('SELECT sum(w.lot_size_execute) as sum from executed_orders w 
  where  w.order_date
  like "%'.$date.'%" and w.side="S" and symbol_id=:sym_id');
  $getvolumetraded->bindParam(':sym_id', $sym_id);
  $getvolumetraded->execute();
  $sum=$getvolumetraded->fetch();
  if($sum > 0)
    {
    return $sum['sum'];
    }
    else
    {
    $data='No Trade'; 
    return $data;
    }
 }

 function valueTraded($sym_id,$date)
 {
    global $dbh;
    $getvolumetraded = $dbh->prepare('SELECT sum(w.lot_size_execute) as sumofvol, avg(w.order_exe_price) as sumofprice from executed_orders w 
    WHERE order_date and w.order_date
    like "%'.$date.'%" and w.side="S" and symbol_id=:sym_id');
    $getvolumetraded->bindParam(':sym_id', $sym_id);
    $getvolumetraded->execute();
    $sum=$getvolumetraded->fetch();
    $vol=$sum['sumofvol'];
    $price=$sum['sumofprice'];
    $totalvalue=$vol*$price;

    return $totalvalue;
 }

 function latestTrade($sym_id)
 {
    global $dbh;
    $specifieddate = $dbh->prepare("SELECT SUBSTRING(max(e.order_date),1,10) dat FROM executed_orders e WHERE e.side = 'S' AND e.symbol_id=:sym_id");
    $specifieddate->bindParam(':sym_id', $sym_id);
    $specifieddate->execute();
    $spdate = $specifieddate->fetch();
    $conditiondate = $spdate['dat'];

    return $conditiondate;
 
}
?> 
