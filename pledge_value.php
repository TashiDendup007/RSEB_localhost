<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'CONNECTIONS/db.php';

$pledges = $dbh->prepare("SELECT a.symbol_id, a.pledge_volume, a.pledge_date FROM cds_pledge a
                         WHERE a.pledge_date BETWEEN '2022-01-01' AND '2022-12-31'
                         AND a.pledge_volume > 0");
$pledges->execute();



$sum = 0;
$vol = 0;
foreach ($pledges as $pledge) {
    $pl_date = '%'.substr($pledge['pledge_date'],0,10).'%';
    $prices = $dbh->prepare("SELECT a.price, a.date FROM market_price_history a  WHERE  a.symbol_id=:symbol and a.date LIKE :pl_date");
    $prices->bindParam(':pl_date', $pl_date);
    $prices->bindParam(':symbol', $pledge['symbol_id']);
    $prices->execute();
    $priceList = $prices->fetch();

    $sum += ($pledge['pledge_volume'] * $priceList['price']);
    $vol += $pledge['pledge_volume'];

 
}

echo  'Pledge value: '.number_format($sum,2);
echo '<br/>';
echo  'Pledged volume: '.number_format($vol,2);
