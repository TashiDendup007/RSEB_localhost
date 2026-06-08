<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('./CONNECTIONS/db.php'); 
    include ('./CONNECTIONS/db1.php');

    $holdingbackup = $dbh->prepare('SELECT cds_holding_id, cd_code, symbol_id, volume, pledge_volume, block_volume, pending_in_vol, pending_out_vol, user_name, institution_id, remarks, flag FROM cds_holding WHERE (volume + pledge_volume + block_volume + pending_in_vol + pending_out_vol) > 0');
    $holdingbackup->execute();

    $insertQuery = $dbh1->prepare("INSERT INTO cds_holding_bckup (cds_holding_id, cd_code, symbol_id, volume, pledge_volume, block_volume, pending_in_vol, pending_out_vol, user_name, institution_id, remarks, flag) VALUES (:hid, :cd, :sid, :v, :pl, :bl, :pin, :pout, :un, :ii, :r, :f)");

    while ($row = $holdingbackup->fetch(PDO::FETCH_ASSOC)) {
        $insertQuery->execute([
            ':hid' => $row['cds_holding_id'],
            ':cd' => $row['cd_code'],
            ':sid' => $row['symbol_id'],
            ':v' => $row['volume'],
            ':pl' => $row['pledge_volume'],
            ':bl' => $row['block_volume'],
            ':pin' => $row['pending_in_vol'],
            ':pout' => $row['pending_out_vol'],
            ':un' => $row['user_name'],
            ':ii' => $row['institution_id'],
            ':r' => $row['remarks'],
            ':f' => $row['flag']
        ]);
    }

?>