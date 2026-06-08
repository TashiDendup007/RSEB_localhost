<?php
date_default_timezone_set("Asia/Thimphu");
session_start();
$role = $_SESSION['sess_userrole'];
if( $role!="7")
{
  header('Location: ../../access.php?err=2');
}
$inactive = 1500;
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
  { 
    header("Location: ../../Authentication/Logout.php"); 
  }
}
$_SESSION['timeout'] = time();
include ('../../CONNECTIONS/db.php');


if(!empty($_POST["getIndiDtls"])) 
{
  $id = $_POST['id'];

  $select = $dbh->prepare("SELECT c.f_name, c.l_name, c.phone, c.cd_code FROM custodial_account c WHERE c.ID=:id");
  $select->bindParam(':id', $id);
  $select->execute();
  $res = $select->fetch();
  if($select->rowCount() > 0)
  {
    echo'
    <div class="col-xs-8">
      <label>Details of Client</label>
      <input type="hidden" name="cdCode" id="cdCode" value="'.$res['cd_code'].'">
      <input type="text" class="form-control" value="Name: '.$res['f_name'].' '.$res['l_name'].', CD Code# '.$res['cd_code'].', Phone# '.$res['phone'].'" readonly>
    </div>
    <script type="text/javascript">
      $(document).ready(function(){
        $("#save_custodial_cds").show();
        $("#symbolId").show();
        $("#volumeId").show();
      });
    </script>';
  }else{
    $sql = $dbh->prepare("SELECT c.f_name, c.l_name, c.phone, c.ID, c.cd_code FROM custodial_account c WHERE c.cd_code=:id");
    $sql->bindParam(':id', $id);
    $sql->execute();
    $res1 = $sql->fetch();
    if($sql->rowCount() > 0){
      echo'
      <div class="col-xs-8">
        <label>Details of Client</label>
        <input type="hidden" name="cdCode" id="cdCode" value="'.$res1['cd_code'].'">
        <input type="text" class="form-control" value="Name: '.$res1['f_name'].' '.$res1['l_name'].', CID# '.$res1['ID'].', Phone# '.$res1['phone'].'" readonly>
      </div>
      <script type="text/javascript">
        $(document).ready(function(){
          $("#save_custodial_cds").show();
          $("#symbolId").show();
          $("#volumeId").show();
        });
      </script>';
    }else{
      echo'
      <div class="col-xs-8">
        <label>Details of Client</label>
        <input type="text" class="form-control" value="No Data Available" readonly>
      </div>
      <script type="text/javascript">
        $(document).ready(function(){
          $("#save_custodial_cds").hide();
          $("#symbolId").hide();
          $("#volumeId").hide();
        });
      </script>';
    }
  }
}
else
{  
  echo "No Method Matching";
  exit();
}
?>
