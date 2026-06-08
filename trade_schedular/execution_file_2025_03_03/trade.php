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

        $sell = $dbh->prepare("SELECT sum(sell_vol) AS total FROM orders WHERE symbol_id = :sy");
        $sell->execute([":sy" => $sym_id]);
        $stotal = $sell->fetchColumn();

        $buy = $dbh->prepare("SELECT sum(buy_vol) AS total FROM orders WHERE symbol_id = :sy");
        $buy->execute([":sy" => $sym_id]);
        $btotal = $buy->fetchColumn();
        //sell price entry

        $q2 = $dbh->prepare('SELECT price, symbol_id FROM orders WHERE side = "S" and symbol_id = :sy');
        $q2->execute([":sy" => $sym_id]);
        
        // query to insert into price table from sell side
        $q222 = $dbh->prepare("INSERT INTO price_table (prices, symbol_id) VALUES (?, ?)");
        while ($row = $q2->fetch()) {
            $a = $row["price"];
            $s = $row["symbol_id"];

            $q222->execute([$a, $s]);
        }

        //buy price entry
        $q2 = $dbh->prepare('SELECT price, symbol_id FROM orders WHERE side = "B" and symbol_id = :sy');
        $q2->execute([":sy" => $sym_id]);

        // query to insert into price table from buy side
        $q222 = $dbh->prepare("INSERT INTO price_table (prices, symbol_id) VALUES (?, ?)");
        while ($row = $q2->fetch()) {
            $a = $row["price"];
            $s = $row["symbol_id"];

            $q222->execute([$a, $s]);
        }

        //sell lot entry
        $q2 = $dbh->prepare("SELECT prices FROM price_table WHERE symbol_id = :sy");
        $q2->execute([":sy" => $sym_id]);
        $sell_lots = $q2->fetchAll(PDO::FETCH_ASSOC);

        // query to get total sell vol where price is greater than p
        $su_sql = $dbh->prepare("SELECT sum(sell_vol) as su FROM orders WHERE price > :p and symbol_id = :sy AND side = 'S'");
        // query to update the total sell vol at the given price
        $su_update = $dbh->prepare("UPDATE price_table SET volume_sell = :vs WHERE prices =:p AND symbol_id = :sy");

        foreach ($sell_lots as $value) {
            $p = $value["prices"];

            $su_sql->execute([
                ":p" => $p,
                ":sy" => $sym_id,
            ]);
            $su = $su_sql->fetchColumn();
            $vs = $stotal - $su;

            $su_update->execute([
                ":vs" => $vs,
                ":p" => $p,
                ":sy" => $sym_id,
            ]);
        }

        //buy lot entry
        $q2 = $dbh->prepare("SELECT prices FROM price_table WHERE symbol_id = :sy");
        $q2->execute([":sy" => $sym_id]);
        $buy_lots = $q2->fetchAll(PDO::FETCH_ASSOC);

        // query to get total buy vol where price is greater than p
        $bu_sql = $dbh->prepare("SELECT sum(buy_vol) as su from orders where price < :p and symbol_id=:sy and side = 'B'");
        // query to update the total buy vol at the given price
        $bu_update = $dbh->prepare("UPDATE price_table SET volume_buy = :vs WHERE prices = :p AND symbol_id = :sy");

        foreach ($buy_lots as $value) {
            $p = $value["prices"];
            
            $bu_sql->execute([
                ":p" => $p,
                ":sy" => $sym_id,
            ]);
            $su = $bu_sql->fetchColumn();
            $vs = $btotal - $su;

            $bu_update->execute([
                ":vs" => $vs,
                ":p" => $p,
                ":sy" => $sym_id,
            ]);
        }

        //finding the difference
        $sys = $dbh->prepare("SELECT DISTINCT symbol_id FROM price_table");
        $sys->execute();
        foreach ($sys as $value) {
            $sym_id = $value["symbol_id"];

            $q2 = $dbh->prepare("SELECT prices FROM price_table WHERE symbol_id = :sy");
            $q2->bindParam(":sy", $sym_id);
            $q2->execute();
            $rows = $q2->fetchAll(PDO::FETCH_ASSOC);

            // query to get buy and sell vol at the given price
            $get_stmt = $dbh->prepare("SELECT volume_buy, volume_sell FROM price_table WHERE prices = :p AND symbol_id = :sy");
            // query to update vol difference of the symbol
            $update_stmt = $dbh->prepare("UPDATE price_table SET difference = :l, diff_chk = :l WHERE prices = :p AND symbol_id = :sy");

            foreach ($rows as $value) {
                $p = $value["prices"];

                $get_stmt->execute([
                    ":p" => $p, ":sy" => $sym_id 
                ]);
                $se = $get_stmt->fetch(PDO::FETCH_ASSOC);

                $a = $se["volume_buy"];
                $b = $se["volume_sell"];

                if ($a > $b) {
                    $l = $b;
                } elseif ($a < $b) {
                    $l = $a;
                } else {
                    $l = $a;
                }

                // update
                $update_stmt->execute([
                    ":l" => $l, ":p" => $p, ":sy" => $sym_id 
                ]);
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
$symbol_rows = $q222->fetchAll(PDO::FETCH_ASSOC);
foreach ($symbol_rows as $value) {
    $sym_id = $value["symbol_id"];

    $q222 = $dbh->prepare(
        "SELECT * FROM price_table WHERE prices=(SELECT max(prices) FROM price_table WHERE symbol_id=:sym_id and difference=(select max(difference) FROM price_table WHERE symbol_id=:sym_id)) AND symbol_id=:sym_id"
    );
    $q222->execute([":sym_id" => $sym_id]);
    $value = $q222->fetch(PDO::FETCH_ASSOC);

    $op = $value["prices"];
    $diff = $value["difference"];
    $sym_id = $value["symbol_id"];
    $vb = $value["volume_buy"];
    $vs = $value["volume_sell"];
    $pid = $value["pid"];
    $diff_chk = $value["diff_chk"];
    
    // echo $diff."---price--".$op."---".$sym_id."--machable--number of row<br>------------<br>";

    if ($vb == $vs && $vb == $diff && $vs == $diff) {
        $pr = $dbh->prepare('SELECT * FROM orders WHERE price <= :op and symbol_id = :sym_id and sell_vol > 0 and side = "S" ORDER BY sell_vol DESC');
        $pr->execute([":op" => $op, ":sym_id" => $sym_id]);
        $pr_sell_rows = $pr->fetchAll(PDO::FETCH_ASSOC);

        $update_sell_lot = $dbh->prepare("UPDATE orders SET sell_vol=:n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");

        foreach ($pr_sell_rows as $value) {
            $oidd = $value["order_id"];
            $sell_vol = $value["sell_vol"];
            $n = 0;
            $v = $sell_vol;
            
            $update_sell_lot->execute([
                ":n" => $n,
                ":v" => $v,
                ":op" => $op,
                ":oidd" => $oidd
            ]);
        }

        $pr = $dbh->prepare("SELECT * FROM orders WHERE price >= :op and symbol_id = :sym_id and buy_vol > 0 and side = 'B' ORDER BY buy_vol DESC");
        $pr->execute([
            ":op" => $op,
            ":sym_id" => $sym_id,
        ]);
        $pr_buy_rows = $pr->fetchAll(PDO::FETCH_ASSOC);

        $update_buy_lot = $dbh->prepare("UPDATE orders SET exe_vol = :v, buy_vol = :n, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");

        foreach ($pr_buy_rows as $value) {
            $oiddb = $value["order_id"];
            $buy_vol = $value["buy_vol"];
            $n = 0;
            $v = $buy_vol;
            
            $update_buy_lot->execute([
                ":n" => $n,
                ":v" => $v,
                ":op" => $op,
                ":oidd" => $oiddb
            ]);
        }
    }
    //elseif X
    elseif ($vb < $vs && $vb == $diff) {
        $diff_chk = compare($pid);
        for ($x = $diff_chk; $x > 0; ) {
            $diff_chk = compare($pid);
            $rowcountsell = rowcountsell($op, $sym_id);

            $pr = $dbh->prepare("SELECT * FROM orders WHERE price <= :op AND symbol_id = :sym_id AND sell_vol > 0 AND side = 'S' ORDER BY sell_vol DESC");
            $pr->execute([":op" => $op, ":sym_id" => $sym_id]);

            $allocation = floor($diff_chk / $rowcountsell);
            
            foreach ($pr as $value) {
                $oidd = $value["order_id"];
                $sell_vol = $value["sell_vol"];
                $exe_vol = $value["exe_vol"];

                if ($sell_vol == $allocation) {
                    $exe_vol = exe_vol($oidd);
                    $n = $sell_vol - $allocation;
                    $v = $exe_vol + $allocation;

                    $update_sell_lot = $dbh->prepare("UPDATE orders SET sell_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                    $update_sell_lot->execute([
                        ":n" => $n,
                        ":v" => $v,
                        ":op" => $op,
                        ":oidd" => $oidd,
                    ]);

                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    
                    $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                    $update_diff_chk->execute([
                        ":dif" => $diff_new,
                        ":pid" => $pid,
                    ]);
                } elseif ($sell_vol < $allocation) {
                    $exe_vol = exe_vol($oidd);
                    $n = $sell_vol - $sell_vol;
                    $v = $exe_vol + $sell_vol;

                    $update_sell_lot = $dbh->prepare("UPDATE orders SET sell_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                    $update_sell_lot->execute([
                        ":n" => $n,
                        ":v" => $v,
                        ":op" => $op,
                        ":oidd" => $oidd,
                    ]);

                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $sell_vol;
                    
                    $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                    $update_diff_chk->execute([
                        ":dif" => $diff_new,
                        ":pid" => $pid,
                    ]);
                } elseif ($sell_vol > $allocation && $allocation > 0) {
                    $exe_vol = exe_vol($oidd);
                    $n = $sell_vol - $allocation;
                    $v = $exe_vol + $allocation;

                    $update_sell_lot = $dbh->prepare("UPDATE orders SET sell_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                    $update_sell_lot->execute([
                        ":n" => $n,
                        ":v" => $v,
                        ":op" => $op,
                        ":oidd" => $oidd,
                    ]);
                    
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    
                    $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                    $update_diff_chk->execute([
                        ":dif" => $diff_new,
                        ":pid" => $pid,
                    ]);
                } elseif ($allocation == 0 && $diff_chk < $rowcountsell) {
                    $diff_chk = compare($pid);
                    if ($diff_chk == 0) {
                    } else {
                        $exe_vol = exe_vol($oidd);
                        $n = $sell_vol - 1;
                        $v = $exe_vol + 1;

                        $update_sell_lot = $dbh->prepare("UPDATE orders SET sell_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                        $update_sell_lot->execute([
                            ":n" => $n,
                            ":v" => $v,
                            ":op" => $op,
                            ":oidd" => $oidd,
                        ]);

                        $diff_chk = compare($pid);
                        $diff_new = $diff_chk - 1;
                        
                        $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                        $update_diff_chk->execute([
                            ":dif" => $diff_new,
                            ":pid" => $pid,
                        ]);
                    }
                }
            }
            $x = compare($pid);
        }

        $pr = $dbh->prepare("SELECT * FROM orders WHERE price >= :op AND symbol_id = :sym_id AND buy_vol > 0 AND side = 'B' ORDER BY buy_vol DESC");
        $pr->execute([
            ":op" => $op,
            ":sym_id" => $sym_id,
        ]);

        $update_buy_lot = $dbh->prepare("UPDATE orders SET exe_vol = :v, buy_vol = :n, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
        foreach ($pr as $value) {
            $oiddb = $value["order_id"];
            $buy_vol = $value["buy_vol"];
            $n = 0;
            $v = $buy_vol;

            $update_buy_lot->execute([
                ":v" => $v,
                ":n" => $n,
                ":op" => $op,
                ":oidd" => $oiddb,
            ]);
        }
    }
    //elseif X
    elseif ($vs < $vb && $vs == $diff) {
        $diff_chk = compare($pid);
        for ($x = $diff_chk; $x > 0; ) {
            $diff_chk = compare($pid);
            $rowcountbuy = rowcountbuy($op, $sym_id);

            $pr = $dbh->prepare("SELECT * FROM orders WHERE price >= :op AND symbol_id = :sym_id AND buy_vol > 0 AND side = 'B' ORDER BY buy_vol DESC");
            $pr->execute([
                ":op" => $op,
                ":sym_id" => $sym_id
            ]);

            $allocation = floor($diff_chk / $rowcountbuy);
            
            foreach ($pr as $value) {
                $oidd = $value["order_id"];
                $buy_vol = $value["buy_vol"];
                $exe_vol = $value["exe_vol"];

                if ($buy_vol == $allocation) {
                    $exe_vol = exe_vol_b($oidd);
                    $n = $buy_vol - $allocation;
                    $v = $exe_vol + $allocation;

                    $update_sell_lot = $dbh->prepare("UPDATE orders SET buy_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                    $update_sell_lot->execute([
                        ":n" => $n,
                        ":v" => $v,
                        ":op" => $op,
                        ":oidd" => $oidd,
                    ]);

                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    
                    $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                    $update_diff_chk->execute([
                        ":dif" => $diff_new,
                        ":pid" => $pid
                    ]);
                } elseif ($buy_vol < $allocation) {
                    $exe_vol = exe_vol_b($oidd);
                    $n = $buy_vol - $buy_vol;
                    $v = $exe_vol + $buy_vol;

                    $update_sell_lot = $dbh->prepare("UPDATE orders SET buy_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                    $update_sell_lot->execute([
                        ":n" => $n,
                        ":v" => $v,
                        ":op" => $op,
                        ":oidd" => $oidd,
                    ]);
                    
                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $buy_vol;
                    
                    $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                    $update_diff_chk->execute([
                        ":dif" => $diff_new,
                        ":pid" => $pid
                    ]);
                } elseif ($buy_vol > $allocation && $allocation > 0) {
                    $exe_vol = exe_vol_b($oidd);
                    $n = $buy_vol - $allocation;
                    $v = $exe_vol + $allocation;

                    $update_sell_lot = $dbh->prepare("UPDATE orders SET buy_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                    $update_sell_lot->execute([
                        ":n" => $n,
                        ":v" => $v,
                        ":op" => $op,
                        ":oidd" => $oidd,
                    ]);

                    $diff_chk = compare($pid);
                    $diff_new = $diff_chk - $allocation;
                    
                    $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                    $update_diff_chk->execute([
                        ":dif" => $diff_new,
                        ":pid" => $pid
                    ]);
                } elseif ($allocation == 0 && $diff_chk < $rowcountbuy) {
                    $diff_chk = compare($pid);
                    if ($diff_chk == 0) {
                    } else {
                        $exe_vol = exe_vol_b($oidd);
                        $n = $buy_vol - 1;
                        $v = $exe_vol + 1;
                        
                        $update_sell_lot = $dbh->prepare("UPDATE orders SET buy_vol = :n, exe_vol = :v, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
                        $update_sell_lot->execute([
                            ":n" => $n,
                            ":v" => $v,
                            ":op" => $op,
                            ":oidd" => $oidd,
                        ]);
                        
                        $diff_chk = compare($pid);
                        $diff_new = $diff_chk - 1;
                        
                        $update_diff_chk = $dbh->prepare("UPDATE price_table SET diff_chk = :dif WHERE pid = :pid");
                        $update_diff_chk->execute([
                            ":dif" => $diff_new,
                            ":pid" => $pid
                        ]);
                    }
                }
            }
            $x = compare($pid);
        }
        $pr = $dbh->prepare("SELECT * FROM orders WHERE price <= :op AND symbol_id = :sym_id AND sell_vol > 0 AND side = 'S' ORDER BY sell_vol DESC");
        $pr->execute([
            ":op" => $op,
            ":sym_id" => $sym_id
        ]);

        foreach ($pr as $value) {
            $oiddb = $value["order_id"];
            $sell_vol = $value["sell_vol"];
            $n = 0;
            $v = $sell_vol;

            $update_buy_lot = $dbh->prepare("UPDATE orders SET exe_vol = :v, sell_vol = :n, exe_price = :op, lot_check = :v, order_size = :n WHERE order_id = :oidd");
            $update_buy_lot->execute([
                ":v" => $v,
                ":n" => $n,
                ":op" => $op,
                ":oidd" => $oiddb,
            ]);
        }
    }
    //allocation end
    //echo "Allocation Completed";
}
//updating executed orders table
$q222 = $dbh->prepare("
        SELECT a.order_id, a.price, a.participant_code, a.order_entry, a.cd_code, a.exe_price, a.exe_vol, a.flag_id, a.member_broker, a.symbol_id, a.side, b.institution_id, c.rate, i.gst_register 
        FROM orders a
        JOIN adm_participants b ON a.participant_code = b.participant_code 
        JOIN adm_institution i ON b.institution_id = i.institution_id
        JOIN client_account ca ON a.cd_code = ca.cd_code
        JOIN bbo_commission c ON c.bro_comm_id = ca.bro_comm_id
        WHERE exe_vol > 0
");
$q222->execute();
$order_rows = $q222->fetchAll(PDO::FETCH_ASSOC);
foreach ($order_rows as $value) {
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
    $gst_register = $value["gst_register"];

    $new_exe_amt = $order_exe_price * $lot_size_execute;
    $amt = ($order_exe_price * $lot_size_execute * $b_commis) / 100;
    $commis_fee = round($amt, 2);
    $gst_fee = round($commis_fee * 0.05, 2);
    
    // $b_commis=client_commission($cd_code);

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
        $update_orders = $dbh->prepare("UPDATE orders SET exe_vol = 0, exe_price = 0 WHERE order_id = :oidd");
        $update_orders->execute([":oidd" => $oidd]);

        if ($side === $s) {
            $new_exe_amt = $new_exe_amt;

            $finding_p_o = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :sym_id AND volume > 0");
            $finding_p_o->execute([":cd_code" => $cd_code, ":sym_id" => $sym_id]);
            $finding_rows = $finding_p_o->fetchAll(PDO::FETCH_ASSOC);

            foreach ($finding_rows as $value) {
                $lo_check_q = $dbh->prepare("SELECT lot_check FROM orders WHERE order_id = :oidd");
                $lo_check_q->execute([":oidd" => $oidd]);
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

                    $update_lot_check = $dbh->prepare("UPDATE orders SET lot_check = :exe_vol_new_s WHERE order_id = :oidd");
                    $update_lot_check->execute([
                        ":exe_vol_new_s" => $exe_vol_new_s,
                        ":oidd" => $oidd,
                    ]);
                } 
                elseif ($exe_vol_new < $value["volume"] && $exe_vol_new > 0) {
                    if ($value["volume"] >= $sum) {
                        $exe_vol_new_l = 0;

                        $update_lot_check = $dbh->prepare("UPDATE orders SET lot_check = :exe_vol_new_l WHERE order_id = :oidd");
                        $update_lot_check->execute([
                            ":exe_vol_new_l" => $exe_vol_new_l,
                            ":oidd" => $oidd,
                        ]);
                    }
                    elseif ($value["volume"] < $sum) {
                        $new_block_s = $value["volume"] - $existing_pending_out_lot;
                        $exe_vol_new_m = $exe_vol_new - $new_block_s;

                        $update_lot_check = $dbh->prepare("UPDATE orders SET lot_check = :exe_vol_new_m WHERE order_id = :oidd");
                        $update_lot_check->execute([
                            ":exe_vol_new_m" => $exe_vol_new_m,
                            ":oidd" => $oidd,
                        ]);
                    }
                } else {
                    echo "no more";
                }
            }

            // update finance with new amount for both bbo finance and mcams wallet for seller.
            $stmt = $dbh->prepare("SELECT order_size, price FROM orders WHERE flag_id = ?");
            $stmt->execute([$flag_id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $net_amt_sell = $res['order_size'] * $res['price'];
            $broker_commission_fee = $net_amt_sell * $b_commis * 0.01;
            $pending_gst_amt = ($gst_register === 'Y') ? ($broker_commission_fee * 0.05) : 0;
            $new_pending_amount = round($net_amt_sell - ($broker_commission_fee + $pending_gst_amt), 2);

            // update new pending amount
            $update_finance = $dbh->prepare("UPDATE bbo_finance SET amount = :new_exe_amt WHERE flag_id = :flag_id");
            $update_finance->execute([
                // ":new_exe_amt" => $new_exe_amt,
                ":new_exe_amt" => $new_pending_amount,
                ":flag_id" => $flag_id,
            ]);

            // update new pending amount for BLA
            if ($institution_id == $ins_id_mcams) {
                $update_fin_bla = $dbh->prepare("UPDATE mcams_wallet SET amount = :new_exe_amt WHERE flag_id = :flag_id");
                $update_fin_bla->execute([
                    ":new_exe_amt" => $new_pending_amount,
                    ":flag_id" => $flag_id,
                ]);
            }

            // enter transaction for the executed orders
            $flag_sell = 2;
            $finsellstatus = 0;
            $remarks_sell = "Amount for selling " . $lot_size_execute . " share @ Nu." . $order_exe_price;

            $b_fin = $dbh->prepare("
                INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id, status)
                VALUES (:cd_code, :amount, :user_name, :remarks, :flag, :institution_id, :flag_id, :status)
            ");
            $b_fin->execute([
                ':cd_code'         => $cd_code,
                ':amount'          => $new_exe_amt,
                ':user_name'       => $member_broker,
                ':remarks'         => $remarks_sell,
                ':flag'            => $flag_sell,
                ':institution_id'  => $institution_id,
                ':flag_id'         => $oidd,
                ':status'          => $finsellstatus,
            ]);

            //commission for the seller start
            $flag = 4;
            $remarks = "Commission for the trade of " . $lot_size_execute . " share @ Nu. " . $order_exe_price;

            $gst_flag = 5;
            $gst_remarks = "GST fee for the trade of " . $lot_size_execute . " share @ Nu. " . $order_exe_price;

            $finsellstatuscomm = 0;
            $sql = "
                INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id, status)
                VALUES (:cd_code1, :amount1, :user_name1, :remarks1, :flag1, :institution_id1, :flag_id1, :status1)
            ";

            $params = [
                ':cd_code1'        => $cd_code,
                ':amount1'         => -abs($commis_fee),
                ':user_name1'      => $member_broker,
                ':remarks1'        => $remarks,
                ':flag1'           => $flag,
                ':institution_id1' => $institution_id,
                ':flag_id1'        => $oidd,
                ':status1'         => $finsellstatuscomm
            ];

            if ($gst_register === 'Y') {
                $sql .= ",
                    (:cd_code2, :amount2, :user_name2, :remarks2, :flag2, :institution_id2, :flag_id2, :status2)
                ";

                $params += [
                    ':cd_code2'        => $cd_code,
                    ':amount2'         => -abs($gst_fee),
                    ':user_name2'      => $member_broker,
                    ':remarks2'        => $gst_remarks,
                    ':flag2'           => $gst_flag,
                    ':institution_id2' => $institution_id,
                    ':flag_id2'        => $oidd,
                    ':status2'         => $finsellstatuscomm
                ];
            }

            $stmt = $dbh->prepare($sql);
            $stmt->execute($params);
            //commission for the seller end

            if ($institution_id == $ins_id_mcams) {
                //insert into mcams wallet
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
        } 
        elseif ($side === $b) {
            $new_exe_amt = $new_exe_amt * -1;
            $new_bbo_amt = $new_exe_amt * -1;

            $cds_client_check = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code = :cd_code AND symbol_id = :sym_id");
            $cds_client_check->execute([":cd_code" => $cd_code, ":sym_id" => $sym_id]);
            $res = $cds_client_check->fetch(PDO::FETCH_ASSOC);

            $buyer_cd_code = isset($res["cd_code"]) ? $res["cd_code"] : '';
            $pending_in_vol_existing = isset($res["pending_in_vol"]) ? $res["pending_in_vol"] : 0;
            $pending_in_vol_new = $pending_in_vol_existing + $pending_in_vol;

            if ($buyer_cd_code === $cd_code) {
                //record update
                $save = $dbh->prepare("UPDATE cds_holding SET pending_in_vol = :pending_in_vol_new WHERE cd_code = :cd_code AND symbol_id = :sym_id");
                $save->execute([
                    ":pending_in_vol_new" => $pending_in_vol_new, 
                    ":cd_code" => $cd_code,
                    ":sym_id" => $sym_id,
                ]);
            } else {
                //create new cds_holding entry for pending in
                $vol = 0;
                $type = "Record First entered via buy of, " .$pending_in_vol . " number of shares";

                $save = $dbh->prepare("INSERT INTO cds_holding (cd_code, volume, user_name, institution_id, symbol_id, remarks, pending_in_vol) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $save->execute([$cd_code, $vol, $username, $institution_id, $sym_id, $type, $pending_in_vol]);
            }

            // update finance with new amount for both bbo finance and mcams wallet for buyer
            $stmt = $dbh->prepare("SELECT order_size, price FROM orders WHERE flag_id = ?");
            $stmt->execute([$flag_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $net_amt_buy = $row['order_size'] * $row['price'];
            $broker_commission_fee = $net_amt_buy * $b_commis * 0.01;
            $pending_gst_amt = ($gst_register === 'Y') ? ($broker_commission_fee * 0.05) : 0;
            $new_pending_amount = round($net_amt_buy + $broker_commission_fee + $pending_gst_amt, 2);

            // update new pending amount
            $update_finance = $dbh->prepare("UPDATE bbo_finance SET amount = :new_exe_amt WHERE flag_id = :flag_id");
            $update_finance->execute([
                ":new_exe_amt" => -abs($new_pending_amount),
                ":flag_id" => $flag_id,
            ]);

            // update new pending amount for BLA
            if ($institution_id == $ins_id_mcams) {
                $update_fin_bla = $dbh->prepare("UPDATE mcams_wallet SET amount = :new_exe_amt WHERE flag_id = :flag_id");
                $update_fin_bla->execute([
                    ":new_exe_amt" => -abs($new_pending_amount),
                    ":flag_id" => $flag_id,
                ]);
            }

            /*$update_finance = $dbh->prepare("UPDATE bbo_finance SET amount = amount + :new_bbo_amt WHERE flag_id = :flag_id");
            $update_finance->execute([
                ":new_bbo_amt" => $new_bbo_amt, 
                ":flag_id" => $flag_id,
            ]);*/

            $flag_buy = 3;
            $remarks_buy = "Amount for buying " . $lot_size_execute . " share @ Nu." . $order_exe_price;

            $b_fin = $dbh->prepare("INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $b_fin->execute([$cd_code, $new_exe_amt, $member_broker, $remarks_buy, $flag_buy, $institution_id, $oidd]);

            //commission for the buyer start
            $flag = 4;
            $remarks = "Commission for the trade of " . $lot_size_execute . " share @ Nu. " . $order_exe_price;

            $gst_flag = 5;
            $gst_remarks = "GST fee for the trade of " . $lot_size_execute . " share @ Nu. " . $order_exe_price;

            $sql_b = "
                INSERT INTO bbo_finance (cd_code, amount, user_name, remarks, flag, institution_id, flag_id)
                VALUES (:cd_code1, :amount1, :user_name1, :remarks1, :flag1, :institution_id1, :flag_id1)
            ";

            $params = [
                ':cd_code1'        => $cd_code,
                ':amount1'         => -abs($commis_fee),
                ':user_name1'      => $member_broker,
                ':remarks1'        => $remarks,
                ':flag1'           => $flag,
                ':institution_id1' => $institution_id,
                ':flag_id1'        => $oidd
            ];

            if ($gst_register === 'Y') {
                $sql_b .= ",
                    (:cd_code2, :amount2, :user_name2, :remarks2, :flag2, :institution_id2, :flag_id2)
                ";

                $params += [
                    ':cd_code2'        => $cd_code,
                    ':amount2'         => -abs($gst_fee),
                    ':user_name2'      => $member_broker,
                    ':remarks2'        => $gst_remarks,
                    ':flag2'           => $gst_flag,
                    ':institution_id2' => $institution_id,
                    ':flag_id2'        => $oidd
                ];
            }

            $stmt = $dbh->prepare($sql_b);
            $stmt->execute($params);
            //commission for the buyer end

            if ($institution_id == $ins_id_mcams) {
                //insert into mcams wallet
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
    //Price update for closing is only done at the last trading cycle
    $dateselect = date("Y-m-d");

    $specifieddate = $dbh->prepare('
        SELECT SUBSTRING(max(e.order_date), 1, 10) AS dat FROM executed_orders e WHERE e.side = "S" AND e.order_date LIKE "%' . $dateselect . '%"
    ');
    $specifieddate->execute();
    $spdate = $specifieddate->fetch();
    $conditiondate = $spdate["dat"];

    if (is_null($conditiondate) == 0) {
        // echo "TRADE";
        $get_symbol_id = $dbh->prepare(
            'SELECT w.symbol_id, sum(w.lot_size_execute) AS s, w.order_date, s.paid_up_shares 
            FROM executed_orders w 
            LEFT JOIN symbol s on s.symbol_id = w.symbol_id
            WHERE order_date AND w.order_date LIKE "%' . $conditiondate . '%" AND w.side="S" GROUP BY w.symbol_id ORDER BY w.order_date ASC'
        );
        $get_symbol_id->execute();

        foreach ($get_symbol_id as $result) {
            $min_vol_required = floor(
                (0.0041 * $result["paid_up_shares"]) / 100
            );

            if (floatval($result["s"]) >= $min_vol_required) {
                $get_price = $dbh->prepare('
                        SELECT SUM(order_exe_price * lot_size_execute) AS total_value, SUM(lot_size_execute) AS total_lot_size, order_date
                        FROM executed_orders
                        WHERE side = "S" AND symbol_id = :symbol_id AND order_date LIKE "%' . $conditiondate . '%" ORDER BY  order_date ASC
                ');
                $get_price->bindParam(":symbol_id", $result["symbol_id"]);
                $get_price->execute();
                $price = $get_price->fetch();

                $avg_price = $price["total_value"] / $price["total_lot_size"];

                $get_mp = $dbh->prepare("SELECT * FROM market_price WHERE symbol_id = :symbol_id");
                $get_mp->bindParam(":symbol_id", $result["symbol_id"]);
                $get_mp->execute();
                if ($get_mp->rowcount() <= 0) {
                    $symid = $result["symbol_id"];

                    $up_insert = $dbh->prepare("INSERT INTO market_price (symbol_id, market_price) VALUES (?, ?)");
                    $up_insert->execute([$symid, $avg_price]);
                } else {
                    $up_insert = $dbh->prepare("UPDATE market_price SET ex_market_price = market_price, ex_date = date WHERE symbol_id = :symbol_id");
                    $up_insert->execute([
                        ":symbol_id" => $result["symbol_id"]
                    ]);

                    $up_price = $dbh->prepare("UPDATE market_price SET market_price = :close_price, date = :dt WHERE symbol_id = :symbol_id");
                    $up_price->execute([
                        ":close_price" => $avg_price, ":dt" => $price["order_date"], ":symbol_id" => $result["symbol_id"]
                    ]);
                }
            }
        }
    } else {
        echo "NO TRADE";
    }
} else {
    // allow to update the price on one session if 0.0041 vol meet
    $dateselect = date("Y-m-d H");
    $start = $dateselect . ':00:00';
    $end   = $dateselect . ':59:59';

    /**
     * 1. Fetch aggregated data (optimized)
     */
    $stmt = $dbh->prepare("
            SELECT w.symbol_id, SUM(w.lot_size_execute) AS total_lot, w.order_exe_price AS price, MAX(w.order_date) AS last_order_date, s.paid_up_shares
            FROM executed_orders w
            JOIN symbol s ON s.symbol_id = w.symbol_id
            WHERE w.order_date BETWEEN :start AND :end AND w.side = 'S'
            GROUP BY w.symbol_id, s.paid_up_shares
    ");
    $stmt->execute([':start' => $start, ':end'   => $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /**
     * 2. Prepare single UPSERT statement
     */
    $upsert = $dbh->prepare("
        INSERT INTO market_price (symbol_id, market_price, date) VALUES (:symbol_id, :price, :date)
        ON DUPLICATE KEY UPDATE
            ex_market_price = market_price,
            ex_date = date,
            market_price = VALUES(market_price),
            date = VALUES(date)
    ");

    /**
     * 3. Process rows (no extra queries inside loop)
     */
    foreach ($rows as $value) {
        $min_vol_required = floor((0.0041 * $value["paid_up_shares"]) / 100);

        if ((float)$value["total_lot"] >= $min_vol_required) {

            $upsert->execute([
                ':symbol_id' => $value["symbol_id"],
                ':price'     => $value["price"],
                ':date'      => $value["last_order_date"]
            ]);
        }
    }
}

//price update market price end
//delete those orders whose remaining orders are
$get_orders = $dbh->prepare("SELECT * FROM orders WHERE order_size = 0");
$get_orders->execute();
$zero_orders = $get_orders->fetchAll(PDO::FETCH_ASSOC);

// delete order with 0 vol
$del = $dbh->prepare("DELETE FROM orders WHERE order_id = :ooid");
// delete finance
$del1 = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = :fid");
// delete mcams wallet
$del2 = $dbh->prepare("DELETE FROM mcams_wallet WHERE flag_id = :fid");

foreach ($zero_orders as $order) {
    $ooid = $order["order_id"];
    $fid = $order["flag_id"];
    
    $del->execute([":ooid" => $ooid]);

    $del1->execute([":fid" => $fid]);

    $del2->execute([":fid" => $fid]);
}
//end order deletion

//code to record price history
$price_history_update = $dbh->prepare("INSERT INTO market_price_history(symbol_id, price, changes) SELECT symbol_id, market_price, ex_market_price - market_price FROM market_price");
$price_history_update->execute();
//end for recording price history
?>
