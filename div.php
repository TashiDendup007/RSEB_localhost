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
                <table id='table_id' class='table table-bordered table-striped' style='font-size:10.5px;'>
                    <thead>
                        <tr>
                            <th>SlNo</th>
                            <th>NAME</th>
                            <th>ADDRESS</th>
                            <th>VOLUME</th>
                            <th>AMT</th>
                            <th>SYMBOL</th>
                            <th>YEAR</th>
                        </tr>
                     </thead>
                    <tbody>
                    <?php
                        $i=0;

                        $sys = array(2,3,4,5,6,7,9,10,12,13,14,15,16,17,18,19,20,34);
                        foreach ($sys as $symbol){
                            $UnknownTable = array(2018,2019,2020,2021,2022);
                        foreach($UnknownTable as $years){
                            $orders = $dbh->prepare("SELECT c.symbol_id,YEAR(c.record_date) as year from corporate_announcement c where c.announcement_type=3 and year(c.record_date)=".$years." and c.symbol_id=".$symbol." and c.`type`='FINAL'");
 //$orders->bindParam(':year',$years);
                        $orders->execute();
                        //print_r($orders);
                        foreach($orders as $row1){

 $orders1 = $dbh->prepare("SELECT concat(a.f_name, '', a.l_name) as name,a.address,c.volume,round(c.volume*10*ca.rate/100,2) as AMT, s.symbol  from spot_date_holding  c , client_account a,symbol s,corporate_announcement ca
 where c.announcement_type=3 and year(c.record_date)=".$years."
 and c.client_id=a.client_id and c.symbol_id=s.symbol_id and s.symbol_id=".$symbol." and ca.corp_announcement_id=c.corp_announcement_id
 order by c.volume  desc limit 20 ");
 /*$orders1->bindParam(':year',$row1['symbol_id']);
 $orders1->bindParam(':sys',$row1['year']);*/
                        $orders1->execute();
                        $i=1;
                        foreach($orders1 as $row){
                            echo'
                            <tr>
                                <td>'.$i++.'</td>
                                <td>'.$row['name'].'</td>
                                <td>'.$row['address'].' </td>
                                <td>'.$row['volume'].'</td>
                                <td>'.$row['AMT'].'</td>
                                <td>'.$row['symbol'].'</td>
                                <td>'.$years.'</td>
                               </tr>'; 

                           
                        }




             }
                        }

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
