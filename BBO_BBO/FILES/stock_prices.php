<?php
  session_start();
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
  $data = array();

  $query= $dbh->prepare("SELECT DISTINCT m.price, m.date as tradedate
              FROM market_price_history m, symbol s
              WHERE s.symbol_id=m.symbol_id
              AND s.symbol =:symbol
              ORDER by m.date");
  $query->bindParam(':symbol',$_REQUEST['symbol']);
  if($query->execute()){
    $msg = '[';
   if($query->rowCount() > 0){
    foreach($query as $rows)
    {
      $msg .= '['.strtotime($rows['tradedate']).'000,'.number_format($rows['price'],2,'.','').'],';
      //  print_r($rows);
      // $data = $rows;
    }

    }
    else{
      $data[] = 'No Data';
      $response['error'] = false;
      $response['message'] = 'No Symbols.';
      $response['data'] = '';
      echo json_encode($data,JSON_NUMERIC_CHECK);
    }
    $msg .= ']';
    $msg = rtrim($msg,',]');
    $msg .= ']]';
    echo $msg;
    die();
  }
  else{
      $data[] = 'Unsuccessful';
      $response['error'] = true;
      $response['message'] = 'Unsuccessful';
      $response['data'] = '';
      echo json_encode($data, JSON_NUMERIC_CHECK);
  }
?>
