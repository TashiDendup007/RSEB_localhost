<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');

    // $cutoffRate = 0; // update this rate from the discovered rate
    $cutoffRate = isset($_POST['rate']) ? $_POST['face_value'] : $_POST['rate'];
    $symbol_id = $_POST['symbol_id'];

    error_log("start => ".time());

    //total size of the offer
    $shares = $dbh->prepare("SELECT paid_up_shares as total FROM symbol WHERE symbol_id = ?");
    $shares->bindParam(1, $symbol_id);
    $shares->execute();
    $shr = $shares->fetch();

    if ($cutoffRate < 1) {
        $paid_up_shares = $shr['total'];

        $bid_stmt = $dbh->prepare("SELECT DISTINCT bid_price FROM bond WHERE status = 0 AND symbol_id = ? ORDER BY bid_price ASC");
        $bid_stmt->bindparam(1, $symbol_id);
        $bid_stmt->execute();
        $bid_rates = $bid_stmt->fetchAll(PDO::FETCH_ASSOC);

        // query to select order
        $stmt = $dbh->prepare("SELECT SUM(order_size) AS total FROM bond WHERE status = 0 AND bid_price <= ? AND symbol_id = ?");

        foreach ($bid_rates as $bid_rate) {

          $stmt->execute([$bid_rate['bid_price'], $symbol_id]);
          $total_vol_bid = $stmt->fetchColumn();

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

    error_log("TOTALAVLVOL => ".$TOTALAVLVOL);
    error_log("TOTALORDERS => ".$TOTALORDERS);

    //UNDER SUBSCRPITION
    if($TOTALAVLVOL > $TOTALORDERS) {
            $all = $dbh->prepare("SELECT order_id, order_size FROM bond WHERE order_size != 0 AND status = 0 AND symbol_id = ? ORDER BY order_size DESC");
            $all->execute([$symbol_id]);
            $all_orders = $all->fetchAll(PDO::FETCH_ASSOC);
            $n = 0;

            $update = $dbh->prepare("UPDATE bond set allocated_size = ?, status = 1 WHERE order_id = ?");
            foreach($all_orders as $orders) {
                $update->execute([$orders['order_size'], $orders['order_id']]);
                $n += $orders['order_size'];
            }
            error_log("n => ".$n);

    } else {
            //over subscription
            error_log("Over Subscribed");
            $overall = $dbh->prepare("SELECT order_id, order_size, bid_price, cd_code FROM bond WHERE symbol_id = ? AND bid_price <= ? ORDER BY bid_price ASC");
            $overall->execute([$symbol_id, $cutoffRate]);
            $overall_orders = $overall->fetchAll(PDO::FETCH_ASSOC);

            $cnt = count($overall_orders);
            error_log('row cnt => '.$cnt);

            foreach($overall as $alloc) {
                error_log("loop count => ".$cnt--);

                $order_id = $alloc['order_id'];
                $order_size = $alloc['order_size'];
                $bid_price = $alloc['bid_price'];
                $cd_code = $alloc['cd_code'];

                $update_sql = "UPDATE bond SET allocated_size = ?, buy_vol = buy_vol - ?, status = 1 WHERE order_id = ?";

                if ($cnt == 0) {
                    error_log("row count == 0");
                    $allocated = $TOTALAVLVOL;
                } else {
                    error_log("row count != 0");
                    // Case 1: bid price is below cutoff — full allocation
                    if ($bid_price < $cutoffRate) {
                        error_log("bid price less than rate");
                        $allocated = $order_size;
                    }
                    // Case 2: pro-rata allocation for bids at cutoff
                    else {
                        error_log("bid price not less than rate");
                        // new logic to allocate atleast multiple of 10
                        $proportion = ($order_size / $TOTALORDERS);
                        $raw_alloc = $proportion * $TOTALAVLVOL;

                        // Round down to nearest 10
                        $allocated = floor($raw_alloc / 10) * 10;

                        // Enforce minimum allocation of 10 if anything is left and allocation > 0
                        if ($allocated < 10 && $TOTALAVLVOL >= 10) {
                            $allocated = 10;
                        }

                        // Prevent over-allocation
                        if ($allocated > $TOTALAVLVOL) {
                            $allocated = floor($TOTALAVLVOL / 10) * 10;
                        }

                        // Final fallback check to prevent allocating less than 10 if TOTALAVLVOL < 10
                        if ($allocated < 10 || $TOTALAVLVOL < 10) {
                            $allocated = 0;
                        }
                    }
                }

                // Skip update if nothing to allocate
                if ($allocated > 0) {
                    $update = $dbh->prepare($update_sql);
                    $update->execute([$allocated, $allocated, $order_id]);
                }

                // Logging
                error_log("$cd_code -> Requested: $order_size, Allocated: $allocated");

                // Update totals
                $check -= $order_size;
                $TOTALAVLVOL -= $allocated;
                $TOTALORDERS -= $order_size;

                error_log("Remaining vol: $TOTALAVLVOL | Remaining orders: $TOTALORDERS | Check: $check");
                
            }
       
    }// end of else
    
    // update status
    $status = $dbh->prepare("UPDATE bond SET status = 1, price_discovered = ? WHERE status = 0 AND symbol_id = ?");
    $status->bindParam(1, $cutoffRate);
    $status->bindParam(2, $symbol_id);
    $status->execute();

    error_log('final-->'.$check);

    if($check > 0) {
        error_log('Reached inside check > 0');
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

    $updateRate = $dbh->prepare("UPDATE symbol SET coupon_rates = ? WHERE symbol_id = ?");
    $updateRate->bindParam(1, $cutoffRate);
    $updateRate->bindParam(2, $symbol_id);
    $updateRate->execute();

    error_log("|end => ".time());
    echo("<div class='alert alert-success'>Bond Allocated </div>");
?>