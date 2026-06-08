<?php
    include('../../CONNECTIONS/db.php');

    $symbol_id_post = $_POST['symbol_id'];
    $announcement_type = $_POST['announcement_type'];

    $all = $dbh->prepare("SELECT a.client_id, a.cd_code, a.allocated_size, a.user_name, a.symbol_id, a.order_id, a.type, a.renounce_cd_code, b.institution_id, b.ID 
                        FROM rights_issue a 
                        JOIN client_account b ON a.client_id = b.client_id
                        WHERE symbol_id = ? 
                        AND allocated_size > 0
                        ORDER BY a.cd_code DESC
            ");
    $all->bindParam(1, $symbol_id_post);
    $all->execute();
    $n = 0;
    $vol_migrated = 0;
    foreach ($all as $srb) {
        
        $order_id = $srb['order_id'];
        $allocated = $srb['allocated_size'];
        $username = $srb['user_name'];
        $institution_id = $srb['institution_id'];
        $symbol_id = $srb['symbol_id'];
        $remark = '';
        $cd_code = '';

        if ($srb['type'] != 'R') {
            $cd_code = $srb['cd_code'];
        } else {
            $cd_code = $srb['renounce_cd_code'];
        }

        $allcds = $dbh->prepare("SELECT cd_code, volume, symbol_id FROM cds_holding WHERE cd_code = :cdCode AND symbol_id = :sym_id");
        $allcds->bindParam(':cdCode', $cd_code);
        $allcds->bindParam(':sym_id', $symbol_id);
        $allcds->execute();
        $ree = $allcds->fetch();

        if ($ree) {
            $updateQuery = "UPDATE cds_holding SET temporary_volume = temporary_volume + :temp_vol WHERE cd_code  = :cdCode AND symbol_id = :sym_id";
            $updateStmt = $dbh->prepare($updateQuery);
            $updateStmt->bindParam(':temp_vol', $allocated);
            $updateStmt->bindParam(':cdCode', $cd_code);
            $updateStmt->bindParam(':sym_id', $symbol_id);
            $updateStmt->execute();
        } else {
            $remark = 'Record first entered via Right issue, ' . $allocated . ' number of shares';

            $insertQuery = "INSERT INTO cds_holding(cd_code, temporary_volume, user_name, institution_id, symbol_id, remarks) VALUES(:cd_code, :allocated, :username, :institution_id, :symbol_id, :remark)";
            $insertStmt = $dbh->prepare($insertQuery);
            $insertStmt->bindParam(':cd_code', $cd_code);
            $insertStmt->bindParam(':allocated', $allocated);
            $insertStmt->bindParam(':username', $username);
            $insertStmt->bindParam(':institution_id', $institution_id);
            $insertStmt->bindParam(':symbol_id', $symbol_id);
            $insertStmt->bindParam(':remark', $remark);
            $insertStmt->execute();
        }

        $updateri = $dbh->prepare("UPDATE rights_issue SET status = 1 WHERE symbol_id = ? AND order_id = ?");
        $updateri->bindParam(1, $symbol_id);
        $updateri->bindParam(2, $order_id);
        $updateri->execute();
        
        echo $n++ . " : Yes done la</br>";
        $vol_migrated = $vol_migrated + $allocated;

    }
    echo $vol_migrated;
?>