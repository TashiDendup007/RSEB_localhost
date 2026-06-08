<?php  
date_default_timezone_set("Asia/Thimphu");
include ('session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
include('../PROCESS/bond_ytm_fun.php');

$check = $dbh->prepare("SELECT c.cd_code FROM users c WHERE c.username = ?");
$check->execute([$username]);
$user_cdcode = $check->fetchColumn();

if (isset($_POST['check_vol_fun'])) {
    $symbol_id = $_POST['symbol_id'];
    $cd_code = $_POST['cd_code'];

    $stmt = $dbh->prepare("SELECT c.volume FROM cds_holding c WHERE c.symbol_id = ? AND c.cd_code = ?");
    $stmt->execute([$symbol_id, $cd_code]);
    $vol = $stmt->fetchColumn();
    $holding = ($vol != '' or $vol != 0) ? $vol : 0;
    echo'
    <div class="col-lg-4 col-md-4 col-sm-12">
      <label for="holding_vol">Holding</label>
      <input type="number" class="form-control" name="holding_vol" id="holding_vol" value="' . $holding . '" readonly>
    </div>';
    exit();
}
elseif (isset($_POST['get_symbols_list'])) {
    $security_type = $_POST['sec_type'];

    echo'
    <div class="col-lg-8 col-md-8 col-sm-12">
      <label>Symbol<font color="red">*</font></label>
      <select name="symbol_id" id="symbol_id" class="form-control" onchange="get_bond_dtls(this.value);">
        <option value="" selected>-Select symbol-</option>';
        $stmt = $dbh->prepare("SELECT symbol, symbol_id, name FROM symbol WHERE status = 1 AND trsstatus = 1 AND security_type = ? AND symbol_id != 118");
        $stmt->execute([$security_type]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo'<option value="' . (int)$row['symbol_id'] . '">' . htmlspecialchars($row['symbol'], ENT_QUOTES, 'UTF-8') . ' ('. htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') .')</option>';
        }
      echo'
      </select>
    </div>';
    exit();
}
elseif (isset($_POST['get_bond_details'])) {
    $symbol_id = $_POST['symbol_id'];

    // market value
    $cap_name = 'CAP';
    $market_price = number_format(market_price($symbol_id), 2, '.', ''); 
    $cap = number_format(circuit($cap_name), 2, '.', '');
    $cap_value = number_format(cap_compute($market_price, $cap),2, '.', '');

    $stmt = $dbh->prepare("SELECT s.maturity_date, s.face_value, s.coupon_rates
        FROM symbol s
        WHERE s.symbol_id = ?
    ");
    $stmt->execute([$symbol_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo'
    <div class="col-lg-4 col-md-4 col-sm-12">
        <label for="face_value">Face Value</label>
        <input type="hidden" id="cap" value="'.$cap_value.'" >
        <input type="hidden" id="mp" value="'.$market_price.'" >
        <input type="number" class="form-control" name="face_value" id="face_value" value="' . $res['face_value'] . '" readonly>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-12">
      <label for="coupon_rate">Rate</label>
      <input type="number" class="form-control" name="coupon_rate" id="coupon_rate" value="' . $res['coupon_rates'] . '" readonly>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-12">
      <label for="maturity_date">Maruity Date</label>
      <input type="date" class="form-control" name="maturity_date" id="maturity_date" value="' . $res['maturity_date'] . '" readonly>
    </div>
    ';
    exit;
}
elseif (isset($_POST['calculate_yield_to_maturity'])) {
    $symbol_id = $_POST['symbol_id'];
    $security_type = $_POST['security_type'];
    $clean_price = $_POST['price'];

    if ($security_type != 'OS') {

        $stmt = $dbh->prepare("SELECT s.maturity_date, s.face_value, s.coupon_rates
            FROM symbol s
            WHERE s.symbol_id = ?
        ");
        $stmt->execute([$symbol_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        $maturity_date = $res['maturity_date'];
        $face_value = $res['face_value'];
        $coupon_rates = $res['coupon_rates'];
        $annual_coupon = $face_value * $coupon_rates / 100; // probably 100 
        $sysDateTime = date("Y-m-d");

        error_log('maturity_date=> ' . $maturity_date);
        error_log('face_value=> ' . $face_value);
        error_log('coupon_rates=> ' . $coupon_rates);
        error_log('annual_coupon=> ' . $annual_coupon);
        error_log('sysDateTime=> ' . $sysDateTime);
        error_log('price_input=> ' . $clean_price);
        /*Maturity Date   : 2035-06-05
        Trade Date      : 2026-02-12
        Face Value      : 1000
        Coupon Rate     : 10%
        Annual Coupon   : 100
        Clean Price     : 950
        Day Count       : Actual / 365
        Coupon Frequency: Annual*/

        // calculate accrued interest
        $accrued_interest = accruedInterestACT365($lastCoupon, $sysDateTime, $annual_coupon);
        $dirty_price = round($cleanPrice + $accrued_interest, 2);
    }
}
elseif (isset($_POST['calculate_yield_to_maturity_latest'])) {
    $symbol_id = $_POST['symbol_id'];
    $security_type = $_POST['security_type'];
    $price = $_POST['price'];

    // get symbol details
    $stmt = $dbh->prepare("
            SELECT 
              s.maturity_date, s.face_value, s.coupon_rates, s.date_of_issue, s.coupon_payable AS frequency 
            FROM symbol s
            WHERE s.symbol_id = ?
    ");
    $stmt->execute([$symbol_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    $coupon_rate = $res['coupon_rates'] / 100;
    $tradeDate = Date('Y-m-d');

    $ytm[] = calculateYTM($price, $res['face_value'], $coupon_rate, $res['date_of_issue'], $res['maturity_date'], $tradeDate, $res['frequency']);
}
elseif(!empty($_POST["load_bond_live_market"])) {
    $symbol_id = $_POST['symbol_id'];

    // symbol details
    $stmt = $dbh->prepare("SELECT symbol, name, paid_up_shares FROM symbol WHERE symbol_id = ?");
    $stmt->execute([$symbol_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Initiate cURL
    $ch = curl_init();
    
    // Where you want to post data
    $url2 = "http://localhost/RSEB2020/api2/market_watch_bond_order.php";

    // Define the POST data
    $data2 = array(
      'OrderForEachSymbol' => 'OrderForEachSymbol',
      'Symbol' => $row['symbol']
    );

    // Set cURL options for the second request
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data2));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // added

    // Execute the second request
    $output = curl_exec($ch);
    // Close cURL handle
    curl_close($ch);
    // Output the HTML
    echo'
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">' . $row['name'] . '</h4>
                <span> Paid up Shares : ' . $row['paid_up_shares'] . '</span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-md-12">
                        <table id="example1" class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>Buy Vol</th>
                                    <th>Price</th>
                                    <th>Sell Vol</th>
                                </tr>
                            </thead>
                            <tbody>';
                            $values = json_decode($output, true);
                            $maxTrade = 0;
                            $valueSize = 0;
                            $valueSize = (is_array($values)) ? count($values) : 0;
                            if ($valueSize > 0) {
                                foreach ($values as $key) {
                                    if ($key['Price'] == $key['Discovered']) {
                                            $class = '#17202A';
                                            $color = 'white';
                                    } else {
                                        $class = 'white';
                                        $color = 'black';
                                    }
                                echo'
                                <tr>
                                    <td style="color:#5DADE2;background-color:'.$class.'">'.number_format($key['BuyVol']).'</td>
                                    <td style="color:'.$color.';background-color:'.$class.'">'.$key['Price'].'</td>
                                    <td style="color:red;background-color:'.$class.'">'.number_format($key['SellVol']).'</td>
                                </tr>';
                                }
                            }
                            echo'
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>';
}
elseif (isset($_POST['placing_offer_rfq'])) {
    $symbol_id = $_POST['symbol_id'];
    $side = $_POST['side_offer'];
    $buyer_code = $_POST['cd_code'];
    $side_name = ($side === 'S') ? 'Place Sell Order' : 'Place Buy Order';

    // get symbols details
    $stmt = $dbh->prepare("SELECT symbol, name FROM symbol WHERE symbol_id = ?");
    $stmt->execute([$symbol_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    // get holding
    $get = $dbh->prepare("SELECT volume FROM cds_holding WHERE symbol_id = ? AND cd_code = ?");
    $get->execute([$symbol_id, $user_cdcode]);
    $holding_vol = $get->fetchColumn();
    
    echo '
    <form action="" method="POST">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h3 class="modal-title text-center">' . $side_name . '</h3>
              </div>
              <div class="modal-body">
                <div class="row form-horizontal">
                    <input type="hidden" name="order_type" id="order_type" class="form-control" value="RFQ">
                    <input type="hidden" name="buyer_code" id="buyer_code" class="form-control" value="'. $buyer_code .'">
                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>CD CODE <font color="red">*</font></label>
                    <input type="text" name="cd_code" id="cd_code" class="form-control" value="'. $user_cdcode .'" maxlength="10" required readonly>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Order Side<font color="red">*</font></label>
                    <input type="hidden" name="side" id="side" value="' . $side . '">
                    <select class="form-control" disabled>
                      <option value="">-Select Side-</option>
                      <option value="S" style="color:red;" '.($side == 'S' ? 'selected' : '').'>SELL</option>
                      <option value="B" style="color:blue;" '.($side == 'B' ? 'selected' : '').'>BUY</option>
                    </select>
                  </div>

                  <div class="col-lg-4 col-md-4 col-sm-12">
                    <label>Symbol</label>
                    <input type="text" name="symbol" id="symbol" class="form-control" value="'. $res['symbol'] .'" maxlength="10" required readonly>
                    <input type="hidden" name="symbol_id" id="symbol_id" class="form-control" value="'. $symbol_id .'">
                  </div>

                  <div class="col-lg-8 col-md-8 col-sm-12">
                    <label>Company Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="'. $res['name'] .'" maxlength="10" required readonly>
                  </div>';
                  if ($holding_vol == '' || $holding_vol < 1) {
                    echo'
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <label>Holding Vol</label>
                        <input type="text" class="form-control"  style="color: red;" value="No shares held for symbol '.$res['symbol'].'" required readonly>
                    </div>';
                  } else {
                    echo'
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <label>Holding Vol</label>
                        <input type="text" name="hold_vol" id="hold_vol" class="form-control" value="'. number_format($holding_vol) .'" required readonly>
                    </div>
                      
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <label>Volume (Unit)<font color="red">*</font></label>
                        <input type="number" name="offer_vol" id="offer_vol" class="form-control" required>
                        <small id="offer_vol_error" style="color:red; display:none;"></small>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <label>Price <font color="red">*</font></label>
                        <input type="number" name="offer_price" id="offer_price" class="form-control" required>
                        <small id="offer_price_error" style="color:red; display:none;"></small>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12" id="acc_div_id">
                        <label>Accured Interest</label>
                        <input type="number" name="accur_int" id="accur_int" class="form-control" readonly>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12" id="dirty_div_id">
                        <label>Payable/Receivable Price Per Share</label>
                        <input type="number" name="dirty_price" id="dirty_price" class="form-control" readonly>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12" id="ytm_div_id">
                        <label>Yield To Maturity (YTM)</label>
                        <input type="number" name="ytm_id" id="ytm_id" class="form-control" readonly>
                    </div>';
                  }
                  echo'
                    <div class="col-lg-12 col-md-12 col-sm-12 text-center">
                        <small id="submit_offer_error" style="color:red; display:none;"></small>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>';
                    if ($holding_vol !== '' && $holding_vol >= 1) {
                        echo'
                        <button type="button" class="btn btn-primary" id="submit_offer_btn"><i class="fa fa-check"></i> Submit</button>
                        ';
                    }
                    echo'
              </div>
          </div>
      </div>
    </form>';
}
elseif (!empty($_POST["trade_details"]) && ($_POST["trade_details"] == 'bond_trade_details')) {
    $fromDate = $_POST['fromDate1'] . ' 00:00:00';
    $toDate   = $_POST['toDate1'] . ' 23:59:59';

    /*
    |--------------------------------------------------------------------------
    | Fetch all orders in single query
    |--------------------------------------------------------------------------
    */
    $sql = "
        SELECT beo.symbol_id, s.symbol, beo.cd_code, beo.side, beo.order_date, beo.lot_size_execute, beo.order_exe_price, beo.dirty_price, (beo.lot_size_execute * beo.dirty_price) AS amount
        FROM bond_executed_orders beo
        INNER JOIN symbol s ON beo.symbol_id = s.symbol_id
        WHERE beo.sub_user = :usr_name AND beo.order_date BETWEEN :fdate AND :tdate
        ORDER BY beo.symbol_id, beo.order_date ASC
    ";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([
        ':usr_name'    => $username,
        ':fdate' => $fromDate,
        ':tdate' => $toDate
    ]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    /*
    |--------------------------------------------------------------------------
    | Group data by symbol
    |--------------------------------------------------------------------------
    */
    $groupedData = [];

    foreach ($orders as $row) {
        $symbolId = $row['symbol_id'];
        if (!isset($groupedData[$symbolId])) {
            $groupedData[$symbolId] = [
                'symbol'       => $row['symbol'],
                'rows'         => [],
                'total_trade'  => 0,
                'buy_vol'      => 0,
                'sell_vol'     => 0
            ];
        }

        $groupedData[$symbolId]['rows'][] = $row;
        $groupedData[$symbolId]['total_trade'] += $row['amount'];
        if ($row['side'] == 'B') {
            $groupedData[$symbolId]['buy_vol'] += $row['lot_size_execute'];
        }

        if ($row['side'] == 'S') {
            $groupedData[$symbolId]['sell_vol'] += $row['lot_size_execute'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HTML Output
    |--------------------------------------------------------------------------
    */
    ?>

    <div class="col-lg-12">
        <div class="box-body table-responsive">
            Summary of Bond Trade<br>
            From : <?= $fromDate ?> - To : <?= $toDate ?>
            <br>
            <table class="table table-bordered">
                <thead>
                    <tr style="background-color:#333;color:#fff">
                        <th>SN</th>
                        <th>ACCOUNT</th>
                        <th>SIDE / DATE</th>
                        <th>VOLUME</th>
                        <th>PRICE</th>
                        <th>DIRTY PRICE</th>
                        <th>AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($groupedData as $symbolData) {
                        foreach ($symbolData['rows'] as $row) {
                    ?>
                            <tr>
                                <td><?= $i++ . '. ' . htmlspecialchars($symbolData['symbol']) ?></td>
                                <td><?= htmlspecialchars($row['cd_code']) ?></td>
                                <td><?= htmlspecialchars($row['side']) ?> - <?= htmlspecialchars($row['order_date']) ?></td>
                                <td><?= number_format($row['lot_size_execute'], 2, ".", ",") ?></td>
                                <td><?= number_format($row['order_exe_price'], 2, ".", ",") ?></td>
                                <td><?= number_format($row['dirty_price'], 2, ".", ",") ?></td>
                                <td>Nu. <?= number_format($row['amount'], 2, ".", ",") ?></td>
                            </tr>
                    <?php
                        }
                    ?>
                        <tr style="font-weight:bold; background:#f5f5f5;">
                            <td>Total :</td>
                            <td>Buy Vol : <?= number_format($symbolData['buy_vol'], 0, ".", ",") ?></td>
                            <td>Sell Vol : <?= number_format($symbolData['sell_vol'], 0, ".", ",") ?></td>
                            <td colspan="2"></td>
                            <td>Total Trade</td>
                            <td>Nu. <?= number_format($symbolData['total_trade'], 2, ".", ",") ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-lg-12">
            &emsp;&emsp;
            <a href="bond_load_function.php?toDate1=<?= urlencode($toDate) ?>&fromDate1=<?= urlencode($fromDate) ?>&print_bond_trade_dtls=print_bond_trade" target="_blank" class="btn btn-default">
                <i class="fa fa-print"></i> Print
            </a>
        </div>
    </div>
    <br>
    <?php
}
elseif (isset($_GET['print_bond_trade_dtls']) && $_GET['print_bond_trade_dtls'] == 'print_bond_trade') {

    $fromDate = $_GET['fromDate1'];
    $toDate   = $_GET['toDate1'];

    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    echo '
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>Trade Confirmation Report</title>
        </head>

        <body onload="window.print();">
            <div class="wrapper">
                <section class="invoice">
                    <div class="page-header" style="display:flex; align-items:flex-start; width:100%;">
                        <div>
                            <img src="../../dist/img/Logo.png" style="height:50px;">
                        </div>
                        <div style="text-align:center; flex:1;">
                            <b>ROYAL SECURITIES EXCHANGE OF BHUTAN</b><br>
                            <div>Trade Confirmation Report</div>
                            <div style="font-size:12px; margin-top:5px;">
                                From: ' . htmlspecialchars($fromDate) . '
                                &nbsp;&nbsp;
                                To: ' . htmlspecialchars($toDate) . ' <br>
                                Report generated on: ' . $sysTime . ' by ' . htmlspecialchars($_SESSION['sess_username']) . '
                            </div>
                        </div>
                    </div>';
                    /*
                    |--------------------------------------------------------------------------
                    | SINGLE QUERY (remove N+1 queries)
                    |--------------------------------------------------------------------------
                    */
                    $sql = "
                        SELECT beo.symbol_id, s.symbol, beo.cd_code, beo.side, beo.order_date, beo.lot_size_execute, beo.order_exe_price, beo.dirty_price, (beo.lot_size_execute * beo.dirty_price) AS amount
                        FROM bond_executed_orders beo
                        INNER JOIN symbol s ON beo.symbol_id = s.symbol_id
                        WHERE beo.sub_user = :user_name AND beo.order_date BETWEEN :fdate AND :tdate
                        ORDER BY beo.symbol_id, beo.order_date
                    ";

                    $stmt = $dbh->prepare($sql);
                    $stmt->execute([
                        ':user_name'    => $username,
                        ':fdate' => $fromDate,
                        ':tdate' => $toDate
                    ]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    /*
                    |--------------------------------------------------------------------------
                    | GROUP DATA
                    |--------------------------------------------------------------------------
                    */
                    $data = [];

                    foreach ($rows as $r) {
                        $sid = $r['symbol_id'];
                        if (!isset($data[$sid])) {
                            $data[$sid] = [
                                'symbol'  => $r['symbol'],
                                'rows'    => [],
                                'buy'     => 0,
                                'sell'    => 0,
                                'total'   => 0
                            ];
                        }

                        $data[$sid]['rows'][] = $r;
                        $data[$sid]['total'] += $r['amount'];

                        if ($r['side'] == 'B') {
                            $data[$sid]['buy'] += $r['lot_size_execute'];
                        } else {
                            $data[$sid]['sell'] += $r['lot_size_execute'];
                        }
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | TABLE OUTPUT
                    |--------------------------------------------------------------------------
                    */
                    echo "
                    <table class='table' border='1' Width='100%'>
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>ACCOUNT</th>
                                <th>SIDE/DATE</th>
                                <th>VOLUME</th>
                                <th>PRICE</th>
                                <th>DIRTY PRICE</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>";
                        $i = 1;
                        foreach ($data as $d) {
                            foreach ($d['rows'] as $r) {
                                $amt = $r['amount'];
                                echo '
                                <tr>
                                    <td>' . $i++ . '. ' . $d['symbol'] . '</td>
                                    <td>' . $r['cd_code'] . '</td>
                                    <td>' . $r['side'] . ' - ' . $r['order_date'] . '</td>
                                    <td>' . number_format($r['lot_size_execute'], 2, ".", ",") . '</td>
                                    <td>' . $r['order_exe_price'] . '</td>
                                    <td>' . $r['dirty_price'] . '</td>
                                    <td>Nu. ' . number_format($amt, 2, ".", ",") . '</td>
                                </tr>';
                            }
                            echo '
                            <tr style="font-weight:bold;">
                                <td>Total :</td>
                                <td>Buy Vol: ' . number_format($d['buy'], 0, ".", ",") . '</td>
                                <td>Sell Vol: ' . number_format($d['sell'], 0, ".", ",") . '</td>
                                <td colspan="2"></td>
                                <td>Total Trade</td>
                                <td>Nu. ' . number_format($d['total'], 2, ".", ",") . '</td>
                            </tr>';
                        }
                        echo "
                        </tbody>
                    </table>

                </section>
            </div>
        </body>
    </html>";
}
else {

}

function accruedInterestACT365($lastCoupon, $tradeDate, $annualCoupon) {
    $d1 = new DateTime($lastCoupon);
    $d2 = new DateTime($tradeDate);
    $days = $d1->diff($d2)->days;
    return ($annualCoupon * $days) / 365;
}

function buildCashFlows($tradeDate, $maturityDate, $coupon, $faceValue) {
    $flows = [];
    $trade = new DateTime($tradeDate);
    $maturity = new DateTime($maturityDate);

    $year = (int)$trade->format('Y');

    for ($y = $year; $y <= (int)$maturity->format('Y'); $y++) {
        $couponDate = new DateTime("$y-06-05");
        if ($couponDate <= $trade) continue;
        if ($couponDate > $maturity) break;

        $t = $trade->diff($couponDate)->days / 365;
        $amount = ($couponDate == $maturity)
            ? $coupon + $faceValue
            : $coupon;

        $flows[] = [$t, $amount];
    }
    return $flows;
}

?>