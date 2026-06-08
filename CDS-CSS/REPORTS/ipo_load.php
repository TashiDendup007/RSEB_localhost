<?php
date_default_timezone_set("Asia/Thimphu");
include('../FILES/sessionStartFile_cdscss.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["subscription"])) 
{
           $toDate = $_POST['toDate1'].' 23:59:00';
           $fromDate = $_POST['fromDate1'].' 00:00:00';
           $cd_code = $_POST['cdcode'];
            echo '<div class="col-xs-12">
                  <div class="box-body">';
                  if($cd_code!=''){
                $query= $dbh->prepare('SELECT * from client_account where cd_code=:cd');
                $query->bindParam(':cd',$cd_code);
                $query->execute();
                $res=$query->fetch();
                  $name=$res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];
                  $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
                  $val=$cd_code; 
              }
              else{
                  $name=$_SESSION['sess_username'];
                  $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
                  $val=$username;
              }
                echo 'IPO Subscription Details  <br> From : '.$fromDate.' - To : '.$toDate;
                echo '<br>Client Details : '.$name;

                $query= $dbh->prepare("SELECT s.symbol,i.type,i.order_size,i.face_value,i.bid_price,i.total_amount from ipo i,symbol s where i.symbol_id=s.symbol_id and i.cd_code=:cd and :fromDate <= i.order_date and i.order_date <= :toDate");
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':fromDate',$fromDate);
                $query->bindParam(':toDate',$toDate);
                $query->execute();
                $i=1;
                echo"<table class='table table-bordered'>
                <thead>
                <tr style='background-color:#333;color:#fff'>
                <th>SN</th>
                <th>Symbol</th>
                <th>Issue Type</th>
                <th>Demand Vol</th>
                <th>Price</th>
                <th>Value</th>
                </tr>
                </thead>
                <tbody>";
                $t =0;
                 foreach($query as $res){         
                    echo'
                    <tr>
                        <td>'.$i++.'</td>
                        <td>'.$res['symbol'].'</td>
                        <td>'.$res['type'].'</td>
                        <td>'.$res['order_size'].'</td>
                        <td>'.$res['face_value'].'</td>
                        <td>'.$res['total_amount'].'</td>
                    <tr>
                     <tr>
                      <td></td><td></td>
                      <td><b>Total Commission<b></td>
                      <td><b>0</b></td>
                      <td><b>Total Value<b></td>
                      <td><b>'.$res['total_amount'].'</b></td>
                      </tr>
                      ';
                       $t =$res['total_amount'];
                    
                  }
                  echo"<tr>
                  <td><b>Total Payable/Receivable<b></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>".number_format($t,2,".",",")."</b></td>
                  </tbody></table></div></div>";
                  
    echo '  <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&iposubscription=iposubscription" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>
            <br>';                 
}
else if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["ipotradeConfirmation"])) 
{
           $toDate = $_POST['toDate1'].' 23:59:00';
           $fromDate = $_POST['fromDate1'].' 00:00:00';
           $cd_code = $_POST['cdcode'];
            echo '<div class="col-xs-12">
                  <div class="box-body">';
                  if($cd_code!=''){
                $query= $dbh->prepare('SELECT * from client_account where cd_code=:cd');
                $query->bindParam(':cd',$cd_code);
                $query->execute();
                $res=$query->fetch();
                  $name=$res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];
                  $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
                  $val=$cd_code; 
              }
              else{
                  $name=$_SESSION['sess_username'];
                  $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
                  $val=$username;
              }
                $i=1;
                $detail= $dbh->prepare($sql);
                $detail->bindParam(':cd',$val);
                $detail->execute();
                $amt=$detail->fetch();
                $amount = $amt['sum'];
                echo 'IPO Trade Confirmation  <br> From : '.$fromDate.' - To : '.$toDate;
                echo '<br>Client Details : '.$name;

                $query= $dbh->prepare("SELECT s.symbol,i.type,i.allocated_size,i.face_value,i.bid_price,i.total_amount from ipo i,symbol s where i.symbol_id=s.symbol_id and i.cd_code=:cd and :fromDate <= i.order_date and i.order_date <= :toDate");
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':fromDate',$fromDate);
                $query->bindParam(':toDate',$toDate);
                $query->execute();
                echo"<table class='table table-bordered'>
                <thead>
                <tr style='background-color:#333;color:#fff'>
                <th>SN</th>
                <th>Symbol</th>
                <th>Issue Type</th>
                <th>Trade Vol</th>
                <th>Price</th>
                <th>Value</th>
                </tr>
                </thead>
                <tbody>";
                 foreach($query as $res){ 
                    $totlValue =  $res['allocated_size']* $res['bid_price'];                
                    echo'
                    <tr>
                    <td>'.$i++.'</td>
                    <td>'.$res['symbol'].'</td>
                    <td>'.$res['type'].'</td>
                    <td>'.$res['allocated_size'].'</td>
                    <td>'.$res['bid_price'].'</td>
                    <td>'.$totlValue .'</td>
                    </tr>'; 

                      echo"<tr>
                  <td></td><td></td>
                  <td><b>Total Commission<b></td>
                  <td><b>".number_format(0,2,".",",")."</b></td>
                  <td><b>Total Value<b></td>
                  <td><b>".number_format($totlValue,2,".",",")."</b></td>
                  </tr>
                  ";
                  }

                  echo"<tr>
                  <td><b>Total Payable/Receivable<b></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>".number_format($totlValue,2,".",",")."</b></td>
                  </tbody></table></div></div>";
                  
    echo '  <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&ipotradeConfirmation=ipotradeConfirmation" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>
            <br>';                 
}
elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["fin_conf"])) 
{
           $toDate = $_POST['toDate1'].' 23:59:00';
           $fromDate = $_POST['fromDate1'].' 00:00:00';
           $cd_code = $_POST['cdcode'];
            echo '<div class="col-xs-12">
                  <div class="box-body">';
                  if($cd_code!=''){
                $query= $dbh->prepare('SELECT * from client_account where  cd_code=:cd and user_name=:un');
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':un',$username);
                $query->execute();
                $res=$query->fetch();
                  $name=$res['cd_code'].' / '.$res['f_name'].''.$res['l_name'].' / '.$res['phone'];
                  $sql='SELECT * from bbo_finance where cd_code=:cd and :fromDate <= finance_date and finance_date <= :toDate';
                  $val=$cd_code; 
              }
              else{
                $name=$_SESSION['sess_username'];
                  $sql='SELECT * from bbo_finance where user_name=:cd and :fromDate <= finance_date and finance_date <= :toDate';
                  $val=$username;
              }
                echo 'Account Activity  <br> From : '.$fromDate.' - To : '.$toDate;
                echo '<br>Client Details : '.$name;
                $query= $dbh->prepare($sql);
                $query->bindParam(':cd',$val);
                $query->bindParam(':fromDate',$fromDate);
                $query->bindParam(':toDate',$toDate);
                $query->execute();
                $i=1;
                $dt=0;
                $ct=0;
                $st=0;
                $bt=0;
                $cot=0;
                echo"<table class='table table-bordered'>
                <thead>
                <tr style='background-color:#333;color:#fff'>
                <th>SN</th>
                <th>Remarks</th>
                <th>Operation</th> 
                <th>Date</th>
                <th>Amount</th>
                </tr>
                </thead>
                <tbody>";
                foreach($query as $res){
                  if($res['flag']==0){$o='Debit';$dt=$dt+$res['amount'];}
                  elseif($res['flag']==1){$o='Credit';$ct=$ct+$res['amount'];}
                  elseif($res['flag']==2){$o='Sell';$st=$st+$res['amount'];}
                  elseif($res['flag']==3){$o='Buy';$bt=$bt+$res['amount'];}
                  elseif($res['flag']==4){$o='Commission';$cot=$cot+$res['amount'];}
                    echo'
                    <tr><td>'.$i++.'</td>
                    <td>'.$res['remarks'].'</td>
                    <td>'.$o.'</td>
                    <td>'.$res['finance_date'].'</td>
                    <td>'.$res['amount'].'</td></tr>';
                  }
                  $crt=($ct+$bt+$cot)*-1;
                  $dbt=$dt+$st;
                  $total=$crt-$dbt;
                  echo"<tr>
                  <td><b>Total credit<b></td>
                  <td><b>".number_format($crt,2,".",",")."</b></td>
                  <td><b>Total debit<b></td>
                  <td><b>".number_format($dbt,2,".",",")."</b></td>
                  <td></td>
                  </tr>
                   <tr>
                  <td><b>Difference<b></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>".number_format($total,2,".",",")."</b></td>
                  </tr>
                  </tbody></table></div></div>";  
    echo '  <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&accountActivity=accountActivity" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>
            <br>';                
}
elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["com"])) 
{
           $toDate = $_POST['toDate1'].' 23:59:00';
           $fromDate = $_POST['fromDate1'].' 00:00:00';
           $cd_code = $_POST['cdcode'];
            echo '<div class="col-xs-12">
                  <div class="box-body">';
                $query= $dbh->prepare('SELECT * from client_account where  cd_code=:cd and user_name=:un');
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':un',$username);
                $query->execute();
                $res=$query->fetch();
                    if($cd_code!=''){
                $query= $dbh->prepare('SELECT * from client_account where  cd_code=:cd and user_name=:un');
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':un',$username);
                $query->execute();
                $res=$query->fetch();
                  $name=$res['cd_code'].' / '.$res['f_name'].''.$res['l_name'].' / '.$res['phone'];
                  $sql='SELECT * from bbo_finance where cd_code=:cd and :fromDate <= finance_date 
                  and finance_date <= :toDate and flag=4';
                  $val=$cd_code; 
              }
              else{
                $name=$_SESSION['sess_username'];
                  $sql='SELECT * from bbo_finance where user_name=:cd and :fromDate <= finance_date 
                  and finance_date <= :toDate and flag=4';
                  $val=$username;
              }
                echo 'Commission <br> From : '.$fromDate.' - To : '.$toDate;
                echo '<br>Client Details : '.$name;
                $query= $dbh->prepare($sql);
                $query->bindParam(':cd',$val);
                $query->bindParam(':fromDate',$fromDate);
                $query->bindParam(':toDate',$toDate);
                $query->execute();
                $i=1;
                $s=0;
                $total=0;
                echo"<table class='table table-bordered'>
                <thead>
                <tr style='background-color:#333;color:#fff'>
                <th>SN</th>
                <th>Remarks</th>
                <th>Operation</th> 
                <th>Date</th>
                <th>Amount</th>
                </tr>
                </thead>
                <tbody>";
                foreach($query as $res){
                   if($res['flag']==4)
                    {
                      $o='Commission';
                      
                    }
                    echo'
                    <tr><td>'.$i++.'</td>
                    <td>'.$res['remarks'].'</td>
                    <td>'.$o.'</td>
                    <td>'.$res['finance_date'].'</td>
                    <td>'.$res['amount'].'</td></tr>';
                    $total=$total+$res['amount'];
                  }
                  
                  echo"
                  <tr>
                  <td><b>Total<b></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>".number_format($total,2,".",",")."</b></td>
                  </tr>
                  </tbody></table></div></div>"; 
           echo '  <div class="row no-print">
                    <div class="col-xs-12">
                      &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&commission=commission" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
                    </div>
                    </div>
                    <br>';              
}
else if(isset($_POST["rightsaudit"])) 
{
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00'; 
    $wc= $dbh->prepare("SELECT * FROM rights_issue where :fdate <= order_date and order_date <= :tdate ");
    $wc->bindParam(':fdate',$fromDate);
    $wc->bindParam(':tdate',$toDate);
    $wc->execute();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
        echo '
        <br><br>
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Orders Audit Details</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-12">
                <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : '.$fromDate.' TO : '.$toDate.'</div>
              </div>
            </div>';
            echo'<div class="row">
                      <div class="col-xs-12 table-responsive">
                        <table class="table  table-striped">
                          <thead style="background-color: #D6EAF8; font-size: 80%;">
                          <tr>
                            <th>Sl#</th>                    
                            <th style="text-align:right;">Type</th>
                            <th style="text-align:right;">CD Code</th>
                            <th style="text-align:right;">Renounce CD Code</th>
                            <th style="text-align:right;">Order Volume</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:right;">User Name</th>
                            <th style="text-align:right;">Order Time</th>
                          </tr>
                          </thead>
                          ';
                      $i=1;
            foreach($wc as $state)
            {                       
                    if($state['type']=='S'){$side='SUBSCRIBE';}else if($state['type']=='R'){$side='RENOUNCE';}else{$side='BID';}
                    echo' <tbody>   <tr style="font-size: 70%;">
                                 <td>'.$i.'</td>
                                 <td style="text-align:right;">'.$side.'</td>
                                 <td style="text-align:right;">'.$state['cd_code'].'</td>
                                 <td style="text-align:right;">'.$state['renounce_cd_code'].'</td>
                                 <td style="text-align:right;">'.$state['order_size'].'</td>';
                                 if($side == 'BID')
                                 {
                                  echo '<td style="text-align:right;">'.$state['bid_price'].'</td>';
                                 }
                                 else
                                 {
                                  echo '<td style="text-align:right;">'.$state['face_value'].'</td>';
                                 }
                                 echo'
                                 <td style="text-align:right;">'.$state['user_name'].'</td>
                                 <td style="text-align:right;">'.$state['order_date'].'</td>
                             </tr>';
                             $i=$i+1;                   
            }
             echo'</tbody>
                    </table>
                   
                 </section>
                 <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="rights_load.php?ge_export=ge_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
            </div>
            </div>';
}
elseif(!empty($_GET['ge_export'])) 
{       
      $replace   = array("\n");
      $search  = array('');
      $toDate = $_GET['toDate'].' 23:59:00';
      $fromDate = $_GET['fromDate'].' 00:00:00';
        $wc= $dbh->prepare("SELECT * FROM rights_issue where :fdate <= order_date and order_date <= :tdate ");
        $wc->bindParam(':fdate',$fromDate);
        $wc->bindParam(':tdate',$toDate);
        $wc->execute(); 
    $columnHeader = '';  
    $i=1;
    /*<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>*/
    $columnHeader = "SNO" . "\t" . "TYPE" . "\t". "CD CODE" . "\t". "RENOUNCE CD CODE" . "\t". "ORDER VOLUME" . "\t". "PRICE" . "\t". "TOTAL AMOUNT" . "\t" . "AVAILABLE RIGHTS" . "\t" . "USER NAME" . "\t" . "ORDER TIME" . "\t"; 
    $setData = '';  
    while ($rec=$wc->fetch()) { 
           if($wc->rowCount() <= 0) 
           {}
            if($rec['type'] == 'B')
            {
              $p = $rec['bid_price'];
              $totala = $rec['bid_price']*$rec['order_size'];
            }
            else
            {
              $p = $rec['face_value'];
              $totala = $rec['total_amount'];
            }
            $rowData = '';  
            $value = $i++ . "\t ". str_replace($search,$replace,$rec['type']) . "\t". str_replace($search,$replace,$rec['cd_code'])
             . "\t". str_replace($search,$replace,$rec['renounce_cd_code']) . "\t". str_replace($search,$replace,$rec['order_size']) . 
             "\t". str_replace($search,$replace,$p) . "\t". str_replace($search,$replace,$totala)
              . "\t". str_replace($search,$replace,$rec['available_rights']) . "\t". str_replace($search,$replace,$rec['user_name']) . "\t" . str_replace($search,$replace,$rec['order_date']) . "\t";  
            $rowData .= $value;  
            $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=RIGHTS.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
else if(isset($_POST["ipoaudit"])) 
{
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00'; 
    $wc= $dbh->prepare("SELECT i.*,c.ID,c.f_name,c.l_name FROM ipo i, client_account c where i.cd_code=c.cd_code and :fdate <= order_date and order_date <= :tdate ");
    $wc->bindParam(':fdate',$fromDate);
    $wc->bindParam(':tdate',$toDate);
    $wc->execute();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
        echo '
        <br><br>
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">IPO Orders Audit Details</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-12">
                <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : '.$fromDate.' TO : '.$toDate.'</div>
              </div>
            </div>';
            echo'<div class="row">
                      <div class="col-xs-12 table-responsive">
                        <table class="table  table-striped">
                          <thead style="background-color: #D6EAF8; font-size: 80%;">
                          <tr>
                            <th>Sl#</th>                    
                            <th style="text-align:right;">Type</th>
                            <th style="text-align:right;">CD Code</th>
                            <th style="text-align:right;">Name</th>
                            <th style="text-align:right;">CID</th>
                            <th style="text-align:right;">Order Volume</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:right;">Order Time</th>
                          </tr>
                          </thead>
                          ';
                      $i=1;
            foreach($wc as $state)
            {                       
                    echo' <tbody>   <tr style="font-size: 70%;">
                                 <td>'.$i.'</td>
                                 <td style="text-align:right;">'.$state['type'].'</td>
                                 <td style="text-align:right;">'.$state['cd_code'].'</td>
                                 <td style="text-align:right;">'.$state['f_name']. ' '.$state['l_name'].'</td>
                                 <td style="text-align:right;">'.$state['ID'].'</td>
                                 <td style="text-align:right;">'.$state['order_size'].'</td>
                                 <td style="text-align:right;">'.$state['bid_price'].'</td>
                                 <td style="text-align:right;">'.$state['order_date'].'</td>
                             </tr>';
                             $i=$i+1;                   
            }
             echo'</tbody>
                    </table>
                   
                 </section>
                 <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="ipo_load.php?ipoaudit_export=ipoaudit_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
            </div>
            </div>';
}
elseif(!empty($_GET['ipoaudit_export'])) 
{       
    $replace   = array("\n");
    $search  = array('');
    $toDate = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00';

    $wc = $dbh->prepare("SELECT i.type, i.cd_code, i.order_size, i.bid_price, i.order_date, c.ID, c.f_name, c.l_name 
        FROM ipo i 
        JOIN client_account c ON i.cd_code = c.cd_code
        WHERE order_date BETWEEN :fdate AND :tdate
    ");
    $wc->bindParam(':fdate', $fromDate);
    $wc->bindParam(':tdate', $toDate);
    $wc->execute(); 

    $columnHeader = '';  
    $i = 1;
    $columnHeader = "SNO\t TYPE\t CD CODE\t NAME\t CID\t ORDER VOLUME\t PRICE\t TOTAL AMOUNT\t USER NAME\t ORDER TIME\t"; 
    $setData = '';  
    while ( $rec=$wc->fetch()) {
      $p = $rec['bid_price'];
      $totala = $rec['bid_price'] * $rec['order_size'];
      $rowData = '';  
      $value = $i++ . "\t ". 
      str_replace($search,$replace,$rec['type']) . "\t". 
      str_replace($search,$replace,$rec['cd_code']).  "\t". 
      str_replace($search,$replace,$rec['f_name'].' '.$rec['l_name']).  "\t". 
      str_replace($search,$replace,$rec['ID']).  "\t". 
      str_replace($search,$replace,$rec['order_size']) . "\t". 
      str_replace($search,$replace,$p) . "\t". 
      str_replace($search,$replace,$totala). "\t". 
      str_replace($search,$replace,$rec['user_name']) . "\t" . 
      str_replace($search,$replace,$rec['order_date']) . "\t";  
      $rowData .= $value;  
      $setData .= trim($rowData) . "\n";     
    }
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=IPO audit.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
else
{

}
?>

