<?php 
    include('sessionStartFile_cdscss.php');
    include ('../../CONNECTIONS/db.php');
    include('../../Functions/f.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
  <style>
      .status-icon i {
          font-size: 20px;
      }

      .status-icon i.success {
          color: #00b900;
      }

      .status-icon i.error {
          color: #d10000;
      }
  </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Single Clearing</a></li>
        </ol>
      </section>

      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bond Clearing Details [<span style="font-size: 14px; color: red;">Click on the date to expand or collapse the list</span>], <i class="fa fa-times" style="color: #d10000;"></i>  Unsettled <i class="fa fa-check" style="color: #00b900;"></i> Settled</h4>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                  <?php 
                    $stmt = $dbh->prepare("SELECT DATE(e.order_date) AS order_date FROM bond_executed_orders e GROUP BY DATE(e.order_date) ORDER BY e.id DESC LIMIT 15");
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $i = 1;
                    $exe_order_date = '';
                    foreach ($results as $key => $value) {
                      $exe_order_date = $value['order_date'];
                      echo'
                      <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="heading_'.$i.'">
                          <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_id_'.$i.'" aria-expanded="true" aria-controls="collapse_id_'.$i.'">
                              Trade Date <i class="fa fa-angle-double-right"></i> <strong>'.$exe_order_date.'</strong>';

                              $check_status = $dbh->prepare("SELECT COUNT(*) AS present FROM bond_executed_orders a WHERE a.status != 1 AND DATE(a.order_date) = ?");
                              $check_status->execute([$exe_order_date]);
                              $result_exe_ord = $check_status->fetchColumn();
                              echo'<span class="status-icon" style="float: right;">';
                              if ($result_exe_ord) {
                                echo'<i class="fa fa-times error"></i>';
                              } else {
                                echo'<i class="fa fa-check success"></i>';
                              }
                              $check_status->closeCursor();
                              unset($check_status);

                              echo'
                              </span>
                            </a>
                          </h4>
                        </div>
                        <div id="collapse_id_'.$i.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_'.$i.'">
                          <div class="panel-body">';
                            $sql = $dbh->prepare("SELECT b.symbol, SUM(a.lot_size_execute) AS total_lot_exe , SUM(a.order_exe_price * a.lot_size_execute) AS total_value 
                                    FROM bond_executed_orders a
                                    INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
                                    WHERE a.side = 'B' AND DATE(a.order_date) = :order_date 
                                    GROUP BY a.symbol_id 
                                    ORDER BY a.symbol_id ASC 
                            ");
                            $sql->bindValue(':order_date', $exe_order_date);
                            $sql->execute();
                            $orders = $sql->fetchAll(PDO::FETCH_ASSOC);

                            $z = 1;
                            $tot_exe_vol = 0;
                            $tota_value = 0;

                            $html_output = '
                            <p><strong>Summary of Traded Volume</strong></p>
                            <div class="table-responsive">
                              <table class="table table-bordered table-striped" width="100%">
                                  <tbody>';
                                  foreach ($orders as $exe) {
                                      $tot_exe_vol += $exe['total_lot_exe'];
                                      $tota_value += $exe['total_value'];
                                      $html_output .= '
                                      <tr>
                                          <td class="text-center">' . $z . '</td>
                                          <td class="text-center">' . $exe['symbol'] . '</td>
                                          <td class="text-right">Volume Traded</td>
                                          <td>' . number_format($exe['total_lot_exe']) . '</td>
                                          <td class="text-right">Value</td>
                                          <td>' . number_format($exe['total_value'], 2) . '</td>
                                      </tr>';
                                      $z++;
                                  }

                                  $html_output .= '
                                  <tr>
                                      <td colspan="3" class="text-right"><strong>Total Vol Traded >></strong></td>
                                      <td><strong>' . number_format($tot_exe_vol) . '</strong></td>
                                      <td class="text-right"><strong>Total Value >></strong></td>
                                      <td><strong>' . number_format($tota_value, 2) . '</strong></td>
                                  </tr>
                                  </tbody>
                              </table>
                            </div>';
                            
                            echo $html_output;

                            echo'
                            <div class="col-lg-12">
                              <div class="col-lg-6 col-md-6 text-left">';
                              if ($result_exe_ord) {
                                echo'<button type="button" class="btn btn-primary" onclick="settlement_process(\'' . $exe_order_date . '\');"><i class="fa fa-tasks" aria-hidden="true"></i> Process Settlement <strong>'.$exe_order_date.'</strong></button>';
                              }
                              echo'
                              </div>

                              <div class="col-lg-6 col-md-6 text-right">
                                <button type="button" class="btn btn-success" onclick="fetch_trade_details(\'' . $exe_order_date . '\')"><i class="fa fa-spinner" aria-hidden="true"></i> <strong>Load Trade Details</strong></button>
                              </div>

                            </div>

                          </div>
                        </div>
                      </div>';
                      $i++;
                    }
                  ?>
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
  function settlement_process(exe_date) {
    showLoading();
    var op = 'bond_trade_settlement';
    if (confirm("Do you want to continue processing the settlement ?")) {
      $.ajax({
          type: "POST",
          url: "rseb-sett-script.php",
          data: {
              bond_trade_settlement: op,
              date: exe_date
          },
          success: function(data) {
              hideloading();
              $("#message").html(data);
              showMessage();

              setTimeout(function() {
                  location.reload();
              }, 1000);
          },
          error: function(jqXHR, textStatus, errorThrown) {
              hideloading();
              alert("Error: " + textStatus + " - " + errorThrown);
          }
      });
    } else {
        hideloading();
        return false;
    }
  }

  function fetch_trade_details(exe_date) {
    showLoading();
    var op = 'bond__trade__details';
    $.ajax({
        type: "POST",
        url: "rseb-sett-script.php",
        data: {
            bond__trade__details: op,
            exe__date: exe_date
        },
        success: function(data) {
            hideloading();
            $("#myModal").modal('show').html(data);
        }
    });
  }
</script>
</html>
