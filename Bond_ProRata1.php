<?php
die();
$cutoffRate=6.5; // update this rate from the discovered rate
date_default_timezone_set("Asia/Thimphu");
include ('CONNECTIONS/db.php');
echo "start".time().'</br>';
    //total order volume
    $orders = $dbh->prepare('SELECT sum(order_size) as orders from bond where symbol_id=82 and bid_price <=:bp');
    $orders->bindParam(':bp',$cutoffRate);
    $orders->execute();
    $res=$orders->fetch();

    //total size of the offer
    $shares = $dbh->prepare('SELECT paid_up_shares as total from symbol where symbol_id=82');
    $shares->execute();
    $shr=$shares->fetch();

    $TOTALAVLVOL = $shr['total']; // total offered volume
    $TOTALORDERS = $res['orders']; // total order volume
    $check= $TOTALAVLVOL;

    //under subscrpition
    if($TOTALAVLVOL > $TOTALORDERS)
    {
    $all = $dbh->prepare('SELECT order_size,order_id from bond where order_size!=0 and status=0 and symbol_id=82 order by order_size DESC');
        $all->execute();
        $n=0;
        foreach($all as $orders)
        {
            $update = $dbh->prepare('UPDATE bond set allocated_size=:al,status=1 where order_id=:orid ');
            $update->bindParam(':al',$orders['order_size']);
            $update->bindParam(':orid',$orders['order_id']);
            $update->execute();
            $n += $orders['order_size'];
        }
        echo $n;
    }
    //over subscription
    else
    {
        $overall = $dbh->prepare('SELECT * from bond where symbol_id=82 and bid_price <= :bp order by bid_price ASC');
        $overall->bindParam(':bp',$cutoffRate);
        $overall->execute();
        $cnt=$overall->rowCount();
        foreach($overall as $alloc)
        {
            echo $cnt--;
            if($cnt==0){
                $update = $dbh->prepare('UPDATE bond set allocated_size=:al,buy_vol=buy_vol-:al,status=1 where order_id=:orid ');
                $update->bindParam(':al',$TOTALAVLVOL);
                $update->bindParam(':orid',$alloc['order_id']);
                $update->execute();
                echo $alloc['cd_code'].'->'.$alloc['order_size'].'</br>';
                $check = $check-$alloc['order_size'];
                $TOTALAVLVOL = $TOTALAVLVOL-$alloc['order_size'];
                $TOTALORDERS = $TOTALORDERS - $alloc['order_size'];

            }else{
                if($alloc['bid_price'] < $cutoffRate){

                    $update = $dbh->prepare('UPDATE bond set allocated_size=:al,buy_vol=buy_vol-:al,status=1 where order_id=:orid ');
                    $update->bindParam(':al',$alloc['order_size']);
                    $update->bindParam(':orid',$alloc['order_id']);
                    $update->execute();
                    echo $alloc['cd_code'].'->'.$alloc['order_size'].'</br>';
                    $check = $check-$alloc['order_size'];
                    $TOTALAVLVOL = $TOTALAVLVOL-$alloc['order_size'];
                    $TOTALORDERS = $TOTALORDERS - $alloc['order_size'];

                }else{
                    $order_size = $alloc['order_size'];
                    $allocated = ($order_size/$TOTALORDERS)*$TOTALAVLVOL;
                    $allocated = round($allocated);
                    $update = $dbh->prepare('UPDATE bond set allocated_size=:al,buy_vol=buy_vol-:al,status=1 where order_id=:orid ');
                    $update->bindParam(':al',$allocated);
                    $update->bindParam(':orid',$alloc['order_id']);
                    $update->execute();
                    echo $alloc['cd_code'].'->->'.$alloc['order_size'].'</br>';
                    $check = $check-$alloc['order_size'];
                    $TOTALAVLVOL = $TOTALAVLVOL-$allocated;
                    $TOTALORDERS = $TOTALORDERS - $allocated;

                }

            }
        }
       
    }
    $status = $dbh->prepare('UPDATE bond set status=1, price_discovered=:pd where status=0 and symbol_id=82');
    $status->bindParam(':pd',$cutoffRate);
    $status->execute();

    echo 'final-->'.$check;
    if($check > 0){
        $overall = $dbh->prepare('SELECT * from bond where symbol_id=82 order by order_size DESC');
        $overall->execute();
        foreach($overall as $alloc)
        {
            if($check > 0){
                $update = $dbh->prepare('UPDATE bond set allocated_size=allocated_size+1,buy_vol=buy_vol-1,status=1, price_discovered=:pd where order_id=:orid ');
                $update->bindParam(':orid',$alloc['order_id']);
                $update->bindParam(':pd',$cutoffRate);
                $update->execute();
                $check--;
            }
        }
    }
//echo "<br/> vals afterr->".$TOTALAVLVOL.'and '.$x;

    $updateRate = $dbh->prepare('UPDATE symbol set coupon_rates=:rate where symbol_id=82');
    $updateRate->bindParam(':rate',$cutoffRate);
    $updateRate->execute();

echo "|end".time();
?>