<?php 
	include "CONNECTIONS/db.php";
	// phpinfo(); 
	date_default_timezone_set("Asia/Thimphu");
	// $dateselect = date("Y-m-d H");

	// echo $dateselect;

	// allow to update the price on one session if 0.0041 vol meet
    $dateselect = '2025-09-30 10';
    $start = $dateselect . ':00:00';
    $end   = $dateselect . ':59:59';

    /**
     * 1. Fetch aggregated data (optimized)
     */
    $stmt = $dbh->prepare("
            SELECT w.symbol_id, SUM(w.lot_size_execute) AS total_lot, w.order_exe_price AS price, MAX(w.order_date) AS last_order_date, s.paid_up_shares
            FROM executed_orders w
            JOIN symbol s ON s.symbol_id = w.symbol_id
            WHERE w.order_date BETWEEN :start AND :end AND w.side = 'S'
            GROUP BY w.symbol_id, s.paid_up_shares
    ");
    $stmt->execute([':start' => $start, ':end'   => $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r($rows);

    /**
     * 2. Prepare single UPSERT statement
     */
    $upsert = $dbh->prepare("
        INSERT INTO market_price (symbol_id, market_price, date) VALUES (:symbol_id, :price, :date)
        ON DUPLICATE KEY UPDATE
            ex_market_price = market_price,
            ex_date = date,
            market_price = VALUES(market_price),
            date = VALUES(date)
    ");

    /**
     * 3. Process rows (no extra queries inside loop)
     */
    foreach ($rows as $value) {
        $min_vol_required = floor((0.0041 * $value["paid_up_shares"]) / 100);

        echo"symbol id => " . $value["symbol_id"] . ", paid up share => " . $value["paid_up_shares"] . ", min_vol => " . $min_vol_required . '<br>';

        if ((float)$value["total_lot"] >= $min_vol_required) {

            $upsert->execute([
                ':symbol_id' => $value["symbol_id"],
                ':price'     => $value["price"],
                ':date'      => $value["last_order_date"]
            ]);
        }
    }

?>