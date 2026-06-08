<!DOCTYPE html>
<?php
include('CONNECTIONS/db.php');
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bond Dashboard - LED Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #FFF;
            font-family: 'Arial Black', sans-serif;
            margin: 0;
            padding: 0;
            width: 1020px;
            height: 460px;
            overflow: hidden;
        }
        .container {
            width: 100%;
            height: 100%;
            padding: 5px;
            position: relative;
        }
        .header {
            background-color: #003366;
            color: #ffffff;
            padding: 5px 10px;
            text-align: left;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 3px solid #FFD700;
            display: flex;
            align-items: center;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
        .header img {
            height: 30px;
            margin-right: 10px;
        }
        .header-text {
            flex-grow: 1;
            text-align: center;
        }
        .table-container {
            position: absolute;
            top: 50px;
            left: 0;
            width: 100%;
            height: 460px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            padding: 15px 5px;
            text-align: center;
            border: 1px solid #333;
            font-size: 1.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        th {
            background-color: #003366;
            color: #ffffff;
            font-size: 1.3rem;
            padding: 10px 5px;
        }
        tr:nth-child(even) {
            background-color: #205A8A;
        }
        tr:nth-child(odd) {
            background-color: #205A8A;
        }
        .total-row {
            background-color: #003366 !important;
            color: #ffffff;
            font-weight: bold;
        }
        .marquee {
            position: absolute;
            top: 40px;
            left: 0;
            width: 100%;
            background-color: #ffffff;
            color: #003366;
            padding: 3px 0;
            font-size: 1.1rem;
            font-weight: bold;
            text-align: center;
        }
        .blink {
            animation: blink-animation 1s steps(2, start) infinite;
        }
        @keyframes blink-animation {
            to { visibility: hidden; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="img/gmc_logo.png" alt="GMC Logo">
        <div class="header-text">GNBB BOND SUBSCRIPTION SUMMARY</div>
        <img src="img/logo.png" alt="GNBB Logo">
    </div>

    <div class="marquee">
        LIVE SUBSCRIPTION DATA <span class="blink">•</span> UPDATED: <?php echo date('d M Y H:i:s'); ?>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 20%">CHANNEL</th>
                    <th style="width: 10%">SYMBOL</th>
                    <th style="width: 15%">CLIENTS</th>
                    <th style="width: 15%">FACE VALUE</th>
                    <th style="width: 20%">UNITS</th>
                    <th style="width: 20%">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $tableData = [
                        ['user_name' => 'NOT LIKE \'MEM%\'', 'type' => 'BOND', 'style' => '#087B30', 'label' => 'RSEB ONLINE'],
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
                                <td><strong>{$data['label']}</strong></td>
                                <td>{$row['symbol']}</td>
                                <td>" . number_format($row['cd_code']) . "</td>
                                <td>" . number_format($row['face_value']) . "</td>
                                <td>" . number_format($row['vol']) . "</td>
                                <td>" . number_format($row['tValue'], 2) . "</td>
                            </tr>";
                        }
                    }
                ?>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL =></strong></td>
                    <td><strong><?= number_format($totBidder) ?></strong></td>
                    <td></td>
                    <td><strong><?= number_format($totalVol) ?></strong></td>
                    <td><strong><?= number_format($grandTotAmt, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Auto-refresh every 30 seconds
    setTimeout(function(){
        window.location.reload();
    }, 10000);
</script>

</body>
</html>