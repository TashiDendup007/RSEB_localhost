<?php
	die("Please check the code before migrate to CDS Holding");

	//since migration is done from first issue , we are assuming that there is no record of this symbol in cds_holding , therefore we are directly inserting. else we might have to add to the volume

	// Optionally also increase other limits
	ini_set('memory_limit', '512M');
	set_time_limit(0);

	date_default_timezone_set("Asia/Thimphu");
	include('../../CONNECTIONS/db.php');

	echo "| Start: " . date('Y-m-d H:i:s') . "<br>";
	try {
	    // Begin transaction for safety and speed
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $dbh->beginTransaction();

	    // Fetch all allocated bonds for the specific symbol
	    $all = $dbh->prepare("
	        SELECT cd_code, allocated_size, user_name, symbol_id 
	        FROM bond
	        WHERE allocated_size != 0 
	            AND status = 1
	            AND symbol_id = 118
	        ORDER BY allocated_size DESC
	        LIMIT 5000
	    ");
	    $all->execute();
	    $rows = $all->fetchAll(PDO::FETCH_ASSOC);

	    // Prepare reusable statements
	    $get_inst_id = $dbh->prepare("SELECT institution_id FROM adm_participants WHERE participant_code = ?");
	    
	    $save_holding = $dbh->prepare("INSERT INTO cds_holding (cd_code, volume, user_name, institution_id, symbol_id, remarks) VALUES (?, ?, ?, ?, ?, ?)");

	    $update_status = $dbh->prepare("UPDATE bond SET status = 2 WHERE status = 1 AND symbol_id = 118 AND cd_code = ?");

	    $total_vol = 0;

	    foreach ($rows as $srb) {
	        $cd_code   = $srb['cd_code'];
	        $vol       = (int) $srb['allocated_size'];
	        $username  = $srb['user_name'];
	        $symbol_id = $srb['symbol_id'];

	        $remarks = "Record First entered via BOND IPO of {$vol} number of shares";

	        // Get institution ID using first 4 letters of user_name
	        $participant_code = substr($username, 0, 4);

	        $get_inst_id->execute([$participant_code]);
	        $inst_id = $get_inst_id->fetchColumn();

	        $institution_id = $inst_id !== false ? $inst_id : 1;

	        // Skip if institution not found (optional safety check)
	        if (!$institution_id) {
	            error_log("Institution ID not found for participant code: $participant_code");
	            continue;
	        }

	        // Save into cds_holding
	        $save_holding->execute([
	            $cd_code, $vol, $username, $institution_id, $symbol_id, $remarks
	        ]);

	        $msg = sprintf(
                "CD Code: %s, Symbol Id: %s, Order Size: %s <br>",
                $cd_code,
                $symbol_id,
                $vol
            );

            echo $msg;

	        // Update bond status
	        $update_status->execute([$cd_code]);

	        $total_vol += $vol;
	    }

	    $dbh->commit();

	    echo "Total Paid up shares ==> {$total_vol}<br>";
	    echo "| End: " . date('Y-m-d H:i:s') . "<br>";

	} catch (Exception $e) {
	    $dbh->rollBack();
	    error_log("Error during bond holding migration: " . $e->getMessage());
	    echo "Error: " . $e->getMessage();
	}

?>