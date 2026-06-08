<?php
  //Email link
  require_once '../PHPMailer-master/class.phpmailer.php';
  require '../PHPMailer-master/class.smtp.php';

  $mail = new PHPMailer(); //create a new object
  $mail->IsSMTP(); //enable SMTP
  //$mail->SMTPDebug = 4; //debugging: 1 = errors and messages, 2 = messages only
  $mail->SMTPAuth = true; //authentication enabled
  $mail->SMTPSecure = 'ssl'; //secure transfer enabled REQUIRED for Gmail
  $mail->Host = "smtp.gmail.com";
  $mail->Port = 465; //or 587
  $mail->IsHTML(true);
  
  $mail->Username = "itrsebl19@gmail.com";
  $mail->Password = "xzwnpzlmmbrwbchp";
  $mail->SetFrom("itrsebl19@gmail.com", "RSEB (Royal Securities Exchange of Bhutan)");
  
  $mail->Subject = "TBL Auction BID Successful";
  $mail->Body = "
    Dear Sir/Madam,<br><br>


    We are pleased to confirm that your BID for the <b>".$symbol."</b> Auction, comprising <b>".$vol."</b> Volume, has been successfully placed.<br><br>

    Thank you for your valued participation and trust in the Royal Securities Exchange of Bhutan. We are committed to serving you with excellence.<br><br>

    <p style='color: red; font-size: 16px; font-weight: bold;'>
      *** This is an automated email. For any queries, please contact us at 02-323849 or email rseb@rsebl.org.bt. ***
    </p>
    <hr>
    Royal Securities Exchange of Bhutan
    Phone: +975-02-323849 | Email: rseb@rsebl.org.bt
    <br><br>
  ";
  $mail->AddAddress($email);
  $mail->Send();
  
?>