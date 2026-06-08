<?php 
	// SCRIPT TO BACKUP SHARE HOLDING DETAILS TO EXCEL
	date_default_timezone_set('Asia/Thimphu');
	include('CONNECTIONS/db.php');

	try {
	    // Fetch all symbols only once
	    $stmt = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE status = 1 ORDER BY symbol ASC");
	    $stmt->execute();
	    $symbols = $stmt->fetchAll(PDO::FETCH_ASSOC);

	    // Prepare header
	    $columnHeader = "CD CODE\t NAME\t ID\t TPN\t PHONE\t Email\t ADDRESS\t BANK\t BANK Account\t SYMBOL\t VOLUME\t";
	    $replace = ["\n", "\r\n", "\r"];
	    $search = ['', '', '']; 

	    $backupDir = "BACKUP/";
	    if (!is_dir($backupDir)) {
	        mkdir($backupDir, 0755, true);
	    }

	    foreach ($symbols as $symbol) {
	        $symbol_id = $symbol['symbol_id'];

	        // Fetch client data for each symbol
	        $select = $dbh->prepare("
		            SELECT 
		                CASE 
		                    WHEN a.acc_type = 'J' THEN a.f_name
		                    ELSE CONCAT_WS(' ', a.f_name, a.l_name)
		                END AS fl_name, a.title, a.cd_code, a.ID, a.phone, a.email, a.tpn, b.bank_short_name, CAST(a.bank_account AS CHAR) AS bank_account, a.address, s.symbol, (c.volume + c.block_volume + c.pledge_volume + c.pending_out_vol) AS tot_vol
		            FROM client_account a 
		            LEFT JOIN cds_holding c ON a.cd_code = c.cd_code
		            LEFT JOIN banks b ON a.bank_id = b.bank_id
		            LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
		            WHERE c.symbol_id = ?
		            HAVING tot_vol > 0
	        ");
	        $select->execute([$symbol_id]);

	        $setData = '';

	        while ($rec = $select->fetch(PDO::FETCH_ASSOC)) {
	            /*$rowData = implode("\t", [
	                str_replace($search, $replace, trim($rec['cd_code'])), 
	                str_replace($search, $replace, trim($rec['title']) . " " . trim($rec['fl_name'])),
	                str_replace($search, $replace, trim($rec['ID'])),
	                str_replace($search, $replace, trim($rec['tpn'])),
	                str_replace($search, $replace, trim($rec['phone'])),
	                str_replace($search, $replace, trim($rec['email'])),
	                str_replace($search, $replace, trim($rec['address'])),
	                str_replace($search, $replace, trim($rec['bank_short_name'])),
	                str_replace($search, $replace, trim($rec['bank_account'])),
	                str_replace($search, $replace, trim($rec['symbol'])),
	                str_replace($search, $replace, $rec['tot_vol']),
	            ]);*/
	            $rowData = implode("\t", [
				    str_replace($search, $replace, trim((string) $rec['cd_code'])),
				    str_replace($search, $replace, trim((string) (isset($rec['title']) ? $rec['title'] : '')) . " " . trim((string) $rec['fl_name'])),
				    str_replace($search, $replace, trim((string) $rec['ID'])),
				    str_replace($search, $replace, trim((string) (isset($rec['tpn']) ? $rec['tpn'] : ''))),
				    str_replace($search, $replace, trim((string) (isset($rec['phone']) ? $rec['phone'] : ''))),
				    str_replace($search, $replace, trim((string) (isset($rec['email']) ? $rec['email'] : ''))),
				    str_replace($search, $replace, trim((string) $rec['address'])),
				    str_replace($search, $replace, trim((string) (isset($rec['bank_short_name']) ? $rec['bank_short_name'] : ''))),
				    str_replace($search, $replace, trim((string) (isset($rec['bank_account']) ? $rec['bank_account'] : ''))),
				    str_replace($search, $replace, trim((string) $rec['symbol'])),
				    str_replace($search, $replace, (string) $rec['tot_vol']) // No need for trim on integers
				]);
	            $setData .= trim($rowData) . "\n";  
	        }

	        // Create filename with date
	        $filename = $backupDir . date('d-m-Y') . '.xls';
	        file_put_contents($filename, ucwords($columnHeader) . "\n" . $setData . "\n", FILE_APPEND);
	    }

	    // Insert backup record into the database
        $bak_date = date('d-m-Y');
        $link = '../../' . $filename;
        $username = 'Admin';

        $stmt = $dbh->prepare("INSERT INTO backupsr (name, link, created_by) VALUES (:da_te, :li_nk, :usr_name)");
        $stmt->execute([':da_te' => $bak_date, ':li_nk' => $link, ':usr_name' => $username]);

	    // echo "Backup created: " . $filename;

	} catch (Exception $e) {
	    error_log($e->getMessage());
	} finally {
	    $dbh = null;
	}
?>
