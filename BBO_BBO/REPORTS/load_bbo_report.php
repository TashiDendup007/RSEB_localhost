<?php
  include ('../FILES/session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');

  if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["trade_confirmation"])) {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $cd_code = $_POST['cdcode'];
    $mem_code = substr($username, 0, 7);

    echo'
    <div class="col-lg-12">
      <div class="box-body table-responsive">';
        $query = $dbh->prepare("SELECT 
            a.cd_code, a.f_name, a.l_name, a.phone, a.bank_account, b.bank_short_name, a.ID, a.email  
            FROM client_account a
            LEFT JOIN banks b ON a.bank_id = b.bank_id
            WHERE a.cd_code = :cd
            AND substr(a.user_name, 1, 7) = :m_code
        ");
        $query->bindParam(':cd', $cd_code);
        $query->bindParam(':m_code', $mem_code);
        $query->execute();
        $row = $query->fetch();
        if (!$row) {
          echo'<p style="color: red;">Invalid client CD Code</p>';
          die();
        }

        echo 'Trade Confirmation  <br> From : '.$fromDate.' - To : '.$toDate;
        echo '<br>CD CODE : '.$row['cd_code'].' , Name: '.$row['f_name'].' '.$row['l_name'].' , CID/DISN: '.$row['ID'].' , Phone No: '.$row['phone'];
        echo'<br>Bank : <b>'.$row['bank_short_name'].'</b>, Account No: <b>'.$row['bank_account'].'</b>';

        $query= $dbh->prepare('SELECT a.*, b.symbol 
          FROM executed_orders a 
          JOIN symbol b ON a.symbol_id = b.symbol_id 
          WHERE cd_code=:cd AND a.order_date BETWEEN :fromDate AND :toDate');
        $query->bindParam(':cd', $cd_code);
        $query->bindParam(':fromDate', $fromDate);
        $query->bindParam(':toDate', $toDate);
        $query->execute();
        $i = 1;
        $total = 0; $totalb = 0; $totals = 0;
        echo"
        <table class='table table-bordered'>
          <thead>
            <tr style='background-color:#333;color:#fff'>
              <th>SN</th>
              <th>Date</th>
              <th>Symbol</th>
              <th>Side</th>
              <th>Trade Vol</th>
              <th>Price</th>
              <th>Value</th>
            </tr>
          </thead>
          <tbody>";
            foreach ($query as $res) {
              $total1 = $res['lot_size_execute'] * $res['order_exe_price'];
              $total = $total + $total1;
              echo'
              <tr>
                <td>'.$i++.'</td>
                <td>'.$res['order_date'].'</td>
                <td>'.$res['symbol'].'</td>
                <td>'.$res['side'].'</td>
                <td>'.$res['lot_size_execute'].'</td>
                <td>'.$res['order_exe_price'].'</td>
                <td>Nu. '.number_format($total1, 2, ".", ",").'</td>
              </tr>';
              if($res['side'] == 'B'){ $totalb = $totalb + $total1; } 
              if($res['side'] == 'S'){ $totals = $totals + $total1; }
            }
           
            // $mem_code = substr($username, 0, 7);
            $b_commis = client_commission_multiple_brokers($cd_code, $mem_code);
            $to_com = ($total * $b_commis) / 100;
            $totalpr = $totals - $totalb - $to_com;
            echo"
            <tr>
              <td><b>Total Buy Value<b></td>
              <td><b>".number_format($totalb,2,".",",")."</b></td>
              <td><b>Total Sell Value<b></td>
              <td><b>".number_format($totals,2,".",",")."</b></td>
              <td><b>Total Commission<b></td>
              <td><b>".number_format($to_com,2,".",",")."</b></td>
              <td></td>
            </tr>
            <tr>
              <td><b>Total Payable/Receivable<b></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td><b>".number_format($totalpr,2,".",",")."</b></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>";
    echo'
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&tradeConfirmation=tradeConfirmation" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>';

        // if (substr($_SESSION['sess_username'], 0, 7) == 'MEMRNRB') {
        echo'
        <div class="row" style="margin-top: 5px;">
          <div class="col-lg-8">
            &emsp;&emsp;&emsp;
            The email will be sent to : <b>'.$row['email'].'</b>. If this is incorrect, Please update it in account registration and then send the email.
          </div>
          <div class="col-lg-4 float-left">
            &emsp;&emsp;
            <button class="btn btn-primary" onclick="sendMailNRB(\''.$cd_code.'\', \''.$fromDate.'\', \''.$toDate.'\')"><i class="fa fa-envelope"></i> Send Mail</button>
          </div>
        </div>

        <script type="text/javascript">
          function sendMailNRB(cd_code, from_date, to_date) {
            if (confirm("Do you want to continue?")) {
              showLoading();
              var op = "sendNRBReportViaMail";
              $.ajax({
                type: "POST",
                url: "load_bbo_report.php",
                data: "cd_code="+cd_code+"&from_date="+from_date+"&to_date="+to_date+"&sendNRBReportViaMail="+op,
                success: function(data){
                  hideloading();
                  alert(data);
                }
              });
            } else {
              return false;
            }
          }
        </script>';
      // }
      echo'

    </div>
    <br>';
  }
  elseif (isset($_POST['sendNRBReportViaMail']) && isset($_POST['cd_code'])) {
      $cd_code = $_POST['cd_code'];
      $from_date = $_POST['from_date'];
      $to_date = $_POST['to_date'];
      // print_r($_POST);

      $trade_date = date("d-M-Y", strtotime($from_date));

      // GET EMAIL
      $sql = $dbh->prepare("SELECT a.cd_code, a.ID, a.title, a.f_name, a.l_name, a.email, a.phone, a.tpn, a.address, i.name, a.bank_account, b.bank_short_name 
            FROM client_account a 
            LEFT JOIN banks b ON a.bank_id = b.bank_id 
            LEFT JOIN adm_institution i ON a.institution_id = i.institution_id
            WHERE a.cd_code = ?
      ");
      $sql->bindParam(1, $cd_code);
      $sql->execute();
      $row = $sql->fetch(PDO::FETCH_ASSOC);

      $email = $row['email'];

      $mem_broker = substr($_SESSION['sess_username'], 0, 7);

      include('mail.php');

      die();
  }
  elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["fin_conf"])) 
  {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $cd_code = $_POST['cdcode'];
    echo'
    <div class="col-lg-12">
      <div class="box-body table-responsive">';
        $name = '';
        if($cd_code != ''){
          $query = $dbh->prepare('SELECT cd_code, f_name, l_name, phone FROM client_account WHERE cd_code=:cd AND user_name=:un');
          $query->bindParam(':cd', $cd_code);
          $query->bindParam(':un', $username);
          $query->execute();
          $res = $query->fetch();
          if ($res) {
            $name = $res['cd_code'].' / '.$res['f_name'].' '.$res['l_name'].' / '.$res['phone'];            
          }
          
          $sql = "SELECT * FROM bbo_finance WHERE cd_code=:cd AND finance_date BETWEEN :fromDate AND :toDate";
          $val = $cd_code; 
        } else {
          $name = $_SESSION['sess_username'];
          $sql = "SELECT * from bbo_finance where user_name=:cd and finance_date BETWEEN :fromDate AND :toDate";
          $val = $username;
        }
        echo'<b>Account Activity</b><br>From : '.$fromDate.' - To : '.$toDate;
        echo'<br>Client Details : '.$name;
        $query = $dbh->prepare($sql);
        $query->bindParam(':cd', $val);
        $query->bindParam(':fromDate', $fromDate);
        $query->bindParam(':toDate', $toDate);
        $query->execute();
        $i = 1;
        $dt = 0;
        $ct = 0;
        $st = 0;
        $bt = 0;
        $cot = 0;
        echo"
        <table class='table table-bordered'>
          <thead>
            <tr style='background-color:#333; color:#fff'>
              <th>SN</th>
              <th>Remarks</th>
              <th>Operation</th> 
              <th>Date</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>";
          foreach($query as $res){
              /*if($res['flag']==0){ $o='Debit'; $dt = $dt + $res['amount']; }
              elseif($res['flag']==1){ $o='Credit'; $ct=$ct+$res['amount'];}
              elseif($res['flag']==2){ $o='Sell'; $st=$st+$res['amount'];}
              elseif($res['flag']==3){ $o='Buy'; $bt=$bt+$res['amount'];}
              elseif($res['flag']==4){ $o='Commission'; $cot=$cot+$res['amount'];}*/

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
    </div>";
    echo'
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&accountActivity=accountActivity" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>
    <br>';                
  }
  elseif (!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["com"])) {
    $toDate   = $_POST['toDate1'] . ' 23:59:00';
    $fromDate = $_POST['fromDate1'] . ' 00:00:00';
    $cd_code  = $_POST['cdcode'];

    echo '
    <div class="col-lg-12">
      <div class="box-body table-responsive">';

    // Fetch client details if cd_code is given
    if (!empty($cd_code)) {
        $query = $dbh->prepare('SELECT * FROM client_account WHERE cd_code=:cd AND user_name=:un');
        $query->bindParam(':cd', $cd_code);
        $query->bindParam(':un', $username);
        $query->execute();
        $res = $query->fetch();
        if ($query->rowCount() > 0) {
            $name = $res['cd_code'] . ' / ' . $res['f_name'] . ' ' . $res['l_name'] . ' / ' . $res['phone'];
        } else {
            $name = $username;
        }
    } else {
        $name = $username; // fallback
    }

    echo '<b>Commission</b><br> From : ' . $fromDate . ' - To : ' . $toDate;
    echo '<br>Client Details : ' . $name;

    // Build SQL
    if (!empty($cd_code)) {
        // case 1: specific client transactions
        $sql = "SELECT remarks, finance_date, amount, flag 
                FROM bbo_finance 
                WHERE cd_code = :cd 
                  AND finance_date BETWEEN :fromDate AND :toDate 
                  AND flag = 4
                ORDER BY finance_date ASC";
        $params = [
            ':cd'       => $cd_code,
            ':fromDate' => $fromDate,
            ':toDate'   => $toDate
        ];
      } else {
      // case 2: group totals per year for logged in broker
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

    $query = $dbh->prepare($sql);
    $query->execute($params);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Conditional table headers
    if (!empty($cd_code)) {
        echo "
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
    } else {
        echo "
        <table class='table table-bordered'>
          <thead>
            <tr style='background-color:#333;color:#fff'>
              <th>SN</th>
              <th>Year</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>";
    }

    $i = 1;
    $total = 0;

    if (!empty($cd_code)) {
        // Detailed rows
        foreach ($rows as $res) {
            $o = ($res['flag'] == 4) ? 'Commission' : '';
            echo "
            <tr>
              <td>{$i}</td>
              <td>{$res['remarks']}</td>
              <td>{$o}</td>
              <td>{$res['finance_date']}</td>
              <td>{$res['amount']}</td>
            </tr>";
            $i++;
            $total += $res['amount'];
        }
    } else {
        // Only Date & Amount
        foreach ($rows as $res) {
            echo "
            <tr>
              <td>{$i}</td>
              <td>{$res['finance_year']}</td>
              <td>{$res['total_amount']}</td>
            </tr>";
            $i++;
            $total += $res['total_amount'];
        }
    }

    // ✅ Total row adjusted
    if (!empty($cd_code)) {
        echo "
            <tr>
              <td><b>Total</b></td>
              <td></td>
              <td></td>
              <td></td>
              <td><b>" . number_format($total, 2, ".", ",") . "</b></td>
            </tr>";
    } else {
        echo "
            <tr>
              <td><b>Total</b></td>
              <td></td>
              <td><b>" . number_format($total, 2, ".", ",") . "</b></td>
            </tr>";
    }

    echo "
          </tbody>
        </table>
        </div>
      </div>";

    echo '
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?cd=' . $cd_code . '&toDate=' . $toDate . '&fromDate=' . $fromDate . '&commission=commission" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>
    <br>';
}
  elseif(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["cashtrnx"])) 
  {
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $toDate = $_POST['toDate1'].' 23:59:00';
    echo'
    <div class="col-lg-12">
      <div class="box-body table-responsive">';
        /*$query= $dbh->prepare("SELECT e.order_date,c.title,c.f_name,c.l_name,c.ID,e.cd_code,s.symbol,e.lot_size_execute,e.order_exe_price,e.lot_size_execute * e.order_exe_price as amount 
          from executed_orders e, client_account c, symbol s 
          where e.cd_code=c.cd_code and e.symbol_id=s.symbol_id and :fromDate <= e.order_date and e.order_date <= :toDate and e.side='B' and member_broker=:un order by e.order_date ASC");*/
        $query= $dbh->prepare("SELECT e.order_date, c.title, c.f_name, c.l_name, c.ID, e.cd_code, s.symbol, e.lot_size_execute, e.order_exe_price, e.lot_size_execute * e.order_exe_price as amount 
          FROM executed_orders e
          JOIN client_account c ON e.cd_code = c.cd_code
          JOIN symbol s ON e.symbol_id = s.symbol_id
          WHERE e.side = 'B' AND e.member_broker = :un AND e.order_date BETWEEN :fromDate AND :toDate
          ORDER BY e.order_date ASC");
        $query->bindParam(':fromDate',$fromDate);
        $query->bindParam(':toDate',$toDate);
        $query->bindParam(':un',$username);
        $query->execute();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
        echo'Cash Transaction <br> From : '.$fromDate.' - To : '.$toDate;
        echo"
        <table class='table table-bordered'>
          <thead>
            <tr style='background-color:#333;color:#fff'>
              <th>SN</th>
              <th>Date of Transaction</th>
              <th>Customer Name</th> 
              <th>CID/DISN</th>
              <th>CD Code</th>
              <th>Symbol Traded</th>
              <th>Buy Qty</th>
              <th>Buy Price</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>";
            $i = 1; $total = 0;
            foreach($rows as $res){
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
              $total=$total+$res['amount'];
            }
            echo"
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td><b>Total<b></td>
              <td><b>".number_format($total,2,".",",")."</b></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>"; 
    echo'
    <div class="row no-print">
      <div class="col-lg-12">
      &emsp;&emsp;<a href="loadReportPrint.php?toDate='.$toDate.'&fromDate='.$fromDate.'&cashtrnx=cashtrnx" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      &emsp;&emsp;<a href="load_bbo_report.php?ge_export=ge_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
      </div>
    </div>
    <br>';
  }
  elseif(!empty($_GET['ge_export'])) 
  {       
    $replace   = array("\n");
    $search  = array('');
    $fromDate=$_GET['fromDate'];
    $toDate=$_GET['toDate'];

    $wc= $dbh->prepare("SELECT e.order_date, c.title, c.f_name, c.l_name, c.ID, e.cd_code, s.symbol, e.lot_size_execute, e.order_exe_price, e.lot_size_execute * e.order_exe_price as amount 
      FROM executed_orders e
      JOIN client_account c ON e.cd_code = c.cd_code
      JOIN symbol s ON e.symbol_id = s.symbol_id
      WHERE e.side = 'B' AND e.member_broker = :un AND e.order_date BETWEEN :fromDate AND :toDate
      ORDER BY e.order_date ASCC");
    $wc->bindParam(':fromDate',$fromDate);
    $wc->bindParam(':toDate',$toDate);
    $wc->bindParam(':un',$username);
    $wc->execute(); 
    $columnHeader = '';  
    $i=1;
    /*<img src="../../dist/img/Logo.png"> &emsp; 
    <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>*/
    $columnHeader = "SNO" . "\t" . "DATE OF TRANSACTION" . "\t". "CUSTOMER NAME" . "\t". "CID/DISN" . "\t". "CD CODE" . "\t". "SYMBOL" . "\t". "BUY QTY" . "\t" . "BUY PRICE" . "\t" . "AMOUNT" . "\t"; 
    $setData = '';  
    while ($rec=$wc->fetch()){
      if($wc->rowCount() <= 0) 
      {}
      $rowData = '';  
      $value = $i++ . 
      "\t". str_replace($search,$replace,$rec['order_date']) . 
      "\t". str_replace($search,$replace,trim($rec['title'])." ".trim($rec['f_name'])." ".trim($rec['l_name'])) .
      "\t". str_replace($search,$replace,$rec['ID']).
      "\t". str_replace($search,$replace,$rec['cd_code']) . "\t". str_replace($search,$replace,$rec['symbol']) . 
      "\t". str_replace($search,$replace,$rec['lot_size_execute']) . "\t". str_replace($search,$replace,$rec['order_exe_price']).
      "\t". str_replace($search,$replace,$rec['amount']) . "\t";  
      $rowData .= $value;  
      $setData .= trim($rowData) . "\n";
    }
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=CASH TRANSACTION.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
  }
  else if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["dTradeDetails"])) 
  {
    $toDate = $_POST['toDate1'].' 23:59:00';
    $fromDate = $_POST['fromDate1'].' 00:00:00';
    $un=substr($username,0,7);
    echo'
    <div class="col-lg-12">
      <div class="box-body table-responsive">';
        echo'<b>Summary of Trade</b><br> From : '.$fromDate.' - To : '.$toDate;
          $sql = $dbh->prepare("SELECT o.occupation_name, c.tpn, c.f_name, c.l_name, c.ID, e.member_broker, e.cd_code,
              COALESCE(IF(e.side = 'B', e.lot_size_execute, NULL), 0) AS BUY,
              COALESCE(IF(e.side = 'S', e.lot_size_execute, NULL), 0) AS SELL,
              e.order_exe_price, e.lot_size_execute * e.order_exe_price AS amount, s.symbol, e.order_date, e.sub_user, c.title 
            FROM 
              executed_orders e
              INNER JOIN symbol s ON e.symbol_id = s.symbol_id
              INNER JOIN client_account c ON c.cd_code = e.cd_code
              INNER JOIN occupation o ON o.occupation = c.occupation
            WHERE 
              substr(e.member_broker, 1, 7) = :un AND order_date >= :fdate AND order_date <= :tdate 
            ORDER BY  e.member_broker ASC");
          $sql->bindParam(':un',$un);
          $sql->bindParam(':fdate',$fromDate);
          $sql->bindParam(':tdate',$toDate);
          $sql->execute();
          $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
          echo"
          <div class='table-responsive' style='font-size: 10px;'>
            <table id='tradeTableId' class='table table-bordered'>
              <thead>
                <tr style='background-color:#333;color:#fff'>
                  <th>USER</th>
                  <th>CD CODE</th>
                  <th>TPN</th>
                  <th>NAME</th>
                  <th>OCCUPATION</th>
                  <th>CID</th>
                  <th>BUY</th>
                  <th>SELL</th>
                  <th>PRICE</th>
                  <th>AMOUNT</th>
                  <th>SYMBOL</th>
                  <th>DATE</th>
                </tr>
              </thead>
            <tbody>";
            $i=1;
            foreach($rows as $res1){
              echo'
              <tr>
                <td>'.$res1['sub_user'].'</td>
                <td>'.$res1['cd_code'].'</td>
                <td>'.$res1['tpn'].'</td>
                <td>'.$res1['title'].'. '.$res1['f_name'].' '.$res1['l_name'].'</td>
                <td>'.$res1['occupation_name'].'</td>
                <td>'.$res1['ID'].'</td>
                <td>'.$res1['BUY'].'</td>
                <td>'.$res1['SELL'].'</td>
                <td>'.$res1['order_exe_price'].'</td>
                <td>'.$res1['amount'].'</td>
                <td>'.$res1['symbol'].'</td>
                <td>'.$res1['order_date'].'</td>
              </tr>';
            }
            echo"
            </tbody>
          </table>
        </div>";
        echo '
      </div>
    </div>
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="load_bbo_report.php?zge_export=zge_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
      </div>
    </div>
    <br>';
  }
  else if (!empty($_GET['zge_export']))
  {
    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');
    $fromDate  = $_GET['fromDate'];
    $toDate    = $_GET['toDate'];
    $un=substr($username,0,7);
    $executed_orders = $dbh->prepare("SELECT z.DzongkhagName, c.DzongkhagID, o.occupation_name, c.tpn, c.f_name, c.l_name, c.ID, e.member_broker, e.cd_code,
              COALESCE(IF(e.side = 'B', e.lot_size_execute, NULL), 0) AS BUY,
              COALESCE(IF(e.side = 'S', e.lot_size_execute, NULL), 0) AS SELL,
              e.order_exe_price, e.lot_size_execute * e.order_exe_price AS amount, s.symbol, e.order_date, e.sub_user, c.title 
            FROM 
              executed_orders e
              INNER JOIN symbol s ON e.symbol_id = s.symbol_id
              INNER JOIN client_account c ON c.cd_code = e.cd_code
              INNER JOIN occupation o ON o.occupation = c.occupation
              INNER JOIN tbldzongkhag z ON z.DzongkhagID = c.DzongkhagID
            WHERE 
              substr(e.member_broker, 1, 7) = :un AND order_date >= :fdate AND order_date <= :tdate 
            ORDER BY  e.member_broker ASC");

    $executed_orders->bindParam(':un',$un);
    $executed_orders->bindParam(':fdate',$fromDate);
    $executed_orders->bindParam(':tdate',$toDate);
    $executed_orders->execute();
    $columnHeader = '';  
    $i=1;
    $columnHeader = "SNO" . "\t". "USER" . "\t" . "CD CODE" . "\t"."TPN" . "\t". "NAME" . "\t" . "CID" . "\t". "BUY" . "\t". "SELL" . "\t". "PRICE" . "\t". "AMOUNT" . "\t". "SYMBOL" . "\t". "DATE". "\t". "OCCUPATION". "\t". "DZONGKHAG". "\t"; 
    $setData = '';  
    while ($rec=$executed_orders->fetch()){
      $name = strtoupper($rec['title'].'. '.$rec['f_name'].' '.$rec['l_name']);
      if($executed_orders->rowCount() <= 0) 
      {}
      $rowData = '';  
      $value = $i++ . 
      "\t ". str_replace($search,$replace,$rec['sub_user']).
      "\t" .str_replace($search,$replace,trim($rec['cd_code']).
      "\t ".str_replace($search,$replace,$rec['tpn']).
      "\t" .str_replace($search, $replace, $name).
      "\t".$rec['ID'].
      "\t".$rec['BUY']."\t".$rec['SELL']). 
      "\t". str_replace($search,$replace,$rec['order_exe_price']) . 
      "\t". str_replace($search,$replace,$rec['amount']) . 
      "\t". str_replace($search,$replace,$rec['symbol']) . 
      "\t". str_replace($search,$replace,$rec['order_date']).
      "\t". str_replace($search,$replace,$rec['occupation_name']).
      "\t". str_replace($search,$replace,$rec['DzongkhagName']).
      "\t";  
      $rowData .= $value;  
      $setData .= trim($rowData) . "\n";
    }
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=DetailedtradeDetails.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
  }
  elseif (!empty($_GET['download_withdrawal_list'])) {
      $replace = array("\n", "\r\n", "\r");
      $search = array('', '', '');

      $stmt = $dbh->prepare("SELECT COALESCE(CONCAT(a.f_name, COALESCE(a.l_name, '')), '') AS NAME, 
            a.phone, a.email, a.ID, m.cd_code, m.amount, a.bank_account
              FROM mcams_wallet m 
              join client_account a on m.cd_code = a.cd_code 
              where m.`type`='DR' and m.paid_to_user = 'PROCESSING'
          ");
      $stmt->execute();

      $columnHeader = '';
      $columnHeader .= "\tDate Generated :".date('Y-m-d') ."\t\n\n";
      $columnHeader .= "Sl\t NAME\t EMAIL\t CID\t CD_CODE\t AMOUNT\t  Account Number\t\n";

      $setData = '';

      $i = 1;
      while ($rec = $stmt->fetch()) {
          $rowData = '';
          $value = $i++ .
              "\t" . str_replace($search, $replace, $rec['NAME']) .
              "\t" . str_replace($search, $replace, $rec['email']) .
              "\t" . str_replace($search, $replace, $rec['ID']) .
              "\t" . str_replace($search, $replace, $rec['cd_code']) .
              "\t" . str_replace($search, $replace, $rec['amount']) .
              "\t" . str_replace($search, $replace, $rec['bank_account']) .
              "\t\n";
          $rowData .= $value;
          $setData .= trim($rowData) . "\n";
      }

      // Additional rows after completing the loop
      $additionalRows = "\n\n\tOriginator Sign : ________________\t\n\tReceiver Sign : ______________________\t\n\n";
      $additionalRows .= "\tFROM : BLA Trading Section TO: Finance Section for Payment\n";

      // Concatenate header, data, and additional rows
      $dataToExport = $columnHeader . $setData . $additionalRows;

      // Set headers for Excel download
      header("Content-type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=NRB_Withdrawal_list.xls");
      header("Pragma: no-cache");
      header("Expires: 0");

      // Output data
      echo ucwords($dataToExport);
  }
  elseif (!empty($_GET['download_withdrawal_list_print'])) {
      date_default_timezone_set("Asia/Thimphu");
      $sysTime = date("Y-m-d");

      echo'
      <html>
        <head>
          <meta charset="utf-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <title>mCaMS - Withdrawal_List</title>
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
        </head>
        <body onload="window.print();">
          <div class="wrapper">
            <section class="invoice" style="background:rgb(248, 249, 249);">
              <div class="row">
                <div class="col-xs-12">
                    <div class="page-header" style="display: flex; align-items: center;">
                        <img src="../../dist/img/Logo.png" style="margin-right: 150px; height: 120px;">
                        <div style="text-align: center;">
                            <b style="font-size: 130%; margin-bottom: 5px;">ROYAL SECURITIES EXCHANGE OF BHUTAN</b>
                            <div class="lead" style="font-size: 111%; margin-bottom: 5px;">Withdrawal List</div>
                            <div class="lead" style="font-size: 111%;">Report generated on: <b>'.$sysTime.'</b></div>
                        </div>
                    </div>
                </div>
              </div>';
              echo'
              <table class="table table-bordered table-striped">
                <thead>
                  <tr style="background-color:#333;color:#fff">
                    <th>SN</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>CID</th>
                    <th>CD Code</th>
                    <th>Amount</th>
                    <th>Account Number</th>
                  </tr>
                </thead>
                <tbody>';
                $stmt = $dbh->prepare("SELECT COALESCE(CONCAT(a.f_name, COALESCE(a.l_name, '')), '') AS NAME, a.phone, a.email, a.ID, m.cd_code, m.amount, a.bank_account 
                    FROM mcams_wallet m 
                    JOIN client_account a ON m.cd_code = a.cd_code 
                    WHERE m.type='DR' AND m.paid_to_user = 'PROCESSING'
                ");
                $stmt->execute();
                $i = 1;
                $total_amt = 0;
                foreach ($stmt as $rec) {
                  $total_amt += $rec['amount'];
                  echo'
                    <tr>
                      <td>'.$i++.'</td>
                      <td>'.$rec['NAME'].'</td>
                      <td>'.$rec['email'].'</td>
                      <td>'.$rec['ID'].'</td>
                      <td>'.$rec['cd_code'].'</td>
                      <td>'.$rec['amount'].'</td>
                      <td>'.$rec['bank_account'].'</td>
                    </tr>';
                }
                echo'
                  <tr>
                    <td colspan="5" style="text-align: right;">Total Amount</td>
                    <td colspan="5" style="font-weight: bold; text-align: left;">('.$total_amt.')</td>
                  </tr>';
                echo'
                </tbody>
              </table>
              <table>
                  <tr>
                      <td rowspan="2" style="height: 80px;">Originator Sign: __________________________________________</td>
                  </tr>
                  <tr></tr> 
                  <tr>
                      <td rowspan="2" style="height: 80px;">Receiver Sign: __________________________________________</td>
                  </tr>
                  <tr></tr>
              </table>
              <br>

              From: BLA Trading Section TO:Finance Section For Payment
                    
              </div>
            </div>
          </section>    
        </div>
      </body>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    </html>';
  }
  else
  {

  }
?>

