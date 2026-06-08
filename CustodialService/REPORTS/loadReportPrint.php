<?php
include ('../../CONNECTIONS/db.php');
session_start();

if(!empty($_GET["accountSummary"])) 
{
  $cid=$_GET['cid'];
  $wc= $dbh->prepare("SELECT c.*,a.name FROM custodial_account c, adm_institution a, custodial_cds h where (h.cd_code=:cid and a.institution_id=c.institution_id and c.cd_code=h.cd_code and (h.volume+h.pledge_volume+h.block_volume+h.pending_in_vol+h.pending_out_vol) !=0) OR ( c.ID=:cid and a.institution_id=c.institution_id and c.cd_code=h.cd_code and (h.volume+h.pledge_volume+h.block_volume+h.pending_in_vol+h.pending_out_vol) !=0) ");
  $wc->bindParam(':cid',$cid);
  $wc->execute();
  $state=$wc->fetch();
  date_default_timezone_set("Asia/Thimphu");
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
                  $save = $dbh->prepare('SELECT a.symbol_id,b.cd_code as cd,a.cd_code FROM custodial_cds a,custodial_account b where (a.cd_code=b.cd_code and a.cd_code=:cid) 
                    OR (a.cd_code=b.cd_code and b.ID=:cid) order by symbol_id ASC');
                  $save->bindParam(':cid', $cid);
                  $save->execute(); 
                  foreach($save as $states)
                  {                       
                    $save5 = $dbh->prepare('SELECT h.*,c.f_name,c.l_name, s.symbol,s.name 
                      from custodial_cds h,custodial_account c,symbol s 
                      where h.cd_code=c.cd_code and s.symbol_id=h.symbol_id -- and s.status=3 
                      and c.cd_code=:cd and h.symbol_id=:sid');
                    $save5->bindParam(':cd', $states['cd_code']);
                    $save5->bindParam(':sid', $states['symbol_id']);
                    $save5->execute();
                    $st=$save5->fetch();
                    $totvol=$st['volume']+$st['block_volume']+$st['pledge_volume']+$st['pending_out_vol']+$st['pending_in_vol'];
                    
                    if($totvol > 0){
                      $save1 = $dbh->prepare('SELECT h.*,c.f_name,c.l_name, s.symbol,s.name as sname 
                        from custodial_cds h,custodial_account c,symbol s 
                        where h.cd_code=c.cd_code and s.symbol_id=h.symbol_id -- and s.status=3 
                        and c.cd_code=:cd and h.symbol_id=:sid order by sname ASC');
                      $save1->bindParam(':cd', $states['cd_code']);
                      $save1->bindParam(':sid', $states['symbol_id']);
                      $save1->execute();
                      foreach($save1 as $state1)
                      {
                        $total=$state1['volume']+$state1['pledge_volume']+$state1['block_volume']+$state1['pending_out_vol']+$state1['pending_in_vol'];
                        if($state1['volume']==0){$v='-';}else{$v=number_format($state1['volume'],0,".",",");}
                        if($state1['block_volume']==0){$bv='-';}else{$bv=number_format($state1['block_volume'],0,".",",");}
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
                    else
                    {}
                  } 
                  echo'
                  </tbody>
                </table>
                <br><br><br>
                _________________________________________________________________________________
                &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;This is a computer generated report and required no signatory.
                _________________________________________________________________________________
                ';
        echo'
        </section>
      </div>
    </body>
  </html>';
}
else if(!empty($_GET["generalShareholderList"])) 
{
  $symbol=$_GET['symbol'];

  $wc= $dbh->prepare("SELECT * FROM symbol WHERE symbol=:symbol");
  $wc->bindParam(':symbol',$symbol);
  $wc->execute();
  $state=$wc->fetch();
  date_default_timezone_set("Asia/Thimphu");
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
          $wc= $dbh->prepare("SELECT c.cd_code,a.title,a.f_name,a.l_name,a.tpn,a.ID,a.address,c.volume+c.pledge_volume+c.block_volume+c.pending_out_vol AS total 
            FROM custodial_cds c, custodial_account a WHERE c.cd_code=a.cd_code AND symbol_id=:sid ORDER BY c.cd_code ASC");
          $wc->bindParam(':sid',$state['symbol_id']);
          $wc->execute();    
          echo'
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
                  $i=1;
                  $sh=0;
                  foreach($wc as $state)
                  {
                    if($state['total'] > 0){
                      echo'
                      <tr style="font-size: 70%;">
                        <td>'.$i.'</td>
                        <td>'.$state['cd_code'].'</td>                         
                        <td>'.$state['title']." ".$state['f_name']. " ".$state['l_name'].'</td>
                        <td>'.$state['tpn'].'</td>
                        <td>'.$state['ID'].'</td>
                        <td>'.$state['address'].'</td>
                        <td style="text-align:right;">'.number_format($state['total'],0,".",",").'</td>
                      </tr>';
                      $totalShares=$state['total'];
                      $sh=$totalShares+$sh;
                      $i=$i+1;
                    }
                    else{}
                  }
                  echo'
                  <tr>
                    <td>Total</td><td></td><td></td><td></td><td></td><td></td><td>'.number_format($sh,0,".",",").'</td>
                  </tr>
            </tbody>
          </table>
        </section>    
      </div>
    </body>
  </html>';
}
else{
  echo "No Method Matching";
  exit();
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
