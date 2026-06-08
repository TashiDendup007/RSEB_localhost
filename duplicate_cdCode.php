<?php
date_default_timezone_set("Asia/Thimphu");
//header('Content-Type: application/json');


include 'CONNECTIONS/db.php';

$query= $dbh->prepare("SELECT 
        r.user_name,r.cd_code
        FROM rights_issue r 
        WHERE r.cd_code LIKE 'SA%' 
        ORDER BY r.cd_code ASC ");
$query->execute();
$duplivlue = '';
$flag = 0;
$occurrance = 0;

foreach ($query as $row) {
    $query= $dbh->prepare("SELECT 
                            p.cd_code, p.name
                            FROM rights_issue_online_temp p
                            WHERE p.name=:name and p.cd_code=:cd");
    $query->bindParam(':name', $row['user_name']);
    $query->bindParam(':cd', $row['cd_code']);
    $query->execute();
    $data= $query->fetch();

    $cd_code=$row['cd_code'];
    $occurance++;

    $duplivlue = $cd_code;



    if($duplivlue =    )

    echo $data['cd_code'].'--'.$data['name'].'</br>';

}
?>