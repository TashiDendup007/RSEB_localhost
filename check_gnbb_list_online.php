<?php
    include('CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bond check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
    <div class="col-sm-12 text-center">
        <h3>Payment List of the GNBB001 (GMC Bond)</h3>
        <form action="" method="POST" class="form-horizontal row justify-content-center align-items-center">
            <div class="col-lg-3 d-flex align-items-center mb-2">
                <label for="start_date" class="me-2 mb-0">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control">
            </div>
            <div class="col-lg-3 d-flex align-items-center mb-2">
                <label for="end_date" class="me-2 mb-0">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control">
            </div>
            <div class="col-lg-6 mb-2">
                <!-- <button type="button" class="btn btn-primary" id="submit_id">Submit</button> -->
                <button type="button" class="btn btn-success" id="generate_excel_id"> Excel</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <td>#</td>
                    <td>Symbol</td>
                    <td>CD Code</td>
                    <td>CID</td>
                    <td>Face Value</td>
                    <td>Order Size</td>
                    <td>Buy Vol</td>
                    <td>OS * FV</td>
                    <td>BOND Total Amount</td>
                    <td>BFS Total Amount</td>
                </tr>
            </thead>
            <tbody id="payment_body_id">
                <?php
                    $i = 1;
                    $total_amount_bond = 0;
                    $total_amount_bfs = 0;

                    $save = $dbh->prepare("SELECT r.symbol_id, 'GNBB001' AS symbol, r.face_value, r.cd_code, r.order_size, r.buy_vol, (r.bid_price * r.order_size) AS tValue, r.total_amount, r.cid_no
                            FROM bond r 
                            WHERE r.user_name NOT LIKE 'MEM%' AND r.symbol_id = 118 AND r.type = 'BOND' 
                            LIMIT 50
                            -- GROUP BY r.symbol_id
                    ");
                    $save->execute();
                    $rows = $save->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($rows as $row) {
                        $stmt = $dbh->prepare("SELECT SUM(b.amount) as tot_amount FROM bond_ipo_temp_dtls b WHERE b.symbol_id = 118 AND b.bfs_code = '00' AND b.name = ?");
                        $stmt->execute([$row['cid_no']]);
                        $bfs_amount = $stmt->fetchColumn();

                        $total_amount_bond += $row['total_amount'];
                        $total_amount_bfs += $bfs_amount;

                        echo "
                        <tr>
                            <td>{$i}</td>
                            <td>{$row['symbol']}</td>
                            <td>{$row['cd_code']}</td>
                            <td>{$row['cid_no']}</td>
                            <td>" . number_format($row['face_value']) . "</td>
                            <td>" . number_format($row['order_size']) . "</td>
                            <td>" . number_format($row['buy_vol']) . "</td>
                            <td>" . number_format($row['tValue']) . "</td>
                            <td>" . number_format($row['total_amount']) . "</td>
                            <td>" . number_format($bfs_amount) . "</td>
                        </tr>";
                        $i++;
                    }
                ?>
                <tr>
                    <td colspan='8' style='text-align:right;'><strong>Total =></strong></td>
                    <td><strong><?= number_format($total_amount_bond); ?></strong></td>
                    <td><strong><?= number_format($total_amount_bfs); ?></strong></td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

</body>
<script>
    window.addEventListener("load", function () {
        document.getElementById("loader").style.display = "none";
        document.getElementById("dashboard-content").style.display = "block";
    });

    $("#generate_excel_id").click( function() {
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        const operation = 'generate_excel_bond_check';

        if (start_date =="" || start_date == "") {
            alert("Required All");
            return false;
        }

        const url = `process/getExport_refundList.php?generate_excel_bond_check=${operation}&start_date=${start_date}&end_date=${end_date}`;

        // Trigger the file download by navigating to the URL
        window.location.href = url;
    });
</script>
</html>
