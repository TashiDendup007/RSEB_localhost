<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include ('../../CONNECTIONS/db_config_website.php');

  $id = isset($_GET['ms']) ? $_GET['ms'] : 0;
  $message = isset($_GET['message']) ? $_GET['message'] : 0;
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
          <small>
            <!-- <small><a href="../REPORTS/load_bbo_report.php?download_withdrawal_list=download_withdrawal_list" class="btn btn-primary"><i class="fa fa-arrow-circle-down"></i> Generate Withdrawal List</a></small> -->
            <a href="../REPORTS/load_bbo_report.php?download_withdrawal_list_print=download_withdrawal_list_print" target="_blank" class="btn btn-primary"><i class="fa fa-arrow-circle-down"></i> Generate Withdrawal List</a>
          </small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">NRB</a></li>
        </ol>
      </section>
      <div class="col-lg-12" id="successMsg" style="display: none;">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Application has been approved successfully. 
        </div>
      </div>
      <div class="col-lg-12" id="rejectMsg" style="display: none;">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Application has been rejected successfully.
        </div>
      </div>
      <div class="col-sm-12 col-xs-12" id="errorMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Encountered with an error. Contact IT Division.
          <br> Message = <?php echo $message; ?>
        </div>
      </div>
      <div class="col-lg-12" id="infoMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> User Already Existed.
        </div>
      </div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <div class="box">
              <div class="box-body table-responsive">
                <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">CID</th>
                      <th scope="col">CD CODE</th>
                      <th scope="col">Name</th>
                      <th scope="col">Amount</th>
                      <th scope="col">Bank Account</th>
                      <th scope="col">Type</th>
                      <th class="text-center" scope="col">Status</th>
                      <th scope="col">Date</th>
                      <th scope="col">Trd Time</th>
                      <th scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $sql = $dbh->prepare("SELECT * FROM mcams_wallet ORDER BY wallet_id DESC");
                    $sql->execute();
                    $row = $sql->fetchAll(PDO::FETCH_ASSOC);

                    if ($sql->rowCount() > 0) {
                      
                      foreach ($row as $data){
                        $type = ($data['type'] == 'CR') ? '#82E0AA' : '#F5B7B1';
                       // $btn = ($data['paid_to_user'] == 'PROCESSING' || $data['paid_to_user'] == 'SELL') ? '<button type="button" class="btn btn-primary btn-sm" onclick="payment('.$data['wallet_id'].')"> PAY</button>' : '';
                        $btn = ($data['paid_to_user'] == 'PROCESSING' ) ? '<button type="button" class="btn btn-primary btn-sm" onclick="payment('.$data['wallet_id'].')"> PAY</button> 
                        <button type="button" class="btn btn-warning btn-sm" onclick="reverse('.$data['wallet_id'].')"><i class="fa fa-repeat" aria-hidden="true"></i> Reverse</button>' : '';

                        echo'
                        <tr data-id="'.$data['wallet_id'].'" style= "background-color:'.$type.'">
                          <td>'.$data['wallet_id'].'</td>
                          <td>'.$data['cid'].'</td>
                          <td id="cd_code'.$data['wallet_id'].'">'.$data['cd_code'].'</td>
                          <td>'.$data['name'].'</td>
                          <td id="amount'.$data['wallet_id'].'">'.$data['amount'].'</td>
                          <td>'.$data['bank_acc_number'].'</td>
                          <td>'.$data['type'].'</td>
                          <td id="status'.$data['wallet_id'].'">'.$data['paid_to_user'].'</td>
                          <td>'.$data['created_Date'].'</td>
                          <td>'.$data['trx_time'].'</td>
                          <td>'.$btn.'</td>
                        </tr>';
                      }
                    } else {
                      echo 'No Results';
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
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $('#listTableId').DataTable({
        "order": [[0, "desc"]]
    });
  });

  function payment(wallet_id) {
    var confirmPayment = confirm("Are you sure you want to process this payment?");
    if (confirmPayment) {
        showLoading();
        var op = 'process_nrb_wallet';
        var row = $("tr[data-id='" + wallet_id + "']");
        var status = $("#status" + wallet_id).text();
        var cd_code = $("#cd_code" + wallet_id).text();
        var amount = $("#amount" + wallet_id).text();

        $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: 'process_nrb_wallet=process_nrb_wallet' + op + '&wallet_id=' + wallet_id + '&status=' + status + '&cd_code=' + cd_code + '&amount=' + amount,
            success: function (data) {
                hideloading();

                if (data == 1) {
                    $.ajax({
                        type: "POST",
                        url: "../PROCESS/process.php",
                        data: "nrb_tr_content=" + wallet_id,
                        success: function (data1) {
                            // Replace the row content with updated data from the server
                            row.html(data1);
                        }
                    });
                }

            }
        });
    }
  }

  function reverse(wallet_id) {
    if (confirm("Are you sure you want to reverse the payment?")) {
        showLoading();
        var op = 'reverse__nrb__payment__process';
        var row = $("tr[data-id='" + wallet_id + "']");
        var status = $("#status" + wallet_id).text();
        var cd_code = $("#cd_code" + wallet_id).text();
        var amount = $("#amount" + wallet_id).text();
        $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: 'reverse__nrb__payment__process=' + op + '&wallet_id=' + wallet_id + '&status=' + status + '&cd_code=' + cd_code + '&amount=' + amount,
            dataType : 'json',
            success: function (data) {
                hideloading();

                if (data.status == 1) {
                  $(`tr[data-id="${wallet_id}"]`).fadeOut('slow');
                }

                alert(data.message);
            }
        });
    }
  }
</script>
</html>
