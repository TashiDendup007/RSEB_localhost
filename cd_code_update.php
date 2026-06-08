<?php  
	date_default_timezone_set("Asia/Thimphu");
    include ('CONNECTIONS/db.php');
    die();
    try {
    	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh->beginTransaction();

    	$stmt = $dbh->prepare("SELECT b.cd_code, b.user_name, b.cid_no 
	    			FROM bond b 
	    			WHERE LENGTH(b.cd_code) < 6 
					AND b.symbol_id = 118
					AND b.user_name NOT LIKE 'MEM%' 
					-- LIMIT 1
		");
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as $row) {
			$old_cd_code = $row['cd_code'];
			$get = $dbh->prepare("SELECT b.cd_code, b.name AS cid, b.phone, b.email, b.details, b.created_at
					FROM bond_ipo_temp_dtls b 
					WHERE b.cd_code = ?
					AND b.name = ?
					AND b.symbol_id = 118 
					AND b.bfs_code = '00'
			");
			$get->execute([$old_cd_code, $row['cid_no']]);
			$results = $get->fetchAll(PDO::FETCH_ASSOC);

			$count = 1;
			foreach ($results as $key => $value) {
				echo 'count ==> '.$count;
				$name = trim(explode('|', $value['details'])[0]);
				$cid = $value['cid'];

				echo ', cid ==> '.$cid;

				// $email = $value['email'];
				// $sys_date_time = date("YmdHis", strtotime($value['created_at']));

				$n1 = substr(strtoupper($name), 0, 2);
				$n2 = substr($cid, -3);
				// $n3 = substr(strtoupper($email), 0, 2);
				// $n4 = substr($sys_date_time, -3);
				$new_cd_code = $n1.$n2.$old_cd_code;
				// echo $new_cd_code.'<br>';

				$check = $dbh->prepare("SELECT 1 FROM bond WHERE cd_code = ? AND symbol_id = 118");
				$check->execute([$new_cd_code]);
				if ($check->fetchColumn()) {
					echo ', CD code present, ';
					echo 'old cd code ==> '.$old_cd_code.', ';
					echo 'New cd_code ==> '.$new_cd_code;
					echo'<hr>';
				} else {
					echo', updated cd code, ';
					echo 'old cd code ==> '.$old_cd_code.', ';
					echo 'New cd_code ==> '.$new_cd_code;
					echo'<hr>';
					// update cd_code 
					$upd_bond = $dbh->prepare("UPDATE bond b SET b.cd_code = ? WHERE b.cd_code = ? AND b.cid_no = ? AND b.symbol_id = 118");
					$upd_bond->execute([$new_cd_code, $old_cd_code, $cid]);

					$upd_bond_audit = $dbh->prepare("UPDATE bond_audits b SET b.cd_code = ? WHERE b.cd_code = ? AND b.cid_no = ? AND b.symbol_id = 118");
					$upd_bond_audit->execute([$new_cd_code, $old_cd_code, $cid]);

					$temp_upd = $dbh->prepare("UPDATE bond_ipo_temp_dtls b SET b.cd_code = ? WHERE b.cd_code = ? AND b.name = ? AND b.symbol_id = 118");
					$temp_upd->execute([$new_cd_code, $old_cd_code, $cid]);

					$temp_upd_audit = $dbh->prepare("UPDATE bond_ipo_temp_dtls_audits b SET b.cd_code = ? WHERE b.cd_code = ? AND b.name = ? AND b.symbol_id = 118");
					$temp_upd_audit->execute([$new_cd_code, $old_cd_code, $cid]);
				}
				$count ++;
			}
		}
		$dbh->commit();
    } catch (Exception $e) {
    	$dbh->rollBack();
    	echo'exception ===>> ' . $e->getMessage();
    }

?>