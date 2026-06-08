<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>P001 Refund List</title>
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
            <form action="process/getExport_refundList.php" method="POST">
                <button type="submit" class="btn btn-dark" name="getExpoxt_refundList_p001" id="getExpoxt_refundList_p001"> Download Refund List</button>
            </form>
        </div>
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id='table_id' class='table table-bordered table-striped' style='font-size:10.5px;'>
                    <thead>
                        <tr>
                            <th>SlNo</th>
                            <th>CD CODE</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Account</th>
                            <th>Symbol</th>
                            <th>Allocated</th>
                            <th>Bid Price</th>
                            <th>Total Deposit</th>
                            <th>Amount Subscribed</th>
                            <th>Refund</th>
                            <th>UserName</th>
                        </tr>
                     </thead>
                    <tbody>
                    <?php
                        $orders = $dbh->prepare("SELECT a.cd_code, a.order_size, a.bid_price, a.allocated_size, a.user_name, a.symbol_id, b.f_name, b.l_name, b.bank_account, b.email, b.phone, a.price_discovered, c.symbol 
                            FROM rights_issue a, client_account b, symbol c 
                            WHERE a.user_name LIKE  'p001%'
                            AND a.symbol_id=c.symbol_id
                            AND a.cd_code=b.cd_code LIMIT 100 OFFSET 0");
                        $orders->execute();
                        $i=1;
                        foreach($orders as $row){
                            $orders = $dbh->prepare("SELECT SUM(amount) AS total FROM rights_issue_online_temp WHERE symbol_id=:id AND cd_code=:cd");
                            $orders->bindParam(':id', $row['symbol_id']);
                            $orders->bindParam(':cd', $row['cd_code']);
                            $orders->execute();
                            $data = $orders->fetch();

                            $total=$row['allocated_size']*$row['price_discovered'];
                            $commission=$row['allocated_size']*$row['price_discovered']*0.01;
                            $GT = $total+$commission;
                            $refund = $data['total']-$GT;
                            echo'
                            <tr>
                                <td>'.$i.'</td>
                                <td>'.$row['cd_code'].'</td>
                                <td>'.$row['f_name'].' '.$row['l_name'].'</td>
                                <td>'.$row['email'].'</td>
                                <td>'.$row['phone'].'</td>
                                <td>'.$row['bank_account'].'</td>
                                <td>'.$row['symbol'].'</td>
                                <td>'.$row['allocated_size'].'</td>
                                <td>'.$row['bid_price'].'</td>
                                <td>'.$data['total'].'</td>
                                <td>'.$GT.'</td>
                                <td>'.$refund.'</td>
                                <td>'.$row['user_name'].'</td>
                            </tr>';
                            $i++;
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#table_id").DataTable({
            "lengthMenu": [ [20, 50, 100, 300, 500, -1], [20, 50, 100, 300, 500, "All"] ]
        });
    });
</script>
</html>
