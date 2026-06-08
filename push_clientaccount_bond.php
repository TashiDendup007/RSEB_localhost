<?php 
	// die("Not time for CD Code Migration");

	// Enable full error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // Optional: Set content type header for HTML output
    header('Content-Type: text/html; charset=utf-8');

    date_default_timezone_set("Asia/Thimphu");
	include('CONNECTIONS/db.php');

	try {
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $dbh->beginTransaction();

	    // Select distinct order numbers to process
	    $select = $dbh->prepare("
	        SELECT DISTINCT p.bfs_order_no 
	        FROM bond_ipo_temp_dtls p 
	        WHERE p.bfs_msg_type = 'AC' AND p.bfs_code = '00' 
	            AND p.symbol_id = 118 
	            AND p.client_acc_check = 0 
	            -- AND p.bfs_order_no = 'BS20250519212935682b4e8457411'
	        ORDER BY p.name ASC 
	        LIMIT 3000
	    ");
	    $select->execute();
	    $results = $select->fetchAll(PDO::FETCH_ASSOC);

	    $n = 0;
	    $m = 0;

	    foreach ($results as $res) {
	        $orderNo = $res['bfs_order_no'];

	        // Get client details
	        $getDtls = $dbh->prepare("
	            SELECT 
	                p.cd_code, p.name AS cid_no, p.email, p.phone, 
	                TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.details, '|', 1), '|', -1)) AS full_name,
	                CASE TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.details, '|', 2), '|', -1))
	                    WHEN '1010' THEN 2
	                    WHEN '1020' THEN 1
	                    WHEN '1030' THEN 4
	                    WHEN '1040' THEN 5
	                    WHEN '1050' THEN 3
	                    WHEN '1060' THEN 7
	                    ELSE 0
	                END AS bank_id,
	                TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.details, '|', 3), '|', -1)) AS account_no,
	                p.dateOfBirth,
	                CASE 
	                    WHEN p.dateOfBirth LIKE '__/__/____' THEN STR_TO_DATE(p.dateOfBirth, '%d/%m/%Y')
	                    ELSE p.dateOfBirth
	                END AS dob, p.gender, p.presentAddress 
	            FROM bond_ipo_temp_dtls p
	            WHERE p.bfs_order_no = ?
	                AND p.details IS NOT NULL 
	            ORDER BY p.id ASC 
	            LIMIT 1
	        ");
	        $getDtls->execute([$orderNo]);
	        $row = $getDtls->fetch(PDO::FETCH_ASSOC);

	        if (!$row || empty($row['cd_code'])) {
	            continue; // skip if no data or CD code missing
	        }

	        // Extract details
	        $cdCode      = $row['cd_code'];
			$cid         = $row['cid_no'];
			$phone       = $row['phone'];
			$email       = $row['email'];
			$bank_id     = $row['bank_id'];
			$bank_acc_no = $row['account_no'];
			$full_name   = $row['full_name'];
			$dob         = $row['dob'];
			$gender      = $row['gender'];
			$presentAddress      = isset($row['presentAddress']) ? $row['presentAddress'] : 14;

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
		                SET phone = ?, email = ?, bank_id = ?, bank_account = ?, dob = ?, gender = ? 
		                WHERE cd_code = ?
		            ");
		            $updateAccount->execute([
		                $phone, $email, $bank_id, $bank_acc_no, $dob, $gender, $cdCode
		            ]);
	        	} else {
	        		echo "Could no Update. Duplicate CD Code ". $cdCode . "<br>";
	        	}
	            
	            $n++;

	        } else {

	            // Insert new account
	            $insertSql = $dbh->prepare("
	                INSERT INTO client_account (
	                    acc_type, cd_code, f_name, nationality, ID, DzongkhagID, phone, user_name, email, bank_id, bank_account, bro_comm_id, address, institution_id, occupation, bank_account_type, dob, gender
	                ) VALUES (
	                    'I', ?, ?, 'Bhutanese', ?, ?, ?, 'EMPRSEB009', ?, ?, ?, '37', '', '1', '101', 'Saving Account', ?, ?
	                )
	            ");
	            $insertSql->execute([
	                $cdCode, $full_name, $cid, $presentAddress, $phone, $email, $bank_id, $bank_acc_no, $dob, $gender
	            ]);
	            $m++;
	        }

	        // Mark as processed
	        $updateStatus = $dbh->prepare("
	            UPDATE bond_ipo_temp_dtls SET client_acc_check = 1 WHERE cd_code = ?
	        ");
	        $updateStatus->execute([$cdCode]);

	        echo "Processed CD Code: $cdCode, CID: $cid, (Updated: $n, Inserted: $m)<br>";
	    }

	    $dbh->commit();

	} catch (Exception $e) {
	    if ($dbh->inTransaction()) {
	        $dbh->rollBack();
	    }
	    error_log("Migration Error: " . $e->getMessage());
	    echo "Error: " . $e->getMessage();
	}

?>