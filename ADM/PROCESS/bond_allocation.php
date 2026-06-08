<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');
    //echo "start".time();

    $orders = $dbh->prepare('SELECT sum(order_size) as orders FROM bond WHERE status=0');
    $orders->execute();
    $res = $orders->fetch();

    $shares = $dbh->prepare('SELECT paid_up_shares AS total FROM symbol WHERE status=1 and symbol="G030" ');
    $shares->execute();
    $shr = $shares->fetch();

    $TOTALAVLVOL = $shr['total'];
    $TOTALORDERS = $res['orders'];

    //under subscrpition
    if($TOTALAVLVOL > $TOTALORDERS) {
        $all = $dbh->prepare('SELECT order_size,order_id from bond where order_size != 0 and status=0 order by order_size DESC');
        $all->execute();
        $n = 0;
        foreach($all as $orders) {
            $update = $dbh->prepare('UPDATE bond set allocated_size=:al,status=1 where order_id=:orid ');
            $update->bindParam(':al',$orders['order_size']);
            $update->bindParam(':orid',$orders['order_id']);
            $update->execute();
            $n += $orders['order_size'];
        }
        echo $n;
    } else {
        echo "<br/> total initial vol".$TOTALAVLVOL;
        for($x = $TOTALAVLVOL; $x>0;) {
            $bal=$dbh->prepare("SELECT order_id,order_size,buy_vol from bond WHERE buy_vol > 0 order by order_date desc");
            $bal->execute();
            $findlowest= findlowest();
            $rowCount = rightsRowCount();
            $allocation = floor($TOTALAVLVOL / $rowCount);
            if($TOTALAVLVOL < $rowCount && $TOTALAVLVOL > 0) {
                $update = timepririoty($TOTALAVLVOL);
                $TOTALAVLVOL = $TOTALAVLVOL-$update;
                echo "vol in time priority-->".$TOTALAVLVOL;
            } else {
                if($findlowest > $allocation) {
                    $size = $allocation;
                    $update = timepririoty1($size);
                    $totalvolall = $size * $rowCount;
                    $TOTALAVLVOL = $TOTALAVLVOL - $totalvolall;
                }
                elseif ($findlowest < $allocation) {

                    $findinglowervol= findinglowervol($allocation);
                    /* $size = $findlowest;
                    $update=timepririoty1($price,$size);
                    $totalvolall=$size*$rowCount;*/
                    $TOTALAVLVOL=$TOTALAVLVOL-$findinglowervol; 
                }
                elseif ($findlowest == $allocation) {
                    $size = $findlowest;
                    $update=timepririoty1($size);
                    $totalvolall=$size*$rowCount;
                    $TOTALAVLVOL=$TOTALAVLVOL-$totalvolall; 
                }
                else {
                    foreach ($bal as $result) {
                        $orderId = $result['order_id'];
                        $buyVol = $result['buy_vol'];
                        if($allocation <= $buyVol)
                        {
                            $size = $allocation;
                            $update = buyUpdate($orderId,$size);
                            $TOTALAVLVOL = $TOTALAVLVOL-$size;
                        }
                        elseif ($allocation > $buyVol) {
                            $size = $buyVol;
                            $update = buyUpdate($orderId,$size);
                            $TOTALAVLVOL = $TOTALAVLVOL-$size;
                        }
                        else {
                            echo "Error with allocation";
                        }
                    }
                }
            }
            $x=$TOTALAVLVOL;
            echo $x."|".$TOTALAVLVOL;
        }
}
    $status = $dbh->prepare('UPDATE bond set status=1 where status=0 ');
    $status->execute();
//echo "<br/> vals afterr->".$TOTALAVLVOL.'and '.$x;
function timepririoty($TOTALAVLVOL)
{ 
    include ('../../CONNECTIONS/db.php');
    $rowCount=$TOTALAVLVOL;
    $bal=$dbh->prepare("UPDATE bond SET buy_vol=buy_vol-1,allocated_size=allocated_size+1 WHERE buy_vol > 0 order by order_date asc limit $rowCount");
    if($bal->execute())
    {
    return $TOTALAVLVOL;
    }
    else
    {
    echo "could not save";
    }
}

function findinglowervol($allocation)
{ 
    include ('../../CONNECTIONS/db.php');
    $bal=$dbh->prepare("SELECT sum(buy_vol) as kakashi from bond where buy_vol<=:allocation and buy_vol >0");
    $bal->bindParam(':allocation',$allocation);
    $bal->execute();
    $row=$bal->fetch();
    $totlessthanallocation=$row['kakashi'];
    $bal=$dbh->prepare("SELECT count(order_id) jing from bond where buy_vol>:allocation and buy_vol >0");
    $bal->bindParam(':allocation',$allocation);
    $bal->execute();
    $row=$bal->fetch();
    $count=$row['jing'];
    $totmorethanallocation=$allocation*$count;
    $TOTALAVLVOL=$totlessthanallocation+$totmorethanallocation;
    $bal=$dbh->prepare("SELECT distinct buy_vol from bond where buy_vol>0 and buy_vol <=:allocation");
    $bal->bindParam(':allocation',$allocation);
    $bal->execute();
    foreach($bal as $row)
    {
        $buy_vol_new=$row['buy_vol'];
        $update_vol=$dbh->prepare("UPDATE bond set buy_vol=buy_vol-:buy_vol_new,allocated_size=allocated_size+:buy_vol_new where buy_vol=:buy_vol_new and buy_vol >0");
        $update_vol->bindParam(':buy_vol_new',$buy_vol_new);
        $update_vol->execute();
    }
    $update = $dbh->prepare('UPDATE bond set buy_vol=buy_vol-:allocation,allocated_size=allocated_size+:allocation where buy_vol > :allocation'); 
    $update->bindParam(':allocation',$allocation);
    $update->execute();
    return $TOTALAVLVOL;
}

function rightsRowCount()
{ 
    include ('../../CONNECTIONS/db.php');
    $bal=$dbh->prepare("SELECT count(order_id) as cnt from bond WHERE buy_vol > 0");
    $bal->execute(); 
    $val=$bal->fetch();
    $count=$val['cnt'];
    return $count; 
}

function findlowest()
{ 
    include ('../../CONNECTIONS/db.php');
    $bal=$dbh->prepare("SELECT buy_vol from bond where buy_vol > 0 order by buy_vol asc limit 1");
    $bal->execute(); 
    $val=$bal->fetch();
    $lowest=$val['buy_vol'];
    return $lowest; 
}

function buyUpdate($orderId,$size)
{ 
    include ('../../CONNECTIONS/db.php');
    $update = $dbh->prepare('UPDATE bond set buy_vol=buy_vol-:allocation,allocated_size=allocated_size+:allocation where order_id=:od'); 
    $update->bindParam(':allocation',$size);
    $update->bindParam(':od',$orderId);
    $update->execute();
}

function timepririoty1($size)
{ 
    include ('../../CONNECTIONS/db.php');
    $bal=$dbh->prepare("UPDATE bond SET buy_vol=buy_vol-:size,allocated_size=allocated_size+:size WHERE buy_vol > 0");
    $bal->bindParam(':size',$size);
    if($bal->execute()){
    echo "ok";
    }
    else{
    echo "could not save";
    }
}
echo "|end".time();
?>