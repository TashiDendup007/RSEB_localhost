<?php
include ('../../CONNECTIONS/db.php');

require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set("Asia/Thimphu");
$sysTime = date("Y-m-d H:i:s");

$cid_no = $_POST['cid_no'];
$email = $_POST['email'];

if(!empty($cid_no) && !empty($email)){
  $template = '';
  $template .='
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
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
                <td>
                    <h3 class="text-center">Royal Securities Exchange of Bhutan</h3>
                    <p class="text-center">
                        Account Summary Details<br>
                        Report generated on: '.$sysTime.'
                    </p>
                </td>
            </tr>
        </table>';
        $fetchSql = $dbh->prepare("SELECT a.ID, a.title, a.f_name, a.l_name, a.email, a.phone, a.tpn, a.address
          FROM client_account a WHERE a.ID=:cidNo 
          ORDER BY a.client_id DESC");
        $fetchSql->bindParam(':cidNo', $cid_no);
        $fetchSql->execute();
        $tes=$fetchSql->fetch();
        $template .='
        <p>
            CID/DISN/CD CODE : '.$tes['ID'].'<br>
            NAME : '.$tes['f_name'].' '.$tes['l_name'].'<br>
            TPN No : '.$tes['tpn'].'<br>
            ADDRESS : '.$tes['address'].'
        </p>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered text-color" width="100%" border="1">
                <thead style="font-size: 12px;">
                    <tr>
                        <th>Sl#</th>                    
                        <th>CD Code/Symbol</th>
                        <th>Block Vol</th>
                        <th>Pledged Vol</th> 
                        <th>Total Volume</th>
                    </tr>
                </thead>
                <tbody>';
                  $sql = $dbh->prepare("SELECT c.cd_code, c.symbol_id, c.volume, c.pledge_volume, c.block_volume, c.pending_in_vol, c.pending_out_vol, s.symbol,
                        (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol) AS total
                        FROM cds_holding c 
                        JOIN client_account a ON c.cd_code=a.cd_code 
                        JOIN symbol s ON c.symbol_id=s.symbol_id
                        WHERE a.ID=:cidNo 
                        AND (c.volume + c.pledge_volume + c.block_volume + c.pending_in_vol + c.pending_out_vol)>0 AND s.status=1");
                  $sql->bindParam(':cidNo', $cid_no);
                  $sql->execute();
                  $count=1;
                  foreach($sql as $res){
                    $template .='
                      <tr>
                        <td>'.$count++.'</td>
                        <td>'.$res['cd_code'].' - '.$res['symbol'].'</td>
                        <td>'.number_format($res['block_volume']).'</td>
                        <td>'.number_format($res['pledge_volume']).'</td>
                        <td>'.number_format($res['total']).'</td>
                    </tr>';
                  } 
                $template .='
                </tbody>
            </table>
          </div>
          <div><hr>
            <p class="text-center text-color">
              THIS IS A COMPUTER GENERATED REPORT AND REQUIRES NO SIGNATORY
            </p><hr>
          </div>
        </div>
      </div>
    </div>
  </body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  </html>';
  $mpdf = new \Mpdf\Mpdf();
  $mpdf->WriteHtml($template);
  $pdf = $mpdf->output("","S");

  sendEmail($pdf, $email);
}else{
  echo 'Required email address.'; die();
}


function sendEmail($pdf, $email)
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
   
      //Recipients
      $mail->setFrom('itrsebl19@gmail.com', 'Royal Securities Exchange of Bhutan(RSEB)');
      $mail->addAddress($email);
      
      //Attachment
      $mail->addStringAttachment($pdf,"ShareStatement.pdf");
   
      // Content
      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = 'Share Statement';
      $mail->Body = '
        Dear Sir/Madam, <br><br> 
        
        Please find attached your share statement.<br><br><br>
        
        <p style="color: red; font-size: 16px; font-weight: bold;">*** This is an automatically generated email, please do not reply. ***</p>
        <hr><strong><i>
        Royal Securities Exchange of Bhutan<br>
        Post Box No. 742<br>
        Email:rseb@rsebl.org.bt<br>
        Phone No.+975-02-323849</i></strong><hr>';
   
      $mail->send();
      echo 'Share Statement has been sent';
  } catch (Exception $e) {
      echo "Share Statement could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
}

?>