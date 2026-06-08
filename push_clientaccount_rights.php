<?php 
	die("Not time for CD Code Migration");
	// Enable full error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // Optional: Set content type header for HTML output
    header('Content-Type: text/html; charset=utf-8');

    date_default_timezone_set("Asia/Thimphu");
	include('CONNECTIONS/db.php');

	$log_file = __DIR__ . '/ADM/PROCESS/account_migration_log_' . date('Ymd_His') . '.log';
	$log_messages = '';
	
	try {
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $dbh->beginTransaction();

	    // Select distinct order numbers to process
	    $select = $dbh->prepare("
	        	WITH rights_table AS (
				    SELECT s.cd_code, s.cid_no
				    FROM rights_issue s 
				    WHERE s.symbol_id = 20 
				        AND s.type = 'SA' 
				        AND s.bid_price >= 30.1
				)
				SELECT 
				    t.cd_code, t.cid_no, p.phone, p.email,
				    TRIM(SUBSTRING_INDEX(p.details, '|', 1)) AS fl_name, 
				    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.details, '|', 2), '|', -1)) AS bank_id,
				    TRIM(SUBSTRING_INDEX(p.details, '|', -1)) AS account_no
				FROM rights_table t 
				LEFT JOIN rights_issue_online_temp p ON t.cid_no = p.name AND t.cd_code = p.cd_code 
				WHERE 
				    p.symbol_id = 20 
				    AND p.bfs_orderid LIKE 'SA%' 
				    AND p.bfs_code = '00' 
				    AND p.dateentry >= '2025-07-02 09:00:00'
				    AND p.phone != '' AND p.email != ''
				GROUP BY t.cd_code, t.cid_no
	    ");
	    $select->execute();
	    $results = $select->fetchAll(PDO::FETCH_ASSOC);

	    $n = 0;
	    $m = 0;

	    foreach ($results as $res) {
	        // Extract details
	        $cdCode      = $res['cd_code'];
			$cid         = $res['cid_no'];
			$phone       = $res['phone'];
			$email       = $res['email'];
			$bank_id     = $res['bank_id'];
			$bank_acc_no = $res['account_no'];
			$full_name   = $res['fl_name'];

	        // Check if client already exists
	        $checkSql = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE cd_code = ?");
	        $checkSql->execute([$cdCode]);
	        $exists = $checkSql->fetchColumn() > 0;

	        if ($exists) {

	        	// check if cd code is for another person
	        	$stmt = $dbh->prepare("SELECT ID FROM client_account WHERE cd_code = ?");
	        	$stmt->execute([$cdCode]);
	        	$check_cid = $stmt->fetchColumn();
	        	if ($cid == $check_cid) {

	        		// Update existing account
		            $updateAccount = $dbh->prepare("
		                UPDATE client_account 
		                SET phone = ?, email = ?, bank_id = ?, bank_account = ?
		                WHERE cd_code = ?
		            ");
		            $updateAccount->execute([
		                $phone, $email, $bank_id, $bank_acc_no, $cdCode
		            ]);
	        	} else {
	        		$log_line = "Could no Update. Duplicate CD Code {$cdCode}" . PHP_EOL;
					echo $log_line . "<br>";
					$log_messages .= $log_line;
	        	}
	            
	            $n++;

	        } else {

	            // Insert new account
	            $insertSql = $dbh->prepare("
	                INSERT INTO client_account (
	                    acc_type, cd_code, f_name, nationality, ID, phone, user_name, email, bank_id, bank_account, address, institution_id, occupation, bank_account_type
	                ) VALUES (
	                    'I', ?, ?, 'Bhutanese', ?, ?, ?, 'EMPRSEB009', ?, ?, '', '1', '101', 'Saving Account'
	                )
	            ");
	            $insertSql->execute([
	                $cdCode, $full_name, $cid, $phone, $email, $bank_id, $bank_acc_no
	            ]);
	            $m++;
	        }

	        // Mark as processed
	        $updateStatus = $dbh->prepare("
	            UPDATE rights_issue_online_temp SET client_acc_check = 1 WHERE cd_code = ? AND symbol_id = 20
	        ");
	        $updateStatus->execute([$cdCode]);

	        $log_line = "Processed CD Code: $cdCode, CID: $cid, (Updated: $n, Inserted: $m)" . PHP_EOL;
			echo $log_line . "<br>";
			$log_messages .= $log_line;
	    }

	    $dbh->commit();

	    file_put_contents($log_file, $log_messages, FILE_APPEND);
		echo "Log saved to: {$log_file} <br>";

	} catch (Exception $e) {
	    if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
	    error_log("Migration Error: " . $e->getMessage());
	    echo "Error: " . $e->getMessage();
	}

?>