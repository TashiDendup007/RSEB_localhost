<?php
session_start();
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
$username = $_SESSION['sess_username'];
$participant_code = substr($username, 0, 7);

if(!empty($_GET["tradeConfirmation"])) 
{
  $toDate = $_GET['toDate'].' 23:59:00';
  $fromDate = $_GET['fromDate'].' 00:00:00'; 
  $cd_code = $_GET['cd'];
  $sec_type = $_GET['sec_type'];
  
  /*SELECT a.f_name,a.l_name,a.ID,b.name,a.address 
    FROM client_account a,adm_institution b 
    WHERE a.cd_code=:cd AND a.institution_id= b.institution_id*/
  $wc = $dbh->prepare("
      SELECT a.cd_code, a.f_name, a.l_name, a.phone, a.bank_account, b.bank_short_name, a.ID, a.email, i.name, i.gst_register 
      FROM client_account a
      LEFT JOIN banks b ON a.bank_id = b.bank_id
      LEFT JOIN adm_institution i ON a.institution_id = i.institution_id 
      WHERE a.cd_code = :cd
  ");
  $wc->bindParam(':cd', $cd_code);
  $wc->execute();
  $state = $wc->fetch();
  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");
  echo'
  <html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Trade Confirmation Report</title>
    </head>
    <body onload="window.print();">
      <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
          <div class="row">
            <div class="col-lg-12">
              <div class="page-header">
                &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                 <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Trade Confirmation Report</div> 
                 <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                 Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">CD Code : '.$cd_code.'</div>
              <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].', BROKER : '.$state['name'].'</div>
            </div>
          </div>';
          $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';
          
          $sql = "SELECT a.lot_size_execute, a.order_exe_price, a.order_date, a.side, b.symbol ";
          if ($sec_type != 'OS') {
            $sql .= ", a.dirty_price, a.accur_rate, a.ytm ";
          }
          $sql .= "FROM {$table_name} a 
                JOIN symbol b ON a.symbol_id = b.symbol_id 
                WHERE cd_code = :cd AND a.order_date BETWEEN :fromDate AND :toDate";

          $query = $dbh->prepare($sql);
          $query->bindParam(':cd', $cd_code);
          $query->bindParam(':fromDate', $fromDate);
          $query->bindParam(':toDate', $toDate);
          $query->execute();
          $i=1;
          $total=0; $totalb=0; $totals=0;
          echo"
          <table class='table table-bordered'>
            <thead>
              <tr style='background-color:#333;color:#fff'>
                <th>SN</th>
                <th>Symbol</th>
                <th>Side</th>
                <th>Trade Vol</th>
                <th>Price</th>";
                if ($sec_type != 'OS') {
                  echo"<th>Dirty Price</th>";
                }
                echo"
                <th>Value</th>
              </tr>
            </thead>
            <tbody>";
              foreach($query as $res){
                $total1 = ($sec_type != 'OS') ? ($res['lot_size_execute'] * $res['dirty_price']) : ($res['lot_size_execute'] * $res['order_exe_price']);
                $total = $total + $total1;
                echo'
                <tr>
                  <td>'.$i++.'</td>
                  <td>'.$res['symbol'].'</td>
                  <td>'.$res['side'].'</td>
                  <td>'.$res['lot_size_execute'].'</td>
                  <td>'.$res['order_exe_price'].'</td>';
                  if ($sec_type != 'OS') {
                    echo'<td>' . $res['dirty_price'] . '</td>';
                  }
                  echo'
                  <td>'.number_format($total1,2,".",",").'</td>
                </tr>';
                if($res['side'] == 'B'){ $totalb += $total1; }
                if($res['side'] == 'S'){ $totals += $total1; }
              }

              // get commission
              $to_com = 0;
              $un = substr($username,0,7);
              if ($sec_type === 'OS') {
                $b_commis = client_commission_multiple_brokers($cd_code,$un);
                $to_com = round(($total * $b_commis) / 100, 2);
              } else {
                $stmt = $dbh->prepare("
                      SELECT SUM(b.amount) AS tot_com
                      FROM bbo_finance b 
                      LEFT JOIN symbol s ON b.symbol_id = s.symbol_id
                      WHERE b.flag = 4 AND b.symbol_id != 0 AND s.security_type IN ('GB', 'CB')
                      AND b.cd_code = ? AND b.finance_date BETWEEN ? AND ? 
                      AND substr(b.user_name, 1, 7) = ? 
                ");
                $stmt->execute([$cd_code, $fromDate, $toDate, $un]);
                $to_com = $stmt->fetchColumn();
              }
              $gst_amt = round($to_com * 0.05, 2);
              
              $totalpr = 0;
              if ($state['gst_register'] == 'Y') {
                $totalpr = $totals - ($totalb + abs($to_com) + abs($gst_amt));
              } else {
                $totalpr = $totals - ($totalb +  abs($to_com));
              }
              echo"
              <tr>
                <td><b>Total Buy Value<b></td>
                <td><b>".number_format($totalb,2,".",",")."</b></td>
                <td><b>Total Sell Value<b></td>
                <td><b>".number_format($totals,2,".",",")."</b></td>
                <td><b>Total Commission<b></td>
                <td colspan='3'><b>".number_format(abs($to_com), 2, ".", ",")."</b></td>
              </tr>";
              if ($state['gst_register'] == 'Y') {
                  echo"
                  <tr>
                    <td colspan='4'></td>
                    <td><b>GST<b></td>
                    <td colspan='3'><b>".number_format(abs($gst_amt), 2, ".", ",")."</b></td>
                  </tr>";
              }
              echo"
              <tr>
                <td colspan='5' style='text-align: center;'>
                  <b>Total Payable/Receivable<b>
                </td>
                <td colspan='3'>
                  <b>Nu. ".number_format($totalpr, 2 ,".",",")."</b>
                </td>
              </tr>
            </tbody>
          </table>
        </section>    
      </div>
    </body>
  </html>";
}
else if(!empty($_GET["pledgeActivity"])) 
{
  $pledgeContCode=$_GET['pledgeContCode'];
  $wc= $dbh->prepare("SELECT c.pledge_contract, c.pledge_name, a.cd_code, a.f_name, a.l_name, a.ID FROM cds_pledge_contract c,client_account a where c.cd_code=a.cd_code AND c.pledge_contract=:plcntr");
  $wc->bindParam(':plcntr',$pledgeContCode);
  $wc->execute();
  $state=$wc->fetch();
  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");
  echo'
  <html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pledge Contract</title>
  </head>
  <body onload="window.print();">
    <div class="wrapper">
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;
              <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Pledge Contract</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">Contract Name : '.$state['pledge_name'].'</div>
            <div class="lead" style="font-size: 70%; margin-top:-10px;">Contract Code : '.$state['pledge_contract'].'</div>
            <div class="lead" style="font-size: 70%; margin-top:-15px;">Client Details : CD Code: '.$state['cd_code'].', Name:'.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'</div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table  table-striped" >
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th>Sl#</th>
                  <th>Security Symbol/Name</th>
                  <th>Volume</th>
                  <th>Pledgee</th>
                  <th>Market Price</th>
                </tr>
              </thead>
              <tbody>';
              $wc= $dbh->prepare("SELECT distinct symbol_id from cds_pledge where pledge_contract=:contCode");
              $wc->bindParam(':contCode',$pledgeContCode);
              $wc->execute();
              $i=1;
              foreach($wc as $state)
              {
                $wc1= $dbh->prepare("SELECT s.symbol,s.name, c.pledgee,sum(c.pledge_volume) as volume,m.market_price from cds_pledge c, market_price m,symbol s where c.symbol_id=m.symbol_id and c.symbol_id=s.symbol_id and c.symbol_id=:sid and c.pledge_contract=:contCode");
                $wc1->bindParam(':sid',$state['symbol_id']);
                $wc1->bindParam(':contCode',$pledgeContCode);
                $wc1->execute();  
                foreach($wc1 as $state1)
                {
                  if($state1['volume'] == 0){}
                  else{
                    echo'
                    <tr style="font-size: 70%;">
                      <td>'.$i.'</td>                            
                      <td>'.$state1['symbol'].'/'.$state1['name'].'</td>
                      <td>'.$state1['volume'].'</td>
                      <td>'.$state1['pledgee'].'</td>
                      <td>'.$state1['market_price'].'</td>
                    </tr>';
                    $i=$i+1;
                  }
                }                                                   
              }
            echo'
            </tbody>
          </table>
        </section>      
      </div>
    </body>
  </html>';
}
else if(!empty($_GET["accountActivity"])) 
{
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    $cd_code = $_GET['cd'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name,a.address from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd_code);
    $wc->execute();
    $state=$wc->fetch();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Finance Activity Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Finance Activity Report</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
             if($cd_code!=''){
                  $query= $dbh->prepare('SELECT * from client_account where  cd_code=:cd and user_name=:un');
                  $query->bindParam(':cd',$cd_code);
                  $query->bindParam(':un',$username);
                  $query->execute();
                  $res=$query->fetch();
                  $name=$res['cd_code'].' / '.$res['f_name'].' '.$res['l_name'].' / '.$res['phone'];
                  $sql='SELECT * from bbo_finance where cd_code=:cd and :fromDate <= finance_date and finance_date <= :toDate';
                  $val=$cd_code; 
              }
              else{
                  $name=$_SESSION['sess_username'];
                  $sql='SELECT * from bbo_finance where user_name=:cd and :fromDate <= finance_date and finance_date <= :toDate';
                  $val=$username;
              }     
            echo'
            <div class="row">
              <div class="col-xs-12">
                  <div class="lead" style="font-size: 70%; margin-top:-10px;">Client Details : '.$name.'</div>
                </div>
            </div>';
            $query= $dbh->prepare($sql);
            $query->bindParam(':cd',$val);
            $query->bindParam(':fromDate',$fromDate);
            $query->bindParam(':toDate',$toDate);
            $query->execute();
            $i = 1;
            $s = 0;
            $dt = 0;
            $ct = 0;
            $st = 0;
            $bt = 0;
            $cot = 0;

            echo'
            <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table table-bordered table-striped" >
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                    <tr>
                        <th>Sl#</th>
                        <th>Remarks</th>
                        <th>Operation</th> 
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>';                                
                    foreach($query as $res){
                      /*if($res['flag']==0){$o='Debit';$dt=$dt+$res['amount'];}
                      elseif($res['flag']==1){$o='Credit';$ct=$ct+$res['amount'];}
                      elseif($res['flag']==2){$o='Sell';$st=$st+$res['amount'];}
                      elseif($res['flag']==3){$o='Buy';$bt=$bt+$res['amount'];}
                      elseif($res['flag']==4){$o='Commission';$cot=$cot+$res['amount'];}*/

                      $operations = [
                          0 => ['name' => 'Debit', 'total' => &$dt],
                          1 => ['name' => 'Credit', 'total' => &$ct],
                          2 => ['name' => 'Sell', 'total' => &$st],
                          3 => ['name' => 'Buy', 'total' => &$bt],
                          4 => ['name' => 'Commission', 'total' => &$cot],
                      ];

                      $flag = $res['flag'];

                      if (isset($operations[$flag])) {
                          $operation = $operations[$flag];
                          $o = $operation['name'];
                          $operation['total'] += $res['amount'];
                      }

                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>'.$res['remarks'].'</td>
                        <td>'.$o.'</td>
                        <td>'.$res['finance_date'].'</td>
                        <td>'.$res['amount'].'</td>
                      </tr>';
                    }
                    $crt = ($ct + $bt + $cot) * -1;
                    $dbt = $dt + $st;
                    $total = $crt - $dbt;
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
            </div>
          </section>    
        </div>
        </body>
      </html>";
}
else if(!empty($_GET["commission"])) 
{
    $toDate   = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00';
    $cd_code  = $_GET['cd'];

    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    echo '<html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Commission Report</title>
    </head>
    <body onload="window.print();">
      <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Commission Report</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';

    if ($cd_code != '') {
        // Client details
        $query = $dbh->prepare('SELECT * FROM client_account WHERE cd_code=:cd AND user_name=:un');
        $query->bindParam(':cd',$cd_code);
        $query->bindParam(':un',$username);
        $query->execute();
        $res = $query->fetch();
        $name = $res ? $res['cd_code'].' / '.$res['f_name'].' '.$res['l_name'].' / '.$res['phone'] : $username;

        $sql = "SELECT 
                YEAR(finance_date) AS finance_year,
                SUM(ABS(amount)) AS total_amount
                FROM bbo_finance
                WHERE user_name LIKE :cd
                  AND finance_date >= :fromDate
                  AND finance_date < :toDate
                  AND flag = 4
                GROUP BY YEAR(finance_date)
                ORDER BY finance_year";
        $params = [':cd'=>$cd_code, ':fromDate'=>$fromDate, ':toDate'=>$toDate];
    } else {
        $name = $_SESSION['sess_username']; 
        $sql = "SELECT 
              YEAR(finance_date) AS finance_year,
              SUM(ABS(amount)) AS total_amount
              FROM bbo_finance
              WHERE user_name LIKE CONCAT(:cd_prefix, '%')
                AND finance_date >= :fromDate
                AND finance_date < :toDate
                AND flag = 4
              GROUP BY YEAR(finance_date)
              ORDER BY finance_year";
        $params = [
          ':cd_prefix' => substr($username, 0, 7),
          ':fromDate'  => $fromDate,
          ':toDate'    => $toDate
      ];
    }

    echo '<div class="row">
            <div class="col-xs-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">Client Details : '.$name.'</div>
            </div>
          </div>';

    $query = $dbh->prepare($sql);
    $query->execute($params);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    $i = 1;
    $total = 0;

    echo '<div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table table-bordered table-striped" >
                <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>';
    if ($cd_code != '') {
        echo '<th>Sl#</th><th>Remarks</th><th>Operation</th><th>Date</th><th>Amount</th>';
    } else {
        echo '<th>Sl#</th><th>Date</th><th>Amount</th>';
    }
    echo '</tr></thead><tbody>';

    if ($cd_code != '') {
        foreach($rows as $res){
            $o = ($res['flag'] == 4) ? 'Commission' : '';
            echo '<tr>
                    <td>'.$i++.'</td>
                    <td>'.$res['remarks'].'</td>
                    <td>'.$o.'</td>
                    <td>'.$res['finance_date'].'</td>
                    <td>'.$res['amount'].'</td>
                  </tr>';
            $total += $res['amount'];
        }
        echo "<tr style='font-size: 70%;'><td><b>Total</b></td><td></td><td></td><td></td><td><b>".number_format($total,2,".",",")."</b></td></tr>";
    } else {
        foreach($rows as $res){
            echo '<tr>
                    <td>'.$i++.'</td>
                    <td>'.$res['finance_year'].'</td>
                    <td>'.$res['total_amount'].'</td>
                  </tr>';
            $total += $res['total_amount'];
        }
        echo "<tr style='font-size: 70%;'><td><b>Total</b></td><td></td><td><b>".number_format($total,2,".",",")."</b></td></tr>";
    }

    echo '       </tbody>
              </table>
            </div>
          </div>
        </section>    
      </div>
    </body>
    </html>';
}
else if(!empty($_GET["cashtrnx"])) 
{
    $sec_type = $_GET['sec_type'];
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';

    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Commission Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
              <div class="row">
                <div class="col-xs-12">
                  <div class="page-header">
                    &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                    <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                     <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Cash Transaction Report</div> 
                     <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                     Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                  </div>
                </div>
              </div>';
               $query= $dbh->prepare("
                    SELECT e.order_date,c.title,c.f_name,c.l_name,c.ID,e.cd_code,s.symbol,e.lot_size_execute,e.order_exe_price,e.lot_size_execute* e.order_exe_price as amount 
                    FROM {$table_name} e, client_account c, symbol s 
                    WHERE e.cd_code=c.cd_code AND e.symbol_id=s.symbol_id 
                      AND :fromDate <= e.order_date AND e.order_date <= :toDate 
                      AND e.side='B' 
                      AND member_broker=:un 
                      ORDER BY e.order_date ASC
                  ");
                  $query->bindParam(':fromDate',$fromDate);
                  $query->bindParam(':toDate',$toDate);
                  $query->bindParam(':un',$username);
                  $query->execute();
                  echo 'Commission <br> From : '.$fromDate.' - To : '.$toDate;
                  echo"
                  <table class='table table-bordered'>
                    <thead>
                      <tr style='background-color:#333;color:#fff'>
                        <th>SN</th>
                        <th>Date of Transaction</th>
                        <th>Customer Name</th> 
                        <th>CID/DISN</th>
                        <th>CC Code</th>
                        <th>Symbol Traded</th>
                        <th>Buy Qty</th>
                        <th>Buy Price</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                  <tbody>";
                  $i=1;
                  $total=0;
                  foreach($query as $res){
                    echo'
                    <tr>
                      <td>'.$i++.'</td>
                      <td>'.$res['order_date'].'</td>
                      <td>'.$res['title']." ".$res['f_name']. " ".$res['l_name'].'</td>
                      <td>'.$res['ID'].'</td>
                      <td>'.$res['cd_code'].'</td>
                      <td>'.$res['symbol'].'</td>
                      <td>'.$res['lot_size_execute'].'</td>
                      <td>'.$res['order_exe_price'].'</td>
                      <td>'.$res['amount'].'</td>
                    </tr>';
                    $total += $res['amount'];
                  }
                  echo"
                    <tr>
                      <td colspan='8'><b>Total<b></td>
                      <td><b>".number_format($total,2,".",",")."</b></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>
        </div>
      </body>
    </html>";
}
else if(!empty($_GET["rightstradeConfirmation"])) 
{
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    $cd_code = $_GET['cd'];
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    $query= $dbh->prepare('SELECT a.name FROM adm_participants b,adm_institution a where a.institution_id=b.institution_id and participant_code=:participant_code');
    $query->bindParam(':participant_code', $participant_code);
    $query->execute();
    $res=$query->fetch();
    $broker = $res['name'];
    echo'<html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Rights Trade Confirmation Report</title>
    </head>
        <body onload="window.print();">
        <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Rights Trade Confirmation</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From :<b>'.$fromDate.'</b>&nbsp;To :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
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
                $detail= $dbh->prepare($sql);
                $detail->bindParam(':cd',$val);
                $detail->execute();
                $amt=$detail->fetch();
                $amount = $amt['sum'];
                echo 'Client Details : '.$name;
                /*echo 'Trade Confirmation  <br> From : '.$fromDate.' - To : '.$toDate;*/
                
                $sql1=$dbh->prepare('SELECT price_discovered from rights_issue where price_discovered !=0 and :fromDate <= order_date and order_date <= :toDate ');
                $sql1->bindParam(':fromDate',$fromDate);
                $sql1->bindParam(':toDate',$toDate);
                $sql1->execute();
                $p= $sql1->fetch();
                $priced = isset($p['price_discovered']) ? $p['price_discovered'] : 0;

                $sql2=$dbh->prepare('SELECT face_value from rights_issue where face_value !=0 and  :fromDate <= order_date and order_date <= :toDate ');
                $sql2->bindParam(':fromDate',$fromDate);
                $sql2->bindParam(':toDate',$toDate);
                $sql2->execute();
                $f= $sql2->fetch();
                $fv = $f['face_value'];

                 $query= $dbh->prepare("SELECT  a.*,symbol from rights_issue a join symbol b on a.symbol_id=b.symbol_id where  (a.cd_code=:cd and a.type='S' and :fromDate <= a.order_date and a.order_date <= :toDate) OR 
                  (a.cd_code IS NOT NULL and a.renounce_cd_code=:cd and a.type='R' and  :fromDate <= a.order_date and a.order_date <= :toDate) OR (a.cd_code=:cd and a.type='B' and :fromDate <= a.order_date and a.order_date <= :toDate)
                   OR (a.cd_code=:cd and a.type='O' and  :fromDate <= a.order_date and a.order_date <= :toDate)");
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':fromDate',$fromDate);
                $query->bindParam(':toDate',$toDate);
                $query->execute();
                $i=1;
                $total=0;$totalb=0;$totals=0;
                echo"<table class='table table-bordered'>
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
                    <tr><td>'.$i++.'</td>
                    <td>'.$res['symbol'].'</td>';
                    if($res['type'] == 'S')
                    {
                      $type ="SUBSCRIBED";
                      $order_size = $res['order_size'];
                      $price = $res['face_value'];
                      $total = $res['total_amount'];
                      $commission=0;
                    } 
                    else if($res['type'] == 'R')
                    {
                      $type ="RENOUNCED";
                      $order_size = $res['order_size'];
                      $price = $res['face_value'];
                      $total = $res['total_amount'];
                      $commission=0;
                    }
                    else if($res['type'] == 'B')
                    {
                      $type ="BID";
                      $order_size = $res['allocated_size'];
                      $price = $res['price_discovered'];
                      $total = $res['allocated_size']*$res['price_discovered'];
                      $face_value = $fv;
                      $commission=$price*$order_size*0.02;
                    }
                    else
                    {
                      $type ="OFFER";
                      $order_size = $res['order_size'];
                      $price = $priced;
                      $total = $priced*$order_size; 
                      $face_value = $fv;
                      $commission=($price-$face_value)*$order_size*0.5;
                    }
                    if($res['type'] == 'S')
                    {
                      echo '
                    <td>'.$type.'</td>
                    <td>'.$order_size.'</td>
                    <td>'.$price.'</td>
                    <td>Nu. '.number_format($total,2,".",",").'</td></tr>';
                    $t = $total+$commission;
                      echo"<tr>
                  <td></td><td></td>
                  <td><b>Total Commission<b></td>
                  <td><b>".number_format($commission,2,".",",")."</b></td>
                  <td><b>Total Value<b></td>
                  <td><b>".number_format($t,2,".",",")."</b></td>
                  </tr>
                  ";
                    }
                     else if($res['type'] == 'R')
                    {
                      echo '
                    <td>'.$type.'</td>
                    <td>'.$order_size.'</td>
                    <td>'.$price.'</td>
                    <td>Nu. '.number_format($total,2,".",",").'</td></tr>';
                    $t = $total+$commission;
                      echo"<tr>
                  <td></td><td></td>
                  <td><b>Total Commission<b></td>
                  <td><b>".number_format($commission,2,".",",")."</b></td>
                  <td><b>Total Value<b></td>
                  <td><b>".number_format($t,2,".",",")."</b></td>
                  </tr>
                   ";
                    }
                    else if($res['type'] == 'B')
                    {
                      echo '
                    <td>'.$type.'</td>
                    <td>'.$order_size.'</td>
                    <td>'.$price.'</td>
                    <td>Nu. '.number_format($total,2,".",",").'</td></tr>';
                    $t = $total+$commission;
                      echo"<tr>
                  <td></td><td></td>
                  <td><b>Total Commission<b></td>
                  <td><b>".number_format($commission,2,".",",")."</b></td>
                  <td><b>Total Value<b></td>
                  <td><b>".number_format($t,2,".",",")."</b></td>
                  </tr>
                   "; 
                    }
                    else
                    {
                      echo '
                    <td>'.$type.'</td>
                    <td>'.$order_size.'</td>
                    <td>'.$price.'</td>
                    <td>Nu. '.number_format($total,2,".",",").'</td></tr>';
                    $t = $total+$commission;
                      echo"<tr>
                  <td></td><td></td>
                  <td><b>Total Commission<b></td>
                  <td><b>".number_format($commission,2,".",",")."</b></td>
                  <td><b>Total Value<b></td>
                  <td><b>".number_format($t,2,".",",")."</b></td>
                  </tr>
                  </tr>";
                    }
                    $t1 += $t;
                  }

                  echo"<tr>
                  <td><b>Total Payable/Receivable<b></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><b>".number_format($t1,2,".",",")."</b></td>
                  </tbody></table>
                  <br><br><br>
                  ____________________________________________________________________________________________________________________________________________
                  &emsp; &emsp;This is a computer generated report and requires no signatory." .$broker;
                  echo"
                  ____________________________________________________________________________________________________________________________________________
                  
                  </div>
                </div>
        </section>    
        </div>
        </body>
      </html>";
}
else if(!empty($_GET["ipotradeConfirmation"])) 
{
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    $cd_code = $_GET['cd'];
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    $query= $dbh->prepare('SELECT a.name from adm_participants b,adm_institution a where a.institution_id=b.institution_id and participant_code=:participant_code');
    $query->bindParam(':participant_code',$participant_code);
    $query->execute();
    $res = $query->fetch();
    $broker = isset($res['name']) ? $res['name'] : '';

    if ($cd_code!='') {
      $query= $dbh->prepare('SELECT * from client_account where cd_code=:cd');
      $query->bindParam(':cd',$cd_code);
      $query->execute();
      $res=$query->fetch();
        $name=$res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];
        $sql='SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
        $val=$cd_code; 
    } else {
        $name = $_SESSION['sess_username'];
        $sql = 'SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
        $val = $username;
    }
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>BOND Trade Confirmation Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">IPO Trade Confirmation</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From :<b>'.$fromDate.'</b>&nbsp;To :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
             echo 'IPO Subscription Details  <br> From : '.$fromDate.' - To : '.$toDate;
                echo '<br>Client Details : '.$name;

                $i=1;
                $query= $dbh->prepare("SELECT s.symbol,i.type,i.allocated_size,i.face_value,i.bid_price,i.total_amount 
                  FROM ipo i 
                  JOIN symbol s ON i.symbol_id = s.symbol_id 
                  WHERE i.cd_code=:cd AND i.order_date BETWEEN :fromDate AND :toDate
                ");
                $query->bindParam(':cd',$cd_code);
                $query->bindParam(':fromDate',$fromDate);
                $query->bindParam(':toDate',$toDate);
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
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>";
                  $totlValue = 0;
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
                    echo"
                    <tr>
                      <td></td><td></td>
                      <td><b>Total Commission<b></td>
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
                    <td><b>".number_format($totlValue,2,".",",")."</b></td>
                  </tr>
                </tbody>
              </table>
              <br><br><br>
          ____________________________________________________________________________________________________________________________________________
          &emsp; &emsp;This is a computer generated report and requires no signatory.(" .$username.")";
          echo"
          ____________________________________________________________________________________________________________________________________________
                  
            </div>
          </div>
        </section>
      </div>
    </body>
  </html>";
}

else if(!empty($_GET["iposubscription"])) 
{
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    $cd_code = $_GET['cd'];
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    $query= $dbh->prepare('SELECT a.name from adm_participants b,adm_institution a where a.institution_id=b.institution_id and participant_code=:participant_code');
    $query->bindParam(':participant_code',$participant_code);
    $query->execute();
    $res=$query->fetch();
    $broker = $res['name'];

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
    echo'<html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>IPO Subscription Report</title>
    </head>
        <body onload="window.print();">
        <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">IPO Subscription</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From :<b>'.$fromDate.'</b>&nbsp;To :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
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
                  </tbody></table>
                  <br><br><br>
                  ____________________________________________________________________________________________________________________________________________
                  &emsp; &emsp;This is a computer generated report and requires no signatory.(" .$username.")";
                  echo"
                  ____________________________________________________________________________________________________________________________________________
                  
                  </div></div>
        </section>    
        </div>
        </body>
      </html>";
}
else if(!empty($_GET["bondsubscription"])) 
{
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    $cd_code = $_GET['cd'];
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    /*$query= $dbh->prepare('SELECT a.name FROM adm_participants b,adm_institution a where a.institution_id=b.institution_id and participant_code=:participant_code');
    $query->bindParam(':participant_code', $participant_code);
    $query->execute();
    $res=$query->fetch();
    $broker = $res['name'];*/

    if($cd_code!=''){
      $query= $dbh->prepare('SELECT * from client_account where cd_code=:cd');
      $query->bindParam(':cd',$cd_code);
      $query->execute();
      $res=$query->fetch();
      $name = $res['cd_code'].' / '.$res['title'].' '.$res['f_name'].' '.$res['l_name'].' / '.$res['ID'];
      $sql = 'SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
      $val = $cd_code; 
    } else {
        $name = $_SESSION['sess_username'];
        $sql = 'SELECT sum(amount) as sum from rights_finance where cd_code=:cd ';
        $val = $username;
    }
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>BOND Subscription Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">BOND Subscription</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From :<b>'.$fromDate.'</b>&nbsp;To :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
             echo 'BOND Subscription Details  <br> From : '.$fromDate.' - To : '.$toDate;
                echo '<br>Client Details : '.$name;

                $query= $dbh->prepare("SELECT s.symbol,s.name,i.type,i.order_size,i.face_value,i.bid_price,i.total_amount 
                  FROM bond i  
                  JOIN symbol s on i.symbol_id = s.symbol_id 
                  WHERE i.cd_code=:cd AND i.order_date BETWEEN :fromDate AND :toDate");
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
                $i=1; $t = 0;
                 foreach($rows as $res){         
                    echo'
                    <tr>
                        <td>'.$i++.'</td>
                        <td>'.$res['symbol'].'('.$res['name'].')'.'</td>
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
                    </tr>';
                    $t =$res['total_amount'];
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
              <br><br><br>
            ____________________________________________________________________________________________________________________________________________
            &emsp; &emsp;This is a computer generated report and requires no signatory.(" .$username.")";
            echo"
            ____________________________________________________________________________________________________________________________________________
            </div>
          </div>
        </section>    
      </div>
    </body>
  </html>";
}
else if(!empty($_GET["bondtradeConfirmation"])) 
{
    $toDate = $_GET['toDate'];
    $fromDate = $_GET['fromDate'];
    $cd_code = $_GET['cd'];
    $symbol_id = $_GET['symbol_id'];

    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    
    /*$query = $dbh->prepare('SELECT a.name FROM adm_participants b, adm_institution a WHERE a.institution_id=b.institution_id AND participant_code=:participant_code');
    $query->bindParam(':participant_code', $participant_code);
    $query->execute();
    $res = $query->fetch();
    $broker = $res['name'];*/

    if($cd_code != ''){
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
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>BOND IPO Trade Confirmation Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">BOND IPO Trade Confirmation</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From :<b>'.$fromDate.'</b>&nbsp;To :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
            echo'BOND Confirmation Details  <br> From : '.$fromDate.' - To : '.$toDate;
            echo'<br>Client Details : '.$name;

            $i = 1;
            $query = $dbh->prepare("SELECT s.symbol, s.name, i.type, i.allocated_size, i.face_value, i.bid_price, i.total_amount, i.price_discovered 
              FROM bond i 
              JOIN symbol s ON i.symbol_id = s.symbol_id
              WHERE i.symbol_id=:sym AND i.cd_code=:cd AND i.order_date BETWEEN :fromDate AND  :toDate");
            $query->bindParam(':cd', $cd_code);
            $query->bindParam(':sym', $symbol_id);
            $query->bindParam(':fromDate',$fromDate);
            $query->bindParam(':toDate',$toDate);
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
                foreach($query as $res){ 
                  $totlValue =  $res['allocated_size']* $res['face_value'];                
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
            <br><br><br>
          ____________________________________________________________________________________________________________________________________________
          &emsp; &emsp;This is a computer generated report and requires no signatory.(" .$username.")";
          echo"
          ____________________________________________________________________________________________________________________________________________
                
              </div>
            </div>
          </section>    
        </div>
      </body>
    </html>";
}
elseif (isset($_GET['bond_allocation_summary'])) {
  $symbol_id = $_GET['symbol_id'];
  $mem_code = $_GET['mem_code'];

  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");

  echo'
  <html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>BOND IPO Trade Confirmation Report</title>
    </head>
    <body onload="window.print();">
      <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
          <div class="row">
            <div class="col-xs-12">
              <div class="page-header">
                &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                <b style="font-size: 110%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                 <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Bond Allocation Summary</div> 
                 <div class="lead" style="font-size: 40%;  margin-top:-25px;">Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
              </div>
            </div>
          </div>';
          $i = 1;
          $select = $dbh->prepare("SELECT b.cd_code, b.bid_price, b.order_size, b.allocated_size, s.symbol, s.name, (s.face_value * b.allocated_size) as amt 
            FROM bond b,symbol s 
            WHERE s.symbol_id = :sym 
              AND s.symbol_id = b.symbol_id 
              ORDER BY bid_price ASC
          ");
          $select->bindParam(":sym", $symbol_id);
          $select->execute();
          $rows = $select->fetchAll(PDO::FETCH_ASSOC);
          echo"
          <table class='table table-bordered'>
            <thead>
              <tr style='background-color:#333;color:#fff'>
                <th>#</th>
                <th>CD CODE</th>
                <th>SYMBOL</th>
                <th>NAME</th>
                <th>BID VOL</th>
                <th>RATE</th>
                <th>AMT</th>
                <th>ALLOCATED UNIT(s)</th>
              </tr>
            </thead>
            <tbody>";
              foreach ($rows as $res) { 
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
                </tr>'; 
                $i++;
              }
              echo"
            </tbody>
          </table>
          <br><br><br>
        ____________________________________________________________________________________________________________________________________________
        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;This is a computer generated report and requires no signatory
        ____________________________________________________________________________________________________________________________________________
              
            </div>
          </div>
        </section>    
      </div>
    </body>
  </html>";
}
?>
<link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../../plugins/datatables/dataTables.bootstrap.css">
  <link rel="stylesheet" href="../../dist/css/skins/_all-skins.min.css">

  <!-- iCheck -->
  <link rel="stylesheet" href="../../plugins/iCheck/flat/blue.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="../../plugins/morris/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="../../plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="../../plugins/datepicker/datepicker3.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="../../modal/jquery.min.js">
  <script src="../../plugins/input-mask/jquery.inputmask.js"></script>
<script src="../../plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="../../plugins/input-mask/jquery.inputmask.extensions.js"></script>
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <script src="../../plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="../../bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables/dataTables.bootstrap.min.js"></script>
<script src="../../plugins/datepicker/bootstrap-datepicker.js"></script>
<!-- SlimScroll -->
<script src="../../plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="../../plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<script src="../../dist/js/angular.min.js"></script>

<!-- page script -->
<script>
  $(function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>
<!-- Page script -->
<script src="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<link rel="stylesheet" href="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="https://cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>


