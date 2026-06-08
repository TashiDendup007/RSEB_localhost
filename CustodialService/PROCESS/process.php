<?php 
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');
date_default_timezone_set("Asia/Thimphu");
session_start();
$role = $_SESSION['sess_userrole'];
if( $role!="7")
{
  header('Location: ../../access.php?err=2');
  exit();
}
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
  { 
    header("Location: ../../Authentication/Logout.php"); 
    exit();
  }
} 
$_SESSION['timeout'] = time();
$username=$_SESSION['sess_username'];
$check= $dbh->prepare('SELECT a.institution_id FROM adm_institution a, adm_participants b,users c WHERE c.participant_code=b.participant_code AND b.institution_id=a.institution_id AND c.username=:un');
$check->bindParam(':un',$username);
$check->execute();
$res=$check->fetch();
$institution_id=$res['institution_id'];

//Saving Record
if(isset($_POST['save_custodial_cc']))
{ 
  $acctype = clean($_POST['acctype']);
  $cdCode=clean($_POST['cdCode']);
  $title = clean($_POST['title']);
  $fName = clean($_POST['fName']);
  $lName=clean($_POST['lName']);
  $occupation = clean($_POST['occupation']);
  $nationality = clean($_POST['nationality']);
  $cidNo=clean($_POST['cidNo']);
  $dz=clean($_POST['dz']);
  $phoneNo=clean($_POST['phoneNo']);
  $email = clean($_POST['email']);
  $tpnNo=clean($_POST['tpnNo']);
  $bankName=clean($_POST['bankName']);
  $accountNo=clean($_POST['accountNo']);
  $bankAccType=clean($_POST['bankAccType']);
  $address=clean($_POST['address']);
  $username=clean($_POST['save_custodial_cc']);


  $save = $dbh->prepare("INSERT INTO custodial_account(acc_type, cd_code, title, f_name, l_name, occupation, nationality, ID, DzongkhagID, tpn, phone, email, bank_id, bank_account, bank_account_type, address, institution_id, user_name)
    VALUES ('$acctype','$cdCode','$title','$fName','$lName','$occupation','$nationality','$cidNo','$dz','$tpnNo','$phoneNo','$email','$bankName','$accountNo','$bankAccType','$address','$institution_id','$username')");
  if($save->execute()) 
  {
    header('location: ../FILES/accountRegistration.php?ms=1');
    exit();
  }  
  else 
  {
    header('location: ../FILES/accountRegistration.php?ms=2');
    exit();
  }
}
elseif (isset($_POST['edit_custodial_dtls']))
{ 
  $id = clean($_POST['edit_custodial_dtls']);
  $getDtls = $dbh->prepare("SELECT c.*,  o.occupation_name occName, z.DzongkhagName dzoName, b.bank_name bankName, c.bank_id bankId
      FROM custodial_account c 
      LEFT JOIN occupation o ON c.occupation = o.occupation 
      LEFT JOIN tbldzongkhag z ON c.DzongkhagID = z.DzongkhagID
      LEFT JOIN banks b ON c.bank_id = b.bank_id
      WHERE c.client_Id=:idc");
  $getDtls->bindParam(":idc", $id);
  $getDtls->execute();
  $res = $getDtls->fetch();
  echo'
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Details</h4>
      </div>
      <form action="../PROCESS/process.php" method="POST" onsubmit="showLoading();">
      <div class="modal-body">
        <div class="box-body">
          <div class="row">  
            <div class="col-xs-4">
              <label>Account Type<font color="red">*</font></label>
              <select class="form-control" id="acctype" name="acctype">
                <option value="I">Individual</option>
              </select>
            </div>
            <div class="col-xs-4">
              <label>Cd Code<font color="red">*</font></label>
              <input type="text" class="form-control" name="cdCode" id="cdCode" maxlength="10" value="'.$res['cd_code'].'" readonly>
              <input type="hidden" class="form-control" name="clientId" id="clientId" value="'.$id.'">
            </div>
            <div class="col-xs-4">
              <label>Title<font color="red">*</font></label>
              <input type="text" class="form-control" name="title" id="title" value="'.$res['title'].'" required>
            </div>
            <div class="col-xs-4">
              <label>First Name<font color="red">*</font></label>
              <input type="text" class="form-control" name="fName" id="fName" value="'.$res['f_name'].'" required>
            </div>
            <div class="col-xs-4">
              <label>Last Name</label>
              <input type="text" class="form-control" name="lName" value="'.$res['l_name'].'" id="lName">
            </div>
            <div class="col-xs-4">
              <label>Occupation<font color="red">*</font></label>
              <select id="occupation" name="occupation" class="form-control" required>
                <option value="'.$res['occupation'].'" selected>'.$res['occName'].'</option>';
                $q=$dbh->prepare('SELECT * FROM occupation ORDER BY occupation_name ASC');
                $q->execute();
                foreach($q as $state)
                {
                  echo'<option value="'.$state['occupation'].'">'.$state['occupation_name'].'</option>';
                }
            echo'</select>
            </div>
            <div class="col-xs-4">
              <label>Nationality<font color="red">*</font></label>
              <input type="text" class="form-control" name="nationality" id="nationality" value="'.$res['nationality'].'" required>
            </div>
            <div class="col-xs-4">
              <label>CID No<font color="red">*</font></label>
              <input type="text" class="form-control" name="cidNo" id="cidNo" value="'.$res['ID'].'" required>
            </div>
            <div class="col-xs-4">
              <label>Dzongkhag<font color="red">*</font></label>
              <select id="dz" name="dz" class="form-control" required>
                <option value="'.$res['DzongkhagID'].'" selected>'.$res['dzoName'].'</option>';
                $q=$dbh->prepare('SELECT * FROM tbldzongkhag ORDER BY DzongkhagName ASC');
                $q->execute();
                foreach($q as $state)
                {
                  echo '<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
                }
              echo'</select>
            </div>
            <div class="col-xs-4">
              <label>Phone No<font color="red">*</font></label>
              <input type="text" class="form-control" name="phoneNo" id="phoneNo" value="'.$res['phone'].'" required>
            </div>
            <div class="col-xs-4">
              <label>Email</label>
              <input type="text" class="form-control" name="email" value="'.$res['email'].'" id="email">
            </div>
            <div class="col-xs-4">
              <label>TPN No</label>
              <input type="text" class="form-control" name="tpnNo" value="'.$res['tpn'].'" id="tpnNo">
            </div>
            <div class="col-xs-4">
              <label>Bank Name<font color="red">*</font></label>
              <select id="bankName" name="bankName" class="form-control" required>
                <option value="'.$res['bankId'].'" selected>'.$res['bankName'].'</option>';
                $q=$dbh->prepare('SELECT * FROM banks ORDER BY bank_name ASC');
                $q->execute();
                foreach($q as $state)
                {
                  echo '<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
                }
              echo'</select>
            </div>
            <div class="col-xs-4">
              <label>Bank Account Number<font color="red">*</font></label>
              <input type="text" class="form-control" name="accountNo" id="accountNo" value="'.$res['bank_account'].'" required>
            </div>
            <div class="col-xs-4">
              <label>Bank Account Type<font color="red">*</font></label>
              <select id="bankAccType" name="bankAccType" class="form-control" required>
                <option value="'.$res['bank_account_type'].'" selected>'.$res['bank_account_type'].'</option>
                <option value="Saving Account">Saving Account</option>
                <option value="Current Account">Current Account</option>
              </select>
            </div>
            <div class="col-xs-12">
              <label>Address<font color="red">*</font></label>
              <input type="text" class="form-control" name="address" id="address" value="'.$res['address'].'" required>
            </div>
          </div>
        </div>
        <!-- <div class="box-footer">
          <div class="col-xs-4"></div>
        </div> -->
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" style="" id="update_custodial_cc" name="update_custodial_cc" value="'.$_SESSION['sess_username'].'"><i class="fa fa-align-justify"></i> Update</button> &nbsp;
        <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button><br>
      </div>
      </form>
    </div>
  </div>';
}
else if(isset($_POST['update_custodial_cc']))
{ 
  $acctype = clean($_POST['acctype']);
  $cdCode = clean($_POST['cdCode']);
  $title = clean($_POST['title']);
  $fName = clean($_POST['fName']);
  $lName = clean($_POST['lName']);
  $occupation = clean($_POST['occupation']);
  $nat = clean($_POST['nationality']);
  $cidNo = clean($_POST['cidNo']);
  $dz = clean($_POST['dz']);
  $phoneNo = clean($_POST['phoneNo']);
  $email = clean($_POST['email']);
  $tpnNo = clean($_POST['tpnNo']);
  $bankName = clean($_POST['bankName']);
  $accountNo = clean($_POST['accountNo']);
  $bankAccType = clean($_POST['bankAccType']);
  $address = clean($_POST['address']);
  $username = clean($_POST['update_custodial_cc']);
  $clientId = clean($_POST['clientId']);


  $update = $dbh->prepare("UPDATE custodial_account SET acc_type=:acctype, title=:title, f_name=:fName, l_name=:lName, occupation=:occupation, nationality=:nat,ID=:cidNo, DzongkhagID=:dz, tpn=:tpnNo, phone=:phoneNo, email=:email, bank_id=:bankName, bank_account=:accountNo, bank_account_type=:bankAccType, address=:address, user_name=:username WHERE client_id=:clientId");
  $update->bindParam(':acctype',$acctype);
  $update->bindParam(':title',$title);
  $update->bindParam(':fName',$fName);
  $update->bindParam(':lName',$lName);
  $update->bindParam(':occupation',$occupation);
  $update->bindParam(':nat',$nat);
  $update->bindParam(':cidNo',$cidNo);
  $update->bindParam(':dz',$dz);
  $update->bindParam(':tpnNo',$tpnNo);
  $update->bindParam(':phoneNo',$phoneNo);
  $update->bindParam(':email',$email);
  $update->bindParam(':bankName',$bankName);
  $update->bindParam(':accountNo',$accountNo);
  $update->bindParam(':bankAccType',$bankAccType);
  $update->bindParam(':address',$address);
  $update->bindParam(':username',$username);
  $update->bindParam(':clientId',$clientId);
  if($update->execute()) 
  {
    header('location: ../FILES/accountRegistration.php?ms=1');
    exit();
  }  
  else 
  {
    header('location: ../FILES/accountRegistration.php?ms=2');
    exit();
  }
}
else if(isset($_POST['save_custodial_cds']))
{ 
  $cdCode = clean($_POST['cdCode']);
  $symId = clean($_POST['symbol']);
  $vol = clean($_POST['volume']);
  $userName = clean($_POST['save_custodial_cds']);

  $checkCdCode = $dbh->prepare("SELECT * FROM custodial_cds WHERE cd_code=:cdCode");
  $checkCdCode->bindParam(":cdCode", $cdCode);
  $checkCdCode->execute();
  if($checkCdCode->rowCount()){
    header('location: ../FILES/shareVolEntry.php?ms=4');
    //exit();
    die();
  }

  $save = $dbh->prepare("INSERT INTO custodial_cds(cd_code, symbol_id, volume, user_name) VALUES ('$cdCode','$symId','$vol','$userName')");
  if($save->execute()) 
  {
    header('location: ../FILES/shareVolEntry.php?ms=1');
    exit();
  }  
  else 
  {
    header('location: ../FILES/shareVolEntry.php?ms=2');
    exit();
  }
}
else
{
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
exit();
}
?>