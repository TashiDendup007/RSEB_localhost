<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  $id = isset($_GET['id']) ? $_GET['id'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
  <?php include('../../CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Share Transfer</a></li>
        </ol>
      </section>
      
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body">
                <div class="table-responsive">
                  <?php 
                    echo'
                    <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">From Account</th>
                          <th scope="col">To Account</th>
                          <th scope="col">Symbol</th>
                          <th scope="col"> Volume</th>
                          <th class="text-center" scope="col">Status</th>
                          <th scope="col">Username</th>
                          <th scope="col">Date</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>';
                      $i = 1;
                      $sql = $dbh->prepare("
                          SELECT c.id, c.from_account, c.to_account, c.symbol_id, c.vol_transfer, c.remarks, c.`status`, c.user_name, c.`type`, c.created_at, s.symbol,
                          CONCAT_WS(' ', a.f_name, a.l_name) AS from_fl_name, a.ID AS from_id,
                          CONCAT_WS(' ', b.f_name, b.l_name) AS to_fl_name, b.ID AS to_id
                          FROM application_cds_transfers c 
                          LEFT JOIN symbol s ON c.symbol_id = s.symbol_id
                          LEFT JOIN client_account a ON c.from_account = a.cd_code 
                          LEFT JOIN client_account b ON c.to_account = b.cd_code
                          WHERE c.`status` = 'SUBMITTED'
                      ");
                      $sql->execute();
                      $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
                      foreach($rows as $res) {
                        $modalId = "modal_" . $res['id'];
                        echo'
                        <tr>
                          <td>'.$i.'</td>
                          <td>'.$res['from_account'].'</td>
                          <td>'.$res['to_account'].'</td>
                          <td>'.$res['symbol'].'</td>
                          <td>'.$res['vol_transfer'].'</td>
                          <td class="text-center btn-primary">'.$res['status'].'</td>
                          <td>'.$res['user_name'].'</td>
                          <td>'.$res['created_at'].'</td>
                          <td>
                          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#' . $modalId . '">
                                <i class="fa fa-eye"></i> View
                            </button>
                          </td>
                        </tr>
                        
                        <!-- Modal -->
                        <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="modalLabel_' . $res['id'] . '" aria-hidden="true">
                          <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h3 class="modal-title" id="modalLabel_' . $res['id'] . '">Share Transfer Details</h3>
                              </div>
                              <div class="modal-body">

                                <div class="row">
                                  <div class="col-lg-6">
                                    <label>From Account</label>
                                    <input type="text" name="" id="from_acc" class="form-control" value="' . $res['from_account'] . '" readonly>
                                  </div>

                                  <div class="col-lg-6">
                                    <label>Transferer Detail</label>
                                    <input type="text" name="" class="form-control" value="' . $res['from_fl_name'] . ' (' . $res['from_id'] . ')" readonly>
                                  </div>

                                  <div class="col-lg-6">
                                    <label>To Account</label>
                                    <input type="text" name="to_acc" id="to_acc" class="form-control" value="' . $res['to_account'] . '" readonly>
                                  </div>

                                  <div class="col-lg-6">
                                    <label>Receiver Detail</label>
                                    <input type="text" name="" class="form-control" value="' . $res['to_fl_name'] . ' (' . $res['to_id'] . ')" readonly>
                                  </div>

                                  <div class="col-lg-6">
                                    <label>Symbol</label>
                                    <input type="hidden" name="symbol_id" class="form-control" value="' . $res['symbol_id'] . '" readonly>
                                    <input type="text" name="" class="form-control" value="' . $res['symbol'] . '" readonly>
                                  </div>

                                  <div class="col-lg-6">
                                    <label>Volume</label>
                                    <input type="text" name="tr_vol" class="form-control" value="' . $res['vol_transfer'] . '" readonly>
                                  </div>

                                  <div class="col-lg-12">
                                    <label>Remarks</label>
                                    <input type="text" name="tr_remarks" class="form-control" value="' . $res['remarks'] . '" readonly>
                                  </div>
                                </div>

                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-lg" data-dismiss="modal" onClick="approve_st('. $res['id'] .')">Update</button>
                                <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">Close</button>
                              </div>
                            </div>
                          </div>
                        </div>';
                        $i++;
                      }
                      echo'
                    </tbody>
                  </table>';
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
<script type="text/javascript">
  $(document).ready(function() {
      $('#listTableId').DataTable();
  });

  function approve_st(id) {
    showLoading();
    if (confirm("Do you want to continue?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: { id : id, share__transfer__approval : id },
        dataType: "html",
        success: function(response) {
          hideloading();

          $("#modal_" + id).modal('hide');
          
          $("#message").html(response);
          showMessage();

          setTimeout( function() {
              location.reload();
          }, 3000);
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
  
</script>
</html>
