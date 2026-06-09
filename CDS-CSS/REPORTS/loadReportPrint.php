<?php
date_default_timezone_set("Asia/Thimphu");
include ('../../CONNECTIONS/db.php');
session_start();
if(!empty($_GET["accountActivity"])) 
{
    $cd=$_GET['cd'];
    $toDate = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00';
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name,a.address from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
    echo'<html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Account Activity Report</title>
    </head>
        <body onload="window.print();">
        <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
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
            $wc= $dbh->prepare("SELECT a.* from (SELECT distinct c.cd_code as cdcode,c.cd_code,c.symbol_id,s1.symbol,c.type,c.remarks,c.volume,c.entry_date from cds_dep_wit c, symbol s1 where (c.symbol_id=s1.symbol_id and 
              :fromDate <=c.entry_date  and c.entry_date <=:toDate and c.cd_code=:cdCode and c.`type` = 'B') OR (c.symbol_id=s1.symbol_id and :fromDate
               <=c.entry_date  and c.entry_date <=:toDate and c.cd_code=:cdCode and c.`type` = 'S') group by symbol_id   
               union all 
            SELECT ct.from_acc,ct.to_acc,ct.symbol_id,s2.symbol,ct.remarks,ct.remarks,ct.trs_vol,ct.trs_date from cds_transfer ct, symbol s2 where ct.symbol_id=s2.symbol_id and 
            :fromDate <=ct.trs_date  and ct.trs_date <=:toDate and (ct.from_acc=:cdCode OR ct.to_acc=:cdCode) group by symbol_id 
            union all 
            SELECT ca.cd_code,ca.cd_code,s.symbol_id,sm.symbol,s.announcement_type,can.rate,s.volume,s.record_date from spot_date_holding s, client_account ca, symbol sm,
            corporate_announcement can where can.announcement_type=s.announcement_type and s.symbol_id=sm.symbol_id and s.client_id=ca.client_id and :fromDate <=s.record_date  
            and s.record_date <=:toDate and ca.cd_code=:cdCode and s.ribon_volume != 0 and s.status=1  and (s.announcement_type=2 or s.announcement_type=4)
          union all
                  SELECT ri.cd_code,ri.cd_code,ri.symbol_id,s.symbol,an.announcement_type,an.rate,ri.order_size,an.record_date from rights_issue ri, corporate_announcement an, symbol s where ri.symbol_id=s.symbol_id and ri.symbol_id=an.symbol_id and an.announcement_type=1 and ri.cd_code=:cdCode and ri.`type`='S' and an.`status`=0 and :fromDate <= an.record_date and an.record_date <= :toDate) a  group by a.symbol_id  order by symbol_id");
            //$wc= $dbh->prepare("SELECT distinct symbol_id from symbol order by symbol_id");
            $wc->bindParam(':cdCode',$cd);
            $wc->bindParam(':fromDate',$fromDate);
            $wc->bindParam(':toDate',$toDate);
            $wc->execute();
            $i=1;
            foreach($wc as $state)
            {
              
              echo '<div class="row">
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
                      $i=1;
                  $wc= $dbh->prepare("SELECT c.cd_code as cd,c.cd_code,c.symbol_id,s1.symbol,c.type,c.remarks,c.volume,c.entry_date from cds_dep_wit c, symbol s1 where (c.symbol_id=s1.symbol_id and 
                  :fromDate <=c.entry_date  and c.entry_date <=:toDate and c.cd_code=:cdCode and c.`type` = 'B' and c.symbol_id=:sid) OR (c.symbol_id=s1.symbol_id and :fromDate
                  <=c.entry_date  and c.entry_date <=:toDate and c.cd_code=:cdCode and c.`type` = 'S' and c.symbol_id=:sid)   
                  union all 
                  SELECT ct.from_acc,ct.to_acc,ct.symbol_id,s2.symbol,ct.type,ct.remarks,ct.trs_vol,ct.trs_date from cds_transfer ct, symbol s2 where ct.symbol_id=s2.symbol_id and 
                  :fromDate <=ct.trs_date  and ct.trs_date <=:toDate and ct.symbol_id=:sid and (ct.from_acc=:cdCode OR ct.to_acc=:cdCode) union all 
                  SELECT ca.cd_code,ca.cd_code,s.symbol_id,sm.symbol,s.announcement_type,can.rate,s.ribon_volume,s.record_date from spot_date_holding s, client_account ca, symbol sm,
                  corporate_announcement can where can.corp_announcement_id=s.corp_announcement_id and s.symbol_id=sm.symbol_id and s.client_id=ca.client_id and :fromDate <=s.record_date  
                  and s.record_date <=:toDate and ca.cd_code=:cdCode and s.symbol_id=:sid and s.ribon_volume != 0 and (s.announcement_type=2 or s.announcement_type=4) and s.status=1
                  group by s.record_date
                  union all
                  SELECT ri.cd_code,ri.renounce_cd_code,ri.symbol_id,s.symbol,an.announcement_type,an.rate,ri.order_size,ri.order_date from rights_issue ri, corporate_announcement an, symbol s where ri.symbol_id=s.symbol_id and ri.symbol_id=an.symbol_id and an.announcement_type=1 and s.symbol_id=:sid and ((ri.cd_code=:cdCode and ri.`type`='S') or (ri.renounce_cd_code=:cdCode and ri.`type`='R')) and an.`status`=0 and :fromDate <= ri.order_date and ri.order_date <= :toDate group by ri.order_date order by entry_date ASC");
                  $wc->bindParam(':cdCode',$cd);
                  $wc->bindParam(':fromDate',$fromDate);
                  $wc->bindParam(':toDate',$toDate);
                  $wc->bindParam(':sid',$state['symbol_id']);
                  $wc->execute();  

                  $buyTotal = $transferinTotal=$rightsSubscribeTotal=$rightsRenounceTotal=$bonusTotal=$bonusTotal=$transferoutTotal=$sellTotal=$buybackTotal =0;                

                  foreach($wc as $det)
                  {
                    
                      echo'    <tr style="font-size: 70%;">
                               <td>'.$i.'</td>
                               <td>'.$det['entry_date'].'</td>';
                               if($det['cd'] == $det['cd_code'] && $det['type'] == 'B')
                               {
                                $v = $det['volume'];
                                echo'  <td>BUY</td>';
                                echo'  <td>'.$det['volume'].'</td>';
                                echo'  <td>-</td>';
                                echo'  <td>'.$v.'</td>';
                                $buyTotal += $v; 
                               }
                               elseif($det['cd'] == $det['cd_code'] && $det['type'] == 'S')
                               {
                                $v = $det['volume']*-1;
                                echo'  <td>SELL</td>';
                                echo'  <td>-</td>';
                                echo'  <td>'.substr($det['volume'],1).'</td>';
                                echo'  <td>'.$v.'</td>';
                                $sellTotal += $v;
                               }
                               elseif($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd']) && $det['type'] =='TR')
                               {
                                $v = $det['volume'];
                                echo'  <td>TRANSFER</td>';                           
                                echo'  <td>-</td>';
                                echo'  <td>'.$det['volume'].'</td>';
                                echo'  <td>'.$v.'</td>';
                                $transferoutTotal += $v;
                               }
                               elseif($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd_code']) && $det['type'] =='TR')
                               {
                                $v = $det['volume'];
                                echo'  <td>TRANSFER</td>';  
                                echo'  <td>'.$det['volume'].'</td>'; 
                                echo'  <td>-</td>';                              
                                echo'  <td>'.$v.'</td>';
                                $transferinTotal += $v;
                               }
                                elseif($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd']) and $det['type'] == 1)
                               {
                                $v = $det['volume'];
                                echo'  <td>RIGHTS</td>';                                
                                echo'  <td>'.$det['volume'].'</td>';
                                echo'  <td>-</td>';
                                echo'  <td>'.$v.'</td>';
                                $rightsSubscribeTotal += $v;
                               }
                               elseif($det['cd'] != $det['cd_code'] && $cd == strtoupper($det['cd_code']) and $det['type'] == 1)
                               {
                                $v = $det['volume'];
                                echo'  <td>RIGHTS</td>';                                
                                echo'  <td>'.$det['volume'].'</td>';
                                echo'  <td>-</td>';
                                echo'  <td>'.$v.'</td>';
                                $rightsRenounceTotal += $v;
                               }
                               elseif($det['cd'] == $det['cd_code'] && $det['type'] == 2)
                               {
                                $v = $det['volume'];
                                echo'  <td>BONUS</td>';                                
                                echo'  <td>'.$det['volume'].'</td>';
                                echo'  <td>-</td>';
                                echo'  <td>'.$v.'</td>';
                                $bonusTotal += $v;
                               }
                               elseif($det['cd'] == $det['cd_code'] && $det['type'] == 4)
                               {
                                $v = $det['volume'];
                                echo'  <td>BUY BACK</td>';                              
                                echo'  <td>-</td>';
                                echo'  <td>'.$det['volume'].'</td>';
                                echo'  <td>'.$v.'</td>';
                                $buybackTotal += $v;
                               }

                    echo '   </tr>
                    ';
                    $i++;
                  }
                  echo '   <tr style="font-size: 70%;">
                        <td></td>
                        <td></td>
                        <td>TOTAL</td>
                        <td>'.($buyTotal+$transferinTotal+$rightsSubscribeTotal+$rightsRenounceTotal+$bonusTotal).'</td>
                        <td>'.($transferoutTotal+$sellTotal+$buybackTotal).'</td>
                        <td>'.($transferinTotal+$rightsSubscribeTotal+$rightsRenounceTotal+$bonusTotal+$transferoutTotal+$sellTotal+$buyTotal).'</td>
                   </tr>
                   </tbody>
                          </table>';


              }
    echo '</section>    
        </div>
        </body>
      </html>';
}
elseif (!empty($_GET["accountSummary"])) {
    $cid = $_GET['cid'];

    /*$wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, c.title, c.tpn, c.address
        FROM client_account c
        WHERE (c.ID = :cid OR c.cd_code = :cid)
        ORDER BY c.client_id DESC LIMIT 1
    ");*/
    $wc = $dbh->prepare("SELECT c.title, c.tpn, c.address, CONCAT_WS(' ', c.f_name, c.l_name) AS full_name, c.phone, c.email, b.bank_short_name, c.bank_account, c.cd_code 
          FROM client_account c 
          JOIN cds_holding h ON c.cd_code = h.cd_code 
          JOIN banks b ON c.bank_id = b.bank_id 
          WHERE (c.ID = :cid OR c.cd_code = :cid)
          AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) > 0
          ORDER BY c.client_id 
          LIMIT 1;
    ");
    $wc->bindParam(':cid', $cid);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Account Summary Details</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
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
                <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['title'].' '.$state['full_name'].'<br>
                  Phone No : '.$state['phone'].', Email : '.$state['email'].'<br> 
                  BANK : '.$state['bank_short_name'].', Account No : '.$state['bank_account'].', TPN : '.$state['tpn'].'<br> 
                  ADDRESS : '.$state['address'].'</div>
              </div>
            </div>';
            echo'
            <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table table-striped">
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                    <tr>
                      <th>Sl#</th>
                      <th>CD Code/Symbol</th>
                      <th style="text-align:right;">Volume</th>
                      <th style="text-align:right;">Block Vol</th>
                      <th style="text-align:right;">Pledged Vol</th>
                      <th style="text-align:right;">PIV</th>
                      <th style="text-align:right;">POV</th>
                      <th style="text-align:right;">Total</th>
                    </tr>
                  </thead>
                  <tbody>';
                    $i = 1;
                    $get = $dbh->prepare("SELECT s.symbol, c.cd_code, c.volume, c.pledge_volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, 
                        (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) AS total_volume
                        FROM cds_holding c
                        LEFT JOIN client_account a ON c.cd_code = a.cd_code
                        LEFT JOIN symbol s ON c.symbol_id = s.symbol_id
                        WHERE (a.ID = :cid OR a.cd_code = :cid) 
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
            <br><br><br>
            _________________________________________________________________________________
            &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;This is a computer generated report and requires no signatory.
            _________________________________________________________________________________
        </section> 
      </div>
    </body>
  </html>';
}
elseif (!empty($_GET["BalanceConfirmation"])) 
{
    $cid = $_REQUEST['cid'];
    $currency = $_REQUEST['currency'];
    $rate = $_REQUEST['rate'];
    $date = $_REQUEST['date'];

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
        AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) != 0");
    $wc->bindParam(':cid',$cid);
    $wc->execute();
    $state=$wc->fetch();
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");
        echo '
        <html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <style>
      body {
              background: rgb(204,204,204); 
            }
            page[size="A4"] {
              background: white;
              width: 21cm;
              height: 29.7cm;
              display: block;
              margin: 0 auto;
              margin-bottom: 0.5cm;
              box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
            }
            @media print {
              body, page[size="A4"] {
                margin: 0;
                box-shadow: 0;
              }
            }
      </style>
    </head>
        <body onload="window.print();">
        <page size="A4">
        <div class="wrapper">
        <section class="invoice"  style="padding-left: 50px; padding-right:50px; padding-top:-10px;">
            <div class="row" >
              <div class="col-xs-12" style="">
                <table border="0" width="100%">
                  <tr>
                      <td style="padding-left: 47px;">
                        <img src="../../img/logo.png" alt="Logo">
                      </td>
                      <td>
                        <h3 class="text-center">༄༄།།  རྒྱལ་གཞུང་འགན་ལེན་བདོག་གཏད་བརྗེ་སོར་ཁང་།</h3>
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
               <div class="lead" style="font-size: 100%; margin-top:-25px; text-align: left;"> 
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
                    ';
                    if ($date == 0) {
                      echo'<b>Date : '.date('d-m-Y').'</b>';
                    } else {
                      echo'<b>Date : '.$date.'</b>';
                    }
                    echo'
                    </div> 
              </span>
               </br>
              <center>
                    <div class="lead" style="font-size: 100%; margin-top:-25px;"> <b>TO WHOM IT MAY CONCERN</b></div> 
              </center>
              </br>
              </br>
              <div class="lead" style="font-size: 100%; margin-top:-10px;">
                The Royal Securities Exchange of Bhutan would like to provide the shareholding details of Mr./Mrs./Miss. <b>'.$state['f_name'].' '.$state['l_name']. '</b> bearing CID/DISN # <b>'.$cid.'</b> as follows: </br>
                </div>
              </div>
            </div>';
                    echo'<div class="row" >
                      <div class="col-xs-12 table-responsive">
                        <table class="table  table-striped">
                          <thead style="background-color: #D6EAF8; font-size: 80%;">
                          <th>Sl#</th>                    
                            <th>CD Code/Symbol</th>
                            <th style="text-align:right;">Shares</th>
                            <th style="text-align:right;">Market Price (Nu.)</th>
                            <th style="text-align:right;">Total (Nu.)</th>';
                            if($currency != 'BTN'){
                              echo'<th style="text-align:right;">Total ('.$currency.')</th>';
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
                    if($totvol > 0){
                      $bv=0;
                      $save1 = $dbh->prepare('SELECT h.*,c.f_name,c.l_name, s.symbol,s.name as sname,mp.market_price from cds_holding h,client_account c,symbol s,market_price mp 
                        where h.cd_code=c.cd_code and s.symbol_id=h.symbol_id  and c.cd_code=:cd and h.symbol_id=:sid and mp.symbol_id=h.symbol_id');
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

                        $total=$state1['volume']+$state1['pledge_volume']+$state1['block_volume']+$state1['pending_out_vol']+$state1['pending_in_vol'];
                        $totalNu=$totalNu+($total*$market_price_value);
                        $totaldollars=$totaldollars+($total*$market_price_value/$rate);
                        if($state1['volume']==0){$v='-';}else{$v=number_format($state1['volume'],0,".",",");}
                        if($state1['block_volume']==0){$bv='-';}else{$bv=number_format($state1['block_volume'],0,".",",");}
                        if($state1['pledge_volume']==0){$pv='-';}else{$pv=number_format($state1['pledge_volume'],0,".",",");}
                        if($state1['pending_in_vol']==0){$piv='-';}else{$piv=number_format($state1['pending_in_vol'],0,".",",");}
                        if($state1['pending_out_vol']==0){$pov='-';}else{$pov=number_format($state1['pending_out_vol'],0,".",",");}                     
                        echo'<tr style="font-size: 70%;">
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
                        echo' </tr>

                       ';
                       $i=$i+1;
                      }

                  }else
                  {}

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
                 echo' </tr>
            </tbody>
                    </table>';
            echo'  <div class="row" style="padding-left: 50px; padding-right:50px;">
            </br></br>
            </br>
            <b>Central Depository </b>
            </br>
            </br>
            </br>

            </div></section>
                    </div>
                    </page>
        </body>
      </html>';
}
elseif (!empty($_GET["pus"])) {
    $symbol = $_GET['symbol'];

    $wc = $dbh->prepare("SELECT symbol, symbol_id, name FROM symbol WHERE symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Number of Shareholders</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
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
                    FROM cds_holding WHERE symbol_id=:sid";
                  if($symbol!=''){
                    $sql = $dbh->prepare($query);
                    $sql->bindParam(':sid',$state['symbol_id']);
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
                    $sql = $dbh->prepare("SELECT DISTINCT symbol_id, symbol FROM symbol WHERE status=1 ORDER BY symbol ASC");
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
        </div>
      </body>
    </html>';
}
elseif (!empty($_GET["mcaps"])) {
    $symbol = $_GET['symbol'];
    $wc = $dbh->prepare("SELECT * FROM symbol where symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Number of Shareholders</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
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
          </section>   
        </div>
      </body>
    </html>';
}
elseif(!empty($_GET["topVolLeaders"])) {
    $symbol = $_GET['symbol'];
    $top = $_GET['top'];

    $wc= $dbh->prepare("SELECT * FROM symbol WHERE symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Top Volume Leaders</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
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
            $wc = $dbh->prepare("SELECT h.cd_code, h.volume, h.pledge_volume, h.block_volume, c.f_name, c.l_name, c.tpn, c.address, h.volume + h.pledge_volume + h.block_volume as tot, c.acc_type
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
              <div class="col-lg-12 table-responsive">
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
        </div>
      </body>
    </html>';
}
elseif (!empty($_GET["numberofSholders"])) {
    $symbol = $_GET['symbol'];

    $wc = $dbh->prepare("SELECT * FROM symbol WHERE symbol=:symbol AND status=1");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Number of Shareholders</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-lg-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Number of Shareholders</div> 
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
                  echo'
                  </tbody>
                </table>
              </div>
            </div>
          </section>    
        </div>
      </body>
    </html>';
}
elseif(!empty($_GET["announcementList"])) {
    $status = $_GET['status'];
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Announcement List</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Announcement List</div> 
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
                      <th>Security Symbol</th>                    
                      <th>Security Name</th>
                      <th>Record Date</th>
                      <th>Ex Date</th>                    
                      <th>Announcement Date</th>
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
        </div>
      </body>
    </html>';
}
elseif(!empty($_GET["generalShareholderList"])) {
    $symbol = $_GET['symbol'];

    $wc = $dbh->prepare("SELECT symbol, symbol_id, name FROM symbol WHERE symbol=:symbol");
    $wc->bindParam(':symbol',$symbol);
    $wc->execute();
    $state = $wc->fetch();
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>General Share Holder List</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
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
            $wc = $dbh->prepare("SELECT a.acc_type, c.cd_code, a.title, a.f_name, a.l_name, a.tpn, a.ID, a.address, SUM(c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) AS total 
              FROM cds_holding c 
              JOIN client_account a ON c.cd_code = a.cd_code
              WHERE symbol_id = :sid 
                AND (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) > 0 
                GROUP BY c.cd_code 
                ORDER BY c.cd_code ASC
            ");
            $wc->bindParam(':sid',$state['symbol_id']);
            $wc->execute();    
            echo '
            <div class="row">
              <div class="col-xs-12 table-responsive">
                <table class="table  table-striped" >
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                    <tr>
                      <th>sl.</th>
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
                    foreach($wc as $state) {
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
                        $i = $i + 1;
                      }
                    }
                  echo'
                  <tr>
                    <td>Total</td>
                    <td></td><td>
                    </td><td></td>
                    <td></td>
                    <td></td>
                    <td>'.number_format($sh,0,".",",").'</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>    
      </div>
    </body>
  </html>';
}
elseif(!empty($_GET["pledgeDetails"])) {
    $symbol=$_GET['symbol'];
    $plType=$_GET['plType'];
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Pledge Details</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Pledge Details</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
        if($plType == 'S') {
          $wc= $dbh->prepare("SELECT symbol, symbol_id, name FROM symbol WHERE symbol=:symbol");
          $wc->bindParam(':symbol',$symbol);
          $wc->execute();
          $state = $wc->fetch();
          $symbol_id = $state['symbol_id'];
          echo'
          <div class="row">
            <div class="col-xs-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">Security Symbol : '.$symbol.'</div>
              <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['name'].'</div>
            </div>
          </div>';
          $wc= $dbh->prepare("SELECT pl.cd_code,cl.title,cl.f_name,cl.l_name,cl.ID, sum(pl.pledge_volume) as pledge_volume,pl.pledgee 
              FROM cds_pledge pl
              JOIN client_account cl ON pl.cd_code = cl.cd_code
              WHERE symbol_id = :sid 
              -- AND pledge_volume > 0
              GROUP BY pl.cd_code, pl.pledgee 
              HAVING SUM(pl.pledge_volume) > 0 
              ORDER BY pl.pledge_volume DESC
          ");
          $wc->bindParam(':sid',$state['symbol_id']);
          $wc->execute();    
          echo '
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table  table-striped" >
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
                    $i = $i + 1;
                  }
          } else {
                echo'
                <div class="row">
                  <div class="col-xs-12">
                    <div class="lead" style="font-size: 70%; margin-top:-10px;">Pledgee : '.$symbol.'</div>
                  </div>
                </div>';
                $wc= $dbh->prepare("SELECT pl.cd_code, cl.title, cl.f_name, cl.l_name, cl.ID, s.symbol, sum(pl.pledge_volume) as pledge_volume 
                  FROM cds_pledge pl
                  JOIN client_account cl ON pl.cd_code = cl.cd_code
                  JOIN symbol s ON pl.symbol_id = s.symbol_id
                  WHERE pl.pledgee = :pl 
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
                      foreach($wc as $state) {
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
                        $sh = $totalShares + $sh;
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
    </div>
  </body>
</html>';
}
elseif (!empty($_GET["pledgeActivity"])) {
    $pledgeContCode = $_GET['pledgeContCode'];

    $wc = $dbh->prepare("SELECT c.pledge_contract, c.pledge_name, a.cd_code, a.f_name, a.l_name, a.ID 
      FROM cds_pledge_contract c 
      JOIN client_account a ON c.cd_code = a.cd_code
      WHERE c.pledge_contract = :plcntr
    ");
    $wc->bindParam(':plcntr', $pledgeContCode);
    $wc->execute();
    $state = $wc->fetch();
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
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
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
                  $wc = $dbh->prepare("SELECT DISTINCT symbol_id FROM cds_pledge WHERE pledge_contract = :contCode");
                  $wc->bindParam(':contCode',$pledgeContCode);
                  $wc->execute();
                  $i = 1;
                  foreach($wc as $state) {
                    $wc1 = $dbh->prepare("SELECT s.symbol, s.name, c.pledgee, sum(c.pledge_volume) as volume, m.market_price 
                      FROM cds_pledge c
                      JOIN market_price m ON c.symbol_id = m.symbol_id 
                      JOIN symbol s ON c.symbol_id = s.symbol_id
                      WHERE c.symbol_id = :sid AND c.pledge_contract = :contCode
                    ");
                    $wc1->bindParam(':sid', $state['symbol_id']);
                    $wc1->bindParam(':contCode', $pledgeContCode);
                    $wc1->execute();  
                    foreach($wc1 as $state1) {
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
        </div>
      </body>
    </html>';
}
elseif (!empty($_GET["detailNetting"])) {
    $toDate = $_GET['toDate1'].' 23:59:00';
    $fromDate = $_GET['fromDate1'].' 00:00:00';
    $table_name = $_GET['table_name'];
    $sysTime = date("Y-m-d");

    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Detail Netting Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Netting Position for Trade</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
              $query= $dbh->prepare("SELECT DISTINCT participant_code FROM {$table_name} WHERE order_date BETWEEN :fdate AND :tdate");
              $query->bindParam(':fdate', $fromDate);
              $query->bindParam(':tdate', $toDate);
              $query->execute();
              foreach ($query as $res) {
                echo'
                <div class="row">
                  <div class="col-xs-12">
                    <div class="lead" style="font-size: 70%; margin-top:-10px;">MEMBER : '.$res['participant_code'].'</div>
                  </div>
                </div>
                <table class="table">
                  <thead>
                    <tr style="background-color:#333;color:#fff">
                      <th>SN</th>
                      <th>VOL BUY</th>
                      <th>VOL SELL</th>
                      <th>AMOUNT</th>
                    </tr>
                  </thead>
                  <tbody>';
                  $i = 1;
                  $executed_orders = $dbh->prepare("
                    SELECT distinct a.symbol_id, b.symbol 
                    FROM {$table_name} a 
                    JOIN symbol b ON a.symbol_id = b.symbol_id
                    WHERE a.status = 0 AND a.participant_code = :pc
                  ");
                  $executed_orders->bindParam(':pc', $res['participant_code']);
                  $executed_orders->execute();
                  $rows = $executed_orders->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $res1) {
                    echo "
                      <tr>
                          <td>SYMBOL :".$res1['symbol']."</td>
                      </tr>";
                      $list_ord = $dbh->prepare("
                          SELECT sum(lot_size_execute) AS totlot, cast(avg(order_exe_price) AS decimal(13,2)) AS avgp 
                          FROM {$table_name}  
                          WHERE status = 0 AND participant_code = :pc AND symbol_id = :syid AND side = 'B'
                      ");
                      $list_ord->bindParam(':pc', $res['participant_code']);
                      $list_ord->bindParam(':syid', $res1['symbol_id']);
                      $list_ord->execute();
                      $res2 = $list_ord->fetch();
                      $totbuyamt = $res2['avgp'] * $res2['totlot'];
                      echo'
                      <tr>
                          <td>'.$i++.'</td>
                          <td>'.$res2['totlot'].'</td>
                          <td>-</td>
                          <td>Nu. ('.number_format($totbuyamt, 2, ".", ",").')</td>
                      </tr>';

                      $list_ord = $dbh->prepare("
                          SELECT sum(lot_size_execute) AS totlots , cast(avg(order_exe_price) AS decimal(13,2)) AS avgps 
                          FROM {$table_name} 
                          WHERE status = 0 AND participant_code=:pc AND symbol_id = :syid AND side = 'S'
                      ");
                      $list_ord->bindParam(':pc', $res['participant_code']);
                      $list_ord->bindParam(':syid', $res1['symbol_id']);
                      $list_ord->execute();
                      $res2 = $list_ord->fetch(); 
                      $totsellamt = $res2['avgps'] * $res2['totlots'];
                      echo'
                      <tr>
                          <td>'.$i.'</td>
                          <td>-</td>
                          <td>'.$res2['totlots'].'</td>
                          <td>Nu. '.number_format($totsellamt, 2, ".", ",").'</td>
                      </tr>';
                
                      $diff = -$totbuyamt + $totsellamt;
                  }
                  if ($diff > 0) {
                      $rm ="Receiveable";
                  } elseif ($diff<0) {
                      $rm = "Payable";
                  } elseif ($diff == 0) {
                      $rm = "None";
                  }
                  echo"
                  <tr>
                    <td><b>Difference<b></td>
                    <td></td><td><b>".$rm."</b></td>
                    <td><b>Nu. ".number_format($diff, 2, ".", ",")."</b></td>
                  </tr>
                </tbody>
              </table>";
              }
        echo"</section>
          </div>
        </body>
      </html>";
}
elseif (!empty($_GET["Clearing"])) {
    $toDate = $_GET['toDate1'].' 23:59:00';
    $fromDate = $_GET['fromDate1'].' 00:00:00';
    $sysTime = date("Y-m-d");

    $sec_type = $_GET['sec_type'];
    $tablename = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';
    $trade_type = ($sec_type === 'OS') ? 'Equity' : 'Bond';

    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Clearing Detail Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">'.$trade_type.' Clearing Detail</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
            $query= $dbh->prepare("
                SELECT DISTINCT a.participant_code,b.clearing_account 
                FROM {$tablename}  a 
                JOIN adm_participants b ON a.participant_code = b.participant_code
                WHERE order_date BETWEEN :fdate AND :tdate
              ");
                $query->bindParam(':fdate', $fromDate);
                $query->bindParam(':tdate', $toDate);
                $query->execute();
                $i=1;
                $loop = 1;
                foreach ($query as $res) {
                  $totalb = 0;
                  $totals = 0;
                  if ($loop <= 3) {
                    echo"
                    <div class='row'>
                      <div class='col-xs-12'>
                        <div class='lead' style='font-size: 70%; margin-top:-10px;'>MEMBER : ".$res['participant_code']."</div>
                      </div>
                    </div>
                    <table class='table table'>
                      <thead>
                        <tr style='background-color:#333;color:#fff'>
                          <th>SN</th>
                          <th>REMARKS</th>
                          <th></th>
                          <th>AMOUNT</th>
                        </tr>
                      </thead>
                      <tbody>";
                      $list_ord = $dbh->prepare("SELECT * FROM {$tablename} WHERE status=0 AND participant_code=:pc AND side = 'B' AND order_date BETWEEN :fdate AND :tdate");
                      $list_ord->bindParam(':pc', $res['participant_code']);
                      $list_ord->bindParam(':fdate',$fromDate);
                      $list_ord->bindParam(':tdate',$toDate);
                      $list_ord->execute();
                      foreach ($list_ord as $res2) {
                        $totalbuy = ($sec_type === 'OS') ? ($res2['lot_size_execute'] * $res2['order_exe_price']) : ($res2['lot_size_execute'] * $res2['dirty_price']);
                        $totalb = $totalb + $totalbuy;
                      }
                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>Total buy amount</td>
                        <td></td><td>Nu. ('.number_format($totalb,2,".",",").')</td>
                      </tr>';
                      $list_ord = $dbh->prepare("SELECT * FROM {$tablename} WHERE status = 0 AND participant_code=:pc AND side = 'S' AND order_date BETWEEN :fdate AND :tdate");
                      $list_ord->bindParam(':pc', $res['participant_code']);
                      $list_ord->bindParam(':fdate', $fromDate);
                      $list_ord->bindParam(':tdate', $toDate);
                      $list_ord->execute();
                      foreach ($list_ord as $res3) {
                        $totalsell = ($sec_type === 'OS') ? ($res3['lot_size_execute'] * $res3['order_exe_price']) : ($res3['lot_size_execute'] * $res3['dirty_price']);
                        $totals = $totals + $totalsell;
                      }
                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>Total sell amount</td>
                        <td></td>
                        <td>Nu. '.number_format($totals,2,".",",").'</td>
                      </tr>';
                      $diff = $totals - $totalb;
                      if ($diff > 0) {
                        $rm = "<span style='color:green;'>CREDIT (Pay)</span>";
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td><b> Account # : ".$res['clearing_account']."</b></td>
                          <td></td>
                          <td><b>Nu. (".number_format($diff,2,".",",").")</b></td>
                        </tr>";
                      } elseif ($diff < 0 ) { 
                        $rm = "<span style='color:red;'>DEBIT(Collect)</span>";
                        $diff = $diff * -1;
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td><b> Account # : ".$res['clearing_account']."</b></td>
                          <td></td>
                          <td><b>Nu. ".number_format($diff,2,".",",")."</b></td>
                        </tr>";
                      } elseif ($diff == 0) {
                        $rm = "None";
                        echo"
                        <tr>
                          <td><b>Instruction : <b>".$rm."</b><b></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>";
                      }
                      echo"
                      </tbody>
                    </table><br>";
                  } else {
                    if($loop == 4) {
                    echo"
                    <div class='row'>
                      <div class='col-xs-12'>
                        <div class='lead' style='font-size: 70%; margin-top:-10px;'>MEMBER : ".$res['participant_code']."</div>
                      </div>
                    </div>
                    <table class='table table'>
                      <thead>
                        <tr style='background-color:#333;color:#fff'>
                          <th>SN</th>
                          <th>REMARKS</th>
                          <th></th>
                          <th>AMOUNT</th>
                        </tr>
                      </thead>
                      <tbody>";
                      $list_ord = $dbh->prepare("SELECT * FROM {$tablename} WHERE status = 0 AND participant_code = :pc  AND side = 'B' AND order_date BETWEEN :fdate AND :tdate");
                      $list_ord->bindParam(':pc', $res['participant_code']);
                      $list_ord->bindParam(':fdate', $fromDate);
                      $list_ord->bindParam(':tdate', $toDate);
                      $list_ord->execute();
                      foreach ($list_ord as $res2) {
                        $totalbuy = ($sec_type === 'OS') ? ($res2['lot_size_execute'] * $res2['order_exe_price']) : ($res2['lot_size_execute'] * $res2['dirty_price']);
                        $totalb = $totalb + $totalbuy;
                      }
                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>Total buy amount</td>
                        <td></td>
                        <td>Nu. ('.number_format($totalb, 2, ".", ",").')</td>
                      </tr>';
                      $list_ord= $dbh->prepare("SELECT * FROM {$tablename}  WHERE status=0 AND participant_code=:pc AND side = 'S' AND order_date >= :fdate AND order_date <= :tdate");
                      $list_ord->bindParam(':pc',$res['participant_code']);
                      $list_ord->bindParam(':fdate',$fromDate);
                      $list_ord->bindParam(':tdate',$toDate);
                      $list_ord->execute();
                      foreach($list_ord as $res3){
                        $totalsell = ($sec_type === 'OS') ? ($res3['lot_size_execute'] * $res3['order_exe_price']) : ($res3['lot_size_execute'] * $res3['dirty_price']);
                        $totals = $totals + $totalsell;
                      }   
                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>Total sell amount</td>
                        <td></td>
                        <td>Nu. '.number_format($totals,2,".",",").'</td>
                      </tr>';
                      $diff = $totals - $totalb;
                      if ($diff > 0) {
                        $rm = "<span style='color:green;'>CREDIT (Pay)</span>";
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td><b> Account # : ".$res['clearing_account']."</b></td>
                          <td></td>
                          <td><b>Nu. (".number_format($diff,2,".",",").")</b></td>
                        </tr>";
                      }
                      elseif ($diff < 0){
                        $rm = "<span style='color:red;'>DEBIT(Collect)</span>";
                        $diff = $diff * -1;
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b>
                          </td><td><b> Account # : ".$res['clearing_account']."</b></td>
                          <td></td>
                          <td><b>Nu. ".number_format($diff,2,".",",")."</b></td>
                        </tr>";
                      }
                      elseif ($diff == 0) {
                        $rm = "None";
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>";
                      }
                      echo"
                      </tbody>
                    </table><br>";
                  } else {
                    echo"
                    <div class='row'>
                      <div class='col-xs-12'>
                        <div class='lead' style='font-size: 70%; margin-top:-10px;'>MEMBER : ".$res['participant_code']."</div>
                      </div>
                    </div>
                    <table class='table table'>
                      <thead>
                        <tr style='background-color:#333;color:#fff'>
                          <th>SN</th>
                          <th>REMARKS</th>
                          <th></th><th>AMOUNT</th>
                        </tr>
                      </thead>
                      <tbody>";
                      $list_ord= $dbh->prepare("SELECT * FROM {$tablename} WHERE status=0 AND participant_code=:pc  AND side = 'B' AND  order_date BETWEEN :fdate AND :tdate");
                      $list_ord->bindParam(':pc',$res['participant_code']);
                      $list_ord->bindParam(':fdate',$fromDate);
                      $list_ord->bindParam(':tdate',$toDate);
                      $list_ord->execute();
                      foreach ($list_ord as $res2) {
                        $totalbuy = ($sec_type === 'OS') ? ($res2['lot_size_execute'] * $res2['order_exe_price']) : ($res2['lot_size_execute'] * $res2['dirty_price']);
                        $totalb = $totalb + $totalbuy;
                      }
                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>Total buy amount</td>
                        <td></td><td>Nu. ('.number_format($totalb, 2, ".", ",").')</td>
                      </tr>';
                      $list_ord= $dbh->prepare("SELECT * FROM {$tablename} WHERE status=0 AND participant_code=:pc  AND side = 'S' AND  order_date BETWEEN :fdate AND :tdate");
                      $list_ord->bindParam(':pc',$res['participant_code']);
                       $list_ord->bindParam(':fdate',$fromDate);$list_ord->bindParam(':tdate',$toDate);
                      $list_ord->execute();
                      foreach ($list_ord as $res3) {
                        $totalsell = ($sec_type === 'OS') ? ($res3['lot_size_execute'] * $res3['order_exe_price']) : ($res3['lot_size_execute'] * $res3['dirty_price']);
                        $totals = $totals + $totalsell;
                      }   
                      echo'
                      <tr>
                        <td>'.$i++.'</td>
                        <td>Total sell amount</td>
                        <td></td>
                        <td>Nu. '.number_format($totals,2,".",",").'</td>
                      </tr>';
                      $diff = $totals - $totalb;
                      if ($diff > 0) {
                        $rm = "<span style='color:green;'>CREDIT (Pay)</span>";
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td><b> Account # : ".$res['clearing_account']."</b></td>
                          <td></td>
                          <td><b>Nu. (".number_format($diff,2,".",",").")</b></td>
                        </tr>";
                      } elseif ($diff < 0) {
                        $rm = "<span style='color:red;'>DEBIT(Collect)</span>";
                        $diff = $diff * -1;
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td><b> Account # : ".$res['clearing_account']."</b></td>
                          <td></td>
                          <td><b>Nu. ".number_format($diff,2,".",",")."</b></td>
                        </tr>";
                      }
                      elseif ($diff == 0) {
                        $rm = "None";
                        echo"
                        <tr>
                          <td><b>Instruction : ".$rm."</b></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>";
                      }
                      echo"
                      </tbody>
                    </table><br>";
                  }
                }
              $loop++;
            }
            echo"
            </section>    
          </div>
        </body>
      </html>";
}
elseif(!empty($_GET["tradeDetails"])) {
    $toDate = $_GET['toDate1'].' 23:59:00';
    $fromDate = $_GET['fromDate1'].' 00:00:00';
    $table_name = $_GET['table_name'];
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Trade Detail Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-lg-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Trade Detail</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                   Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
                </div>
              </div>
            </div>';
            $query = $dbh->prepare("SELECT DISTINCT participant_code FROM {$table_name} WHERE order_date BETWEEN :fdate AND :tdate");
                $query->bindParam(':fdate', $fromDate);
                $query->bindParam(':tdate', $toDate);
                $query->execute();
                foreach ($query as $res) {
                  echo'
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="lead" style="font-size: 70%; margin-top:-10px;">MEMBER : '.$res['participant_code'].'</div>
                    </div>
                  </div>';
                  echo"
                  <table class='table table-bordered'>
                    <thead>
                        <tr style='background-color:#333;color:#fff'>
                            <th>SN</th>
                            <th>ACCOUNT </th>
                            <th>SIDE/DATE</th>
                            <th>VOLUME</th>
                            <th>PRICE</th>
                            <th>AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>";
                    $i = 1;
                    $executed_orders = $dbh->prepare("
                        SELECT DISTINCT a.symbol_id, b.symbol 
                        FROM {$table_name} a 
                        JOIN symbol b ON a.symbol_id = b.symbol_id
                        WHERE a.participant_code=:pc and  order_date BETWEEN :fdate AND :tdate
                    ");
                    $executed_orders->bindParam(':pc', $res['participant_code']);
                    $executed_orders->bindParam(':fdate', $fromDate);
                    $executed_orders->bindParam(':tdate', $toDate);
                    $executed_orders->execute();
                    foreach($executed_orders as $res1){
                        $list_ord= $dbh->prepare("
                            SELECT * FROM {$table_name} 
                            WHERE participant_code=:pc AND symbol_id=:syid AND order_date BETWEEN :fdate AND :tdate
                        ");
                        $list_ord->bindParam(':pc', $res['participant_code']);
                        $list_ord->bindParam(':syid', $res1['symbol_id']);
                        $list_ord->bindParam(':fdate', $fromDate);
                        $list_ord->bindParam(':tdate', $toDate);
                        $list_ord->execute();
                        foreach ($list_ord as $res3) {
                            $amt = $res3['lot_size_execute'] * $res3['order_exe_price'];
                            echo'
                            <tr>
                                <td>'.$i++.' .'.$res1['symbol'].'</td>
                                <td>'.$res3['cd_code'].'</td>
                                <td>'.$res3['side'].' - '.$res3['order_date'].'</td>
                                <td>'.number_format($res3['lot_size_execute'],2,".",",").'</td>
                                <td>'.$res3['order_exe_price'].'</td>
                                <td>Nu. '.number_format($amt,2,".",",").'</td>
                            </tr>';
                        }
                        $list_ord = $dbh->prepare("
                          SELECT sum(lot_size_execute) as totlot , cast(avg(order_exe_price) as decimal(13,2)) as avgp 
                          FROM {$table_name} WHERE participant_code=:pc AND symbol_id=:syid AND side='B' AND  order_date BETWEEN :fdate AND :tdate
                        ");
                        $list_ord->bindParam(':pc',$res['participant_code']);
                        $list_ord->bindParam(':syid',$res1['symbol_id']);
                        $list_ord->bindParam(':fdate',$fromDate);
                        $list_ord->bindParam(':tdate',$toDate);
                        $list_ord->execute();
                        $res2 = $list_ord->fetch();
                        $totbuyamt = $res2['avgp'] * $res2['totlot'];

                        $list_ord= $dbh->prepare("
                          SELECT sum(lot_size_execute) as totlots , cast(avg(order_exe_price) AS decimal(13,2)) AS avgps 
                          FROM {$table_name} WHERE participant_code=:pc and symbol_id=:syid and side='S' and  order_date BETWEEN :fdate AND :tdate
                        ");
                        $list_ord->bindParam(':pc',$res['participant_code']);
                        $list_ord->bindParam(':syid',$res1['symbol_id']);
                        $list_ord->bindParam(':fdate',$fromDate);
                        $list_ord->bindParam(':tdate',$toDate);
                        $list_ord->execute();
                        $res4 = $list_ord->fetch(); 
                        $totsellamt = $res4['avgps'] * $res4['totlots'];  
                        echo'
                        <tr style="font-weight: bold;">
                          <td>Total :</td>
                          <td> Buy Vol : '.number_format((float)($res2['totlot'] ?? 0), 0, ".", ",").'</td>
                          <td> Sell Vol : '.number_format((float)($res4['totlots'] ?? 0), 0, ".", ",").'</td>
                          <td></td>
                          <td>Total Trade</td>
                          <td>Nu. '.number_format($totsellamt,2,".",",").'</td>
                        </tr>';    
                    }
                    echo"
                    </tbody>
                </table>";
              }
        echo"</section>    
        </div>
        </body>
      </html>";
} 
elseif(!empty($_GET["orderaudit"])) {
    $toDate = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00'; 
    $sysTime = date("Y-m-d");
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Trade Detail Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
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
                    JOIN symbol s ON o.symbol_id = s.symbol_id
                    WHERE o.order_date BETWEEN :fdate AND :tdate
                  ");
                  $wc->bindParam(':fdate', $fromDate);
                  $wc->bindParam(':tdate', $toDate);
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
        </div>
      </body>
    </html>';
}
//print report for online Terminal 
elseif(!empty($_GET["op"]) && $_GET["op"]=="terminal_report") 
{
  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");

  $cidNo=$_GET['cidNo'];
  $cdCode=$_GET['cdCode'];
  $pCode=$_GET['pCode'];

  $wc= $dbh->prepare("SELECT c.cd_code, u.name, u.cid, u.address, u.phone, u.participant_code, c.user_name, c.title, a.name pCode, u.email, DATE(u.created_at) cDate, DAY(u.created_at) cDay, MONTHNAME(u.created_at) cMonth, YEAR(u.created_at) cYear
    FROM users u 
    LEFT JOIN adm_participants a ON u.participant_code=a.participant_code
    LEFT JOIN client_account c ON u.cid = c.ID
    WHERE u.cid=:cid AND u.participant_code=:pCode AND c.user_name LIKE '{$pCode}%'");
  $wc->bindParam(':cid',$cidNo);
  $wc->bindParam(':pCode',$pCode);
  $wc->execute();
  $state = $wc->fetch();
  echo'
  <html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Online Terminal Form</title>
  </head>
  <body onload="window.print();">
    <div class="wrapper">
      <section class="invoice" style="background:rgb(248, 249, 249);">
        <div class="row">
          <div class="col-xs-12">
            <div class="">
              <div class="col-xs-2">
                <img src="../../dist/img/Logo.png">
              </div>
              <div class="col-xs-10">
                <center><b style="font-size: 25px;">༄༄།། རྒྱལ་གཞུང་གན་ལེན་བདོག་གཏད་བརྗེ་སོར་ཁང་།</b></center><br>
                <center><b style="font-size: 25px; float: left;">ROYAL SECURITIES EXCHANGE OF BHUTAN LIMITED</b></center><br><br>
              </div>
            </div>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-xs-12">
            <center><div class="lead" style=""><b>Application for Online Trading(Internet Trading Terminal) to be submitted along with application fee of Nu.500/-</b></div>
            </center>
          </div>
          <div class="col-xs-12">
            <span style="float:right;"><b>DATE[Y-M-D]:</b> '.$state['cDate'].'</span>
            <b>Chief Executive Officer,<br>
            Royal Securities Exchange of Bhutan Ltd,<br>
            Thimphu, Bhutan<br><br></b>

            Sir,<br><br>

            I wish to apply for Online Trading Terminal (Internet Trading) through <b>'.$pCode.'</b> Securities Limited to transact on my own behalf which my details are filled in as follow:-<br><br>
            
            FULL NAME: <b>'.$state['name'].'</b><br><br>
            CITIZEN IDENTITY CARD NO: <b>'.$state['cid'].'</b>&emsp; &emsp; &emsp;
            CD CODE: <b>'.$state['cd_code'].'</b>&emsp; &emsp; &emsp;
            PARTICIPATE CODE: <b>'.$state['participant_code'].'</b><br><br>

            MOBILE NO: <b>'.$state['phone'].'</b>&emsp; &emsp; &emsp;
            Email Address: <b>'.$state['email'].'</b><br><br>

            CURRENT ADDRESS: <b>'.$state['address'].'</b><br><br><br><br>

            <center><b>Declaration</b></center>
            <center>I declare that, the information stated above are true to the best of my knowledge and belief.</center><br><br><br><br>

            <img src="../../dist/img/stamp.jpg" style="float:right; height: 150px;"><br><br><br><br><br><br><br><br>
            <p style="float:right;">Name and Signature of the applicant</p>

            <p style="float:left;">Recommendation by the Brokerage Firm/ Signature</p><br><br><br><br><br><br>



            <center><b>AGREEMENT BETWEEN THE ROYAL SECURITIES EXCHANGE OF BHUTAN (RSEB) AND THE CLIENT SEEKING TO USE ONLINE TRADING TERMINAL WITH THE RSEB</b></center><br>

            <p align="justify">This Agreement is drawn on this <b>'.$state['cDay'].'</b> day of <b>'.$state['cMonth'].'</b> between the <b>ROYAL SECURITIES EXCHANGE OF BHUTAN LIMITED (RSEB)</b> situated at Thimphu, Bhutan hereinafter called the <b>“RSEB”</b> of the One part; AND <b>'.$state['name'].'</b> situated at <b>'.$state['address'].'</b> hereinafter called <b>“the Client”</b> of the <b>Other Part</b>.<p>

            <center><b>Witnesseth</b></center><br>
            <b>WHEREAS</b> the Client has furnished to the RSEB the duly filled-in application in the specified form requesting the RSEB for online trading terminal.<br><br>

            <b>NOW THEREFORE</b> in consideration of the RSEB having agreed to provide online trading terminal to the client, the parties hereto do hereby agree and covenant with each other as follows:<br><br>

            <b>1.&nbsp;&nbsp; General Clauses</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp; 1.1 Words and expressions used but not defined in this Agreement but defined under, The Companies Act 2000, The Financial Services Act 2011, The Exchange ATS Rule shall have the meaning assigned to them under the aforesaid Acts, Regulations or Rules as the case may be. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;1.2 The parties hereto shall be bound by the Companies Act 2000, The Financial Services Act 2011, The Exchange ATS Rule and agree to abide by the Rules and Operating Instructions issued from time to time by the RSEB in the same manner and to the same extent as if the same were set out herein and formed part of this Agreement. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;1.3 The Client shall continue to be bound by the Rules and Operating Instructions / User Manual of RSEB even after ceasing to be a Client in so far as may be necessary for completion of or compliance with its obligations in respect of all matters, entries or transactions which the Client may have carried out, executed, entered into, undertaken or may have been required to do, before ceasing to be the Client and which may have remained outstanding, incomplete or pending at the time of its ceasing to be a Client.<br><br>

            <b>2. &nbsp;&nbsp Fees and Charges</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;2.1 &nbsp;&nbsp;The Client shall pay such fees and charges to the RSEB, as may be mutually agreed upon, for availing online trading terminal for rendering such other services as are incidental or consequential to the Client.

            &nbsp;&nbsp;&nbsp;&nbsp;2.2 &nbsp;&nbsp;The RSEB shall be entitled to change or revise the fees and charges from time to time provided however that no increase therein shall be effected by the RSEB unless the RSEB shall have given at least one months notice in writing to the Client in that behalf.<br> 

            &nbsp;&nbsp;&nbsp;&nbsp;2.3 &nbsp;&nbsp;The Client further agrees that in the event of default in the payment of any of the fees or charges to the RSEB on their respective due dates or within one month of the same being demanded then, without prejudice to the right of the RSEB to terminate the Agreement and close the Online Trading Terminal of the Client, the RSEB shall be entitled to charge interest on the amount remaining outstanding or unpaid at the highest prevailing Bank Rate.<br><br>

            <b>3. &nbsp;&nbsp Responsibilities</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.1&nbsp;&nbsp;  The RSEB shall ensure that satisfactory arrangements are in place to ensure confidentiality of information in such a way that information is only accessible to an authorized person.<br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.2&nbsp;&nbsp;  The client shall safeguard the integrity of the service including the control to prevent: <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)  Non-compliance with laws, Rules, Regulations and Guidelines issued by the RSEB, leading to illegal transactions, fraud or malpractice,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii) Presentation of incorrect data, whether unintentionally or malevolently, <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iii)  False presentation or the use of incomplete information for transactions,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iv) Manipulation of data,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;v)  Viruses, leading to inter alia loss of data, unauthorized access to or manipulation of data, unavailability or threat of unavailability of system, <br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.3  &nbsp;&nbsp;Ensure the availability of the service in the event that:<br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)  In the event of any failure in the Online Trading Services arising through the failure of internet or the Online System the client shall route their orders through their respective brokers.<br>

            &nbsp;&nbsp;&nbsp;&nbsp;3.4  &nbsp;&nbsp;The Client shall be held responsible for any kind of transaction arising due to his /her negligence such as loss of password, unauthorized transactions or any kind of fraud or malpractice. <br><br>

            <b>4.  &nbsp;&nbsp;Redressal of Grievances </b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;The RSEB shall promptly attend to all grievances / complaints of the Client and shall resolve all such grievances / complaints as it relate to matters exclusively within the domain of the RSEB and shall endeavor to resolve the same at the earliest.<br><br>

            <b>5.  &nbsp;&nbsp;Termination</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.1  &nbsp;&nbsp;The RSEB shall be entitled to terminate this agreement in the event of the Client: <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i.  Failing to pay the fees or charges as may be mutually agreed upon within a period of one month from the date of demand made in that behalf; <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii. commits or participates in any fraud or other act of moral turpitude in his / its dealings with the RSEB; <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iii.  otherwise misconducts himself in any manner. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.2  &nbsp;&nbsp;The RSEB may also terminate the Agreement without assigning any reasons for such termination provided the RSEB shall have issued at least one months prior notice in writing to the Client in that behalf. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.3  &nbsp;&nbsp;The Client may at any time terminate the Agreement by calling upon the RSEB to close his / her Online Trading terminal with the RSEB provided no instructions remain pending or unexecuted and no fees or charges remain payable by the Client to the RSEB. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;5.4 &nbsp;&nbsp; Notwithstanding termination of the Agreement by the RSEB or closure of his / its Online Trading Terminal by the Client, the provisions of the Agreement and all mutual rights and obligations arising therefrom shall, except in so far as the same are contrary to or inconsistent with such termination or closure, shall continue to be binding on the parties in respect of all acts, deeds, matters and things done and transactions effected during the period when the Agreement was effective.<br><br>

            <b>6.  Authorized Representative </b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;Where the Client is a body corporate, it shall, simultaneously with the execution of the Agreement furnish to the RSEB, a list of officials authorized by it, who shall represent and interact on its behalf with the RSEB. Any change in such list including additions, deletions or alterations thereto shall be forthwith communicated to the RSEB.<br><br>

            <b>7.  Service of Notice</b><br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.1  &nbsp;&nbsp;Any notice or communication required to be given under the Agreement shall not be binding unless the same is in writing and shall have been served by delivering the same at the address set out hereinabove against a written acknowledgement of receipt thereof or by sending the same by pre-paid registered post at the aforesaid address or transmitting the same by facsimile transmission, electronic mail or electronic data transfer at number or address that shall have been previously specified by the party to be notified. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.2  &nbsp;&nbsp;Notice given by personal delivery shall be deemed to be given at the time of delivery. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.3  &nbsp;&nbsp;Notice sent by post in accordance with this clause shall be deemed to be given at the commencement of business of the recipient of the notice on the third working day next following its posting. <br>

            &nbsp;&nbsp;&nbsp;&nbsp;7.4  &nbsp;&nbsp;Notice sent by facsimile transmission, electronic mail or electronic data transfer shall be deemed to be given at the time of its actual transmission.<br><br>


            <b>8.  Governing Law</b><br> 

            &nbsp;&nbsp;&nbsp;&nbsp;The Agreement shall be governed by and construed in accordance with the laws in force in Kingdom of Bhutan.<br> <br> 

            <b>9.  Interpretation</b> <br>

            &nbsp;&nbsp;&nbsp;&nbsp;Unless the context otherwise requires, words denoting the singular shall include the plural and vice versa and words denoting the masculine gender shall include the feminine and vice versa and any reference to any stature, enactment or legislation or any provision thereof shall include any amendment thereto or any reenactment thereof.<br> <br> 

            <b>10. Jurisdiction</b> <br>

            &nbsp;&nbsp;&nbsp;&nbsp;The parties hereto agree to submit to the exclusive jurisdiction of the Royal Court of Justice, Kingdom of Bhutan.<br> <br> 

            <b>11. Execution of Agreement</b> <br>

            &nbsp;&nbsp;&nbsp;&nbsp;This Agreement is executed in duplicate and a copy each shall be retained by each of the parties hereto.<br><br>

            <b>IN WITNESS WHEREOF</b> the parties hereto have hereunto set and subscribed their respective hands/seals to this Agreement in duplicate on the day <b>'.$state['cDay'].'</b>, month <b>'.$state['cMonth'].'</b>, year <b>'.$state['cYear'].'</b>.<br>

            <br><br><br><br><br><br>
          </div>
          <div class="col-xs-12">
            <div class="col-xs-10"><br>
              SIGNED AND DELIVERED<br>
              By the within named RSEB<br>
              by the hand of its authorized representative: <b>IT Department</b><br>
              in the presence of: <b>RSEB</b><br>
              Name & Address of witness:<br>
            </div>
            <div class="col-xs-2">
              <img src="../../dist/img/stamp.jpg" style="float:right; height: 150px;">
            </div>
            <div class="col-xs-10"><br>
              SIGNED AND DELIVERED<br>
              By the within named CLIENT<br>
              by the hand of its authorized representative: <b>'.$state['name'].'</b><br>
              in the presence of: <b>'.$pCode.'</b><br>
              Name & Address of witness:<br>
            </div>
            <div class="col-xs-2">
              <img src="../../dist/img/stamp.jpg" style="float:right; height: 150px;">
            </div>

            <div class="col-xs-12 text-center">
              <br><br><br><br><br><br><br><br>
              <!-- _________________________________________________________________________________<br>
                This is a computer generated report and required no signatory. <br>
              _________________________________________________________________________________<br>-->
            </div>
          </div>
        </div>
      </section>    
    </div>
  </body>
</html>';
}
else if(!empty($_GET["consolidate_share_report"])) {
    $cid=$_GET['cid'];
    
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    $wc = $dbh->prepare("SELECT c.ID, c.cd_code, c.f_name, c.l_name, c.title, c.tpn, c.address
        FROM client_account c
        WHERE (c.ID = :cid OR c.cd_code = :cid)
        ORDER BY c.client_id DESC LIMIT 1
    ");
    $wc->bindParam(':cid', $cid);
    $wc->execute();
    $state = $wc->fetch();
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Account Summary Details</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp;
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
            </div>';
            echo'
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
                <br>
                  _________________________________________________________________________________
                &emsp; &emsp; &emsp; &emsp; This is a computer generated report and requires no signatory.
                _________________________________________________________________________________
          </section>    
        </div>
      </body>
    </html>';
}
elseif (!empty($_GET["get_share_dtls_statement"])) {
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    $cid_no = $_GET['cid_no'];
    $toDate = $_GET['toDate'].' 23:59:00';
    $fromDate = $_GET['fromDate'].' 00:00:00';

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
    echo'
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Account Activity Report</title>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <section class="invoice" style="background:rgb(248, 249, 249);">
            <div class="row">
              <div class="col-xs-12">
                <div class="page-header">
                  &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                  <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                   <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Account Activity Report</div> 
                   <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b></div></center>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-12">
                <div class="lead" style="font-size: 70%; margin-top:-15px;">NAME : '.$state['full_name'].' , CID/DISN# '.$state['ID'].'</div>
              </div>
            </div>';
            $wc = $dbh->prepare("SELECT a.symbol_id, s.symbol
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
            $wc->bindParam(':cid__no', $cid_no);
            $wc->bindParam(':from__date', $fromDate);
            $wc->bindParam(':to__date', $toDate);
            $wc->execute();
            $i=1;
            foreach ($wc as $state) {
              echo '
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
                          <th>CD Code</th>
                          <th>To CD Code</th>
                          <th>Transaction vol</th>
                          <th>Actual Vol</th>
                          <th>Balance</th>
                        </tr>
                        </thead>
                        <tbody>';
                      $i=1;
                      
                      $stmt = $dbh->prepare("SELECT SUM(h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) AS total_vol
                          FROM cds_holding h 
                          JOIN client_account a ON h.cd_code = a.cd_code 
                          WHERE a.ID = :c_id
                          AND h.symbol_id = :s_id
                          AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) > 0");
                      $stmt->bindParam(':c_id', $cid_no);
                      $stmt->bindParam(':s_id', $state['symbol_id']);
                      $stmt->execute();
                      $get = $stmt->fetch();
                      $present_vol = isset($get['total_vol']) ? $get['total_vol'] : 0;

                  $wc= $dbh->prepare("WITH combined_data AS (
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
                          SELECT rir.cd_code, rir.renounce_cd_code, rir.symbol_id, s.symbol, rir.type, rir.bid_price, rir.allocated_size, rir.order_date, rir.order_size AS actl_vol
                          FROM rights_issue rir
                          JOIN client_account ttr ON rir.renounce_cd_code = ttr.cd_code
                          JOIN symbol s ON rir.symbol_id = s.symbol_id 
                          WHERE s.symbol_id = :sym_id
                          AND ttr.ID = :cid
                          AND rir.type = 'R'
                          AND rir.order_date BETWEEN :from_date AND :to_date
                          UNION ALL
                          SELECT rira.cd_code, rira.renounce_cd_code, rira.symbol_id, s.symbol, rira.type, rira.bid_price, rira.allocated_size, rira.order_date, rira.order_size AS actl_vol
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
                  $wc->bindParam(':cid',$cid_no);
                  $wc->bindParam(':from_date',$fromDate);
                  $wc->bindParam(':to_date',$toDate);
                  $wc->bindParam(':sym_id',$state['symbol_id']);
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
                    elseif ($det['type'] === 'TR'|| $det['type'] === 'ST') {
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
                  echo '
                    <tr style="font-size: 70%;">
                      <td colspan="5" align="right">Current Volume:</td>
                      <td>'.$present_vol.'</td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>';
              }
    echo '</section>    
        </div>
        </body>
      </html>';
}
else{}

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


