<?php
  date_default_timezone_set("Asia/Thimphu");
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="2")
  {
    header('Location: ../../access.php?err=2'); die();
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); die();
    }
  }
  $_SESSION['timeout'] = time();
  $username=$_SESSION['sess_username'];
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');

if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["tradeConfirmation"])) 
{
  $toDate = $_POST['toDate1'].' 23:59:00';
  $fromDate = $_POST['fromDate1'].' 00:00:00';
  $cd_code = $_POST['cdcode'];
  
  echo'
  <div class="col-lg-12">
    <div class="box-body table-responsive">';
      if($cd_code!=''){
        $query= $dbh->prepare('SELECT * from client_account where cd_code=:cd');
        $query->bindParam(':cd',$cd_code);
        $query->execute();
        $res=$query->fetch();
        $name=$res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];
        $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
        $val=$cd_code; 
      }else{
        $name=$_SESSION['sess_username'];
        $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
        $val=$username;
      }

      $detail= $dbh->prepare($sql);
      $detail->bindParam(':cd',$val);
      $detail->execute();
      $amt=$detail->fetch();
      $amount = $amt['sum'];
      echo'<b>Trade Confirmation</b><br> From : '.$fromDate.' - To : '.$toDate;
      echo'<br>Client Details : '.$name;

      $sql1=$dbh->prepare('SELECT price_discovered FROM rights_issue WHERE price_discovered !=0 and :fromDate <= order_date and order_date <= :toDate ');
      $sql1->bindParam(':fromDate',$fromDate);
      $sql1->bindParam(':toDate',$toDate);
      $sql1->execute();
      $p= $sql1->fetch();
      $priced = isset($p['price_discovered']) ? $p['price_discovered'] : 0;

      $sql2=$dbh->prepare('SELECT face_value FROM rights_issue WHERE face_value !=0 and  :fromDate <= order_date and order_date <= :toDate ');
      $sql2->bindParam(':fromDate',$fromDate);
      $sql2->bindParam(':toDate',$toDate);
      $sql2->execute();
      $f= $sql2->fetch();
      $fv = $f['face_value'];

      $query= $dbh->prepare("SELECT  a.*, symbol from rights_issue a join symbol b on a.symbol_id=b.symbol_id where  (a.cd_code=:cd and a.type='S' and :fromDate <= a.order_date and a.order_date <= :toDate) OR 
        (a.cd_code IS NOT NULL and a.renounce_cd_code=:cd and a.type='R' and  :fromDate <= a.order_date and a.order_date <= :toDate) OR (a.cd_code=:cd and a.type='B' and :fromDate <= a.order_date and a.order_date <= :toDate) OR (a.cd_code=:cd and a.type='O' and  :fromDate <= a.order_date and a.order_date <= :toDate)");
      $query->bindParam(':cd',$cd_code);
      $query->bindParam(':fromDate',$fromDate);
      $query->bindParam(':toDate',$toDate);
      $query->execute();
      $i=1;
      $total=0;$totalb=0;$totals=0;
      echo"
      <table class='table table-bordered'>
        <thead>
          <tr style='background-color:#333;color:#fff'>
            <th>SN</th>
            <th>Symbol</th>
            <th>Right Issue Type</th>
            <th>Trade Vol</th>
            <th>Price</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>";
        $t =0;
        $t1 =0;
        foreach($query as $res){
          echo'
          <tr>
            <td>'.$i++.'</td>
            <td>'.$res['symbol'].'</td>';
          if($res['type'] == 'S')
          {
            $type ="SUBSCRIBED";
            $order_size = $res['order_size'];
            $price = $res['face_value'];
            $total = $res['total_amount'];
            $commission=0;
          }else if($res['type'] == 'R'){
            $type ="RENOUNCED";
            $order_size = $res['order_size'];
            $price = $res['face_value'];
            $total = $res['total_amount'];
            $commission=0;
          }else if($res['type'] == 'B'){
            $type ="BID";
            $order_size = $res['allocated_size'];
            $price = $res['price_discovered'];
            $total = $res['allocated_size']*$res['price_discovered'];
            $face_value = $fv;
            $commission=$price*$order_size*0.02;
          }else{
            $type ="OFFER";
            $order_size = $res['order_size'];
            $price = $priced;
            $total = $priced*$order_size; 
            $face_value = $fv;
            $commission=($price-$face_value)*$order_size*0.5;
          }

          if($res['type'] == 'S'){
            echo '
            <td>'.$type.'</td>
            <td>'.$order_size.'</td>
            <td>'.$price.'</td>
            <td>Nu. '.number_format($total,2,".",",").'</td>
          </tr>';
          $t = $total+$commission;
          echo"
          <tr>
            <td></td><td></td>
            <td><b>Total Commission<b></td>
            <td><b>".number_format($commission,2,".",",")."</b></td>
            <td><b>Total Value<b></td>
            <td><b>".number_format($t,2,".",",")."</b></td>
          </tr>";
          }else if($res['type'] == 'R'){
          echo '
            <td>'.$type.'</td>
            <td>'.$order_size.'</td>
            <td>'.$price.'</td>
            <td>Nu. '.number_format($total,2,".",",").'</td>
          </tr>';
          $t = $total+$commission;
          echo"
          <tr>
            <td></td><td></td>
            <td><b>Total Commission<b></td>
            <td><b>".number_format($commission,2,".",",")."</b></td>
            <td><b>Total Value<b></td>
            <td><b>".number_format($t,2,".",",")."</b></td>
          </tr>";
          }else if($res['type'] == 'B'){
          echo'
            <td>'.$type.'</td>
            <td>'.$order_size.'</td>
            <td>'.$price.'</td>
            <td>Nu. '.number_format($total,2,".",",").'</td>
          </tr>';
          $t = $total+$commission;
          echo"
          <tr>
            <td></td><td></td>
            <td><b>Total Commission<b></td>
            <td><b>".number_format($commission,2,".",",")."</b></td>
            <td><b>Total Value<b></td>
            <td><b>".number_format($t,2,".",",")."</b></td>
          </tr>"; 
          }else{
          echo '
            <td>'.$type.'</td>
            <td>'.$order_size.'</td>
            <td>'.$price.'</td>
            <td>Nu. '.number_format($total,2,".",",").'</td>
          </tr>';
          $t = $total+$commission;
          echo"
          <tr>
            <td></td><td></td>
            <td><b>Total Commission<b></td>
            <td><b>".number_format($commission,2,".",",")."</b></td>
            <td><b>Total Value<b></td>
            <td><b>".number_format($t,2,".",",")."</b></td>
          </tr>";
          }
          $t1 += $t;
        }
          echo"
          <tr>
            <td><b>Total Payable/Receivable<b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>".number_format($t1,2,".",",")."</b></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>";
  echo'
  <div class="row no-print">
    <div class="col-lg-12">
    &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&rightstradeConfirmation=rightstradeConfirmation" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>
  </div><br>';                 
}
elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["fin_conf"])) 
{
  $toDate = $_POST['toDate1'].' 23:59:00';
  $fromDate = $_POST['fromDate1'].' 00:00:00';
  $cd_code = $_POST['cdcode'];
  echo'
  <div class="col-lg-12">
    <div class="box-body table-responsive">';
      if($cd_code!=''){
        $query= $dbh->prepare('SELECT * from client_account where  cd_code=:cd and user_name=:un');
        $query->bindParam(':cd',$cd_code);
        $query->bindParam(':un',$username);
        $query->execute();
        $res=$query->fetch();
        $name=$res['cd_code'].' / '.$res['f_name'].''.$res['l_name'].' / '.$res['phone'];
        $sql='SELECT * from bbo_finance where cd_code=:cd and :fromDate <= finance_date and finance_date <= :toDate';
        $val=$cd_code; 
      }else{
        $name=$_SESSION['sess_username'];
        $sql='SELECT * from bbo_finance where user_name=:cd and :fromDate <= finance_date and finance_date <= :toDate';
        $val=$username;
      }
      echo'<b>Account Activity</b><br> From : '.$fromDate.' - To : '.$toDate;
      echo'<br>Client Details : '.$name;
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
      echo"
      <table class='table table-bordered'>
        <thead>
          <tr style='background-color:#333;color:#fff'>
            <th>SN</th>
            <th>Remarks</th>
            <th>Operation</th> 
            <th>Date</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody style='font-size: 11px;'>";
          foreach($query as $res){
            if($res['flag']==0){$o='Debit';$dt=$dt+$res['amount'];}
            elseif($res['flag']==1){$o='Credit';$ct=$ct+$res['amount'];}
            elseif($res['flag']==2){$o='Sell';$st=$st+$res['amount'];}
            elseif($res['flag']==3){$o='Buy';$bt=$bt+$res['amount'];}
            elseif($res['flag']==4){$o='Commission';$cot=$cot+$res['amount'];}
            echo'
            <tr>
              <td>'.$i++.'</td>
              <td>'.$res['remarks'].'</td>
              <td>'.$o.'</td>
              <td>'.$res['finance_date'].'</td>
              <td>'.$res['amount'].'</td>
            </tr>';
          }
          $crt=($ct+$bt+$cot)*-1;
          $dbt=$dt+$st;
          $total=$crt-$dbt;
          echo"
          <tr>
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
        </tbody>
      </table>
    </div>
  </div>";  
  echo'
  <div class="row no-print">
    <div class="col-lg-12">
    &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&accountActivity=accountActivity" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>
  </div><br>';
}
elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["com"])) 
{
  $toDate = $_POST['toDate1'].' 23:59:00';
  $fromDate = $_POST['fromDate1'].' 00:00:00';
  $cd_code = $_POST['cdcode'];
  echo'
  <div class="col-xs-12">
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
      }else{
        $name=$_SESSION['sess_username'];
        $sql='SELECT * from bbo_finance where user_name=:cd and :fromDate <= finance_date 
        and finance_date <= :toDate and flag=4';
        $val=$username;
      }
      echo'<b>Commission</b><br> From : '.$fromDate.' - To : '.$toDate;
      echo'<br>Client Details : '.$name;
      $query= $dbh->prepare($sql);
      $query->bindParam(':cd',$val);
      $query->bindParam(':fromDate',$fromDate);
      $query->bindParam(':toDate',$toDate);
      $query->execute();
      $i=1;
      $s=0;
      $total=0;
      echo"
      <table class='table table-bordered'>
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
            <tr>
              <td>'.$i++.'</td>
              <td>'.$res['remarks'].'</td>
              <td>'.$o.'</td>
              <td>'.$res['finance_date'].'</td>
              <td>'.$res['amount'].'</td>
            </tr>';
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
        </tbody>
      </table>
    </div>
  </div>"; 
  echo'
  <div class="row no-print">
    <div class="col-xs-12">
      &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&commission=commission" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>
  </div><br>';              
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
  echo'
  <br><br>
  <section class="invoice" style="background:rgb(248, 249, 249);">
    <div class="row">
      <div class="col-lg-12">
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
      <div class="col-lg-12">
        <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : '.$fromDate.' TO : '.$toDate.'</div>
      </div>
    </div>';
    echo'
    <div class="row">
      <div class="col-lg-12 table-responsive">
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
          </thead>';
          $i=1;
          foreach($wc as $state)
          {                       
            if($state['type']=='S'){$side='SUBSCRIBE';}else if($state['type']=='R'){$side='RENOUNCE';}else{$side='BID';}
            echo'
            <tbody>
              <tr style="font-size: 70%;">
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
          echo'
          </tbody>
        </table>
      </div>
    </div>
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
  
  /*<img src="../../dist/img/Logo.png"> &emsp;<b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>*/
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
//To generate report for Share Auction
else if(isset($_POST["shareAuctionAudit"])) 
{
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00'; 

    /*$sql = "SELECT * FROM rights_issue WHERE '".$fromDate."' <= order_date AND order_date <= '".$toDate."' AND user_name 
      like '".substr($_SESSION['sess_username'],0,7)."%'"." AND order_size != 0";*/

    $sql = "SELECT r.*, c.f_name, c.l_name, c.ID -- , b.rate
        FROM rights_issue r 
        LEFT JOIN client_account c ON r.cd_code = c.cd_code 
        -- LEFT JOIN bbo_commission b on c.bro_comm_id = b.bro_comm_id
        WHERE '".$fromDate."' <= r.order_date AND r.order_date <= '".$toDate."' AND r.user_name LIKE '".substr($_SESSION['sess_username'],0,7)."%'"." AND r.order_size != 0";
    $wc= $dbh->prepare($sql);
   // $wc->bindParam(':fdate',$fromDate);
    //$wc->bindParam(':tdate',$toDate);
    //$wc->bindParam(':uname',substr($_SESSION['sess_username'],0,7));
    $wc->execute();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    echo'
    <br><br>
    <section class="invoice" style="background:rgb(248, 249, 249);">
    <div class="row">
      <div class="col-xs-12">
        <div class="page-header">
          &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
          <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
            <center>
              <div class="lead" style="font-size: 55%; margin-top:-25px;">Share Auction Audit Details</div> 
                <div class="lead" style="font-size: 40%;  margin-top:-25px;"> Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'
              </div>
            </center>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12">
        <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : '.$fromDate.' TO : '.$toDate.'</div>
      </div>
    </div>';
    echo'
    <div class="row">
      <div class="col-xs-12 table-responsive">
        <table class="table table-striped" id="saTableId">
          <thead style="background-color: #D6EAF8;">
          <tr style="font-size: 14px;">
            <th>Sl#</th>                    
            <th style="text-align:Center;">Type</th>
            <th style="text-align:Center;">CD Code</th>
            <th style="text-align:Center;">CID</th>
            <th style="text-align:Center;">Order Volume</th>
            <th style="text-align:Center;">Price</th>
            <th style="text-align:Center;">Amount</th>
            <th style="text-align:Center;">Commission</th>
            <th style="text-align:Center;">Grand Total</th>
            <th style="text-align:Center;">User Name</th>
            <th style="text-align:Center;">Order Time</th>
          </tr>
          </thead>
          <tbody style="font-size: 12px;">';
          $i=1;
          foreach($wc as $state)
          {
            if($state['type']=='S'){
              $side='SUBSCRIBE';
            }else if($state['type']=='R'){
              $side='RENOUNCE';
            }else if($state['type']=='SA'){
              $side='SHARE AUCTION';
            }else{
              $side='BID';
            }
            $total_amount = $state['order_size'] * $state['bid_price'];
            echo' 
            <tr>
              <td>'.$i.'</td>
              <td style="text-align:left;">'.$side.'</td>
              <td style="text-align:center;">'.$state['cd_code'].'</td>
              <td style="text-align:center;">'.$state['ID'].'</td>
              <td style="text-align:center;">'.number_format($state['order_size']).'</td>
              <td style="text-align:center;">'.number_format($state['bid_price'], 2).'</td>
              <td style="text-align:center;">'.number_format($total_amount, 2).'</td>
              <td style="text-align:center;">'.number_format($total_amount * 0.02, 2).'</td>
              <td style="text-align:center;">'.number_format(($total_amount * 0.02) + $total_amount, 2).'</td>
              <td style="text-align:left;">'.$state['user_name'].'</td>
              <td style="text-align:left;">'.$state['order_date'].'</td>
           </tr>';
           $i++;                   
          }
          echo'
          </tbody>
        </table>
      </section>
      <div class="row no-print">
        <div class="col-xs-12">
          &emsp;&emsp;<a href="rights_load.php?ge_export_shareAuc=ge_export_shareAuc&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
        </div>
      </div>
      <script type="text/javascript">
        $(document).ready(function() {
            $("#saTableId").DataTable({
              aLengthMenu: [[10, 50, 100, 200, -1], [10, 50, 100, 200, "All"]],
              iDisplayLength: 10
            });
        });
      </script>';
}
elseif(!empty($_GET['ge_export_shareAuc'])) 
{       
    $replace   = array("\n");
    $search  = array('');
    $toDate = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00';

    $sql = "SELECT r.*, c.f_name, c.l_name, c.ID -- , b.rate
          FROM rights_issue r 
          LEFT JOIN client_account c ON r.cd_code = c.cd_code 
          -- LEFT JOIN bbo_commission b on c.bro_comm_id = b.bro_comm_id
          WHERE '".$fromDate."' <= r.order_date AND r.order_date <= '".$toDate."' AND r.user_name LIKE '".substr($_SESSION['sess_username'],0,7)."%'"." AND r.order_size != 0";
    $wc= $dbh->prepare($sql);
    //$wc->bindParam(':fdate',$fromDate);
    //$wc->bindParam(':tdate',$toDate);
    //$wc->bindParam(':uname',$_SESSION['sess_username']);
    $wc->execute(); 

    $columnHeader = '';  
    $i=1;
    $columnHeader = "SNO" . "\t" . 
    "TYPE" . "\t". 
    "CD CODE" . "\t". 
    "CID NO" . "\t". 
    "ORDER VOLUME" . "\t". 
    "PRICE" . "\t". 
    "AMOUNT" . "\t" .   
    "COMMISSION" . "\t" .   
    "TOTAL AMOUNT" . "\t" .   
    "USER NAME" . "\t" . 
    "ORDER TIME" . "\t"; 
    $setData = '';  
    while ($rec=$wc->fetch()) { 

      $rowData = '';  
      $value = $i++ . 
      "\t". str_replace($search, $replace, $rec['type']) . 
      "\t". str_replace($search, $replace, $rec['cd_code']). 
      "\t". str_replace($search, $replace, $rec['ID']). 
      "\t". str_replace($search, $replace, number_format($rec['order_size'])).
      "\t". str_replace($search, $replace, number_format($rec['bid_price'], 2)). 
      "\t". str_replace($search, $replace, number_format($rec['order_size'] * $rec['bid_price'], 2)). 
      "\t". str_replace($search, $replace, number_format($rec['order_size'] * $rec['bid_price'] * 0.02, 2)). 
      "\t". str_replace($search, $replace, number_format(($rec['order_size'] * $rec['bid_price'] * 0.02) + $rec['order_size'] * $rec['bid_price'], 2)). 
      "\t". str_replace($search, $replace, $rec['user_name']). 
      "\t". str_replace($search, $replace, $rec['order_date']).
      "\t";  
      $rowData .= $value;  
      $setData .= trim($rowData) . "\n";     
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=Share_Auction_Report.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
else
{

}
?>

