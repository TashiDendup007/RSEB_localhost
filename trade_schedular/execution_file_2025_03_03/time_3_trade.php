<?php  
if ($currentHour == 15) {
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
            'SELECT w.symbol_id, sum(w.lot_size_execute) s, w.order_date, s.paid_up_shares 
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
                                SELECT 
                                  SUM(order_exe_price * lot_size_execute) AS total_value,
                                  SUM(lot_size_execute) AS total_lot_size,
                                  order_date
                                  FROM 
                                      executed_orders
                                  WHERE 
                                      side = "S" AND
                                      symbol_id = :symbol_id AND order_date LIKE "%' .
                        $conditiondate .
                        '%" ORDER BY  order_date ASC
                ');
                $get_price->bindParam(":symbol_id", $result["symbol_id"]);
                $get_price->execute();
                $price = $get_price->fetch();

                $avg_price = $price["total_value"] / $price["total_lot_size"];
                // $avg_price = number_format($avg_price, 2);

                $get_mp = $dbh->prepare(
                    "SELECT * FROM market_price WHERE symbol_id = :symbol_id"
                );
                $get_mp->bindParam(":symbol_id", $result["symbol_id"]);
                $get_mp->execute();
                if ($get_mp->rowcount() <= 0) {
                    $symid = $result["symbol_id"];
                    $up_insert = $dbh->prepare(
                        "INSERT INTO market_price (symbol_id, market_price) VALUES (?, ?)"
                    );
                    $up_insert->execute([$symid, $avg_price]);
                } else {
                    $up_insert = $dbh->prepare(
                        "UPDATE market_price SET ex_market_price = market_price, ex_date = date WHERE symbol_id = :symbol_id"
                    );
                    $up_insert->bindParam(":symbol_id", $result["symbol_id"]);
                    $up_insert->execute();

                    $up_price = $dbh->prepare(
                        "UPDATE market_price SET market_price=:close_price, date=:dt WHERE symbol_id=:symbol_id"
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
    // echo "not valid trading hour";
    //Price update for closing is only done at the last trading cycle
    // allow to update the price if 

}

?>