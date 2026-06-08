<?php
include ('../../CONNECTIONS/db.php');
date_default_timezone_set("Asia/Thimphu");

if (isset($_POST['gen_ent'])) { 
    $corp_announcement_id = $_POST['gen_ent'];
    $announcement_type = $_POST['atype'];

    $sql = "SELECT 
		    a.ribon_volume, 
		    a.volume, 
		    b.cd_code, 
		    b.acc_type, 
		    b.title, 
		    b.f_name, 
		    b.l_name, 
		    CASE 
		        WHEN b.acc_type = 'I' THEN CONCAT_WS(' ', b.f_name, b.l_name) 
		        ELSE CONCAT(b.f_name, IF(b.l_name = '', '', CONCAT(',C/O ', b.l_name))) 
		    END AS full_name,
		    b.ID, 
		    b.address, 
		    b.tpn, 
		    b.phone, 
		    b.email, 
		    b.bank_account, 
		    c.symbol, 
		    c.face_value, 
		    an.rate, 
		    an.record_date, 
		    ban.bank_name, 
		    ban.bank_short_name, 
		    CASE 
		        WHEN a.announcement_type = 3 THEN (c.face_value * a.volume * an.rate) / 100 
		        ELSE a.ribon_volume 
		    END AS entitlement 
		FROM spot_date_holding a
		INNER JOIN client_account b ON a.client_id = b.client_id 
		INNER JOIN symbol c ON a.symbol_id = c.symbol_id
		INNER JOIN corporate_announcement an ON a.corp_announcement_id = an.corp_announcement_id
		INNER JOIN banks ban ON b.bank_id = ban.bank_id
		WHERE a.corp_announcement_id = :gid
		AND a.announcement_type = :ann_type
	";
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(':gid', $corp_announcement_id);
	$stmt->bindParam(':ann_type', $announcement_type);
	$stmt->execute();

	$symbol = '';
	$columnHeader = '';  
	$i = 1;
	$columnHeader = "SNO\t CD CODE\t NAME\t CID\t ADDRESS\t TPN\t PHONE\t EMAIL\t BANK ACCOUNT\t BANK NAME\t VOLUME\t RATE\t Face Value\t"; 
	switch ($announcement_type) {
		case 1:
			$columnHeader .= "RIGHTS VOLUME\t";
			$file_name = "Entitlement_List_Rights_";
			break;
		case 2:
			$columnHeader .= "BONUS VOLUME\t";
			$file_name = "Entitlement_List_Bonus_";
			break;
		case 3:
			$columnHeader .= "AMOUNT\t";
			$file_name = "Entitlement_List_Dividend_";
			break;
		default:
			$columnHeader .= "BUYBACK\t";
			$file_name = "BUYBACK_LIST_";
			break;
	}
	$setData = '';  
	while ($rec = $stmt->fetch()) { 
		if ($rec['volume'] > 0) {
			$symbol = $rec['symbol'];
			$rowData = ''; 
			$value = $i++ . "\t ".
			$rec['cd_code']."\t". 
			trim($rec['title'] ?? '')." ".$rec['full_name']."\t". 
			$rec['ID'] . "\t". 
			trim($rec['address']) . "\t". 
			$rec['tpn'] . "\t". 
			trim($rec['phone']) . "\t". 
			trim($rec['email']) . "\t". 
			trim($rec['bank_account']). "\t".
			$rec['bank_short_name'] . "\t". 
			number_format($rec['volume']) . "\t". 
			number_format($rec['rate'], 2)."\t". 
			number_format($rec['face_value'], 2)."\t". 
			number_format($rec['entitlement'], 2)."\t";
			$rowData .= $value;  
			$setData .= trim($rowData) . "\n"; 
		}
	}

	header("Content-type: application/octet-stream");  
	header("Content-Disposition: attachment; filename=".$file_name."_".$symbol.".xls");  
	header("Pragma: no-cache");  
	header("Expires: 0");  
	echo ucwords($columnHeader) . "\n" . $setData . "\n";
}
elseif(!empty($_GET['ge_export'])) {
	$replace   = array("\n","\r\n","\r");
	$search  = array('','','');
	$symbol_id = $_GET['symbol_id'];

	$wc = $dbh->prepare("SELECT a.acc_type, c.cd_code, a.title, a.f_name, a.l_name, a.tpn, a.phone, a.ID, a.address, a.bank_account, ban.bank_name, ban.bank_short_name, (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) AS total 
  		FROM cds_holding c 
  		JOIN client_account a ON c.cd_code = a.cd_code 
  		JOIN banks ban ON a.bank_id = ban.bank_id
  		WHERE c.symbol_id = :sid 
      ORDER BY a.bank_account ASC
    ");
    $wc->bindParam(':sid',$symbol_id);
    $wc->execute(); 
		$columnHeader = '';  
		$i=1;
		$columnHeader = "SNO\t CD CODE\t NAME\t TPN\t PHONE\t ADDRESS\t ID\t VOLUME\t BANK NAME\t BANK ACCOUNT\t"; 
		$setData = '';  
		while ($rec=$wc->fetch()) { 
 	    if($rec['total'] > 0) {
				$name = '';
				if ($rec['acc_type'] == 'I') {
					$name = trim(isset($rec['title']) ? $rec['title'] : '') . " " . (isset($rec['f_name']) ? $rec['f_name'] : '') . ' ' . (isset($rec['l_name']) ? $rec['l_name'] : '');
				} else {
					$name = $rec['f_name'];
				}

        $rowData = '';  
        $value = $i++ ."\t". 
			str_replace($search, $replace, $rec['cd_code']) . "\t" .
			str_replace($search, $replace, strtoupper($name)) . "\t" . 
			str_replace($search, $replace, isset($rec['tpn']) ? $rec['tpn'] : '') . "\t" . 
			str_replace($search, $replace, isset($rec['phone']) ? trim($rec['phone']) : '') . "\t" . 
			str_replace($search, $replace, isset($rec['address']) ? trim($rec['address']) : '') . "\t" . 
			str_replace($search, $replace, $rec['ID']) . "\t" . 
			str_replace($search, $replace, $rec['total']) . "\t" . 
			str_replace($search, $replace, isset($rec['bank_short_name']) ? $rec['bank_short_name'] : '') . "\t" . 
			str_replace($search, $replace, trim(isset($rec['bank_account']) ? $rec['bank_account'] : '') . " -") . "\t"
		;
        $rowData .= $value;  
        $setData .= trim($rowData) . "\n";
 	    }
		   	        
		}  
		header("Content-type: application/octet-stream");  
		header("Content-Disposition: attachment; filename=General_Shareholders_List.xls");  
		header("Pragma: no-cache");  
		header("Expires: 0");  
		echo ucwords($columnHeader) . "\n" . $setData . "\n"; 

		exit;
}
elseif(!empty($_GET['pledgeDetailsExport'])) {
	    $replace   = array("\n","\r\n","\r");
	    $search  = array('','','');

	    $symbol=$_GET['symbol'];
    	$plType=$_GET['plType'];
    	$sysTime = date("Y-m-d");

    	if ($plType == 'S') {
        $wc = $dbh->prepare("SELECT symbol_id, symbol, name FROM symbol WHERE symbol=:symbol");
        $wc->bindParam(':symbol',$symbol);
        $wc->execute();
        $state = $wc->fetch();
        $symbol_id = $state['symbol_id'];

      	$wc = $dbh->prepare("SELECT pl.cd_code, cl.title, cl.f_name, cl.l_name, cl.ID, sum(pl.pledge_volume) as pledge_volume, pl.pledgee 
	      		FROM cds_pledge pl 
	      		JOIN client_account cl ON pl.cd_code = cl.cd_code
	      		WHERE symbol_id = :sid 
	      		-- AND pledge_volume > 0
	      		GROUP BY pl.cd_code, pl.pledgee 
	      		HAVING SUM(pl.pledge_volume) > 0 
	      		ORDER BY pl.pledge_volume DESC
	      	");
	        $wc->bindParam(':sid', $symbol_id);
	        $wc->execute(); 
					$columnHeader = '';  
					$i = 1;
					$columnHeader = "SNO\t CD CODE\t NAME\t CID\t PLEDGEE\t NUMBER OF SHARES PLEDGED\t"; 
					$setData = '';  
					while ($rec=$wc->fetch()) { 
						$rowData = '';  
						$value = $i++ . "\t ".
						str_replace($search,$replace,$rec['cd_code']). "\t" .
						str_replace($search,$replace,trim($rec['title'])." ".$rec['f_name'].' '.$rec['l_name']). "\t". 
						str_replace($search,$replace,$rec['ID']) . "\t". 
						str_replace($search,$replace,$rec['pledgee']) ."\t". 
						str_replace($search,$replace,$rec['pledge_volume']) ."\t";  
						$rowData .= $value;  
						$setData .= trim($rowData) . "\n";
					}
      } else {
				$wc = $dbh->prepare("SELECT pl.cd_code, cl.title, cl.f_name, cl.l_name, cl.ID, s.symbol, sum(pl.pledge_volume) as pledge_volume 
						FROM cds_pledge pl
						JOIN client_account cl ON pl.cd_code = cl.cd_code
						JOIN symbol s ON pl.symbol_id = s.symbol_id
						WHERE pl.pledgee=:pl 
						-- AND pledge_volume > 0
						GROUP BY pl.symbol_id, pl.cd_code 
						HAVING SUM(pl.pledge_volume) > 0
						ORDER BY pl.pledge_volume DESC
				");
				$wc->bindParam(':pl',$symbol);
				$wc->execute(); 
				$columnHeader = '';  
				$i = 1;
				$columnHeader = "SNO\t CD CODE\t NAME\t CID\t SYMBOL\t NUMBER OF SHARES PLEDGED\t"; 
				$setData = '';  
				WHILE ($rec=$wc->fetch()) { 
					$rowData = '';  
					$value = $i++ . "\t ". 
					str_replace($search,$replace,$rec['cd_code']). "\t" .
					str_replace($search,$replace,trim($rec['title'])." ".$rec['f_name'].' '.$rec['l_name']). "\t". 
					str_replace($search,$replace,$rec['ID']) . "\t". 
					str_replace($search,$replace,$rec['symbol']) . "\t". 
					str_replace($search,$replace,$rec['pledge_volume']) . "\t";  
					$rowData .= $value;  
					$setData .= trim($rowData) . "\n";
        }
      }
			header("Content-type: application/octet-stream");  
			header("Content-Disposition: attachment; filename=Pledge_Details_".$symbol.".xls");  
			header("Pragma: no-cache");  
			header("Expires: 0");  
			echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif (isset($_POST['download_escrow_csv'])) {
    $company_name = $_POST['company_name'];
    $file_name = "Escrow_Report_" . ($company_name === 'ALL' ? 'All_Companies' : $company_name);

    // Column headers with tabs for Excel format
    $columnHeader = "CD CODE\tID\tNAME\tACC. NO\tPAYABLE AMOUNT\tPAID AMOUNT\tBALANCE AMOUNT\tPAYMENT DATE\n";

    // Main Query - matches the report query structure
    $mainQuery = $dbh->prepare('SELECT 
            ud.CID,
            ud.cd_code,
            ud.name,
            ud.amount AS payable_amount,
            (SELECT COALESCE(SUM(up.amount), 0) 
             FROM uc_payment up 
             WHERE up.cid_no = ud.CID) AS paid_amount,
            (SELECT up.account_number 
             FROM uc_payment up 
             WHERE up.cid_no = ud.CID 
             ORDER BY up.payment_date DESC 
             LIMIT 1) AS account_number,
            (SELECT up.payment_date 
             FROM uc_payment up 
             WHERE up.cid_no = ud.CID 
             ORDER BY up.payment_date DESC 
             LIMIT 1) AS payment_date
        FROM unclaimed_dividend ud
        WHERE (:com_name = "ALL" OR ud.company_name = :com_name)
        GROUP BY ud.CID');
    $mainQuery->execute([':com_name' => $company_name]);

    $setData = '';
    $totals = ['payable' => 0, 'paid' => 0, 'balance' => 0];

    while ($record = $mainQuery->fetch(PDO::FETCH_ASSOC)) {
        $payable = (float)$record['payable_amount'];
        $paid = (float)$record['paid_amount'];
        $balance = $payable - $paid;

        $totals['payable'] += $payable;
        $totals['paid'] += $paid;
        $totals['balance'] += $balance;

        $setData .= 
            $record['cd_code'] . "\t" .
            $record['CID'] . "\t" .
            $record['name'] . "\t" .
            ($record['account_number'] ? $record['account_number'] : 'N/A') . "\t" .
            number_format($payable, 2) . "\t" .
            number_format($paid, 2) . "\t" .
            number_format($balance, 2) . "\t" .
            ($record['payment_date'] ? $record['payment_date'] : 'N/A') . "\n";
    }

    // Add totals at the bottom
    $setData .= "\n\nSUMMARY TOTALS\n";
    $setData .= "Total Payable Amount:\t" . number_format($totals['payable'], 2) . "\n";
    $setData .= "Total Paid Amount:\t" . number_format($totals['paid'], 2) . "\n";
    $setData .= "Total Balance Amount:\t" . number_format($totals['balance'], 2) . "\n";

    // Set headers for Excel download
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename={$file_name}_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output the data
    echo ucwords($columnHeader) . $setData;
    exit;
}
?>
