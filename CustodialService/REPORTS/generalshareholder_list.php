<?php 
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
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-yellow sidebar-mini">
  <div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
<!-- Site wrapper -->
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="../FILES/custodial_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Reports</a></li>      
      </ol>
    </section>
    <!-- Main content -->
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">General Shareholder List</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <br>
        <div class="col-xs-4">
        <?php
          $query = $dbh->prepare("SELECT s.name, s.symbol, s.security_type, s.symbol_id FROM symbol s WHERE s.symbol='GSL'");
          $query->execute();
          $res=$query->fetch();
          echo'<label>Security Symbol:</label> '.$res['symbol'].'<br>';
          echo'<label>NAME:</label> '.$res['name'].'';
        ?>
        </div>
        <br><br><br>
        <div class="box-footer">
          <?php
            $sql = $dbh->prepare("SELECT c.cd_code, a.title, a.f_name, a.l_name, a.tpn, a.ID, a.address, c.volume+c.pledge_volume+c.block_volume+c.pending_out_vol AS total 
              FROM custodial_cds c, custodial_account a, symbol s  
              WHERE c.cd_code=a.cd_code AND c.symbol_id=s.symbol_id AND s.security_type='CS' ORDER BY c.cd_code ASC");
            $sql->execute();
            echo'
            <div class="row">
              <div class="col-xs-12 table-responsive">
                <table id="example" class="table table-striped">
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                    <tr style="font-size: 14px;">
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
                  $i=1;
                  $sh=0;
                  foreach($sql as $state)
                  {
                    if($state['total'] > 0){
                    echo'
                      <tr style="font-size: 13px;">
                        <td>'.$i.'</td>
                        <td>'.$state['cd_code'].'</td>                         
                        <td>'.$state['title']." " .$state['f_name']. " ".$state['l_name'].'</td>
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
                  <!-- <tr>
                    <td>Total</td><td></td><td></td><td></td><td></td><td></td><td>'.number_format($sh,0,".",",").'</td>
                  </tr>-->
                  </tbody>
                </table>

            <div class="row no-print">
              <div class="col-xs-12">
                &emsp;&emsp;<a href="loadReportPrint.php?symbol='.$res['symbol'].'&generalShareholderList=generalShareholderList" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a> 
                &emsp;&emsp;<a href="generate_excel.php?symbol_id='.$res['symbol_id'].'&ge_export=ge_export" class="btn btn-success"><i class="fa fa-save"></i> Export</a>            
              </div>
            </div>'; 
          ?>
        </div>
      </div>  
    </section>
  </div>
<?php include('../NAV/footer.php') ?>

<script type="text/javascript">
  $(document).ready(function() {
    //$('#example').DataTable();
    $('#example').DataTable({
        "order": [[ 1, "asc" ]]
    });
   $('select[name=example_length]').append($("<option></option>").attr("value","-1").text("All"));
  });

  function showLoading() {
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
  }
  function hideloading() {
    document.getElementById('loadingmsg').style.display = 'none';
    document.getElementById('loadingover').style.display = 'none';
  }
</script>
</body>
</html>