<?php
date_default_timezone_set("Asia/Thimphu");
include 'db.php';
include 'sanitize.php';



 if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["dTradeDetails"])) 
{
      

            $toDate = $_POST['toDate1'].' 23:59:00';
            $fromDate = $_POST['fromDate1'].' 00:00:00';
          
            echo '<div class="col-xs-12">
                  <div class="box-body">';
                echo 'Summary of IPO<br> From : '.$fromDate.' - To : '.$toDate;
                  $executed_orders= $dbh4->prepare('SELECT * from ipo where created_date >= :fdate  and created_date <= :tdate order by created_date ASC');
                  $executed_orders->bindParam(':fdate',$fromDate);
                  $executed_orders->bindParam(':tdate',$toDate);                
                  $executed_orders->execute();
                  echo"<table class='table table-bordered'>";
                  echo "<thead><tr style='background-color:#333;color:#fff'>
                 
                  <th>Name</th>
                  <th>CID</th>
                  <th>E-mail</th>
                  <th>Amount</th>
                  <th>Volume</th>
                  <th>Reference No</th>
                  <th>Address</th>
                   <th>Account</th>
                  <th>DATE</th>
                  </tr></thead><tbody>";
              
                  foreach($executed_orders as $res1){
                    echo'<tr>
                        <td>'.$res1['name'].'</td>
                        <td>'.$res1['cid'].'</td>
                        <td>'.$res1['email'].'</td>
                        <td>'.$res1['amount'].'</td>
                        <td>'.$res1['vol'].'</td>
                        <td>'.$res1['journal'].'</td>
                        <td>'.$res1['address'].'</td>
                        <td>'.$res1['account'].'</td>
                        <td>'.$res1['created_date'].'</td></tr>';
                          
                  }
                  
                 echo" </tbody></table>";
                echo '
            </div>
          </div>
          <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="reportProcess.php?zge_export=zge_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
              
            </div>
            </div>
            <br>';
}
else if (!empty($_GET['zge_export'])){

    $replace   = array("\n","\r\n","\r");
        $search    = array('','','');
        $fromDate  = $_GET['fromDate'];
        $toDate    = $_GET['toDate'];
       
        $executed_orders= $dbh4->prepare('SELECT * from ipo where created_date >= :fdate  and created_date <= :tdate order by created_date ASC');
                  $executed_orders->bindParam(':fdate',$fromDate);
                  $executed_orders->bindParam(':tdate',$toDate);
                 
                  $executed_orders->execute();
    $columnHeader = '';  
    $i=1;
    $columnHeader = "SNO" . "\t". "Name" . "\t" . "CID" . "\t". "EMAIL" . "\t". "PHONE" . "\t". "AMOUNT" . "\t". "VOLUME" . "\t". "REF No" . "\t". "ADDRESS" . "\t"."Account"."\t". "DATE". "\t"; 
    $setData = '';  
    while ($rec=$executed_orders->fetch()) { 
           if($executed_orders->rowCount() <= 0) 
           {}
            $rowData = '';  
            $value = $i++ . "\t ". str_replace($search,$replace,$rec['name']). "\t" .str_replace($search,$replace,trim($rec['cid'])." \t".$rec['email'] ." \t".$rec['phone_no']."\t".$rec['amount'])
             . "\t". str_replace($search,$replace,$rec['vol']) . "\t". str_replace($search,$replace,$rec['journal']) . 
             "\t". str_replace($search,$replace,$rec['address']) ."\t ". str_replace($search,$replace,$rec['account']). "\t". str_replace($search,$replace,$rec['created_date'])
              ."\t";  
            $rowData .= $value;  
            $setData .= trim($rowData) . "\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=DetailedtradeDetails.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}



else{

}

?>