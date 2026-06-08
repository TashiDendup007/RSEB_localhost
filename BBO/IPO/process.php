<?php

include 'db.php';
include 'sanitize.php';


if(isset($_POST['subscribe'])){
  $cid = $_POST['CID'];
  $name = $_POST['Name'];
  $email = $_POST['Email'];
  $amount = $_POST['Amount'];
  $journal = $_POST['ref'];
  $vol = round($amount/13);
  $address = $_POST['addrs'];
  $status=0;

//echo $name."---".$email."---".$cid."---".$amount."---".$address."---".$journal."---".$vol."---".$status;

 $save = $dbh->prepare("INSERT INTO ipo(cid,name,email,amount,journal,vol,address,status) VALUES
    ('$cid','$name','$email','$amount','$journal','$vol','$address','$status')");
  if($save->execute())
  {
    echo 'Message! Successfully Subscribed.';
  }  
  else
  {
    echo 'Message! There was an error while operation. Please try again.';
  }
}
/*
else{

}*/

?>