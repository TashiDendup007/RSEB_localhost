<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Refund List</title>
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
        
        <div class="col-lg-12">
            <div class="table-responsive">
                
                    <?php
                        
                         $orders = $dbh->prepare("SELECT distinct corporate_announcement.symbol_id,symbol,year(record_date) as rc,corp_announcement_id 
                          from corporate_announcement,symbol where announcement_type=3 and symbol.symbol_id=corporate_announcement.symbol_id limit 2");
                        $orders->execute();
                         foreach($orders as $corp){

                          echo $corp['symbol'].' - '.$corp['rc'];
                          echo"<table id='' class='table table-bordered table-striped' style='font-size:10.5px;'>
                    <thead>
                        <tr>
                            <th>SlNo</th>
                            <th>NAME</th>
                            <th>ADDRESS</th>
                            <th>ID</th>
                            <th>VOLUME</th>
                            <th>AMT</th>
                            <th>PUS</th>
                            <th>SYMBOL</th>
                            <th>YEAR</th>
                        </tr>
                     </thead>
                    <tbody>";
                        $orders = $dbh->prepare("select  year(c.record_date) as year,sp.volume,sp.client_id,c.rate,c.rate*sp.volume/100 as amt,sy.paid_up_shares,sy.symbol from  corporate_announcement c, spot_date_holding sp,symbol sy where sp.corp_announcement_id=:ca and sp.corp_announcement_id=c.corp_announcement_id and sp.volume !=0 and sp.symbol_id=sy.symbol_id and c.announcement_type=3");
                        $orders->bindParam(':ca',$corp['corp_announcement_id']);
                        $orders->execute();
                        $i=1;
                         foreach($orders as $years){
                                $orders1 = $dbh->prepare("SELECT * FROM client_account where client_id=:cid");
                                $orders1->bindParam(':cid',$years['client_id']);
                                $orders1->execute();
                                //$i=1;
                                foreach($orders1 as $row){
                                    echo'
                                    <tr>
                                        <td>'.$i++.'</td>
                                        <td>'.$row['f_name'].' '.$row['l_name'].'</td>
                                        <td>'.$row['address'].' </td>
                                        <td>'.$row['ID'].' </td>
                                        <td>'.$years['volume'].'</td>
                                        <td>'.round($years['amt']).'</td>
                                        <td>'.round($years['paid_up_shares']).'</td>
                                        <td>'.$years['symbol'].'</td>
                                        <td>'.$years['year'].'</td>
                                       </tr>';                                    
                                }
                          }
                          echo' </tbody></table>';
                        }
                    ?>
                   
                
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
