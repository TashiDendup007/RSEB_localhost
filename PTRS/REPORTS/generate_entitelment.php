<?php
include ('../../CONNECTIONS/db.php');
date_default_timezone_set("Asia/Thimphu");

if (isset($_POST['gen_ent'])) {
    $corp_announcement_id = $_POST['gen_ent'];
    $announcement_type = $_POST['atype'];

    $sql = "SELECT a.ribon_volume, a.volume, b.cd_code, b.acc_type, b.title, b.f_name, b.l_name, 
    	(case 
				WHEN b.acc_type = 'I' THEN CONCAT_WS(' ', b.f_name, b.l_name) 
				ELSE CONCAT(b.f_name, ' ', if(b.l_name = '', '', CONCAT(',C/O ', b.l_name))) END
			) full_name,
    	b.ID, b.address, b.tpn, b.phone, b.email, b.bank_account, c.symbol, c.face_value, an.rate, an.record_date, ban.bank_name, ban.bank_short_name, (CASE WHEN a.announcement_type = 3 THEN (c.face_value * a.volume * an.rate) ELSE a.ribon_volume END) entitlement 
			FROM spot_date_holding a
			JOIN client_account b ON a.client_id = b.client_id 
			JOIN symbol c ON a.symbol_id = c.symbol_id
			JOIN corporate_announcement an ON a.corp_announcement_id = an.corp_announcement_id
			JOIN banks ban ON b.bank_id = ban.bank_id
			WHERE a.corp_announcement_id = :gid
			AND a.announcement_type = :ann_type";
		$stmt = $dbh->prepare($sql);
		$stmt->bindParam(':gid', $corp_announcement_id);
		$stmt->bindParam(':ann_type', $announcement_type);
		$stmt->execute();

		$symbol = '';
		$columnHeader = '';  
		$i = 1;
		$columnHeader = "SNO\t CD CODE\t NAME\t CID\t ADDRESS\t TPN\t PHONE\t EMAIL\t BANK ACCOUNT\t BANK NAME\t VOLUME\t RATE\t"; 
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
			if ($rec['entitlement'] > 0) {
				$symbol = $rec['symbol'];
				$rowData = ''; 
			  $value = $i++ . "\t ".
			  $rec['cd_code']."\t". 
			  trim($rec['title'])." ".$rec['full_name']."\t". 
			  $rec['ID'] . "\t". 
			  $rec['address'] . "\t". 
			  $rec['tpn'] . "\t". 
			  $rec['phone'] . "\t". 
			  $rec['email'] . "\t". 
			  trim($rec['bank_account']). "\t".
			  $rec['bank_short_name'] . "\t". 
			  number_format($rec['volume']) . "\t". 
			  number_format($rec['rate'], 2)."\t". 
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
	$columnHeader = "SNO\t CD CODE\t NAME\t TPN\t PHONE\t ADDRESS\t ID\t VOLUME\t BANK name\t BANK Account\t"; 
	$setData = '';  
	while ($rec=$wc->fetch()) { 
	    if($rec['total'] > 0) {
			$name = '';
			if ($rec['acc_type'] == 'I') {
				$name = trim($rec['title'])." ".$rec['f_name'].' '.$rec['l_name'];
			} else {
				$name = $rec['f_name'];
			}

    $rowData = '';  
    $value = $i++ ."\t". 
    str_replace($search, $replace, $rec['cd_code']). "\t".
    str_replace($search, $replace, $name). "\t". 
    str_replace($search, $replace, $rec['tpn']) . "\t". 
    str_replace($search, $replace, $rec['phone']) . "\t". 
    str_replace($search, $replace, $rec['address']) . "\t". 
    str_replace($search, $replace, $rec['ID']) . "\t". 
    str_replace($search, $replace, $rec['total']) . "\t". 
    str_replace($search, $replace, $rec['bank_short_name']) . "\t" . 
    str_replace($search, $replace, trim($rec['bank_account']). " -") . "\t";  
    $rowData .= $value;  
    $setData .= trim($rowData) . "\n";
	    }
	   	        
	}  
	header("Content-type: application/octet-stream");  
	header("Content-Disposition: attachment; filename=General_Shareholders_List.xls");  
	header("Pragma: no-cache");  
	header("Expires: 0");  
	echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
?>
