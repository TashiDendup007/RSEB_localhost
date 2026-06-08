<?php
        die("Not time for full allotment. Please go through the code before allotment.");

        // Enable full error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        // Optional: Set content type header for HTML output
        header('Content-Type: text/html; charset=utf-8');

        date_default_timezone_set("Asia/Thimphu");
        include('CONNECTIONS/db.php');

        echo "| Start: " . date('Y-m-d H:m:s') . "<br>";
        
        try {
            if (!$dbh) {
                throw new Exception("Database connection failed.");
            }

            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();

            // Get total orders
            $stmtOrders = $dbh->prepare("
                SELECT SUM(order_size) AS total_orders 
                FROM bond 
                WHERE status = 0 AND symbol_id = 118
            ");
            $stmtOrders->execute();
            $totalOrders = (int)$stmtOrders->fetchColumn();

            error_log("Total order subscribed: $totalOrders");
            echo "Total order subscribed: $totalOrders<br>";

            // Fetch all eligible orders
            $stmtAll = $dbh->prepare("
                SELECT order_id, order_size, cd_code, user_name, cid_no 
                FROM bond 
                WHERE order_size != 0 AND status = 0 AND symbol_id = 118 
                ORDER BY order_size DESC
            ");
            $stmtAll->execute();
            $ordersList = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

            $totalAllotment = 0;

            // Process each order
            $stmtUpdate = $dbh->prepare("
                UPDATE bond 
                SET allocated_size = ?, status = 1 
                WHERE order_id = ?
            ");

            foreach ($ordersList as $order) {
                $stmtUpdate->execute([
                    $order['order_size'], 
                    $order['order_id']
                ]);

                $msg = sprintf(
                    "CD Code: %s, CID No: %s, Order Size: %s",
                    $order['cd_code'],
                    $order['cid_no'],
                    $order['order_size']
                );

                echo $msg . "<br>";
                error_log($msg);

                $totalAllotment += $order['order_size'];
            }

            error_log("Total Allotment: $totalAllotment");
            echo "Allotment: $totalAllotment<br>";

            $dbh->commit();
        } catch (Exception $e) {
            if ($dbh->inTransaction()) {
                $dbh->rollBack();
            }
            $errorMessage = "Exception: " . $e->getMessage();
            error_log($errorMessage);
            echo $errorMessage;
        }

        echo "| End: " . date('Y-m-d H:m:s');
?>
