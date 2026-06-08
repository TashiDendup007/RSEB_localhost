<?php
	date_default_timezone_set("Asia/Thimphu");
	include('../CONNECTIONS/db.php');

	if(isset($_POST["getExpoxt_refundList"]))
	{
		$replace   = array("\n","\r\n","\r");
	    $search    = array('','','');
	    $columnHeader = '';  
	    $i=1;
	    $columnHeader = 
		"Sl No"."\t".
		"CD CODE"."\t".
		"Name"."\t".
		"Email"."\t".
		"Phone"."\t".
		"Bank Name"."\t".
		"Account"."\t".
		"Symbol"."\t".
		"Allocated"."\t".
		"Bid Price"."\t".
		"Total Deposit"."\t".
		"Amount Subscribed"."\t".
		"Refund"."\t".
		"UserName"."\t"; 

	    $setData = '';
	    $orders= $dbh->prepare('SELECT a.cd_code, a.order_size, a.bid_price, a.allocated_size, a.user_name, a.symbol_id, b.f_name, b.l_name, b.bank_account, b.email, b.phone, a.price_discovered 
            FROM rights_issue a, client_account b 
            WHERE a.cd_code=b.cd_code AND a.user_name NOT LIKE "MEM%" AND a.user_name NOT LIKE "CC%" ');
		$orders->execute();
		$i=1;
		foreach($orders as $row){
			$tempAccountNo = $dbh->prepare("SELECT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, '|', 1), '|', -1) AS NAME, b.phone, b.email,
                SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, '|', 2), '|', -1) AS bank_name,
                SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, '|', 3), '|', -1) AS AccountNo
                FROM rights_issue_online_temp b
                WHERE b.cd_code=:cdcode22");
            $tempAccountNo->bindParam(':cdcode22', $row['cd_code']);
            $tempAccountNo->execute();
            $dataTemp = $tempAccountNo->fetch();

			$orders = $dbh->prepare('SELECT SUM(amount) as total FROM rights_issue_online_temp WHERE bfs_code="00" AND type = "AC" AND name=:name');
	        $orders->bindParam(':name', $row['user_name']);
	        $orders->execute();
	        $data = $orders->fetch();

	        $symbol='';
	        if($row['symbol_id']==5){
	            $symbol='BNBL';
	        }else{
	            $symbol = 'RICB';
	        }
	        $total=$row['allocated_size']*$row['price_discovered'];
	        $commission=$row['allocated_size']*$row['price_discovered']*0.01;
	        $GT = $total+$commission;
	        $refund = $data['total']-$GT;

	        $rowData = '';  
	        $value = 
	        	str_replace($search,$replace,$i)."\t".
	        	str_replace($search,$replace,$row['cd_code'])."\t".
	        	str_replace($search,$replace,trim($row['f_name'])." ".trim($row['l_name']))."\t".
	        	str_replace($search,$replace,$dataTemp['email'])."\t".
	        	str_replace($search,$replace,$dataTemp['phone'])."\t".
	        	str_replace($search,$replace,trim($dataTemp['bank_name']))."\t".
	        	str_replace($search,$replace,trim($dataTemp['AccountNo']))."\t".
	        	str_replace($search,$replace,$symbol)."\t".
	        	str_replace($search,$replace,$row['allocated_size'])."\t".
	        	str_replace($search,$replace,$row['bid_price'])."\t".
	        	str_replace($search,$replace,$data['total'])."\t".
	        	str_replace($search,$replace,number_format((float)$GT, 2, '.', ''))."\t".
	        	str_replace($search,$replace,number_format((float)$refund, 2, '.', ''))."\t".
	        	str_replace($search,$replace,$row['user_name'])."\t";
	        $rowData .= $value;  
	        $setData .= trim($rowData)."\n"; 
	        $i++;
		}
	    /*while($row = $orders->fetch())
	    {*/
	    	    
		//}  
	    header("Content-type: application/octet-stream");  
	    header("Content-Disposition: attachment; filename=RefundList.xls");  
	    header("Pragma: no-cache");  
	    header("Expires: 0");  
	    echo ucwords($columnHeader)."\n".$setData."\n"; 
	}
	//to generate CC refund list
	else if(isset($_POST["getExpoxt_refundList_cc"]))
	{
		$replace   = array("\n","\r\n","\r");
	    $search    = array('','','');
	    $columnHeader = '';  
	    $i=1;
	    $columnHeader = 
		"CD CODE"."\t".
		"Name"."\t".
		"CID"."\t".
		"Email"."\t".
		"Phone"."\t".
		"Bank Name"."\t".
		"Account"."\t".
		"Symbol"."\t".
		"Order Size"."\t".
		"Allocated"."\t".
		"Bid Price"."\t".
		"Discovered Price"."\t".
		"Total Deposit"."\t".
		"Amount Subscribed"."\t".
		"Refund"."\t".
		"UserName"."\t"; 

	    $setData = '';
	    $orders= $dbh->prepare("SELECT a.cd_code, a.order_size, a.bid_price, a.allocated_size, a.available_rights, a.user_name, a.symbol_id, b.f_name, b.l_name, b.bank_account, b.email, b.phone, a.price_discovered, c.symbol 
            FROM rights_issue a, client_account b, symbol c 
            WHERE a.user_name LIKE  'c%'
            AND a.symbol_id=c.symbol_id AND a.cd_code=b.cd_code 
            -- LIMIT 1000 OFFSET 0");
		$orders->execute();
		foreach($orders as $row){
			$tempAccountNo = $dbh->prepare("SELECT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, '|', 1), '|', -1) AS NAME, b.phone, b.email, b.name, 
                SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, '|', 2), '|', -1) AS bank_name,
                SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, '|', 3), '|', -1) AS AccountNo
                FROM rights_issue_online_temp b
                WHERE b.cd_code=:cdcode22");
            $tempAccountNo->bindParam(':cdcode22', $row['cd_code']);
            $tempAccountNo->execute();
            $dataTemp = $tempAccountNo->fetch();

			$orders = $dbh->prepare("SELECT SUM(amount) AS total FROM rights_issue_online_temp WHERE symbol_id=:id AND cd_code=:cd");
	        $orders->bindParam(':id', $row['symbol_id']);
	        $orders->bindParam(':cd', $row['cd_code']);
	        $orders->execute();
	        $data = $orders->fetch();

	        $total=$row['allocated_size']*$row['price_discovered'];
	        $commission=$row['allocated_size']*$row['price_discovered']*0.01;
	        $GT = $total+$commission;
	        $refund = $data['total']-$GT;

	        $rowData = '';  
	        $value = 
	        	str_replace($search,$replace,$row['cd_code'])."\t".
	        	str_replace($search,$replace,trim($row['f_name'])." ".trim($row['l_name']))."\t".
	        	str_replace($search,$replace,$dataTemp['name'])."\t".
	        	str_replace($search,$replace,$dataTemp['email'])."\t".
	        	str_replace($search,$replace,$dataTemp['phone'])."\t".
	        	str_replace($search,$replace,trim($dataTemp['bank_name']))."\t".
	        	str_replace($search,$replace,trim($dataTemp['AccountNo']))."\t".
	        	str_replace($search,$replace,$row['symbol'])."\t".
	        	str_replace($search,$replace,$row['available_rights'])."\t".
	        	str_replace($search,$replace,$row['allocated_size'])."\t".
	        	str_replace($search,$replace,$row['bid_price'])."\t".
	        	str_replace($search,$replace,$row['price_discovered'])."\t".
	        	str_replace($search,$replace,$data['total'])."\t".
	        	str_replace($search,$replace,$GT)."\t".
	        	str_replace($search,$replace,$refund)."\t".
	        	str_replace($search,$replace,$row['user_name'])."\t";
	        $rowData .= $value;  
	        $setData .= trim($rowData)."\n"; 
		}
	    header("Content-type: application/octet-stream");  
	    header("Content-Disposition: attachment; filename=RefundList_CC.xls");  
	    header("Pragma: no-cache");  
	    header("Expires: 0");  
	    echo ucwords($columnHeader)."\n".$setData."\n"; 
	}
	//to generate p001 refund list
	else if(isset($_POST["getExpoxt_refundList_p001"]))
	{
		$replace   = array("\n","\r\n","\r");
	    $search    = array('','','');
	    $columnHeader = '';  
	    $i=1;
	    $columnHeader = 
		"CD CODE"."\t".
		"CID No"."\t".
		"Name"."\t".
		"Email"."\t".
		"Phone"."\t".
		"Account"."\t".
		"Symbol"."\t".
		"Allocated"."\t".
		"Bid Price"."\t".
		"Total Deposit"."\t".
		"Amount Subscribed"."\t".
		"Refund"."\t".
		"UserName"."\t"; 

	    $setData = '';
	    $orders= $dbh->prepare("SELECT a.cd_code, b.ID CID, a.order_size, a.bid_price, a.allocated_size, a.user_name, a.symbol_id, b.f_name, b.l_name, b.bank_account, b.email, b.phone, a.price_discovered, c.symbol 
            FROM rights_issue a, client_account b, symbol c 
            WHERE a.user_name LIKE 'p001%'
            AND a.symbol_id=c.symbol_id AND a.cd_code=b.cd_code 
            -- LIMIT 1000 OFFSET 0");
		$orders->execute();
		foreach($orders as $row){
			//$orders = $dbh->prepare("SELECT SUM(amount) AS total FROM rights_issue_online_temp WHERE symbol_id=:id AND cd_code=:cd");
			$orders22 = $dbh->prepare("SELECT amount, payment_status, vol_applied, price, amount AS total FROM rights_issue_online_temp WHERE symbol_id=:id AND cd_code=:cd");
	        $orders22->bindParam(':id', $row['symbol_id']);
	        $orders22->bindParam(':cd', $row['cd_code']);
	        $orders22->execute();
	        //$data = $orders22->fetch();
	        $data_tot=0;
	        foreach($orders22 as $data){
	        	$data_tot1=0;
	        	if($data['payment_status'] == 'PE'){
	        		$data_tot1 = ($data['vol_applied'] * $data['price'])+($data['vol_applied'] * $data['price'] * 0.01);
	        	}else{
	        		$data_tot1 = $data['amount'];
	        	}
	        	$data_tot = $data_tot+$data_tot1;
	        }
	        
	        $total=$row['allocated_size']*$row['price_discovered'];
	        $commission=$row['allocated_size']*$row['price_discovered']*0.01;
	        $GT = $total+$commission;
	        //$refund = $data['total']-$GT;
	        $refund = $data_tot-$GT;

	        $rowData = '';  
	        $value = 
	        	str_replace($search,$replace,$row['cd_code'])."\t".
	        	str_replace($search,$replace,$row['CID'])."\t".
	        	str_replace($search,$replace,trim($row['f_name'])." ".trim($row['l_name']))."\t".
	        	str_replace($search,$replace,$row['email'])."\t".
	        	str_replace($search,$replace,$row['phone'])."\t".
	        	str_replace($search,$replace,trim($row['bank_account']))."\t".
	        	str_replace($search,$replace,$row['symbol'])."\t".
	        	str_replace($search,$replace,$row['allocated_size'])."\t".
	        	str_replace($search,$replace,$row['bid_price'])."\t".
	        	str_replace($search,$replace,$data_tot)."\t".
	        	str_replace($search,$replace,$GT)."\t".
	        	str_replace($search,$replace,$refund)."\t".
	        	str_replace($search,$replace,$row['user_name'])."\t";
	        $rowData .= $value;  
	        $setData .= trim($rowData)."\n"; 
		}
	    header("Content-type: application/octet-stream");  
	    header("Content-Disposition: attachment; filename=RefundList_P001.xls");  
	    header("Pragma: no-cache");  
	    header("Expires: 0");  
	    echo ucwords($columnHeader)."\n".$setData."\n"; 
	}
	elseif (isset($_POST["generate_payment_list_gnbb001"])) {
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];

		try {
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$totalDeposited = 0;
            $orders = $dbh->prepare("SELECT 
                    r.bfs_orderid, r.bfs_code, r.cd_code, r.vol_applied, r.price, r.amount, r.`type`, r.phone, r.email, r.name as cid_no, r.dateentry
                    FROM rights_issue_online_temp r
                    WHERE r.symbol_id = 20
                    AND r.bfs_code = '00'
                    AND DATE(r.dateentry) BETWEEN ? AND ?
                    AND r.vol_applied != 0
                    AND DATE(r.dateentry) > '2025-07-01'
					GROUP BY r.bfs_orderid
                    ORDER BY r.id ASC 
            ");
            $orders->execute([$start_date, $end_date]);
            $ordersss = $orders->fetchAll(PDO::FETCH_ASSOC);
            $i=1;
            foreach($ordersss as $row){
                $totalDeposited += $row['amount'];
                echo'
                <tr>
                    <td>'.$i.'</td>
                    <td>'.$row['bfs_orderid'].'</td>
                    <td>'.$row['bfs_code'].'</td>
                    <td>'.$row['cd_code'].'</td>
                    <td>'.$row['vol_applied'].'</td>
                    <td>'.$row['price'].'</td>
                    <td>'.$row['amount'].'</td>
                    <td>'.$row['type'].'</td>
                    <td>'.$row['phone'].'</td>
                    <td>'.$row['email'].'</td>
                    <td>'.$row['cid_no'].'</td>
                    <td>'.$row['dateentry'].'</td>
                </tr>';
                $i++;
            }
            echo'
            <tr>
                <td colspan="12">Total Deposited: = Nu. <strong>'.number_format($totalDeposited, 2).'</strong></td>
            </tr>
            ';
		} catch (Exception $e) {
			echo'Error occured';
		}
		exit;
	}
	elseif(isset($_GET["getExpoxt_payment_list"]))
	{
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];

		$replace   = array("\n","\r\n","\r");
	    $search    = array('','','');
	    $columnHeader = '';  
	    $i=1;
	    $columnHeader = 
		"Sl No"."\t".
		"Order No"."\t".
		"BFS Code"."\t".
		"CD CODE"."\t".
		"Vol"."\t".
		"Price"."\t".
		"Amount"."\t".
		"Msg Type"."\t".
		"Phone"."\t".
		"Email"."\t".
		"CID"."\t".
		"Date"."\t".

	    $setData = '';
	    $orders = $dbh->prepare("SELECT 
                r.bfs_orderid, r.bfs_code, r.cd_code, r.vol_applied, r.price, r.amount, r.`type`, r.phone, r.email, r.name as cid_no, r.dateentry
                FROM rights_issue_online_temp r
                WHERE r.symbol_id = 20
                AND r.bfs_code = '00'
                AND DATE(r.dateentry) BETWEEN ? AND ?
                AND r.vol_applied != 0
                AND DATE(r.dateentry) > '2025-07-01'
				GROUP BY r.bfs_orderid
                ORDER BY r.id ASC 
	    ");
		$orders->execute([$start_date, $end_date]);
		$ordersss = $orders->fetchAll(PDO::FETCH_ASSOC);
		$i=1;
		foreach($ordersss as $row){
	        $rowData = '';  
	        $value = 
	        	str_replace($search, $replace, $i)."\t".
	        	str_replace($search,$replace,trim($row['bfs_orderid']))."\t".
	        	str_replace($search,$replace,trim($row['bfs_code']))."\t".
	        	str_replace($search,$replace,$row['cd_code'])."\t".
	        	str_replace($search,$replace,$row['vol_applied'])."\t".
	        	str_replace($search,$replace,trim($row['price']))."\t".
	        	str_replace($search,$replace,trim($row['amount']))."\t".
	        	str_replace($search,$replace,$row['type'])."\t".
	        	str_replace($search,$replace,$row['phone'])."\t".
	        	str_replace($search,$replace,$row['email'])."\t".
	        	str_replace($search,$replace,$row['cid_no'])."\t".
	        	str_replace($search,$replace,$row['dateentry'])."\t";
	        $rowData .= $value;  
	        $setData .= trim($rowData)."\n"; 
	        $i++;
		}
	    header("Content-type: application/octet-stream");  
	    header("Content-Disposition: attachment; filename=Payment_List.xls");  
	    header("Pragma: no-cache");  
	    header("Expires: 0");  
	    echo ucwords($columnHeader)."\n".$setData."\n"; 
	}
	elseif (isset($_POST["generate_payment_list_gnbb001_rseb"])) {
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];

		try {
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$totalDeposited = 0;
            $orders = $dbh->prepare("SELECT 
                    d.bfs_order_no, d.bfs_code, d.cd_code, d.vol_applied, d.price, d.amount, d.bfs_msg_type, d.phone, d.email, d.name as cid_no, d.created_at, d.details 
                    FROM bond_ipo_temp_dtls d 
                    WHERE d.symbol_id = 118
                    AND d.bfs_code = '00'
                    AND d.employee_id LIKE 'EMPRSEB%'
                    AND DATE(d.created_at) BETWEEN ? AND ?
            ");
            $orders->execute([$start_date, $end_date]);
            $ordersss = $orders->fetchAll(PDO::FETCH_ASSOC);
            $i=1;
            foreach($ordersss as $row){
                $totalDeposited += $row['amount'];
                echo'
                <tr>
                    <td>'.$i.'</td>
                    <td>'.$row['bfs_order_no'].'</td>
                    <td>'.$row['cd_code'].'</td>
                    <td>'.$row['vol_applied'].'</td>
                    <td>'.$row['price'].'</td>
                    <td>'.number_format($row['amount'], 2).'</td>
                    <td>'.$row['details'].'</td>
                    <td>'.$row['phone'].'</td>
                    <td>'.$row['email'].'</td>
                    <td>'.$row['cid_no'].'</td>
                    <td>'.$row['created_at'].'</td>
                </tr>';
                $i++;
            }
            echo'
            <tr>
                <td colspan="12">Total Deposited: = Nu. <strong>'.number_format($totalDeposited, 2).'</strong></td>
            </tr>
            ';
		} catch (Exception $e) {
			echo'Error occured';
		}
		exit;
	}
	elseif(isset($_GET["getExpoxt_payment_list_rseb"]))
	{
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];

		$replace   = array("\n","\r\n","\r");
	    $search    = array('','','');
	    $columnHeader = '';  
	    $i=1;
	    $columnHeader = 
		"Sl No"."\t".
		"Order No"."\t".
		"CD CODE"."\t".
		"Vol"."\t".
		"Price"."\t".
		"Amount"."\t".
		"Dtls/Journal No"."\t".
		"Phone"."\t".
		"Email"."\t".
		"CID"."\t".
		"Date"."\t".

	    $setData = '';
	    $orders= $dbh->prepare("SELECT 
                d.bfs_order_no, d.bfs_code, d.cd_code, d.vol_applied, d.price, d.amount, d.bfs_msg_type, d.phone, d.email, d.name as cid_no, d.created_at, d.details
                FROM bond_ipo_temp_dtls d 
                WHERE d.symbol_id = 118
                AND d.bfs_code = '00' 
                AND d.employee_id LIKE 'EMPRSEB%'
                AND DATE(d.created_at) BETWEEN ? AND ?
	    ");
		$orders->execute([$start_date, $end_date]);
		$ordersss = $orders->fetchAll(PDO::FETCH_ASSOC);
		$i=1;
		foreach($ordersss as $row){
	        $rowData = '';  
	        $value = 
	        	str_replace($search, $replace, $i)."\t".
	        	str_replace($search,$replace,trim($row['bfs_order_no']))."\t".
	        	str_replace($search,$replace,$row['cd_code'])."\t".
	        	str_replace($search,$replace,$row['vol_applied'])."\t".
	        	str_replace($search,$replace,trim($row['price']))."\t".
	        	str_replace($search,$replace,trim($row['amount']))."\t".
	        	str_replace($search,$replace,$row['details'])."\t".
	        	str_replace($search,$replace,$row['phone'])."\t".
	        	str_replace($search,$replace,$row['email'])."\t".
	        	str_replace($search,$replace,$row['cid_no'])."\t".
	        	str_replace($search,$replace,$row['created_at'])."\t";
	        $rowData .= $value;  
	        $setData .= trim($rowData)."\n"; 
	        $i++;
		}
	    header("Content-type: application/octet-stream");  
	    header("Content-Disposition: attachment; filename=Payment_List.xls");  
	    header("Pragma: no-cache");  
	    header("Expires: 0");  
	    echo ucwords($columnHeader)."\n".$setData."\n"; 
	}
	elseif(isset($_GET["generate_excel_bond_check"]))
	{
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];

		$replace   = array("\n","\r\n","\r");
	    $search    = array('','','');
	    $columnHeader = '';  
	    $i=1;
	    $columnHeader = 
		"Sl No"."\t".
		"Symbol"."\t".
		"CD Code"."\t".
		"CID"."\t".
		"Face Value"."\t".
		"Order Size"."\t".
		"Buy Vol"."\t".
		"OS_FV"."\t".
		"BOND Total Amount"."\t".
		"BFS Total Amount"."\t".

	    $setData = '';
	    $orders= $dbh->prepare("SELECT r.symbol_id, 'GNBB001' AS symbol, r.face_value, r.cd_code, r.order_size, r.buy_vol, (r.bid_price * r.order_size) AS tValue, r.total_amount, r.cid_no
                FROM bond r 
                WHERE r.user_name NOT LIKE 'MEM%' AND r.symbol_id = 118 AND r.type = 'BOND' 
                AND DATE(r.order_date) BETWEEN ? AND ?
                -- Limit 50
	    ");
		$orders->execute([$start_date, $end_date]);
		$ordersss = $orders->fetchAll(PDO::FETCH_ASSOC);
		$i=1;
		foreach($ordersss as $row){

			$stmt = $dbh->prepare("SELECT SUM(b.amount) as tot_amount FROM bond_ipo_temp_dtls b WHERE b.symbol_id = 118 AND b.bfs_code = '00' AND b.name = ?");
            $stmt->execute([$row['cid_no']]);
            $bfs_amount = $stmt->fetchColumn();

	        $rowData = '';  
	        $value = 
	        	str_replace($search, $replace, $i)."\t".
	        	str_replace($search,$replace,trim($row['symbol']))."\t".
	        	str_replace($search,$replace,trim($row['cd_code']))."\t".
	        	str_replace($search,$replace,trim($row['cid_no']))."\t".
	        	str_replace($search,$replace,$row['face_value'])."\t".
	        	str_replace($search,$replace,trim($row['order_size']))."\t".
	        	str_replace($search,$replace,trim($row['buy_vol']))."\t".
	        	str_replace($search,$replace,trim($row['tValue']))."\t".
	        	str_replace($search,$replace,$row['total_amount'])."\t".
	        	str_replace($search,$replace, $bfs_amount)."\t";
	        $rowData .= $value;  
	        $setData .= trim($rowData)."\n"; 
	        $i++;
		}
	    header("Content-type: application/octet-stream");  
	    header("Content-Disposition: attachment; filename=Payment_List.xls");  
	    header("Pragma: no-cache");  
	    header("Expires: 0");  
	    echo ucwords($columnHeader)."\n".$setData."\n"; 
	}
	else{
		echo' <h2><span class="text-center"> No such method!!! </span></h2>';
		die();
	}
?>