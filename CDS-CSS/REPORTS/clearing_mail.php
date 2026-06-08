<?php
require '../FILES/vendor/phpmailer/src/Exception.php';
require '../FILES//vendor/phpmailer/src/PHPMailer.php';
require '../FILES//vendor/phpmailer/src/SMTP.php';

require_once __DIR__ . '/../../CDS-CSS/FILES//vendor/autoload.php';

date_default_timezone_set("Asia/Thimphu");
$sysTime = date("Y-m-d");

if(!empty($from_date) && !empty($to_date)) {
  $template = '';
  $template .='
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>'.file_get_contents("bootstrap_class_for_mail.css").'</style>
  </head>
  <body>
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <table border="0" width="100%">
            <tr>
                <td>
                    <img src="../../img/logo.png" alt="Logo">
                </td>
                <td style="font-size: 12px; text-align: center;">
                    <h3>ROYAL SECURITIES EXCHANGE OF BHUTAN</h3>
                    <p>
                        '. $trade_type .' Clearing Detail<br>
                        From Date: <b>'.$from_date.'</b> To Date: <b>'.$to_date.'</b> <br>
                        Report generated on: '.$sysTime.' by '.$_SESSION['sess_username'].'
                    </p>
                </td>
            </tr>
          </table>
          <hr>';

          $template .='
          <div class="card-body table-responsive">';
            $query= $dbh->prepare("
                SELECT DISTINCT a.participant_code, b.clearing_account
                FROM {$table_name} a
                INNER JOIN adm_participants b ON a.participant_code = b.participant_code
                WHERE a.order_date BETWEEN ? AND ?
            ");
            $query->execute([$from_date, $to_date]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $i = 1;
            foreach ($results as $res) {
              $totalb = 0;
              $totals = 0;
              $template .='
              <b style="font-size: 11px;">MEMBER : '.$res['participant_code'].'</b><br>
              <table class="table table-striped" width="100%">
                  <thead>
                    <tr>
                      <th style="font-size: 11px;">SN</th>
                      <th style="font-size: 11px;">REMARKS</th>
                      <th style="font-size: 11px;">AMOUNT</th>
                    </tr>
                  </thead>
                  <tbody>';
                  $stmt = $dbh->prepare("
                      SELECT SUM(lot_size_execute * order_exe_price) AS total_buy_amt 
                      FROM {$table_name} WHERE status = 0 AND participant_code = ? AND side = 'B' AND order_date BETWEEN ? AND ?
                  ");
                  $stmt->execute([$res['participant_code'], $from_date, $to_date]);
                  $total_buy_amt = $stmt->fetchColumn();
                  $totalb = isset($total_buy_amt) ? $total_buy_amt : 0;

                  $template .='
                  <tr>
                      <td style="font-size: 10px;">'.$i++.'</td>
                      <td style="font-size: 10px;">Total buy amount</td>
                      <td style="font-size: 10px;">Nu. ('.number_format($totalb, 2, ".",",").')</td>
                  </tr>';

                  $stmt1 = $dbh->prepare("
                      SELECT SUM(lot_size_execute * order_exe_price) AS total_sell_amt 
                      FROM {$table_name} WHERE status = 0 AND participant_code = ? AND side = 'S' AND order_date BETWEEN ? AND ?
                  ");
                  $stmt1->execute([$res['participant_code'], $from_date, $to_date]);
                  $total_sell_amt = $stmt1->fetchColumn();
                  $totals = isset($total_sell_amt) ? $total_sell_amt : 0;

                  $template .='
                  <tr>
                      <td style="font-size: 10px;">'.$i++.'</td>
                      <td style="font-size: 10px;">Total sell amount</td>
                      <td style="font-size: 10px;">Nu. '.number_format($totals, 2, ".",",").'</td>
                  </tr>';

                  $diff = $totals - $totalb;
                  if ($diff != 0) {
                      $isCredit = $diff > 0;
                      $rm = $isCredit ? "<span style='color:green;'>CREDIT (Pay)</span>" : "<span style='color:red;'>DEBIT(Collect)</span>";
                      $amount = $isCredit ? $diff : -$diff;
                      $formattedAmount = number_format($amount, 2, ".", ",");
                      $template .='
                      <tr style="font-size: 10px;">
                        <td style="font-size: 10px;"><b>Instruction : '.$rm.'</b></td>
                        <td style="font-size: 10px;"><b> Account # : '.$res['clearing_account'].'</b></td>
                        <td style="font-size: 10px;"><b>Nu. '.$formattedAmount.'</b></td>
                      </tr>';
                  } else {
                      $template .='
                      <tr style="font-size: 10px;">
                          <td style="font-size: 10px;"><b>Instruction : None</b></td>
                          <td style="font-size: 10px;"></td>
                          <td style="font-size: 10px;"></td>
                      </tr>';
                  }
              $template .='</tbody>
              </table>
              <br>';
            }
            $template .='
          </div>
        </div>
      </div>
    </div>
  </body>
  </html>';

  $stmt = $dbh->prepare("SELECT e.email_add FROM email_confirmation e WHERE e.status = 1 AND e.email_for IN ('trade_confirmation', 'clearing_detail') ORDER BY e.institute_id ASC");
  $stmt->execute();
  $broker_emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $mpdf = new \Mpdf\Mpdf();
  $mpdf->WriteHtml($template);
  $pdf = $mpdf->output("", "S");

  sendEmail($pdf, $broker_emails, $trade_date);
} else {
  echo 'Required From and To Date'; 
  die();
}


function sendEmail($pdf, $broker_emails, $trade_date)
{
  //Instantiation and passing `true` enables exceptions
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  try {
      //Server settings
      //$mail->SMTPDebug = 2;                                     // Enable verbose debug output
      $mail->isSMTP();                                            // Set mailer to use SMTP
      $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
      $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
      $mail->Username   = 'itrsebl19@gmail.com';                  // SMTP username
      $mail->Password   = 'xzwnpzlmmbrwbchp';                     // SMTP password
      $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
      $mail->Port       = 465;                                    // TCP port to connect to
   
      //Recipient
      $mail->setFrom('itrsebl19@gmail.com', 'Royal Securities Exchange of Bhutan (RSEB)');
      foreach ($broker_emails as $key => $value) {
        $mail->addAddress($value['email_add']);
      }
      
      //Attachment
      $mail->addStringAttachment($pdf, "ClearingReport_".$trade_date.".pdf");
   
      // Content
      $mail->isHTML(true);
      $mail->Subject = 'CLEARING DETAIL REPORT';

      $mail->Body = '
        Dear Sir/Madam, <br><br> 
        Please find attached the trade report dated '.$trade_date.', which needs to be settled within 2 business days. We kindly request your prompt attention to this matter. Buyers are advised to deposit the specific funds reflected in the report by the next business day.<br><br> 

        <p style="color: red; font-size: 16px; font-weight: bold;">
          *** This is an automated email; replies will not be monitored. For any queries, contact the focal person mentioned below. ***
        </p>
        <br>
        Thank you for your cooperation.<br>
        Best regards,<br>
        <hr>
        <strong><i>
          Khandu Wangmo<br>
          Royal Securities Exchange of Bhutan, RSEB Office, Thimphu<br>
          Email: khanduwang@rsebl.org.bt<br>
          Phone No.+975-02-323849 / +975-17626573
        </i></strong><hr>
      ';
      $mail->send();
      echo 'Clearing Detail has been sent';
  } catch (Exception $e) {
      echo "Clearing Detail could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
}

?>