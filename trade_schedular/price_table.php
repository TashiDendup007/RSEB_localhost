<?php  
// define database related variables
date_default_timezone_set("Asia/Thimphu");
include "../../CONNECTIONS/db.php";
include "f.php";

$ins_id_mcams = 230822044455;
//price discovery start
$deleting_record = $dbh->prepare("DELETE FROM price_table");
if ($deleting_record->execute()) {
    $sell = $dbh->prepare("SELECT DISTINCT symbol_id FROM orders");
    $sell->execute();
    foreach ($sell as $value) {
        $sym_id = $value["symbol_id"];
        $sell = $dbh->prepare(
            "SELECT sum(sell_vol) AS total FROM orders WHERE symbol_id=:sy"
        );
        $sell->bindParam(":sy", $sym_id);
        $sell->execute();
        $s = $sell->fetch();
        $stotal = $s["total"];

        $buy = $dbh->prepare(
            "SELECT sum(buy_vol) AS total FROM orders WHERE symbol_id=:sy"
        );
        $buy->bindParam(":sy", $sym_id);
        $buy->execute();
        $b = $buy->fetch();
        $btotal = $b["total"];
        //sell price entry

        $q2 = $dbh->prepare(
            'SELECT price, symbol_id FROM orders WHERE side="S" and symbol_id=:sy'
        );
        $q2->bindParam(":sy", $sym_id);
        $q2->execute();
        while ($row = $q2->fetch()) {
            $a = $row["price"];
            $s = $row["symbol_id"];
            $q222 = $dbh->prepare(
                "INSERT into price_table (prices,symbol_id) VALUES ('$a','$s')"
            );
            $q222->execute();
        }
        //buy price entry
        $q2 = $dbh->prepare(
            'SELECT  price,symbol_id FROM orders where side="B" and symbol_id=:sy'
        );
        $q2->bindParam(":sy", $sym_id);
        $q2->execute();
        while ($row = $q2->fetch()) {
            $a = $row["price"];
            $s = $row["symbol_id"];

            $q222 = $dbh->prepare(
                "INSERT INTO price_table (prices, symbol_id) VALUES (?, ?)"
            );
            $q222->execute([$a, $s]);
        }
        //sell lot entry
        $q2 = $dbh->prepare(
            "SELECT prices FROM price_table WHERE symbol_id = :sy"
        );
        $q2->bindParam(":sy", $sym_id);
        $q2->execute();
        foreach ($q2 as $value) {
            $p = $value["prices"];
            $q2 = $dbh->prepare(
                'SELECT sum(sell_vol) as su from orders where price > :p and symbol_id=:sy and side="S"'
            );
            $q2->bindParam(":sy", $sym_id);
            $q2->bindParam(":p", $p);
            $q2->execute();
            $se = $q2->fetch();
            $su = $se["su"];
            $vs = $stotal - $su;

            $q222 = $dbh->prepare(
                "UPDATE price_table set volume_sell=:vs where prices =:p and symbol_id=:sy"
            );
            $q222->bindParam(":sy", $sym_id);
            $q222->bindParam(":vs", $vs);
            $q222->bindParam(":p", $p);
            $q222->execute();
        }
        //buy lot entry
        $q2 = $dbh->prepare(
            "SELECT  prices from price_table where symbol_id=:sy"
        );
        $q2->bindParam(":sy", $sym_id);
        $q2->execute();
        foreach ($q2 as $value) {
            $p = $value["prices"];
            $q2 = $dbh->prepare(
                'SELECT sum(buy_vol) as su from orders where price < :p and symbol_id=:sy and side="B"'
            );
            $q2->bindParam(":sy", $sym_id);
            $q2->bindParam(":p", $p);
            $q2->execute();
            $se = $q2->fetch();
            $su = $se["su"];
            $vs = $btotal - $su;
            $q222 = $dbh->prepare(
                "UPDATE price_table set volume_buy=:vs where prices =:p and symbol_id=:sy"
            );
            $q222->bindParam(":sy", $sym_id);
            $q222->bindParam(":vs", $vs);
            $q222->bindParam(":p", $p);
            $q222->execute();
        }
        //finding the difference
        $sys = $dbh->prepare("SELECT distinct  symbol_id from price_table");
        $sys->execute();
        foreach ($sys as $value) {
            $sym_id = $value["symbol_id"];
            $q2 = $dbh->prepare(
                "SELECT  prices from price_table where symbol_id=:sy "
            );
            $q2->bindParam(":sy", $sym_id);
            $q2->execute();
            foreach ($q2 as $value) {
                $p = $value["prices"];
                $q2 = $dbh->prepare(
                    "SELECT volume_buy,volume_sell from price_table where prices = :p and symbol_id=:sy "
                );
                $q2->bindParam(":sy", $sym_id);
                $q2->bindParam(":p", $p);
                $q2->execute();
                $se = $q2->fetch();
                $a = $se["volume_buy"];
                $b = $se["volume_sell"];

                if ($a > $b) {
                    $l = $b;
                } elseif ($a < $b) {
                    $l = $a;
                } else {
                    $l = $a;
                }
                $q222 = $dbh->prepare(
                    "UPDATE price_table set difference=:l, diff_chk=:l where prices =:p and symbol_id=:sy"
                );
                $q222->bindParam(":sy", $sym_id);
                $q222->bindParam(":l", $l);
                $q222->bindParam(":p", $p);
                $q222->execute();
            }
        }
    }
}

?>