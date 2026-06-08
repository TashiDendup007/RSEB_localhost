<?php
  //Email link
  require_once '../../PHPMailer-master/class.phpmailer.php';
  require '../../PHPMailer-master/class.smtp.php';
  // require '../../PHPMailer-master/PHPMailerAutoload.php';

  $mail = new PHPMailer(); //create a new object
  $mail->IsSMTP(); //enable SMTP
  //$mail->SMTPDebug = 4; //debugging: 1 = errors and messages, 2 = messages only
  $mail->SMTPAuth = true; //authentication enabled
  $mail->SMTPSecure = 'ssl'; //secure transfer enabled REQUIRED for Gmail
  $mail->Host = "smtp.gmail.com";
  $mail->Port = 465; //or 587
  $mail->IsHTML(true);
  // $mail->Password = "xzwnpzlmmbrwbchp";
  $mail->Username = "itrsebl19@gmail.com";
  $mail->Password = "xzwnpzlmmbrwbchp";
  $mail->SetFrom("itrsebl19@gmail.com", "RSEB");
  $mail->Subject = "Online Trading Terminal Application Approval";
  $mail->Body="
      Dear Sir/Madam,<br><br>
      
      kindly note that the Broker <b>".$username."</b> has verified the Online Trading Terminal Application. Please approve the application for the CD CODE ".$cdCode.". <br><br>

      Thank you.

      <br><br><br>
      <p style='color: red;'>*** This is an automatically generated email, please do not reply. ***</p><br><br>
      Royal Securities Exchange of Bhutan<br>
      Post Box No. 742<br>
      Email:rseb@rsebl.org.bt<br>
      Phone No.+975-02-323849<br>
  ";
  $mail->AddAddress('dorji.zangmo@rsebl.org.bt');
  // $mail->AddCC('ugyen.zangmo@rsebl.org.bt');
  $mail->Send();

?>