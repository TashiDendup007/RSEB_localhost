<?php
    include('CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bond Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #loader {
            position: fixed;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #dashboard-content {
            display: none;
        }
    </style>
</head>
<body style="background-color: #e9f5ff;">

<div id="loader">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<h2 class="mb-4 text-center"><img src="img/gmc_logo.png" width="70"> GNBB Bond Subscription Summary <img src="img/logo.png" width="70"></h2>

<div class="container mt-3">
    <h4 class="mb-4 text-center">Subscription From Different Channels </h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <td>Channel</td>
                    <td>Symbol</td>
                    <td>Total Client</td>
                    <td>Face Value</td>
                    <td>Total Subscribed (Units)</td>
                    <td>Total Amount</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    $tableData = [
                        ['user_name' => 'NOT LIKE \'MEM%\'', 'type' => 'BOND', 'style' => '#087B30', 'label' => 'RSEB Online'],
                        ['user_name' => 'LIKE \'MEMRICB%\'', 'type' => 'BOND', 'style' => '#8691A7', 'label' => 'RICB'],
                        ['user_name' => 'LIKE \'MEMBNBL%\'', 'type' => 'BOND', 'style' => '#810987', 'label' => 'BNBL'],
                        ['user_name' => 'LIKE \'MEMBOBL%\'', 'type' => 'BOND', 'style' => '#840C46', 'label' => 'BOBL'],
                        ['user_name' => 'LIKE \'MEMBDBL%\'', 'type' => 'BOND', 'style' => '#A39229', 'label' => 'BDBL'],
                        ['user_name' => 'LIKE \'MEMDKLT%\'', 'type' => 'BOND', 'style' => '#8691A7', 'label' => 'DK BANK'],
                    ];

                    $totalVol = 0;
                    $grandTotAmt = 0;
                    $totBidder = 0;

                    foreach ($tableData as $data) {
                        $sql = "SELECT r.symbol_id,
                                    'GNBB001' AS symbol, r.face_value,
                                    COUNT(DISTINCT r.cd_code) AS cd_code, 
                                    SUM(r.order_size) AS vol, 
                                    SUM(r.bid_price * r.order_size) AS tValue,
                                    SUM(r.total_amount) AS totalAmt
                                FROM bond r 
                                WHERE r.user_name {$data['user_name']} AND r.symbol_id = 118 AND r.type = '{$data['type']}' 
                                GROUP BY r.symbol_id";

                        $save = $dbh->prepare($sql);
                        $save->execute();
                        
                        foreach ($save as $row) {
                            $totalVol += $row['vol'];
                            $grandTotAmt += $row['tValue'];
                            $totBidder += $row['cd_code'];

                            echo "
                            <tr>
                                <td>{$data['label']}</td>
                                <td>{$row['symbol']}</td>
                                <td>{$row['cd_code']}</td>
                                <td>" . number_format($row['face_value']) . "</td>
                                <td>" . number_format($row['vol']) . "</td>
                                <td>" . number_format($row['tValue'], 2) . "</td>
                            </tr>";
                        }
                    }
                ?>
                <tr>
                    <td colspan='2' style='text-align:right;'><strong>Total =></strong></td>
                    <td><strong><?= $totBidder; ?></strong></td>
                    <td></td>
                    <td><strong><?= number_format($totalVol); ?></strong></td>
                    <td><strong><?= number_format($grandTotAmt, 2); ?></strong></td>
                </tr>

            </tbody>
        </table>
    </div>
</div>


<!-- <div class="container mt-5">
    <h4 class="mb-4 text-center">Daily Subscription Summary</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Total Subscribed (Units)</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sql = "SELECT 
                            DATE_FORMAT(order_date, '%d %M %Y') AS order_day,
                            SUM(order_size) AS total_order_size,
                            SUM(order_size * face_value) AS total_amount
                        FROM bond
                        WHERE symbol_id = 118
                        GROUP BY DATE(order_date)
                        ORDER BY order_date DESC";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $total_amt = 0;
                    foreach ($results as $row) {
                        echo'
                        <tr>
                            <td>'.$row['order_day'].'</td>
                            <td>'.number_format($row['total_order_size']).'</td>
                            <td>'.number_format($row['total_amount'], 2).'</td>
                        </tr>
                        ';
                        $total_amt += $row['total_amount'];
                    }
                ?>
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total</strong></td>
                    <td><strong><?= number_format($total_amt, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div> -->

</body>
<script>
    window.addEventListener("load", function () {
        document.getElementById("loader").style.display = "none";
        document.getElementById("dashboard-content").style.display = "block";
    });
</script>
</html>
