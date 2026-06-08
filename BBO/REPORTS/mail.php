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
                        <th>Clean Price</th>';
                        if ($sec_type != 'OS') {
                          $template .= '<th>Dirty Price</th>';
                        }
                        $template .='
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody style="font-size: 12px;">';
                  $table_name = ($sec_type === 'OS') ? 'executed_orders' : 'bond_executed_orders';

                  $query = "SELECT a.lot_size_execute, a.order_exe_price, a.order_date, a.side, b.symbol ";
                  if ($sec_type != 'OS') {
                    $query .= ", a.dirty_price, a.accur_rate, a.ytm ";
                  }
                  $query .= "FROM {$table_name} a 
                        JOIN symbol b ON a.symbol_id = b.symbol_id 
                        WHERE cd_code = :cd AND a.order_date BETWEEN :fromDate AND :toDate";

                  $sql = $dbh->prepare($query);
                  $sql->bindParam(1, $cd_code);
                  $sql->bindParam(2, $from_date);
                  $sql->bindParam(3, $to_date);
                  $sql->execute();
                  $count = 1;
                  $total = 0;
                  $totalb = 0;
                  $totals = 0;
                  foreach($sql as $res) {
                    $total1 = ($sec_type != 'OS') ? ($res['lot_size_execute'] * $res['dirty_price']) : ($res['lot_size_execute'] * $res['order_exe_price']);
                    $total = $total + $total1;

                    $template .='
                      <tr>
                        <td>'.$count++.'</td>
                        <td style="text-align: center;">'.$res['symbol'].'</td>
                        <td style="text-align: center;">'.$res['side'].'</td>
                        <td style="text-align: center;">'.$res['lot_size_execute'].'</td>
                        <td style="text-align: center;">'.$res['order_exe_price'].'</td>';
                        if ($sec_type != 'OS') {
                          $template .= '<td style="text-align: center;">'.$res['dirty_price'].'</td>';
                        }
                        $template .= '
                        <td>'.number_format($total1, 2).'</td>
                    </tr>';
                    if ($res['side'] == 'B') {
                        $totalb += $total1;
                    } elseif ($res['side'] == 'S') {
                        $totals += $total1;
                    }
                  }

                  // get commission
                  $to_com = 0;
                  $un = substr($_SESSION['sess_username'], 0, 7);
                  if ($sec_type === 'OS') {
                    $b_commis = client_commission_multiple_brokers($cd_code, $un);
                    $to_com = round(($total * $b_commis) / 100, 2);
                  } 
                  else {
                    $stmt = $dbh->prepare("
                          SELECT SUM(b.amount) AS tot_com
                          FROM bbo_finance b 
                          LEFT JOIN symbol s ON b.symbol_id = s.symbol_id
                          WHERE b.flag = 4 AND b.symbol_id != 0 AND s.security_type IN ('GB', 'CB')
                          AND b.cd_code = ? AND b.finance_date BETWEEN ? AND ? 
                          AND substr(b.user_name, 1, 7) = ? 
                    ");
                    $stmt->execute([$cd_code, $fromDate, $toDate, $un]);
                    $to_com = $stmt->fetchColumn();
                  }
                  $gst_amt = round($tot_com * 0.05, 2);

                  $totalpr = 0;
                  if ($row['gst_register'] == 'Y') {
                    $totalpr = $totals - ($totalb + abs($tot_com) + abs($gst_amt));
                  } else {
                    $totalpr = $totals - ($totalb +  abs($tot_com));
                  }

                  $template .= '
                    <tr>
                      <td><b>Total Buy Value</b></td>
                      <td>'.number_format($totalb, 2, ".", ",").'</td>
                      <td><b>Total Sell Value</b></td>
                      <td>'.number_format($totals,2, ".", ",").'</td>
                      <td><b>Total Commission</b></td>
                      <td>'.number_format(abs($tot_com),2, ".", ",").'</td>
                    </tr>';

                    if ($row['gst_register'] == 'Y') {
                      $template .= '
                        <tr>
                          <td colspan="4"></td>
                          <td><b>GST<b></td>
                          <td colspan="2"><b>'.number_format(abs($gst_amt), 2, ".", ",").'</b></td>
                        </tr>';
                    }

                    $template .= '
                    <tr>
                      <td colspan="5" style="text-align: center;"><b>Total Payable/Receivable</b></td>
                      <td colspan="2">Nu. '.number_format($totalpr, 2,".",",").'</td>
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
      /*foreach ($broker_emails as $key => $value) {
        $mail->addCC($value['email_add']);
      }*/
      
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
                        Name: Parsuram Tirwa/ Sangay Tenzin<br>
                        Email: parsuram_tirwa@ricb.bt/ sangay_tenzin2@ricb.bt<br>
                        Phone No: +975-17612015/ +975-77487283',
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
        <strong>
          <i>'.$broker_address.'</i>
        </strong><hr>
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