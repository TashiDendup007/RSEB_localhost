<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
$username=$_SESSION['sess_username'];
$cdcode=find_link_user_cd_code($username);
$list= ins_id($username);
$ins_id=$list[0];$p_code=$list[1];
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php') ?>

  
<style>
  /* Custom CSS to make the table more compact */
  #LoadData th,
  #LoadData td {
    white-space: nowrap;
    max-width: 0;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  /* Optional: Add custom styling for the table rows */
  #LoadData tbody tr {
    font-size: 14px; /* Adjust font size as needed */
  }
</style>
  
</head>
<body class="hold-transition skin-black sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
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
          <li><a href="#">wallet</a></li>
        </ol>
      </section>
      <!-- Main content -->
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php') ?>
        <div class="box">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <div class="box-header">
                <h3 class="box-title">Wallet details</h3>
              </div>
              <div class="box-body table-responsive" >

              <table id="LoadData" class="table table-sm">
             
              <?php

                  $ch = curl_init();
                  // Where you want to post data
                  $url1 = "http://localhost/RSEB2020/api2/indivclentholding.php";
                  // Define the POST data
                  $data1 = array(
                      'WalletTrxHistory' => 'WalletTrxHistory',
                      'cd_code' => $cdcode
                  );
                  // Set cURL options for the second request
                  curl_setopt($ch, CURLOPT_URL, $url1);
                  curl_setopt($ch, CURLOPT_POST, true);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data1));
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  // Execute the second request
                  $output = curl_exec($ch);
                  $values = json_decode($output, true);
                  // Close cURL handle
                  curl_close($ch);
                  // Output the HTML
                  echo 
                      '<thead>
                        <tr>
                          <th>Trx Time</th>
                          <th>Amount</th>
                          <th>Type</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>';
                      $i=1;
                      $crTotal = 0;
                      $drTotal = 0;
                      foreach($values as $key){

                        if($key['type']=='CR'){
                          $color = '#ABEBC6';
                          $crTotal += $key['amount'];
                          
                        }else{
                          $color = '#F5B7B1';
                          $drTotal += $key['amount'];
                          $status  = $key['paid_to_user'];
                        }

                        if($key['paid_to_user'] == 'PROCESSING'){
                          $status = '<i class="fa fa-clock-o" aria-hidden="true" style="font-size:20px"></i> Pending';
                        }else{
                          $status = '<i class="fa fa-check-circle-o green" aria-hidden="true" style="font-size:20px"></i> Processed';
                        }
                        
                        echo'<tr style="background-color: '.$color.'">
                        <td>'.$key['trx_time'].'</td>
                        <td>'.$key['amount'].'</td>
                        <td>'.$key['type'].'</td>
                        <td>'. $status.'</td>
                        </tr>';
                      }
                    echo'</tbody>';
              ?>
               </table>
               <?php  echo "Total Debit: Nu. ". number_format($drTotal,2). '. Total Credit: Nu. ' . number_format($crTotal,2);?>
                                
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
  <script type="text/javascript">

  $("#LoadData").DataTable({
    order: [[0, 'desc']],
    });
  </script>
</html>
