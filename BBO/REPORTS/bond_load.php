<?php
date_default_timezone_set("Asia/Thimphu");

include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');

if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["subscription"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $cd_code = $_POST['cdcode'];
    echo'
    <div class="col-lg-12">
      <div class="box-body">';
      if($cd_code!=''){
        $query= $dbh->prepare('SELECT * FROM client_account WHERE cd_code=:cd');
        $query->bindParam(':cd',$cd_code);
        $query->execute();
        $res = $query->fetch();
        $name = $res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];

        $sql = 'SELECT sum(amount) as sum FROM rights_finance WHERE cd_code=:cd ';
        $val = $cd_code; 
      } else {
          $name = $_SESSION['sess_username'];
          $sql = 'SELECT sum(amount) as sum FROM rights_finance WHERE cd_code=:cd ';
          $val = $username;
      }
      echo'BOND Subscription Details <br> From : '.$fromDate.' - To : '.$toDate;
      echo'<br>Client Details : '.$name;

      $query = $dbh->prepare("SELECT s.symbol, i.type, i.order_size, i.face_value, i.bid_price, i.total_amount 
          FROM bond i  
          JOIN symbol s on i.symbol_id = s.symbol_id
          WHERE i.cd_code = :cd 
          AND i.order_date BETWEEN :fromDate AND :toDate
      ");
      $query->bindParam(':cd', $cd_code);
      $query->bindParam(':fromDate', $fromDate);
      $query->bindParam(':toDate', $toDate);
      $query->execute();
      $rows = $query->fetchAll(PDO::FETCH_ASSOC);
      echo"
      <table class='table table-bordered'>
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
          $i=1; $t=1;
          foreach($rows as $res) {
            echo'
            <tr>
              <td>'.$i.'</td>
              <td>'.$res['symbol'].'</td>
              <td>'.$res['type'].'</td>
              <td>'.$res['order_size'].'</td>
              <td>'.$res['face_value'].'</td>
              <td>'.$res['total_amount'].'</td>
            <tr>
            <tr>
              <td></td>
              <td></td>
              <td><b>Total Commission<b></td>
              <td><b>0</b></td>
              <td><b>Total Value<b></td>
              <td><b>'.$res['total_amount'].'</b></td>
            </tr>';
            $t = $res['total_amount'];
            $i++;
          }
          echo"
          <tr>
            <td><b>Total Payable/Receivable<b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>".number_format($t,2,".",",")."</b></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>";
  echo'
  <div class="row no-print">
    <div class="col-lg-12">
      &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&bondsubscription=bondsubscription" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>
  </div>
  <br>';                 
} elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["ipotradeConfirmation"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $cd_code = $_POST['cdcode'];
    $symbol_id = $_POST['symbol_id'];
    echo'
    <div class="col-xs-12">
      <div class="box-body">';
        if ($cd_code != '') {
          $query = $dbh->prepare('SELECT * FROM client_account WHERE cd_code=:cd');
          $query->bindParam(':cd',$cd_code);
          $query->execute();
          $res = $query->fetch();
          $name = $res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];
          $sql = 'SELECT sum(amount) as sum FROM rights_finance WHERE cd_code=:cd ';
          $val = $cd_code; 
        } else {
          $name = $_SESSION['sess_username'];
          $sql = 'SELECT sum(amount) as sum FROM rights_finance WHERE cd_code=:cd ';
          $val = $username;
        }
        $i = 1;
        $detail = $dbh->prepare($sql);
        $detail->bindParam(':cd',$val);
        $detail->execute();
        $amt = $detail->fetch();
        $amount = $amt['sum'];
        echo 'BOND IPO Trade Confirmation <br> From : '.$fromDate.' - To : '.$toDate;
        echo '<br>Client Details : '.$name;

        $query = $dbh->prepare("SELECT s.symbol,s.name,i.type,i.allocated_size,i.face_value,i.bid_price,i.total_amount,i.price_discovered 
          FROM bond i 
          JOIN symbol s ON i.symbol_id = s.symbol_id
          WHERE i.symbol_id=:sym AND i.cd_code=:cd AND i.order_date BETWEEN :fromDate AND  :toDate");
        $query->bindParam(':cd', $cd_code);
        $query->bindParam(':sym', $symbol_id);
        $query->bindParam(':fromDate', $fromDate);
        $query->bindParam(':toDate', $toDate);
        $query->execute();

        echo"
        <table class='table table-bordered'>
          <thead>
            <tr style='background-color:#333;color:#fff'>
              <th>SN</th>
              <th>Symbol</th>
              <th>Issue Type</th>
              <th>Trade Vol</th>
              <th>Price</th>
              <th>Qouted Rate (%)</th>
              <th>Discovered Rate (%)</th>
              <th>Value</th>
            </tr>
          </thead>
        <tbody>";
        $totlValue = 0;
        foreach ($query as $res) { 
          $totlValue =  $res['allocated_size'] * $res['face_value'];                
          echo'
          <tr>
            <td>'.$i++.'</td>
            <td>'.$res['symbol'].'</td>
            <td>'.$res['type'].'-'.$res['name'].'</td>
            <td>'.$res['allocated_size'].'</td>
            <td>'.$res['face_value'].'</td>
            <td>'.$res['bid_price'].'</td>
            <td>'.$res['price_discovered'].'</td>
            <td>'.number_format($totlValue) .'</td>
          </tr>'; 
          echo"
          <tr>
            <td></td><td></td>
            <td><b>Total Commission<b></td><td></td><td></td>
            <td><b>".number_format(0,2,".",",")."</b></td>
            <td><b>Total Value<b></td>
            <td><b>".number_format($totlValue,2,".",",")."</b></td>
          </tr>";
          }
          echo"
          <tr>
            <td><b>Total Payable/Receivable<b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>".number_format($totlValue,2,".",",")."</b></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>";
echo'<div class="row no-print">
      <div class="col-xs-12">
        &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&symbol_id='.$symbol_id.'&toDate='.$toDate.'&fromDate='.$fromDate.'&bondtradeConfirmation=bondtradeConfirmation" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
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
        $wc= $dbh->prepare("SELECT i.*,c.ID,c.f_name,c.l_name FROM ipo i, client_account c where i.cd_code=c.cd_code and :fdate <= order_date and order_date <= :tdate ");
        $wc->bindParam(':fdate',$fromDate);
        $wc->bindParam(':tdate',$toDate);
        $wc->execute(); 
    $columnHeader = '';  
    $i=1;
    /*<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>*/
    $columnHeader = "SNO" . "\t" . "TYPE" . "\t". "CD CODE" . "\t". "NAME" . "\t". "CID"  . "\t". "ORDER VOLUME" . "\t". "PRICE" . "\t". "TOTAL AMOUNT" . "\t" . "USER NAME" . "\t" . "ORDER TIME" . "\t"; 
    $setData = '';  
    while ($rec=$wc->fetch()) { 
           if($wc->rowCount() <= 0) 
           {}
    
              $p = $rec['bid_price'];
              $totala = $rec['bid_price']*$rec['order_size'];
  
            $rowData = '';  
            $value = $i++ . "\t ". str_replace($search,$replace,$rec['type']) . "\t". str_replace($search,$replace,$rec['cd_code']).  "\t". str_replace($search,$replace,$rec['f_name'].' '.$rec['l_name']).  "\t". str_replace($search,$replace,$rec['ID']).  "\t". str_replace($search,$replace,$rec['order_size']) . 
             "\t". str_replace($search,$replace,$p) . "\t". str_replace($search,$replace,$totala)
               . "\t". str_replace($search,$replace,$rec['user_name']) . "\t" . str_replace($search,$replace,$rec['order_date']) . "\t";  
            $rowData .= $value;  
            $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=IPO audit.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif(!empty($_POST["bond_allocation_summary"])) {
    $symbol_id = $_POST['symbol_id'];
    $mem_code = substr($username, 0, 7);
    echo"
    <div class='col-lg-12'>
      <div class='box-body table-responsive'>
        <table id='table__table__id' class='table table-bordered table-striped'>
          <thead>
            <tr style='background-color:#333; color:#fff'>
              <th>#</th>
              <th>CD CODE</th>
              <th>SYMBOL</th>
              <th>NAME</th>
              <th>BID VOL</th>
              <th>RATE</th>
              <th>AMT</th>
              <th>ALLOCATED UNIT(s)</th>
              <th>Username</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>";
          $i = 1;
          $select = $dbh->prepare("SELECT b.cd_code, b.bid_price, b.order_size, b.allocated_size, s.symbol, s.name, (s.face_value * b.allocated_size) AS amt, b.user_name, b.order_date 
              FROM bond b 
              LEFT JOIN symbol s ON s.symbol_id = b.symbol_id 
              WHERE s.symbol_id = ? 
              -- AND SUBSTR(b.user_name, 1, 7) = ? 
              ORDER BY bid_price ASC
          ");
          $select->bindParam(1, $symbol_id);
          // $select->bindParam(2, $mem_code);
          $select->execute();
          $rows = $select->fetchAll(PDO::FETCH_ASSOC);
          foreach($rows as $res) {
            echo'
            <tr>
              <td>'.$i.'</td>
              <td>'.$res['cd_code'].'</td>
              <td>'.$res['symbol'].'</td>
              <td>'.$res['name'].'</td>
              <td>'.number_format($res['order_size']).'</td>
              <td>'.$res['bid_price'].'</td>
              <td>'.number_format($res['amt']).'</td>
              <td>'.number_format($res['allocated_size']).'</td>
              <td>'.$res['user_name'].'</td>
              <td>'.$res['order_date'].'</td>
            </tr>';
            $i++;
          }
          echo"
        </tbody>
      </table>
    </div>
  </div>";
  echo'
  <script type="text/javascript">
    $( function () {
      $("#table__table__id").DataTable();
    });
  </script>

  <div class="row no-print">
    <div class="col-lg-6 text-left">
      &emsp;&emsp;<a href="loadReportPrint.php?bond_allocation_summary=bond_allocation_summary&symbol_id='.$symbol_id.'&mem_code='.$mem_code.'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>

    <div class="col-lg-6 text-right">
      <a href="bond_load.php?bond_summary_excel=bond_summary_excel&symbol_id='.$symbol_id.'&mem_code='.$mem_code.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
    </div>

  </div>
  <br>';                 
}
elseif(!empty($_GET['bond_summary_excel'])) 
{       
      $replace   = array("\n");
      $search  = array('');

      $symbol_id = $_GET['symbol_id'];
      $mem_code = $_GET['mem_code'];

      $select = $dbh->prepare("SELECT CASE
                WHEN a.acc_type = 'I' THEN CONCAT(a.f_name, COALESCE(a.l_name, ''))
                  ELSE a.f_name
               END AS full_name,
               b.cd_code, b.bid_price, b.price_discovered, b.order_size, b.allocated_size, s.symbol, s.name, (s.face_value * b.allocated_size) AS amt, b.user_name, b.order_date 
          FROM bond b 
          JOIN client_account a on b.cd_code = a.cd_code
          JOIN symbol s ON s.symbol_id = b.symbol_id 
          WHERE s.symbol_id = ? 
              -- AND SUBSTR(b.user_name, 1, 7) = ? 
              ORDER BY bid_price ASC
      ");
      $select->bindParam(1, $symbol_id);
      // $select->bindParam(2, $mem_code);
      $select->execute();

      $columnHeader = '';  
      $columnHeader = "SNO\t CD CODE\t NAME\t SYMBOL\t BOND Name\t BID VOL\t PRICE\t RATE\t AMOUNT\t ALLOCATED UNIT(s)\t USERNAME\t Date\t"; 
      $setData = ''; 
      $i=1;
      while ($rec = $select->fetch()) { 
          if($select->rowCount() <= 0) 
          {}
          $rowData = '';  
          $value = $i++ . "\t".
            str_replace($search, $replace, $rec['cd_code']) . "\t". 
            str_replace($search, $replace, $rec['full_name']) . "\t". 
            str_replace($search, $replace, $rec['symbol']).  "\t". 
            str_replace($search, $replace, $rec['name']).  "\t". 
            str_replace($search, $replace, $rec['order_size']).  "\t". 
            str_replace($search, $replace, $rec['bid_price']) ."\t". 
            str_replace($search, $replace, $rec['price_discovered']). "\t". 
            str_replace($search, $replace, $rec['amt']). "\t". 
            str_replace($search, $replace, $rec['allocated_size']) . "\t" .
            str_replace($search, $replace, $rec['user_name']) . "\t" .
            str_replace($search, $replace, $rec['order_date']) . "\t";  
          $rowData .= $value;  
          $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=IPO_bond_summary_allocate.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["bond__subscription__summary"])) 
{
    $symbol_id = $_POST['symbol_id'];
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $part_code = substr($username, 0, 7);
    echo"
    <div class='col-lg-12'>
      <div class='box-body table-responsive'>
        <table id='table_summary_id' class='table table-bordered'>
          <thead>
            <tr style='background-color:#333;color:#fff'>
              <th>SN</th>
              <th>Name</th>
              <th>CID</th>
              <th>CD CODE</th>
              <th>Phone</th>
              <th>Symbol</th>
              <th>Issue Type</th>
              <th>Unit(s)</th>
              <th>Face Value</th>
              <th>Amount</th>
              <th>Broker</th>
              <th>Username</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>";
          $i = 1;
          $total = 0;
          $query= $dbh->prepare("SELECT s.symbol, i.type, i.order_size, i.face_value, i.bid_price, i.total_amount, a.title, CONCAT_WS(' ', a.f_name, a.l_name) AS name, a.ID, a.phone, a.email, i.cd_code, i.user_name, i.order_date 
                  FROM bond i 
                  JOIN client_account a ON i.cd_code = a.cd_code 
                  JOIN symbol s on i.symbol_id = s.symbol_id
                  WHERE 
                    DATE(i.order_date) BETWEEN ? AND ?
                    -- AND i.user_name LIKE ? 
                    AND i.symbol_id = ?

          ");
          // $query->execute([$fromDate, $toDate, "$part_code%", $symbol_id]);
          $query->execute([$fromDate, $toDate, $symbol_id]);
          $rows = $query->fetchAll(PDO::FETCH_ASSOC);
          foreach($rows as $res) {
            echo'
            <tr>
              <td>'.$i.'</td>
              <td>'.$res['name'].'</td>
              <td>'.$res['ID'].'</td>
              <td>'.$res['cd_code'].'</td>
              <td>'.$res['phone'].'</td>
              <td>'.$res['symbol'].'</td>
              <td>'.$res['type'].'</td>
              <td>'.$res['order_size'].'</td>
              <td>'.$res['face_value'].'</td>
              <td>'.$res['total_amount'].'</td>
              <td>'.substr($res['user_name'], 0, 7).'</td>
              <td>'.$res['user_name'].'</td>
              <td>'.$res['order_date'].'</td>
            </tr>';
            $total += $res['total_amount'];
            $i++;
          }
          echo"
        </tbody>
      </table>
    </div>
  </div>";
  echo'
  <div class="row">
    <div class="col-lg-6 text-left">
      Total Amount => <b>'.number_format($total,2,".",",").'</b>
    </div>
    <div class="col-lg-6 text-right">
      <a href="bond_load.php?toDate='.$toDate.'&fromDate='.$fromDate.'&part_code='.$part_code.'&symbol_id='.$symbol_id.'&extract_excel_bond_summary=extract_excel_bond_summary" target="_blank" class="btn btn-success"><i class="fa fa-file"></i> Download Excel</a>
    </div>
  </div><br>
  <script type="text/javascript">
    $(document).ready(function() {
      $("#table_summary_id").DataTable();
    });
  </script>';
  exit;
}
elseif (isset($_GET['extract_excel_bond_summary'])) {
    $fromDate = $_GET['fromDate'];
    $toDate = $_GET['toDate'];
    $part_code = $_GET['part_code'];
    $symbol_id = $_GET['symbol_id'];

    $replace = array("\n", "\r\n", "\r");
    $search = array('', '', '');

    $stmt = $dbh->prepare("
          SELECT s.symbol, i.type, i.order_size, i.face_value, i.bid_price, i.total_amount, a.title, CONCAT_WS(' ', a.f_name, a.l_name) AS name, a.ID, a.phone, a.email, i.cd_code, i.user_name, i.order_date, u.name AS user_fl_name 
          FROM bond i 
          JOIN client_account a ON i.cd_code = a.cd_code 
          JOIN symbol s on i.symbol_id = s.symbol_id
          LEFT JOIN users u ON i.user_name = u.username
          WHERE 
            DATE(i.order_date) BETWEEN ? AND ?
            -- AND i.user_name LIKE ? 
            AND i.symbol_id = ?
    ");
    // $stmt->execute([$fromDate, $toDate, "$part_code%", $symbol_id]);
    $stmt->execute([$fromDate, $toDate, $symbol_id]);

    $columnHeader = '';
    // $columnHeader .= "\tDate Generated :".date('Y-m-d') ."\t\n\n";
    $columnHeader .= "Sl\t Name\t CID\t CD Code\t Phone\t Symbol\t Issue Type\t Unit\t Face Value\t Amount\t Broker\t Broker\t User Name\t  Date\t\n";

    $setData = '';

    $i = 1;
    while ($rec = $stmt->fetch()) {
        $rowData = '';
        $value = $i++ .
            "\t" . str_replace($search, $replace, $rec['name']) .
            "\t" . str_replace($search, $replace, $rec['ID']) .
            "\t" . str_replace($search, $replace, $rec['cd_code']) .
            "\t" . str_replace($search, $replace, $rec['phone']) .
            "\t" . str_replace($search, $replace, $rec['symbol']) .
            "\t" . str_replace($search, $replace, $rec['type']) .
            "\t" . str_replace($search, $replace, $rec['order_size']) .
            "\t" . str_replace($search, $replace, $rec['face_value']) .
            "\t" . str_replace($search, $replace, $rec['total_amount']) .
            "\t" . str_replace($search, $replace, substr($rec['user_name'], 0, 7)) .
            "\t" . str_replace($search, $replace, $rec['user_name']) .
            "\t" . str_replace($search, $replace, $rec['user_fl_name']) .
            "\t" . str_replace($search, $replace, $rec['order_date']) .
            "\t\n";
        $rowData .= $value;
        $setData .= trim($rowData) . "\n";
    }
    // Concatenate header, data, and additional rows
    $dataToExport = $columnHeader . $setData;

    // Set headers for Excel download
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Bond_Subscription_Summary.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output data
    echo ucwords($dataToExport);
}
else
{

}
?>

