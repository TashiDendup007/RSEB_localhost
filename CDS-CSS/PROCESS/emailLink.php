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
  $mail->Username = "itrsebl19@gmail.com";
  $mail->Password = "xzwnpzlmmbrwbchp";
  $mail->SetFrom("itrsebl19@gmail.com", "RSEB");
  $mail->Subject = "mCaMS and CaMS client terminal user credentials";
  $mail->Body="
  Dear Sir/Madam,<br><br>
  
  kindly note the following credentials for your Online Trading Terminal : <br><br>

  username : ".$un."<br>
  password : ".$un."<br><br>

  Please change your password on your first login. <br><br>

  URL/access link : https://cms.rsebl.org.bt/RSEB/access.php . <br><br>

  <!-- You can also find the Online Trading Terminal and mCaMS user manual, attached. <br><br>

  Please contact us anytime for any queries and acknowledge us the receipt of the email la.<br><br> -->

  Note : You can use the same credentials in the Mobile app (mCaMS) but you need to change your password on your first login in the web access, then only you can use the same details to login in the app. Please follow the following links to download the app :<br><br>

  For iPhone <br>
  https://itunes.apple.com/app/id1457797916?fbclid=IwAR2u3ZyND34WPRUOolAPbjFKUIharifM-mW9dkN9m4q_Io5a8FhRiQuhqrg <br><br>

  For Android <br>
  https://play.google.com/store/apps/details?id=rsebl.org.bt&fbclid=IwAR14aBeYNShG6UYPM7kB43wlU0Acsh6raGhFayhaSgGtwfFrVKnmLExg57A <br><br>

  Thank you.<br><br>

  <p style='color: red; font-size: 16px; font-weight: bold;'>
    *** This is an automatically generated email, please do not reply. For any queries, please contact 02-323849 or mail to rseb@rsebl.org.bt ***
  </p><br><br>

  <i>Royal Securities Exchange of Bhutan</i><br>
  <i>Post Box No. 742</i><br>
  ";
  $mail->AddAddress($email);
  $mail->AddCC($emailBroker);
  $mail->Send();
  /*if(!$mail->Send())
  {
    echo "<br>Mailer Error: ".$mail->ErrorInfo;
  }else{
    header('location: ../FILES/userList.php?ms=1');
    exit();
  }*/

?>