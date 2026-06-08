<?php
// define database related variables
date_default_timezone_set("Asia/Thimphu");
include "../../CONNECTIONS/db.php";
include "f.php";

$ins_id_mcams = 230822044455;
//price discovery start
$deleting_record = $dbh->prepare("DELETE FROM price_table");
if ($deleting_record->execute()) {
    $sell = $dbh->prepare("SELECT DISTINCT symbol_id FROM orders ");
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
                "INSERT into price_table (prices,symbol_id) VALUES ('$a','$s')"
            );
            $q222->execute();
        }
        //sell lot entry
        $q2 = $dbh->prepare(
            "SELECT  prices from price_table where symbol_id=:sy"
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
                    "UPDATE price_table set difference=:l,diff_chk=:l where prices =:p and symbol_id=:sy"
                );
                $q222->bindParam(":sy", $sym_id);
                $q222->bindParam(":l", $l);
                $q222->bindParam(":p", $p);
                $q222->execute();
            }
        }
    }
}
//1st ifloop  end
else {
    echo "PRICES COULD NOT BE DISCOVERED";
}
//price discovery ends

$q222 = $dbh->prepare("SELECT DISTINCT symbol_id FROM price_table");
$q222->execute();
foreach ($q222 as $value) {
    $sym_id = $value["symbol_id"];
    $q222 = $dbh->prepare(
        "SELECT * from price_table where prices=(SELECT max(prices) FROM price_table where symbol_id=:sym_id and difference=(select max(difference) FROM price_table WHERE symbol_id=:sym_id) ) and symbol_id=:sym_id"
    );
    $q222->bindParam(":sym_id", $sym_id);
    $q222->execute();
    $value = $q222->fetch();
    $op = $value["prices"];
    $diff = $value["difference"];
    $sym_id = $value["symbol_id"];
    $vb = $value["volume_buy"];
    $vs = $value["volume_sell"];
    $pid = $value["pid"];
    $diff_chk = $value["diff_chk"];
    // echo $diff."---price--".$op."---".$sym_id."--machable--number of row<br>------------<br>";
    if ($vb == $vs && $vb == $diff && $vs == $diff) {
        $pr = $dbh->prepare(
            'SELECT * from orders WHERE price <= :op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol  DESC'
        );
        $pr->bindParam(":op", $op);
        $pr->bindParam(":sym_id", $sym_id);
        $pr->execute();
        foreach ($pr as $value) {
            $oidd = $value["order_id"];
            $sell_vol = $value["sell_vol"];
            $n = 0;
            $v = $sell_vol;
            $update_sell_lot = $dbh->prepare(
                "UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
            );
            $update_sell_lot->bindParam(":oidd", $oidd);
            $update_sell_lot->bindParam(":n", $n);
            $update_sell_lot->bindParam(":op", $op);
            $update_sell_lot->bindParam(":v", $v);
            $update_sell_lot->execute();
        }
        $pr = $dbh->prepare(
            'SELECT * from orders WHERE price >=:op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC'
        );
        $pr->bindParam(":op", $op);
        $pr->bindParam(":sym_id", $sym_id);
        $pr->execute();
        foreach ($pr as $value) {
            $oiddb = $value["order_id"];
            $buy_vol = $value["buy_vol"];
            $n = 0;
            $v = $buy_vol;
            $update_buy_lot = $dbh->prepare(
                "UPDATE orders SET exe_vol=:v,buy_vol=:n,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
            );
            $update_buy_lot->bindParam(":oidd", $oiddb);
            $update_buy_lot->bindParam(":n", $n);
            $update_buy_lot->bindParam(":op", $op);
            $update_buy_lot->bindParam(":v", $v);
            $update_buy_lot->execute();
        }
    }
    //elseif X
    elseif ($vb < $vs && $vb == $diff) {
        $diff_chk = compare($pid);
        for ($x = $diff_chk; $x > 0; ) {
            $diff_chk = compare($pid);
            $rowcountsell = rowcountsell($op, $sym_id);
            $pr = $dbh->prepare(
                'SELECT * from orders WHERE price <= :op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol DESC'
            );
            $pr->bindParam(":op", $op);
            $pr->bindParam(":sym_id", $sym_id);
            $pr->execute();
            $allocation = floor($diff_chk / $rowcountsell);
            foreach ($pr as $value) {
                $oidd = $value["order_id"];
                $sell_vol = $value["sell_vol"];
                $exe_vol = $value["exe_vol"];
                if ($sell_vol == $allocation) {
                    $exe_vol = exe_vol($oidd);
                    $n = $sell_vol - $allocation;
                    $v = $exe_vol + $allocation;
                    $update_sell_lot = $dbh->prepare(
                        "UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                    );
                    $update_sell_lot->bindParam(":oidd", $oidd);
                    $update_sell_lot->bindParam(":n", $n);
                    $update_sell_lot->bindParam(":op", $op);
                    $update_sell_lot->bindParam(":v", $v);
                    $update_sell_lot->execute();
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    $update_diff_chk = $dbh->prepare(
                        "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                    );
                    $update_diff_chk->bindParam(":pid", $pid);
                    $update_diff_chk->bindParam(":dif", $diff_new);
                    $update_diff_chk->execute();
                } elseif ($sell_vol < $allocation) {
                    $exe_vol = exe_vol($oidd);
                    $n = $sell_vol - $sell_vol;
                    $v = $exe_vol + $sell_vol;
                    $update_sell_lot = $dbh->prepare(
                        "UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                    );
                    $update_sell_lot->bindParam(":oidd", $oidd);
                    $update_sell_lot->bindParam(":n", $n);
                    $update_sell_lot->bindParam(":op", $op);
                    $update_sell_lot->bindParam(":v", $v);
                    $update_sell_lot->execute();
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $sell_vol;
                    $update_diff_chk = $dbh->prepare(
                        "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                    );
                    $update_diff_chk->bindParam(":pid", $pid);
                    $update_diff_chk->bindParam(":dif", $diff_new);
                    $update_diff_chk->execute();
                } elseif ($sell_vol > $allocation && $allocation > 0) {
                    $exe_vol = exe_vol($oidd);
                    $n = $sell_vol - $allocation;
                    $v = $exe_vol + $allocation;
                    $update_sell_lot = $dbh->prepare(
                        "UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                    );
                    $update_sell_lot->bindParam(":oidd", $oidd);
                    $update_sell_lot->bindParam(":n", $n);
                    $update_sell_lot->bindParam(":op", $op);
                    $update_sell_lot->bindParam(":v", $v);
                    $update_sell_lot->execute();
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    $update_diff_chk = $dbh->prepare(
                        "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                    );
                    $update_diff_chk->bindParam(":pid", $pid);
                    $update_diff_chk->bindParam(":dif", $diff_new);
                    $update_diff_chk->execute();
                } elseif ($allocation == 0 && $diff_chk < $rowcountsell) {
                    $diff_chk = compare($pid);
                    if ($diff_chk == 0) {
                    } else {
                        $exe_vol = exe_vol($oidd);
                        $n = $sell_vol - 1;
                        $v = $exe_vol + 1;
                        $update_sell_lot = $dbh->prepare(
                            "UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                        );
                        $update_sell_lot->bindParam(":oidd", $oidd);
                        $update_sell_lot->bindParam(":n", $n);
                        $update_sell_lot->bindParam(":op", $op);
                        $update_sell_lot->bindParam(":v", $v);
                        $update_sell_lot->execute();
                        $diff_chk = compare($pid);
                        $diff_new = $diff_chk - 1;
                        $update_diff_chk = $dbh->prepare(
                            "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                        );
                        $update_diff_chk->bindParam(":pid", $pid);
                        $update_diff_chk->bindParam(":dif", $diff_new);
                        $update_diff_chk->execute();
                    }
                }
            }
            $x = compare($pid);
        }
        $pr = $dbh->prepare(
            'SELECT * from orders WHERE price >=:op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC'
        );
        $pr->bindParam(":op", $op);
        $pr->bindParam(":sym_id", $sym_id);
        $pr->execute();
        foreach ($pr as $value) {
            $oiddb = $value["order_id"];
            $buy_vol = $value["buy_vol"];
            $n = 0;
            $v = $buy_vol;
            $update_buy_lot = $dbh->prepare(
                "UPDATE orders SET exe_vol=:v,buy_vol=:n,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
            );
            $update_buy_lot->bindParam(":oidd", $oiddb);
            $update_buy_lot->bindParam(":n", $n);
            $update_buy_lot->bindParam(":op", $op);
            $update_buy_lot->bindParam(":v", $v);
            $update_buy_lot->execute();
        }
    }
    //elseif X
    elseif ($vs < $vb && $vs == $diff) {
        $diff_chk = compare($pid);
        for ($x = $diff_chk; $x > 0; ) {
            $diff_chk = compare($pid);
            $rowcountbuy = rowcountbuy($op, $sym_id);
            $pr = $dbh->prepare(
                'SELECT * from orders WHERE price >= :op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC'
            );
            $pr->bindParam(":op", $op);
            $pr->bindParam(":sym_id", $sym_id);
            $pr->execute();
            $allocation = floor($diff_chk / $rowcountbuy);
            foreach ($pr as $value) {
                $oidd = $value["order_id"];
                $buy_vol = $value["buy_vol"];
                $exe_vol = $value["exe_vol"];
                if ($buy_vol == $allocation) {
                    $exe_vol = exe_vol_b($oidd);
                    $n = $buy_vol - $allocation;
                    $v = $exe_vol + $allocation;
                    $update_sell_lot = $dbh->prepare(
                        "UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                    );
                    $update_sell_lot->bindParam(":oidd", $oidd);
                    $update_sell_lot->bindParam(":n", $n);
                    $update_sell_lot->bindParam(":op", $op);
                    $update_sell_lot->bindParam(":v", $v);
                    $update_sell_lot->execute();
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    $update_diff_chk = $dbh->prepare(
                        "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                    );
                    $update_diff_chk->bindParam(":pid", $pid);
                    $update_diff_chk->bindParam(":dif", $diff_new);
                    $update_diff_chk->execute();
                } elseif ($buy_vol < $allocation) {
                    $exe_vol = exe_vol_b($oidd);
                    $n = $buy_vol - $buy_vol;
                    $v = $exe_vol + $buy_vol;
                    $update_sell_lot = $dbh->prepare(
                        "UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                    );
                    $update_sell_lot->bindParam(":oidd", $oidd);
                    $update_sell_lot->bindParam(":n", $n);
                    $update_sell_lot->bindParam(":op", $op);
                    $update_sell_lot->bindParam(":v", $v);
                    $update_sell_lot->execute();
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $buy_vol;
                    $update_diff_chk = $dbh->prepare(
                        "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                    );
                    $update_diff_chk->bindParam(":pid", $pid);
                    $update_diff_chk->bindParam(":dif", $diff_new);
                    $update_diff_chk->execute();
                } elseif ($buy_vol > $allocation && $allocation > 0) {
                    $exe_vol = exe_vol_b($oidd);
                    $n = $buy_vol - $allocation;
                    $v = $exe_vol + $allocation;
                    $update_sell_lot = $dbh->prepare(
                        "UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                    );
                    $update_sell_lot->bindParam(":oidd", $oidd);
                    $update_sell_lot->bindParam(":n", $n);
                    $update_sell_lot->bindParam(":op", $op);
                    $update_sell_lot->bindParam(":v", $v);
                    $update_sell_lot->execute();
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    $update_diff_chk = $dbh->prepare(
                        "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                    );
                    $update_diff_chk->bindParam(":pid", $pid);
                    $update_diff_chk->bindParam(":dif", $diff_new);
                    $update_diff_chk->execute();
                } elseif ($allocation == 0 && $diff_chk < $rowcountbuy) {
                    $diff_chk = compare($pid);
                    if ($diff_chk == 0) {
                    } else {
                        $exe_vol = exe_vol_b($oidd);
                        $n = $buy_vol - 1;
                        $v = $exe_vol + 1;
                        $update_sell_lot = $dbh->prepare(
                            "UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
                        );
                        $update_sell_lot->bindParam(":oidd", $oidd);
                        $update_sell_lot->bindParam(":n", $n);
                        $update_sell_lot->bindParam(":op", $op);
                        $update_sell_lot->bindParam(":v", $v);
                        $update_sell_lot->execute();
                        $diff_chk = compare($pid);
                        $diff_new = $diff_chk - 1;
                        $update_diff_chk = $dbh->prepare(
                            "UPDATE price_table SET diff_chk=:dif where pid=:pid"
                        );
                        $update_diff_chk->bindParam(":pid", $pid);
                        $update_diff_chk->bindParam(":dif", $diff_new);
                        $update_diff_chk->execute();
                    }
                }
            }
            $x = compare($pid);
        }
        $pr = $dbh->prepare(
            'SELECT * from orders WHERE price <=:op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol DESC'
        );
        $pr->bindParam(":op", $op);
        $pr->bindParam(":sym_id", $sym_id);
        $pr->execute();
        foreach ($pr as $value) {
            $oiddb = $value["order_id"];
            $sell_vol = $value["sell_vol"];
            $n = 0;
            $v = $sell_vol;
            $update_buy_lot = $dbh->prepare(
                "UPDATE orders SET exe_vol=:v,sell_vol=:n,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd"
            );
            $update_buy_lot->bindParam(":oidd", $oiddb);
            $update_buy_lot->bindParam(":n", $n);
            $update_buy_lot->bindParam(":op", $op);
            $update_buy_lot->bindParam(":v", $v);
            $update_buy_lot->execute();
        }
    }
    //allocation end
    //echo "Allocation Completed";
}
//updating executed orders table
/*$q222 = $dbh->prepare('SELECT a.*, b.institution_id,c.rate from orders a, adm_participants b , bbo_commission c,client_account ca 
                       where a.participant_code=b.participant_code and a.cd_code=ca.cd_code and c.bro_comm_id=ca.bro_comm_id and exe_vol > 0');*/

$q222 = $dbh->prepare("
    SELECT a.order_id, a.price, a.participant_code, a.order_entry, a.cd_code, a.exe_price, a.exe_vol, a.flag_id, a.member_broker, a.symbol_id, a.side, b.institution_id, c.rate 
    FROM orders a
    JOIN adm_participants b ON a.participant_code = b.participant_code
    JOIN client_account ca ON a.cd_code = ca.cd_code
    JOIN bbo_commission c ON c.bro_comm_id = ca.bro_comm_id
    WHERE exe_vol > 0
");
$q222->execute();
foreach ($q222 as $value) {
    $oidd = $value["order_id"];
    $price = $value["price"];
    $p_code = $value["participant_code"];
    $order_entry = $value["order_entry"];
    $cd_code = $value["cd_code"];
    $order_exe_price = $value["exe_price"];
    $order_executed_time = date("Y-m-d H:i:s");
    $lot_size_execute = $value["exe_vol"];
    $pending_in_vol = $value["exe_vol"];
    $username = $value["order_entry"];
    $institution_id = $value["institution_id"];
    $flag_id = $value["flag_id"];
    $member_broker = $value["member_broker"];
    $status = 0;
    $sym_id = $value["symbol_id"];
    $side = $value["side"];
    $s = "S";
    $b = "B";
    $b_commis = $value["rate"];
    $new_exe_amt = $order_exe_price * $lot_size_execute;
    
    // echo "it reached here";
    $amt = ($order_exe_price * $lot_size_execute * $b_commis) / 100;
    $commis_fee = round($amt, 2);
    $gst_fee = round($commis_fee * 0.05, 2);

    /*$b_commis=client_commission($cd_code);*/
    /*$executed_orders = $dbh->prepare("INSERT INTO executed_orders(member_broker,cd_code,order_exe_price,order_date,lot_size_execute,status,symbol_id,side,lot_check,order_id,participant_code,sub_user) 
        VALUES ('$member_broker','$cd_code','$order_exe_price','$order_executed_time','$lot_size_execute','$status','$sym_id','$side','$lot_size_execute','$oidd','$p_code','$order_entry')");
    $executed_orders->execute()*/

    $executed_orders = $dbh->prepare("
        INSERT INTO executed_orders (member_broker, cd_code, order_exe_price, order_date, lot_size_execute, status, symbol_id, side, lot_check, order_id, participant_code, sub_user)
        VALUES (:member_broker, :cd_code, :order_exe_price, :order_date, :lot_size_execute, :status, :symbol_id, :side, :lot_check, :order_id, :participant_code, :sub_user)
    ");
    $result = $executed_orders->execute([
        ':member_broker'     => $member_broker,
        ':cd_code'           => $cd_code,
        ':order_exe_price'   => $order_exe_price,
        ':order_date'        => $order_executed_time,
        ':lot_size_execute'  => $lot_size_execute,
        ':status'            => $status,
        ':symbol_id'         => $sym_id,
        ':side'              => $side,
        ':lot_check'         => $lot_size_execute,
        ':order_id'          => $oidd,
        ':participant_code'  => $p_code,
        ':sub_user'          => $order_entry,
    ]);

    if ($result) {
        // echo "...and here";
        $update_orders = $dbh->prepare(
            "UPDATE orders SET exe_vol=0,exe_price=0 where order_id=:oidd"
        );
        $update_orders->bindParam(":oidd", $oidd);
        $update_orders->execute();
        if ($side === $s) {
            $new_exe_amt = $new_exe_amt;
            $finding_p_o = $dbh->prepare(
                "SELECT * FROM cds_holding WHERE cd_code=:cd_code and symbol_id=:sym_id and volume > 0"
            );
            $finding_p_o->bindParam(":sym_id", $sym_id);
            $finding_p_o->bindParam(":cd_code", $cd_code);
            $finding_p_o->execute();
            foreach ($finding_p_o as $value) {
                $lo_check_q = $dbh->prepare(
                    "SELECT lot_check from orders where order_id=:oidd "
                );
                $lo_check_q->bindParam(":oidd", $oidd);
                $lo_check_q->execute();
                $loo_check_q = $lo_check_q->fetch();
                $lot_check = $loo_check_q["lot_check"];
                $exe_vol_new = $lot_check;
                $coid = $value["cds_holding_id"];
                $existing_pending_out_lot = $value["pending_out_vol"];
                $sum = $existing_pending_out_lot + $lot_check;
                $pending_out_vol = $value["volume"];

                if ($exe_vol_new >= $value["volume"]) {
                    $new_block = $value["volume"] - $existing_pending_out_lot;
                    $exe_vol_new_s = $exe_vol_new - $new_block;
                    $update_lot_check = $dbh->prepare(
                        "UPDATE orders SET lot_check=:exe_vol_new_s where order_id=:oidd "
                    );
                    $update_lot_check->bindParam(
                        ":exe_vol_new_s",
                        $exe_vol_new_s
                    );
                    $update_lot_check->bindParam(":oidd", $oidd);
                    $update_lot_check->execute();
                } elseif ($exe_vol_new < $value["volume"] && $exe_vol_new > 0) {
                    if ($value["volume"] >= $sum) {
                        $exe_vol_new_l = 0;
                        $update_lot_check = $dbh->prepare(
                            "UPDATE orders SET lot_check=:exe_vol_new_l where order_id=:oidd "
                        );
                        $update_lot_check->bindParam(
                            ":exe_vol_new_l",
                            $exe_vol_new_l
                        );
                        $update_lot_check->bindParam(":oidd", $oidd);
                        $update_lot_check->execute();
                    } elseif ($value["volume"] < $sum) {
                        $new_block_s =
                            $value["volume"] - $existing_pending_out_lot;
                        $exe_vol_new_m = $exe_vol_new - $new_block_s;
                        $update_lot_check = $dbh->prepare(
                            "UPDATE orders SET lot_check=:exe_vol_new_m where order_id=:oidd "
                        );
                        $update_lot_check->bindParam(
                            ":exe_vol_new_m",
                            $exe_vol_new_m
                        );
                        $update_lot_check->bindParam(":oidd", $oidd);
                        $update_lot_check->execute();
                    }
                } else {
                    echo "no more";
                }
            }
            $update_finance = $dbh->prepare(
                "UPDATE bbo_finance SET amount=:new_exe_amt where flag_id=:flag_id "
            );
            $update_finance->bindParam(":new_exe_amt", $new_exe_amt);
            $update_finance->bindParam(":flag_id", $flag_id);
            $update_finance->execute();

            $flag_sell = 2;
            $finsellstatus = 0;
            $remarks_sell = "Amount for selling " . $lot_size_execute . " share @ Nu." . $order_exe_price;

            $b_fin = $dbh->prepare("INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id, status) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
            $b_fin->execute([
                $cd_code, $new_exe_amt, $member_broker, $remarks_sell, $flag_sell, $institution_id, $oidd, $finsellstatus
            ]);

            //commission for the seller start
            $flag = 4;
            $remarks = "Commission for the trade of " . $lot_size_execute . " share @ Nu." . $order_exe_price;

            $gst_flag = 5;
            $gst_remarks = "GST fee for the trade of " . $lot_size_execute . " share @ Nu. " . $order_exe_price;

            /*$list= ins_id($member_broker);
             $ins_id=$list[0];$p_code=$list[1];*/

            $finsellstatuscomm = 0;
            /*$b_fin = $dbh->prepare("INSERT into bbo_finance(cd_code,amount,user_name,remarks,flag,institution_id,flag_id,status) 
                VALUES ('$cd_code','-$amt','$member_broker','$remarks','$flag','$institution_id','$oidd','$finsellstatuscomm')");
            $b_fin->execute();*/

            $stmt_b_fin = $dbh->prepare("
                INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id, status)
                VALUES 
                (:cd_code1, :amount1, :user_name1, :remarks1, :flag1, :institution_id1, :flag_id1, :status1),
                (:cd_code2, :amount2, :user_name2, :remarks2, :flag2, :institution_id2, :flag_id2, :status2)
            ");

            $stmt_b_fin->execute([
                ':cd_code1'        => $cd_code,
                ':amount1'         => -abs($commis_fee),
                ':user_name1'      => $member_broker,
                ':remarks1'        => $remarks,
                ':flag1'           => $flag,
                ':institution_id1' => $institution_id,
                ':flag_id1'        => $oidd,
                ':status1'         => $finsellstatuscomm,

                ':cd_code2'        => $cd_code,
                ':amount2'         => -abs($gst_fee),
                ':user_name2'      => $member_broker,
                ':remarks2'        => $gst_remarks,
                ':flag2'           => $gst_flag,
                ':institution_id2' => $institution_id,
                ':flag_id2'        => $oidd,
                ':status2'         => $finsellstatuscomm,
            ]);
            //commission for the seller end

            if ($institution_id == $ins_id_mcams) {
                //insert into mcams wallet
                /*$sql = "INSERT INTO mcams_wallet (cd_code, amount, type, paid_to_user, trx_time)
                        VALUES ('$cd_code', '-$amt', 'DR', 'COMMISSION', CURRENT_TIMESTAMP),
                            ('$cd_code', '$new_exe_amt', 'CR', 'SELL', CURRENT_TIMESTAMP)";
                $b_order = $dbh->prepare($sql);
                $b_order->execute();*/

                $stmt_bla = $dbh->prepare("
                    INSERT INTO mcams_wallet (cd_code, amount, type, paid_to_user, trx_time)
                    VALUES 
                    (:cd_code_comm, :amount_comm, :type_comm, :paid_to_user_comm, CURRENT_TIMESTAMP),
                    (:cd_code_sell, :amount_sell, :type_sell, :paid_to_user_sell, CURRENT_TIMESTAMP),
                    (:cd_code_gst, :gst_fee, :type_gst, :paid_to_user_gst, CURRENT_TIMESTAMP)
                ");
                $stmt_bla->execute([
                    ':cd_code_comm'     => $cd_code,
                    ':amount_comm'      => -abs($commis_fee),  // ensure negative for commission
                    ':type_comm'        => 'DR',
                    ':paid_to_user_comm'=> 'COMMISSION',
                    
                    ':cd_code_sell'     => $cd_code,
                    ':amount_sell'      => $new_exe_amt,      // positive amount for sell
                    ':type_sell'        => 'CR',
                    ':paid_to_user_sell'=> 'SELL',

                    ':cd_code_gst'     => $cd_code,
                    ':gst_fee'      => -abs($gst_fee),      // positive amount for sell
                    ':type_gst'        => 'DR',
                    ':paid_to_user_gst'=> 'GST',
                ]);
            }
        } elseif ($side === $b) {
            $new_exe_amt = $new_exe_amt * -1;
            $new_bbo_amt = $new_exe_amt * -1;
            $cds_client_check = $dbh->prepare(
                "SELECT * from cds_holding where cd_code=:cd_code and symbol_id=:sym_id"
            );
            $cds_client_check->bindParam(":cd_code", $cd_code);
            $cds_client_check->bindParam(":sym_id", $sym_id);
            $cds_client_check->execute();
            $res = $cds_client_check->fetch();

            $buyer_cd_code = isset($res["cd_code"]) ? $res["cd_code"] : '';
            $pending_in_vol_existing = isset($res["pending_in_vol"]) ? $res["pending_in_vol"] : 0;
            $pending_in_vol_new = $pending_in_vol_existing + $pending_in_vol;
            if ($buyer_cd_code === $cd_code) {
                //record update
                $save = $dbh->prepare(
                    "UPDATE cds_holding SET pending_in_vol=:pending_in_vol_new  where cd_code=:cd_code and symbol_id=:sym_id "
                );
                $save->bindParam(":pending_in_vol_new", $pending_in_vol_new);
                $save->bindParam(":cd_code", $cd_code);
                $save->bindParam(":sym_id", $sym_id);
                $save->execute();
            } else {
                //create new cds_holding entry for pending in
                $vol = 0;
                $type = "Record First entered via buy of, " . $pending_in_vol . " number of shares";
                $save = $dbh->prepare(
                    "INSERT INTO cds_holding(cd_code,volume,user_name,institution_id,symbol_id,remarks,pending_in_vol) VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $save->execute([$cd_code, $vol, $username, $institution_id, $sym_id, $type, $pending_in_vol]);
            }
            $update_finance = $dbh->prepare(
                "UPDATE bbo_finance SET amount=amount+:new_bbo_amt where flag_id=:flag_id "
            );
            $update_finance->bindParam(":new_bbo_amt", $new_bbo_amt);
            $update_finance->bindParam(":flag_id", $flag_id);
            $update_finance->execute();

            $flag_buy = 3;
            $remarks_buy = "Amount for buying" . $lot_size_execute . " share @ Nu." . $order_exe_price;
            $b_fin = $dbh->prepare(
                "INSERT into bbo_finance (cd_code,amount,user_name,remarks,flag,institution_id,flag_id) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $b_fin->execute([$cd_code, $new_exe_amt, $member_broker, $remarks_buy, $flag_buy, $institution_id, $oidd]);
            //commission for the buyer start
            $flag = 4;
            $remarks = "Commission for the trade of " . $lot_size_execute . " share @ Nu." . $order_exe_price;

            $gst_flag = 5;
            $gst_remarks = "GST fee for the trade of " . $lot_size_execute . " share @ Nu. " . $order_exe_price;
            
            /*$b_fin = $dbh->prepare("INSERT into bbo_finance(cd_code,amount,user_name,remarks,flag,institution_id,flag_id)
                                VALUES ('$cd_code','-$amt','$member_broker','$remarks','$flag','$institution_id','$oidd')");
            $b_fin->execute();*/

            $stmt_b_fin = $dbh->prepare("
                INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id)
                VALUES 
                (:cd_code1, :amount1, :user_name1, :remarks1, :flag1, :institution_id1, :flag_id1),
                (:cd_code2, :amount2, :user_name2, :remarks2, :flag2, :institution_id2, :flag_id2)
            ");

            $stmt_b_fin->execute([
                ':cd_code1'        => $cd_code,
                ':amount1'         => -abs($commis_fee),
                ':user_name1'      => $member_broker,
                ':remarks1'        => $remarks,
                ':flag1'           => $flag,
                ':institution_id1' => $institution_id,
                ':flag_id1'        => $oidd,

                ':cd_code2'        => $cd_code,
                ':amount2'         => -abs($gst_fee),
                ':user_name2'      => $member_broker,
                ':remarks2'        => $gst_remarks,
                ':flag2'           => $gst_flag,
                ':institution_id2' => $institution_id,
                ':flag_id2'        => $oidd,
            ]);
            //commission for the buyer end

            if ($institution_id == $ins_id_mcams) {
                //insert into mcams wallet
                /*$sql = "INSERT INTO mcams_wallet (cd_code, amount, type, paid_to_user, trx_time)
                        VALUES ('$cd_code', '-$amt', 'DR', 'COMMISSION', CURRENT_TIMESTAMP),
                              ('$cd_code', '$new_exe_amt', 'DR', 'BUY', CURRENT_TIMESTAMP)";
                $b_order = $dbh->prepare($sql);
                $b_order->execute();*/

                $stmt_bla = $dbh->prepare("
                    INSERT INTO mcams_wallet (cd_code, amount, type, paid_to_user, trx_time)
                    VALUES 
                    (:cd_code_comm, :amount_comm, :type_comm, :paid_to_user_comm, CURRENT_TIMESTAMP),
                    (:cd_code_buy, :amount_buy, :type_buy, :paid_to_user_buy, CURRENT_TIMESTAMP),
                    (:cd_code_gst, :gst_fee, :type_gst, :paid_to_user_gst, CURRENT_TIMESTAMP)
                ");
                $stmt_bla->execute([
                    ':cd_code_comm'     => $cd_code,
                    ':amount_comm'      => -abs($commis_fee),  // ensure negative for commission
                    ':type_comm'        => 'DR',
                    ':paid_to_user_comm'=> 'COMMISSION',
                    
                    ':cd_code_buy'     => $cd_code,
                    ':amount_buy'      => $new_exe_amt,      // positive amount for sell
                    ':type_buy'        => 'DR',
                    ':paid_to_user_buy'=> 'BUY',

                    ':cd_code_gst'     => $cd_code,
                    ':gst_fee'      => -abs($gst_fee),      // positive amount for sell
                    ':type_gst'        => 'DR',
                    ':paid_to_user_gst'=> 'GST',
                ]);
            }
        } else {
            echo "Message!! Something wrong with buy or sell order";
        }
    }
}
//price update market price start
$currentHour = date("H");

if ($currentHour == 15) {
    $dateselect = date("Y-m-d");

    $specifieddate = $dbh->prepare(
        'SELECT SUBSTRING(max(e.order_date),1,10) dat from executed_orders e where e.side="S" and e.order_date  like "%' .
            $dateselect .
            '%"'
    );
    $specifieddate->execute();
    $spdate = $specifieddate->fetch();
    $conditiondate = $spdate["dat"];

    if (is_null($conditiondate) == 0) {
        // echo "TRADE";
        $get_symbol_id = $dbh->prepare(
            'SELECT w.symbol_id, sum(w.lot_size_execute) s,w.order_date,s.paid_up_shares from executed_orders w 
                  LEFT JOIN symbol s on s.symbol_id=w.symbol_id
                  WHERE order_date AND w.order_date LIKE "%' .
                $conditiondate .
                '%" AND w.side="S" GROUP BY w.symbol_id ORDER BY w.order_date ASC'
        );
        $get_symbol_id->execute();

        //SELECT w.symbol_id,sum(w.lot_size_execute),w.order_date from executed_orders w where w.order_date like "%"(SELECT SUBSTR(min(e.order_date),1,16) from executed_orders e where e.order_date like '%2019-12-27%' and e.side='S')"%"  and w.side='S' group by w.symbol_id order by w.order_date AS
        /*foreach($get_symbol_id as $result)
        {
          echo 'sid->'.$result['symbol_id'].'<br>'.'summ->'.$result['s'].'<br>'; 
        }*/

        /*$get_symbol_id= $dbh->prepare('SELECT distinct symbol_id from executed_orders where order_date like "%'.$dateselect.'%"');
        $get_symbol_id->execute();*/
        foreach ($get_symbol_id as $result) {
            $min_vol_required = floor(
                (0.0041 * $result["paid_up_shares"]) / 100
            );
            // echo "<b>".$result['s'].'-->'.$result['symbol_id'].'->'.$min_vol_required."</b><br/>";

            if (floatval($result["s"]) >= $min_vol_required) {
                $get_price = $dbh->prepare(
                    'SELECT 
                                  SUM(order_exe_price * lot_size_execute) AS total_value,
                                  SUM(lot_size_execute) AS total_lot_size,
                                  order_date
                                  FROM 
                                      executed_orders
                                  WHERE 
                                      side = "S" AND
                                      symbol_id = :symbol_id AND order_date LIKE "%' .
                        $conditiondate .
                        '%" ORDER BY  order_date ASC'
                );
                $get_price->bindParam(":symbol_id", $result["symbol_id"]);
                $get_price->execute();
                $price = $get_price->fetch();

                $avg_price = $price["total_value"] / $price["total_lot_size"];
                // $avg_price = number_format($avg_price, 2);

                $get_mp = $dbh->prepare(
                    "SELECT * FROM market_price WHERE symbol_id=:symbol_id"
                );
                $get_mp->bindParam(":symbol_id", $result["symbol_id"]);
                $get_mp->execute();
                if ($get_mp->rowcount() <= 0) {
                    $symid = $result["symbol_id"];
                    $up_insert = $dbh->prepare(
                        "INSERT INTO market_price (symbol_id,market_price) VALUES ('$symid','$avg_price')"
                    );
                    $up_insert->execute();
                } else {
                    $up_insert = $dbh->prepare(
                        "UPDATE market_price SET ex_market_price=market_price, ex_date=date where symbol_id=:symbol_id"
                    );
                    $up_insert->bindParam(":symbol_id", $result["symbol_id"]);
                    $up_insert->execute();

                    $up_price = $dbh->prepare(
                        "UPDATE market_price SET market_price=:close_price,date=:dt where symbol_id=:symbol_id"
                    );
                    $up_price->bindParam(":symbol_id", $result["symbol_id"]);
                    $up_price->bindParam(":close_price", $avg_price);
                    $up_price->bindParam(":dt", $price["order_date"]);
                    $up_price->execute();
                }
            } else {
            }
        }
    } else {
        echo "NO TRADE";
    }
} else {
    echo "not valid trading hour";
    //Price update for closing is only done at the last trading cycle
}

//price update market price end
//delete those orders whose remaining orders are
$get_orders = $dbh->prepare("SELECT * FROM orders WHERE order_size=0");
$get_orders->execute();
foreach ($get_orders as $del) {
    $ooid = $del["order_id"];
    $fid = $del["flag_id"];
    $del = $dbh->prepare("DELETE FROM orders  WHERE order_id=:ooid");
    $del->bindParam(":ooid", $ooid);
    $del->execute();

    $del1 = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id=:fid");
    $del1->bindParam(":fid", $fid);
    $del1->execute();

    $del2 = $dbh->prepare("DELETE FROM mcams_wallet WHERE flag_id=:fid");
    $del2->bindParam(":fid", $fid);
    $del2->execute();
}
//end order deletion
//code to record price history
$sql ="INSERT INTO market_price_history(symbol_id,price,changes) SELECT symbol_id,market_price,ex_market_price-market_price from market_price";
//echo $sql;
$price_history_update = $dbh->prepare($sql);
if ($price_history_update->execute()) {
    echo "price history updated";
} else {
    echo "no";
}

//end for recording price history
//code for new price update

/*$total_vol_traded_3_months=$dbh->prepare("SELECT sum(lot_size_execute) as lse from executed_orders
where order_date >= DATE_SUB(NOW(),INTERVAL 3 MONTH) and order_date<=CURRENT_DATE 
and side='B'");
$total_vol_traded_3_months->execute();
$val1=$total_vol_traded_3_months->fetch();
$total_vol_traded_in_3_months=$val1['lse'];*/

//end code for new price update

?>
