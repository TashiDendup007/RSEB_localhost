<?php
date_default_timezone_set("Asia/Thimphu");
include('../FILES/sessionStartFile_cdscss.php');
include ('../../CONNECTIONS/db.php');
include ('../../functions/function-sanitize.php');
require('../../fpdf/fpdf.php');

if (!empty($_POST["dep"])) {
    $cd = $_POST['cdcode'];
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00';
    $sysTime = date("Y-m-d");

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, b.name, a.address 
        FROM client_account a 
        JOIN adm_institution b ON a.institution_id = b.institution_id 
        where a.cd_code=:cd
    ");
    $wc->bindParam(':cd',$cd);
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
        $sql = "SELECT a.symbol_id, s.symbol
                  FROM (
                      SELECT c.symbol_id
                      FROM cds_dep_wit c
                      WHERE c.entry_date BETWEEN :fromDate AND :toDate
                          AND c.cd_code = :cdCode
                          AND c.type IN ('B', 'S')
                      UNION ALL
                      SELECT ct.symbol_id
                      FROM cds_transfer ct
                      WHERE ct.trs_date BETWEEN :fromDate AND :toDate
                          AND (:cdCode IN (ct.from_acc, ct.to_acc))
                      UNION ALL
                      SELECT s.symbol_id
                      FROM spot_date_holding s
                      JOIN client_account ca ON s.client_id = ca.client_id
                      WHERE s.record_date BETWEEN :fromDate AND :toDate
                          AND ca.cd_code = :cdCode
                          AND s.ribon_volume != 0
                          AND s.status = 1
                          AND s.announcement_type IN (2, 4)
                      UNION ALL
                      SELECT ri.symbol_id
                      FROM rights_issue_auction ri
                      JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id
                      WHERE an.announcement_type = 1
                          AND (:cdCode IN (ri.cd_code, ri.renounce_cd_code))
                          AND ri.type IN ('S', 'R')
                          AND an.status = 0
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

                $sql = "WITH combined_data AS (
                          SELECT c.cd_code AS cd, c.cd_code, c.symbol_id, s1.symbol, c.type, c.remarks, c.volume, c.entry_date
                          FROM cds_dep_wit c
                          JOIN symbol s1 ON c.symbol_id = s1.symbol_id
                          WHERE c.entry_date BETWEEN :fromDate AND :toDate
                              AND c.cd_code = :cdCode
                              AND c.type IN ('B', 'S')
                              AND c.symbol_id = :sid

                          UNION ALL

                          SELECT ct.from_acc, ct.to_acc, ct.symbol_id, s2.symbol, ct.type, ct.remarks, ct.trs_vol, ct.trs_date
                          FROM cds_transfer ct
                          JOIN symbol s2 ON ct.symbol_id = s2.symbol_id
                          WHERE ct.trs_date BETWEEN :fromDate AND :toDate
                              AND ct.symbol_id = :sid
                              AND (:cdCode IN (ct.from_acc, ct.to_acc))

                          UNION ALL

                          SELECT ca.cd_code, ca.cd_code, s.symbol_id, sm.symbol, s.announcement_type, can.rate, s.ribon_volume, s.record_date
                          FROM spot_date_holding s
                          JOIN client_account ca ON s.client_id = ca.client_id
                          JOIN symbol sm ON s.symbol_id = sm.symbol_id
                          JOIN corporate_announcement can ON s.corp_announcement_id = can.corp_announcement_id
                          WHERE s.record_date BETWEEN :fromDate AND :toDate
                              AND ca.cd_code = :cdCode
                              AND s.symbol_id = :sid
                              AND s.ribon_volume != 0
                              AND s.announcement_type IN (2, 4)
                              AND s.status = 1

                          UNION ALL

                          SELECT ri.cd_code, ri.renounce_cd_code, ri.symbol_id, s.symbol, an.announcement_type, an.rate, ri.order_size, ri.order_date
                          FROM rights_issue_auction ri
                          JOIN corporate_announcement an ON ri.symbol_id = an.symbol_id
                          JOIN symbol s ON ri.symbol_id = s.symbol_id
                          WHERE an.announcement_type = 1
                              AND s.symbol_id = :sid
                              AND (:cdCode IN (ri.cd_code, ri.renounce_cd_code))
                              AND ri.type IN ('S', 'R')
                              AND an.status = 0
                              AND ri.order_date BETWEEN :fromDate AND :toDate
                      )
                      SELECT *
                      FROM combined_data
                      ORDER BY entry_date ASC;
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
                      $v = $det['volume'] * -1;
                      echo'
                      <td>SELL</td>
                      <td>-</td>
                      <td>'.substr($det['volume'], 1).'</td>
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
                    elseif ($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd']) && $det['type'] == 1) {
                      $v = $det['volume'];
                      echo'
                      <td>RIGHTS</td>
                      <td>'.$det['volume'].'</td>
                      <td>-</td>
                      <td>'.$v.'</td>';
                      $rightsSubscribeTotal += $v;
                    }
                    elseif ($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd_code']) && $det['type'] == 1) {
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
elseif(!empty($_POST["topVolLead"])) {
  $symbol = $_POST['symbol'];
  $top = $_POST['top'];
  $sysTime = date("Y-m-d");

  $wc = $dbh->prepare("SELECT * from symbol where symbol=:symbol");
  $wc->bindParam(':symbol',$symbol);
  $wc->execute();
  $state = $wc->fetch();
  echo '
  <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-xs-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Top Volume Leaders</div> 
          </div>
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
             Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
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
elseif(!empty($_POST["pus"])) {
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
elseif (!empty($_POST["market_caps"])) {
  $symbol=$_POST['symbol'];

  $wc= $dbh->prepare("SELECT symbol, symbol_id FROM symbol where symbol=:symbol");
  $wc->bindParam(':symbol', $symbol);
  $wc->execute();
  $state = $wc->fetch();
  $sysTime = date("Y-m-d");
  echo '
  <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-lg-12">
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
        <div class="col-lg-12 table-responsive">
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

              $query = "SELECT s.symbol, sum(h.volume + h.pledge_volume + h.block_volume + h.pending_out_vol) as total, m.market_price as LSP, (sum(h.volume + h.pledge_volume + h.block_volume + h.pending_out_vol)) * m.market_price as MC 
                      FROM cds_holding h
                      JOIN symbol s ON h.symbol_id = s.symbol_id 
                      JOIN market_price m ON h.symbol_id = m.symbol_id
                      WHERE h.symbol_id=:sid AND s.status=1 AND s.security_type='OS'
              ";
              if ($symbol != '') {
                $sql = $dbh->prepare($query);
                $sql->bindParam(':sid', $state['symbol_id']);
                $sql->execute();
                $state1 = $sql->fetch();
                echo'
                <tr style="font-size: 70%;">
                   <td>'.$i++.'</td> 
                   <td>'.$symbol.'</td>                         
                   <td>'.number_format($state1['total'], 0, ".", ",").'</td>
                   <td>'.$state1['LSP'].'</td>
                   <td>'.number_format($state1['MC'], 0, ".", ",").'</td>
                </tr>';
                $totalShares = $state1['total'];
                $sh = $totalShares + $sh;
                $totalShares1 = $state1['MC'];
                $sh1 = $totalShares1 + $sh1;
              } else {
                  $sql = $dbh->prepare("SELECT DISTINCT symbol_id, symbol 
                    FROM symbol WHERE security_type='OS' AND status='1' ORDER BY symbol ASC");
                  $sql->execute();
                  foreach ($sql as $state2) {
                    $sql1 = $dbh->prepare($query);
                    $sql1->bindParam(':sid',$state2['symbol_id']);
                    $sql1->execute();
                    foreach ($sql1 as $state3) { 
                      echo'
                      <tr style="font-size: 70%;">
                         <td>'.$i++.'</td> 
                         <td>'.$state2['symbol'].'</td>                         
                         <td>'.number_format($state3['total'], 0, ".", ",").'</td>
                         <td>'.$state3['LSP'].'</td>
                         <td>'.number_format($state3['MC'], 0, ".", ",").'</td>
                      </tr>';
                      $totalShares = $state3['total'];
                      $sh = $totalShares + $sh;
                      $totalShares1 = $state3['MC'];
                      $sh1 = $totalShares1 + $sh1;
                    }                  
                  }
                }
                echo '
                <tr>
                <td>Total</td>
                <td></td>
                <td>'.number_format($sh, 0 , ".", ",").'</td>
                <td></td>
                <td>'.number_format($sh1, 0, ".", ",").'</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section> 
      <div class="row no-print">
        <div class="col-xs-12">
          &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&mcaps=mcaps" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
      </div>';
}
elseif(!empty($_POST["announcement"])) 
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
elseif(!empty($_POST["announcementList"])) {
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
                <td>'.number_format($state['rate'], 2).'</td>
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
elseif(!empty($_POST["entitlement_list"])) {

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
            WHERE symbol_id=:sid 
              AND (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) > 0 
              GROUP BY c.cd_code 
              ORDER BY c.cd_code ASC
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
                            echo '<td>' . strtoupper(isset($state['title']) ? $state['title'] : '') . " " . strtoupper(isset($state['f_name']) ? $state['f_name'] : '') . " " . strtoupper(isset($state['l_name']) ? $state['l_name'] : '') . '</td>';
                        } else {
                            echo '<td>' . strtoupper(isset($state['f_name']) ? $state['f_name'] : '') . '</td>';
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
elseif(!empty($_POST["pledgeActivity"])) {
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
elseif (!empty($_POST["pledgeDetails"])) {
    $symbol = strtoupper($_POST['symbol']);
    $plType = $_POST['plType'];
    $sysTime = date("Y-m-d");
    
    echo'
    <section class="invoice" style="background:rgb(248, 249, 249);">
      <div class="row">
        <div class="col-lg-12">
          <div class="page-header">
            &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
            <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Pledge Details</div> 
             <div class="lead" style="font-size: 40%;  margin-top:-25px;">
             Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
          </div>
        </div>
      </div>';
      if ($plType == 'S') {
        $wc = $dbh->prepare("SELECT symbol_id, symbol, name FROM symbol WHERE symbol=:symbol");
        $wc->bindParam(':symbol', $symbol);
        $wc->execute();
        $state = $wc->fetch();
        $symbol_id = $state['symbol_id'];
        echo'
        <div class="row">
          <div class="col-lg-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">Security Symbol : '.$symbol.'</div>
            <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['name'].'</div>
          </div>
        </div>';
        $wc = $dbh->prepare("SELECT pl.cd_code, cl.title, cl.f_name, cl.l_name, cl.ID, sum(pl.pledge_volume) as pledge_volume, pl.pledgee 
          FROM cds_pledge pl
          JOIN client_account cl ON pl.cd_code = cl.cd_code
          WHERE symbol_id = :sid 
          -- AND pledge_volume > 0
          GROUP BY pl.cd_code, pl.pledgee 
          HAVING SUM(pl.pledge_volume) > 0 
          ORDER BY pl.pledge_volume DESC
          
        ");
        $wc->bindParam(':sid', $state['symbol_id']);
        $wc->execute();
        echo'
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table class="table  table-striped">
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th>Sl.</th> 
                  <th>CD Code</th>
                  <th>Account Name</th>
                  <th>CID#</th>
                  <th>Pledgee</th>
                  <th style="text-align:right;">Number of Shares Pledged</th>
                </tr>
              </thead>
            <tbody>';
            $i = 1;
            $sh = 0;
            foreach ($wc as $state) {
              echo'
              <tr style="font-size: 70%;">
                <td>'.$i.'</td>
                <td>'.$state['cd_code'].'</td>
                <td>'.$state['title']." " .$state['f_name']. " ".$state['l_name'].'</td>
                <td>'.$state['ID'].'</td>                               
                <td>'.$state['pledgee'].'</td>
                <td style="text-align:right;">'.number_format($state['pledge_volume'],0,".",",").'</td>
              </tr>';
              $totalShares = $state['pledge_volume'];
              $sh = $totalShares + $sh;
              $i++;
            }
        } else {
          echo'
          <div class="row">
            <div class="col-xs-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">Pledgee : '.$symbol.'</div>
            </div>
          </div>';
          $wc = $dbh->prepare("SELECT pl.cd_code, cl.title, cl.f_name, cl.l_name, cl.ID, s.symbol, sum(pl.pledge_volume) as pledge_volume 
            FROM cds_pledge pl
            JOIN client_account cl ON pl.cd_code = cl.cd_code
            JOIN symbol s ON pl.symbol_id = s.symbol_id
            WHERE pl.pledgee=:pl 
            -- AND pledge_volume > 0
            GROUP BY pl.symbol_id, pl.cd_code 
            HAVING SUM(pl.pledge_volume) > 0
            ORDER BY pl.pledge_volume DESC 
          ");
          $wc->bindParam(':pl',$symbol);
          $wc->execute();    
          echo'
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table  table-striped" >
                <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>
                    <th>Sl.</th> 
                    <th>CD Code</th>                    
                    <th>Account Name</th>
                    <th>CID#</th>                          
                    <th>Symbol</th>
                    <th style="text-align:right;">Number of Shares Pledged</th>
                  </tr>
                </thead>
                <tbody>';
                $i = 1;
                $sh = 0;
                foreach ($wc as $state) {
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td>'.$state['cd_code'].'</td>                         
                    <td>'.$state['title']." " .$state['f_name']. " ".$state['l_name'].'</td>
                    <td>'.$state['ID'].'</td>
                    <td>'.$state['symbol'].'</td>
                    <td style="text-align:right;">'.number_format($state['pledge_volume'],0,".",",").'</td>
                  </tr>';
                  $totalShares = $state['pledge_volume'];
                  $sh = $totalShares+$sh;
                  $i++;
                }
        }
        echo '
        <tr>
          <td>Total</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td style="text-align:right;">'.number_format($sh,0,".",",").'</td>
        </tr>
      </tbody>
    </table>
  </section> 
  <div class="row no-print">
    <div class="col-xs-12">
      &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$symbol.'&plType='.$plType.'&pledgeDetails=pledgeDetails" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a> &emsp;&emsp;<a href="generate_entitelment.php?symbol='.$symbol.' &plType='.$plType.'&pledgeDetailsExport=pledgeDetailsExport" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
    </div>
  </div>';
}
elseif(!empty($_POST["deposits"])) 
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
elseif (isset($_POST["individual_report"])) {
  $cid = $_POST['cid'];

  /*$wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, c.title, c.tpn, c.address
        FROM client_account c
        WHERE (c.ID = :cid OR c.cd_code = :cid)
        ORDER BY c.client_id DESC LIMIT 1
  ");*/
   $wc = $dbh->prepare("SELECT c.title, c.tpn, c.address, CONCAT_WS(' ', c.f_name, c.l_name) AS full_name, c.phone, c.email, b.bank_short_name, c.bank_account, c.cd_code 
        FROM client_account c 
        JOIN cds_holding h ON c.cd_code = h.cd_code 
        JOIN banks b ON c.bank_id = b.bank_id 
        WHERE (c.ID = :cid OR c.cd_code= :cid)
        AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) > 0
        ORDER BY c.client_id 
        LIMIT 1
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
        <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['title'].' '.$state['full_name'].'</br>
          Phone No : '.$state['phone'].', Email : '.$state['email'].'<br> 
          BANK : '.$state['bank_short_name'].', Account No : '.$state['bank_account'].', TPN : '.$state['tpn'].'<br> 
          ADDRESS : '.$state['address'].'</div>
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
                WHERE (a.ID = :cid OR a.cd_code= :cid)
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
else if(isset($_POST["BalanceConfirmation"])) 
{
    $cid = $_POST['cid'];
    $currency = $_POST['currency'];
    $rate = $_POST['rate'] ? $_POST['rate'] : 1;
    $date = isset($_POST['date']) ? $_POST['date'] : 0;
    
    $wc= $dbh->prepare("SELECT 
    c.*, 
    a.name
    FROM 
        client_account c
    LEFT JOIN 
        adm_institution a ON a.institution_id = c.institution_id
    LEFT JOIN 
        cds_holding h ON c.cd_code = h.cd_code
    LEFT JOIN 
        market_price mp ON mp.symbol_id = h.symbol_id
    WHERE 
        (h.cd_code = :cid OR c.ID = :cid)
        AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) != 0
    ");
    $wc->bindParam(':cid',$cid);
    $wc->execute();
    $state=$wc->fetch();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
        echo '
        <section class="invoice"  style="padding-left: 50px; padding-right:50px;">
            <div class="row" >
              <div class="col-xs-12" style="margin-top:-20px;">
                <table border="0" width="100%">
                    <tr>
                        <td style="padding-left: 47px;">
                            <img src="../../img/logo.png" alt="Logo">
                        </td>
                        <td>
                            <h3 class="text-center"><strong>༄༄།།  རྒྱལ་གཞུང་འགན་ལེན་བདོག་གཏད་བརྗེ་སོར་ཁང་།</strong></h3>
                            <h3 class="text-center"><strong>ROYAL SECURITIES EXCHANGE OF BHUTAN</strong></h3>
                        </td>
                    </tr>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-12" >
              </br>
              </br>
              <span>
               <div class="lead" style="font-size: 100%; margin-top:-25px; text-align: left;padding-left: 50px; padding-right:50px;"> 
                    <b>CD/MISC/'.date('Y').'/</b>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    if ($date == 0) {
                      echo'<b>Date : '.date('d-m-Y').'</b>';
                    } else {
                      echo'<b>Date : '.$date.'</b>';
                    }
                    echo'
                    </div> 
              </span>
               </br>
               </br>
              <center>
                    <div class="lead" style="font-size: 100%; margin-top:-25px;"> <b>TO WHOM IT MAY CONCERN</b></div> 
              </center>
              </br>
              </br>
              <div class="lead" style="font-size: 100%; margin-top:-10px;padding-left: 50px; padding-right:50px;">
                The Royal Securities Exchange of Bhutan would like to provide the shareholding details of Mr./Mrs./Miss. <b>'.$state['f_name'].' '.$state['l_name']. '</b> bearing CID/DISN # <b>'.$cid.'</b> as follows: </br>
                </div>
              </div>
            </div>';
            echo'
            <div class="row" style="padding-left: 50px; padding-right:50px;">
              <div class="col-xs-12 table-responsive">
                <table class="table  table-striped">
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>
                    <th>Sl#</th>                    
                    <th>CD Code/Symbol</th>
                    <th style="text-align:right;">Number of Shares</th>
                    <th style="text-align:right;">Market Price (Nu.)</th>
                    <th style="text-align:right;">Total Amount (Nu.)</th>';
                    if($currency != 'BTN'){
                      echo'<th style="text-align:right;">Total Amount ('.$currency.')</th>';
                    }
                    
                  echo'</tr>
                  </thead>
                  <tbody>';
            $i=1;
            $save = $dbh->prepare('SELECT a.symbol_id,b.cd_code as cd,a.cd_code FROM cds_holding a
                left join client_account b on a.cd_code=b.cd_code
                left join symbol s on a.symbol_id=s.symbol_id
                where (a.cd_code=:cid OR b.ID=:cid) 
                and s.status=1
                order by symbol_id ASC');
              $save->bindParam(':cid', $cid);
              $save->execute(); 
              $totalNu=0;
              $totaldollars=0;
              $totalYEN = 0;
              foreach($save as $states)
              {                       
                $save5 = $dbh->prepare('SELECT h.*,c.f_name,c.l_name, s.symbol,s.name from cds_holding h,client_account c,symbol s 
                  where h.cd_code=c.cd_code and s.symbol_id=h.symbol_id  and c.cd_code=:cd and h.symbol_id=:sid');
                $save5->bindParam(':cd', $states['cd_code']);
                $save5->bindParam(':sid', $states['symbol_id']);
                $save5->execute();
                $st=$save5->fetch();

                $totvol=$st['volume']+$st['block_volume']+$st['pledge_volume']+$st['pending_out_vol']+$st['pending_in_vol'];
                if ($totvol > 0) {
                  $bv=0;
                  $save1 = $dbh->prepare('SELECT h.*, c.f_name, c.l_name, s.symbol, s.name as sname, mp.market_price 
                    FROM cds_holding h,client_account c,symbol s,market_price mp 
                    where h.cd_code=c.cd_code and s.symbol_id=h.symbol_id AND c.cd_code=:cd and h.symbol_id=:sid and mp.symbol_id=h.symbol_id');
                  $save1->bindParam(':cd', $states['cd_code']);
                  $save1->bindParam(':sid', $states['symbol_id']);
                  $save1->execute();
                  if($save1->rowCount() == 0){
                    $save1 = $dbh->prepare('SELECT h.volume, h.block_volume, h.pledge_volume, h.pending_in_vol, h.pending_out_vol, c.f_name,c.l_name, s.symbol, s.name as sname, s.face_value AS market_price
                        FROM cds_holding h, client_account c, symbol s
                        WHERE h.cd_code=c.cd_code AND s.symbol_id=h.symbol_id AND c.cd_code=:cd AND h.symbol_id=:sid');
                    $save1->bindParam(':cd', $states['cd_code']);
                    $save1->bindParam(':sid', $states['symbol_id']);
                    $save1->execute();
                  }

                  foreach($save1 as $state1)
                  {
                    $market_price_value = $state1['market_price'];

                    if ($date != 0) {
                      $get_price = $dbh->prepare("SELECT FORMAT(avg(m.price), 2) as market_price
                                FROM market_price_history m 
                                WHERE date(m.date) = ? AND m.symbol_id = ?
                      ");
                      $get_price->bindParam(1, $date);
                      $get_price->bindParam(2, $states['symbol_id']);
                      $get_price->execute();
                      $p_price = $get_price->fetch();

                      $market_price_value = $p_price['market_price'];
                    }

                    /*$total = $state1['volume']+$state1['pledge_volume']+$state1['block_volume']+$state1['pending_out_vol']+$state1['pending_in_vol'];
                    $totalNu = $totalNu + ($total * $state1['market_price']);
                    $totaldollars=$totaldollars+($total*$state1['market_price']/$rate);
                    if($state1['volume']==0){$v='-';}else{$v=number_format($state1['volume'],0,".",",");}
                    if($state1['block_volume']==0){$bv='-';}else{$bv=number_format($state1['block_volume'],0,".",",");}
                    if($state1['pledge_volume']==0){$pv='-';}else{$pv=number_format($state1['pledge_volume'],0,".",",");}
                    if($state1['pending_in_vol']==0){$piv='-';}else{$piv=number_format($state1['pending_in_vol'],0,".",",");}
                    if($state1['pending_out_vol']==0){$pov='-';}else{$pov=number_format($state1['pending_out_vol'],0,".",",");} */

                    $total = $state1['volume']+$state1['pledge_volume']+$state1['block_volume']+$state1['pending_out_vol']+$state1['pending_in_vol'];
                    $totalNu = $totalNu + ($total * $market_price_value);
                    // $totaldollars=$totaldollars+($total*$market_price_value/$rate);
                    $totaldollars += ($total * (float)$market_price_value / (float)$rate);

                    if($state1['volume']==0){$v='-';}else{$v=number_format($state1['volume'],0,".",",");}
                    if($state1['block_volume']==0){$bv='-';}else{$bv=number_format($state1['block_volume'],0,".",",");}
                    if($state1['pledge_volume']==0){$pv='-';}else{$pv=number_format($state1['pledge_volume'],0,".",",");}
                    if($state1['pending_in_vol']==0){$piv='-';}else{$piv=number_format($state1['pending_in_vol'],0,".",",");}
                    if($state1['pending_out_vol']==0){$pov='-';}else{$pov=number_format($state1['pending_out_vol'],0,".",",");} 
                    
                    echo'
                      <tr style="font-size: 70%;">
                        <td>'.$i.'</td>
                        <td>'.$states['cd'].'-'.$state1['symbol'].'</td>
                        <td style="text-align:right;">'.number_format($total,2,".",",").'</td>
                        <td style="text-align:right;">'.$market_price_value.'</td>
                        <td style="text-align:right;">'.number_format($total*$market_price_value,2,".",",").'</td>';
                        if($currency != 'BTN'){
                          if($currency == 'YEN'){

                            echo'<td style="text-align:right;">'.number_format($total*$market_price_value*100/$rate,2,".",",").'</td>';
                            $totalYEN += $total*$market_price_value*100/$rate;
                          }else{
                             echo'<td style="text-align:right;">'.number_format($total*$market_price_value/$rate,2,".",",").'</td>';
                          }
                       
                     }

                    echo'</tr>';
                    $i=$i+1;
                  }

                }else{}

            } 
            echo '<tr style="font-size: 70%;">
                    <td></td>';
                    if($currency != 'BTN'){

                      if($currency == 'YEN'){
                        $totaldollars = $totalYEN;
                      }

                      echo'
                    <td><b>Total<b><i> (Rate indicated by the RMA of '.date('d-m-Y').' @ '.$rate.')</i></td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;"><b>'.number_format($totalNu,2,".",",").'</b></td>
                    <td style="text-align:right;"><b>'.number_format($totaldollars,2,".",",").'</b></td>
                    ';}
                    else{
                      echo'
                    <td><b>Total<b><i> The information is as on date : '.date('d-m-Y').')</i></td>
                    <td></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"><b>'.number_format($totaldollars,2,".",",").'</b></td>

                    ';
                  }
                    echo'
                  </tr>';
            echo'</tbody>
                    </table>';
            echo'  <div class="row" style="padding-left: 50px; padding-right:50px;">
            </br></br>
            </br>
            <b>Central Depository </b>
            </br>
            </br>
            </br>

            </div></section>
                    <div class="row no-print">
                    <div class="col-xs-12">
                      &emsp;&emsp;<a href="loadReportPrint.php?cid='.$cid.'&BalanceConfirmation=BalanceConfirmation&rate='.$rate.'&currency='.$currency.'&date='.$date.'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
                    </div>
                    </div>';
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
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00'; 
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

    $i = 1;
    $columnHeader = "SNO\t TYPE\t Name\t ID\t CD CODE\t RENOUNCE CD CODE\t ORDER VOLUME\t PRICE\t TOTAL AMOUNT\t USER NAME\t ORDER TIME\t"; 
    $setData = '';

    $replace   = array("\n");
    $search  = array(''); 

    while ($rec=$wc->fetch()) { 
        if($wc->rowCount() <= 0) 
        {}

        $side = '';
        switch ($rec['type']) {
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

        $price = ($side == 'BID') ? $rec['bid_price'] : $rec['face_value'];
        $totala = ($side == 'BID') ? ($rec['bid_price'] * $rec['order_size']) : $rec['total_amount'];
        
        $rowData = '';  
        $value = $i++ . "\t"
            . str_replace($search,$replace,$side) . "\t"
            . str_replace($search,$replace,$rec['fl_name']). "\t"
            . str_replace($search,$replace,$rec['ID']). "\t"
            . str_replace($search,$replace,$rec['cd_code']). "\t"
            . str_replace($search,$replace,$rec['renounce_cd_code']) . "\t"
            . str_replace($search,$replace,$rec['order_size']) . "\t"
            . str_replace($search,$replace,$price) . "\t"
            . str_replace($search,$replace,$totala). "\t"
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
elseif(!empty($_GET['ul_ge_export'])) {
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
elseif(!empty($_POST["rightsRefundList"])) {
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
elseif(!empty($_GET['ul_refund_export'])) 
{       
      $replace   = array("\n");
      $search  = array('');
      $symbol_id=$_GET['symbol_id'];
      $price=$_GET['price'];
       $wc= $dbh->prepare("SELECT r.cd_code,c.title,c.f_name,c.l_name,c.ID,c.bank_account,c.phone,c.address,r.order_size,r.bid_price,r.order_size* r.bid_price as AtColl,r.allocated_size,r.price_discovered,round(0.02*(r.allocated_size* r.price_discovered)) as commission,(r.allocated_size* r.price_discovered)+round(0.02*(r.allocated_size* r.price_discovered)) as Total,(r.order_size* r.bid_price)-((r.allocated_size* r.price_discovered)+round(0.02*(r.allocated_size* r.price_discovered))) as Payable,r.user_name from rights_issue_auction r,client_account c where r.cd_code=c.cd_code and r.`type`='B' and r.allocated_size!=0 and r.symbol_id=:symbol and r.price_discovered>=:price order by user_name ASC");
              $wc->bindParam(':symbol',$symbol_id);
              $wc->bindParam(':price',$price);
              $wc->execute();
    $columnHeader = '';  
    $i=1;
    /*<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>*/
    $columnHeader = "SNO" . "\t" . "BROKER" . "\t" . "CD CODE" . "\t". "NAME" . "\t". "CID/DISN" . "\t". "BANK ACCOUNT" . "\t". "PHONE". "\t" ."ADDRESS" . "\t" ."ORDER SIZE"."\t" ."ORDER PRICE"."\t" ."TOTAL AMT"."\t" . "ALLOCATED RIGHTS" . "\t". "PRICE DISCOVERED". "\t" ."COMMISSION". "\t"  ."TOTAL". "\t"  ."PAYABLE/RECEIVABLE". "\t" ; 
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
    header("Content-Disposition: attachment; filename=Rights_AUCTION_List.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0"); 
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif (!empty($_GET['zge_export'])) {
    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');
    $fromDate  = $_GET['fromDate'];
    $toDate    = $_GET['toDate'];
    $symbol_id    = $_GET['symbol_id'];
    $table_name    = $_GET['table_name'];
    $trade_type    = $_GET['trade_type'];

    $query = "SELECT e.member_broker, e.sub_user, e.cd_code, if(e.side = 'B', e.lot_size_execute, 0) as BUY, if(e.side = 'S', e.lot_size_execute, 0) as SELL, e.order_exe_price, e.lot_size_execute * e.order_exe_price as amount, s.symbol, e.order_date 
        FROM {$table_name} e 
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
      $value = $i++ ."\t ". 
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
    header("Content-Disposition: attachment; filename=".$trade_type."_DetailedtradeDetails_".$fromDate."_".$toDate.".xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif(isset($_POST["ipoaudit"])) {
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
            <center><div class="lead" style="font-size: 55%; margin-top:-25px;">IPO Orders Audit Details</div> 
            <div class="lead" style="font-size: 40%;  margin-top:-25px;">
              Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'
            </div></center>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="lead" style="font-size: 70%; margin-top:-10px;">FROM : '.$fromDate.' TO : '.$toDate.'</div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12 table-responsive">
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
            <tbody>';
            $i = 1;
            $wc = $dbh->prepare("SELECT i.type, i.cd_code, i.order_size, i.bid_price, i.order_date, c.ID, c.f_name, c.l_name 
              FROM ipo i 
              JOIN client_account c ON i.cd_code = c.cd_code
              WHERE order_date BETWEEN :fdate AND :tdate
            ");
            $wc->bindParam(':fdate', $fromDate);
            $wc->bindParam(':tdate', $toDate);
            $wc->execute();
            $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $state) {
              echo'
              <tr style="font-size: 70%;">
                <td>'.$i.'</td>
                <td style="text-align:right;">'.$state['type'].'</td>
                <td style="text-align:right;">'.$state['cd_code'].'</td>
                <td style="text-align:right;">'.$state['f_name']. ' '.$state['l_name'].'</td>
                <td style="text-align:right;">'.$state['ID'].'</td>
                <td style="text-align:right;">'.$state['order_size'].'</td>
                <td style="text-align:right;">'.$state['bid_price'].'</td>
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
        &emsp;&emsp;<a href="ipo_load.php?ipoaudit_export=ipoaudit_export&fromDate='.$fromDate.'&toDate='.$toDate.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
      </div>
    </div>';
}
elseif (!empty($_GET['ipoaudit_export'])) {
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
elseif (isset($_POST["commisionReport"])) {
    $brokerId = $_POST['brokerId'];
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];

    $brokerName = ($_POST['brokerId'] == '0') ? $brokerName = "ALL" : $_POST['brokerId'];
    
    /*$query = $dbh->prepare("SELECT 
          SUM(IF(side = 'B', lot_size_execute, 0)) ShareBuy,
          SUM(IF(side = 'S', lot_size_execute, 0)) ShareSell,
          SUM(lot_size_execute) TotalExecutedShare,
          ROUND(0.0015 * (SUM(order_exe_price * lot_size_execute)), 2) TradingFees, 
          ROUND(0.00025 * (SUM(order_exe_price * lot_size_execute)), 2) DepositoryFees,
          ROUND(SUM(order_exe_price * lot_size_execute), 2) TotalTradingAmt 
          FROM executed_orders 
          WHERE 
          IF('0' = :brokerId, 1 = 1, participant_code = :brokerId) AND
          IF('0' = :fdate, 1 = 1, DATE(order_date) >= :fdate) AND 
          IF('0' = :tdate, 1 = 1, DATE(order_date) <= :tdate)
    ");*/
    // optimized query
    // $query = $dbh->prepare("SELECT 
    //                           SUM(CASE WHEN side = 'B' THEN lot_size_execute ELSE 0 END) AS ShareBuy,
    //                           SUM(CASE WHEN side = 'S' THEN lot_size_execute ELSE 0 END) AS ShareSell,
    //                           SUM(lot_size_execute) AS TotalExecutedShare,
    //                           ROUND(0.0015 * SUM(order_exe_price * lot_size_execute), 2) AS TradingFees, 
    //                           ROUND(0.00025 * SUM(order_exe_price * lot_size_execute), 2) AS DepositoryFees,
    //                           ROUND(SUM(order_exe_price * lot_size_execute), 2) AS TotalTradingAmt 
    //                       FROM executed_orders 
    //                       WHERE 
    //                           (:brokerId = '0' OR participant_code = :brokerId) AND
    //                           (:fdate = '0' OR DATE(order_date) >= :fdate) AND 
    //                           (:tdate = '0' OR DATE(order_date) <= :tdate);
    // ");
    //fees dynamic
    // Fetch fees from fee_masters table
    $feeQuery = $dbh->prepare("SELECT fee, name FROM fee_masters WHERE type IN ('TF', 'DF') AND name IN ('Trading Fee', 'Depository Fee')");
    $feeQuery->execute();
    $fees = $feeQuery->fetchAll(PDO::FETCH_KEY_PAIR); // Fetch as key-value pairs

    //error_log(print_r($fees, true));
    $tradingFee = isset($fees['Trading Fee']) ? $fees['Trading Fee'] : 0.0015;
    $depositoryFee = isset($fees['Depository Fee']) ? $fees['Depository Fee'] : 0.00025;

    //new query
    $query = $dbh->prepare("SELECT 
          participant_code,
          SUM(CASE WHEN side = 'B' THEN lot_size_execute ELSE 0 END) AS sharebuy,
          SUM(CASE WHEN side = 'S' THEN lot_size_execute ELSE 0 END) AS sharesell,
          SUM(lot_size_execute) AS totalexecutedshare,
          ROUND(:tradingFee * SUM(order_exe_price * lot_size_execute), 2) AS tradingfees, 
          ROUND(:depositoryFee * SUM(order_exe_price * lot_size_execute), 2) AS depositoryfees,
          ROUND(SUM(order_exe_price * lot_size_execute), 2) AS totaltradingamt 
      FROM executed_orders 
      WHERE 
          (:brokerId = '0' OR participant_code = :brokerId) AND
          (:fdate = '0' OR DATE(order_date) >= :fdate) AND 
          (:tdate = '0' OR DATE(order_date) <= :tdate)
      GROUP BY participant_code
      ORDER BY participant_code;
    ");

    $query->bindParam(':tradingFee', $tradingFee);
    $query->bindParam(':depositoryFee', $depositoryFee);
    $query->bindParam(':brokerId', $brokerId);
    $query->bindParam(':fdate', $fromDate);
    $query->bindParam(':tdate', $toDate);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);//new
    $state = $query->fetch();

    // Initialize totals new
    $totalShareBuy = 0;
    $totalShareSell = 0;
    $totalExecutedShare = 0;
    $totalTradingFees = 0;
    $totalDepositoryFees = 0;
    $totalTradingAmt = 0;
    $total_gst_amt = 0;
    $total_final_amt = 0;
    //new

    foreach ($results as $row) {
        $totalShareBuy += $row['sharebuy'];
        $totalShareSell += $row['sharesell'];
        $totalExecutedShare += $row['totalexecutedshare'];
        $totalTradingFees += $row['tradingfees'];
        $totalDepositoryFees += $row['depositoryfees'];
        $totalTradingAmt += $row['totaltradingamt'];
    }

    $sysTime = date("Y-m-d");
    $isAllBrokers = ($brokerId == '0');

    //new
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
                <th style="text-align:center;">Broker ID</th>
                <th style="text-align:center;">Buy Shares</th>
                <th style="text-align:center;">Sell Shares</th>
                <th style="text-align:center;">Total Shares</th>
                <th style="text-align:center;">Total Value</th>
                <th style="text-align:center;">Trading Fee</th>
                <th style="text-align:center;">Depo. Fee</th>
                <th style="text-align:center;">GST</th>
                <th style="text-align:center;">Fee (RSEB)</th>
              </tr>
            </thead>
            <tbody>';

    // Display the selected broker's data
    foreach ($results as $row) {
        $total_trade_depo = round($row['tradingfees'] + $row['depositoryfees'], 2);
        $gst_fee = round($total_trade_depo * 0.05, 2);
        $final_amt = round($gst_fee + $total_trade_depo, 2);

        $total_gst_amt +=  $gst_fee;
        $total_final_amt +=  $final_amt;
        
        echo '
        <tr style="font-size: 70%;">
          <td style="text-align:center;">'.$row['participant_code'].'</td>
          <td style="text-align:center;">'.number_format($row['sharebuy']).'</td>
          <td style="text-align:center;">'.number_format($row['sharesell']).'</td>
          <td style="text-align:center;">'.number_format($row['totalexecutedshare']).'</td>
          <td style="text-align:center;">'.number_format($row['totaltradingamt'], 2).'</td>
          <td style="text-align:center;">'.number_format($row['tradingfees'], 2).'</td>
          <td style="text-align:center;">'.number_format($row['depositoryfees'], 2).'</td>
          <td style="text-align:center;">'.number_format($gst_fee, 2).'</td>
          <td style="text-align:center;">'.number_format($final_amt, 2).'</td>
        </tr>';
    }

    // Display the totals row only if it's for all brokers
    if ($isAllBrokers) {
        echo '
        <tr style="font-size: 70%; background-color: #f2f2f2;">
          <td style="text-align:center;"><strong>Total</strong></td>
          <td style="text-align:center;"><strong>'.number_format($totalShareBuy).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($totalShareSell).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($totalExecutedShare).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($totalTradingAmt, 2).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($totalTradingFees, 2).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($totalDepositoryFees, 2).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($total_gst_amt, 2).'</strong></td>
          <td style="text-align:center;"><strong>'.number_format($total_final_amt, 2).'</strong></td>
        </tr>';
    }

    echo '
        </tbody>
      </table>
    </div>
  </div>
</section>';
}
elseif (!empty($_GET['emd_export'])) {
  $replace   = array("\n","\r\n","\r");
  $search    = array('','','');
  $fromDate  = $_GET['fromDate'];
  $toDate    = $_GET['toDate'];

  $sql = $dbh->prepare("SELECT e.order_no, e.name, e.cd_code, e.cid, e.phone, e.fee_status, e.email, e.created_date, e.app_fee, e.gst 
    FROM emd e WHERE e.fee_status=1 AND e.created_date BETWEEN :fdate AND :tdate GROUP BY e.order_no");
  $sql->bindParam(':fdate',$fromDate);
  $sql->bindParam(':tdate',$toDate);
  $sql->execute();

  $columnHeader = '';  
  $i=1;
  $columnHeader = "SLNO\t Name\t CID No\t CD Code\t Phone\t Amount\t GST\t Order No\t Date\t"; 
  $setData = '';  
  while ($rec=$sql->fetch()) {
    $rowData = '';  
    $value = $i++ . "\t ". 
    str_replace($search, $replace, $rec['name']). "\t" .
    str_replace($search, $replace, trim($rec['cid'])." \t".
    $rec['cd_code']." \t".
    $rec['phone']."\t".
    $rec['app_fee']."\t".
    $rec['gst']). "\t". 
    str_replace($search, $replace, $rec['order_no']) . "\t". 
    str_replace($search, $replace, $rec['created_date']) ."\t";  
    $rowData .= $value;  
    $setData .= trim($rowData) . "\n";
  }  
  header("Content-type: application/octet-stream");  
  header("Content-Disposition: attachment; filename=EMD_Details.xls");  
  header("Pragma: no-cache");  
  header("Expires: 0");  
  echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
}
elseif(isset($_POST["bondReport"])) {
    $reportType = $_POST['reportType'];
    $symbolId = $_POST['symbolId'];
    $symbol22 = $_POST['symbol22'];
    $sysTime = date("Y-m-d");
    
    if($reportType == 1){
      echo'
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Coupon Payment Report</div> 
                  <div class="lead" style="font-size: 40%;  margin-top:-25px;">Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div>
                </center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table id="tableId" class="table table-striped">
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th style="text-align:center;">Sl No</th>
                  <th style="text-align:center;">Name of Bond</th>
                  <th style="text-align:center;">Amount Raised(Nu.)</th>
                  <th style="text-align:center;">Coupon Rate</th>
                  <th style="text-align:center;">Issue Date</th>
                  <th style="text-align:center;">Maturity Date</th>
                  <th style="text-align:center;">Coupon Payable</th>
                </tr>
              </thead>
              <tbody>';
              $i=1;
              $query= $dbh->prepare("SELECT s.symbol_id, s.symbol, s.name, s.paid_up_shares * 1000 as amount_issue, s.coupon_rates, s.date_of_issue, s.maturity_date,
                  case 
                    when s.coupon_payable=1 then 'Annually'
                    when s.coupon_payable=2 then 'Semi-annually'
                    when s.coupon_payable=3 then 'Quarterly'
                    ELSE ''
                  END coupon_payable
                  FROM symbol s 
                  WHERE s.security_type in ('GB', 'CP', 'CB') and s.status=1 and s.trsstatus in (1, 2) 
              ");
              $query->execute();
              $states = $query->fetchAll(PDO::FETCH_ASSOC);
              foreach ($states as $state) {
                echo'
                <tr style="font-size: 70%;">
                  <td style="text-align:center;">'.$i++.'</td>
                  <td style="text-align:center;">'.$state['symbol'].'</td>
                  <td style="text-align:center;">'.number_format($state['amount_issue'], 2).'</td>';
                  if($state['coupon_rates'] == '' || $state['coupon_rates'] == 0){
                    echo'<td style="text-align:center;">'.$state['coupon_rates'].'</td>';
                  }else{
                    echo'<td style="text-align:center;">'.$state['coupon_rates'].'%</td>';
                  }
                  echo'
                  <td style="text-align:center;">'.$state['date_of_issue'].'</td>
                  <td style="text-align:center;">'.$state['maturity_date'].'</td>
                  <td style="text-align:center;">'.$state['coupon_payable'].'</td>
                </tr>';
              }  
              echo'
              </tbody>
            </table>
          </div>
        </div>
      </section>
      <div class="row no-print">
        <div class="col-xs-12">
          &emsp;&emsp;<a href="loadReport.php?get_export_bondReport=get_export_bondReport&reportType='.$reportType.'&symbolId='.$symbolId.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
      </div>';
    }
    elseif($reportType == 2) {
      $sqlToGetDtls ="SELECT s.symbol_id, s.symbol, s.name, s.maturity_date, s.date_of_issue, s.coupon_rates,
          case when s.coupon_payable=1 then 'Annually'
          when s.coupon_payable=2 then 'Semi-Annually'
          when s.coupon_payable=3 then 'Quaterly'
          END AS payable
          FROM symbol s 
          WHERE s.symbol_id=:symbolId";
      $sql= $dbh->prepare($sqlToGetDtls);
      $sql->bindParam(':symbolId',$symbolId);
      $sql->execute();
      $res = $sql->fetch();
      echo'
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Coupon Payment Details Report</div> 
                  <div class="lead" style="font-size: 40%;  margin-top:-25px;">Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div>
                </center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <div class="lead" style="font-size: 70%; margin-top:-10px;">
              Symbol : <strong>'.$res['symbol'].'</strong>&emsp;&emsp;&emsp;&emsp;&emsp;
              Bond Name : <strong>'.$res['name'].'</strong>
            </div>
            <div class="lead" style="font-size: 70%; margin-top:-10px;">
              Issue Date : <strong>'.$res['date_of_issue'].'</strong>&emsp;&emsp;&emsp;&emsp;&emsp;
              Maturity Date : <strong>'.$res['maturity_date'].'</strong>
            </div>
            <div class="lead" style="font-size: 70%; margin-top:-10px;">
              Coupon Rate : <strong>'.$res['coupon_rates'].' %</strong>&emsp;&emsp;&emsp;&emsp;&emsp;
              Coupon Payable : <strong>'.$res['payable'].'</strong>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table id="tableId" class="table table-striped" style="font-size: 15px;">
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th style="text-align:center;">SlNo</th>
                  <th style="text-align:left;">CD Code</th>
                  <th style="text-align:left;">Name</th>
                  <th style="text-align:center;">CID</th>
                  <th style="text-align:center;">No of Bond(Vol)</th>
                  <th style="text-align:center;">Coupon Payable(Nu.)</th>
                  <th style="text-align:center;">Bank Name</th>
                  <th style="text-align:center;">Account No</th>
                  <th style="text-align:center;">Phone</th>
                </tr>
              </thead>
              <tbody>';
              $i=1;
              $totalVol = 0;
              $totalCouAmt = 0;
              $query= $dbh->prepare("SELECT c.cd_code, c.symbol_id, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, c.user_name, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 100 AS amount, s.symbol, s.coupon_rates, s.date_of_issue, s.maturity_date, a.f_name, a.l_name, a.ID, a.phone, a.email, a.address, ROUND(((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100, 0) AS couponpayment, b.bank_name, a.bank_account, b.bank_short_name 
                FROM cds_holding c 
                LEFT JOIN client_account a ON c.cd_code=a.cd_code 
                LEFT JOIN symbol s ON c.symbol_id=s.symbol_id
                LEFT JOIN banks b ON a.bank_id = b.bank_id
                WHERE s.symbol_id=:sId AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) != 0");
              $query->bindParam(':sId',$symbolId);
              $query->execute();
              $states = $query->fetchAll(PDO::FETCH_ASSOC);
              foreach($states as $state){
                echo'
                <tr style="font-size: 70%;">
                  <td style="text-align:center;">'.$i++.'</td>
                  <td style="text-align:left;">'.$state['cd_code'].'</td>
                  <td style="text-align:left;">'.$state['f_name'].' '.$state['l_name'].'</td>
                  <td style="text-align:center;">'.$state['ID'].'</td>
                  <td style="text-align:center;">'.$state['volume'].'</td>
                  <td style="text-align:center;">'.number_format($state['couponpayment'], 2).'</td>
                  <td style="text-align:center;">'.$state['bank_short_name'].'</td>
                  <td style="text-align:left;">'.$state['bank_account'].'</td>
                  <td style="text-align:left;">'.$state['phone'].'</td>
                </tr>';
                $totalVol = $totalVol + $state['volume'];
                $totalCouAmt = $totalCouAmt + $state['couponpayment'];
              }
              echo'
              <tr>
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td>'.$totalVol.'</td>
                <td>'.$totalCouAmt.'</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      </section>
      <div class="row no-print">
        <div class="col-xs-12">
          &emsp;&emsp;<a href="loadReport.php?get_export_bondReport=get_export_bondReport&reportType='.$reportType.'&symbolId='.$symbolId.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
      </div>';
    }
    elseif ($reportType == 3) {
      echo'
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; 
              <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Final Amount Report</div> 
                  <div class="lead" style="font-size: 40%;  margin-top:-25px;">Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div>
                </center>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 table-responsive">
            <table id="tableId" class="table table-striped">
              <thead style="background-color: #D6EAF8; font-size: 80%;">
                <tr>
                  <th style="text-align:center;">SlNo</th>
                  <th style="text-align:left;">CD Code</th>
                  <th style="text-align:left;">Name</th>
                  <th style="text-align:center;">CID</th>
                  <th style="text-align:center;">No of Bond(Vol)</th>
                  <th style="text-align:center;">Principal Amount(Nu)</th>
                  <th style="text-align:center;">Coupon Amount(Nu)</th>
                  <th style="text-align:center;">Final Amout(Nu)</th>
                  <th style="text-align:center;">Bank Name</th>
                  <th style="text-align:center;">Account No</th>
                  <th style="text-align:center;">Phone</th>
                </tr>
              </thead>
              <tbody>';
              $i=1;
              $totalVol = 0;
              $totalPrinAmt = 0;
              $totalCouAmt = 0;
              $totalFinalAmt = 0;
              $query= $dbh->prepare("SELECT c.cd_code, c.symbol_id, c.volume, c.pledge_volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, c.user_name, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) AS TotalVol, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 AS amount, s.symbol, s.coupon_rates, s.date_of_issue, s.maturity_date, a.f_name, a.l_name, a.ID, a.phone, a.email, a.address, ROUND(((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100, 0) AS couponpayment, b.bank_name, a.bank_account, b.bank_short_name, ROUND((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000, 2) as principal, ROUND(((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100, 2) couponPayment, ROUND((((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100)+((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol)*1000),2) AS finalAmt
                FROM cds_holding c 
                LEFT JOIN client_account a ON c.cd_code=a.cd_code 
                LEFT JOIN symbol s ON c.symbol_id=s.symbol_id
                LEFT JOIN banks b ON a.bank_id = b.bank_id
                WHERE s.symbol_id=:sId22 AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol)!=0");
              $query->bindParam(':sId22',$symbol22);
              $query->execute();
              $rows = $query->fetchAll(PDO::FETCH_ASSOC);
              foreach($rows as $state){
                echo'
                <tr style="font-size: 70%;">
                  <td style="text-align:center;">'.$i.'</td>
                  <td style="text-align:left;">'.$state['cd_code'].'</td>
                  <td style="text-align:left;">'.$state['f_name'].' '.$state['l_name'].'</td>
                  <td style="text-align:center;">'.$state['ID'].'</td>
                  <td style="text-align:center;">'.$state['TotalVol'].'</td>
                  <td style="text-align:center;">'.number_format($state['principal'], 2).'</td>
                  <td style="text-align:center;">'.number_format($state['couponpayment'], 2).'</td>
                  <td style="text-align:center;">'.number_format($state['finalAmt'], 2).'</td>
                  <td style="text-align:center;">'.$state['bank_short_name'].'</td>
                  <td style="text-align:left;">'.$state['bank_account'].'</td>
                  <td style="text-align:left;">'.$state['phone'].'</td>
                </tr>';
                $i++;
                $totalVol = $totalVol + $state['TotalVol'];
                $totalPrinAmt = $totalPrinAmt + $state['principal'];
                $totalCouAmt = $totalCouAmt + $state['couponpayment'];
                $totalFinalAmt = $totalFinalAmt + $state['finalAmt'];
              }
              echo'
              <tr>
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td>'.$totalVol.'</td>
                <td>'.$totalPrinAmt.'</td>
                <td>'.$totalCouAmt.'</td>
                <td>'.$totalFinalAmt.'</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      </section>
      <div class="row no-print">
        <div class="col-xs-12">
          &emsp;&emsp;<a href="loadReport.php?get_export_bondReport=get_export_bondReport&reportType='.$reportType.'&symbolId='.$symbol22.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
        </div>
      </div>';
    }
}
elseif (!empty($_GET['get_export_bondReport']))
{
  $replace   = array("\n","\r\n","\r");
  $search    = array('','','');
  $reportType  = $_GET['reportType'];
  $symbolId    = $_GET['symbolId'];

  if($reportType == 1) {
    $sql= $dbh->prepare("SELECT s.symbol_id, s.symbol, s.name, s.paid_up_shares * 1000 as amount_issue, s.coupon_rates, s.date_of_issue, s.maturity_date,
      case 
        when s.coupon_payable=1 then 'Annually'
        when s.coupon_payable=2 then 'Semi-annually'
        when s.coupon_payable=3 then 'Quarterly'
        ELSE ''
      END coupon_payable
      FROM symbol s 
      WHERE s.security_type in ('GB', 'CP', 'CB') and s.status=1 and s.trsstatus in (1, 2) ");
    $sql->execute();
    $columnHeader = '';  
    $i=1;
    $columnHeader = "SlNo\t Name of Bond\t Amount Raised(Nu.)\t Coupon Rate(%)\t Issue Date\t Maturity Date\t Coupon Payable\t"; 
    $setData = '';  
    while ($rec=$sql->fetch()) { 
      $rowData = '';  
      $value = $i++ . "\t "
      .str_replace($search,$replace,$rec['symbol'])."\t"
      .str_replace($search,$replace,trim(number_format($rec['amount_issue'], 2))."\t"
      .$rec['coupon_rates']."\t"
      .$rec['date_of_issue']."\t"
      .$rec['maturity_date'])."\t"
      .str_replace($search,$replace,$rec['coupon_payable'])."\t"; 
      $rowData .= $value;
      $setData .= trim($rowData)."\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=CouponPayment.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader)."\n".$setData."\n"; 
  }
  elseif ($reportType==2) {
    $sqlToGetDtls="SELECT s.symbol_id, s.symbol, s.name, s.maturity_date, s.date_of_issue, s.coupon_rates,
          case when s.coupon_payable=1 then 'Annually'
            when s.coupon_payable=2 then 'Semi-Annually'
            when s.coupon_payable=3 then 'Quaterly'
          END AS payable
          FROM symbol s 
          WHERE s.symbol_id=:symbolId";
    $sql= $dbh->prepare($sqlToGetDtls);
    $sql->bindParam(':symbolId',$symbolId);
    $sql->execute();
    $res=$sql->fetch();
    
    $sql = $dbh->prepare("SELECT c.cd_code, c.symbol_id, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, c.user_name, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 100 AS amount, s.symbol, s.coupon_rates, s.date_of_issue, s.maturity_date, CONCAT(IFNULL(a.f_name, ''), '', IFNULL(a.l_name, '')) AS fullname, a.f_name, a.l_name, a.ID, a.phone, a.email, a.address, ROUND(((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100, 0) AS couponpayment, b.bank_name, a.bank_account, b.bank_short_name 
        FROM cds_holding c 
        LEFT JOIN client_account a ON c.cd_code=a.cd_code 
        LEFT JOIN symbol s ON c.symbol_id=s.symbol_id
        LEFT JOIN banks b ON a.bank_id = b.bank_id
        WHERE s.symbol_id=:sId AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) != 0");
    $sql->bindParam(':sId', $symbolId);
    $sql->execute();
    $columnHeader = '';
    //$columnHeader0 = "Symbol=".$res['symbol']."\t";
    $i=1;
    $columnHeader = "SlNo\t CD Code\t Name\t CID No\t No of Bond(Vol)\t Coupon Payment(Nu)\t Bank Name\t Account No\t Phone\t"; 
    $setData = '';  
    while ($rec=$sql->fetch()) { 
      $rowData = '';  
      $value = $i++."\t"
        .str_replace($search,$replace,trim($rec['cd_code']))."\t"
        .str_replace($search,$replace,$rec['fullname'])."\t"
        .str_replace($search,$replace,trim($rec['ID']))."\t"
        .str_replace($search,$replace,$rec['volume'])."\t"
        .str_replace($search,$replace,number_format($rec['couponpayment'], 2))."\t"
        .str_replace($search,$replace,$rec['bank_short_name'])."\t"
        .str_replace($search,$replace,trim($rec['bank_account']). " -")."\t"
        .str_replace($search,$replace,trim($rec['phone']))."\t"
        ;
      $rowData .= $value;  
      $setData .= trim($rowData) . "\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=CouponPaymentDetails.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader) . "\n" . $setData . "\n"; 
  }
  elseif ($reportType == 3) {
    $sql= $dbh->prepare("SELECT c.cd_code, c.symbol_id, c.volume, c.pledge_volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, c.user_name, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) AS TotalVol, (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 AS amount, s.symbol, s.coupon_rates, s.date_of_issue, s.maturity_date, CONCAT(IFNULL(a.f_name, ''), '', IFNULL(a.l_name, '')) AS fullname, a.f_name, a.l_name, a.ID, a.phone, a.email, a.address, ROUND(((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100, 0) AS couponpayment, b.bank_name, a.bank_account, b.bank_short_name, ROUND((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000, 2) as principal, ROUND(((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100, 2) couponPayment, ROUND((((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) * 1000 * s.coupon_rates)/100)+((c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol)*1000),2) AS finalAmt
        FROM cds_holding c 
        LEFT JOIN client_account a ON c.cd_code=a.cd_code 
        LEFT JOIN symbol s ON c.symbol_id=s.symbol_id
        LEFT JOIN banks b ON a.bank_id = b.bank_id
        WHERE s.symbol_id=:sId22 AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol)!=0");
    $sql->bindParam(':sId22',$symbolId);
    $sql->execute();
    $columnHeader = '';  
    $i=1;
    $columnHeader = "SlNo\t CD Code\t Name\t CID No\t No of Bond(Vol)\t Principal Amount(Nu)\t Coupon Amount(Nu)\t Final Amount(Nu)\t Bank Name\t Account No\t Phone\t"; 
    $setData = '';  
    while ($rec=$sql->fetch()) 
    { 
      $rowData = '';  
      $value = $i++ . "\t "
        .str_replace($search,$replace,trim($rec['cd_code']))."\t"
        .str_replace($search,$replace,$rec['fullname'])."\t"
        .str_replace($search,$replace,trim($rec['ID']))."\t"
        .str_replace($search,$replace,$rec['TotalVol'])."\t"
        .str_replace($search,$replace,number_format($rec['principal'], 2))."\t"
        .str_replace($search,$replace,number_format($rec['couponpayment'], 2))."\t"
        .str_replace($search,$replace,number_format($rec['finalAmt'], 2))."\t"
        .str_replace($search,$replace,$rec['bank_short_name'])."\t"
        .str_replace($search,$replace,trim($rec['bank_account']). " -")."\t"
        .str_replace($search,$replace,trim($rec['phone']))."\t"
      ;
      $rowData .= $value;
      $setData .= trim($rowData)."\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=FinalAmountPayableReport.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader)."\n".$setData."\n"; 
  }
}
elseif(isset($_POST["getMaturityDate"]))
{
  $symId = $_POST['symbolId'];

  //$sql = $dbh->prepare("SELECT s.maturity_date FROM symbol s WHERE s.symbol_id=:sId");
  $sql = $dbh->prepare("SELECT DATE_FORMAT(s.maturity_date, '%m/%d/%Y') AS matDate FROM symbol s WHERE s.symbol_id=:sId");
  $sql->bindParam(':sId', $symId);
  $sql->execute();
  $res = $sql->fetch();

  echo $res['matDate'];
}
elseif(isset($_POST["TerminalUserReport"]))
{
  $participantCode = $_POST['broker'];
  $sysTime = date("Y-m-d");
  echo'
  <section class="invoice" style="background:rgb(248, 249, 249);">
    <div class="row">
      <div class="col-lg-12">
        <div class="page-header">
          Terminal User Report
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12 table-responsive">
        <table id="TerminalUserTable" class="table table-striped" style="font-size: 15px;">
          <thead style="background-color: #D6EAF8; font-size: 80%;">
            <tr>
              <th style="text-align:center;">SlNo</th>
              <th style="text-align:left;">CD Code</th>
              <th style="text-align:left;">Name</th>
              <th style="text-align:center;">Username</th>
              <th style="text-align:center;">CID</th>
              <th style="text-align:center;">Email</th>
              <th style="text-align:center;">Phone</th>
            </tr>
          </thead>
          <tbody>';
          $i = 1;
          $query = "SELECT a.cd_code, u.name, u.username, u.cid, u.email, u.phone
              FROM users u 
              LEFT JOIN client_account a on u.cid = a.ID 
              WHERE u.role_id = 4 AND u.status = 1 ";
          
          if($participantCode != 'ALL') {
            $query .= "AND u.participant_code = :partCode AND u.participant_code = SUBSTR(a.user_name, 1, 7) ";
          }

          $query .= "GROUP BY u.username";
          $sql = $dbh->prepare($query);
          if($participantCode != 'ALL') {
            $sql->bindParam(':partCode', $participantCode);
          }
          $sql->execute();
          $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
          foreach($rows as $res){
            echo'
            <tr style="font-size: 70%;">
              <td style="text-align:center;">'.$i++.'</td>
              <td style="text-align:left;">'.$res['cd_code'].'</td>
              <td style="text-align:left;">'.$res['name'].'</td>
              <td style="text-align:center;">'.$res['username'].'</td>
              <td style="text-align:center;">'.$res['cid'].'</td>
              <td style="text-align:left;">'.$res['email'].'</td>
              <td style="text-align:left;">'.$res['phone'].'</td>
            </tr>';
          }
          echo'
          </tbody>
        </table>
      </div>
    </div>
    </section>
    <div class="row no-print">
      <div class="col-xs-12">
        &emsp;&emsp;<a href="loadReport.php?export_terminal_user=export_terminal_user&participantCode='.$participantCode.'" class="btn btn-success"><i class="fa fa-save"></i> Export</a>
      </div>
    </div><br>
    <script>
    $(function () {
      $("#TerminalUserTable").DataTable();
    });
  </script>';
}
elseif(!empty($_GET['export_terminal_user'])){
  $participantCode = $_GET['participantCode'];
  $replace   = array("\n","\r\n","\r");
  $search    = array('','','');

  $query = "SELECT a.cd_code, u.name, u.username, u.cid, u.email, u.phone
      FROM users u 
      LEFT JOIN client_account a on u.cid = a.ID 
      WHERE u.role_id = 4 AND u.status = 1 ";
  
  if($participantCode != 'ALL') {
    $query .= " AND u.participant_code = :partCode AND u.participant_code = SUBSTR(a.user_name, 1, 7) ";
  }

  $query .= " GROUP BY u.username";
  $sql = $dbh->prepare($query);
  if($participantCode != 'ALL') {
    $sql->bindParam(':partCode', $participantCode);
  }
  $sql->execute();

  $columnHeader = '';  
  $i=1;
  $columnHeader = "SlNo\t CD Code\t Name\t Username\t CID\t Email\t Phone\t"; 
  $setData = '';  
  while ($rec=$sql->fetch()) { 
    $rowData = '';  
    $value = $i++ . "\t "
      .str_replace($search,$replace,trim($rec['cd_code']))."\t"
      .str_replace($search,$replace,$rec['name'])."\t"
      .str_replace($search,$replace,$rec['username'])."\t"
      .str_replace($search,$replace,$rec['cid'])."\t"
      .str_replace($search,$replace,trim($rec['email']))."\t"
      .str_replace($search,$replace,trim($rec['phone']))."\t"
    ;
    $rowData .= $value;
    $setData .= trim($rowData)."\n";     
  }  
  header("Content-type: application/octet-stream");  
  header("Content-Disposition: attachment; filename=Terminal_User.xls");  
  header("Pragma: no-cache");  
  header("Expires: 0");  
  echo ucwords($columnHeader)."\n".$setData."\n"; 
}
elseif (isset($_POST["consolidated_individual_report"])) {
  $cid = $_POST['cid'];
  $sysTime = date("Y-m-d");

  $wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, c.title, c.tpn, c.address
        FROM client_account c
        WHERE (c.ID = :cid OR c.cd_code = :cid)
        ORDER BY c.client_id DESC LIMIT 1
    ");
  $wc->bindParam(':cid', $cid);
  $wc->execute();
  $state = $wc->fetch();
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
          $get = $dbh->prepare("SELECT s.symbol, SUM(c.volume) AS volume, SUM(c.pledge_volume) AS pledge_volume, SUM(c.block_volume) AS block_volume, SUM(c.pending_in_vol) AS p_i_v, SUM(c.pending_out_vol) AS p_o_v, sum(c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) AS total_volume
            FROM cds_holding c
            LEFT JOIN client_account a ON c.cd_code = a.cd_code
            LEFT JOIN symbol s ON c.symbol_id = s.symbol_id
            WHERE a.ID = ?
            AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) > 0
            AND s.status = 1 
            GROUP BY c.symbol_id 
            ORDER BY s.symbol ASC 
          ");
          $get->bindParam(1, $cid);
          $get->execute(); 
          $gets = $get->fetchAll(PDO::FETCH_ASSOC);
          $i = 1;
          foreach ($gets as $get) {
            $vol = isset($get['volume']) ? $get['volume'] : '-';
            $bv = isset($get['block_volume']) ? $get['block_volume'] : '-';
            $pv = isset($get['pledge_volume']) ? $get['pledge_volume'] : '-';
            $piv = isset($get['p_i_v']) ? $get['p_i_v'] : '-';
            $pov = isset($get['p_o_v']) ? $get['p_o_v'] : '-';
            echo'
            <tr style="font-size: 70%;">
               <td>'.$i.'</td>
               <td>'.$get['symbol'].'</td>
               <td style="text-align:right;">'.$vol.'</td>
               <td style="text-align:right;">'.$bv.'</td>
               <td style="text-align:right;">'.$pv.'</td>
               <td style="text-align:right;">'.$piv.'</td>
               <td style="text-align:right;">'.$pov.'</td>
               <td style="text-align:right;">'.number_format($get['total_volume'], 0, ".", ",").'</td>
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
        &emsp;&emsp;<a href="loadReportPrint.php?cid='.$cid.'&consolidate_share_report=consolidate_share_report" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
    </div>
  </section>';
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
                      $total_share = abs($total_share - $v);
                      echo'
                      <td>SELL</td>
                      <td>'.$det['cd'].'</td>
                      <td></td>
                      <td>'.substr($det['volume'], 1).'</td>
                      <td></td>
                      <td>'.abs($total_share).'</td>';
                    }
                    elseif ($det['type'] === 'TR' || $det['type'] === 'ST') {
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
                      $total_share = abs($det['volume'] - $det['actl_vol']);
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
elseif (isset($_POST["get_cid_update_log"])) {
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00'; 

    echo'
    <div class="box">
      <div class="box-body">
        <div class="row">
          <div class="col-lg-12 table-responsive">
            <table id="cid_list_id" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>CD Code</th>
                  <th>Name</th>
                  <th>Old CID</th>
                  <th>New CID</th>
                  <th>Remarks</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>';
              $wc = $dbh->prepare("SELECT cd_code, name, old_cid, new_cid, remark, created_date FROM update_cid_log c WHERE c.created_date BETWEEN :fdate AND :tdate");
              $wc->bindParam(':fdate', $fromDate);
              $wc->bindParam(':tdate', $toDate);
              $wc->execute();
              $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              foreach ($rows as $state) {
                echo'
                <tr>
                  <td>'.$i.'</td>
                  <td>'.$state['cd_code'].'</td>
                  <td>'.$state['name'].'</td>
                  <td>'.$state['old_cid'].'</td>
                  <td>'.$state['new_cid'].'</td>
                  <td>'.$state['remark'].'</td>
                  <td>'.$state['created_date'].'</td>
                </tr>';
                $i++;
              }
              echo'
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
    <script type="text/javascript">
      $( function () {
        $("#cid_list_id").DataTable();
      });
    </script>';
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
elseif (isset($_POST['generate_bond_subscription_list'])) {
    $symbol_id = $_POST['symbol_id'];
    $total_order_size = 0;
    $total_amount = 0;
    echo'
    <div class="row">
      <div class="col-lg-12 table-responsive">
        <span style="color: red;">100 records are displayed. Please download all of them in Excel.</span>
        <table id="bond__table__id" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>CID</th>
              <th>CD Code</th>
              <th>Symbol</th>
              <th>Order Size</th>
              <th>Amount(Nu.)</th>
              <th>Allotment</th>
              <th>Details</th>
              <th>User Name</th>
            </tr>
          </thead>
          <tbody>';
          $wc = $dbh->prepare("SELECT b.cd_code, b.order_size, b.bid_price, b.face_value, b.total_amount, b.allocated_size, b.user_name, b.cid_no, b.order_date, s.symbol 
                FROM bond b 
                LEFT JOIN symbol s ON b.symbol_id = s.symbol_id 
                WHERE b.symbol_id = ?
                GROUP BY b.cd_code
                Limit 100
          ");
          $wc->execute([$symbol_id]);
          $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
          $i = 1;

          $stmtBroker = $dbh->prepare("
              SELECT CONCAT_WS(' ', a.f_name, a.l_name) AS fl_name, a.phone, a.email, a.bank_account, a.bank_id, a.ID 
              FROM client_account a 
              WHERE a.cd_code = ?
          ");

          $stmtNonBroker = $dbh->prepare("
              SELECT b.phone, b.email, b.details 
              FROM bond_ipo_temp_dtls b 
              WHERE b.name = ? AND b.cd_code = ? AND b.bfs_code = '00'
              ORDER BY b.id ASC 
              LIMIT 1
          ");

          foreach ($rows as $state) {
            $details = '';
            $cid_no = $state['cid_no'];
            $cd_code = $state['cd_code'];
            $bond_username = $state['user_name'];

            $total_order_size += $state['order_size'];
            $total_amount += $state['total_amount'];

            if (strncasecmp($bond_username, 'MEM', 3) === 0) {
                $stmtBroker->execute([$cd_code]);
                if ($res = $stmtBroker->fetch(PDO::FETCH_ASSOC)) {
                    $details = $res['fl_name'] . '|' . $res['bank_id'] . '|' . $res['bank_account'];
                }
            } else {
                $stmtNonBroker->execute([$cid_no, $cd_code]);
                if ($res = $stmtNonBroker->fetch(PDO::FETCH_ASSOC)) {
                    $details = $res['details'];
                }
            }

            $unit_formatted = number_format($state['order_size']);
            $formatted_amount = number_format($state['total_amount'], 2);

            echo <<<HTML
              <tr>
                <td>{$i}</td>
                <td>{$cid_no}</td>
                <td>{$cd_code}</td>
                <td>{$state['symbol']}</td>
                <td>{$unit_formatted}</td>
                <td>{$formatted_amount}</td>
                <td>{$state['allocated_size']}</td>
                <td>{$details}</td>
                <td>{$bond_username}</td>
              </tr>
              HTML;

            $i++;
          }
          echo'
          </tbody>
        </table>
      </div>
      
      <div class="col-lg-12">
        <div class="col-lg-6 col-md-6 text-left">
            Total Subscribed => <strong>'.number_format($total_order_size).'</strong><br>
            Total Amount => <strong>'.number_format($total_amount, 2).'</strong>
        </div>

        <div class="col-lg-6 col-md-6 text-right">
          <a href="loadReport.php?export_bond_subscription=export_bond_subscription&symbol_id='.$symbol_id.'" target="_blank" class="btn btn-success"><i class="fa fa-angle-double-right"></i> Download Excel</a>
        </div>
      </div>

    </div>
    <script type="text/javascript">
      $( function () {
        $("#bond__table__id").DataTable();
      });
    </script>';
    exit;
}
elseif (!empty($_GET['export_bond_subscription'])) {
    ini_set('memory_limit', '512M');
    set_time_limit(0);
    
    $symbol_id = $_GET['symbol_id'];

    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');

    $getSymbol = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id = ?");
    $getSymbol->execute([$symbol_id]);
    $symbol_name = $getSymbol->fetchColumn();

    $stmt = $dbh->prepare("SELECT b.cd_code, b.order_size, b.bid_price, b.face_value, b.total_amount, b.allocated_size, b.user_name, b.cid_no, b.order_date, s.symbol  
                FROM bond b 
                LEFT JOIN symbol s ON b.symbol_id = s.symbol_id 
                WHERE b.symbol_id = ?
                GROUP BY b.cd_code
                -- LIMIT 100
    ");
    $stmt->execute([$symbol_id]);

    $stmtBroker = $dbh->prepare("
        SELECT CONCAT_WS(' ', a.f_name, a.l_name) AS fl_name, a.phone, a.email, a.bank_account, a.bank_id, a.ID 
        FROM client_account a 
        WHERE a.cd_code = ?
    ");

    $stmtNonBroker = $dbh->prepare("
        SELECT b.phone, b.email, b.details 
        FROM bond_ipo_temp_dtls b 
        WHERE b.name = ? AND b.cd_code = ? AND b.bfs_code = '00'
        ORDER BY b.id ASC 
        LIMIT 1
    ");

    $columnHeader = "SlNo\t CID\t CD Code\t Symbol\t Order Size\t Face Value\t Amount(Nu.)\t Allotment\t Details\t Phone\t Email\t User Name\t Date\t"; 
    $setData = '';  
    $i = 1;
    while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $rowData = ''; 

      $details = '';
      $phone_no = '';
      $email = '';
      $cid_no = $rec['cid_no'];
      $cd_code = $rec['cd_code'];
      $bond_username = $rec['user_name'];

      if (strncasecmp($bond_username, 'MEM', 3) === 0) {
          $stmtBroker->execute([$cd_code]);
          if ($res = $stmtBroker->fetch(PDO::FETCH_ASSOC)) {
              $details = $res['fl_name'] . '|' . $res['bank_id'] . '|' . $res['bank_account'];
              $phone_no = $res['phone'];
              $email = $res['email'];
          }
      } else {
          $stmtNonBroker->execute([$cid_no, $cd_code]);
          if ($res = $stmtNonBroker->fetch(PDO::FETCH_ASSOC)) {
              $details = $res['details'];
              $phone_no = $res['phone'];
              $email = $res['email'];
          }
      }

      $value = $i++."\t"
          .str_replace($search, $replace, trim($cid_no))."\t"
          .str_replace($search, $replace, trim($rec['cd_code']))."\t"
          .str_replace($search, $replace, trim($rec['symbol']))."\t"
          .str_replace($search, $replace, trim(number_format($rec['order_size'])))."\t"
          .str_replace($search, $replace, trim($rec['face_value']))."\t"
          .str_replace($search, $replace, trim(number_format($rec['total_amount'], 2)))."\t"
          .str_replace($search, $replace, trim($rec['allocated_size']))."\t"
          .str_replace($search, $replace, trim($details))."\t"
          .str_replace($search, $replace, trim($phone_no))."\t"
          .str_replace($search, $replace, trim($email))."\t"
          .str_replace($search, $replace, trim($bond_username))."\t"
          .str_replace($search, $replace, trim($rec['order_date']))."\t"
      ;
      $rowData .= $value;
      $setData .= trim($rowData)."\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=Bond_subscription_list_".$symbol_name.".xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader)."\n".$setData."\n";
}
elseif (isset($_POST['get_pledge_release_report'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $pledgee_bank = $_POST['pledgee_bank'];

    $total_release_vol = 0;

    echo'
    <div class="row">
      <div class="col-lg-12 table-responsive">
        <table id="bond__table__id" class="table table-bordered table-striped" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th>CID</th>
              <th>CD Code</th>
              <th>Name</th>
              <th>Pledge Name</th>
              <th>Pledge Contract</th>
              <th>Symbol</th>
              <th>Release Vol</th>
              <th>Pledgee Bank</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>';
          $stmt = $dbh->prepare("SELECT c.cd_code, a.ID,  CONCAT_WS(' ', a.f_name, a.l_name) AS fl_name, a.phone, c.pledge_name, c.pledge_contract, c.symbol_id, s.symbol, c.pledge_volume, c.pledgee, c.pledge_date
                FROM cds_pledge c 
                LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
                LEFT JOIN client_account a ON c.cd_code = a.cd_code 
                WHERE DATE(c.pledge_date) BETWEEN ? AND ?
                AND c.pledge_volume < 0
                AND IF(? = '0', 1 = 1, TRIM(c.pledgee) = TRIM(?))
          ");
          $stmt->bindValue(1, $from_date);
          $stmt->bindValue(2, $to_date);
          $stmt->bindValue(3, $pledgee_bank);
          $stmt->bindValue(4, $pledgee_bank);
          $stmt->execute();
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $i = 1;
          foreach ($rows as $state) {
            echo'
            <tr>
              <td>'.$i.'</td>
              <td>'.$state['ID'].'</td>
              <td>'.$state['cd_code'].'</td>
              <td>'.$state['fl_name'].'</td>
              <td>'.$state['pledge_name'].'</td>
              <td>'.$state['pledge_contract'].'</td>
              <td>'.$state['symbol'].'</td>
              <td>'.$state['pledge_volume'].'</td>
              <td>'.$state['pledgee'].'</td>
              <td>'.$state['pledge_date'].'</td>
            </tr>';
            $total_release_vol += $state['pledge_volume'];
            $i++;
          }
          echo'
          </tbody>
        </table>
      </div>
      
      <div class="col-lg-12">
        <div class="col-lg-6 col-md-6 text-left">
            Total Release Vol => <strong>'.number_format($total_release_vol).'</strong>
        </div>

        <div class="col-lg-6 col-md-6 text-right">
          <a href="loadReport.php?export__pledge__release=export__pledge__release&from_date='.$from_date.'&to_date='.$to_date.'&pledgee_bank='.$pledgee_bank.'" target="_blank" class="btn btn-success"><i class="fa fa-angle-double-right"></i> Download Excel</a>
        </div>
      </div>

    </div>
    <script type="text/javascript">
      $( function () {
        $("#bond__table__id").DataTable();
      });
    </script>';
}
elseif (!empty($_GET['export__pledge__release'])) {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    $pledgee_bank = $_GET['pledgee_bank'];

    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');

    $stmt = $dbh->prepare("SELECT c.cd_code, a.ID,  CONCAT_WS(' ', a.f_name, a.l_name) AS fl_name, a.phone, c.pledge_name, c.pledge_contract, c.symbol_id, s.symbol, c.pledge_volume, c.pledgee, c.pledge_date
          FROM cds_pledge c 
          LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
          LEFT JOIN client_account a ON c.cd_code = a.cd_code 
          WHERE DATE(c.pledge_date) BETWEEN ? AND ?
          AND c.pledge_volume < 0
          AND IF(? = '0', 1 = 1, TRIM(c.pledgee) = TRIM(?))
    ");
    $stmt->bindParam(1, $from_date);
    $stmt->bindParam(2, $to_date);
    $stmt->bindParam(3, $pledgee_bank);
    $stmt->bindParam(4, $pledgee_bank);
    $stmt->execute();

    $columnHeader = "SlNo\t CID\t CD CODE\t Name\t Pledge Name\t Pledge Contract\t Symbol\t Release Vol\t Pledgee Bank\t Date\t"; 
    $setData = '';  
    $i = 1;
    while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $rowData = ''; 
      $value = 
          $i++."\t"
          .str_replace($search, $replace, trim($rec['ID']))."\t"
          .str_replace($search, $replace, trim($rec['cd_code']))."\t"
          .str_replace($search, $replace, trim($rec['fl_name']))."\t"
          .str_replace($search, $replace, trim($rec['pledge_name']))."\t"
          .str_replace($search, $replace, trim($rec['pledge_contract']))."\t"
          .str_replace($search, $replace, trim($rec['symbol']))."\t"
          .str_replace($search, $replace, trim($rec['pledge_volume']))."\t"
          .str_replace($search, $replace, trim($rec['pledgee']))."\t"
          .str_replace($search, $replace, trim($rec['pledge_date']))."\t"
      ;
      $rowData .= $value;
      $setData .= trim($rowData)."\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=Pledge_Release_" . $from_date . "_To_" . $to_date . ".xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader)."\n".$setData."\n"; 
}
elseif (isset($_POST["get_share_transfer_report"])) {
    $fromDate = $_POST['fromDate'].' 00:00:00'; 
    $toDate = $_POST['toDate'].' 23:59:00';
    $tr_type = $_POST['tr_type']; 

    echo'
    <div class="box">
      <div class="box-body">
        <div class="row">
          <div class="col-lg-12 table-responsive">
            <table id="share_table_id" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>From Account</th>
                  <th>To Account</th>
                  <th>Symbol</th>
                  <th>Volume</th>
                  <th>Remarks</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>';
              $sql = "SELECT c.from_acc, c.to_acc, c.symbol_id, c.trs_vol, c.remarks, c.trs_date, s.symbol
                    FROM cds_transfer c 
                    LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
                    WHERE c.trs_date BETWEEN ? AND ? 
              ";

              if (in_array($tr_type, ['ST', 'TR'])) {
                  $sql .= " AND c.type = ?";
              }
              
              $sql .= " ORDER BY c.trs_date ASC";

              $wc = $dbh->prepare($sql);
              $wc->bindParam(1, $fromDate);
              $wc->bindParam(2, $toDate);

              if (in_array($tr_type, ['ST', 'TR'])) {
                  $wc->bindParam(3, $tr_type);
              }

              $wc->execute();
              $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
              $i = 1;
              foreach ($rows as $state) {
                echo'
                <tr>
                  <td>'.$i.'</td>
                  <td>'.$state['from_acc'].'</td>
                  <td>'.$state['to_acc'].'</td>
                  <td>'.$state['symbol'].'</td>
                  <td>'.$state['trs_vol'].'</td>
                  <td>'.$state['remarks'].'</td>
                  <td>'.$state['trs_date'].'</td>
                </tr>';
                $i++;
              }
              echo'
              </tbody>
            </table>
          </div>

          <div class="col-lg-12 col-md-12 text-right">
            <a href="loadReport.php?export__share__transfer__report=export__share__transfer__report&fromDate='.$fromDate.'&toDate='.$toDate.'&tr_type='.$tr_type.'" target="_blank" class="btn btn-success"><i class="fa fa-angle-double-right"></i> Download Excel</a>
          </div>

        </div>

      </div>
    </div>
    <script type="text/javascript">
      $( function () {
        $("#share_table_id").DataTable();
      });
    </script>';
}
elseif (!empty($_GET['export__share__transfer__report'])) {
    $fromDate = $_GET['fromDate'];
    $toDate = $_GET['toDate'];
    $tr_type = $_GET['tr_type'];

    $replace   = array("\n","\r\n","\r");
    $search    = array('','','');


    $sql = "SELECT c.from_acc, c.to_acc, c.symbol_id, c.trs_vol, c.remarks, c.trs_date, s.symbol
          FROM cds_transfer c 
          LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
          WHERE c.trs_date BETWEEN ? AND ? 
    ";

    if (in_array($tr_type, ['ST', 'TR'])) {
        $sql .= " AND c.type = ?";
    }
    
    $sql .= " ORDER BY c.trs_date ASC";

    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $fromDate);
    $stmt->bindParam(2, $toDate);

    if (in_array($tr_type, ['ST', 'TR'])) {
        $stmt->bindParam(3, $tr_type);
    }
    
    $stmt->execute();

    $columnHeader = "SlNo\t From Account\t To Account\t Symbol\t Volume\t Remarks\t Date\t"; 
    $setData = '';  
    $i = 1;
    while ($rec = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $rowData = ''; 
      $value = 
          $i++."\t"
          .str_replace($search, $replace, trim($rec['from_acc']))."\t"
          .str_replace($search, $replace, trim($rec['to_acc']))."\t"
          .str_replace($search, $replace, trim($rec['symbol']))."\t"
          .str_replace($search, $replace, trim($rec['trs_vol']))."\t"
          .str_replace($search, $replace, trim($rec['remarks']))."\t"
          .str_replace($search, $replace, trim($rec['trs_date']))."\t"
      ;
      $rowData .= $value;
      $setData .= trim($rowData)."\n";     
    }  
    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=share_transfer_report.xls");  
    header("Pragma: no-cache");  
    header("Expires: 0");  
    echo ucwords($columnHeader)."\n".$setData."\n"; 
}
?>
