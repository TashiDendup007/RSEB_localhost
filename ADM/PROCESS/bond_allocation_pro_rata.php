<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');

    $cutoffRate = isset($_POST['rate']) ? $_POST['face_value'] : $_POST['rate'];
    $symbol_id = $_POST['symbol_id'];

    // Total offered shares
    $shares = $dbh->prepare("SELECT paid_up_shares as total FROM symbol WHERE symbol_id = ?");
    $shares->execute([$symbol_id]);
    $total_avl_vol = $shares->fetchColumn();

    echo "Offer Vol => {$total_avl_vol} <br>";
    // Prepare log file
    $log_file = __DIR__ . '/allocation_logs/allocation_log_' . date('Y-m-d_H-i-s') . '.txt';
    file_put_contents($log_file, "=== Allocation Log for Symbol {$symbol_id} at ".date('Y-m-d H:i:s')." ===\n", FILE_APPEND);

    // Fetch all orders at/below cutoff
    $stmt = $dbh->prepare("SELECT order_id, cd_code, order_size, buy_vol 
                           FROM bond 
                           WHERE symbol_id = ? AND bid_price <= ? AND status = 0
                           ORDER BY buy_vol ASC");
    $stmt->execute([$symbol_id, $cutoffRate]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($orders) == 0) {
        echo "No active orders for allotment";
        exit();
    }

    $cnt = count($orders);
    $floor = floor($total_avl_vol / $cnt);

    $remaining_avl_vol = $total_avl_vol;

    // Prepare update statement
    $update = $dbh->prepare("UPDATE bond 
                             SET allocated_size = allocated_size + ?, buy_vol = buy_vol - ? 
                             WHERE order_id = ?");

    // 1️ Floor allocation
    file_put_contents($log_file, "--- Floor Allocation ---\n", FILE_APPEND);
    foreach ($orders as $o) {
        $alloc = ($o['buy_vol'] <= $floor) ? $o['buy_vol'] : $floor;
        $update->execute([$alloc, $alloc, $o['order_id']]);
        $remaining_avl_vol -= $alloc;

        $log_line = "CD {$o['cd_code']} | Order {$o['order_size']} | Allocated (floor) = {$alloc} | Remaining offer = {$remaining_avl_vol}\n";
        file_put_contents($log_file, $log_line, FILE_APPEND);
        echo nl2br($log_line);
    }

    // 2️ Pro-rata allocation for remaining orders
    if ($remaining_avl_vol > 0) {
        file_put_contents($log_file, "--- Pro-Rata Allocation ---\n", FILE_APPEND);

        $stmt = $dbh->prepare("SELECT order_id, cd_code, buy_vol AS pending
                               FROM bond 
                               WHERE symbol_id = ? AND bid_price <= ? 
                               AND buy_vol > 0
                               ORDER BY buy_vol DESC");
        $stmt->execute([$symbol_id, $cutoffRate]);
        $remaining_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_pending = array_sum(array_column($remaining_orders, 'pending'));
        file_put_contents($log_file, "Total pending volume for pro-rata = {$total_pending}\n", FILE_APPEND);

        $pro_rata_allocation = 0;
        foreach ($remaining_orders as $o) {
            $alloc = round(($o['pending'] / $total_pending) * $remaining_avl_vol);
            if ($alloc > 0) {
                $update->execute([$alloc, $alloc, $o['order_id']]);
                $pro_rata_allocation += $alloc;

                $log_line = "CD {$o['cd_code']} | Pending {$o['pending']} | Allocated (pro-rata) = {$alloc}\n";
                file_put_contents($log_file, $log_line, FILE_APPEND);
                echo nl2br($log_line);
            }
        }
        $remaining_avl_vol -= $pro_rata_allocation;
        file_put_contents($log_file, "Remaining offer after pro-rata = {$remaining_avl_vol}\n", FILE_APPEND);

        // 3️ Handle rounding leftovers
        if ($remaining_avl_vol > 0) {
            file_put_contents($log_file, "--- Leftover Allocation ---\n", FILE_APPEND);
            foreach ($remaining_orders as $o) {
                if ($remaining_avl_vol <= 0) break;
                $update->execute([1, 1, $o['order_id']]);
                $remaining_avl_vol--;

                $log_line = "CD {$o['cd_code']} | Allocated leftover +1 | Remaining offer = {$remaining_avl_vol}\n";
                file_put_contents($log_file, $log_line, FILE_APPEND);
                echo nl2br($log_line);
            }
        }
    }

    // update all status 
    $update_status = $dbh->prepare("UPDATE bond SET status = 1 WHERE symbol_id = ?");
    $update_status->execute([$symbol_id]);

    file_put_contents($log_file, "=== Allocation Completed ===\n\n", FILE_APPEND);
    echo "<b>Allocation Completed. Check allocation_log.txt for details.</b>";
?>
