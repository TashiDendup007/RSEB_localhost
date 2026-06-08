<?php
    echo "Check Code before allocation and change symboold_id and bid_price";
    die();
    
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');
    echo "Start =>> " . date('Y-m-d H:i:s');

    $orders = $dbh->prepare("SELECT sum(order_size) AS orders FROM rights_issue WHERE symbol_id = 20 AND type IN ('SA', 'B') AND status = 0 AND bid_price >= 30.1");
    $orders->execute();
    $res = $orders->fetch();

    $totalorders = $res['orders'];
    $totalavlvol = 2500894;
    echo "<br>Total bid orders size = {$totalorders}";

    $log_file = __DIR__ . '/auction_allocation_' . date('Ymd_His') . '.log';
    $log_messages = '';

    //under subscrpition
    if($totalavlvol > $totalorders) {
        $all = $dbh->prepare("SELECT order_size, order_id FROM rights_issue WHERE order_size != 0 AND symbol_id = 20 AND type IN ('SA', 'B') AND status = 0 AND bid_price >= 30.1 ORDER BY order_size DESC");
        $all->execute();
        $n = 0;
        foreach($all as $orders) {
            $update = $dbh->prepare("UPDATE rights_issue set allocated_size = :al, status = 1 WHERE order_id = :orid");
            $update->bindParam(':al',$orders['order_size']);
            $update->bindParam(':orid',$orders['order_id']);
            $update->execute();
            $n += $orders['order_size'];
        }
        echo "Order allocated => {$n}";

        exit();
    }

    echo "<br> total initial available vol {$totalavlvol} <br>";
    $remaining_avl_vol = $totalavlvol;

    while (true) {
        // get bidder count
        $stmt = $dbh->prepare("SELECT count(*) as order_count 
            FROM rights_issue 
            WHERE symbol_id = 20 
                AND type IN ('SA', 'B') 
                AND status = 0 
                AND buy_vol > 0 
                AND bid_price >= 30.1
        ");
        $stmt->execute();
        $total_count_bidder = $stmt->fetchColumn();

        // stop the loop if remaining volume is exhausted or can't allocate fairly
        if ($remaining_avl_vol <= 0 || $total_count_bidder == 0) {
            break;
        }

        // ------------------ Allocation Based on Equal Distribution ------------------
        if ($remaining_avl_vol >= $total_count_bidder) {
            $allocation_size = floor($remaining_avl_vol / $total_count_bidder);

            $stmt = $dbh->prepare("
                SELECT order_id, type, cd_code, symbol_id, order_size, buy_vol, bid_price, allocated_size, user_name, status, order_date 
                FROM rights_issue 
                WHERE symbol_id = 20 AND type IN ('SA', 'B') AND status = 0 AND buy_vol > 0 AND bid_price >= 30.1
            ");
            $stmt->execute();
            $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $update_stmt = $dbh->prepare("UPDATE rights_issue 
                SET allocated_size = allocated_size + :allocated, buy_vol = buy_vol - :allocated, status = :stass 
                WHERE order_id = :id
            ");

            $log_line = "Normal Allocation (allocation_size = $allocation_size)" . PHP_EOL;
            echo $log_line . "<br>";
            $log_messages .= $log_line;

            foreach ($all_orders as $key => $value) {
                if ($remaining_avl_vol <= 0) break;

                $min_vol_toallocate = min($value['buy_vol'], $allocation_size);
                $status = (($min_vol_toallocate + $value['allocated_size']) >= $value['order_size']) ? 1 : 0;

                $update_stmt->execute([
                    ':allocated' => $min_vol_toallocate,
                    ':id' => $value['order_id'],
                    ':stass' => $status
                ]);

                $remaining_avl_vol -= $min_vol_toallocate;

                $log_line = "CD Code => {$value['cd_code']}, allocated => {$min_vol_toallocate}" . PHP_EOL;
                echo $log_line . "<br>";
                $log_messages .= $log_line;
            }

        } else {
            // ------------------ Time Priority Allocation ------------------
            $stmt = $dbh->prepare("
                SELECT order_id, type, cd_code, symbol_id, order_size, buy_vol, bid_price, allocated_size, user_name, status, order_date 
                FROM rights_issue 
                WHERE symbol_id = 20 AND type IN ('SA', 'B') AND status = 0 AND buy_vol > 0 AND bid_price >= 30.1 
                ORDER BY order_date ASC 
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $log_line = "Time Priority Allocation" . PHP_EOL;
            echo $log_line . "<br>";
            $log_messages .= $log_line;

            $update_with_time = $dbh->prepare("UPDATE rights_issue 
                SET allocated_size = allocated_size + 1, buy_vol = buy_vol - 1, status = 1 
                WHERE order_id = ?
            ");

            foreach ($rows as $key => $value) {
                if ($remaining_avl_vol <= 0) break;

                $update_with_time->execute([$value['order_id']]);
                $remaining_avl_vol--;

                $log_line = "CD Code => {$value['cd_code']}, allocated => 1" . PHP_EOL;
                echo $log_line . "<br>";
                $log_messages .= $log_line;
            }
        }

        $log_line = "remaining_avl_vol ==> {$remaining_avl_vol} " . PHP_EOL;
        echo $log_line . "<br>";
        $log_messages .= $log_line;
    }

    file_put_contents($log_file, $log_messages, FILE_APPEND);
    echo "Log saved to: {$log_file} <br>";

    echo "End =>> " . date('Y-m-d H:i:s');

    // finally update the status of unfullfill orders
    $status = $dbh->prepare("UPDATE rights_issue SET status = 1 WHERE status = 0 AND symbol_id = 20 AND type IN ('SA', 'B') AND bid_price >= 30.1");
    $status->execute();
?>