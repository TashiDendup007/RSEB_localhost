<?php
	die("Please check Code before migrate. change symbol id");

	include ('../../CONNECTIONS/db.php');
	$symbol_id = 20;
	$from_date = '2025-05-29';
	$to_date = '2025-06-28';

	try {
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $dbh->beginTransaction();

	    $n = 1;
	    $vol_migrated = 0;
	    
	    $log_file = __DIR__ . '/migration_log_' . date('Ymd_His') . '.log';
		$log_messages = '';

	    // Fetch all subscriptions and renouncements within date range
	    $orders_stmt = $dbh->prepare("
	        SELECT 
	            r.order_id, r.type, r.cd_code, r.renounce_cd_code, r.order_size, r.allocated_size, 
	            r.user_name, r.cid_no, a.institution_id 
	        FROM rights_issue r
	        LEFT JOIN adm_participants a ON SUBSTRING(r.user_name, 1, 7) = a.participant_code 
	        WHERE r.symbol_id = ? 
	        AND r.status = 0 
	        AND r.type IN ('S', 'R') 
	        AND DATE(r.order_date) BETWEEN ? AND ?
	    ");
	    $orders_stmt->execute([$symbol_id, $from_date, $to_date]);
	    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

	    // Prepare statements to reuse
	    $cds_lookup_stmt = $dbh->prepare("
	        SELECT cd_code FROM cds_holding WHERE cd_code = ? AND symbol_id = ?
	    ");
	    $cds_update_stmt = $dbh->prepare("
	        UPDATE cds_holding SET temporary_volume = COALESCE(temporary_volume, 0) + ? WHERE cd_code = ? AND symbol_id = ?
	    ");
	    $cds_insert_stmt = $dbh->prepare("
	        INSERT INTO cds_holding (cd_code, temporary_volume, user_name, institution_id, symbol_id, remarks) VALUES (?, ?, ?, ?, ?, ?)
	    ");
	    $status_update_stmt = $dbh->prepare("
	        UPDATE rights_issue SET status = 1 WHERE symbol_id = ? AND order_id = ?
	    ");

	    foreach ($orders as $order) {
	        $cd_code = ($order['type'] === 'S') ? $order['cd_code'] : $order['renounce_cd_code'];
	        $allocated_vol = $order['allocated_size'];

	        // Skip invalid cd_code
	        if (empty($cd_code) || $allocated_vol <= 0) continue;

	        $username = $order['user_name'];
	        $institution_id = $order['institution_id'];
	        $remarks = "Record via Rights Issue: {$allocated_vol} shares";

	        // Check if record exists in cds_holding
	        $cds_lookup_stmt->execute([$cd_code, $symbol_id]);
	        $existing = $cds_lookup_stmt->fetch(PDO::FETCH_ASSOC);

	        if ($existing) {
	            $cds_update_stmt->execute([$allocated_vol, $cd_code, $symbol_id]);
	        } else {
	            $cds_insert_stmt->execute([$cd_code, $allocated_vol, $username, $institution_id, $symbol_id, $remarks]);
	        }

	        // Update migration status
	        $status_update_stmt->execute([$symbol_id, $order['order_id']]);

	        $log_line = "{$n} : migrated {$allocated_vol} shares to {$cd_code}" . PHP_EOL;
			echo $log_line . "<br>"; // Optional: for browser output
			$log_messages .= $log_line;

	        $n++;
	        $vol_migrated += $allocated_vol;
	    }

	    $dbh->commit();
	    
	    file_put_contents($log_file, $log_messages, FILE_APPEND);
		echo "Log saved to: {$log_file} <br>";

	    echo "Total rights subscribed/renounced migrated => {$vol_migrated}";
	} catch (Exception $e) {
	    $dbh->rollBack();
	    echo "Exception occurred => " . $e->getMessage() . ", line => " . $e->getLine();
	}
	exit;
?>