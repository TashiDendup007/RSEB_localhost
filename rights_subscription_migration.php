<?php

die();

include 'CONNECTIONS/db.php';
/*echo "start".time();*/

$all = $dbh->prepare('SELECT a.*,b.*
                    FROM rights_issue a, client_account b
                    where symbol_id=63
                   -- and  status=1
                    and allocated_size > 0
                    and a.cd_code=b.cd_code
                    order by a.cd_code desc -- LIMIT 7100');
$all->execute();
$n = 0;
$vol_migrated = 0;
foreach ($all as $srb) {
    if ($srb['type'] != 'R') {
        $allcds = $dbh->prepare('SELECT c.cd_code,c.volume,c.symbol_id from cds_holding c where c.cd_code=:cd and c.symbol_id=:sid');
        $allcds->bindParam(':cd', $srb['cd_code']);
        $allcds->bindParam(':sid', $srb['symbol_id']);
        $allcds->execute();
        $ree = $allcds->fetch();

        $cd_code = $srb['cd_code'];
        $allocated = $srb['allocated_size'];
        $username = $srb['user_name'];
        $institution_id = $srb['institution_id'];
        $symbol_id = $srb['symbol_id'];
        $type = 'Record First entered via Right issue, ' . $allocated . ' number of shares';

        if ($allcds->rowCount() > 0) {
            $save = $dbh->prepare("UPDATE cds_holding SET temporary_volume=temporary_volume+:temporary_volume  where cd_code=:cd_code and symbol_id=:sym_id ");
            $save->bindParam(':temporary_volume', $allocated);
            $save->bindParam(':cd_code', $cd_code);
            $save->bindParam(':sym_id', $symbol_id);
            $save->execute();
        } else {
            $save = $dbh->prepare("INSERT into cds_holding(cd_code,temporary_volume,user_name,institution_id,symbol_id,remarks)VALUES
               ('$cd_code','$allocated','$username','$institution_id','$symbol_id','$type')");
            $save->execute();
        }

        $updateri = $dbh->prepare("UPDATE rights_issue SET status=1 where symbol_id=:sym_id and order_id=:order ");
        $updateri->bindParam(':sym_id', $symbol_id);
        $updateri->bindParam(':order', $srb['order_id']);
        $updateri->execute();
        echo $n++ . " : Yes done la</br>";
        $vol_migrated = $vol_migrated + $srb['allocated_size'];
    } else if ($srb['type'] == 'R') {
        $allcds = $dbh->prepare('SELECT c.cd_code,c.volume,c.symbol_id from cds_holding c where c.cd_code=:cd and c.symbol_id=:sid');
        $allcds->bindParam(':cd', $srb['renounce_cd_code']);
        $allcds->bindParam(':sid', $srb['symbol_id']);
        $allcds->execute();
        $ree = $allcds->fetch();

        $renounce_cd_code = $srb['renounce_cd_code'];
        $allocated = $srb['allocated_size'];
        $username = $srb['user_name'];
        $institution_id = $srb['institution_id'];
        $symbol_id = $srb['symbol_id'];
        $type = 'Record First entered via Right issue, ' . $allocated . ' number of shares';

        if ($allcds->rowCount() > 0) {
            $save = $dbh->prepare("UPDATE cds_holding SET temporary_volume=temporary_volume+:temporary_volume  where cd_code=:cd_code and symbol_id=:sym_id ");
            $save->bindParam(':temporary_volume', $allocated);
            $save->bindParam(':cd_code', $renounce_cd_code);
            $save->bindParam(':sym_id', $symbol_id);
            $save->execute();
        } else {
            $save = $dbh->prepare("INSERT into cds_holding(cd_code,temporary_volume,user_name,institution_id,symbol_id,remarks)VALUES
               ('$renounce_cd_code','$allocated','$username','$institution_id','$symbol_id','$type')");
            $save->execute();
        }

        $updateri = $dbh->prepare("UPDATE rights_issue SET status=1 where symbol_id=:sym_id and order_id=:order ");
        $updateri->bindParam(':sym_id', $symbol_id);
        $updateri->bindParam(':order', $srb['order_id']);
        $updateri->execute();
        echo $n++ . " : Yes done la</br>";
        $vol_migrated = $vol_migrated + $srb['allocated_size'];

    } else {

    }

}
echo $vol_migrated;
?>