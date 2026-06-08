<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
// include('../../Functions/f.php');

// $username=$_SESSION['sess_username'];
$cid = substr($username, -11);

/*$cdcode=find_link_user_cd_code($username);
$list= ins_id($username);
$ins_id=$list[0];
$p_code=$list[1];*/
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php') ?>

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
          <li><a href="te-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Balance</a></li>
        </ol>
      </section>
      <!-- Main content -->
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php') ?>
        <div class="box">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <div class="box-header">
                <h3 class="box-title">Balance 
                  <br/>
                  <span style="font-size: 12px;">
                  (NOTE: The Balance Volume under CD code which are not under your current Broker needs to be transfered first to be sold.)
                  </span>
                </h3>
              </div>
              <div class="box-body table-responsive">
            <?php
            // Set the time zone and currency
            date_default_timezone_set("Asia/Thimphu");
            $currency = 'BTN';
            $rate = 1;

            // Get the shareholding details
            $stmt = $dbh->prepare("SELECT c.*, a.name, h.volume, h.block_volume, h.pledge_volume, h.pending_in_vol, h.pending_out_vol,
                       s.symbol, s.name AS symbol_name, mp.market_price
                FROM client_account c
                JOIN adm_institution a ON a.institution_id = c.institution_id
                JOIN cds_holding h ON h.cd_code = c.cd_code
                JOIN market_price mp ON mp.symbol_id = h.symbol_id
                JOIN symbol s ON s.symbol_id = h.symbol_id
                WHERE (h.cd_code = :cid OR c.ID = :cid)
                    AND (h.volume + h.pledge_volume + h.block_volume + h.pending_in_vol + h.pending_out_vol) != 0
                    AND s.status = 1
                ORDER BY s.symbol_id ASC
                LIMIT 100 OFFSET 0");
            $stmt->bindParam(':cid', $cid);
            $stmt->execute();
            $state = $stmt->fetchALL();
            // Output the shareholding details
            echo '
                <section class="invoice">
                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <img src="../../img/logo.png" alt="Logo">
                        </div>
                        <div class="col-lg-8 col-md-10 col-sm-10">
                            <h3 class="text-center"><strong>༄༄།།  རྒྱལ་གཞུང་འགན་ལེན་བདོག་གཏད་བརྗེ་སོར་ཁང་།</strong></h3>
                            <h3 class="text-center"><strong>ROYAL SECURITIES EXCHANGE OF BHUTAN</strong></h3>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                        </div>
                                    </div>
                                    <!--
                                    <div class="row">
                                      <div class="col-lg-12 col-md-12 col-sm-12">
                                         <div class="lead" style="font-size: 100%;">
                                              <div class="col-lg-2 col-md-12 col-sm-12"></div>
                                              <div class="col-lg-8 col-md-12 col-sm-12"></div>
                                              <div class="col-lg-2 col-md-12 col-sm-12"><b>Date : '.date('d-m-Y').'</b></div>
                                         </div>
                                      </div>
                                         </br>
                                         
                                         <div class="col-lg-12 col-md-12 col-sm-12">
                                              <center>
                                                <div class="lead" style="font-size: 100%;">
                                                  <b>TO WHOM IT MAY CONCERN</b>
                                                </div>
                                              </center>
                                          </div>
                                          <div class="lead" style="font-size: 100%;">
                                            The Royal Securities Exchange of Bhutan would like to provide the shareholding details of Mr./Mrs./Miss. <b>'.(isset($state[0]['f_name']) ? $state[0]['f_name'] : '').' '.(isset($state[0]['l_name']) ? $state[0]['l_name'] : '').'</b> bearing CID/DISN # <b>'.$cid.'</b> as follows: </br>
                                          </div>
                                    </div>
                                    -->';
                                    echo'
                                    <div class="row">
                                      <div class="table-responsive-lg">
                                        <table class="table  table-striped">
                                          <thead style="background-color: #D6EAF8; font-size: 80%;">
                                          <tr>
                                            <th>#</th>
                                            <th>Symbol</th>
                                            
                                            <th style="text-align:right;">Pledged</th>
                                            <th style="text-align:right;">PIV</th>
                                            <th style="text-align:right;">POV</th>
                                            <th style="text-align:right;">Volume</th>
                                            <th style="text-align:right;">Price</th>
                                            <th style="text-align:right;">Amount</th>';
                                            if($currency != 'BTN'){
                                              echo'<th style="text-align:right;">Total Amount ('.$currency.')</th>';
                                            }

                                          echo'</tr>
                                          </thead>
                                          <tbody>';

                                          $i = 1;
                                          $totalNu = 0;
                                          $totaldollars = 0;
                                              foreach ($state as $row) {
                                                  $total = $row['volume'] + $row['pledge_volume'] + $row['block_volume'] + $row['pending_out_vol'] + $row['pending_in_vol'];
                                                  $formatted_total = number_format($total, 2, '.', ',');
                                                  $formatted_market_price = number_format($row['market_price'], 2, '.', ',');
                                                  $totalNu += $total * $row['market_price'];
                                                  $totaldollars += $total * $row['market_price'] / $rate;
                                                  $v = $row['volume'] == 0 ? '-' : number_format($row['volume'], 0, '.', ',');
                                                  $bv = $row['block_volume'] == 0 ? '-' : number_format($row['block_volume'], 0, '.', ',');
                                                  $pv = $row['pledge_volume'] == 0 ? '-' : number_format($row['pledge_volume'], 0, '.', ',');
                                                  $piv = $row['pending_in_vol'] == 0 ? '-' : number_format($row['pending_in_vol'], 0, '.', ',');
                                                  $pov = $row['pending_out_vol'] == 0 ? '-' : number_format($row['pending_out_vol'], 0, '.', ',');
                                                  echo '
                                                  <tr style="font-size: 70%;">
                                                      <td>' . $i . '</td>
                                                      <td>' . $row['cd_code'] . '-' . $row['symbol'] . '</td>
                                                      
                                                      <td style="text-align:right;">' . $row['pledge_volume'] . '</td>
                                                      <td style="text-align:right;">' . $row['pending_in_vol'] . '</td>
                                                      <td style="text-align:right;">' . $row['pending_out_vol'] . '</td>
                                                      <td style="text-align:right;">' . $formatted_total . '</td>
                                                      <td style="text-align:right;">' . $formatted_market_price . '</td>
                                                      <td style="text-align:right;">' . number_format($total * $row['market_price'], 2, '.', ',') . '</td>';
                                                      if($currency != 'BTN'){
                                                          echo'<td style="text-align:right;">' . number_format($total * $row['market_price'] / $rate, 2, '.', ',') . '</td>';
                                                      }
                                                  echo '</tr>';
                                                  $i++;
                                        }
                                    echo '<tr style="font-size: 70%;">
                                            <td></td>
                                            <td><b>Total<b><i>(The information is as on date : '.date('d-m-Y').')</i></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td style="text-align:right;"></td>
                                            <td style="text-align:right;"><b>'.number_format($totaldollars,2,".",",").'</b></td>
                                          </tr></tbody>
                                            </table> <div class="row" style="padding-left: 50px; padding-right:50px;">
                                    </br></br>
                                    </br>
                                    <b>Central Depository </b>
                                    </br>
                                    </br>
                                    </br>

                                    </div>
                                    </section>
                                <div class="row no-print">
                                <div class="col-xs-12">
                                  &emsp;&emsp;<a href="printbalance.php?cid='.$cid.'&BalanceConfirmation=BalanceConfirmation&rate='.$rate.'&currency='.$currency.'" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
                                </div>
                                </div>';
                      ?>
              </div>
             </div>
            </div>
          </div>
          <!-- /.box -->
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
      <?php include('../NAV/footer.php') ?>
    </div>
  </body>
</html>
