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
  
  $mail->Subject = "Bond Subscription Success";
  $mail->Body = "
    Respected Dasho/Lyonpo/Sir/Madam,<br><br>


    We are pleased to confirm that your subscription for the <b>".$symbol."</b> Bond, comprising <b>".$vol."</b> Unit(s), has been successfully processed by the Royal Securities Exchange of Bhutan.<br><br>

    Should you wish to update your subscription details, please visit our secure portal:<br>

    <a href='https://rsebl.org.bt/bond' target='_new'> CLICK HERE TO OPEN THE PAGE</a><br><br>


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