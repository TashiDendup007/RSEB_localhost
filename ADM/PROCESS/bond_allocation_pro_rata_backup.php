<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');

    // $cutoffRate = 0; // update this rate from the discovered rate
    $cutoffRate = $_POST['rate']; 
    $symbol_id = $_POST['symbol_id'];

    // $sysTime = date('H:i:s');
    echo "start => ".time().'<br>';

    //total size of the offer
    $shares = $dbh->prepare("SELECT paid_up_shares as total FROM symbol WHERE symbol_id = ?");
    $shares->bindParam(1, $symbol_id);
    $shares->execute();
    $shr = $shares->fetch();

    if ($cutoffRate < 1) {
        $paid_up_shares = $shr['total'];

        $bid_rates = $dbh->prepare("SELECT DISTINCT bid_price FROM bond WHERE status = 0 AND symbol_id = ? ORDER BY bid_price ASC");
        $bid_rates->bindparam(1, $symbol_id);
        $bid_rates->execute();
        foreach ($bid_rates as $bid_rate) {
          $stmt = $dbh->prepare("SELECT SUM(order_size) AS total FROM bond WHERE status = 0 AND bid_price <= ? AND symbol_id = ?");
          $stmt->bindParam(1, $bid_rate['bid_price']);
          $stmt->bindParam(2, $symbol_id);
          $stmt->execute();
          $res = $stmt->fetch();
          $total_vol_bid = $res['total']; 

          if ($total_vol_bid >= $paid_up_shares) {
            $cutoffRate = $bid_rate['bid_price'];
            break;
          } else {
            $cutoffRate = 0.00;
          }
        }
    }

    //total order volume
    $orders = $dbh->prepare("SELECT sum(order_size) as orders FROM bond WHERE symbol_id = ? and bid_price <= ?");
    $orders->bindParam(1, $symbol_id);
    $orders->bindParam(2, $cutoffRate);
    $orders->execute();
    $res = $orders->fetch();

    $TOTALAVLVOL = $shr['total']; // total offered volume
    $TOTALORDERS = $res['orders']; // total order volume
    $check = $TOTALAVLVOL;

    //UNDER SUBSCRPITION
    if($TOTALAVLVOL > $TOTALORDERS) {
        $all = $dbh->prepare("SELECT order_size, order_id FROM bond WHERE order_size != 0 AND status = 0 AND symbol_id = ? ORDER BY order_size DESC");
        $all->bindParam(1, $symbol_id);
        $all->execute();
        $n = 0;
        foreach($all as $orders) {
            $update = $dbh->prepare("UPDATE bond set allocated_size = ?, status = 1 WHERE order_id = ?");
            $update->bindParam(1, $orders['order_size']);
            $update->bindParam(2, $orders['order_id']);
            $update->execute();
            $n += $orders['order_size'];
        }
        echo $n."<br>";

    } else { //over subscription
        $overall = $dbh->prepare("SELECT * FROM bond WHERE symbol_id = ? AND bid_price <= ? ORDER BY bid_price ASC");
        $overall->bindParam(1, $symbol_id);
        $overall->bindParam(2, $cutoffRate);
        $overall->execute();

        $cnt = $overall->rowCount();

        foreach($overall as $alloc) {
            echo $cnt--."<br>";
            $sql = "UPDATE bond SET allocated_size = ?, buy_vol = buy_vol - ?, status = 1 WHERE order_id = ?";
            if ($cnt == 0) {
                $update = $dbh->prepare($sql);
                $update->bindParam(1, $TOTALAVLVOL);
                $update->bindParam(2, $TOTALAVLVOL);
                $update->bindParam(3, $alloc['order_id']);
                $update->execute();

                echo $alloc['cd_code'].'->'.$alloc['order_size'].'<br>';
                
                $check -= $alloc['order_size'];
                $TOTALAVLVOL -= $alloc['order_size'];
                $TOTALORDERS -= $alloc['order_size'];

            } else {

                if ($alloc['bid_price'] < $cutoffRate) {
                    $update = $dbh->prepare($sql);
                    $update->bindParam(1, $alloc['order_size']);
                    $update->bindParam(2, $alloc['order_size']);
                    $update->bindParam(3, $alloc['order_id']);
                    $update->execute();

                    echo $alloc['cd_code'].'->'.$alloc['order_size'].'<br>';

                    $check -= $alloc['order_size'];
                    $TOTALAVLVOL -= $alloc['order_size'];
                    $TOTALORDERS -= $alloc['order_size'];

                } else {
                    $order_size = $alloc['order_size'];
                    $allocated = round(($order_size / $TOTALORDERS) * $TOTALAVLVOL);
                    
                    $update = $dbh->prepare($sql);
                    $update->bindParam(1, $allocated);
                    $update->bindParam(2, $allocated);
                    $update->bindParam(3, $alloc['order_id']);
                    $update->execute();
                    
                    echo $alloc['cd_code'].'->->'.$alloc['order_size'].'<br>';
                    
                    $check -= $alloc['order_size'];
                    $TOTALAVLVOL -= $allocated;
                    $TOTALORDERS -= $allocated;

                }

            }
        }
       
    }
    
    $status = $dbh->prepare("UPDATE bond SET status = 1, price_discovered = ? WHERE status = 0 AND symbol_id = ?");
    $status->bindParam(1, $cutoffRate);
    $status->bindParam(2, $symbol_id);
    $status->execute();

    echo 'final-->'.$check."<br>";

    if($check > 0){
        $overall = $dbh->prepare("SELECT * FROM bond WHERE symbol_id = ? ORDER BY order_size DESC");
        $overall->bindParam(1, $symbol_id);
        $overall->execute();
        foreach($overall as $alloc) {
            if($check > 0) {
                $update = $dbh->prepare("UPDATE bond SET allocated_size = allocated_size + 1, buy_vol = buy_vol - 1, status = 1, price_discovered = ? WHERE order_id = ?");
                $update->bindParam(1, $cutoffRate);
                $update->bindParam(2, $alloc['order_id']);
                $update->execute();
                $check--;
            }
        }
    }
    //echo "<br/> vals afterr->".$TOTALAVLVOL.'and '.$x;

    $updateRate = $dbh->prepare('UPDATE symbol set coupon_rates = ? WHERE symbol_id = ?');
    $updateRate->bindParam(1, $cutoffRate);
    $updateRate->bindParam(2, $symbol_id);
    $updateRate->execute();

    echo "|end".time();
?>