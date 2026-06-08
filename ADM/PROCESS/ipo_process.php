<?php 
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$role = $_SESSION['sess_userrole'];
if( $role!="1")
{
  header('Location: ../../access.php?err=2'); die();
}
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
  { 
    header("Location: ../../Authentication/Logout.php"); die();
  }
}
include('../../Functions/f.php'); 
$_SESSION['timeout'] = time();
$username=$_SESSION['sess_username'];

//Saving Record
if (isset($_POST['save_ipo_symbol'])) { 
  //variable declaration  
  $isin = $_POST['isin'];
  $sy =$_POST['sy'];
  $name = $_POST['name'];
  $sector=$_POST['sector'];
  $fv = $_POST['fv'];
  $pv=$_POST['pv'];
  $bl=$_POST['bl'];
  $pus=$_POST['pus'];
  $doe=$_POST['doe'];
  $dol=$_POST['dol'];
  $stype = $_POST['stype'];
  
  if ($stype == 'OS') {
    $matPeriod = 0;
    $matDate = '0000-00-00';
  } else {
    $matPeriod = $_POST['matPeriod'];
    $matDate = $_POST['matDate'];
  }
  $status=$_POST['status'];
  // !- variable declaration   
  $sql= "SELECT symbol from ipo where symbol=:sym";
  $save = $dbh->prepare($sql);
  $save->bindParam(':sym', $sy);
  $save->execute();
 if($row = $save->fetch()==0) { 
   $sql= "INSERT INTO ipo(isin,symbol,name,sector,face_value,premium_value,security_type,maturity_period,maturity_date,status,board_lot,paid_up_shares,date_of_listing,date_of_est)
   VALUES ('$isin','$sy','$name','$sector','$fv','$pv','$stype','$matPeriod','$matDate','$status','$bl','$pus','$dol','$doe')";
   $save = $dbh->prepare($sql);
   if($save->execute())
      {   
          echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div>';
      }  
   else
      {    
          echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div>';
      }
  }
  else
  { 
    echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Record Already Exists.</div></div>';
  }  
} else if (isset($_POST['save_broker'])) { 
  $participant = $_POST['participant'];
  $broker = $_POST['broker'];
  $type = $_POST['type'];
  $rate = $_POST['rate'];
  $symbol = $_POST['symbol'];
  $status = $_POST['status'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $sql= "INSERT INTO assign_broker(participant_code, username, type, rate, symbol, status) VALUES (:part, :broker, :type, :rate, :symbol, :status)";
    $save = $dbh->prepare($sql);
    $save->bindParam(':part', $participant);
    $save->bindParam(':broker', $broker);
    $save->bindParam(':type', $type);
    $save->bindParam(':rate', $rate);
    $save->bindParam(':symbol', $symbol);
    $save->bindParam(':status', $status);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    $save = null;

    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div>';
  }catch(PDOException $e){
    $dbh->rollBack();
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-check"> </i> There was an error while operation.</div></div>';
  }
  die();
} else if (isset($_POST['edit_assignBroker'])) { 
  date_default_timezone_set("Asia/Thimphu"); 
  $sysDateTime = date("Y-m-d H:i:s"); 

  $id = $_POST['id'];
  $participant = $_POST['participant'];
  $broker = $_POST['broker'];
  $type = $_POST['type'];
  $rate = $_POST['rate'];
  $symbol = $_POST['symbol'];
  $status = $_POST['status'];

  try{
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $sql= "UPDATE assign_broker SET participant_code=:part, username=:broker, type=:type, rate=:rate, symbol=:symbol, status=:status, updated_at=:udate WHERE id=:id";
    $save = $dbh->prepare($sql);
    $save->bindParam(':part', $participant);
    $save->bindParam(':broker', $broker);
    $save->bindParam(':type', $type);
    $save->bindParam(':rate', $rate);
    $save->bindParam(':symbol', $symbol);
    $save->bindParam(':status', $status);
    $save->bindParam(':udate', $sysDateTime);
    $save->bindParam(':id', $id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    $save = null;

    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div>';
  }catch(PDOException $e){
    $dbh->rollBack();
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div>';
  }
  die();
}
else if (isset($_POST['delete_assign_broker'])) { 
  $id = $_POST['id'];
  try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $save = $dbh->prepare("DELETE FROM assign_broker WHERE id=:id");
      $save->bindParam(':id', $id);
      $save->execute();

      $dbh->commit();
      $dbh = null;
      $save = null;

      $data = array(
          'status' => 200,
          'success' => true,
          'message' => 'Data deleted successfully.'
      );
  } catch(PDOException $e) {
      $data = array(
          'status' => 400,
          'success' => false,
          'message' => $e->getMessage()
      );
  }

  header('Content-Type: application/json');
  echo json_encode($data);
  die();
}
else
{
  echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Oops Sorry! There was no function operation.</div></div>';
  // header('location: ../FILES/bbo-landing.php?ms=2');
  die();
}
?>