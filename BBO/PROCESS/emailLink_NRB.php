<?php
  //Email link
  require_once '../../PHPMailer-master/class.phpmailer.php';
  require '../../PHPMailer-master/class.smtp.php';
  //require '../../PHPMailer-master/PHPMailerAutoload.php';
  
  $mail = new PHPMailer();
  $mail->IsSMTP(); 
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = 'ssl'; 
  $mail->Host = "smtp.gmail.com";
  $mail->Port = 465; 
  $mail->IsHTML(true);
  $mail->Username = "itrsebl19@gmail.com";
  $mail->Password = "xzwnpzlmmbrwbchp";
  $mail->SetFrom("itrsebl19@gmail.com", "RSEB");
  $mail->Subject = "mCaMS and CaMS client terminal user credentials";
  
  $mail->Body="
    Dear Sir/Madam,<br><br>
    
    kindly note the following credentials for your Online Trading Terminal : <br><br>

    <b>Username</b> : ".$clientUserName."<br>
    <b>Password</b> : ".$clientUserName."<br><br>

    Please change your password on your first login. <br><br>

    URL/access link : https://cms.rsebl.org.bt/RSEB/access.php . <br><br>

    <!-- You can also find the Online Trading Terminal and mCaMS user manual, attached. <br><br>

    Please contact us anytime for any queries and acknowledge us the receipt of the email la.<br><br> -->

    Note : You can use the same credentials in the Mobile app (mCaMS) but you need to change your password on your first login in the web access, then only you can use the same details to login in the app. Please follow the following links to download the app :<br><br>

    For iPhone <br>
    https://itunes.apple.com/app/id1457797916?fbclid=IwAR2u3ZyND34WPRUOolAPbjFKUIharifM-mW9dkN9m4q_Io5a8FhRiQuhqrg <br><br>

    For Android <br>
    https://play.google.com/store/apps/details?id=rsebl.org.bt&fbclid=IwAR14aBeYNShG6UYPM7kB43wlU0Acsh6raGhFayhaSgGtwfFrVKnmLExg57A <br><br>

    <!-- Kindly acknowledge the receipt of this email.<br><br> -->

    Thank you.

    <br><br><br>
    <p style='color: red; font-size: 16px; font-weight: bold;'>
      *** This is an automatically generated email, please do not reply. For any queries, please contact 02-323849 or mail to rseb@rsebl.org.bt ***
    </p><br><br>

    <i style='font-size: 14px;'>
    Royal Securities Exchange of Bhutan<br>
    Norzin Lam, Thimphu, RICB Building, Top Floor<br>
    Post Box No. 742<br>
    </i>
  ";
  $mail->AddAddress($email);
  $mail->Send();

?>