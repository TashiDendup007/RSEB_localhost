<?php
require '../../CDS-CSS/FILES/vendor/phpmailer/src/Exception.php';
require '../../CDS-CSS/FILES/vendor/phpmailer/src/PHPMailer.php';
require '../../CDS-CSS/FILES/vendor/phpmailer/src/SMTP.php';

require_once __DIR__ . '/../../CDS-CSS/FILES/vendor/autoload.php';

date_default_timezone_set("Asia/Thimphu");
$sysTime = date("Y-m-d");

if(!empty($cd_code) && !empty($email)) {
  $template = '';
  $template .='
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .table-bordered {
          border-collapse: collapse;
          border: 1px solid #000;
      }
      .table-bordered th,
      .table-bordered td {
          border: 1px solid #000;
          padding: 4px;
      }
    </style>
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
                        Trade Confirmation Report<br>
                        From Date: <b>'.$from_date.'</b> To Date: <b>'.$to_date.'</b> <br>
                        Report generated on: '.$sysTime.' by '.$_SESSION['sess_username'].'
                    </p>
                </td>
            </tr>
        </table>
        <hr>';

        $template .='
        <p style="font-size: 11px;">
            CD CODE : <b>'.$row['cd_code'].'</b><br>
            NAME : <b>'.$row['f_name'].' '.$row['l_name'].'</b>, CID/DISN # <b>'.$row['ID'].'</b>, BROKER: <b>'.$row['name'].'</b><br> 
            BANK : <b>'.$row['bank_short_name'].'</b>, ACCOUNT NO: <b>'.$row['bank_account'].'</b>
        </p>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered text-color" width="100%" style="border: 1px solid #000;">
                <thead style="font-size: 12px;">
                    <tr>
                        <th>#</th>
                        <th>Symbol</th>
                        <th>Side</th>
                        <th>Trade Vol</th>
                        <th>Price</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody style="font-size: 12px;">';
                  $sql = $dbh->prepare("SELECT a.lot_size_execute, a.order_exe_price, a.side, a.order_date, (a.lot_size_execute * a.order_exe_price) AS tot_val, b.symbol 
                    FROM executed_orders a,symbol b 
                    where  a.symbol_id=b.symbol_id 
                    and cd_code = ?
                    AND a.order_date BETWEEN ? AND ?
                  ");
                  $sql->bindParam(1, $cd_code);
                  $sql->bindParam(2, $from_date);
                  $sql->bindParam(3, $to_date);
                  $sql->execute();
                  $count = 1;
                  $total = 0;
                  $totalb = 0;
                  $totals = 0;
                  foreach($sql as $res) {
                    $total1 = $res['lot_size_execute'] * $res['order_exe_price'];
                    $total = $total + $total1;

                    $template .='
                      <tr>
                        <td>'.$count++.'</td>
                        <td style="text-align: center;">'.$res['symbol'].'</td>
                        <td style="text-align: center;">'.$res['side'].'</td>
                        <td style="text-align: center;">'.$res['lot_size_execute'].'</td>
                        <td style="text-align: center;">'.$res['order_exe_price'].'</td>
                        <td>'.number_format($res['tot_val'], 2).'</td>
                    </tr>';
                    if ($res['side'] == 'B') {
                        $totalb += $total1;
                    } elseif ($res['side'] == 'S') {
                        $totals += $total1;
                    }
                  } 
                  $un = substr($_SESSION['sess_username'], 0, 7);
                  $b_commis = client_commission_multiple_brokers($cd_code, $un);
                  $to_com = ($total * $b_commis) / 100;
                  $totalpr = $totals - $totalb - $to_com;

                  $template .= '
                    <tr>
                      <td><b>Total Buy Value</b></td>
                      <td>'.number_format($totalb, 2, ".", ",").'</td>
                      <td><b>Total Sell Value</b></td>
                      <td>'.number_format($totals,2, ".", ",").'</td>
                      <td><b>Total Commission</b></td>
                      <td>'.number_format($to_com,2, ".", ",").'</td>
                    </tr>
                    <tr>
                      <td colspan="5"><b>Total Payable/Receivable</b></td>
                      <td>Nu. '.number_format($totalpr,2,".",",").'</td>
                    </tr>
                  ';

                $template .='
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  </html>';

  $stmt = $dbh->prepare("SELECT e.email_add FROM email_confirmation e WHERE e.status = 1 AND e.mem_code = ? AND e.email_for IN ('trade_confirmation')");
  $stmt->bindParam(1, $mem_broker);
  $stmt->execute();
  $broker_emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $mpdf = new \Mpdf\Mpdf();
  // content
  $mpdf->WriteHtml($template);
  // footer
  $footerHtml = "<div style='text-align: center;'><hr><p style='font-size: 9px; margin-top: 0px;'>THIS IS A COMPUTER GENERATED REPORT AND DOES NOT REQUIRE SIGNATURE</p></div>";
  $mpdf->SetHTMLFooter($footerHtml);
  $pdf = $mpdf->output("", "S");

  sendEmail($pdf, $email, $trade_date, $cd_code, $broker_emails, $mem_broker);
} else {
  echo 'Required email address.'; die();
}


function sendEmail($pdf, $email, $trade_date, $cdCode, $broker_emails, $mem_broker)
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
      $mail->addAddress($email);
      
      // CC recipients
      foreach ($broker_emails as $key => $value) {
        $mail->addCC($value['email_add']);
      }
      
      //Attachment
      $mail->addStringAttachment($pdf, "TradeConfirmation_".$cdCode.".pdf");
   
      // Content
      $mail->isHTML(true);
      $mail->Subject = 'TRADE CONFIRMATION REPORT';

      // Define an associative array to store broker addresses
      $broker_addresses = [
          'MEMRNRB' => 'Royal Securities Exchange of Bhutan, RSEB Office, Thimphu<br>
                        Post Box No: 742<br>
                        Email: khanduwang@rsebl.org.bt<br>
                        Phone No.+975-02-323849 / +975-17626573',
          'MEMBNBL' => 'BNB Securities, BNB Corporate Head Office, Nordzin Lam II, Thimphu<br>
                        Name: Karma Choden<br>
                        Email: karmachoden@bnb.bt<br>
                        Phone no: +975-17434138',
          'MEMBOBL' => 'BOB Securities, BOBL Corporate Office, Norzin Lam, Thimphu<br>
                        Name: Sonam Peldon<br>
                        Email: sonam.peldon2956@bob.bt<br>
                        Phone No: +975-77789206',
          'MEMBPCL' => 'Bhutan Post Corporation Ltd, Head Office, Thimphu<br>
                        Name: Ugyen Tshomo<br>
                        Email: ugyen.tshomo@bhutanpost.bt<br>
                        Phone No: +975-17248632',
          'MEMDSBP' => 'Drukyul Securities Broker Pvt Ltd, Jangchub Lam, Thimphu<br>
                        Name: Tshering Chophel<br>
                        Email: drukyulsecurities@gmail.com<br>
                        Phone No: +975-77142330',
          'MEMLDSB' => 'Lekpay Dolma Securities Broker Pvt Ltd, Namgyel Plaza Building, Thimphu<br>
                        Post Box No: 761<br>
                        Name: Tashi Wangchen<br>
                        Email: lekpaydolmashares@gmail.com<br>
                        Phone No: +975-77108828',
          'MEMSERS' => 'Sershing Securities Broker Pvt Ltd, Yangchen Lam, Thimphu<br>
                        Post Box No: 369<br>
                        Name: Kinley Pem<br>
                        Email: sershingsecurities@gmail.com<br>
                        Phone No: +975-17955891',
          'MEMRICB' => 'RICB Securities, RICB Office Building, Norzin Lam, Thimphu<br>
                        Name: Parsuram Tirwa/ Sangay Tenzin/ Sarita Poudel<br>
                        Email: parsuram_tirwa@ricb.bt/ sangay_tenzin2@ricb.bt/ sarita_poudel@ricb.bt<br>
                        Phone No: +975-17612015/ +975-77487283/ +975-17970963',
          'MEMRINS' => 'Rinson Securities Pvt Ltd, Soenamling Building, Jangchub lam, Thimphu<br>
                        Post Box No: 987<br>
                        Name: Anisha Gurung<br>
                        Email: rinsecurities@gmail.com<br>
                        Phone No: +975-77642981',
          'MEMBDBL' => 'BDB Securities, BDBL Office Building, Thimphu<br>
                        Name: Kencho Wangmo<br>
                        Email: kencho.wangmo@bdb.bt<br>
                        Phone No: +975-17455833',
      ];

      // Check if the $mem_broker exists in the array, if not, set $broker_address to an empty string
      $broker_address = isset($broker_addresses[$mem_broker]) ? $broker_addresses[$mem_broker] : '';

      $mail->Body = '
        Dear Sir/Madam, <br><br> 
        Please find attached the trade confirmation report for trades executed on <strong>'.$trade_date.'</strong>. Settlement will occur in 2 business days.<br><br>

        <p style="color: red; font-size: 16px; font-weight: bold;">*** This is an automated email. Please do not reply. For any queries, contact your broker using the details below. *** </p>
        <br>

        <hr>
        <strong><i>
          '.$broker_address.'
        </i></strong><hr>
      ';
      if ($mail->send()) {
        echo 'Trade confirmation has been sent';
      } else {
        echo 'Could not send Eamil: '.$mail->ErrorInfo;
      }
  } catch (Exception $e) {
      echo "Trade confirmation could not be sent. Mailer Error: { $mail->ErrorInfo }";
  }
}

?>