<?php 
	include('../FILES/sessionStartFile_admin.php');
	include ('../../CONNECTIONS/db.php');
	date_default_timezone_set("Asia/Thimphu");

	if (isset($_POST['push_bond_allocated_data']) && isset($_POST['symbol_id'])) {
		$symbol_id = $_POST['symbol_id'];

		try {
		    $dbh->beginTransaction();

		    // Fetch bond orders
		    $stmt = $dbh->prepare("
		        SELECT b.cd_code, b.order_size, b.allocated_size, b.bid_price, b.user_name, b.symbol_id 
		        FROM bond b 
		        WHERE b.symbol_id = ? AND b.allocated_size != 0 -- AND b.status = 0
		    ");
		    $stmt->execute([$symbol_id]);
		    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		    if (!empty($rows)) {
		        $remark = 'DEPOSIT';
		        $insti_id = 1;

		        // Prepare insert statement once
		        $insert = $dbh->prepare("
		            INSERT INTO cds_holding (cd_code, symbol_id, volume, user_name, institution_id, remarks) VALUES (?, ?, ?, ?, ?, ?)
		        ");

		        foreach ($rows as $value) {
		            $insert->execute([
		                $value['cd_code'], 
		                $value['symbol_id'], 
		                $value['allocated_size'], 
		                $value['user_name'], 
		                $insti_id, 
		                $remark
		            ]);
		        }

		        $dbh->commit();
		        echo '<div class="alert alert-success" role="alert">Successfully migrated data to CDS Holding.</div>';
		    } else {
		        echo '<div class="alert alert-warning" role="alert">Allocation has not been done.</div>';
		    }
		} catch (Exception $e) {
		    $dbh->rollBack();
		    error_log("Exception occurred ==> " . $e->getMessage());
		    echo '<div class="alert alert-danger" role="alert">There was an exception. Please try again later.</div>';
		}
		exit;
	}
	else {
		echo '<div class="alert alert-danger" role="alert">No match method.</div>';
		exit;
	}
	
?>
