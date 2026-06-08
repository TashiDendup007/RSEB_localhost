<?php
date_default_timezone_set("Asia/Thimphu");
session_start();
$role = $_SESSION['sess_userrole'];
if( $role!="7")
{
  header('Location: ../../access.php?err=2');
}
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
  { 
    header("Location: ../../Authentication/Logout.php"); 
  }
}
$_SESSION['timeout'] = time();
include ('../../CONNECTIONS/db.php');
include ('../../functions/function-sanitize.php');
require('../../fpdf/fpdf.php');

if(isset($_POST["indv"])) 
{
  $cid=$_POST['cid'];
  $wc= $dbh->prepare("SELECT c.*,a.name 
    FROM custodial_account c, adm_institution a, custodial_cds h 
    WHERE (h.cd_code=:cid AND a.institution_id=c.institution_id AND c.cd_code=h.cd_code 
    AND (h.volume+h.pledge_volume+h.block_volume+h.pending_in_vol+h.pending_out_vol) !=0) 
    OR ( c.ID=:cid AND a.institution_id=c.institution_id AND c.cd_code=h.cd_code 
    AND (h.volume+h.pledge_volume+h.block_volume+h.pending_in_vol+h.pending_out_vol) !=0) ");
  $wc->bindParam(':cid',$cid);
  $wc->bindParam(':cid',$cid);
  $wc->execute();
  $state=$wc->fetch();
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
             <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Account Summary Details</div> 
             <div class="lead" style="font-size: 40%; margin-top:-25px;">
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
            $i=1;
            $save = $dbh->prepare('SELECT a.symbol_id, b.cd_code as cd, a.cd_code FROM custodial_cds a, custodial_account b 
              WHERE (a.cd_code=b.cd_code AND a.cd_code=:cid) OR (a.cd_code=b.cd_code AND b.ID=:cid) ORDER BY symbol_id ASC');
            $save->bindParam(':cid', $cid);
            $save->bindParam(':cid', $cid);
            $save->execute(); 
            foreach($save as $states)
            {
              $save5 = $dbh->prepare('SELECT h.*, c.f_name, c.l_name, s.symbol, s.name 
                FROM custodial_cds h, custodial_account c, symbol s 
                WHERE h.cd_code=c.cd_code AND s.symbol_id=h.symbol_id -- AND s.status=3 
                AND c.cd_code=:cd AND h.symbol_id=:sid');
              $save5->bindParam(':cd', $states['cd_code']);
              $save5->bindParam(':sid', $states['symbol_id']);
              $save5->execute();
              $st=$save5->fetch();
              $totvol=$st['volume']+$st['block_volume']+$st['pledge_volume']+$st['pending_out_vol']+$st['pending_in_vol'];
              if($totvol > 0)
              {
                $save1 = $dbh->prepare('SELECT h.*,c.f_name,c.l_name, s.symbol,s.name as sname 
                  from custodial_cds h,custodial_account c,symbol s 
                  where h.cd_code=c.cd_code and s.symbol_id=h.symbol_id -- AND s.status=3 
                  and c.cd_code=:cd and h.symbol_id=:sid');
                $save1->bindParam(':cd', $states['cd_code']);
                $save1->bindParam(':sid', $states['symbol_id']);
                $save1->execute();
                foreach($save1 as $state1)
                {
                  $total=$state1['volume']+$state1['pledge_volume']+$state1['block_volume']+$state1['pending_out_vol']+$state1['pending_in_vol'];
                  if($state1['volume']==0){$v='-';}else{$v=number_format($state1['volume'],0,".",",");}
                  if($state1['block_volume']==0){$bv='-';}else{$v=number_format($state1['block_volume'],0,".",",");}
                  if($state1['pledge_volume']==0){$pv='-';}else{$pv=number_format($state1['pledge_volume'],0,".",",");}
                  if($state1['pending_in_vol']==0){$piv='-';}else{$piv=number_format($state1['pending_in_vol'],0,".",",");}
                  if($state1['pending_out_vol']==0){$pov='-';}else{$pov=number_format($state1['pending_out_vol'],0,".",",");}                     
                  echo'
                  <tr style="font-size: 70%;">
                    <td>'.$i.'</td>
                    <td>'.$states['cd'].'-'.$state1['symbol'].'</td>
                    <td style="text-align:right;">'.$v.'</td>
                    <td style="text-align:right;">'.$bv.'</td>
                    <td style="text-align:right;">'.$pv.'</td>
                    <td style="text-align:right;">'.$piv.'</td>
                    <td style="text-align:right;">'.$pov.'</td>
                    <td style="text-align:right;">'.number_format($total,0,".",",").'</td>
                  </tr>';
                  $i=$i+1;
                }
              }
            }
            echo'
            </tbody>
          </table>
        </section>
      <div class="row no-print">
        <div class="col-xs-12">
          &emsp;&emsp;<a href="loadReportPrint.php?cid='.$cid.'&accountSummary=accountSummary" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
        </div>
      </div>';
      exit();
}
else{
  echo "No Matching Method";
  exit();
}
?>
