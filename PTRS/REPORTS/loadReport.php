<?php
  date_default_timezone_set("Asia/Thimphu");
  include("../FILES/session_file.php");
  include ('../../CONNECTIONS/db.php');
  include ('../../functions/function-sanitize.php');
  require('../../fpdf/fpdf.php');

if(!empty($_POST["dep"])) {
    $cd = $_POST['cdcode'];
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00';
    $fromDate1 = $_POST['fromDate'];

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, b.name, a.address 
        FROM client_account a 
        JOIN adm_institution b ON a.institution_id = b.institution_id 
        where a.cd_code=:cd
    ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo '
    <br><br>
    <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Account Activity Report</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">CD Code : '.$cd.'</div>
            <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].', BROKER : '.$state['name'].'</div>
          </div>
        </div>';

        /*$stmt = $dbh->prepare("SELECT a.* 
          FROM (
            SELECT distinct c.cd_code as cdcode, c.cd_code, c.symbol_id, s1.symbol, c.type, c.remarks, c.volume, c.entry_date 
            FROM cds_dep_wit c 
            JOIN symbol s1 ON c.symbol_id = s1.symbol_id  
            WHERE c.entry_date BETWEEN :fromDate AND :toDate AND c.cd_code=:cdCode AND c.type IN ('B', 'S')
            GROUP BY symbol_id   
            UNION ALL 
            SELECT ct.from_acc, ct.to_acc, ct.symbol_id, s2.symbol, ct.remarks, ct.remarks, ct.trs_vol, ct.trs_date 
            FROM cds_transfer ct 
            JOIN symbol s2 ON ct.symbol_id = s2.symbol_id 
            WHERE ct.trs_date BETWEEN :fromDate AND :toDate AND (ct.from_acc=:cdCode OR ct.to_acc=:cdCode) 
            GROUP BY symbol_id 
            UNION ALL 
            SELECT ca.cd_code, ca.cd_code, s.symbol_id, sm.symbol, s.announcement_type, can.rate, s.volume, s.record_date 
            FROM spot_date_holding s 
            JOIN client_account ca ON s.client_id = ca.client_id 
            JOIN symbol sm ON s.symbol_id = sm.symbol_id
            JOIN corporate_announcement can ON s.announcement_type = can.announcement_type
            WHERE s.record_date BETWEEN :fromDate and :toDate and ca.cd_code=:cdCode and s.ribon_volume != 0 and s.status=1 and  s.announcement_type IN (2, 4) 
            UNION ALL
            SELECT ri.cd_code, ri.cd_code, ri.symbol_id, s.symbol, an.announcement_type, an.rate, ri.order_size, an.record_date 
            FROM rights_issue_auction ri 
            JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id
            JOIN symbol s ON ri.symbol_id = s.symbol_id
            WHERE  an.announcement_type=1 AND (ri.cd_code=:cdCode OR ri.renounce_cd_code=:cdCode) AND ri.type IN ('S', 'R') and an.status=0 
            AND an.record_date BETWEEN :fromDate AND :toDate
          ) a 
          GROUP BY a.symbol_id 
          ORDER BY symbol_id
        ");
        $stmt->execute([
            ':cdCode' => $cd,
            ':fromDate' => $fromDate,
            ':toDate' => $toDate,
        ]);*/

        $sql = "SELECT a.cd_code, a.symbol_id, s.symbol, a.type, a.remarks, a.volume, a.entry_date 
                FROM (
                    SELECT c.cd_code, c.cd_code as cd_code1, c.symbol_id, c.type, c.remarks, c.volume, c.entry_date 
                    FROM cds_dep_wit c 
                    WHERE c.entry_date BETWEEN :fromDate AND :toDate AND c.cd_code = :cdCode AND c.type IN ('B', 'S')
                    UNION ALL 
                    SELECT ct.from_acc AS cd_code, ct.to_acc AS cd_code1, ct.symbol_id, ct.remarks AS type, ct.remarks, ct.trs_vol AS volume, ct.trs_date AS entry_date
                    FROM cds_transfer ct 
                    WHERE ct.trs_date BETWEEN :fromDate AND :toDate AND (ct.from_acc = :cdCode OR ct.to_acc = :cdCode) 
                    UNION ALL 
                    SELECT ca.cd_code, ca.cd_code as cd_code1, s.symbol_id, s.announcement_type AS type, can.rate AS remarks, s.volume, s.record_date AS entry_date
                    FROM spot_date_holding s 
                    JOIN client_account ca ON s.client_id = ca.client_id 
                    JOIN corporate_announcement can ON s.announcement_type = can.announcement_type
                    WHERE s.record_date BETWEEN :fromDate AND :toDate AND ca.cd_code = :cdCode AND s.ribon_volume != 0 AND s.status = 1 AND s.announcement_type IN (2, 4) 
                    UNION ALL
                    SELECT ri.cd_code, ri.cd_code AS cd_code1, ri.symbol_id, an.announcement_type AS type, an.rate AS remarks, ri.order_size AS volume, an.record_date AS entry_date
                    FROM rights_issue_auction ri 
                    JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id 
                    WHERE an.announcement_type = 1 AND (ri.cd_code = :cdCode OR ri.renounce_cd_code = :cdCode) AND ri.type IN ('S', 'R') AND an.status = 0 
                    AND an.record_date BETWEEN :fromDate AND :toDate
                ) a
                JOIN symbol s ON a.symbol_id = s.symbol_id
                GROUP BY a.symbol_id
                ORDER BY a.symbol_id
            ";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':fromDate' => $fromDate,
            ':toDate' => $toDate,
            ':cdCode' => $cd,
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $i = 1;
        foreach ($results as $state) {
          echo'
          <div class="row">
            <div class="col-xs-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">Symbol: '.$state['symbol'].'</div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table  table-striped" >
                <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>
                    <th>Sl#</th>
                    <th>Date</th>
                    <th>Transaction Type</th>
                    <th>Pending In Vol</th>
                    <th>Pending Out Vol</th>
                    <th>Total Shares</th>
                  </tr>
                </thead>
                <tbody>';
                $i = 1;
                $sql = "SELECT c.cd_code as cd, c.cd_code, c.symbol_id, s1.symbol, c.type, c.remarks, c.volume, c.entry_date 
                  FROM cds_dep_wit c 
                  JOIN symbol s1 ON c.symbol_id = s1.symbol_id
                  WHERE c.entry_date BETWEEN :fromDate AND :toDate and c.cd_code=:cdCode and c.type IN ('B', 'S') AND c.symbol_id=:sid
                  
                  UNION ALL 
                  
                  SELECT ct.from_acc,ct.to_acc,ct.symbol_id,s2.symbol,ct.type,ct.remarks,ct.trs_vol,ct.trs_date 
                  from cds_transfer ct 
                  JOIN symbol s2 ON ct.symbol_id = s2.symbol_id
                  where ct.trs_date BETWEEN :fromDate AND :toDate AND ct.symbol_id=:sid AND (ct.from_acc = :cdCode OR ct.to_acc = :cdCode) 
                  
                  UNION ALL 
                  
                  SELECT ca.cd_code,ca.cd_code, s.symbol_id, sm.symbol, s.announcement_type, can.rate, s.ribon_volume, s.record_date 
                  FROM spot_date_holding s 
                  JOIN client_account ca ON s.client_id = ca.client_id
                  JOIN symbol sm ON s.symbol_id = sm.symbol_id
                  JOIN corporate_announcement can ON s.corp_announcement_id = can.corp_announcement_id
                  WHERE s.record_date BETWEEN :fromDate and :toDate and ca.cd_code=:cdCode and s.symbol_id=:sid and s.ribon_volume != 0 
                  AND s.announcement_type IN (2, 4) and s.status=1
                  GROUP BY s.record_date
                  
                  UNION ALL

                  SELECT ri.cd_code,ri.renounce_cd_code,ri.symbol_id,s.symbol,an.announcement_type,an.rate,ri.order_size,ri.order_date 
                  from rights_issue_auction ri 
                  JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id
                  JOIN symbol s ON ri.symbol_id = s.symbol_id
                  WHERE an.announcement_type = 1 and s.symbol_id = :sid and (ri.cd_code = :cdCode OR ri.renounce_cd_code = :cdCode) AND ri.type IN ('S', 'R') 
                  AND an.status = 0 AND ri.order_date BETWEEN :fromDate AND :toDate 
                  GROUP BY ri.order_date 
                  ORDER BY entry_date ASC
                ";
                $wc = $dbh->prepare($sql);
                $wc->bindParam(':cdCode', $cd);
                $wc->bindParam(':fromDate', $fromDate);
                $wc->bindParam(':toDate', $toDate);
                $wc->bindParam(':sid', $state['symbol_id']);
                $wc->execute();
                $rows = $wc->fetchAll(PDO::FETCH_ASSOC);

                $buyTotal = $transferinTotal = $rightsSubscribeTotal = $rightsRenounceTotal = $bonusTotal = $bonusTotal = $transferoutTotal = $sellTotal = $buybackTotal = 0;
                foreach($rows as $det) {
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td>'.$det['entry_date'].'</td>';
                    if($det['cd'] == $det['cd_code'] && $det['type'] == 'B') {
                      $v = $det['volume'];
                      echo'
                      <td>BUY</td>
                      <td>'.$det['volume'].'</td>
                      <td>-</td>
                      <td>'.$v.'</td>';
                      $buyTotal += $v; 
                    }
                    elseif ($det['cd'] == $det['cd_code'] && $det['type'] == 'S') {
                      $v = $det['volume']*-1;
                      echo'
                      <td>SELL</td>
                      <td>-</td>
                      <td>'.substr($det['volume'],1).'</td>
                      <td>'.$v.'</td>';
                      $sellTotal += $v;
                    }
                    elseif ($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd']) && $det['type'] =='TR') {
                      $v = $det['volume'];
                      echo'
                      <td>TRANSFER</td>
                      <td>-</td>
                      <td>'.$det['volume'].'</td>
                      <td>'.$v.'</td>';
                      $transferoutTotal += $v;
                    }
                    elseif ($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd_code']) && $det['type'] =='TR') {
                      $v = $det['volume'];
                      echo'
                      <td>TRANSFER</td>
                      <td>'.$det['volume'].'</td>
                      <td>-</td>
                      <td>'.$v.'</td>';
                      $transferinTotal += $v;
                    }
                    elseif ($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd']) and $det['type'] == 1) {
                      $v = $det['volume'];
                      echo'
                      <td>RIGHTS</td>
                      <td>'.$det['volume'].'</td>
                      <td>-</td>
                      <td>'.$v.'</td>';
                      $rightsSubscribeTotal += $v;
                    }
                    elseif ($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd_code']) and $det['type'] == 1) {
                      $v = $det['volume'];
                      echo'
                      <td>RIGHTS</td>
                      <td>'.$det['volume'].'</td>
                      <td>-</td>
                      <td>'.$v.'</td>';
                      $rightsRenounceTotal += $v;
                    }
                    elseif ($det['cd'] == $det['cd_code'] && $det['type'] == 2) {
                      $v = $det['volume'];
                      echo'
                      <td>BONUS</td>
                      <td>'.$det['volume'].'</td>
                      <td>-</td>
                      <td>'.$v.'</td>';
                      $bonusTotal += $v;
                    }
                    elseif($det['cd'] == $det['cd_code'] && $det['type'] == 4) {
                      $v = $det['volume'];
                      echo'
                      <td>BUY BACK</td>
                      <td>-</td>
                      <td>'.$det['volume'].'</td>
                      <td>'.$v.'</td>';
                      $buybackTotal += $v;
                    }
                  echo'
                  </tr>';
                  $i++;
                }
                echo'
                <tr style="font-size: 70%;">
                  <td></td>
                  <td></td>
                  <td>TOTAL</td>
                  <td>'.($buyTotal + $transferinTotal + $rightsSubscribeTotal + $rightsRenounceTotal + $bonusTotal).'</td>
                  <td>'.($transferoutTotal + $sellTotal + $buybackTotal).'</td>
                  <td>'.($transferinTotal + $rightsSubscribeTotal + $rightsRenounceTotal + $bonusTotal + $transferoutTotal + $sellTotal + $buyTotal).'</td>
                </tr>
              </tbody>
            </table>';
          }
          echo'
          </section> 
          <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd.'&toDate='.$toDate.'&fromDate='.$fromDate.'&accountActivity=accountActivity" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
          </div>';
    
}
elseif (!empty($_POST["topVolLead"])) {
    $symbol = $_POST['symbol'];
    $top = $_POST['top'];

    $wc = $dbh->prepare("SELECT * from symbol where symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");

    echo '
    <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Top Volume Leaders</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">Symbol : '.$symbol.'</div>
            <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['name'].'</div>
          </div>
        </div>';
        $wc = $dbh->prepare("SELECT h.cd_code, h.volume, h.pledge_volume, h.block_volume, c.f_name, c.l_name, c.tpn, c.address, h.volume + h.pledge_volume + h.block_volume AS tot, c.acc_type
            FROM cds_holding h 
            JOIN client_account c ON h.cd_code = c.cd_code 
            JOIN symbol s ON s.symbol_id = h.symbol_id
            WHERE s.symbol = :symb 
            ORDER BY tot
            DESC LIMIT :tp
        ");
        $wc->bindParam(':symb', $symbol, PDO::PARAM_STR);
        $wc->bindParam(':tp', $top, PDO::PARAM_INT);
        $wc->execute();
        $states = $wc->fetchAll(PDO::FETCH_ASSOC);
        echo'
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table  table-striped" >
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                <th>Sl.</th> 
                  <th>Cd Code</th>                    
                  <th>Account Name</th>
                  <th>Tax#</th>
                  <th>Address</th>
                  <th>Total</th>
                </tr>
                </thead>
                <tbody>';
                $i = 1;
                foreach ($states as $state) {
                  $name = $state['acc_type'] == 'I' ? strtoupper($state['f_name']). " ".strtoupper($state['l_name']) : strtoupper($state['f_name']);
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td>'.$state['cd_code'].'</td>
                    <td>'.$name.'</td>
                    <td>'.$state['tpn'].'</td>
                    <td>'.$state['address'].'</td>
                    <td>'.number_format($state['tot'], 0, ".", ",").'</td>
                  </tr>';
                  $i++;
                }
                echo'
                </tbody>
              </table>
            </div>
          </div>
        </section> 
        <div class="row no-print">
          <div class="col-xs-12">
            &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&top='.$top.'&topVolLeaders=topVolLeaders" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
          </div>
        </div>';
        exit();
}
elseif (!empty($_POST["noOfShareholders"])) {
      $symbol = $_POST['symbol'];
      $wc = $dbh->prepare("SELECT * from symbol where symbol=:symbol and status=1");
      $wc->bindParam(':symbol',$symbol);
      $wc->execute();
      $state = $wc->fetch();
      $sysTime = date("Y-m-d");
      echo'
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Number of Shareholders</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table  table-striped" >
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th>SL #</th>
                  <th>Security Symbol</th>                    
                  <th style="text-align:right;">Share Holders</th>
                </tr>
              </thead>
              <tbody>';
              if ($symbol != '') {
                $sql = $dbh->prepare("SELECT count(*) AS total_share_holder
                  FROM cds_holding c 
                  WHERE c.symbol_id = :sid 
                  AND (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) > 0
                ");
                $sql->bindParam(':sid',$state['symbol_id']);
                $sql->execute();
                $nos = $sql->fetch();
                echo'
                <tr style="font-size: 70%;">
                   <td>1</td>
                   <td>'.$symbol.'</td>
                   <td style="text-align:right;">'.$nos['total_share_holder'].'</td>
                </tr>';
              } else {
                $stmt = $dbh->prepare("SELECT s.symbol, COUNT(*) total_share_holder 
                  FROM cds_holding c 
                  JOIN symbol s ON c.symbol_id = s.symbol_id
                  WHERE s.status = 1
                  AND (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) > 0 
                  GROUP BY c.symbol_id
                  ORDER BY s.symbol ASC
                ");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $i = 1;
                foreach ($rows as $key) {
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td>'.$key['symbol'].'</td>                         
                    <td style="text-align:right;">'.$key['total_share_holder'].'</td>
                  </tr>';
                  $i++;
                }
              }
              echo '
              </tbody>
            </table>
          </div>
        </div>
      </section> 
      <div class="row no-print">
        <div class="col-lg-12">
          &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&numberofSholders=numberofSholders" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
      </div>';
}
elseif (!empty($_POST["pus"])) {
    $symbol = $_POST['symbol'];

    $wc= $dbh->prepare("SELECT * FROM symbol where symbol=:symbol");
    $wc->bindParam(':symbol', $symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-xs-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Paid Up Shares</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
             Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table  table-striped" >
            <thead style="background-color: #D6EAF8; font-size: 80%;">
              <tr>
                <th>SL#</th>
                <th>Security Symbol</th>
                <th>Available Vol</th>
                <th>Block Vol</th>
                <th>Pledge Vol</th>
                <th>Pending  Vol</th>
                <th>Total Vol</th>
              </tr>
            </thead>
            <tbody>';
            $i = 1;
            $query = "SELECT sum(volume) as v,sum(pledge_volume) as pv,sum(block_volume) as bv,sum(pending_out_vol) as pov, sum(volume + pledge_volume + block_volume + pending_out_vol) as tots
                FROM cds_holding 
                WHERE symbol_id=:sid";
            if ($symbol != '') {
              $sql = $dbh->prepare($query);
              $sql->bindParam(':sid', $state['symbol_id']);
              $sql->execute();
              $state1 = $sql->fetch();                    
              echo'
              <tr style="font-size: 70%;">
                <td>'.$i++.'</td> 
                <td>'.$symbol.'</td>
                <td>'.number_format($state1['v'],0,".",",").'</td>
                <td>'.number_format($state1['bv'],0,".",",").'</td>
                <td>'.number_format($state1['pv'],0,".",",").'</td>
                <td>'.number_format($state1['pov'],0,".",",").'</td>
                <td>'.number_format($state1['tots'],0,".",",").'</td>
              </tr>';
            } else {
              $sql=$dbh->prepare("SELECT DISTINCT symbol_id, symbol FROM symbol WHERE status=1 ORDER BY symbol ASC");
              $sql->execute();
              foreach ($sql as $state2) {
                $sql1 = $dbh->prepare($query);
                $sql1->bindParam(':sid', $state2['symbol_id']);
                $sql1->execute();

                foreach ($sql1 as $state3) { 
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i++.'</td> 
                    <td>'.$state2['symbol'].'</td>                         
                    <td>'.number_format($state3['v'],0,".",",").'</td>
                    <td>'.number_format($state3['bv'],0,".",",").'</td>
                    <td>'.number_format($state3['pv'],0,".",",").'</td>
                    <td>'.number_format($state3['pov'],0,".",",").'</td>
                    <td>'.number_format($state3['tots'],0,".",",").'</td>
                  </tr>';
                }                  
              }
            }              
            echo'
          </tbody>
        </table>
      </div>
    </div>
  </section> 
  <div class="row no-print">
    <div class="col-xs-12">
    &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&pus=pus" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>
  </div>';
}
elseif (!empty($_POST["announcement"])) 
{

    $cd=$_POST['cdcode'];
    $toDate = $_POST['toDate'];
    $fromDate = $_POST['fromDate'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name,a.address from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
        echo '
        <br><br>
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <h2 class="page-header">
                  <i class="glyphicon glyphicon-briefcase"></i> RSEBL CENTRAL DEPOSITORY               
                </h2>
              </div>
            </div>
            <div class="col-xs-6">
              <p class="lead">Account Activity Report</p>            
            </div>            
            <br><br><br>
            Account Code : <b>'.$cd.'</b>
            <br>
            Name: <b>'.$state['f_name']." ".$state['l_name'].'</b>
            <br>
            Address : <b>'.$state['address'].'</b>
            <br>
            From Date : <b>'.$fromDate.'</b>
            <br>
            To Date : <b>'.$toDate.'</b>
            <br><br>';

    $wc= $dbh->prepare("select s.symbol,p.pledge_date,h.volume,h.pledge_volume,h.pending_in_vol,h.pending_out_vol,h.block_volume 
      from cds_holding h, cds_pledge p,symbol s where h.cd_code=p.cd_code and h.symbol_id=s.symbol_id and :fromDate <= p.pledge_date 
      <= :toDate and h.cd_code=:cdCode order by s.symbol");
    $wc->bindParam(':fromDate',$fromDate);
    $wc->bindParam(':toDate',$toDate);
    $wc->bindParam(':cdCode',$cd); 
    $wc->execute();    
    echo '  <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table table-striped" >
                  <thead style="background-color: #D6EAF8  ;">
                  <tr>
                    <th>Trn. Id</th>                    
                    <th>Transaction Type</th>
                    <th>Trn. Volume</th>
                    <th>Available</th>
                    <th>Pledge</th>
                    <th>Pending-In</th>
                    <th>Pending-Out</th>
                    <th>Blocked</th>
                  </tr>
                  </thead>
                  <tbody>';
    $i=1;
    foreach($wc as $state)
    {
    echo'            <tr>
                         <td>'.$state['symbol'].'</td>
                         <td> '.$state['pledge_date'].'</td>
                         <td>'.$state['volume'].'</td>
                         <td>'.$state['pledge_volume'].'</td>
                         <td>'.$state['pending_in_vol'].'</td>
                         <td>'.$state['pending_out_vol'].'</td>
                         <td>'.$state['block_volume'].'</td>
                     </tr>';
    $i=$i+1;
    }
      echo '       </tbody>
            </table>

            <br><br><br><br>
            <div class="row no-print">
            <div class="col-xs-12">
              <a href="allbids-report-print.php?cid=&pid=&itemid=" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>';
}
elseif (!empty($_POST["announcementList"])) {
    $status = $_POST['status'];
    $sysTime = date("Y-m-d");
    echo'
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-lg-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Announcement List</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
             Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12 table-responsive">
          <table class="table  table-striped" >
            <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th>#</th>
                  <th>Security Symbol</th>
                  <th>Security Name</th>
                  <th>Record Date</th>
                  <th>Ex Date</th>
                  <th>Announcement Date</th>
                  <th>Rate (%)</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>';
              $wc = $dbh->prepare("SELECT c.symbol_id, c.announcement_type, c.record_date, c.ex_date, c.announcement_date, c.rate, s.symbol, s.name 
                FROM corporate_announcement c 
                JOIN symbol s ON c.symbol_id = s.symbol_id
                WHERE c.status=:status");
              $wc->bindParam(':status', $status);
              $wc->execute();
              $states = $wc->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              foreach ($states as $state) {
                switch ($state['announcement_type']) {
                    case "1":
                        $corporate_name = 'RIGHTS';
                        break;
                    case "2":
                        $corporate_name = 'BONUS';
                        break;
                    case "3":
                        $corporate_name = 'DIVIDEND';
                        break;
                    default:
                        $corporate_name = 'BUYBACK';
                        break;
                }
              echo'
              <tr style="font-size: 70%;">
                <td>'.$i.'</td>
                <td>'.$state['symbol'].'</td>
                <td>'.$state['name'].'</td>
                <td>'.$state['record_date'].'</td>
                <td>'.$state['ex_date'].'</td>
                <td>'.$state['announcement_date'].'</td>
                <td>'.$state['rate'].'</td>
                <td>'.$corporate_name.'</td>
              </tr>';
              $i++;
            }
            echo'
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <div class="row no-print">
      <div class="col-xs-12">
        &emsp;&emsp;<a href="loadReportPrint.php?status='.$status.'&announcementList=announcementList" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>';
}
elseif (!empty($_POST["entitlement_list"])) 
{

    $symbol=$_POST['symbol'];
    $wc= $dbh->prepare("SELECT * from symbol where symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state=$wc->fetch();
        echo '
        <br><br>
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <h2 class="page-header">
                  <i class="glyphicon glyphicon-briefcase"></i> RSEBL CENTRAL DEPOSITORY               
                </h2>
              </div>
            </div>';
            date_default_timezone_set("Asia/Thimphu");
                $sysTime = date("d/m/Y");
      echo' <div class="col-xs-6">
              <p class="lead">Top Volume Leaders Report</p>           
            </div>            
            <br><br><br>
            Symbol : <b>'.$symbol. " " .$state['name'].'</b>
            <br>
            As on: <b>'.$sysTime.'</b>
            <br><br>';

    $wc= $dbh->prepare("select s.symbol,p.pledge_date,h.volume,h.pledge_volume,h.pending_in_vol,h.pending_out_vol,h.block_volume 
      from cds_holding h, cds_pledge p,symbol s where h.cd_code=p.cd_code and h.symbol_id=s.symbol_id and :fromDate <= p.pledge_date 
      <= :toDate and h.cd_code=:cdCode order by s.symbol");
    $wc->bindParam(':fromDate',$symbol);
    $wc->bindParam(':toDate',$symbol);
    $wc->bindParam(':cdCode',$symbol); 
    $wc->execute();    
    echo '  <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table table-striped" >
                  <thead style="background-color: #D6EAF8  ;">
                  <tr>
                    <th>Cd Code</th>                    
                    <th>Account Name</th>
                    <th>Tax#</th>
                    <th>Address</th>
                    <th>Position Owned</th>
                  </tr>
                  </thead>
                  <tbody>';
    $i=1;
    foreach($wc as $state)
    {
    echo'            <tr>
                         <td>'.$state['symbol'].'</td>
                         <td> '.$state['pledge_date'].'</td>
                         <td>'.$state['volume'].'</td>
                         <td>'.$state['pledge_volume'].'</td>
                         <td>'.$state['pending_in_vol'].'</td>
                         <td>'.$state['pending_out_vol'].'</td>
                         <td>'.$state['block_volume'].'</td>
                     </tr>';
    $i=$i+1;
    }
      echo '       </tbody>
            </table>

            <br><br><br><br>
            <div class="row no-print">
            <div class="col-xs-12">
              <a href="allbids-report-print.php?cid=&pid=&itemid=" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>';
}
elseif (!empty($_POST["genShHoldList"])) {
      $symbol = $_POST['symbol'];

      $wc = $dbh->prepare("SELECT symbol, symbol_id, name FROM symbol WHERE symbol=:symbol");
      $wc->bindParam(':symbol',$symbol);
      $wc->execute();
      $state = $wc->fetch();
      $symbol_id = $state['symbol_id'];
      $sysTime = date("Y-m-d");
      echo '
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">General Share Holder List</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">Security Symbol : '.$symbol.'</div>
            <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['name'].'</div>
          </div>
        </div>';
        $wc = $dbh->prepare("SELECT a.acc_type, c.cd_code, a.title, a.f_name, a.l_name, a.tpn, a.ID, a.address, sum(c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) AS total 
            FROM cds_holding c 
            JOIN client_account a ON c.cd_code = a.cd_code
            WHERE symbol_id=:sid AND (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) > 0 
            GROUP BY c.cd_code ORDER BY c.cd_code ASC
        ");
        $wc->bindParam(':sid', $state['symbol_id']);
        $wc->execute();    
        echo'  
        <div class="row">
          <div class="col-lg-12 table-responsive">
            <table id="tableListId" class="table table-striped" >
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th>Sl.</th> 
                  <th>CD Code</th>                    
                  <th>Account Name</th>
                  <th>Tax#</th>
                  <th>ID Number</th>
                  <th>Address</th>
                  <th style="text-align:right;">Position Owned</th>
                </tr>
                </thead>
                <tbody>';
                $i = 1;
                $sh = 0;
                foreach ($wc as $state) {
                  if ($state['total'] > 0) {
                    echo'
                    <tr style="font-size: 70%;">
                       <td>'.$i.'</td>
                       <td>'.$state['cd_code'].'</td>';
                       if ($state['acc_type'] == 'I') {
                            echo '<td>' .
                                strtoupper($state['title'] ?? '') . " " .
                                strtoupper($state['f_name'] ?? '') . " " .
                                strtoupper($state['l_name'] ?? '') .
                                '</td>';
                        } else {
                            echo '<td>' . strtoupper($state['f_name'] ?? '') . '</td>';
                        }

                       echo'
                       <td>'.$state['tpn'].'</td>
                       <td>'.$state['ID'].'</td>
                       <td>'.$state['address'].'</td>
                       <td style="text-align:right;">'.number_format($state['total'],0,".",",").'</td>
                    </tr>';
                    $totalShares = $state['total'];
                    $sh = $totalShares + $sh;
                    $i++;
                  }
                }
                echo'
                <tr>
                  <td>Total</td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>'.number_format($sh, 0, ".", ",").'</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
      <div class="row no-print">
        <div class="col-lg-12">
          &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&generalShareholderList=generalShareholderList" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a> 
          &emsp;&emsp;<a href="generate_entitelment.php?symbol_id='.$symbol_id.'&ge_export=ge_export" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
      </div>
      <script type="type/javascript">
        $("#tableListId").DataTable();
      </script>
      '; 
}
elseif (!empty($_POST["pledgeActivity"])) {
    $pledgeContCode = $_POST['pledgeContCode'];

    $wc = $dbh->prepare("SELECT c.pledge_contract, c.pledge_name, a.cd_code, a.f_name, a.l_name, a.ID 
      FROM cds_pledge_contract c 
      JOIN client_account a ON c.cd_code=a.cd_code
      WHERE c.pledge_contract = :plcntr ");
    $wc->bindParam(':plcntr', $pledgeContCode);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-lg-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Pledge Contract</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
             Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">Contract Name : '.$state['pledge_name'].'</div>
          <div class="lead" style="font-size: 70%; margin-top:-10px;">Contract Code : '.$state['pledge_contract'].'</div>
          <div class="lead" style="font-size: 70%; margin-top:-15px;">Client Details : CD Code: '.$state['cd_code'].', Name:'.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'</div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12 table-responsive">
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
            $wc = $dbh->prepare("SELECT DISTINCT symbol_id FROM cds_pledge WHERE pledge_contract = :contCode");
            $wc->bindParam(':contCode', $pledgeContCode);
            $wc->execute();
            $i = 1;                
            foreach ($wc as $state) { 
              $wc1 = $dbh->prepare("SELECT s.symbol, s.name, c.pledgee, SUM(c.pledge_volume) as volume, m.market_price 
                FROM cds_pledge c 
                JOIN market_price m ON  c.symbol_id = m.symbol_id
                JOIN symbol s ON c.symbol_id = s.symbol_id
                WHERE c.symbol_id = :sid AND c.pledge_contract = :contCode
              ");
              $wc1->bindParam(':sid', $state['symbol_id']);
              $wc1->bindParam(':contCode', $pledgeContCode);
              $wc1->execute();  
              foreach ($wc1 as $state1) {
                echo'
                <tr style="font-size: 70%;">
                  <td>'.$i.'</td>
                  <td>'.$state1['symbol'].'/'.$state1['name'].'</td>
                  <td>'.$state1['volume'].'</td>
                  <td>'.$state1['pledgee'].'</td>
                  <td>'.$state1['market_price'].'</td>
                </tr>';
                $i++;
              }                                                   
            }
            echo'
            </tbody>
          </table>
        </div>
      </div>
    </section> 
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?pledgeContCode='.$pledgeContCode.'&pledgeActivity=pledgeActivity" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>';  
}
elseif (!empty($_POST["deposits"])) 
{

    $symbol=$_POST['symbol'];
    $wc= $dbh->prepare("SELECT * from symbol where symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state=$wc->fetch();
        echo '
        <br><br>
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <h2 class="page-header">
                  <i class="glyphicon glyphicon-briefcase"></i> RSEBL CENTRAL DEPOSITORY               
                </h2>
              </div>
            </div>';
            date_default_timezone_set("Asia/Thimphu");
                $sysTime = date("d/m/Y");
      echo' <div class="col-xs-6">
              <p class="lead">Top Volume Leaders Report</p>           
            </div>            
            <br><br><br>
            Symbol : <b>'.$symbol. " " .$state['name'].'</b>
            <br>
            As on: <b>'.$sysTime.'</b>
            <br><br>';

    $wc= $dbh->prepare("select s.symbol,p.pledge_date,h.volume,h.pledge_volume,h.pending_in_vol,h.pending_out_vol,h.block_volume 
      from cds_holding h, cds_pledge p,symbol s where h.cd_code=p.cd_code and h.symbol_id=s.symbol_id and :fromDate <= p.pledge_date 
      <= :toDate and h.cd_code=:cdCode order by s.symbol");
    $wc->bindParam(':fromDate',$symbol);
    $wc->bindParam(':toDate',$symbol);
    $wc->bindParam(':cdCode',$symbol); 
    $wc->execute();    
    echo '  <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table table-striped" >
                  <thead style="background-color: #D6EAF8  ;">
                  <tr>
                    <th>Cd Code</th>                    
                    <th>Account Name</th>
                    <th>Tax#</th>
                    <th>Address</th>
                    <th>Position Owned</th>
                  </tr>
                  </thead>
                  <tbody>';
    $i=1;
    foreach($wc as $state)
    {
    echo'            <tr>
                         <td>'.$state['symbol'].'</td>
                         <td> '.$state['pledge_date'].'</td>
                         <td>'.$state['volume'].'</td>
                         <td>'.$state['pledge_volume'].'</td>
                         <td>'.$state['pending_in_vol'].'</td>
                         <td>'.$state['pending_out_vol'].'</td>
                         <td>'.$state['block_volume'].'</td>
                     </tr>';
    $i=$i+1;
    }
      echo '       </tbody>
            </table>

            <br><br><br><br>
            <div class="row no-print">
            <div class="col-xs-12">
              <a href="allbids-report-print.php?cid=&pid=&itemid=" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>';
}
elseif (isset($_POST["individual_report"])) 
{
    $cid = $_POST['cid'];

  $wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, c.title, c.tpn, c.address
        FROM client_account c
        WHERE (c.ID = :cid OR c.cd_code = :cid)
        ORDER BY c.client_id DESC LIMIT 1
    ");
  $wc->bindParam(':cid', $cid);
  $wc->execute();
  $state = $wc->fetch();
  $sysTime = date("Y-m-d");

  echo '
  <section class="invoice" style="background:rgb(248, 249, 249);">
    <div class="row">
      <div class="col-xs-12">
        <div class="page-header">
          &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
          <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
           <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Account Summary Details</div> 
           <div class="lead" style="font-size: 40%;  margin-top:-25px;">
           Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12">
        <div class="lead" style="font-size: 70%; margin-top:-10px;">CID/DISN/CD CODE : '.$cid.'</div>
        <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['title'].' '.$state['f_name'].' '.$state['l_name'].' ,</br>
         TPN : '.$state['tpn'].'</br> ADDRESS : '.$state['address'].'</div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12 table-responsive">
        <table class="table  table-striped">
          <thead style="background-color: #D6EAF8; font-size: 80%;">
            <tr>
              <th>Sl#</th>
              <th>CD Code/Symbol</th><th style="text-align:right;">Volume</th><th style="text-align:right;">Block Vol</th><th style="text-align:right;">Pledged Vol</th><th style="text-align:right;">PIV</th><th style="text-align:right;">POV</th><th style="text-align:right;">Total</th>
            </tr>
          </thead>
          <tbody>';
          $i = 1;
          $get = $dbh->prepare("SELECT s.symbol, c.cd_code, c.volume, c.pledge_volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, 
                (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) AS total_volume
                FROM cds_holding c
                LEFT JOIN client_account a ON c.cd_code = a.cd_code
                LEFT JOIN symbol s ON c.symbol_id = s.symbol_id
                WHERE a.ID = :cid
                AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) > 0
                AND s.status = 1
            ");
          $get->bindParam(':cid', $cid);
          $get->execute(); 
          $gets = $get->fetchAll(PDO::FETCH_ASSOC);
          $i = 1;
          foreach ($gets as $get) {
            $vol = isset($get['volume']) ? $get['volume'] : '-';
            $bv = isset($get['block_volume']) ? $get['block_volume'] : '-';
            $pv = isset($get['pledge_volume']) ? $get['pledge_volume'] : '-';
            $piv = isset($get['pending_in_vol']) ? $get['pending_in_vol'] : '-';
            $pov = isset($get['pending_out_vol']) ? $get['pending_out_vol'] : '-';
            echo'
            <tr style="font-size: 70%;">
               <td>'.$i.'</td>
               <td>'.$get['cd_code'].'-'.$get['symbol'].'</td>
               <td style="text-align:right;">'.$vol.'</td>
               <td style="text-align:right;">'.$bv.'</td>
               <td style="text-align:right;">'.$pv.'</td>
               <td style="text-align:right;">'.$piv.'</td>
               <td style="text-align:right;">'.$pov.'</td>
               <td style="text-align:right;">'.number_format($get['total_volume'],0,".",",").'</td>
            </tr>';
            $i++;
          }
          echo'
          </tbody>
        </table>
      </div>
    </div>
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?cid='.$cid.'&accountSummary=accountSummary" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>
  </section>';
}
elseif (isset($_POST["ordersaudit"])) {
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00'; 
    $sysTime = date("Y-m-d");
    echo '
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
        <div class="col-xs-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : '.$fromDate.' TO : '.$toDate.'</div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table  table-striped">
            <thead style="background-color: #D6EAF8; font-size: 80%;">
              <tr>
                <th>Sl#</th>
                <th style="text-align:right;">Symbol</th>
                <th style="text-align:right;">CD Code</th>
                <th style="text-align:right;">Member Entry</th>
                <th style="text-align:right;">Order Volume</th>
                <th style="text-align:right;">Side</th>
                <th style="text-align:right;">Price</th>
                <th style="text-align:right;">Order Time</th>
              </tr>
            </thead>
            <tbody>';
            $i=1;
            $wc = $dbh->prepare("SELECT s.symbol, o.cd_code, o.order_entry, o.order_size, o.side, o.price, o.order_date  
              FROM orders o
              Join symbol s ON o.symbol_id = s.symbol_id
              WHERE o.order_date BETWEEN :fdate AND :tdate
            ");
            $wc->bindParam(':fdate',$fromDate);
            $wc->bindParam(':tdate',$toDate);
            $wc->execute();
            $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows as $state) {
              $side = ($state['side'] == 'B') ? 'BUY' : 'SELL';
              echo' 
              <tr style="font-size: 70%;">
                <td>'.$i.'</td>
                <td style="text-align:right;">'.$state['symbol'].'</td>
                <td style="text-align:right;">'.$state['cd_code'].'</td>
                <td style="text-align:right;">'.$state['order_entry'].'</td>
                <td style="text-align:right;">'.$state['order_size'].'</td>
                <td style="text-align:right;">'.$side.'</td>
                <td style="text-align:right;">'.$state['price'].'</td>
                <td style="text-align:right;">'.$state['order_date'].'</td>
              </tr>';
              $i++;
            }
            echo'
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?fromDate='.$fromDate.'&toDate='.$toDate.'&orderaudit=orderaudit" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>';
}
elseif (isset($_POST["rightsaudit"])) {
    $syml_id = $_POST['symbol_id'];
    $fromDate = $_POST['fromDate'].' 00:00:00'; 
    $toDate = $_POST['toDate'].' 23:59:00';

    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    echo '
    <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Rights Orders Audit Details</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : <b>'.$fromDate.'</b> TO : <b>'.$toDate.'</b></div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table table-striped table-bordered" id="rights_issue_table_id">
              <thead style="background-color: #D6EAF8; font-size: 14px;">
                <tr>
                  <th>Sl#</th>
                  <th style="text-align:right;">Type</th>
                  <th style="text-align:left;">Name</th>
                  <th style="text-align:left;">ID</th>
                  <th style="text-align:right;">CD Code</th>
                  <th style="text-align:right;">Renounce CD Code</th>
                  <th style="text-align:right;">Order Volume</th>
                  <th style="text-align:right;">Price</th>
                  <th style="text-align:right;">Order Time</th>
                </tr>
              </thead>
              <tbody>';
              $wc = $dbh->prepare("SELECT CONCAT_WS(' ', a.f_name, a.l_name) AS fl_name, a.ID, r.type, r.cd_code, r.renounce_cd_code, r.order_size, r.bid_price, r.face_value, r.order_date
                    FROM rights_issue r 
                    LEFT JOIN client_account a on r.cd_code = a.cd_code 
                    WHERE r.order_date BETWEEN ? AND ? 
                    AND r.symbol_id = ?
              ");
              $wc->execute([$fromDate, $toDate, $syml_id]);
              $results = $wc->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              $side = '';
              $total_subscribed = 0;
              foreach($results as $state) {
                  switch ($state['type']) {
                      case 'S':
                          $side = 'SUBSCRIBE';
                          break;
                      case 'R':
                          $side = 'RENOUNCE';
                          break;
                      default:
                          $side = 'BID';
                          break;
                  }

                  $price = ($side == 'BID') ? $state['bid_price'] : $state['face_value'];
                  $total_subscribed += $state['order_size'];
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td style="text-align:right;">'.$side.'</td>
                    <td style="text-align:left;">'.$state['fl_name'].'</td>
                    <td style="text-align:left;">'.$state['ID'].'</td>
                    <td style="text-align:right;">'.$state['cd_code'].'</td>
                    <td style="text-align:right;">'.$state['renounce_cd_code'].'</td>
                    <td style="text-align:right;">'.$state['order_size'].'</td>
                    <td style="text-align:right;">'.$price.'</td>
                    <td style="text-align:right;">'.$state['order_date'].'</td>
                  </tr>';
                  $i++;
              }
              echo'
              </tbody>
            </table>

            <div class="col-lg-12 col-md-12 col-sm-12 text-right">
              Total Subscribed: '.number_format($total_subscribed).'
            </div>

            <script type="text/javascript">
              $( function () {
                  $("#rights_issue_table_id").DataTable();
              });
            </script>
            
        </section>
        <div class="row no-print">
          <div class="col-xs-12">
            &emsp;&emsp;<a href="loadReport.php?ge_export=ge_export&fromDate='.$fromDate.'&toDate='.$toDate.'&symbol_id='.$syml_id.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
          </div>
        </div>';
}
elseif(!empty($_GET['ge_export'])) {       
    $replace   = array("\n");
    $search  = array('');

    $syml_id = $_GET['symbol_id'];
    $toDate = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00';

    $stmt = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id = ?");
    $stmt->execute([$syml_id]);
    $symbol_name = $stmt->fetchColumn();

    $wc = $dbh->prepare("SELECT CONCAT_WS(' ', a.f_name, a.l_name) AS fl_name, a.ID, r.type, r.cd_code, r.renounce_cd_code, r.order_size, r.bid_price, r.face_value, r.order_date, r.total_amount, r.available_rights, r.user_name 
      FROM rights_issue r 
      LEFT JOIN client_account a on r.cd_code = a.cd_code 
      WHERE r.order_date BETWEEN ? AND ? 
      AND r.symbol_id = ?
    ");
    $wc->execute([$fromDate, $toDate, $syml_id]); 

    $columnHeader = '';  
    $i = 1;
    $columnHeader = "SNO\t TYPE\t Name\t ID\t CD CODE\t RENOUNCE CD CODE\t ORDER VOLUME\t PRICE\t TOTAL AMOUNT\t AVAILABLE RIGHTS\t USER NAME\t ORDER TIME\t"; 
    $setData = ''; 

    while ($rec=$wc->fetch()) { 
        if($wc->rowCount() <= 0) 
        {}

        if($rec['type'] == 'B') {
            $p = $rec['bid_price'];
            $totala = $rec['bid_price'] * $rec['order_size'];
        } 
        else {
            $p = $rec['face_value'];
            $totala = $rec['total_amount'];
        }
        $rowData = '';  
        $value = $i++ . "\t "
            . str_replace($search,$replace,$rec['type']) . "\t"
            . str_replace($search,$replace,$rec['fl_name']). "\t"
            . str_replace($search,$replace,$rec['ID']). "\t"
            . str_replace($search,$replace,$rec['cd_code']). "\t"
            . str_replace($search,$replace,$rec['renounce_cd_code']) . "\t"
            . str_replace($search,$replace,$rec['order_size']) . "\t"
            . str_replace($search,$replace,$p) . "\t"
            . str_replace($search,$replace,$totala). "\t"
            . str_replace($search,$replace,$rec['available_rights']) . "\t"
            . str_replace($search,$replace,$rec['user_name']) . "\t" 
            . str_replace($search,$replace,$rec['order_date']) . "\t";  
        $rowData .= $value;  
        $setData .= trim($rowData) . "\n";     
    }
  
  header("Content-type: application/octet-stream");  
  header("Content-Disposition: attachment; filename=RIGHTS_".$symbol_name.".xls");  
  header("Pragma: no-cache");  
  header("Expires: 0"); 
  echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif (!empty($_POST["unsubscribed_list"])) {
    $symbol_id = $_POST['symbol'];
    $corp_ann_id = $_POST['corp_ann_id'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    $wc = $dbh->prepare("SELECT symbol_id, symbol, name FROM symbol WHERE symbol_id = ?");
    $wc->bindParam(1, $symbol_id);
    $wc->execute();
    $state = $wc->fetch(PDO::FETCH_ASSOC);
    $symbol = $state['symbol'];
    $symbol_name = $state['name'];

    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    echo '
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-xs-12">
          <div class="page-header">&emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
            <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Rights Unsubscribed List</div> 
            <div class="lead" style="font-size: 40%;  margin-top:-25px;">
              Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">Security Symbol : <b>'.$symbol_name.' ('.$symbol.')</b></div>
        </div>
      </div>';
      $wc = $dbh->prepare("SELECT ca.cd_code, ca.title, CONCAT_WS(' ', ca.f_name, ca.l_name) AS fl_name, ca.ID, ca.phone, b.bank_short_name AS bank, ca.bank_account AS Account_no, ca.address, (sdh.ribon_volume - COALESCE(ri.order_size, 0)) AS Volume 
          FROM spot_date_holding sdh
          JOIN client_account ca ON sdh.client_id = ca.client_id
          LEFT JOIN (
              SELECT cd_code, SUM(order_size) AS order_size
              FROM rights_issue
              WHERE type IN ('S', 'R') AND symbol_id = ? 
              -- AND YEAR(order_date) = ?
              AND DATE(order_date) BETWEEN ? AND ? 
              GROUP BY cd_code
          ) ri ON ca.cd_code = ri.cd_code
          JOIN banks b ON ca.bank_id = b.bank_id
          WHERE sdh.corp_announcement_id = ? 
          AND sdh.ribon_volume > 0 
          AND (sdh.ribon_volume - COALESCE(ri.order_size, 0)) > 0
      ");
      $wc->bindParam(1, $symbol_id);
      // $wc->bindParam(2, $record_year);
      $wc->bindParam(2, $from_date);
      $wc->bindParam(3, $to_date);
      $wc->bindParam(4, $corp_ann_id);
      $wc->execute();
      $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
      echo'
        <div class="row">
          <div class="col-lg-12 table-responsive">
            <table id="table_unsubs_id" class="table table-striped table-bordered">
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>
                      <th>Sl#</th>
                      <th style="text-align:left;">CD CODE</th>
                      <th style="text-align:left;">NAME</th>
                      <th style="text-align:left;">CID</th>
                      <th style="text-align:left;">PHONE</th>
                      <th style="text-align:left;">ADDRESS</th>
                      <th style="text-align:left;">AVAILABLE RIGHTS</th>
                  </tr>
              </thead>
              <tbody>';
              $i = 1;
              $total = 0;
              foreach($rows as $state) {
                  $total += $state['Volume'];
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td style="text-align:left;">'.$state['cd_code'].'</td>
                    <td style="text-align:left;">'.$state['title'] ." ". $state['fl_name'].'</td>
                    <td style="text-align:left;">'.$state['ID'].'</td>
                    <td style="text-align:left;">'.$state['phone'].'</td>
                    <td style="text-align:left;">'.$state['address'].'</td>
                    <td style="text-align:left;">'.$state['Volume'].'</td>
                  </tr>';
                  $i++;
                }
                echo'
              </tbody>
          </table>
          
          <script type="text/javascript">
            $(document).ready(function() {
                $("#table_unsubs_id").DataTable();
            });
          </script>

          <div class="col-lg-12 text-right">
            Total Unsubscribed: '.number_format($total, 0, ".", ",").'
          </div>

        </section>
        <div class="row no-print">
          <div class="col-xs-12">
            &emsp;&emsp;<a href="loadReport.php?ul_ge_export=ul_ge_export&symbol_id='.$symbol_id.'&corp_ann_id='.$corp_ann_id.'&from_date='.$from_date.'&to_date='.$to_date.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
          </div>
        </div>';
}
elseif(!empty($_GET['ul_ge_export'])) 
{       
      $symbol_id = $_GET['symbol_id'];
      $corp_ann_id = $_GET['corp_ann_id'];
      $from_date = $_GET['from_date'];
      $to_date = $_GET['to_date'];
    
      $stmt = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id = ?");
      $stmt->execute([$symbol_id]);
      $symbol_name = $stmt->fetchColumn();

      $wc = $dbh->prepare("SELECT ca.cd_code, ca.title, CONCAT_WS(' ', ca.f_name, ca.l_name) AS fl_name, ca.ID, ca.phone, b.bank_short_name AS bank, ca.bank_account AS Account_no, ca.address, (sdh.ribon_volume - COALESCE(ri.order_size, 0)) AS Volume 
          FROM spot_date_holding sdh
          JOIN client_account ca ON sdh.client_id = ca.client_id 
          LEFT JOIN (
              SELECT cd_code, SUM(order_size) AS order_size
              FROM rights_issue
              WHERE type IN ('S', 'R') AND symbol_id = ? 
              AND DATE(order_date) BETWEEN ? AND ? 
              GROUP BY cd_code
          ) ri ON ca.cd_code = ri.cd_code
          JOIN banks b ON ca.bank_id = b.bank_id
          WHERE sdh.corp_announcement_id = ? 
          AND sdh.ribon_volume > 0 
          AND (sdh.ribon_volume - COALESCE(ri.order_size, 0)) > 0
      ");
      $wc->bindParam(1, $symbol_id);
      $wc->bindParam(2, $from_date);
      $wc->bindParam(3, $to_date);
      $wc->bindParam(4, $corp_ann_id);
      $wc->execute();

      $columnHeader = '';  
      $i=1;

      $replace   = array("\n");
      $search  = array('');

      $columnHeader = "SNO\t CD CODE\t NAME\t CID\t PHONE\t BANK\t ACCOUNT NO\t ADDRESS\t AVAILABLE RIGHTS\t"; 
      $setData = '';  
      while ($rec=$wc->fetch()) { 
          if($wc->rowCount() <= 0) 
          {}
          $rowData = '';
          if($rec['Volume'] == 0){
          
          }
          else {
            $value = $i++ . "\t ". 
            str_replace($search,$replace,$rec['cd_code']) . "\t".
            str_replace($search,$replace,trim($rec['title'])." ".$rec['fl_name']) ."\t".
            str_replace($search,$replace,trim($rec['ID']))."\t".
            str_replace($search,$replace,trim($rec['phone']))."\t".
            str_replace($search,$replace,trim($rec['bank']))."\t".
            str_replace($search,$replace,trim($rec['Account_no']))."\t".
            str_replace($search,$replace,trim($rec['address'])) ."\t".
            str_replace($search,$replace,trim($rec['Volume'])) ."\t";
            $rowData .= $value;  
            $setData .= trim($rowData) . "\n";  
        }
               
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=Rights_Unsubscribed_".$symbol_name.".xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif (!empty($_POST["rightsRefundList"])) {
    $symbol = $_POST['symbol'];
    $sysTime = date("Y-m-d");

    $wc = $dbh->prepare("SELECT s.symbol_id, s.name, r.price_discovered 
        FROM symbol s
        JOIN rights_issue_auction r on s.symbol_id = r.symbol_id
        WHERE s.symbol=:symbol AND r.type = 'B' AND r.price_discovered != 0 limit 1
    ");
    $wc->bindParam(':symbol', $symbol);
    $wc->execute();
    $state = $wc->fetch();
    $symbol_id = isset($state['symbol_id']) ? $state['symbol_id'] : '';
    $price = isset($state['price_discovered']) ? $state['price_discovered'] : '';
    $symbol_name = isset($state['name']) ? $state['name'] : '';
    echo '
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-xs-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Rights Unsubscribed List</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
             Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">Security Symbol : '.$symbol.'</div>
          <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$symbol_name.'</div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table  table-striped">
            <thead style="background-color: #D6EAF8; font-size: 80%;">
              <tr>
                <th>Sl#</th>
                <th style="text-align:left;">CD CODE</th>
                <th style="text-align:left;">NAME</th>
                <th style="text-align:left;">CID/DISN</th>
                <th style="text-align:left;">BANK ACCOUNT</th>
                <th style="text-align:left;">PHONE</th>
                <th style="text-align:left;">ADDRESS</th>
                <th style="text-align:left;">ALLOCATED RIGHTS</th>
                <th style="text-align:left;">PRICE DISCOVERED</th>
                <th style="text-align:left;">COMMISSION</th>
                <th style="text-align:left;">TOTAL</th>
              </tr>
            </thead>
            <tbody>';
              $i = 1;
              $t = 0;
              $ta = 0;
              $wc = $dbh->prepare("SELECT r.cd_code, c.title, c.f_name, c.l_name, c.ID, c.bank_account, c.phone, c.address, r.order_size, r.bid_price, r.order_size * r.bid_price, r.allocated_size, r.price_discovered, round(0.02 * (r.allocated_size * r.price_discovered)) as commission, (r.allocated_size * r.price_discovered) + round(0.02 * (r.allocated_size * r.price_discovered)) as Total, (r.order_size * r.bid_price) - ((r.allocated_size * r.price_discovered) + round(0.02 * (r.allocated_size * r.price_discovered))) as Payable, r.user_name 
                from rights_issue_auction r 
                JOIN client_account c ON r.cd_code = c.cd_code
                where r.type = 'B' AND r.allocated_size != 0 AND r.symbol_id = :symbol AND r.price_discovered >= :price 
                ORDER BY user_name ASC
              ");
              $wc->bindParam(':symbol', $symbol_id);
              $wc->bindParam(':price', $price);
              $wc->execute();
              $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
              foreach($rows as $state) {
                echo' 
                <tr style="font-size: 70%;">
                  <td>'.$i.'</td>
                  <td style="text-align:left;">'.$state['cd_code'].'</td>
                  <td style="text-align:left;">'.$state['title'] ." ". $state['f_name'] ." ".  $state['l_name'].'</td>
                  <td style="text-align:left;">'.$state['ID'].'</td>
                  <td style="text-align:left;">'.$state['bank_account'].'</td>
                  <td style="text-align:left;">'.$state['phone'].'</td>
                  <td style="text-align:left;">'.$state['address'].'</td>
                  <td style="text-align:left;">'.$state['allocated_size'].'</td>
                  <td style="text-align:left;">'.$state['price_discovered'].'</td>
                  <td style="text-align:left;">'.$state['commission'].'</td>
                  <td style="text-align:left;">'.$state['Total'].'</td>
                </tr>';
                $t += $state['allocated_size']; 
                $ta += $state['Total']; 
                $i++;
              }
              echo'
              <tr>
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align:right;">'.number_format($t,0,".",",").'</td>
                <td></td>
                <td></td>
                <td style="text-align:right;">'.number_format($ta,0,".",",").'</td>
              </tr>
            </tbody>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <div class="row no-print">
    <div class="col-xs-12">
    &emsp;&emsp;<a href="loadReport.php?ul_refund_export=ul_refund_export&symbol_id='.$symbol_id.'&price='.$price.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
    </div>
  </div>';
}
elseif(!empty($_GET['ul_refund_export'])) {
  $replace   = array("\n");
  $search  = array('');
  $symbol_id=$_GET['symbol_id'];
  $price=$_GET['price'];
   $wc= $dbh->prepare("SELECT r.cd_code,c.title,c.f_name,c.l_name,c.ID,c.bank_account,c.phone,c.address,r.order_size,r.bid_price,r.order_size* r.bid_price as AtColl,r.allocated_size,r.price_discovered,round(0.005*(r.allocated_size* r.price_discovered)) as commission,(r.allocated_size* r.price_discovered)+round(0.005*(r.allocated_size* r.price_discovered)) as Total,(r.order_size* r.bid_price)-((r.allocated_size* r.price_discovered)+round(0.005*(r.allocated_size* r.price_discovered))) as Payable,r.user_name from rights_issue r,client_account c where r.cd_code=c.cd_code and r.`type`='B' and r.allocated_size!=0 and r.symbol_id=:symbol and r.price_discovered>=:price order by user_name ASC");
          $wc->bindParam(':symbol',$symbol_id);
          $wc->bindParam(':price',$price);
          $wc->execute();
    $columnHeader = '';  
    $i=1;
    $columnHeader = "SNO" . "\t" . "BROKER" . "\t" . "CD CODE" . "\t". "NAME" . "\t". "CID/DISN" . "\t". "BANK ACCOUNT" . "\t". "PHONE". "\t" ."ADDRESS" . "\t" ."ORDER SIZE"."\t" ."ORDER PRICE"."\t" ."TOTAL AMT"."\t" . "ALLOCATED RIGHTS" . "\t". "PRICE DISCOVERED". "\t" ."COMMISSION". "\t"  ."TOTAL". "\t"  ."REFUND". "\t" ; 
    $setData = '';  
    while ($rec=$wc->fetch()) { 
       if($wc->rowCount() <= 0) 
       {}
      $rowData = ''; 

      $value = $i++ . "\t ". str_replace($search,$replace,$rec['user_name']) ."\t ". str_replace($search,$replace,$rec['cd_code']) . "\t". str_replace($search,$replace,trim($rec['title'])." ".$rec['f_name']." ".$rec['l_name']) . "\t". 
      str_replace($search,$replace,$rec['ID'])
       . "\t". str_replace($search,$replace,trim($rec['bank_account']). " -") . "\t". str_replace($search,$replace,$rec['phone']) ."\t".str_replace($search,$replace,trim($rec['address'])) . "\t".str_replace($search,$replace,trim($rec['order_size'])) ."\t".str_replace($search,$replace,trim($rec['bid_price'])) ."\t".str_replace($search,$replace,trim($rec['AtColl'])) .
       "\t". str_replace($search,$replace,$rec['allocated_size']) . "\t" . str_replace($search,$replace,$rec['price_discovered']) . "\t" . str_replace($search,$replace,$rec['commission']) . "\t" . str_replace($search,$replace,$rec['Total']) . "\t" . str_replace($search,$replace,$rec['Payable'])  . "\t";  
       $rowData .= $value;  
      $setData .= trim($rowData) . "\n";  
    }
    
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=Rights_REFUND_List.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif  (!empty($_GET['zge_export'])) {
    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');
    $fromDate  = $_GET['fromDate'];
    $toDate    = $_GET['toDate'];
    $symbol_id    = $_GET['symbol_id'];

    $query = "SELECT e.member_broker, e.sub_user, e.cd_code, if(e.side = 'B', e.lot_size_execute, 0) as BUY, if(e.side = 'S', e.lot_size_execute, 0) as SELL, e.order_exe_price, e.lot_size_execute * e.order_exe_price as amount, s.symbol, e.order_date 
        FROM executed_orders e 
        JOIN symbol s ON e.symbol_id = s.symbol_id 
        WHERE order_date BETWEEN :fdate AND :tdate 
    ";
    if ($symbol_id != 0) {
        $query .= " AND e.symbol_id=:sym_id ";
    }
    $query .= " ORDER BY e.member_broker ASC";

    $executed_orders = $dbh->prepare($query);
    $executed_orders->bindParam(':fdate', $fromDate);
    $executed_orders->bindParam(':tdate', $toDate);
    if ($symbol_id != 0) {
        $executed_orders->bindParam(':sym_id', $symbol_id);
    }
    $executed_orders->execute();

    $columnHeader = '';  
    $i = 1;
    $columnHeader = "SNO\t MEMBER BROKER\t TRADING PLATFORM\t CD CODE\t ORDER ENTRY\t BUY\t SELL\t ORDER_EXE_PRICE\t AMOUNT\t SYMBOL\t DATE \t"; 
    $setData = '';  
    while ($rec = $executed_orders->fetch()) { 
      $platform = (strlen($rec['sub_user']) == 18) ? 'MCAMS' : 'BROKER';

      $rowData = ''; 
      $value = $i++ . "\t ". 
      str_replace($search, $replace, $rec['member_broker']). "\t" .
      str_replace($search, $replace, $platform). "\t" .
      str_replace($search, $replace, trim($rec['cd_code'])." \t".
      $rec['sub_user']." \t".
      $rec['BUY']."\t".
      $rec['SELL']). "\t". 
      str_replace($search, $replace, $rec['order_exe_price']) . "\t". 
      str_replace($search, $replace, $rec['amount']) . "\t". 
      str_replace($search, $replace, $rec['symbol']) . "\t". 
      str_replace($search, $replace, $rec['order_date'])."\t";  
      $rowData .= $value;  
      $setData .= trim($rowData) . "\n";
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=DetailedtradeDetails.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif (isset($_POST["commisionReport"])) 
{
    $brokerId = $_POST['brokerId'];
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];
    
    $brokerName = ($_POST['brokerId'] == '0') ? $brokerName = "ALL" : $_POST['brokerId'];

    $query = $dbh->prepare("SELECT 
                              SUM(CASE WHEN side = 'B' THEN lot_size_execute ELSE 0 END) AS ShareBuy,
                              SUM(CASE WHEN side = 'S' THEN lot_size_execute ELSE 0 END) AS ShareSell,
                              SUM(lot_size_execute) AS TotalExecutedShare,
                              ROUND(0.0015 * SUM(order_exe_price * lot_size_execute), 2) AS TradingFees, 
                              ROUND(0.00025 * SUM(order_exe_price * lot_size_execute), 2) AS DepositoryFees,
                              ROUND(SUM(order_exe_price * lot_size_execute), 2) AS TotalTradingAmt 
                          FROM executed_orders 
                          WHERE 
                              (:brokerId = '0' OR participant_code = :brokerId) AND
                              (:fdate = '0' OR DATE(order_date) >= :fdate) AND 
                              (:tdate = '0' OR DATE(order_date) <= :tdate);
    ");
    $query->bindParam(':brokerId',$brokerId);
    $query->bindParam(':fdate',$fromDate);
    $query->bindParam(':tdate',$toDate);
    $query->execute();
    $state = $query->fetch();

    $sysTime = date("Y-m-d");
    echo '
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-xs-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Commission Report</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
              Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div>
             </center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">Broker : <strong>'.$brokerName.'</strong></div>
          <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : <strong>'.$fromDate.'</strong> TO : <strong>'.$toDate.'</strong></div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
            <table id="tableId" class="table table-striped">
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th style="text-align:center;">Executed Buy Shares</th>
                  <th style="text-align:center;">Executed Sell Shares</th>
                  <th style="text-align:center;">Total Executed Shares</th>
                  <th style="text-align:center;">Total Value</th>
                  <th style="text-align:center;">Trading Fees</th>
                  <th style="text-align:center;">Depository Fees</th>
                  <th style="text-align:center;">Total Fees (RSEB)</th>
                </tr>
              </thead>
              <tbody>
                <tr style="font-size: 70%;">
                  <td style="text-align:center;">'.number_format(isset($state['ShareBuy']) ? $state['ShareBuy'] : 0).'</td>
                  <td style="text-align:center;">'.number_format(isset($state['ShareSell']) ? $state['ShareSell'] : 0).'</td>
                  <td style="text-align:center;">'.number_format(isset($state['TotalExecutedShare']) ? $state['TotalExecutedShare'] : 0).'</td>
                  <td style="text-align:center;">'.number_format(isset($state['TotalTradingAmt']) ? $state['TotalTradingAmt'] : 0, 2).'</td>
                  <td style="text-align:center;">'.number_format(isset($state['TradingFees']) ? $state['TradingFees'] : 0, 2).'</td>
                  <td style="text-align:center;">'.number_format(isset($state['DepositoryFees']) ? $state['DepositoryFees'] : 0, 2).'</td>
                  <td style="text-align:center;">'.number_format($state['TradingFees'] + $state['DepositoryFees'],2).'</td>
                </tr>
            </tbody>
          </table>
        </section>
        <div class="row no-print">
        </div>';
}
elseif (isset($_POST["priceAdjustment"])) {
    $level = $_POST['level'];
    $levelName = "";
    
    if($_POST['level'] == '1'){
      $levelName = "One Month";
      $query= $dbh->prepare("SELECT 
            t.symbol_id, m.symbol, m.name
            FROM 
            (SELECT 
            r.symbol_id,
            MAX(DATE(r.order_date)) Order_Date
            FROM executed_orders r 
            GROUP BY r.symbol_id 
            ORDER BY r.symbol_id ASC
            ) t
            LEFT JOIN symbol m ON t.symbol_id = m.symbol_id 
            -- WHERE t.Order_Date NOT BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()
            WHERE t.Order_Date <= DATE(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
            AND m.security_type NOT IN ('GB', 'CP') 
            UNION ALL 
            SELECT s.symbol_id, s.symbol, s.name 
            FROM symbol s WHERE s.symbol_id NOT IN (SELECT symbol_id FROM executed_orders r) AND s.security_type ='OS' 
            AND s.status=1 AND s.trsstatus=1
            ORDER BY symbol ASC");
    }else{
      $levelName = "One Year";
      $query= $dbh->prepare("SELECT 
            t.symbol_id, m.symbol, m.name
            FROM 
            (SELECT 
            r.symbol_id,
            MAX(DATE(r.order_date)) Order_Date
            FROM executed_orders r 
            GROUP BY r.symbol_id 
            ORDER BY r.symbol_id ASC
            ) t 
            LEFT JOIN symbol m ON t.symbol_id = m.symbol_id 
            -- WHERE t.Order_Date NOT BETWEEN DATE_SUB(NOW(), INTERVAL 1 YEAR) AND NOW()
            WHERE t.Order_Date <= DATE_SUB(NOW(), INTERVAL 1 YEAR) 
            AND m.security_type NOT IN ('GB', 'CP') 
            UNION ALL 
            SELECT s.symbol_id, s.symbol, s.name 
            FROM symbol s WHERE s.symbol_id NOT IN (SELECT symbol_id FROM executed_orders r) AND s.security_type ='OS' 
            AND s.status=1 AND s.trsstatus=1
            ORDER BY symbol ASC");
    }
    $query->execute();
    
    $sysTime = date("Y-m-d");
    echo '
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-xs-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Price Adjustment Report</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
              Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div>
             </center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">Untraded For : <strong>'.$levelName.'</strong></div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <table class="table table-striped" id="tableId">
            <thead style="background-color: #D6EAF8; font-size: 80%;">
              <tr>
                <th style="text-align:right;">Sl. No</th>
                <th style="text-align:right;">Symbol Name</th>
                <th style="text-align:center;">Company Name</th>
              </tr>
            </thead>
            <tbody>';
            $i=1;
            foreach ($query as $state) {
              echo'
              <tr style="font-size: 70%;">
                <td style="text-align:right;">'.$i.'</td>
                <td style="text-align:right;">'.$state['symbol'].'</td>
                <td style="text-align:center;">'.$state['name'].'</td>
              </tr>';
              $i++;
            }  
            echo'
            </tbody>
          </table>
        </section>
        <div class="row no-print"></div>';
}
elseif (isset($_POST["get_no_pledge_audit"])) {
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00'; 
    
    echo'
    <div class="box">
      <div class="box-body">
        <div class="row">
          <div class="col-lg-6 table-responsive">
            <strong>Pledged Volume</strong>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Symbol</th>
                  <th>Pledge Volume</th>
                </tr>
              </thead>
              <tbody>';
              $stmt = $dbh->prepare("SELECT 
                    s.symbol, SUM(c.pledge_volume) AS pledge_volume
                    FROM cds_pledge c 
                    LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
                    WHERE c.pledge_volume > 0 
                    AND c.pledge_date BETWEEN :fdate AND :tdate
                    GROUP BY c.symbol_id
                    ORDER BY c.symbol_id ASC
              ");
              $stmt->bindParam(':fdate', $fromDate);
              $stmt->bindParam(':tdate', $toDate);
              $stmt->execute();
              $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              $pledge__vol = 0;
              foreach ($rows as $state) {
                $pledge__vol += $state['pledge_volume'];
                echo'
                <tr>
                  <td>'.$i.'</td>
                  <td>'.$state['symbol'].'</td>
                  <td>'.$state['pledge_volume'].'</td>
                </tr>';
                $i++;
              }
              echo'
                <tr>
                  <td colspan="2"><b>Total Pledge Volume</b></td>
                  <td><b>'.number_format($pledge__vol).'</b></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="col-lg-6 table-responsive">
            <strong>Pledge Released Volume</strong>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Symbol</th>
                  <th>Released Volume</th>
                </tr>
              </thead>
              <tbody>';
              $stmt = $dbh->prepare("SELECT 
                    s.symbol, SUM(c.pledge_volume) AS pledge_volume
                    FROM cds_pledge c 
                    LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
                    WHERE c.pledge_volume < 0 
                    AND c.pledge_date BETWEEN :fdate AND :tdate
                    GROUP BY c.symbol_id
                    ORDER BY c.symbol_id ASC
              ");
              $stmt->bindParam(':fdate', $fromDate);
              $stmt->bindParam(':tdate', $toDate);
              $stmt->execute();
              $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              $release__vol = 0;
              foreach ($rows as $state) {
                $release__vol += $state['pledge_volume'];
                echo'
                <tr>
                  <td>'.$i.'</td>
                  <td>'.$state['symbol'].'</td>
                  <td>'.$state['pledge_volume'].'</td>
                </tr>';
                $i++;
              }
              echo'
                <tr>
                  <td colspan="2"><b>Total Pledge Volume</b></td>
                  <td><b>'.number_format($release__vol).'</b></td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
    ';
}
elseif (isset($_POST['get_highlights_details'])) {
    $fromdate = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    $date = new DateTime($fromdate);
    $monthName = $date->format('F');

    $trade = [];

    // month name
    $trade['monthname'] = strtoupper($monthName);

    // get volume traded 
    $v_sql = $dbh->prepare("SELECT 
            SUM(e.lot_size_execute) AS volume_traded, SUM(e.lot_size_execute * e.order_exe_price) AS traded_worth
            FROM executed_orders e
            WHERE DATE(e.order_date) BETWEEN ? AND ?
            AND e.side='B'
    ");
    $v_sql->bindParam(1, $fromdate);
    $v_sql->bindParam(2, $to_date);
    $v_sql->execute();
    $rows = $v_sql->fetch();

    if ($rows !== false) {
      $trade['volume_traded'] = number_format($rows['volume_traded']);
      $trade['traded_worth'] = number_format($rows['traded_worth'], 2);
    }

    // get executed trade
    $v_sql_1 = $dbh->prepare("SELECT 
            count(*) as trade_execute
            FROM executed_orders e
            WHERE DATE(e.order_date) BETWEEN ? AND ? 
    ");
    $v_sql_1->bindParam(1, $fromdate);
    $v_sql_1->bindParam(2, $to_date);
    $v_sql_1->execute();
    $rows_1 = $v_sql_1->fetch();
    if ($rows_1 !== false) {
      $trade['trade_execute'] = number_format($rows_1['trade_execute']);
    }

    // get user and symbol count
    $v_sql_2 = $dbh->prepare("SELECT 
        COUNT(DISTINCT e.cd_code) AS user, COUNT(DISTINCT e.symbol_id) AS symbols
        FROM executed_orders e
        WHERE DATE(e.order_date) BETWEEN ? AND ? 
    ");
    $v_sql_2->bindParam(1, $fromdate);
    $v_sql_2->bindParam(2, $to_date);
    $v_sql_2->execute();
    $rows_2 = $v_sql_2->fetch();
    if ($rows_2 !== false) {
      $trade['user_count'] = $rows_2['user'];
      $trade['symbol_count'] = $rows_2['symbols'];
    }

    // get mcams user traded
    $v_sql_3 = $dbh->prepare("SELECT 
        COUNT(DISTINCT e.cd_code) AS mcams_user 
        FROM executed_orders e
        WHERE DATE(e.order_date) BETWEEN ? AND ? 
        AND LENGTH(e.sub_user) = 18 
    ");
    $v_sql_3->bindParam(1, $fromdate);
    $v_sql_3->bindParam(2, $to_date);
    $v_sql_3->execute();
    $rows_3 = $v_sql_3->fetch();
    if ($rows_3 !== false) {
      $trade['mCams_user'] = $rows_3['mcams_user'];
    }

    // get worth traded through mcams
    $v_sql_4 = $dbh->prepare("SELECT 
            SUM(IF(e.side = 'B', e.lot_size_execute, 0)) AS buy_vol, 
            SUM(IF(e.side = 'B', e.lot_size_execute * e.order_exe_price, 0)) AS buy_vol_worth, 
            SUM(IF(e.side = 'S', e.lot_size_execute, 0)) AS sell_vol, 
            SUM(IF(e.side = 'S', e.lot_size_execute * e.order_exe_price, 0)) AS sell_vol_worth
            FROM executed_orders e
            WHERE DATE(e.order_date) BETWEEN ? AND ? 
            AND LENGTH(e.sub_user) = 18
    ");
    $v_sql_4->bindParam(1, $fromdate);
    $v_sql_4->bindParam(2, $to_date);
    $v_sql_4->execute();
    $rows_4 = $v_sql_4->fetch();
    if ($rows_4 !== false) {
      $trade['buy_vol'] = number_format($rows_4['buy_vol']);
      $trade['buy_vol_worth'] = number_format($rows_4['buy_vol_worth'], 2);
      $trade['sell_vol'] = number_format($rows_4['sell_vol']);
      $trade['sell_vol_worth'] = number_format($rows_4['sell_vol_worth'], 2);
    }

    $data['success'] = true;
    $data['message'] = '';
    $data['data'] = $trade;
    header("HTTP/1.1 200 ok");
    echo json_encode($data);
    die();
}
elseif (!empty($_POST["market_caps"])) 
{
    $symbol = $_POST['symbol'];

    $wc = $dbh->prepare("SELECT * from symbol where symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();

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
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Market Capitalisation</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">
               Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table  table-striped" >
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th>SL#</th>
                  <th>Companies</th>                    
                  <th>Paid up shares</th>
                  <th>Last traded price</th>
                  <th>Market Capitalisation</th>
                </tr>
                </thead>
                <tbody>';
                $i = 1;
                $sh = 0;
                $sh1 = 0;
                if($symbol != '') {
                    $sql=$dbh->prepare("SELECT s.symbol, sum(h.volume+ h.pledge_volume+ h.block_volume+ h.pending_out_vol) as total,m.market_price as LSP, (sum(h.volume+ h.pledge_volume+ h.block_volume+ 
                      h.pending_out_vol))*m.market_price as MC 
                      from cds_holding h, symbol s, market_price m 
                      WHERE s.symbol_id = h.symbol_id AND s.symbol_id = m.symbol_id AND h.symbol_id = :sid AND s.status = 1 AND s.security_type='OS'
                    ");
                    $sql->bindParam(':sid',$state['symbol_id']);
                    $sql->execute();
                    $state1 = $sql->fetch();
                    echo'
                    <tr style="font-size: 70%;">
                      <td>'.$i++.'</td> 
                      <td>'.$symbol.'</td>
                      <td>'.number_format($state1['total'],0,".",",").'</td>
                      <td>'.$state1['LSP'].'</td>
                      <td>'.number_format($state1['MC'],0,".",",").'</td>
                    </tr>';
                    $totalShares = $state1['total'];
                    $sh = $totalShares + $sh;
                    $totalShares1 = $state1['MC'];
                    $sh1 = $totalShares1 + $sh1;
                }
                else{
                  $sql = $dbh->prepare("SELECT DISTINCT symbol_id,symbol from symbol WHERE security_type='OS' and status='1' order by symbol asc");
                  $sql->execute();
                  foreach($sql as $state2)
                  {
                      $sql1=$dbh->prepare("SELECT s.symbol,sum(h.volume+ h.pledge_volume+ h.block_volume+ h.pending_out_vol) as total,m.market_price as LSP, (sum(h.volume+ h.pledge_volume+ h.block_volume+ 
                        h.pending_out_vol)) * m.market_price as MC from cds_holding h, symbol s, market_price m 
                        WHERE s.symbol_id=h.symbol_id and s.symbol_id=m.symbol_id and h.symbol_id=:sid and s.`status`=1 and s.security_type='OS'
                      ");
                      $sql1->bindParam(':sid', $state2['symbol_id']);
                      $sql1->execute();
                      foreach($sql1 as $state3)
                      { 
                      echo'
                      <tr style="font-size: 70%;">
                        <td>'.$i++.'</td> 
                        <td>'.$state2['symbol'].'</td>
                        <td>'.number_format($state3['total'],0,".",",").'</td>
                        <td>'.$state3['LSP'].'</td>
                        <td>'.number_format($state3['MC'],0,".",",").'</td>
                      </tr>';
                      $totalShares=$state3['total'];
                      $sh=$totalShares+$sh;
                      $totalShares1=$state3['MC'];
                      $sh1=$totalShares1+$sh1;
                    }                  
                  }
              }              
              echo'
              <tr><td>Total</td><td></td><td>'.number_format($sh,0,".",",").'</td><td></td><td>'.number_format($sh1,0,".",",").'</td></tr></tbody>
            </table></section> 
            <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&mcaps=mcaps" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
            </div>';
}
elseif (isset($_POST["generate_historical_data"])) {
    $symbol = $_POST['symbol'];
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];

    if ($symbol != '' || $fromDate != '' || $toDate != '') {
      echo'
      <div class="row">
        <div class="col-lg-12">
          <div class="table-responsive">
            <table class="table table-striped table-bordered" id="table__id" width="100%">
              <thead>
                <tr>
                  <th>Sl No</th>
                  <th>Symbol</th>
                  <th>Date</th>
                  <th>Traded Volume</th>
                  <th>High Price</th>
                  <th>Low Price</th>
                  <th>Market Price</th>
                </tr>
              </thead>
              <tbody>';
              $stmt = $dbh->prepare("SELECT 
                          s.symbol,
                          DATE_FORMAT(eo.order_date, '%d/%m/%Y') AS Date,
                          eo.lot_size_execute AS Traded_Volume,
                          eo.max_order_exe_price AS Max_Price,    
                          eo.min_order_exe_price AS Min_Price,
                          mph.price AS market_price
                      FROM (
                          SELECT 
                              DATE(order_date) AS order_date,
                              SUM(lot_size_execute) AS lot_size_execute,
                              MAX(order_exe_price) AS max_order_exe_price,
                              MIN(order_exe_price) AS min_order_exe_price,
                              symbol_id
                          FROM executed_orders
                          WHERE symbol_id = ? 
                              AND side = 'B' 
                              AND DATE(order_date) >= ? 
                              AND DATE(order_date) <= ? 
                          GROUP BY DATE(order_date), symbol_id 
                      ) AS eo 
                      JOIN symbol AS s ON eo.symbol_id = s.symbol_id 
                      JOIN market_price_history AS mph ON DATE(mph.date) = eo.order_date AND mph.symbol_id = ? 
                      GROUP BY eo.order_date 
              ");
              $stmt->execute([$symbol, $fromDate, $toDate, $symbol]);
              $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              foreach ($results as $key => $value) {
                echo'
                <tr>
                  <td>'.$i.'</td>
                  <td>'.$value['symbol'].'</td>
                  <td>'.$value['Date'].'</td>
                  <td>'.$value['Traded_Volume'].'</td>
                  <td>'.$value['Max_Price'].'</td>
                  <td>'.$value['Min_Price'].'</td>
                  <td>'.$value['market_price'].'</td>
                </tr>';
                $i++;
              }
              echo'
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-12 text-right"><br>
        <a href="loadReport.php?exportHistoricalDate=exportHistoricalDate&fromDate='.$fromDate.'&toDate='.$toDate.'&symbol='.$symbol.'" target="_blank" class="btn btn-success"><i class="fa fa-angle-double-right"></i> <strong>Export Historical Data</strong></a>
      </div>

      <script type="text/javascript">
        $(function () {
            $("#table__id").DataTable();
        });
      </script>';
      die();
  } else {
      echo'<div style="color: red;">Please enter all the mandatory fields.</div>';
      die();
  }
}
elseif (!empty($_GET['exportHistoricalDate'])){
    $symbol = $_GET['symbol'];
    $fromDate = $_GET['fromDate'];
    $toDate = $_GET['toDate'];

    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');

    $getSymbol = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id = ?");
    $getSymbol->execute([$symbol]);
    $symbol_name = $getSymbol->fetchColumn();

    $stmt = $dbh->prepare("SELECT 
                s.symbol,
                DATE_FORMAT(eo.order_date, '%d/%m/%Y') AS Date,
                eo.lot_size_execute AS Traded_Volume,
                eo.max_order_exe_price AS Max_Price,    
                eo.min_order_exe_price AS Min_Price,
                mph.price AS market_price
            FROM (
                SELECT 
                    DATE(order_date) AS order_date,
                    SUM(lot_size_execute) AS lot_size_execute,
                    MAX(order_exe_price) AS max_order_exe_price,
                    MIN(order_exe_price) AS min_order_exe_price,
                    symbol_id
                FROM executed_orders
                WHERE symbol_id = ? 
                    AND side = 'B' 
                    AND DATE(order_date) >= ? 
                    AND DATE(order_date) <= ? 
                GROUP BY DATE(order_date), symbol_id 
            ) AS eo 
            JOIN symbol AS s ON eo.symbol_id = s.symbol_id 
            JOIN market_price_history AS mph ON DATE(mph.date) = eo.order_date AND mph.symbol_id = ? 
            GROUP BY eo.order_date 
    ");
    $stmt->execute([$symbol, $fromDate, $toDate, $symbol]);

    $columnHeader = "SlNo"."\t"."Symbol"."\t"."Date"."\t"."Traded Volume"."\t"."High Price"."\t"."Low Price"."\t"."Market Price"."\t"; 
    $setData = '';  
    $i = 1;
    while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) { 
      $rowData = '';  
      $value = $i++."\t"
        .str_replace($search,$replace,trim($rec['symbol']))."\t"
        .str_replace($search,$replace,$rec['Date'])."\t"
        .str_replace($search,$replace,$rec['Traded_Volume'])."\t"
        .str_replace($search,$replace,$rec['Max_Price'])."\t"
        .str_replace($search,$replace,trim($rec['Min_Price']))."\t"
        .str_replace($search,$replace,trim($rec['market_price']))."\t"
      ;
      $rowData .= $value;
      $setData .= trim($rowData)."\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=Historical_Data_".$symbol_name.".xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader)."\n".$setData."\n"; 
}
elseif (!empty($_POST["get_share_dtls_statement"])) {
    $cid_no = $_POST['cid_no'];
    $from_date = $_POST['from_date'].' 00:00:00';
    $to_date = $_POST['to_date'].' 23:59:00';
    $sysTime = date("Y-m-d");

    $wc = $dbh->prepare("SELECT 
            CASE
                WHEN a.acc_type = 'I' THEN CONCAT(a.f_name,' ', COALESCE(a.l_name, ''))
                ELSE a.f_name
            END AS full_name, a.ID, a.address 
        FROM client_account a 
        WHERE a.ID = ? ORDER BY a.client_id DESC LIMIT 1
    ");
    $wc->bindParam(1, $cid_no);
    $wc->execute();
    $state = $wc->fetch();
    echo '
    <br><br>
    <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
               <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Account Activity Report</div> 
               <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$from_date.'</b>&nbsp;To Date :<b>'.$to_date.'</b></div></center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">.
            <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['full_name'].' , CID/DISN# '.$state['ID'].'</div>
          </div>
        </div>';
        $stmt = $dbh->prepare("SELECT a.symbol_id, s.symbol
                    FROM (
                        SELECT symbol_id
                        FROM (
                            SELECT c.symbol_id
                            FROM cds_dep_wit c
                            JOIN client_account a ON c.cd_code = a.cd_code 
                            WHERE c.entry_date BETWEEN :from__date AND :to__date
                            AND a.ID = :cid__no
                            AND c.type IN ('B', 'S')
                            UNION ALL
                            SELECT ct.symbol_id
                            FROM client_account ca 
                            JOIN cds_transfer ct ON ca.cd_code IN (ct.from_acc, ct.to_acc)
                            WHERE ct.trs_date BETWEEN :from__date AND :to__date
                            AND ca.ID=:cid__no
                            UNION ALL
                            SELECT s.symbol_id
                            FROM spot_date_holding s
                            JOIN client_account cat ON s.client_id = cat.client_id
                            WHERE s.record_date BETWEEN :from__date AND :to__date
                            AND cat.ID = :cid__no
                            AND s.ribon_volume != 0
                            AND s.announcement_type IN (2, 4) 
                            UNION ALL
                            SELECT ri.symbol_id
                            FROM rights_issue ri
                            JOIN client_account tt ON tt.cd_code IN (ri.cd_code, ri.renounce_cd_code)
                            JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id
                            WHERE an.announcement_type = 1
                            AND tt.ID = :cid__no
                            AND ri.type IN ('S', 'R')
                            AND an.record_date BETWEEN :from__date AND :to__date
                            UNION ALL
                            SELECT ria.symbol_id
                            FROM rights_issue_auction ria
                            JOIN client_account tta ON tta.cd_code IN (ria.cd_code, ria.renounce_cd_code)
                            JOIN corporate_announcement ana ON ria.symbol_id = ana.symbol_id
                            WHERE ana.announcement_type = 1
                            AND tta.ID = :cid__no
                            AND ria.type IN ('S', 'R')
                            AND ana.record_date BETWEEN :from__date AND :to__date
                            UNION ALL 
                            SELECT r_auc.symbol_id 
                            FROM rights_issue r_auc 
                            JOIN client_account ttauc ON r_auc.cd_code = ttauc.cd_code 
                            JOIN symbol s ON r_auc.symbol_id = s.symbol_id
                            WHERE ttauc.ID = :cid__no
                            AND r_auc.type IN ('B', 'SA')
                            AND r_auc.order_date BETWEEN :from__date AND :to__date
                            AND r_auc.bid_price >= r_auc.price_discovered
                            UNION ALL 
                            SELECT r_aucb.symbol_id 
                            FROM rights_issue_auction r_aucb
                            JOIN client_account ttaucb ON r_aucb.cd_code = ttaucb.cd_code 
                            JOIN symbol s ON r_aucb.symbol_id = s.symbol_id
                            WHERE ttaucb.ID = :cid__no
                            AND r_aucb.type IN ('B', 'SA')
                            AND r_aucb.order_date BETWEEN :from__date AND :to__date
                            AND r_aucb.bid_price >= r_aucb.price_discovered
                        ) subquery
                        GROUP BY symbol_id
                    ) a
                    JOIN symbol s ON a.symbol_id = s.symbol_id
                    WHERE s.status = 1
                    ORDER BY a.symbol_id");
        $stmt->execute([
            ':cid__no' => $cid_no,
            ':from__date' => $from_date,
            ':to__date' => $to_date,
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $i = 1;
        foreach ($results as $res) {
          echo'
          <div class="row">
            <div class="col-xs-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">Symbol: '.$res['symbol'].'</div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table  table-striped" >
                <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>
                    <th>Sl#</th>
                    <th>Date</th>
                    <th>Transaction Type</th>
                    <th>CD Code</th>
                    <th>To CD Code</th>
                    <th>Transaction vol</th>
                    <th>Actual Vol</th>
                    <th>Balance</th>
                  </tr>
                </thead>
                <tbody>';
                $i = 1;

                $stmt = $dbh->prepare("SELECT SUM(h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) AS total_vol
                    FROM cds_holding h 
                    JOIN client_account a ON h.cd_code = a.cd_code 
                    WHERE a.ID = :c_id
                    AND h.symbol_id = :s_id
                    AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) > 0");
                $stmt->bindParam(':c_id', $cid_no);
                $stmt->bindParam(':s_id', $res['symbol_id']);
                $stmt->execute();
                $get = $stmt->fetch();
                $present_vol = isset($get['total_vol']) ? $get['total_vol'] : 0;

                $wc = $dbh->prepare("WITH combined_data AS (
                          SELECT c.cd_code AS cd, c.cd_code, c.symbol_id, s1.symbol, c.type, c.remarks, c.volume, c.entry_date, c.volume as actl_vol
                          FROM cds_dep_wit c
                          JOIN client_account a ON c.cd_code = a.cd_code
                          JOIN symbol s1 ON c.symbol_id = s1.symbol_id
                          WHERE c.entry_date BETWEEN :from_date AND :to_date
                          AND a.ID = :cid
                          AND c.type IN ('B', 'S')
                          AND c.symbol_id = :sym_id
                          UNION ALL
                          SELECT ct.from_acc, ct.to_acc, ct.symbol_id, s2.symbol, ct.type, ct.remarks, ct.trs_vol, ct.trs_date, ct.trs_vol as actl_vol
                          FROM client_account ca 
                          JOIN cds_transfer ct ON ca.cd_code IN (ct.from_acc, ct.to_acc)
                          JOIN symbol s2 ON ct.symbol_id = s2.symbol_id
                          WHERE ct.trs_date BETWEEN :from_date AND :to_date
                          AND ca.ID = :cid
                          AND ct.symbol_id = :sym_id
                          UNION ALL
                          SELECT cat.cd_code, cat.cd_code, s.symbol_id, sm.symbol, s.announcement_type, can.rate, s.ribon_volume, s.record_date, s.volume as actl_vol
                          FROM spot_date_holding s
                          JOIN client_account cat ON s.client_id = cat.client_id
                          JOIN symbol sm ON s.symbol_id = sm.symbol_id
                          JOIN corporate_announcement can ON s.corp_announcement_id = can.corp_announcement_id
                          WHERE s.record_date BETWEEN :from_date AND :to_date
                          AND cat.ID = :cid
                          AND s.symbol_id = :sym_id
                          AND s.ribon_volume != 0
                          AND s.announcement_type IN (2, 4)
                          UNION ALL
                          SELECT ri.cd_code, ri.renounce_cd_code, ri.symbol_id, s.symbol, 'SUB' AS type, an.rate, ri.order_size, ri.order_date, sp.volume AS actl_vol
                          FROM rights_issue ri 
                          JOIN client_account tt ON tt.cd_code IN (ri.cd_code, ri.renounce_cd_code) 
                          JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id
                          JOIN symbol s ON ri.symbol_id = s.symbol_id
                          JOIN spot_date_holding sp ON tt.client_id = sp.client_id
                          WHERE an.announcement_type = 1
                          AND s.symbol_id = :sym_id
                          AND tt.ID = :cid
                          AND ri.type = 'S' 
                          AND ri.order_date BETWEEN :from_date AND :to_date
                          AND YEAR(ri.order_date) = YEAR(an.announcement_date)
                          AND sp.corp_announcement_id = an.corp_announcement_id
                          UNION ALL
                          SELECT ria.cd_code, ria.renounce_cd_code, ria.symbol_id, sa.symbol, 'SUB' AS type, ana.rate, ria.order_size, ria.order_date, spa.volume AS actl_vol
                          FROM rights_issue_auction ria 
                          JOIN client_account tta ON tta.cd_code IN (ria.cd_code, ria.renounce_cd_code)  
                          JOIN corporate_announcement ana ON ria.symbol_id = ana.symbol_id
                          JOIN symbol sa ON ria.symbol_id = sa.symbol_id
                          JOIN spot_date_holding spa ON tta.client_id = spa.client_id
                          WHERE ana.announcement_type = 1
                          AND sa.symbol_id = :sym_id
                          AND tta.ID = :cid
                          AND ria.type = 'S' 
                          AND ria.order_date BETWEEN :from_date AND :to_date
                          AND YEAR(ria.order_date) = YEAR(ana.announcement_date)
                          AND spa.corp_announcement_id = ana.corp_announcement_id
                          UNION ALL 
                          SELECT r_auc.cd_code, r_auc.renounce_cd_code, r_auc.symbol_id, s.symbol, 'BID' AS type, r_auc.bid_price, r_auc.allocated_size AS volume, r_auc.order_date, r_auc.order_size
                          FROM rights_issue r_auc 
                          JOIN client_account ttauc ON r_auc.cd_code = ttauc.cd_code 
                          JOIN symbol s ON r_auc.symbol_id = s.symbol_id
                          WHERE s.symbol_id = :sym_id
                          AND ttauc.ID = :cid
                          AND r_auc.type IN ('B', 'SA')
                          AND r_auc.order_date BETWEEN :from_date AND :to_date
                          AND r_auc.bid_price >= r_auc.price_discovered
                          UNION ALL 
                          SELECT 
                          r_aucb.cd_code, r_aucb.renounce_cd_code, r_aucb.symbol_id, s.symbol, 'BID' AS type, r_aucb.bid_price, r_aucb.allocated_size AS volume, r_aucb.order_date, r_aucb.order_size
                          FROM rights_issue_auction r_aucb
                          JOIN client_account ttaucb ON r_aucb.cd_code = ttaucb.cd_code 
                          JOIN symbol s ON r_aucb.symbol_id = s.symbol_id
                          WHERE s.symbol_id = :sym_id
                          AND ttaucb.ID = :cid
                          AND r_aucb.type IN ('B', 'SA')
                          AND r_aucb.order_date BETWEEN :from_date AND :to_date
                          AND r_aucb.bid_price >= r_aucb.price_discovered
                          UNION ALL
                          SELECT rir.cd_code, rir.renounce_cd_code, rir.symbol_id, s.symbol, rir.type, rir.bid_price, rir.order_size, rir.order_date, rir.order_size AS actl_vol
                          FROM rights_issue rir
                          JOIN client_account ttr ON rir.renounce_cd_code = ttr.cd_code
                          JOIN symbol s ON rir.symbol_id = s.symbol_id 
                          WHERE s.symbol_id = :sym_id
                          AND ttr.ID = :cid
                          AND rir.type = 'R'
                          AND rir.order_date BETWEEN :from_date AND :to_date
                          UNION ALL
                          SELECT rira.cd_code, rira.renounce_cd_code, rira.symbol_id, s.symbol, rira.type, rira.bid_price, rira.order_size, rira.order_date, rira.order_size AS actl_vol
                          FROM rights_issue_auction rira
                          JOIN client_account ttra ON rira.renounce_cd_code = ttra.cd_code
                          JOIN symbol s ON rira.symbol_id = s.symbol_id 
                          WHERE s.symbol_id = :sym_id
                          AND ttra.ID = :cid
                          AND rira.type = 'R'
                          AND rira.order_date BETWEEN :from_date AND :to_date
                      )
                      SELECT *
                      FROM combined_data 
                      GROUP BY volume, entry_date
                      ORDER BY entry_date ASC");
                $wc->bindParam(':cid', $cid_no);
                $wc->bindParam(':from_date', $from_date);
                $wc->bindParam(':to_date', $to_date);
                $wc->bindParam(':sym_id', $res['symbol_id']);
                $wc->execute();
                $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
                $total_share = 0;
                $cd_code = '';
                foreach($rows as $det) {
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td>'.$det['entry_date'].'</td>';
                    if($det['type'] === 'B') {
                      $v = $det['volume'];
                      $total_share += $det['volume'];
                      echo'
                      <td>BUY</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.$det['volume'].'</td>
                      <td></td>
                      <td>'.abs($total_share).'</td>';
                    }
                    elseif ($det['type'] === 'S') {
                      // $v = $det['volume'] * -1;
                      $v = abs($det['volume']);
                      $total_share -= $v;
                      echo'
                      <td>SELL</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.substr($det['volume'], 1).'</td>
                      <td></td>
                      <td>'.abs($total_share).'</td>';
                    }
                    elseif ($det['type'] === 'TR') {
                      $v = $det['volume'];
                      /*if ($cd_code === $det['cd']) {
                        $total_share -= $det['volume'];
                      } elseif ($cd_code === $det['cd_code']) {
                        $total_share += $det['volume'];
                      }*/
                      // $total_share += $det['volume'];
                      echo'
                      <td>TRANSFER</td>
                      <td>'.$det['cd'].'</td>
                      <td>'.$det['cd_code'].'</td>
                      <td>'.$det['volume'].'</td>
                      <td></td>
                      <!-- <td>'.abs($total_share).'</td> -->
                      <td></td>
                      ';
                    }
                    elseif ($det['type'] === 'SUB') {
                      $v = $det['volume'];
                      $total_share_sub = $det['volume'] + $det['actl_vol'];
                      // $total_share += $det['volume'];
                      $total_share = $total_share_sub;
                      echo'
                      <td>RIGHTS</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.$det['volume'].'</td>
                      <td>'.$det['actl_vol'].'</td>
                      <td>'.abs($total_share_sub).'</td>';
                    }
                    elseif ($det['type'] === 'R') {
                      $v = $det['volume'];
                      $total_share_r = $total_share + $det['volume'];
                      $total_share = $total_share_r;
                      echo'
                      <td>RENOUNCE</td>
                      <td>'.$det['cd'].'</td>
                      <td>'.$det['cd_code'].'</td>
                      <td>'.$det['volume'].'</td>
                      <td></td>
                      <td>'.abs($total_share_r).'</td>';
                    }
                    elseif ($det['type'] == 2) {
                      $v = $det['volume'];
                      $total_bonus = $det['volume'] + $det['actl_vol'];
                      $total_share = $total_bonus;
                      echo'
                      <td>BONUS</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.$det['volume'].'</td>
                      <td>'.$det['actl_vol'].'</td>
                      <td>'.abs($total_bonus).'</td>';
                    }
                    elseif($det['type'] == 4) {
                      $v = $det['volume'];
                      $total_share = $det['volume'] - $det['actl_vol'];
                      echo'
                      <td>BUY BACK</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.$det['volume'].'</td>
                      <td>'.$det['actl_vol'].'</td>
                      <td>'.abs($total_share).'</td>';
                    }
                    elseif($det['type'] === 'BID' || $det['type'] === 'SA') {
                      $v = $det['volume'];
                      $total_share += $det['volume'];
                      echo'
                      <td>BID</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.$det['volume'].'</td>
                      <td></td>
                      <td>'.abs($total_share).'</td>';
                    }
                  echo'
                  </tr>';
                  $cd_code = $det['cd'];
                  $i++;
                }
                echo'
                <tr style="font-size: 70%;">
                  <td colspan="5" align="right">Current Volume:</td>
                  <td>'.$present_vol.'</td>
                  <td></td>
                </tr>
              </tbody>
            </table>';
          }
          echo'
          </section> 
          <div class="row no-print">
            <div class="col-xs-12">
              &emsp;&emsp;<a href="loadReportPrint.php?cid_no='.$cid_no.'&toDate='.$to_date.'&fromDate='.$from_date.'&get_share_dtls_statement=get_share_dtls_statement" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            </div>
          </div>';
}
?>
