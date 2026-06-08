<?php
  //Email link
  require_once '../../PHPMailer-master/class.phpmailer.php';
  require '../../PHPMailer-master/class.smtp.php';
  // require '../../PHPMailer-master/PHPMailerAutoload.php';
  
  $mail = new PHPMailer();
  $mail->IsSMTP();
  //$mail->SMTPDebug = 4; //debugging: 1 = errors and messages, 2 = messages only
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = 'ssl';
  $mail->Host = "smtp.gmail.com";
  $mail->Port = 465;
  $mail->IsHTML(true);

  $mail->Username = "itrsebl19@gmail.com";
  $mail->Password = "xzwnpzlmmbrwbchp"; //19
  // $mail->Password = "jwkqqavidwdcttua"; //25
  $mail->SetFrom("itrsebl19@gmail.com", "RSEB");
  
  $mail->Subject = "CaMS Login credentials";
  $mail->Body="
    Dear Sir/Madam,<br><br>

    kindly note the following credentials for your CaMS System : <br><br>

    username : ".$username."<br>
    password : ".$username."<br><br>

    Please change your password on your first login. <br><br>

    URL/access link : <a href='https://cms.rsebl.org.bt/RSEB/access.php'>Click here to access the System</a>. <br><br><br>

    Thank you.<br><br>

    <p style='color: red; font-size: 16px; font-weight: bold;'>
      *** This is an automatically generated email, please do not reply. For any queries, please contact 02-323849 or mail to rseb@rsebl.org.bt ***
    </p><br><br>

    <i>Royal Securities Exchange of Bhutan</i><br>
    <i>Post Box No. 742</i><br>
  ";
  $mail->AddAddress($email);
  $mail->Send();

?>