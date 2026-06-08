<?php 
    session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="3"){
      header('Location: ../../access.php?err=2');
    }
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) {
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive){ 
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
<body class="hold-transition skin-green sidebar-mini">
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
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Bulk Clearing</a></li> 
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="box">
        <div class="box-body">
          <div class="box-body">
              <div class="row">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                      <td><b>CD CODE</b></td>
                      <td><b>SYMBOL</b></td> 
                      <td><b>VOL</b></td>
                      <td><b>TRADE DATE</b></td>
                      <td><b>PRICE</b></td>
                      <td><b>AMOUNT</b></td>
                     </tr>
                  </thead>   
                  <tbody >
<?php
                    $executed_orders= $dbh->prepare('SELECT a.*,b.symbol FROM executed_orders a, b.symbol WHERE  a.symbol_id=b.symbol_id and a.status=0');
                    $executed_orders->execute();
                      while($result=$executed_orders->fetch(PDO::FETCH_ASSOC)){
                          $amt=$result['lot_size_execute']*$result['order_exe_price'];
                          echo '<tr>'; 
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td> '.$result['symbol'].'</td>';
                          echo '<td> '.$result['lot_size_execute'].'</td>';
                          echo '<td> '.$result['order_date'].'</td>';
                          echo '<td> '.number_format($result['order_exe_price'],2,".",",").'/Kg</td>';
                          echo '<td> '.number_format($amt,2,".",",").'</td>
                                </tr>';
                            }        
?>
                  </tbody>
                </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
 </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>  
</body>
</html>
