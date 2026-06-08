<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include ('../../CONNECTIONS/db_config_website.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small>Dashboard</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">NRB</a></li>
        </ol>
      </section>
      
      <section class="content">
        <div class="row">
          <div class="col-lg-12 col-md-12">
              <!-- START -->

          <!-- TABLE: LATEST ORDERS -->
             <div class="col-md-12 col-lg-12 col-sm-12">
              <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Today's Executed Orders</h3>

                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table class="table no-margin">
                      <thead>
                      <tr>
                        <th>Name</th>
                        <th>CD Code</th>
                        <th>SYM</th>
                        <th>Side</th>
                        <th>Volume</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Commission</th>
                        <th>Trx Time</th>
                        <th>Trade Confirmation</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php 
                        $fromDate = date('Y-m-d').' 00:00:00';
                        $toDate = date('Y-m-d').' 23:59:59';

                        $executed_orders= $dbh->prepare('SELECT o.occupation_name,c.tpn,c.f_name, c.l_name,co.rate as comrate, c.ID,e.member_broker,e.cd_code,e.side,e.order_exe_price,e.lot_size_execute,e.lot_size_execute * e.order_exe_price as amount, s.symbol, e.order_date, e.sub_user, c.title , s.symbol
                            FROM executed_orders e, symbol s,client_account c, occupation o, bbo_commission co where e.symbol_id=s.symbol_id
                            AND order_date >= :fdate  AND order_date <= :tdate  
                            AND substr(e.member_broker,1,7)=:un 
                            AND c.cd_code=e.cd_code AND o.occupation=c.occupation AND co.bro_comm_id = c.bro_comm_id order by e.member_broker');
                        $executed_orders->bindParam(':fdate',$fromDate);
                        $executed_orders->bindParam(':tdate',$toDate);
                        $executed_orders->bindParam(':un',$username);
                        $executed_orders->execute();
                        foreach($executed_orders as $row){
                          $comm= $row['comrate']*$row['lot_size_execute']*$row['order_exe_price']/100;
                          if($row['side']=='B'){
                            $col = 'bg-red';
                          } else {
                            $col = 'bg-aqua';
                          }
                          echo '
                          <tr class="'.$col.'">
                            <td>'.$row['f_name'].$row['l_name'].'</td>
                            <td>'.$row['cd_code'].'</td>
                            <td>'.$row['symbol'].'</td>
                            <td>'.$row['side'].'</td>
                            <td>'.$row['lot_size_execute'].'</td>
                            <td>'.$row['order_exe_price'].'</td>
                            <td>'.$row['amount'].'</td>
                            <td>'.$comm.'</td>
                            <td>'.$row['order_date'].'</td>
                            <td><button class="btn btn-success"><i class="fa fa-fw fa-send-o"></i> SEND </button></td>
                          </tr>';
                        }
                      ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- <div class="box-footer clearfix">
                  <a href="javascript:void(0)" class="btn btn-sm btn-info btn-flat pull-left">Place New Order</a>
                  <a href="javascript:void(0)" class="btn btn-sm btn-default btn-flat pull-right">View All Orders</a>
                </div> -->
              </div>
            </div>

            <div class="col-md-6 col-lg-6 col-sm-12">
              <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Current Wallet Balance</h3>
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                      <thead>
                      <tr>
                        <th>Client</th>
                        <th>CD</th>
                        <th>Balance</th>
                      </tr>
                      </thead>
                      <tbody>
                        <?php
                          $mcams_wallet= $dbh->prepare('SELECT sum(m.amount) as total, m.cd_code, ca.f_name FROM mcams_wallet m, client_account ca WHERE ca.cd_code=m.cd_code GROUP BY m.cd_code');
                          $mcams_wallet->execute();
                          foreach($mcams_wallet as $row){
                          echo '
                          <tr>
                            <td><a href="">'.$row['f_name'].'</a></td>
                            <td>'.$row['cd_code'].'</td>
                            <td>'.$row['total'].'</td>
                          </tr>';
                          }
                        ?>
                      
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-lg-6 col-sm-12">
              <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Trade Confirmation</h3>
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                      <thead>
                      <tr>
                        <th>Client</th>
                        <th>CD</th>
                        <th>Balance</th>
                      </tr>
                      </thead>
                      <tbody>
                        <?php
                          $mcams_wallet = $dbh->prepare('SELECT DISTINCT(e.cd_code) , ca.f_name
                            FROM executed_orders e, client_account ca 
                            WHERE DATE(e.order_date) = CURDATE() and ca.cd_code=e.cd_code
                          ');
                          $mcams_wallet->execute();
                          foreach($mcams_wallet as $row){
                            echo '
                            <tr>
                              <td>'.$row['f_name'].'</td>
                              <td>'.$row['cd_code'].'</td>
                              <td>Status</td>
                            </tr>';
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

              </div>
            </div>
          </div>
        </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
 $( function () {
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
</html>
