<?php 
session_start();
$username = $_SESSION['sess_username'];
include ('../../CONNECTIONS/db.php');
// include ('../../CONNECTIONS/function-sanitize.php');
include('../../Functions/f.php');
date_default_timezone_set("Asia/Thimphu");

//Saving Record
if(isset($_POST['save_participant'])) {
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set error mode to exception
    $dbh->beginTransaction(); // begin transaction

    $Type = $_POST['Type'];
    $Pcode = $_POST['Pcode'];
    $Ins = $_POST['Ins'];
    $cp = $_POST['cp'];
    $add = $_POST['add'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $ca = $_POST['ca'];
    $org = $_POST['org'];
    $status = 1;
    
    $save = $dbh->prepare("INSERT INTO adm_participants(participant_type, participant_code, contact_person, institution_id, address, phone, email, clearing_account, name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $save->bindParam(1, $Type);
    $save->bindParam(2, $Pcode);
    $save->bindParam(3, $cp);
    $save->bindParam(4, $Ins);
    $save->bindParam(5, $add);
    $save->bindParam(6, $phone);
    $save->bindParam(7, $email);
    $save->bindParam(8, $ca);
    $save->bindParam(9, $org);
    $save->bindParam(10, $status);
    $save->execute();
    
    $dbh->commit(); // commit transaction
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div>';
    die();
  } catch(PDOException $e) {
    $dbh->rollback(); // rollback transaction on exception
    $dbh = null;
    echo '<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation. Error message: '.$e->getMessage().'</div></div>';
    die();
  }
}
elseif (isset($_POST['edit_parts']))
{ 
  /*$Type = filter_input(INPUT_POST, 'Type', FILTER_SANITIZE_STRING);
  $Pcode = filter_input(INPUT_POST, 'Pcode', FILTER_SANITIZE_STRING);
  $Ins = filter_input(INPUT_POST, 'Ins', FILTER_SANITIZE_STRING);
  $cp = filter_input(INPUT_POST, 'cp', FILTER_SANITIZE_STRING);
  $add = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
  $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $ca = filter_input(INPUT_POST, 'ca', FILTER_SANITIZE_STRING);
  $participant_id = filter_input(INPUT_POST, 'participantId', FILTER_SANITIZE_NUMBER_INT);
  $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);*/

  $Type = $_POST['Type'];
  $Pcode = $_POST['Pcode'];
  $Ins = $_POST['Ins'];
  $cp = $_POST['cp'];
  $add = $_POST['address'];
  $phone = $_POST['phone'];
  $email =$_POST['email'];
  $ca = $_POST['ca'];
  $participant_id = $_POST['participantId'];
  $status = $_POST['status'];

  if (!$Type || !$Pcode || !$Ins || !$cp || !$add || !$phone || !$email || !$ca || !$participant_id) {
    echo '<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error with input type. Please enter correct data.</div></div>';
    die();
  }
  try{
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE adm_participants SET participant_type=:Type,participant_code=:Pcode,contact_person=:cp,institution_id=:Ins,address=:add,phone=:ph,email=:email,clearing_account=:ca, status=:status where participant_id=:id ");
    $save->bindParam(':Type',$Type);
    $save->bindParam(':Pcode',$Pcode);
    $save->bindParam(':cp',$cp);
    $save->bindParam(':Ins',$Ins);
    $save->bindParam(':add',$add);
    $save->bindParam(':ph',$phone);
    $save->bindParam(':email',$email);
    $save->bindParam(':id',$participant_id);
    $save->bindParam(':status',$status);
    $save->bindParam(':ca',$ca);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    $save = null;

    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Record Updated Successfully.</div></div>';
    die();
  }catch(PDOException $e){
    $dbh->rollBack();
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation. => '.$e->getMessage().'</div></div>';
    die();
  }
}
elseif (isset($_POST['delete_participant'])) { 
  $participant_id = $_POST['delete_id'];
  try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $save = $dbh->prepare("DELETE FROM adm_participants WHERE participant_id=:id");
      $save->bindParam(':id', $participant_id);
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
  exit();
}
elseif (isset($_POST['save_users'])) {
  $name = $_POST['name'];
  $username = $_POST['un'];
  $role = $_POST['role'];
  $participant_code = $_POST['pcode'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $status = $_POST['status'];
  $address = $_POST['add'];
  $cid = $_POST['cid'];
  $log_check = "1";

  // Check if username already exists
  $check_username = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username=:username");
  $check_username->bindParam(':username', $username);
  $check_username->execute();
  if ($check_username->fetchColumn() > 0) {
      echo '<div class="col-lg-12 col-xs-12">
          <div class="alert alert-warning alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"></i> User Name Already Exists.
          </div>
      </div>';
      die();
  }
  // Insert new user
  try {
      // begin the transaction
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();
      
      // $password = md5($username);
      $hashedPassword = password_hash($username, PASSWORD_BCRYPT);
      $isBcrypt = 1;

      // get cd code 
      $stmt = $dbh->prepare("SELECT cd_code FROM client_account WHERE ID = ?");
      $stmt->execute([$cid]);
      $cd_code = $stmt->fetchColumn();
      
      $insert_user = $dbh->prepare("INSERT INTO users (name, username, password, role_id, participant_code, phone, email, status, log_check, address, cid, is_bcrypt, cd_code) VALUES (:name, :username, :pwd, :role, :participant_code, :phone, :email, :status, :log_check, :address, :cid, :bCrypt, :cdcode)");
      $insert_user->bindParam(':name', $name);
      $insert_user->bindParam(':username', $username);
      $insert_user->bindParam(':pwd', $hashedPassword);
      $insert_user->bindParam(':role', $role);
      $insert_user->bindParam(':participant_code', $participant_code);
      $insert_user->bindParam(':phone', $phone);
      $insert_user->bindParam(':email', $email);
      $insert_user->bindParam(':status', $status);
      $insert_user->bindParam(':log_check', $log_check);
      $insert_user->bindParam(':address', $address);
      $insert_user->bindParam(':cid', $cid);
      $insert_user->bindParam(':bCrypt', $isBcrypt);
      $insert_user->bindParam(':cdcode', $cd_code);
      $insert_user->execute();
      
      // commit the transaction
      $dbh->commit();

      if ($role == 4) {
        $emailBroker = '';
        $participantEmails = array(
            "MEMBNBL" => "karmachoden@bnb.bt",
            "MEMBOBL" => "tshering.wangmo2405@bob.bt",
            "MEMDSBP" => "drukyulsecurities@gmail.com",
            "MEMLDSB" => "lekpaydolmashares@gmail.com",
            "MEMSERS" => "sershingsecurities@gmail.com",
            "MEMRICB" => "sangay_tenzin2@ricb.bt",
            "MEMBDBL" => "kencho.wangmo@bdb.bt",
            "MEMRINS" => "rinsecurities@gmail.com",
            "MEMBPCL" => "ugyen.tshomo@bhutanpost.bt", 
        );

        if (isset($participantEmails[$participant_code])) {
            $emailBroker = $participantEmails[$participant_code];
        }

        include('emailLink.php');  
      } else {
        include('login_credentail_email.php');
      }

      echo '
      <div class="col-lg-12 col-xs-12">
          <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"></i> Operation Successfully Completed.
          </div>
      </div>';
      // close the database connection
      $insert_user = null;
      $dbh = null;
      
  } catch (PDOException $e) {
      // roll back the transaction
      $dbh->rollBack();
      echo '
      <div class="col-lg-12 col-xs-12">
          <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"></i> There was an error while operation ==> '.htmlspecialchars($e->getMessage()).'
          </div>
      </div>';
  }
  die();
}
elseif (isset($_POST['edit_users'])) { 
  // Validate user input
  if (!isset($_POST['name'], $_POST['phone'], $_POST['email'], $_POST['status'], $_POST['add'], $_POST['edit_users'])) {
    // http_response_code(400);
    echo'
    <div class="col-lg-12 col-xs-12">
      <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Fields empty, please check.
      </div>
    </div>';
    exit;
  }

  try{
    //variable declaration  
    $name = $_POST['name'];
    $phone=$_POST['phone'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $add = $_POST['add'];
    $user_id = $_POST['edit_user'];
    // !- variable declaration

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE users SET name=:name, email=:email, phone=:phone, status=:status, address=:add WHERE user_id=:id");
    $save->bindParam(':name', $name);
    $save->bindParam(':email', $email);
    $save->bindParam(':phone', $phone);
    $save->bindParam(':status', $status);
    $save->bindParam(':add', $add);
    $save->bindParam(':id', $user_id);
    $save->execute();

    $dbh->commit();
    $save = null;
    $dbh = null;

    echo'
    <div class="col-lg-12 col-xs-12">
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Record Updated Successfully.
      </div>
    </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'
    <div class="col-lg-12 col-xs-12">
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation=> '.$e->getMessage().'.
      </div>
    </div>';
  }
  die();
}
elseif (isset($_POST['delete_user'])) { 
  $user_id = $_POST['delete_usr'];
  try{
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();
    
    $save = $dbh->prepare("DELETE FROM users where user_id=:id");
    $save->bindParam(':id',$user_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    echo'
    <div class="col-lg-12 col-xs-12">
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp Record Deleted Successfully.
      </div>
    </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'
    <div class="col-lg-12 col-xs-12">
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation=> '.$e->getMessage().'.
      </div>
    </div>';
  }
  die();
}
elseif (isset($_POST['save_linkusers'])) { 
  $pcode = $_POST['pcode'];
  $ct = $_POST['ct'];
  $un = $_POST['un'];
  $bun = $_POST['bun'];
  $message = '';
  
  try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $sql = "SELECT client_code FROM linkuser WHERE client_code=:ct";
      $selectStmt = $dbh->prepare($sql);
      $selectStmt->bindParam(':ct', $ct);
      $selectStmt->execute();
      if ($selectStmt->rowCount() == 0) { 
        $insertStmt = $dbh->prepare("INSERT INTO linkuser(participant_code,client_code,username,broker_user_name) VALUES(:pcode, :ct, :un, :bun)");
        $insertStmt->bindParam(':pcode', $pcode);
        $insertStmt->bindParam(':ct', $ct);
        $insertStmt->bindParam(':un', $un);
        $insertStmt->bindParam(':bun', $bun);
        $insertStmt->execute();
      
        $dbh->commit();
        $dbh = null;
        $message =
            '<div class="col-lg-12 col-md-12">
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                  <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.
              </div>
            </div>';
      }else{
        $message = '
            <div class="col-lg-12 col-md-12">
              <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Link User Already Created.
              </div>
            </div>';
      }
  } catch(PDOException $e) {
    $dbh->rollBack();
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
    $message = '
          <div class="col-lg-12 col-xs-12">
              <div class="alert alert-error alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                  <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation ==> Error Code: '.$errorCode.', Error Message: '.$errorMessage.'
              </div>
          </div>';
  }
  echo $message;
  die();
}
elseif (isset($_POST['delete_link_users'])) { 
  $id = $_POST['delete_link_user'];
  $message = '';

  try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $deletestmt = $dbh->prepare("DELETE FROM linkuser where id=:id ");
      $deletestmt->bindParam(':id', $id);
      $deletestmt->execute();

      $dbh->commit();
      $dbh = null;
      $deletestmt = null;

      $message = '
        <div class="col-lg-12 col-md-12">
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Deleted User Successfully.
          </div>
        </div>';
      $data = array(
        'status' => 200,
        'success' => true,
        'message' => $message
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $errorMsg = $e->getMessage();
      $message = '
        <div class="col-lg-12 col-md-12">
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Error occurred ==> '.$errorMsg.'.
          </div>
        </div>';
      $data = array(
          'status' => 400,
          'success' => true,
          'message' => $message,
        );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif (isset($_POST['reset_pass'])) {
  $id = $_POST['reset_pass_val'];

  // $password = 'admin';
  $year = date("Y"); 
  $newPassword = 'adMin@'.$year;
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    // Hash the password using bcrypt
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $updatestmt = $dbh->prepare("UPDATE users SET password = :pwd, is_bcrypt = 1, log_check = 0 WHERE username = (SELECT username FROM linkuser WHERE id = :id)");
    $updatestmt->bindParam(':pwd', $hashedPassword, PDO::PARAM_STR);
    $updatestmt->bindParam(':id', $id, PDO::PARAM_INT);
    $updatestmt->execute();

    $dbh->commit();
    $dbh = null;
    $message = '
        <div class="col-lg-12 col-md-12">
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> Password Reset Successfully. New password => <b>'.$newPassword.'</b>
          </div>
        </div>';
  } catch(PDOException $e) {
      $dbh->rollBack();
      $message = '
        <div class="col-lg-12 col-md-12">
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> There was an error while operation ==> '.$e->getMessage().'
          </div>
        </div>';
  }
  echo $message;
  die();
} 
elseif (isset($_POST['unlock_account'])) { 
  $un=$_POST['username'];
  $message='';
  try{
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $stmt = $dbh->prepare("DELETE FROM login_attempts WHERE username=:un");
    $stmt->bindParam(':un',$un);
    $stmt->execute();

    $dbh->commit();
    $dbh = null;
    $message = '
        <div class="col-lg-12 col-xs-12">
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Account Unlocked Successfully.
          </div>
        </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $errorMsg = $e->getMessage();
    $message = '
        <div class="col-lg-12 col-xs-12">
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> Error occurred. == '.$errorMsg.'
          </div>
        </div>';
  }
  echo $message;
  die();
} 
elseif (isset($_POST['save_symbol'])) {
  $isin = $_POST['isin'];
  $sy = $_POST['sy'];
  $name = $_POST['name'];
  $sector = $_POST['sector'];
  $fv = $_POST['fv'];
  $pv = $_POST['pv'];
  $bl = $_POST['bl'];
  $pus = $_POST['pus'];
  $doe = $_POST['doe'];
  $dol = $_POST['dol'];
  $stype = $_POST['stype'];
  $message = '';

  if (in_array($stype, ['CP', 'GB', 'CB'])) {
    $matPeriod = $_POST['matPeriod'];
    $matDate = $_POST['matDate'];
    $cpnRate = $_POST['cpnRate'];
    $cpnPayable = $_POST['cpnPayable'];
    $issueDate = $_POST['issueDate'];

    if (!$matPeriod || $matPeriod === '' ) {
        echo'
        <div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Required Maturity Period</div></div>';
        exit;
    }
  } else {
    $matPeriod = 0;
    $matDate = NULL;
    $cpnRate = 0;
    $cpnPayable = 0;
    $issueDate = NULL;
  }
  $status=$_POST['status'];
  $trsstatus=1;

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $selectStmt = $dbh->prepare("SELECT COUNT(*) as count FROM symbol WHERE symbol=:sym");
    $selectStmt->bindParam(':sym', $sy);
    $selectStmt->execute();
    if($selectStmt->fetchColumn() == 0) {
      $save = $dbh->prepare("INSERT INTO symbol (isin, symbol, name, sector, face_value, premium_value, security_type, maturity_period, maturity_date, status, board_lot, paid_up_shares, date_of_listing, date_of_est, trsstatus, coupon_rates, coupon_payable, date_of_issue)
        VALUES (:isin, :sy, :name, :sector, :fv, :pv, :stype, :matPeriod, :matDate, :status, :bl, :pus, :dol, :doe, :trsstatus, :cpnRate, :cpnPayable, :issueDate)");
      $save->bindParam(':isin', $isin);
      $save->bindParam(':sy', $sy);
      $save->bindParam(':name', $name);
      $save->bindParam(':sector', $sector);
      $save->bindParam(':fv', $fv);
      $save->bindParam(':pv', $pv);
      $save->bindParam(':stype', $stype);
      $save->bindParam(':matPeriod', $matPeriod);
      $save->bindParam(':matDate', $matDate);
      $save->bindParam(':status', $status);
      $save->bindParam(':bl', $bl);
      $save->bindParam(':pus', $pus);
      $save->bindParam(':dol', $dol);
      $save->bindParam(':doe', $doe);
      $save->bindParam(':trsstatus', $trsstatus);
      $save->bindParam(':cpnRate', $cpnRate);
      $save->bindParam(':cpnPayable', $cpnPayable);
      $save->bindParam(':issueDate', $issueDate);
      $save->execute();

      if($stype == 'CB' || $stype == 'GB' || $stype == 'CP') {
        $sqlExe = $dbh->prepare("SELECT s.symbol_id, s.symbol, s.maturity_period, s.maturity_date FROM symbol s ORDER BY s.symbol_id DESC LIMIT 1");
        $sqlExe->execute();
        $res = $sqlExe->fetch();
        $symbolId = $res['symbol_id'];
        $symbolName = $res['symbol'];
        $couponDate = '';

        if($stype == 'CP') {
          $date = new DateTime($issueDate);

          $date->modify('+'.$matPeriod.' day');
          $couponDate = $date->format('Y-m-d');
          $issueDate = $couponDate;

          $insertSql = "INSERT INTO coupon_payable_date(symbol_id, symbol_name, payment_schedule, date, status) 
                        VALUES(:symbol_id, :symbol_name, '1', :coupon_date, '0')";
          $insert = $dbh->prepare($insertSql);
          $insert->bindParam(':symbol_id', $symbolId);
          $insert->bindParam(':symbol_name', $symbolName);
          $insert->bindParam(':coupon_date', $couponDate);
          $insert->execute();

        } else {
          for ($i=1; $i<=$matPeriod; $i++) {
            $date = new DateTime($issueDate);
            if($cpnPayable == 1) {
              if($i == 1) {
                $date->modify('+1 year');
                $date->modify('-1 day');
              } else {
                $date->modify('+1 year');
              }
            } elseif($cpnPayable == 2) {
              $date->modify('+6 month');
              if ($i == 1) {
                $date->modify('-1 day');
              } else {
                if ($i%2 == 0) {
                  $date->modify('+1 day');
                } else {
                  $date->modify('-1 day');
                }
              }
            }

            /*if($cpnPayable==1)
            {
              $date->modify('+1 year');
            }
            elseif($cpnPayable==2)
            {
              $date->modify('+6 month');
            }
            elseif($cpnPayable==3)
            {
              $date->modify('+3 month');
            }*/
            //$date->modify('-1 day');

            $couponDate = $date->format('Y-m-d');
            $issueDate = $couponDate;

            $insertSql = "INSERT INTO coupon_payable_date(symbol_id, symbol_name, payment_schedule, date, status) 
                          VALUES(:symbol_id, :symbol_name, :iterate, :coupon_date, '0')";
            $insert = $dbh->prepare($insertSql);
            $insert->bindParam(':symbol_id', $symbolId);
            $insert->bindParam(':symbol_name', $symbolName);
            $insert->bindParam(':iterate', $i);
            $insert->bindParam(':coupon_date', $couponDate);
            $insert->execute();
          }
        }
      }
      $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div>';
    } else { 
      $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Symbol Already Exists.</div></div>';
    }

    $dbh->commit();
  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation. ==> '.$e->getMessage().', on '.$e->getline().'</div></div>';
  }
  $dbh = null;
  echo $message;
  die();
}
/*elseif (isset($_POST['delete_symbol']))
{ 
//variable declaration  
$symbol_id=$_POST['delete_symbol'];
// !- variable declaration   
$save = $dbh->prepare("DELETE from  symbol where symbol_id=:id ");
$save->bindParam(':id',$symbol_id);
   if($save->execute())
      {
          header('location: ../FILES/Admin-dashboard.php?ms=5');
      }  
   else
      {
           header('location: ../FILES/Admin-dashboard.php?ms=2');
      }  
}*/
elseif (isset($_POST['edit_symbol'])) { 
  //variable declaration  
  $isin = $_POST['isin'];
  $sy = $_POST['sy'];
  $name = $_POST['name'];
  $sector = $_POST['sector'];
  $fv = $_POST['fv'];
  $pv = $_POST['pv'];
  $bl = $_POST['bl'];
  $pus = $_POST['pus'];
  $doe = $_POST['doe'];
  $dol = $_POST['dol'];
  $stype = $_POST['stype'];
  $status = $_POST['status'];
  $symbol_id = $_POST['symId'];

  $matPeriod = $_POST['matPeriod'];
  $matDate = $_POST['matDate'];
  $issueDate = $_POST['issueDate'];
  $cpnRate = $_POST['cpnRate'];
  $cpnPayable = $_POST['cpnPayable'];
  // !- variable declaration
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE symbol SET isin=:isin, symbol=:sy, name=:name, sector=:sector, face_value=:fv, premium_value=:pv, security_type=:stype, status=:status, board_lot=:bl, paid_up_shares=:pus, date_of_listing=:dol, date_of_est=:doe, maturity_date=:matDate, maturity_period=:matPeriod, date_of_issue=:issueDate, coupon_rates=:cpnRate, coupon_payable=:cpnPayable WHERE symbol_id=:id");
    $save->bindParam(':isin',$isin);
    $save->bindParam(':sy',$sy);
    $save->bindParam(':name',$name);
    $save->bindParam(':sector',$sector);
    $save->bindParam(':fv',$fv);
    $save->bindParam(':pv',$pv);
    $save->bindParam(':stype',$stype);
    $save->bindParam(':status',$status);
    $save->bindParam(':bl',$bl);
    $save->bindParam(':pus',$pus);
    $save->bindParam(':dol',$dol);
    $save->bindParam(':doe',$doe);
    $save->bindParam(':matDate',$matDate);
    $save->bindParam(':matPeriod',$matPeriod);
    $save->bindParam(':issueDate',$issueDate);
    $save->bindParam(':cpnRate',$cpnRate);
    $save->bindParam(':cpnPayable',$cpnPayable);
    $save->bindParam(':id',$symbol_id);
    $save->execute();
    
    $dbh->commit();
    $dbh = null;
    $save = null;

    $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Record Updated Successfully.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation. ==> '.$e->getMessage().'</div></div>';
  }
  echo $message;
  die();

} elseif (isset($_POST['save_institute'])) {
  $inst_id = date("ymdhis");
  $I_Name = $_POST['Ins_Name'];
  $Address = $_POST['Address'];
  $gst_reg = $_POST['gst_reg'];

  // $C_Person = $_POST['C_Person'];
  // $Phone = $_POST['Phone'];
  // $ca = $_POST['ca'];
  
  try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $save = $dbh->prepare("INSERT INTO adm_institution(institution_id, name, address, gst_register) VALUES(:inc, :ins_name, :address, :gst)");
      $save->bindParam(':inc', $inst_id);
      $save->bindParam(':ins_name', $I_Name);
      $save->bindParam(':address', $Address);
      $save->bindParam(':gst', $gst_reg);
      $save->execute();
      
      $dbh->commit();
      $dbh = null;
      $save = null;

      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Operation Successfully Completed.</div></div>';
  } catch(PDOException $e) {
      $dbh->rollBack();
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Oops Sorry! There was an error while operation. ==> '.$e->getMessage().'</div></div>';
  }
  exit();
}
elseif (isset($_POST['edit_ins'])) {
  $inc = date("ymdhis");
  $i_name = $_POST['ins_name'];
  $address = $_POST['address'];
  $institution_id = $_POST['inst_id'];
  $gst_register = $_POST['gst_register'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE adm_institution SET name = :name, address = :ad, gst_register = :gst WHERE institution_id = :id");
    $save->bindParam(':name', $i_name);
    $save->bindParam(':ad', $address);
    $save->bindParam(':gst', $gst_register);
    $save->bindParam(':id', $institution_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    $save = null;
    
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Record Updated Successfully.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation.==>'.$e->getMessage().'</div></div>';
  }
  exit();
}
elseif (isset($_POST['delete_institute'])) { 
  $institution_id = $_POST['institute_id'];
  try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $save = $dbh->prepare("DELETE FROM adm_institution WHERE institution_id=:id");
      $save->bindParam(':id',$institution_id);
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
      $dbh->rollBack();
      $data = array(
          'status' => 400,
          'success' => false,
          'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }

  header('Content-Type: application/json');
  echo json_encode($data);
  die();
}
elseif(isset($_POST['save_price'])) { 
  $price = $_POST['price'];
  $id = $_POST['sy'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $select = $dbh->prepare("SELECT COUNT(*) FROM market_price WHERE symbol_id=:id");
    $select->bindParam(':id', $id);
    $select->execute();
    if ($select->fetchColumn() <= 0) {
      $save = $dbh->prepare("INSERT INTO market_price(symbol_id, market_price) VALUES(:id, :price)");
      $save->bindParam(':id', $id);
      $save->bindParam(':price', $price);
      $save->execute();
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div>';
    } else {
       echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Price for this symbol already defined.</div></div>'; 
    }

    $dbh->commit();
    $dbh = null;
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'
      <div class="col-lg-12 col-xs-12">
        <div class="alert alert-error alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation. ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  die();
}
elseif(isset($_POST['update_price'])) { 
  $price = $_POST['mp'];
  $id = $_POST['id'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE market_price SET market_price=:mp WHERE id=:id");
    $save->bindParam(':mp', $price);
    $save->bindParam(':id', $id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-xs-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'
      <div class="col-lg-12 col-xs-12">
        <div class="alert alert-error alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation == '.$e->getMessage().'
        </div>
      </div>';
  }
  die();
}
elseif(!empty($_POST["cancle_id"])) {
  $id = $_POST["cancle_id"];
  $fid = $_POST["fid"];
  $v = $_POST["v"];
  $side = $_POST["side"];
  $cd_code = $_POST["cd_code"];
  $sy_id = $_POST["sy_id"];
  
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    if ($side == 'S') {
        $cds_acc=$dbh->prepare("UPDATE cds_holding SET pending_out_vol=pending_out_vol-:v,volume=volume+:v WHERE cd_code=:cdcode and symbol_id=:sy_id");
        $cds_acc->bindParam(':v',$v);
        $cds_acc->bindParam(':cdcode',$cd_code);
        $cds_acc->bindParam(':sy_id',$sy_id);
        $cds_acc->execute();
    } elseif ($side == 'B') {
      /*$cds_acc=$dbh->prepare("UPDATE cds_holding SET pending_in_vol=pending_in_vol-:v WHERE cd_code=:cdcode and symbol_id=:sy_id");
      $cds_acc->bindParam(':v',$v);
      $cds_acc->bindParam(':cdcode',$cd_code);
      $cds_acc->bindParam(':sy_id',$sy_id);
      $cds_acc->execute();*/
    }

    $order_date1 = $dbh->prepare("SELECT max(order_date) as od FROM orders_audit WHERE flag_id=:fid");
    $order_date1->bindParam(':fid', $fid);
    $order_date1->execute();
    $of = $order_date1->fetch();
    $o_date = $of['od'];

    $order_cancle_status = $dbh->prepare("UPDATE orders_audit set flag='C',username=:un WHERE flag_id=:fid and order_date = :od");
    $order_cancle_status->bindParam(':un', $username);
    $order_cancle_status->bindParam(':fid', $fid);
    $order_cancle_status->bindParam(':od', $o_date);
    $order_cancle_status->execute();

    $order_cancle = $dbh->prepare("DELETE FROM orders WHERE order_id=:id");
    $order_cancle->bindParam(':id', $id);
    $order_cancle->execute();

    $bbo_fin_del=$dbh->prepare("DELETE FROM bbo_finance WHERE flag_id=:fid");
    $bbo_fin_del->bindParam(':fid', $fid);
    $bbo_fin_del->execute();
    
    $dbh->commit();
    $dbh = null;

    $data = array(
        'status' => 200,
        'success' => true,
        'message' => '<div class="col-lg-12 col-xs-12">
                        <div class="alert alert-success alert-dismissible">
                          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Order for <b>'.$cd_code.'</b> Deleted Successfully.
                        </div>
                      </div>',
    );
  } catch (PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    $data = array(
        'status' => 200,
        'success' => true,
        'message' => '<div class="col-lg-12 col-xs-12">
                        <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                          <i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Error! ==> '.$e->getMessage().'
                        </div>
                      </div>',
    );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} elseif (isset($_POST['update_cid'])) { 
  $cidNo = $_POST['cidNo'];
  $cdCode = $_POST['cdCode'];
  $name = $_POST['name'];
  $oldcid = $_POST['oldcid'];
  $remark = $_POST['remark'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $sql = $dbh->prepare("SELECT a.ID FROM client_account a WHERE a.cd_code=:cd");
    $sql->bindParam(':cd',$cdCode);
    $sql->execute();
    $res = $sql->fetch();
    $cidExit = $res['ID'];

    if ($cidExit == $cidNo) {
      echo'
      <div class="col-lg-12 col-md-12"><div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"> </i> CID number already existed.</div>
      </div>';
      die();
    } else {
      $stmt = $dbh->prepare("INSERT INTO update_cid_log(cd_code, name, old_cid, new_cid, remark) VALUES(:cdCode, :name, :oldcid, :cidNo, :remark)");
      $stmt->bindParam(':cdCode', $cdCode);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':oldcid', $oldcid);
      $stmt->bindParam(':cidNo', $cidNo);
      $stmt->bindParam(':remark', $remark);
      $stmt->execute();

      $update = $dbh->prepare("UPDATE client_account a SET a.ID=:id WHERE a.cd_code=:cdCode");
      $update->bindParam(':id', $cidNo);
      $update->bindParam(':cdCode', $cdCode);
      $update->execute();

      $dbh->commit();
      $stmt = null;
      $update = null;
      $dbh = null;

      echo'
        <div class="col-lg-12 col-md-12">
          <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
          <i class="icon fa fa-check"></i> CID updated Successfully.</div>
        </div>';
    }
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'
      <div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation == '.$e->getMessage().'.</div>
      </div>';
  }
  die();
} 
elseif (isset($_POST['admApprove'])) { 
  $sysDateTime = date("Y-m-d H:i:s"); 

  $cid = $_POST['cid'];
  $name = $_POST['name'];
  $role = $_POST['role'];
  $pCode = $_POST['pCode'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  //$email = 'tashi.dendup@rsebl.org.bt';
  $status = $_POST['status'];
  $address = $_POST['address'];

  $un = $_POST['online_un'];
  //$pwd = md5($_POST['online_un'];
  $pwd = md5($un);
  $onlineUsrId = $_POST['onlineUsrId'];
  $user_name = $_POST['username'];
  $a_code = $_POST['admApprove'];
  $log_check = "1";

  $emailBroker='';
  //Setting Broker mail
  if($pCode == "MEMBNBL"){$emailBroker='dorjizangmo@bnb.bt';}
    elseif($pCode == "MEMBOBL"){$emailBroker='tshering.wangmo2405@bob.bt';}
      elseif($pCode == "MEMDSBP"){$emailBroker='drukyulsecurities@gmail.com';}
        elseif($pCode == "MEMLDSB"){$emailBroker='lekpaydolmashares@gmail.com';}
          elseif($pCode == "MEMSERS"){$emailBroker='sershingsecurities@gmail.com';}
            elseif($pCode == "MEMRICB"){$emailBroker='kuenzang_choden@ricb.bt';}
                elseif($pCode == "MEMBDBL"){$emailBroker='phub.thinley@bdb.bt';}
                  elseif($pCode == "MEMRINS"){$emailBroker='rinsecurities@gmail.com';}

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    //select broker user name and CD code from client account;
    $select = $dbh->prepare("SELECT c.cd_code, c.user_name, c.ID FROM client_account c WHERE c.ID=:cid AND c.user_name LIKE :pCode");
    $select->bindParam(':cid', $cid);
    $select->bindValue(':pCode', '%' . $pCode . '%');
    $select->execute();
    $result = $select->fetch();
    $cd_Code = $result['cd_code'];
    $brokerUser = $result['user_name'];

    //variable declaration
    $save = $dbh->prepare("SELECT * FROM users WHERE username=:uname");
    $save->bindParam(':uname', $un);
    $save->execute();
    // check whether user is already existed or not
    if ($save->rowCount() == 0) {
        $sql = $dbh->prepare("INSERT INTO api_online_terminal_audit(user_online_id, cid, cd_code, name, participant_code, phone, email, address, declaration, broker_user, status, app_fee, fee_status, order_no, created_date) SELECT a.user_online_id, a.cid, a.cd_code, a.name, a.participant_code, a.phone,a.email, a.address, a.declaration, a.broker_user, a.status, a.app_fee, a.fee_status, a.order_no,  a.created_date FROM api_online_terminal a WHERE a.user_online_id=:id");
        $sql->bindParam(':id', $onlineUsrId);
        $sql->execute();


        $query = $dbh->prepare("UPDATE api_online_terminal a SET a.status=:ap, a.broker_user=:usn, a.created_date=:sysDaTi WHERE a.user_online_id=:id");
        $query->bindParam(':ap', $a_code);
        $query->bindParam(':usn', $user_name);
        $query->bindParam(':sysDaTi', $sysDateTime);
        $query->bindParam(':id', $onlineUsrId);
        $query->execute();

        $sqlInsert = "INSERT INTO users(name, username, password, role_id, participant_code, phone, email, status, log_check, address, cid) VALUES(:name1, :un1, :pwd1, :role1, :pCode1, :phone1, :email1, :status1, :log_check1, :address1, :cid1)";
        $insert = $dbh->prepare($sqlInsert);
        $insert->bindParam(':name1', $name);
        $insert->bindParam(':un1', $un);
        $insert->bindParam(':pwd1', $pwd);
        $insert->bindParam(':role1', $role);
        $insert->bindParam(':pCode1', $pCode);
        $insert->bindParam(':phone1', $phone);
        $insert->bindParam(':email1', $email);
        $insert->bindParam(':status1', $status);
        $insert->bindParam(':log_check1', $log_check);
        $insert->bindParam(':address1', $address);
        $insert->bindParam(':cid1', $cid);
        $insert->execute();

        $sqlInsert2 = "INSERT INTO linkuser(participant_code, client_code, username, broker_user_name) VALUES (:pCode2, :cd_Code2, :un2, :brokerUser2)";
        $insert2 = $dbh->prepare($sqlInsert2);
        $insert2->bindParam(':pCode2', $pCode);
        $insert2->bindParam(':cd_Code2', $cd_Code);
        $insert2->bindParam(':un2', $un);
        $insert2->bindParam(':brokerUser2', $brokerUser);
        $insert2->execute();

        include('emailLink.php');

        $dbh->commit();
        $dbh = null;

        header('location: ../FILES/userList.php?ms=1');
    } else { 
      $dbh = null;
      header('location: ../FILES/userList.php?ms=4'); // user existed
      die();
    }
  } catch (PDOException $e) {
    $dbh->rollBack();
     header('location: ../FILES/userList.php?ms=3&errorMsg='.$e->getMessage());
  }
  die();
}
elseif (isset($_POST['admReject'])) { 
  $sysDateTime = date("Y-m-d H:i:s"); 
  
  $onlineUsrId = $_POST['onlineUsrId'];
  $user_name = $_POST['username'];
  $a_code = $_POST['admReject'];
  $log_check = "1";

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $sql=$dbh->prepare("INSERT INTO api_online_terminal_audit(user_online_id, cid, cd_code, name, participant_code, phone, email, address, declaration, broker_user, status, app_fee, fee_status, order_no, created_date) SELECT a.user_online_id, a.cid, a.cd_code, a.name, a.participant_code, a.phone,a.email, a.address, a.declaration, a.broker_user, a.status, a.app_fee, a.fee_status, a.order_no, a.created_date FROM api_online_terminal a WHERE a.user_online_id=:id");
    $sql->bindParam(':id', $onlineUsrId);
    $sql->execute();

    $update = $dbh->prepare("UPDATE api_online_terminal a SET a.status=:ap, a.broker_user=:usn, a.created_date=:sysDaTi WHERE a.user_online_id=:id");
    $update->bindParam(':ap', $a_code);
    $update->bindParam(':usn', $user_name);
    $update->bindParam(':sysDaTi', $sysDateTime);
    $update->bindParam(':id', $onlineUsrId);
    $update->execute();

    $dbh->commit();
    $dbh = null;

    header('location: ../FILES/userList.php?ms=2');
  } catch(PDOException $e) {
    $dbh->rollBack();
    header('location: ../FILES/userList.php?ms=3&errorMsg='.$e->getMessage());
  }
  die();
} 
elseif (isset($_POST['getOrdersOutOfRange'])) { 
  $sysDateTime = date("Y-m-d H:i:s"); 
  
  $symbol = $_POST['symbol'];
  $side = $_POST['side'];
  $arr = [];

  if($side == 'S'){
    $arr = ['S'];
  } elseif ($side == 'B'){
    $arr = ['B'];
  } else {
    $arr = ['B','S'];
  }

  // Joining array elements using implode() function
  /*$names = implode('\', \'', $arr);
  $fin = "'" . $names . "'";*/
  $usernames_str = "'" . implode("','", $arr) . "'";

  $sqlQuery = "SELECT a.order_id, a.cd_code, a.side, a.flag_id, a.member_broker, a.order_entry, a.price, a.sell_vol, a.buy_vol, a.order_date, b.symbol, b.symbol_id 
        FROM symbol b 
        JOIN orders a on  a.symbol_id = b.symbol_id ";
  if ($symbol !== 'ALL') {
      $sqlQuery .= "WHERE a.symbol_id = :symId ";
  }
  $sqlQuery .= "AND a.side IN ($usernames_str) 
          ORDER BY order_date DESC";
  $query = $dbh->prepare($sqlQuery);
  if ($symbol !== 'ALL') {
      $query->bindParam(':symId', $symbol);
  }

  $query->execute();
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  $i = 1;
  echo'
  <div class="table-responsive">
    <table id="cancel_table" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th></th>
          <th>SYMBOL</th>
          <th>CD CODE</th>
          <th>BROKER</th>
          <th>ENTRY</th>
          <th>PRICE</th>
          <th>VOLUME</th>
          <th>SIDE</th>
          <th>TIME</th>
        </tr>
      </thead>
      <tbody>';
      foreach ($result as $res) {
        $cap_name = 'CAP';
        $market_price = market_price($res['symbol_id']); 
        $cap = circuit($cap_name);
        $cap_value = round(cap_compute($market_price, $cap), 2);
        $low_p = round($market_price - $cap_value, 2);
        $high_p = round($market_price + $cap_value, 2);


        $bg_color = ($res['price'] < $low_p || $res['price'] > $high_p) ? 'red' : (($res['side'] == 'S') ? '#e8d4d7' : '#dce2e9');
        $volume = $res['side'] == 'S' ? $res['sell_vol'] : $res['buy_vol'];
        $side_text = $res['side'] == 'S' ? 'SELL' : 'BUY';

        echo'
        <tr style="background-color:' . $bg_color . ';" data-id="' . $i . '" data-side="' . $res['side'] . '" data-cd_code="' . $res['cd_code'] . '" data-flag_id="' . $res['flag_id'] . '" data-symbol_id="' . $res['symbol_id'] . '" data-volume="' . $volume . '">
            <td>
              <input type="checkbox" class="delete_checkbox" value="' . $res['order_id'] . '" style="height: 25px; width: 20px;">
            </td>
            <td>' . $res['symbol'] . '</td>
            <td>' . $res['cd_code'] . '</td>
            <td>' . $res['member_broker'] . '</td>
            <td>' . $res['order_entry'] . '</td>
            <td>' . $res['price'] . '</td>
            <td>' . $volume . '</td>
            <td>' . $side_text . '</td>
            <td>' . $res['order_date'] . '</td>
        </tr>';
        $i++;
      }
      echo'
      </tbody>
    </table>
  </div>

   <div class="col-lg-12 text-right">
    <button id="delete_selected" type="button" class="btn btn-danger btn-lg"><i class="fa fa-trash-o"></i> Delete Orders</button>
  </div>

  <script type="text/javascript">
    $( document ).ready(function() {
      $("#cancel_table").DataTable({
         "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]
      });

      $("#delete_selected").click( function () {
        showLoading();
        var selectedItems = [];

        $(".delete_checkbox:checked").each( function () {
            var itemData = {
                order_id: $(this).val(),
                side: $(this).closest("tr").data("side"),
                cd_code: $(this).closest("tr").data("cd_code"),
                symbol_id: $(this).closest("tr").data("symbol_id"),
                volume: $(this).closest("tr").data("volume"),
                flag_id: $(this).closest("tr").data("flag_id")
            };
            selectedItems.push(itemData);
        });

        if (!selectedItems || selectedItems.length === 0) {
            hideloading();
            alert("Select order to delete.");
            return false;
        } else {
            if (confirm("Do you want to continue?")) {
                var op = "delete__pending__order";
                $.ajax({
                    url: "../PROCESS/process.php",
                    type: "POST",
                    data: {items: selectedItems, delete__pending__order: op},
                    dataType: "json",
                    success: function(response) {
                        hideloading();
                        $("#message").html(response.message);
                        if (response.status === 200) {
                            $(".delete_checkbox:checked").closest("tr").remove();
                        } 
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        hideloading();
                        alert("Error deleting items: " + textStatus);
                    }
                });
            } else {
              hideloading();
              return false;
            }
        }
      });
    });
  </script>';
  die();
}
elseif (isset($_POST['delete__pending__order'])) {
    $items = $_POST['items'];

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        foreach ($items as $item) {
          $order_id = $item['order_id'];
          $side = $item['side'];
          $cd_code = $item['cd_code'];
          $symbol_id = $item['symbol_id'];
          $volume = $item['volume'];
          $flag_id = $item['flag_id'];

          if ($side == 'S') {
            // update cds_holding
            $update = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = pending_out_vol - :vol, volume = volume + :vol 
                WHERE cd_code = :cdcode AND symbol_id = :sy_id
            ");
            $update->bindParam(':vol', $volume);
            $update->bindParam(':cdcode', $cd_code);
            $update->bindParam(':sy_id', $symbol_id);
            $update->execute();
          }

          // Get the max order date
          $stmt = $dbh->prepare("SELECT max(order_date) as od FROM orders_audit WHERE flag_id = ?");
          $stmt->execute([$flag_id]);
          $ord_date =  $stmt->fetchColumn();

          // Update orders_audit
          $update_order_audit = $dbh->prepare("UPDATE orders_audit SET flag = 'C', username = ? WHERE flag_id = ? AND order_date = ?");
          $update_order_audit->execute([$username, $flag_id, $ord_date]);

          // Delete from orders
          $delete_order = $dbh->prepare("DELETE FROM orders WHERE order_id = ?");
          $delete_order->execute([$order_id]);

          // Delete from bbo_finance
          $bbo_fin_del = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = ?");
          $bbo_fin_del->execute([$flag_id]);

          // Delete DR entry from mcams if exist
          $b_order = $dbh->prepare("DELETE FROM mcams_wallet WHERE flag_id = ?");
          $b_order->execute([$flag_id]);
        }

        $dbh->commit();
        $dbh = null;
        
        $color = 'success';
        $icon = 'check';
        $message = 'Successfully Deleted';
        $status = 200;

    } catch (PDOException $e) {
        $dbh->rollBack();
        $dbh = null;

        $color = 'danger';
        $icon = 'times';
        $message = $e->getMessage();
        $status = 400;
    }

    $alert_message = '<div class="col-lg-12 col-xs-12">
                        <div class="alert alert-'.$color.' alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                          <i class="icon fa fa-'.$icon.'"></i> '.$message.'
                        </div>
                      </div>';
    // Create JSON response
    $data = [
      "status" => $status,
      "message" => $alert_message,
      "data" => []
    ];

    header('Content-Type: application/json');
    $json_data = json_encode($data);

    echo $json_data;
    die();
}
elseif(isset($_POST['check_schedular_existed'])) {
  $data = [];
  try {
    $check = $dbh->prepare("SELECT * FROM task_schedulars");
    $check->execute();
    if($check->rowCount() > 0){
      $select = $dbh->prepare("SELECT schedular_name, task_code, remarks, status FROM task_schedulars WHERE task_code='TS'");
      $select->execute();
      $row = $select->fetch();

      $data['trade_mode'] = $row['status'];
      $data['remarks'] = $row['remarks'];
      $data['tradeCode'] = $row['task_code'];

      $select22 = $dbh->prepare("SELECT schedular_name, task_code, remarks, status FROM task_schedulars WHERE task_code='IS'");
      $select22->execute();
      $row22 = $select22->fetch();

      $data['index_mode'] = $row22['status'];
      $data['indexCode'] = $row22['task_code'];

      $data['success'] = 'true';
      $data['message'] = 'success';
      $data['status'] = 200;
      $data['data'] = $data;
    } else {
      $data['success'] = 'false';
      $data['message'] = 'fail';
      $data['status'] = 400;
      $data['data'] = $data;
    }
  } catch (PDOException $e) {
    $data['success'] = 'false';
    $data['message'] = 'fail';
    $data['status'] = 400;
    $data['data'] = '';
  }
  header("Content-Type: application/json");
  echo json_encode($data);
  die();
} 
elseif(isset($_POST['set_schedular_mode'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $trade_mode = $_POST['trade_mode'];
  $index_mode = $_POST['index_mode'];
  // $tradeCode = $_POST['tradeCode'];
  // $indexCode = $_POST['indexCode'];
  $remarks = $_POST['remarks'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $tradeLogSql = $dbh->prepare("INSERT INTO task_schedulars_logs(schedular_name, task_code, remarks, status) VALUES ('Trade Schedular', 'TS', '$remarks', '$trade_mode')");
    $tradeLogSql->execute();

    $indexLogSql = $dbh->prepare("INSERT INTO task_schedulars_logs(schedular_name, task_code, remarks, status) VALUES ('Trade Schedular', 'IS', '$remarks', '$index_mode')");
    $indexLogSql->execute();

    $check = $dbh->prepare("SELECT * FROM task_schedulars");
    $check->execute();
    if ($check->rowCount() > 0) {
      $updateTrade = $dbh->prepare("UPDATE task_schedulars SET remarks=:remarks, status=:status, updated_date=:updDate where task_code='TS'");
      $updateTrade->bindParam(':remarks', $remarks);
      $updateTrade->bindParam(':status', $trade_mode);
      $updateTrade->bindParam(':updDate', $sysDateTime);
      // $updateTrade->bindParam(':tskcode', $tradeCode);
      $updateTrade->execute();

      $updateIndex = $dbh->prepare("UPDATE task_schedulars SET remarks=:remarks, status=:status1, updated_date=:updDate1 where task_code='IS'");
      $updateIndex->bindParam(':remarks', $remarks);
      $updateIndex->bindParam(':status1', $index_mode);
      $updateIndex->bindParam(':updDate1', $sysDateTime);
      // $updateIndex->bindParam(':tskcode1', $indexCode);
      $updateIndex->execute();

      $dbh->commit();
      $dbh = null;
      echo'
      <div class="col-lg-12 col-sm-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Updated Schedular Successfully.
        </div>
      </div>';
    } else {
      $insertTrade = $dbh->prepare("INSERT INTO task_schedulars(schedular_name, task_code, remarks, status) VALUES ('Trade Schedular', 'TS', '$remarks', '$trade_mode')");
      $insertTrade->execute();

      $insertIndex = $dbh->prepare("INSERT INTO task_schedulars(schedular_name, task_code, remarks, status) VALUES ('Index Schedular', 'IS', '$remarks', '$index_mode')");
      $insertIndex->execute();

      $dbh->commit();
      $dbh = null;
      echo'
      <div class="col-lg-12 col-sm-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Submitted Schedular Successfully.
        </div>
      </div>';
    }
  } catch (PDOException $e) {
    $dbh->rollBack();
    echo'
    <div class="col-lg-12 col-sm-12">
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Error occurred. Please try again later!!!
      </div>
    </div>';
  }
  die();
} elseif(isset($_POST['get_schedular_logs'])) {
  $row = $_POST['row'];

  if ($row == 'ALL') {
    $select = $dbh->prepare("SELECT * FROM task_schedulars_logs ORDER BY id DESC");
  } else {
    $select = $dbh->prepare("SELECT * FROM task_schedulars_logs ORDER BY id DESC LIMIT $row");
  }
  $select->execute();
  $result = $select->fetchAll(PDO::FETCH_ASSOC);
  echo'
  <div class="col-lg-12">
    <div class="table-response">
      <table id="logTableId" class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">Sl No</th>
            <th scope="col">Schedular Name</th>
            <th scope="col">Task Code</th>
            <th scope="col">Status</th>
            <th scope="col">Remarks</th>
            <th scope="col">Date</th>
          </tr>
        </thead>
        <tbody>';
        $i=1;
        foreach ($result as $row) {
          echo'
          <tr>
            <th scope="row">'.$i.'</th>
            <td>'.$row['schedular_name'].'</td>
            <td>'.$row['task_code'].'</td>
            <td>'.$row['status'].'</td>
            <td>'.$row['remarks'].'</td>
            <td>'.$row['created_date'].'</td>
          </tr>';
        $i++;
        }
        echo'
        </tbody>
      </table>
    </div>
  </div>
  <script type="text/javascript">
    $( document ).ready(function() {
      $("#logTableId").DataTable();
    });
  </script>';
  die();
} elseif(isset($_POST['save_role'])) {
  $name = $_POST['name'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("INSERT INTO role_masters(role_name, status) VALUES(:name, :status)");
    $insert->bindParam(':name', $name);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;
    header('location: ../FILES/user_role.php?ms=1');
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    header('location: ../FILES/user_role.php?ms=2');
  }
  die();
} elseif(isset($_POST['delete_role'])) {
  $name = $_POST['name'];
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE From role_masters WHERE id=:id");
    $delete->bindParam(':id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    echo'
    <div class="col-lg-12 col-md-12">
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Deleted Role Successfully.</div>
    </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'
    <div class="col-lg-12 col-md-12">
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Error. Message => '.$e.'</div>
    </div>';
  }
  die();
} elseif(isset($_POST['update_role'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $name = $_POST['name'];
  $status = $_POST['status'];
  $id = $_POST['id'];
  try{
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE role_masters SET role_name=:roleName, status=:status, updated_at=:udate WHERE id=:id");
    $update->bindParam(':roleName', $name);
    $update->bindParam(':status', $status);
    $update->bindParam(':udate', $sysDateTime);
    $update->bindParam(':id', $id);
    $update->execute();

    $dbh->commit();
    $dbh = null;
    header('location: ../FILES/user_role.php?ms=3');
  }catch(PDOException $e){
    $dbh->rollBack();
    $dbh = null;
    header('location: ../FILES/user_role.php?ms=2');
  }
  die();
} elseif(isset($_POST['save_sector'])) {
  $name = $_POST['name'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("INSERT INTO sector_masters(name, status) VALUES(:name, :status)");
    $insert->bindParam(':name', $name);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Oops Sorry! There was no function operation.</div></div>';
  }
  die();
} elseif(isset($_POST['update_sector'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $name = $_POST['name'];
  $status = $_POST['status'];
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE sector_masters SET name=:name, status=:status, updated_at=:uDate WHERE id=:id");
    $update->bindParam(':name', $name);
    $update->bindParam(':status', $status);
    $update->bindParam(':uDate', $sysDateTime);
    $update->bindParam(':id', $id);
    $update->execute();

    $dbh->commit();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> Successfully updated sector.
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  die();
} elseif(isset($_POST['delete_sector'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM sector_masters WHERE id=:id");
    $delete->bindParam(':id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200,
        'success' => true,
        'message' => 'Data deleted successfully.'
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400,
          'success' => false,
          'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif (isset($_POST['get_total_volume'])) {
  $symbol_id = $_POST['symbol_id'];
  $announcement_id = $_POST['announcement_id'];
  $announcement_type = $_POST['announcement_type'];
  $old_vol = 0;
  $new_vol = 0;

  try {
    $stmt = $dbh->prepare("SELECT SUM(s.ribon_volume) AS new_volume FROM spot_date_holding s WHERE s.symbol_id=:sym_id AND s.corp_announcement_id=:ann_id AND s.announcement_type=:ann_type");
    $stmt->bindParam(':sym_id', $symbol_id);
    $stmt->bindParam(':ann_id', $announcement_id);
    $stmt->bindParam(':ann_type', $announcement_type);
    $stmt->execute();
    $result = $stmt->fetch();
    $new_vol = $result['new_volume'];

    $sql = $dbh->prepare("SELECT SUM(c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) old_volume FROM cds_holding c WHERE c.symbol_id=:sy_id");
    $sql->bindParam(':sy_id', $symbol_id);
    $sql->execute();
    $row = $sql->fetch();
    $old_vol = $row['old_volume'];

  } catch (PDOException $e) {
    $dbh->rollBack();
  }
  $response = array(
    'old_vol' => $old_vol,
    'new_vol' => $new_vol,
  );
  echo json_encode($response);
  die();
} 
elseif(isset($_POST['corporateActionProcess'])) {
  $symbol_id = $_POST['symbol_id'];
  $announcement_id = $_POST['announcement_id'];
  $announcement_type = $_POST['announcement_type'];

  $total_old_vol = 0;
  $total_new_vol = 0;
  $updated_vol = 0;
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    // fetch all ribon volume from spot_date_holding table
    $orders = $dbh->prepare("
        SELECT DISTINCT s.sdh_id, h.cd_code, s.volume as spot_vol, s.ribon_volume, h.volume, h.pledge_volume, h.block_volume, h.pending_out_vol 
        FROM spot_date_holding s
        JOIN client_account c ON s.client_id = c.client_id
        JOIN cds_holding h ON c.cd_code = h.cd_code AND h.symbol_id = s.symbol_id 
        WHERE s.volume > 0 
          -- And s.ribon_volume > 0 
          AND s.status = 1 
          AND s.symbol_id = ?
          AND s.announcement_type = ?
          AND s.corp_announcement_id = ? 
    ");
    $orders->execute([$symbol_id, $announcement_type, $announcement_id]);
    $orders = $orders->fetchAll(PDO::FETCH_ASSOC);
    
    if ($announcement_type == 2) {
      // update shares in cds_holding table for corporate type Bonus
      foreach ($orders as $copy) {
        // $update = $dbh->prepare("UPDATE cds_holding SET volume = volume + :ribon WHERE cd_code = :cd AND symbol_id = :symbolId");
        $update = $dbh->prepare("UPDATE cds_holding SET volume = volume + :ribon, temporary_volume = :ribon WHERE cd_code = :cd AND symbol_id = :symbolId");
        $update->bindParam(':ribon', $copy['ribon_volume']);
        $update->bindParam(':cd', $copy['cd_code']);
        $update->bindParam(':symbolId', $symbol_id);
        $update->execute();

        $total_old_vol += $copy['spot_vol'];
        $total_new_vol += $copy['ribon_volume'];
      }
      $updated_vol = $total_old_vol + $total_new_vol;
    }
    elseif ($announcement_type == 4) {
      // update shares in cds_holding table for corporate type Buy Back
      $i = 0;
      // Prepare common update statements once
      $update_volume_stmt = $dbh->prepare("UPDATE cds_holding SET volume = ?, remarks = ? WHERE cd_code = ? AND symbol_id = ?");
      $update_pledge_stmt = $dbh->prepare("UPDATE cds_holding SET pledge_volume = ?, remarks = ? WHERE cd_code = ? AND symbol_id = ?");
      $update_block_stmt = $dbh->prepare("UPDATE cds_holding SET block_volume = ?, remarks = ? WHERE cd_code = ? AND symbol_id = ?");
      $update_pending_stmt = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = ?, remarks = ? WHERE cd_code = ? AND symbol_id = ?");
      $adjust_both_stmt = $dbh->prepare("UPDATE cds_holding SET pledge_volume = :pledge_volume, volume = 0, remarks = :remarks WHERE cd_code = :cd AND symbol_id = :symbol");

      foreach ($orders as $pro) {
          $i++;
          $cd_code = $pro['cd_code'];
          $ribon = $pro['ribon_volume'];

          $total_old_vol += $pro['spot_vol'];
          $total_new_vol += $ribon;

          if ($ribon < 1) continue;

          $remarks = 'buyback on ' . date('Y-m-d') . ' - ' . $ribon;
          $adjusted = false;

          if ($pro['volume'] >= $ribon) {
              $new_vol = $pro['volume'] - $ribon;
              $update_volume_stmt->execute([$new_vol, "From Vol $remarks", $cd_code, $symbol_id]);
              $adjusted = true;

          } elseif ($pro['pledge_volume'] >= $ribon) {
              $new_vol = $pro['pledge_volume'] - $ribon;
              $update_pledge_stmt->execute([$new_vol, "From Pledge $remarks", $cd_code, $symbol_id]);
              $adjusted = true;

          } elseif ($pro['block_volume'] >= $ribon) {
              $new_vol = $pro['block_volume'] - $ribon;
              $update_block_stmt->execute([$new_vol, "From Block $remarks", $cd_code, $symbol_id]);
              $adjusted = true;

          } elseif ($pro['pending_out_vol'] >= $ribon) {
              $new_vol = $pro['pending_out_vol'] - $ribon;
              $update_pending_stmt->execute([$new_vol, "From Pending $remarks", $cd_code, $symbol_id]);
              $adjusted = true;

              // Check for pending sell orders
              $order_stmt = $dbh->prepare("SELECT o.order_id, o.order_size, o.sell_vol, o.price, o.commis_amt, c.rate
                  FROM orders o
                  LEFT JOIN client_account a ON o.cd_code = a.cd_code
                  LEFT JOIN bbo_commission c ON a.bro_comm_id = c.bro_comm_id
                  WHERE o.symbol_id = ? AND o.cd_code = ? AND o.side = 'S'");
              $order_stmt->execute([$symbol_id, $cd_code]);

              if ($res = $order_stmt->fetch(PDO::FETCH_ASSOC)) {
                  $new_order_vol = $pro['pending_out_vol'] - $ribon;
                  $comm_amt = ($new_order_vol * $res['price']) * ($res['rate'] / 100);

                  $update_order = $dbh->prepare("UPDATE orders SET order_size = ?, sell_vol = ?, commis_amt = ? WHERE order_id = ?");
                  $update_order->execute([$new_order_vol, $new_order_vol, $comm_amt, $res['order_id']]);
              }
          } elseif (($pro['volume'] + $pro['pledge_volume']) >= $ribon) {
              // Adjust across both volume and pledge
              $new_volume = $pro['volume'] - $ribon;
              $pledge_volume = $pro['pledge_volume'];
              if ($new_volume < 0) {
                  $pledge_volume += $new_volume; // subtract remaining
                  $new_volume = 0;
              }
              if ($pledge_volume < 0) {
                  echo "Insufficient combined volume for {$cd_code} — Vol: {$pro['volume']}, Pledge: {$pro['pledge_volume']}<br>";
              }
              $adjust_both_stmt->execute([
                  ':pledge_volume' => max($pledge_volume, 0),
                  ':cd' => $cd_code,
                  ':remarks' => "From Vol/Pledge $remarks",
                  ':symbol' => $symbol_id,
              ]);
              $adjusted = true;
          }

          if (!$adjusted) {
              // echo "Could not adjust for {$cd_code} - Not enough volume<br>";
              error_log("Could not adjust for {$cd_code} - Not enough volume<br>");
          }

          // update temp_vol
          $update_temp_vol = $dbh->prepare("UPDATE cds_holding c SET c.temporary_volume = ? WHERE c.cd_code = ? AND c.symbol_id = ?");
          $update_temp_vol->execute([$ribon, $cd_code, $symbol_id]);

          // echo "CD code = " . $cd_code . ", spot_vol => ". $pro['spot_vol']. ", ribon_vol => ". $ribon . "<br>";
          error_log("CD code = " . $cd_code . ", spot_vol => ". $pro['spot_vol']. ", ribon_vol => ". $ribon . "<br>");
      }
      $updated_vol = $total_old_vol - $total_new_vol;
      error_log("Record Processed ==> {$i}");
    } // end of foreach 4

    error_log("Total Old Volume ==> {$total_old_vol}, Total Ribon Vol ==> {$total_new_vol}, Update New Total Volume ==> {$updated_vol}");

    // set status 0 in spot_date_holding table after updating shares in cds_holding table
    $updStatus = $dbh->prepare("UPDATE spot_date_holding SET status = 0 WHERE symbol_id = ? AND corp_announcement_id = ? AND announcement_type = ? AND status = 1");
    $updStatus->bindParam(1, $symbol_id);
    $updStatus->bindParam(2, $announcement_id);
    $updStatus->bindParam(3, $announcement_type);
    $updStatus->execute();

    // set status 0 in corporate_announcement table
    $updStatus2 = $dbh->prepare("UPDATE corporate_announcement SET status = 0 WHERE symbol_id = ? AND corp_announcement_id = ? AND announcement_type = ? AND status = 1");
    $updStatus2->bindParam(1, $symbol_id);
    $updStatus2->bindParam(2, $announcement_id);
    $updStatus2->bindParam(3, $announcement_type);
    $updStatus2->execute();

    $dbh->commit();
    
    echo'
    <div class="col-lg-12 col-md-12">
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> 
        Operation was successful.
        <br>Total Old Volume ==> '.$total_old_vol.'
        <br>Total Ribon Vol ==> '.$total_new_vol.'
        <br>Update New Total Volume ==> '.$updated_vol.'
      </div>
    </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    error_log("Error occurred ==> " . $e->getMessage());
    echo'
    <div class="col-lg-12 col-md-12">
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> 
        Exception occurred. Please contact RSEB IT Support.
      </div>
    </div>';
  }
  $dbh = null;
  die();
}
elseif(isset($_POST['save_circuit_breaker'])) {
  $name = $_POST['name'];
  $margin = $_POST['margin'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("INSERT INTO circuit_breaker(name, margin, status) VALUES(:name, :margin, :status)");
    $insert->bindParam(':name', $name);
    $insert->bindParam(':margin', $margin);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved Circuit Breaker.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  die();
} elseif(isset($_POST['update_cir_breaker'])) {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $margin = $_POST['margin'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("UPDATE circuit_breaker SET name=:name, margin=:margin, status=:status WHERE id=:id");
    $insert->bindParam(':name', $name);
    $insert->bindParam(':margin', $margin);
    $insert->bindParam(':status', $status);
    $insert->bindParam(':id', $id);
    $insert->execute();

    $dbh->commit();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully Updated Circuit Breaker.</div></div>';

  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  die();
} elseif(isset($_POST['save_occupation'])) {
  $name = $_POST['name'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("INSERT INTO occupation(occupation_name, status) VALUES(:name, :status)");
    $insert->bindParam(':name', $name);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved occupation.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  die();
} elseif(isset($_POST['update_occupation'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $name = $_POST['name'];
  $status = $_POST['status'];
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE occupation SET occupation_name=:name, status=:status, updated_at=:sdate WHERE occupation=:id");
    $update->bindParam(':name', $name);
    $update->bindParam(':status', $status);
    $update->bindParam(':sdate', $sysDateTime);
    $update->bindParam(':id', $id);
    $update->execute();

    $dbh->commit();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> Successfully updated occupation.
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  die();
} elseif(isset($_POST['delete_occupation'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM occupation WHERE occupation=:id");
    $delete->bindParam(':id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Data deleted successfully.'
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400, 'success' => false, 'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} elseif(isset($_POST['save_corporate'])) {
  $name = $_POST['name'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("INSERT INTO corporate_action_masters(corporate_name, status) VALUES(:name, :status)");
    $insert->bindParam(':name', $name);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved corporate action.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  die();
} elseif(isset($_POST['update_corporate'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $name = $_POST['name'];
  $status = $_POST['status'];
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE corporate_action_masters SET corporate_name=:name, status=:status, updated_at=:sdate WHERE id=:id");
    $update->bindParam(':name', $name);
    $update->bindParam(':status', $status);
    $update->bindParam(':sdate', $sysDateTime);
    $update->bindParam(':id', $id);
    $update->execute();

    $dbh->commit();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Successfully updated corporate.
      </div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  die();
} elseif(isset($_POST['delete_corporate'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM corporate_action_masters WHERE id=:id");
    $delete->bindParam(':id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Data deleted successfully.'
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400, 'success' => false, 'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif(isset($_POST['save_rights_offer'])) {
  $symbol_id = $_POST['symbol_id'];
  $corp_ann_type = $_POST['corp_ann_type'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $getCorpAnnId = $dbh->prepare("SELECT corp_announcement_id FROM corporate_announcement 
      WHERE symbol_id=:sym_id AND announcement_type=:annTypeId AND status=1 
      ORDER BY corp_announcement_id DESC");
    $getCorpAnnId->bindParam(':sym_id', $symbol_id);
    $getCorpAnnId->bindParam(':annTypeId', $corp_ann_type);
    $getCorpAnnId->execute();
    $rows = $getCorpAnnId->fetch();
    if (!$rows) {
      echo'
      <div class="col-lg-12 col-md-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> 
          No Corporate Announcement for symbol_id = '.$symbol_id.' and announcement type = '.$corp_ann_type.'
      </div></div>';
      die();
    }

    $insert = $dbh->prepare("INSERT INTO rights_offers(symbol_id, start_at, end_at, corp_announcement_id, announcement_type, status) 
                            VALUES(:sym_id, :s_date, :e_date, :corp_ann_id, :ann_type, :status)");
    $insert->bindParam(':sym_id', $symbol_id);
    $insert->bindParam(':s_date', $start_date);
    $insert->bindParam(':e_date', $end_date);
    $insert->bindParam(':corp_ann_id', $rows['corp_announcement_id']);
    $insert->bindParam(':ann_type', $corp_ann_type);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;

    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved rights offer.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  die();
} elseif(isset($_POST['update_rights_offer'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $id = $_POST['id'];
  $symbol_id = $_POST['symbol_id'];
  $corp_ann_type = $_POST['corp_ann_type'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE rights_offers 
        SET symbol_id=:symId, start_at=:s_date, end_at=:e_date, announcement_type=:ann_type, status=:stas, updated_at=:u_date 
        WHERE id=:id");
    $update->bindParam(':symId', $symbol_id);
    $update->bindParam(':s_date', $start_date);
    $update->bindParam(':e_date', $end_date);
    $update->bindParam(':ann_type', $corp_ann_type);
    $update->bindParam(':stas', $status);
    $update->bindParam(':u_date', $sysDateTime);
    $update->bindParam(':id', $id);
    $update->execute();

    $dbh->commit();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> Successfully updated rights offer
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  die();
}
elseif(isset($_POST['delete_rights_offer'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM rights_offers WHERE id=:id");
    $delete->bindParam(':id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Data deleted successfully.'
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400, 'success' => false, 'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif(isset($_POST['save_bond_offer'])) {
  $symbol_id = $_POST['symbol_id'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];
  $bond_type = $_POST['bond_type'];
  $sysDateTime = date("Y-m-d H:i:s");
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $check = $dbh->prepare("SELECT 1 FROM bond_offers WHERE symbol_id = ?");
    $check->bindParam(1, $symbol_id);
    $check->execute();

    if ($check->fetchColumn()) {
      $message = '<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-times"></i> Duplicate Bond.</div></div>';
      echo $message;
      exit;
    }

    $insert = $dbh->prepare("INSERT INTO bond_offers(symbol_id, start_bond_at, end_bond_at, status, created_at, type) VALUES(?, ?, ?, ?, ?, ?)");
    $insert->bindParam(1, $symbol_id);
    $insert->bindParam(2, $start_date);
    $insert->bindParam(3, $end_date);
    $insert->bindParam(4, $status);
    $insert->bindParam(5, $sysDateTime);
    $insert->bindParam(6, $bond_type);
    $insert->execute();

    $dbh->commit();

    $message = '<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved bond offer.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  $dbh = null;
  echo $message;
  die();
} elseif(isset($_POST['update_bond_offer'])) {
  $id = $_POST['id'];
  $symbol_id = $_POST['symbol_id'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];
  $bond_type = $_POST['type'];
  $sysDateTime = date("Y-m-d H:i:s");
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE bond_offers 
        SET symbol_id = ?, start_bond_at = ?, end_bond_at = ?, status = ?, updated_at = ?, type = ? 
        WHERE id = ?
    ");
    $update->bindParam(1, $symbol_id);
    $update->bindParam(2, $start_date);
    $update->bindParam(3, $end_date);
    $update->bindParam(4, $status);
    $update->bindParam(5, $sysDateTime);
    $update->bindParam(6, $bond_type);
    $update->bindParam(7, $id);
    $update->execute();

    $dbh->commit();
    
    $message = '
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> Successfully Updated Bond Offer
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  $dbh = null;
  echo $message;
  die();
} 
elseif(isset($_POST['delete_bond_offer'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM bond_offers WHERE id = ?");
    $delete->bindParam(1, $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200, 'success' => true, 'message' => 'Bond deleted successfully.'
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400, 'success' => false, 'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif(isset($_POST['save_auction_offer'])) {
  $symbol_id = $_POST['symbol_id'];
  $offer_vol = $_POST['offer_vol'];
  $min_price = $_POST['min_price'];
  $max_price = $_POST['max_price'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $getSymbol = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id=:sym_id");
    $getSymbol->bindParam(':sym_id', $symbol_id);
    $getSymbol->execute();
    $row = $getSymbol->fetch();
    $symbolName = $row['symbol'];

    $insert = $dbh->prepare("INSERT INTO share_auctions(symbol_id, symbol, offer_volume, start_price, max_price, auction_date, end_date, status) 
                            VALUES(:sym_id, :sym, :vol, :s_price, :m_price, :auc_date, :en_date, :status)");
    $insert->bindParam(':sym_id', $symbol_id);
    $insert->bindParam(':sym', $symbolName);
    $insert->bindParam(':vol', $offer_vol);
    $insert->bindParam(':s_price', $min_price);
    $insert->bindParam(':m_price', $max_price);
    $insert->bindParam(':auc_date', $start_date);
    $insert->bindParam(':en_date', $end_date);
    $insert->bindParam(':status', $status);
    $insert->execute();

    $dbh->commit();
    $dbh = null;

    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved auction.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    echo'<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  die();
}
elseif(isset($_POST['delete_auction_offer'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM share_auctions WHERE id=:id");
    $delete->bindParam(':id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200, 'success' => true, 'message' => '<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Deleted Share Auction.</div></div>',
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400, 'success' => false, 'message' => '<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> 
            There was issue operation==>'.$e->getMessage().'</div></div>',
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
} 
elseif(isset($_POST['update_share_auction'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $id = $_POST['id'];
  $symbol_id = $_POST['symbol_id'];
  $offer_vol = $_POST['offer_vol'];
  $min_price = $_POST['min_price'];
  $max_price = $_POST['max_price'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];
  $message = '';

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $getSymbol = $dbh->prepare("SELECT symbol FROM symbol WHERE symbol_id=:sym_id");
    $getSymbol->bindParam(':sym_id', $symbol_id);
    $getSymbol->execute();
    $row = $getSymbol->fetch();
    $symbol = $row['symbol'];

    $update = $dbh->prepare("UPDATE share_auctions 
        SET symbol_id=:symId, symbol=:sym, offer_volume=:vol, start_price=:mn_price, max_price=:mx_price, auction_date=:s_date, end_date=:e_date, status=:stas, updated_at=:u_date 
        WHERE id=:id");
    $update->bindParam(':symId', $symbol_id);
    $update->bindParam(':sym', $symbol);
    $update->bindParam(':vol', $offer_vol);
    $update->bindParam(':mn_price', $min_price);
    $update->bindParam(':mx_price', $max_price);
    $update->bindParam(':s_date', $start_date);
    $update->bindParam(':e_date', $end_date);
    $update->bindParam(':stas', $status);
    $update->bindParam(':u_date', $sysDateTime);
    $update->bindParam(':id', $id);
    $update->execute();

    $dbh->commit();
    $dbh = null;
    $message = '
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> Successfully updated share auction.
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $dbh = null;
    $message = '
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  echo $message;
  die();
}
elseif(isset($_POST['save_ipo_offer'])) {
  $symbol_id = $_POST['symbol_id'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];
  $sysDateTime = date("Y-m-d H:i:s");
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $insert = $dbh->prepare("INSERT INTO ipo_offers(symbol_id, start_at, end_at, status, created_at) VALUES(?, ?, ?, ?, ?)");
    $insert->bindParam(1, $symbol_id);
    $insert->bindParam(2, $start_date);
    $insert->bindParam(3, $end_date);
    $insert->bindParam(4, $status);
    $insert->bindParam(5, $sysDateTime);
    $insert->execute();

    $dbh->commit();

    $message = '<div class="col-lg-12 col-md-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully saved IPO offer.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '<div class="col-lg-12 col-md-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was issue operation==>'.$e->getMessage().'</div></div>';
  }
  $dbh = null;
  echo $message;
  die();
} 
elseif(isset($_POST['update_ipo_offer'])) {
  $id = $_POST['id'];
  $symbol_id = $_POST['symbol_id'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $status = $_POST['status'];
  $sysDateTime = date("Y-m-d H:i:s");
  $message = '';
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $update = $dbh->prepare("UPDATE ipo_offers 
        SET symbol_id = ?, start_at = ?, end_at = ?, status = ?, updated_at = ?
        WHERE id = ?
    ");
    $update->bindParam(1, $symbol_id);
    $update->bindParam(2, $start_date);
    $update->bindParam(3, $end_date);
    $update->bindParam(4, $status);
    $update->bindParam(5, $sysDateTime);
    $update->bindParam(6, $id);
    $update->execute();

    $dbh->commit();
    
    $message = '
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> Successfully Updated IPO Offer
        </div>
      </div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    $message = '
      <div class="col-lg-12 col-md-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"></i> There was an error ==> '.$e->getMessage().'
        </div>
      </div>';
  }
  $dbh = null;
  echo $message;
  die();
} 
elseif(isset($_POST['delete_ipo_offer'])) {
  $id = $_POST['id'];
  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM ipo_offers WHERE id = ?");
    $delete->bindParam(1, $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;
    $data = array(
        'status' => 200, 'success' => true, 'message' => 'IPO deleted successfully.'
    );
  } catch(PDOException $e) {
      $dbh->rollBack();
      $data = array(
          'status' => 400, 'success' => false, 'message' => 'Error Occurred ==> '.$e->getMessage(),
      );
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  die();
}
elseif (isset($_POST['save_email_confirmation'])) { 
  $email = $_POST['email'];
  $institute_id = $_POST['institute_id'];
  $member = $_POST['member'];
  $purpose = $_POST['purpose'];
  $e_type = $_POST['e_type'];
  $status = 1;

  try {
    $save = $dbh->prepare("INSERT INTO email_confirmation (email_add, institute_id, mem_code, email_for, email_recipient_type, status) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $save->execute([$email, $institute_id, $member, $purpose, $e_type, $status]);

    if ($result) {
        $message = [
            'type' => 'success',
            'text' => 'Operation Successfully Completed.',
            'icon' => 'fa-check'
        ];
    } else {
        throw new Exception('Database insertion failed.');
    }
  } catch (Exception $e) {
      $message = [
          'type' => 'danger',
          // 'text' => 'Oops Sorry! There was an error while operation.',
          'text' => $e->getMessage(),
          'icon' => 'fa-times'
      ];
  }
  echo'
  <div class="col-lg-12 col-xs-12">
      <div class="alert alert-'.$message['type'].' alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <i class="icon fa '.$message['icon'].'"></i> '.$message['text'].' 
      </div>
  </div>';
  die();
}
elseif (isset($_POST['deleteEmailConfirmation'])) { 
  $id = $_POST['id'];

  try {
    $save = $dbh->prepare("DELETE FROM email_confirmation WHERE id = ?");
    $result = $save->execute([$id]);

    if ($result) {
        $message = [
            'type' => 'success',
            'text' => 'Operation Successfully Completed.',
            'icon' => 'fa-check'
        ];
    } else {
        throw new Exception('Database Deletion Failed.');
    }
  } catch (Exception $e) {
      $message = [
          'type' => 'danger',
          'text' => $e->getMessage(),
          'icon' => 'fa-times'
      ];
  }
  echo'
  <div class="col-lg-12 col-xs-12">
      <div class="alert alert-'.$message['type'].' alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <i class="icon fa '.$message['icon'].'"></i> '.$message['text'].' 
      </div>
  </div>';
  die();
}
elseif (isset($_POST['update__assign__broker__dtls'])) {
    $id = $_POST['id'];
    $part_code = $_POST['part_code'];
    $brk_usr_name = $_POST['brk_usr_name'];
    $type = $_POST['type'];
    $symbol = $_POST['symbol'];
    $rate = $_POST['rate'];
    $status = $_POST['status'];

    try {
      $stmt = $dbh->prepare("UPDATE assign_broker SET participant_code = ?, username = ?, type = ?, symbol = ?, rate = ?, status = ? WHERE id = ?");
      $stmt->execute([$part_code, $brk_usr_name, $type, $symbol, $rate, $status, $id]);

      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully Updated.</div></div>';

    } catch (PDOException $e) {
      echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Error ==> '.$e->getMessage().'</div></div>';
    }
    die();
}
elseif (isset($_POST['delete__assigned__broker'])) {
    $id = $_POST['id'];

    header('Content-Type: application/json');
    $response = array();

    try {
      $stmt = $dbh->prepare("DELETE FROM assign_broker WHERE id = ?");
      $stmt->execute([$id]);

      $response['status'] = 'success';
      $response['message'] = '
        <div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Successfully Deleted.</div></div>';
    } catch (PDOException $e) {
      $response['status'] = 'error';
      $response['message'] = '
        <div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Error ==> '.$e->getMessage().'</div></div>';
    }
    echo json_encode($response);
    die();
}
elseif (isset($_POST['reset_pwd_from_adm'])) {
    $id = $_POST['user_pri_id'];

    $year = date("Y"); 
    $newPassword = 'adMin@'.$year;
    $message = '';

    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      // Hash the password using bcrypt
      $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

      $updatestmt = $dbh->prepare("UPDATE users SET password = ?, is_bcrypt = 1 WHERE user_id = ? AND role_id != 4");
      $updatestmt->bindParam(1, $hashedPassword, PDO::PARAM_STR);
      $updatestmt->bindParam(2, $id, PDO::PARAM_INT);
      $updatestmt->execute();

      $dbh->commit();
      $dbh = null;
      $message = '
          <div class="col-lg-12 col-md-12">
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> Password Reset Successfully. <br>New password => <b>' . $newPassword . '</b>
            </div>
          </div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        $message = '
          <div class="col-lg-12 col-md-12">
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> There was an error while operation ==> ' . $e->getMessage() . '
            </div>
          </div>';
    }
    echo $message;
    die();
}
elseif (isset($_POST['set_user_inactive'])) {
    $id = $_POST['user_pri_id'];
    $message = '';

    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $updatestmt = $dbh->prepare("UPDATE users SET status = 0 WHERE user_id = ?");
      $updatestmt->bindParam(1, $id, PDO::PARAM_INT);
      $updatestmt->execute();

      $dbh->commit();
      $dbh = null;
      $message = '
          <div class="col-lg-12 col-md-12">
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> Successfully set user as inactive</b>
            </div>
          </div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        $message = '
          <div class="col-lg-12 col-md-12">
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> There was an error while operation ==> ' . $e->getMessage() . '
            </div>
          </div>';
    }
    echo $message;
    die();
}
elseif (isset($_POST['set_user_active'])) {
    $id = $_POST['user_pri_id'];
    $message = '';

    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $updatestmt = $dbh->prepare("UPDATE users SET status = 1 WHERE user_id = ?");
      $updatestmt->bindParam(1, $id, PDO::PARAM_INT);
      $updatestmt->execute();

      $dbh->commit();
      $dbh = null;
      $message = '
          <div class="col-lg-12 col-md-12">
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> Successfully set user as active</b>
            </div>
          </div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        $message = '
          <div class="col-lg-12 col-md-12">
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> There was an error while operation ==> ' . $e->getMessage() . '
            </div>
          </div>';
    }
    echo $message;
    die();
}
else {
  echo'
    <div class="col-lg-12 col-md-12">
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Oops Sorry! There was no function operation.
      </div>
    </div>';
  die();
}
?>