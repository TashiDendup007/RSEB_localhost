<?php  
  include ('../FILES/session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');

  if (isset($_POST['trade_confirmation']) && $_POST['trade_confirmation'] === 'trade_confirmation') {
    $cd_code  = trim($_POST['cdcode']);
    $fromDate = $_POST['fromDate1'] . ' 00:00:00';
    $toDate   = $_POST['toDate1'] . ' 23:59:59';
    $mem_code = substr($username, 0, 7);

    /*
    |--------------------------------------------------------------------------
    | Client Information
    |--------------------------------------------------------------------------
    */
    $clientSql = "
        SELECT ca.cd_code, ca.f_name, ca.l_name, ca.phone, ca.bank_account, ca.ID, ca.email, b.bank_short_name, ai.gst_register
        FROM client_account ca
        LEFT JOIN banks b ON b.bank_id = ca.bank_id
        LEFT JOIN adm_institution ai ON ai.institution_id = ca.institution_id
        WHERE ca.cd_code = :cd_code
        AND LEFT(ca.user_name, 7) = :mem_code
        LIMIT 1
    ";
    $stmt = $dbh->prepare($clientSql);
    $stmt->execute([':cd_code' => $cd_code, ':mem_code' => $mem_code]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        exit('<p style="color:red;">Invalid Client CD Code</p>');
    }

    /*
    |--------------------------------------------------------------------------
    | Trade Data
    |--------------------------------------------------------------------------
    */
    $tradeSql = "
        SELECT beo.order_date, beo.side, beo.lot_size_execute, beo.order_exe_price, s.symbol, (beo.lot_size_execute * beo.order_exe_price) AS trade_value
        FROM bond_executed_orders beo
        INNER JOIN symbol s ON s.symbol_id = beo.symbol_id
        WHERE beo.cd_code = :cd_code
        AND beo.order_date >= :from_date
        AND beo.order_date <= :to_date
        ORDER BY beo.order_date ASC
    ";
    $stmt = $dbh->prepare($tradeSql);
    $stmt->execute([':cd_code'   => $cd_code, ':from_date' => $fromDate, ':to_date'   => $toDate]);

    /*
    |--------------------------------------------------------------------------
    | HTML Header
    |--------------------------------------------------------------------------
    */
    ?>
    <div class="col-lg-12">
        <div class="table-responsive">
            <h4>Trade Confirmation</h4>
            <p>From: <?= htmlspecialchars($fromDate) ?> - To: <?= htmlspecialchars($toDate) ?></p>
            <p>
                CD CODE: <?= htmlspecialchars($client['cd_code']) ?>,
                Name: <?= htmlspecialchars($client['f_name'] . ' ' . $client['l_name']) ?>,
                CID/DISN: <?= htmlspecialchars($client['ID']) ?>,
                Phone: <?= htmlspecialchars($client['phone']) ?>
            </p>
            <p>
                Bank: <b><?= htmlspecialchars($client['bank_short_name']) ?></b>,
                Account No: <b><?= htmlspecialchars($client['bank_account']) ?></b>
            </p>

            <table class="table table-bordered">
                <thead>
                    <tr style="background:#333;color:#fff">
                        <th>SN</th>
                        <th>Date</th>
                        <th>Symbol</th>
                        <th>Side</th>
                        <th>Trade Vol</th>
                        <th>Price</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                    $i        = 1;
                    $totalBuy = 0;
                    $totalSell = 0;
                    $grandTotal = 0;
                    $hasRows = false;

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                      $hasRows = true;
                      $tradeValue = (float)$row['trade_value'];
                      $grandTotal += $tradeValue;

                      if ($row['side'] === 'B') {
                          $totalBuy += $tradeValue;
                      } elseif ($row['side'] === 'S') {
                          $totalSell += $tradeValue;
                      }
                    ?>
                      <tr>
                          <td><?= $i++ ?></td>
                          <td><?= htmlspecialchars($row['order_date']) ?></td>
                          <td><?= htmlspecialchars($row['symbol']) ?></td>
                          <td><?= htmlspecialchars($row['side']) ?></td>
                          <td><?= number_format($row['lot_size_execute']) ?></td>
                          <td><?= number_format($row['order_exe_price'], 2) ?></td>
                          <td>Nu.<?= number_format($tradeValue, 2) ?></td>
                      </tr>
                  <?php
                    }

                    if (!$hasRows) {
                        exit('<p style="color:red;">Result not found.</p>');
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Commission Calculation
                    |--------------------------------------------------------------------------
                    */
                    $commissionRate = client_commission_multiple_brokers($cd_code, $mem_code);
                    $commission = round(($grandTotal * $commissionRate) / 100, 2);
                    $gst = 0;
                    if ($client['gst_register'] === 'Y') {
                        $gst = round($commission * 0.05, 2);
                    }
                    $payable = $totalSell - $totalBuy - $commission - $gst;
                  ?>
                    <tr>
                        <td colspan="2"><b>Total Buy Value</b></td>
                        <td colspan="2"><b><?= number_format($totalBuy, 2) ?></b></td>
                        <td colspan="2"><b>Total Sell Value</b></td>
                        <td><b><?= number_format($totalSell, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td colspan="5"><b>Total Commission</b></td>
                        <td><b><?= number_format($commission, 2) ?></b></td>
                        <td></td>
                    </tr>
                    <?php if ($gst > 0): ?>
                    <tr>
                        <td colspan="5"><b>GST</b></td>
                        <td><b><?= number_format($gst, 2) ?></b></td>
                        <td></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="5"><b>Total Payable/Receivable</b></td>
                        <td><b><?= number_format($payable, 2) ?></b></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- button for print -->
        <div class="row no-print mt-3">
          <div class="col-lg-12 mb-3">
              <a href="loadReportPrint.php?cd=<?= urlencode($cd_code) ?>&toDate=<?= urlencode($toDate) ?>&fromDate=<?= urlencode($fromDate) ?>&tradeConfirmation=tradeConfirmation" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
          </div>

          <div class="col-lg-8">
              <p class="mb-1">The email will be sent to:
                  <b><?= htmlspecialchars($client['email']) ?></b>
              </p>
              <small class="text-muted">
                  If this is incorrect, please update it in account registration
                  before sending the email.
              </small>
          </div>
          <div class="col-lg-4 text-start text-lg-end">
              <button type="button" class="btn btn-primary send-mail-btn" data-cd="<?= htmlspecialchars($cd_code) ?>" data-from="<?= htmlspecialchars($fromDate) ?>" data-to="<?= htmlspecialchars($toDate) ?>">
                  <i class="fa fa-envelope"></i> Send Mail
              </button>
          </div>
      </div>
      <script>
      $(document).on('click', '.send-mail-btn', function () {
          if (!confirm('Do you want to continue?')) {
              return;
          }
          const button = $(this);
          const cdCode  = button.data('cd');
          const fromDate = button.data('from');
          const toDate   = button.data('to');
          button.prop('disabled', true);
          showLoading();
          $.ajax({
              type: 'POST',
              url: '', // load_bbo_report.php
              data: {
                  cd_code: cdCode,
                  from_date: fromDate,
                  to_date: toDate,
                  sendNRBReportViaMail: 'sendNRBReportViaMail'
              },
              success: function (response) {
                  alert(response);
              },
              error: function () {
                  alert('Failed to send email. Please try again.');
              },
              complete: function () {
                  hideloading();
                  button.prop('disabled', false);
              }
          });
      });
      </script>

    </div>
    <?php
  }
  else {
    echo'
    <div class="row no-print">
      <div class="col-lg-12">
        &emsp;&emsp;<a href="loadReportPrint.php?cd='.$cd_code.'&toDate='.$toDate.'&fromDate='.$fromDate.'&tradeConfirmation=tradeConfirmation" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
      </div>
        <div class="row" style="margin-top: 5px;">
          <div class="col-lg-8">
            &emsp;&emsp;&emsp;
            The email will be sent to : <b>'.$row['email'].'</b>. <br>&emsp;&emsp;&emsp;If this is incorrect, Please update it in account registration and then send the email.
          </div>
          <div class="col-lg-4 float-left">
            &emsp;&emsp;
            <button class="btn btn-primary" onclick="sendMailNRB(\''.$cd_code.'\', \''.$fromDate.'\', \''.$toDate.'\')"><i class="fa fa-envelope"></i> Send Mail</button>
          </div>
        </div>

        <script type="text/javascript">
          function sendMailNRB(cd_code, from_date, to_date) {
            if (confirm("Do you want to continue?")) {
              showLoading();
              var op = "sendNRBReportViaMail";
              $.ajax({
                type: "POST",
                url: "load_bbo_report.php",
                data: "cd_code="+cd_code+"&from_date="+from_date+"&to_date="+to_date+"&sendNRBReportViaMail="+op,
                success: function(data){
                  hideloading();
                  alert(data);
                }
              });
            } else {
              return false;
            }
          }
        </script>
    </div>
    <br>';
  }

?>