<?php
header('Content-Type: application/json');

include 'CONNECTIONS/db.php';

$query= $dbh->prepare("SELECT SUBSTR(order_date,1,10) as dates, sum(order_size)  
                       as val FROM rights_issue GROUP BY SUBSTR(order_date,1,10) 
                            HAVING COUNT(SUBSTR(order_date,1,10))>2");
$query->execute();

$data = array();

foreach ($query as $row) {
    $data[] = $row;
}

echo json_encode($data);
?>