<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');

    $symbol_id = $_POST['symbol_id'];
    $symbol = $_POST['symbol'];

    $orders = $dbh->prepare("SELECT SUM(order_size) AS orders FROM ipo WHERE symbol_id = ?");
    $orders->bindParam(1, $symbol_id);
    $orders->execute();
    $res = $orders->fetch();

    $shares = $dbh->prepare("SELECT paid_up_shares AS total FROM symbol WHERE status = 1 and symbol_id = ?");
    $shares->bindParam(1, $symbol_id);
    $shares->execute();
    $shr = $shares->fetch();

    $TOTALAVLVOL = $shr['total'];
    $TOTALORDERS = $res['orders'];

    //under subscrpition
    if ($TOTALAVLVOL > $TOTALORDERS) {

        $all = $dbh->prepare("SELECT order_size, order_id 
            FROM ipo 
            WHERE order_size != 0 
                AND status = 0 
                AND symbol_id = ?
            ORDER BY order_size DESC
        ");
        $all->bindParam(1, $symbol_id);
        $all->execute();
        $n = 0;
        foreach ($all as $orders) {
            $update = $dbh->prepare("UPDATE ipo SET allocated_size = :al, status = 1 WHERE order_id = :orid");
            $update->bindParam(':al', $orders['order_size']);
            $update->bindParam(':orid', $orders['order_id']);
            $update->execute();
            $n += $orders['order_size'];
        }
        echo $n;

    } else {

        echo "<br/> total initial vol".$TOTALAVLVOL;
        for($x = $TOTALAVLVOL; $x > 0;)
        {
            $bal = $dbh->prepare("SELECT order_id, order_size, buy_vol 
                FROM ipo 
                WHERE buy_vol > 0 AND symbol_id = ?
                ORDER BY order_date DESC");
            $bal->bindParam(1, $symbol_id);
            $bal->execute();

            $findlowest= findlowest($symbol_id);
            $rowCount = rightsRowCount($symbol_id);
            $allocation = floor($TOTALAVLVOL / $rowCount);
            
            if ($TOTALAVLVOL < $rowCount && $TOTALAVLVOL > 0) {
                $update = timepririoty($TOTALAVLVOL);
                $TOTALAVLVOL = $TOTALAVLVOL - $update;
                echo "vol in time priority-->".$TOTALAVLVOL;
            }
            else {
                if($findlowest > $allocation) {
                    $size = $allocation;
                    $update = timepririoty1($size);
                    $totalvolall = $size * $rowCount;
                    $TOTALAVLVOL = $TOTALAVLVOL - $totalvolall;
                }
                elseif ($findlowest < $allocation) {
                    $findinglowervol = findinglowervol($allocation, $symbol_id);
                    /* $size = $findlowest;
                    $update = timepririoty1($price, $size);
                    $totalvolall = $size * $rowCount; */
                    $TOTALAVLVOL = $TOTALAVLVOL - $findinglowervol; 
                }
                elseif ($findlowest == $allocation) {
                    $size = $findlowest;
                    $update = timepririoty1($size);
                    $totalvolall = $size * $rowCount;
                    $TOTALAVLVOL = $TOTALAVLVOL - $totalvolall; 
                }
                else {
                    foreach($bal as $result) {
                        $orderId = $result['order_id'];
                        $buyVol = $result['buy_vol'];
                        
                        if($allocation <= $buyVol) {
                            $size = $allocation;
                            $update = buyUpdate($orderId, $size);
                            $TOTALAVLVOL = $TOTALAVLVOL - $size;
                        }
                        elseif ($allocation > $buyVol) {
                            $size = $buyVol;
                            $update = buyUpdate($orderId, $size);
                            $TOTALAVLVOL = $TOTALAVLVOL - $size;
                        }
                        else {
                            echo "Error with allocation";
                        }
                    }
                }
            }
            $x = $TOTALAVLVOL;
            echo $x."|".$TOTALAVLVOL;
        }
}
//echo "<br/> vals afterr->".$TOTALAVLVOL.'and '.$x;
function timepririoty($TOTALAVLVOL) { 
    include ('../../CONNECTIONS/db.php');
    $rowCount = $TOTALAVLVOL;
    $bal = $dbh->prepare("UPDATE ipo SET buy_vol = buy_vol - 1, allocated_size = allocated_size + 1 WHERE buy_vol > 0 ORDER BY order_date ASC LIMIT $rowCount");
    
    if($bal->execute()) {
        return $TOTALAVLVOL;
    } else {
        echo "could not save";
    }
}

function findinglowervol($allocation, $symbol_id)
{ 
    include ('../../CONNECTIONS/db.php');
    $bal = $dbh->prepare("SELECT sum(buy_vol) AS kakashi FROM ipo WHERE buy_vol <= :allocation AND buy_vol > 0 AND symbol_id = :sym_id");
    $bal->bindParam(':allocation', $allocation);
    $bal->bindParam(':sym_id', $symbol_id);
    $bal->execute();
    $row = $bal->fetch();
    $totlessthanallocation = $row['kakashi'];

    $bal = $dbh->prepare("SELECT count(order_id) AS jing FROM ipo WHERE buy_vol>:allocation and buy_vol > 0");
    $bal->bindParam(':allocation', $allocation);
    $bal->execute();
    $row = $bal->fetch();
    $count = $row['jing'];

    $totmorethanallocation = $allocation * $count;
    $TOTALAVLVOL = $totlessthanallocation + $totmorethanallocation;
    
    $bal = $dbh->prepare("SELECT DISTINCT buy_vol FROM ipo WHERE buy_vol > 0 AND buy_vol <= :allocation");
    $bal->bindParam(':allocation', $allocation);
    $bal->execute();
    foreach($bal as $row) {
        $buy_vol_new = $row['buy_vol'];
        $update_vol = $dbh->prepare("UPDATE ipo set buy_vol = buy_vol - :buy_vol_new, allocated_size = allocated_size + :buy_vol_new WHERE buy_vol = :buy_vol_new AND buy_vol > 0");
        $update_vol->bindParam(':buy_vol_new', $buy_vol_new);
        $update_vol->execute();
    }
    $update = $dbh->prepare("UPDATE ipo SET buy_vol = buy_vol - :allocation, allocated_size = allocated_size + :allocation WHERE buy_vol > :allocation"); 
    $update->bindParam(':allocation', $allocation);
    $update->execute();
    return $TOTALAVLVOL;
}

function rightsRowCount($symbol_id)
{ 
    include ('../../CONNECTIONS/db.php');
    $bal = $dbh->prepare("SELECT count(order_id) AS cnt FROM ipo WHERE buy_vol > 0 AND symbol_id = ?");
    $bal->bindParam(1, symbol_id);
    $bal->execute(); 
    $val = $bal->fetch();
    $count = $val['cnt'];
    return $count; 
}

function findlowest($symbol_id) { 
    include ('../../CONNECTIONS/db.php');
    $bal = $dbh->prepare("SELECT buy_vol FROM ipo WHERE buy_vol > 0 AND symbol_id = ? ORDER BY buy_vol ASC LIMIT 1");
    $bal->bindParam(1, symbol_id);
    $bal->execute(); 
    $val = $bal->fetch();
    $lowest = $val['buy_vol'];
    return $lowest; 
}

function buyUpdate($orderId, $size)
{ 
    include ('../../CONNECTIONS/db.php');
    $update = $dbh->prepare('UPDATE ipo SET buy_vol = buy_vol - :allocation, allocated_size = allocated_size + :allocation WHERE order_id = :od'); 
    $update->bindParam(':allocation', $size);
    $update->bindParam(':od', $orderId);
    $update->execute();
}

function timepririoty1($size)
{ 
    include ('../../CONNECTIONS/db.php');
    $bal=$dbh->prepare("UPDATE ipo SET buy_vol=buy_vol-:size,allocated_size=allocated_size+:size WHERE buy_vol > 0");
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