<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment List</title>
    <?php 
        date_default_timezone_set("Asia/Thimphu");
        include ('CONNECTIONS/db.php');
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">
</head>
<body>
    <div class="container">
        <div class="col-sm-12 text-center">
            <h3>TBL Rights Issue Online Subscription Payment List</h3>
            <hr>
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
                    <button type="button" class="btn btn-primary" id="submit_id">Submit</button>
                    <button type="button" class="btn btn-success" id="generate_excel_id"> Excel</button>
                </div>
            </form>
        </div>
        <hr>
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id='table_id' class='table table-bordered table-striped' style='font-size:10.5px;'>
                    <thead>
                        <tr>
                            <th>SlNo</th>
                            <th>Order No</th>
                            <th>BFS Code</th>
                            <th>CD CODE</th>
                            <th>Vol</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>Msg Type</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>CID</th>
                            <th>Date</th>
                        </tr>
                     </thead>
                    <tbody id="payment_body_id">
                        <?php
                            $totalDeposited = 0;

                            $orders = $dbh->prepare("SELECT 
                                r.bfs_orderid, r.bfs_code, r.cd_code, r.vol_applied, r.price, r.amount, r.`type`, r.phone, r.email, r.name as cid_no, r.dateentry
                                FROM rights_issue_online_temp r
                                WHERE r.symbol_id = 20
                                AND r.bfs_code = '00'
                                AND r.vol_applied != 0
                                AND DATE(r.dateentry) > '2025-07-01'
                                GROUP BY r.bfs_orderid
                                ORDER BY r.id ASC 
                                LIMIT 100
                            ");
                            $orders->execute();
                            $ordersss = $orders->fetchAll(PDO::FETCH_ASSOC);
                            $i=1;
                            foreach($ordersss as $row){
                                $totalDeposited += $row['amount'];
                                echo'
                                <tr>
                                    <td>'.$i.'</td>
                                    <td>'.$row['bfs_orderid'].'</td>
                                    <td>'.$row['bfs_code'].'</td>
                                    <td>'.$row['cd_code'].'</td>
                                    <td>'.$row['vol_applied'].'</td>
                                    <td>'.$row['price'].'</td>
                                    <td>'.$row['amount'].'</td>
                                    <td>'.$row['type'].'</td>
                                    <td>'.$row['phone'].'</td>
                                    <td>'.$row['email'].'</td>
                                    <td>'.$row['cid_no'].'</td>
                                    <td>'.$row['dateentry'].'</td>
                                </tr>';
                                $i++;
                            }
                        ?>
                        <tr>
                            <td colspan="12">Total Deposited: = Nu. <strong><?php echo number_format($totalDeposited, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- <div class="col-lg-6 text-left">
            Total Deposited: = <?php echo number_format($totalDeposited, 2); ?>
        </div>
        <div class="col-lg-6 text-right">
            <form action="process/getExport_refundList.php" method="POST">
                <button type="submit" class="btn btn-dark" name="getExpoxt_payment_list" id="getExpoxt_payment_list"> Download Payment List</button>
            </form>
        </div> -->

    </div>
</body>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script type="text/javascript">
    /*$(document).ready(function(){
        $("#table_id").DataTable({
            "lengthMenu": [ [10, 20, 50, 100, 300, 500, -1], [10, 20, 50, 100, 300, 500, "All"] ]
        });
    });*/
</script>
<script type="text/javascript">
    $("#submit_id").click( function() {
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        const operation = 'generate_payment_list_gnbb001';

        if (start_date =="" || start_date == "") {
            alert("Required All");
            return false;
        }

        $.ajax({
            url: 'process/getExport_refundList.php',
            type: 'POST',
            data: {
                start_date: start_date,
                end_date: end_date,
                generate_payment_list_gnbb001: operation
            },
            dataType: 'html',
            success: function(response) {
                $("#payment_body_id").html(response);
            },
        });
    });

    $("#generate_excel_id").click( function() {
        const start_date = $("#start_date").val();
        const end_date = $("#end_date").val();
        const operation = 'getExpoxt_payment_list';

        if (start_date =="" || start_date == "") {
            alert("Required All");
            return false;
        }

        const url = `process/getExport_refundList.php?getExpoxt_payment_list=${operation}&start_date=${start_date}&end_date=${end_date}`;

        // Trigger the file download by navigating to the URL
        window.location.href = url;
    });
</script>

</html>
