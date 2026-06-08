<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');
date_default_timezone_set("Asia/Thimphu");
// include ('sessionStartFile_cdscss.php');

session_start();
$role = isset($_SESSION['sess_userrole']) ? $_SESSION['sess_userrole'] : 0;
$username = isset($_SESSION['sess_username']) ? $_SESSION['sess_username'] : '';

$role_array = array('1','2','3','4','5','6','7');
 if(!in_array($role, $role_array)) {
     header('Location: ../../access.php?err=2');
     exit;
 }
$inactive = 1500;
if(isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive) {
      header("Location: ../../Authentication/Logout.php");
      exit();
    }
}
$_SESSION['timeout'] = time();

$check = $dbh->prepare("
            SELECT a.institution_id, c.participant_code 
            FROM adm_institution a
            JOIN adm_participants b ON b.institution_id = a.institution_id
            JOIN users c ON c.participant_code = b.participant_code 
            WHERE c.username = :un
");
$check->bindParam(':un', $username);
$check->execute();
$res = $check->fetch();
$institution_id = isset($res['institution_id']) ? $res['institution_id'] : 0;
// check if session out
if ($institution_id == 0) {
    header('Location: ../../access.php?err=2');
    exit;
}

//Saving Record
if(isset($_POST['save_client_dtls'])) {
    $atype = $_POST['atype'];
    $cdcode = strtoupper($_POST['cdcode']);
    $title = $_POST['title'];
    $fn = $_POST['fn'];
    $ln = $_POST['ln'];
    $occupation = $_POST['occupation'];
    $nat = $_POST['nat'];
    $id = $_POST['id'];
    $dz = $_POST['dz'];
    $tpn = $_POST['tpn'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $bank = $_POST['bank'];
    $accno = $_POST['accno'];
    $bankAccType = $_POST['bankAccType'];
    $add = $_POST['add'];
    $commission = $_POST['commission'];
    $username = $_POST['username'];
    $licenseNo = $_POST['licenseNo'];
    $message = '';

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        // Check CD Code
        $check_cdCode = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE cd_code = :cd");
        $check_cdCode->bindParam(':cd', $cdcode);
        $check_cdCode->execute();
        $count_cdCode = $check_cdCode->fetchColumn();
        if ($count_cdCode > 0) {
            echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> CD Code already existed.</div></div></div>';
            die();
        }

        $save = $dbh->prepare("INSERT INTO client_account (acc_type, cd_code, title, f_name, l_name, occupation, nationality, ID, DzongkhagID, tpn, phone, email, bank_id,bank_account, bank_account_type, bro_comm_id, address, institution_id, user_name) 
            VALUES(:atype, :cdcode, :title, :fn, :ln, :occupation, :nat, :id, :dz, :tpn, :phone, :email, :bank, :accno, :bankAccType, :commiss, :add, :institution_id, :username)
        ");
        $save->bindParam(":atype", $atype);
        $save->bindParam(":cdcode", $cdcode);
        $save->bindParam(":title", $title);
        $save->bindParam(":fn", $fn);
        $save->bindParam(":ln", $ln);
        $save->bindParam(":occupation", $occupation);
        $save->bindParam(":nat", $nat);
        $save->bindParam(":id", $id);
        $save->bindParam(":dz", $dz);
        $save->bindParam(":tpn", $tpn);
        $save->bindParam(":phone", $phone);
        $save->bindParam(":email", $email);
        $save->bindParam(":bank", $bank);
        $save->bindParam(":accno", $accno);
        $save->bindParam(":bankAccType", $bankAccType);
        $save->bindParam(":commiss", $commission);
        $save->bindParam(":add", $add);
        $save->bindParam(":institution_id", $institution_id);
        $save->bindParam(":username", $username);
        $save->execute();

        $dbh->commit();
        $dbh = null;

        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Operation Successfully Completed.</div></div></div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        error_log("Error ===>> ".$e->getMessage().", line => ".$e->getLine());
        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation.</div></div></div>';
    }
    $dbh = null;
    echo $message;
    die();
} 
elseif (isset($_POST['save_client_dtls_new'])) {
    $atype = $_POST['atype'];
    $title = $_POST['title'];
    $fn = $_POST['fn'];
    $ln = $_POST['ln'];
    $occupation = $_POST['occupation'];
    $nat = $_POST['nat'];
    $id = $_POST['id'];
    $dz = $_POST['dz'];
    $tpn = $_POST['tpn'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $bank = $_POST['bank'];
    $accno = $_POST['accno'];
    $bankAccType = $_POST['bankAccType'];
    $add = $_POST['add'];
    $commission = $_POST['commission'];
    $username = $_POST['username'];
    $licenseNo = $_POST['licenseNo'];
    $gender = $_POST['gender'];
    $marital = $_POST['marital'];
    $dob = $_POST['dob'];
    $gewog_id = $_POST['gewog_id'];
    $village_id = $_POST['village_id'];
    $guardian_name = $_POST['guardian_name'];

    $message = '';
    $cdcode = '';
    $year = date('Y');
    $user_mem_code = substr($username, 0, 7);

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        // Check CID exists
        $checkId = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE ID = :id AND institution_id = :ins_idd");
        $checkId->bindParam(':id', $id);
        $checkId->bindParam(':ins_idd', $institution_id);
        $checkId->execute();
        $count_id = $checkId->fetchColumn();
        if ($count_id > 0) {
            echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> CID/DISN number already existed.</div></div></div>';
            die();
        }

        $stmt = $dbh->prepare("SELECT a.cd_code, a.ca_date, SUBSTRING(a.user_name, 1, 7) AS mem_code 
                    FROM client_account a 
                    WHERE SUBSTRING(a.user_name, 1, 7) = ? 
                    AND YEAR(a.ca_date) = ? 
                    AND a.cd_code LIKE 'CD%'
                    ORDER BY a.client_id DESC 
                    LIMIT 1
        ");
        $stmt->bindParam(1, $user_mem_code);
        $stmt->bindParam(2, $year);
        $stmt->execute();
        $rows = $stmt->fetch();

        if ($rows) {
            $last_cd_code = $rows['cd_code'];
            $mem_code = $rows['mem_code'];

            $prefix = substr($last_cd_code, 0, 2);
            $numeric_part = intval(substr($last_cd_code, 2));
            $cd_code_new_number = $numeric_part + 1;
            $cdcode = $prefix . str_pad($cd_code_new_number, strlen($last_cd_code) - 2, '0', STR_PAD_LEFT);

        } else {
            $short_year = date('y');
            $cdcode = 'CD' . $short_year. '000001';
        }

        // Check CD Code
        $check_cdCode = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE cd_code = :cd");
        $check_cdCode->bindParam(':cd', $cdcode);
        $check_cdCode->execute();
        $count_cdCode = $check_cdCode->fetchColumn();
        if ($count_cdCode > 0) {
            echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> CD Code already existed.</div></div></div>';
            die();
        }

        $save = $dbh->prepare("INSERT INTO client_account (acc_type, cd_code, title, f_name, l_name, occupation, nationality, ID, DzongkhagID, tpn, phone, email, bank_id, bank_account, bank_account_type, bro_comm_id, address, institution_id, user_name, license_no, dob, guardian_name, gender, marital_status, gewog_id, village_id) 
            VALUES(:atype, :cdcode, :title, :fn, :ln, :occupation, :nat, :id, :dz, :tpn, :phone, :email, :bank, :accno, :bankAccType, :commiss, :add, :institution_id, :username, :license_no, :dob, :guard_name, :gender, :marital, :gwg_id, :villg_id)
        ");
        $save->bindParam(":atype", $atype);
        $save->bindParam(":cdcode", $cdcode);
        $save->bindParam(":title", $title);
        $save->bindParam(":fn", $fn);
        $save->bindParam(":ln", $ln);
        $save->bindParam(":occupation", $occupation);
        $save->bindParam(":nat", $nat);
        $save->bindParam(":id", $id);
        $save->bindParam(":dz", $dz);
        $save->bindParam(":tpn", $tpn);
        $save->bindParam(":phone", $phone);
        $save->bindParam(":email", $email);
        $save->bindParam(":bank", $bank);
        $save->bindParam(":accno", $accno);
        $save->bindParam(":bankAccType", $bankAccType);
        $save->bindParam(":commiss", $commission);
        $save->bindParam(":add", $add);
        $save->bindParam(":institution_id", $institution_id);
        $save->bindParam(":username", $username);
        $save->bindParam(":license_no", $licenseNo);
        $save->bindParam(":dob", $dob);
        $save->bindParam(":guard_name", $guardian_name);
        $save->bindParam(":gender", $gender);
        $save->bindParam(":marital", $marital);
        $save->bindParam(":gwg_id", $gewog_id);
        $save->bindParam(":villg_id", $village_id);
        $save->execute();

        $dbh->commit();
        $dbh = null;

        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Successfully Registered. CD Code: '.$cdcode.'</div></div></div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        error_log("Error ===>> ".$e->getMessage().", line => ".$e->getLine());
        $message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation.</div></div></div>';
    }
    $dbh = null;
    echo $message;
    die();
} 
elseif (isset($_POST['edit_cli'])) {
    //variable declaration
    $atype = $_POST['atype'];
    $title = $_POST['title'];
    $fn = $_POST['fn'];
    $ln = $_POST['ln'];
    $occupation = $_POST['occupation'];
    $nat = $_POST['nat'];
    $dz = $_POST['dz'];
    $tpn = $_POST['tpn'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $bank = $_POST['bank'];
    $accno = $_POST['accno'];
    $bankAccType = $_POST['bankAccType'];
    $add = $_POST['add'];
    $cli_id = $_POST['edit_cli'];
    $id = $_POST['id'];

    $licenseNo = ($atype === 'I') ? '' : $_POST['licenseNo']; 
    $dob = ($atype === 'I') ? $_POST['dob'] : '1900-01-01';
    $guardian_name = ($atype === 'I') ? $_POST['guardian_name'] : '';

    $gender = ($atype === "I") ? $_POST['gender'] : '';
    $marital = ($atype === "I") ? $_POST['marital'] : '';
    $gewog_id = $_POST['gewog_id'];
    $village_id = $_POST['village_id'];
    // !- variable declaration

    //nominee start
   /* if ($atype=="I") {
        $n = clean($_POST['name']);
        $c = clean($_POST['cid']);
        $r = clean($_POST['relationship']);
        for($i=0; $i<count($_POST['name']); $i++) {
         $names = clean($_POST['name'][$i]);
         $cid = clean($_POST['cid'][$i]);
         $relationship = clean($_POST['relationship'][$i]);
         $id=clean($_POST['id']);
         $save = $dbh->prepare("INSERT INTO client_nominee(Nominee_name,Nominee_cid,Nominee_relation,ID) VALUES ('$names','$cid','$relationship','$id')");
         $save->execute();
        }
    }*/
    //nominee end

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $save = $dbh->prepare("UPDATE client_account 
                SET acc_type = :atype, title = :title, f_name = :fn, l_name = :ln, occupation = :occupation, nationality = :nat, DzongkhagID = :dz, tpn = :tpn, phone = :phone, email = :email,
                bank_id = :bank, bank_account = :accno, bank_account_type = :bankAccType, address = :add, license_no = :liceNo, dob = :dob, guardian_name = :guardian_name, gender = :gndr, marital_status = :mrl_stus, gewog_id = :gw_id, village_id = :vg_id  
                WHERE client_id = :id
        ");
        $save->bindParam(':atype',$atype);
        $save->bindParam(':title',$title);
        $save->bindParam(':fn',$fn);
        $save->bindParam(':ln',$ln);
        $save->bindParam(':occupation',$occupation);
        $save->bindParam(':nat',$nat);
        $save->bindParam(':dz',$dz);
        $save->bindParam(':tpn',$tpn);
        $save->bindParam(':phone',$phone);
        $save->bindParam(':email',$email);
        $save->bindParam(':bank',$bank);
        $save->bindParam(':accno',$accno);
        $save->bindParam(':bankAccType',$bankAccType);
        $save->bindParam(':add',$add);
        $save->bindParam(':liceNo', $licenseNo);
        $save->bindParam(':dob', $dob);
        $save->bindParam(':guardian_name', $guardian_name);
        $save->bindParam(':gndr', $gender);
        $save->bindParam(':mrl_stus', $marital);
        $save->bindParam(':gw_id', $gewog_id);
        $save->bindParam(':vg_id', $village_id);
        $save->bindParam(':id',$cli_id);
        $save->execute();

        $check = $dbh->prepare("SELECT * FROM users WHERE cid=:cid");
        $check->bindParam(":cid", $id);
        $check->execute();
        if ($check->fetch()) {
            $update = $dbh->prepare("UPDATE users SET email=:eml WHERE cid=:cidd");
            $update->bindParam(":eml", $email);
            $update->bindParam(":cidd", $id);
            $update->execute();
        }

        $dbh->commit();
        $dbh = null;
        header('location: ../FILES/account_reg.php?ms=3');
    } catch(PDOException $e) {
        $dbh->rollBack();
        header('location: ../FILES/account_reg.php?ms=2');
    }
    exit();
}
elseif (isset($_POST['delete_cli'])) {
    //variable declaration
    $client_id  = $_POST['delete_cli'];
    // !- variable declaration
    $save = $dbh->prepare("DELETE from  client_account where client_id=:id ");
    $save->bindParam(':id',$client_id);
    if($save->execute()) {
        header('location: ../FILES/cds-css-landing.php?ms=5');
    } else {
        header('location: ../FILES/cds-css-landing.php?ms=2');
    }
    exit();
}
elseif (isset($_POST['delete_nom']))
{
    //variable declaration
    $id  = $_POST['delete_nom'];
    // !- variable declaration
    $save = $dbh->prepare("DELETE from  client_nominee where nominee_id=:id ");
    $save->bindParam(':id',$id);
    if($save->execute()) {
           echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp Record Deleted Successfully.</div></div></div>';
          exit();
    } else {
           echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
           exit();
    }
}
//bbo finance start
elseif (isset($_POST['mat'])) {
    //variable declaration
    $cd_code = $_POST['cdcode'];
    $sy = $_POST['sy'];
    $hol = $_POST['hol'];
    $rm = $_POST['rm'];
    $type = 'DEPOSIT';
    $cd_code=strtoupper($cd_code);
    // !- variable declaration

    $saveT = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cdCode, :sym, :vol, :usr, :inst_id, :remaks, :typ)");
    $saveT->bindParam(":cdCode", $cd_code);
    $saveT->bindParam(":sym", $sy);
    $saveT->bindParam(":vol", $hol);
    $saveT->bindParam(":usr", $username);
    $saveT->bindParam(":inst_id", $institution_id);
    $saveT->bindParam(":remaks", $rm);
    $saveT->bindParam(":typ", $type);
    $saveT->execute();

    $q = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cd AND symbol_id=:sy");
    $q->bindParam(':cd', $cd_code);
    $q->bindParam(':sy', $sy);
    $q->execute();
    if($q->rowCount() > 0) {
        $row = $q->fetch();
        $e_vol = $row['volume'];
        $new_vol = $e_vol + $hol;

        $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol WHERE cd_code=:cd AND symbol_id=:sy");
        $save->bindParam(':vol', $new_vol);
        $save->bindParam(':cd', $cd_code);
        $save->bindParam(':sy', $sy);
        if ($save->execute()) {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
        } else {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }
    } else {
        $save = $dbh->prepare("INSERT INTO cds_holding(cd_code, volume, user_name, institution_id, symbol_id, remarks) 
            VALUES (:cdCode, :vol, :usr, :inst_id, :sym, :typ)");
        $save->bindParam(":cdCode", $cd_code);
        $save->bindParam(":vol", $hol);
        $save->bindParam(":usr", $username);
        $save->bindParam(":inst_id", $institution_id);
        $save->bindParam(":sym", $sy);
        $save->bindParam(":typ", $type);
        if ($save->execute()) {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
        } else {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }
    }
    exit();
}
elseif (isset($_POST['demat'])) {
    //variable declaration
    $cd_code = $_POST['cdcode'];
    $sy = $_POST['sy'];
    $hol = $_POST['hol'];
    $rm = $_POST['rm'];
    $type = 'WITHDRAW';
    // !- variable declaration

    // insert into cds_dep_wit
    $saveT = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) 
        VALUES(:cdCode, :sym, -:vol, :usr, :inst_id, :remaks, :typ)");
    $saveT->bindParam(":cdCode", $cd_code);
    $saveT->bindParam(":sym", $sy);
    $saveT->bindParam(":vol", $hol);
    $saveT->bindParam(":usr", $username);
    $saveT->bindParam(":inst_id", $institution_id);
    $saveT->bindParam(":remaks", $rm);
    $saveT->bindParam(":typ", $type);
    $saveT->execute();

    $q=$dbh->prepare("SELECT a.*, b.symbol FROM cds_holding a, symbol b WHERE a.cd_code=:cd AND a.symbol_id=:sy AND a.symbol_id=b.symbol_id");
    $q->bindParam(':cd',$cd_code);
    $q->bindParam(':sy',$sy);
    $q->execute();
    if ($q->rowCount() > 0) {
       $row = $q->fetch();
       $e_vol = $row['volume'];
       $symbol = $row['symbol'];
       $new_vol = $e_vol - $hol;

       if( $new_vol >= 0) {
            $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol WHERE cd_code=:cd AND symbol_id=:sy");
            $save->bindParam(':vol', $new_vol);
            $save->bindParam(':cd', $cd_code);
            $save->bindParam(':sy', $sy);
            $row = $save->execute();
            if($save->execute()) {
                echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
            } else {
                echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            }
        } elseif ($new_vol < 0) {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp The Client ,'.$cd_code. ' has only '.$e_vol.' , of '.$sy.'</div></div></div>';
        }
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There are no shares to Withdraw</div></div></div>';
    }
    exit();
}
//transfer start
elseif (isset($_POST['transfer'])) {
    //variable declaration
    $F_cd = strtoupper($_POST['F_cd']);
    $T_cd = strtoupper($_POST['T_cd']);
    $sy = $_POST['sy'];
    $rm = $_POST['remarks'];
    $vol = $_POST['trs'];
    $user_name = $_POST['userName'];
    // !- variable declaration

    //To check existing volume
    $q22 = $dbh->prepare("SELECT volume FROM cds_holding WHERE cd_code=:cd and symbol_id=:id");
    $q22->bindParam(':cd',$F_cd);
    $q22->bindParam(':id',$sy);
    $q22->execute();
    $row22 = $q22->fetch();
    $vol_existing = $row22['volume'];

    if ($vol_existing < $vol) {
        echo'
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                    Insufficient Volume to be Transferred
                </div>
            </div>
        </div>';
        die();
    }

    // check if same ID exist or not
    $stmt = $dbh->prepare("SELECT 1 FROM client_account WHERE cd_code IN (?, ?) GROUP BY ID HAVING COUNT(DISTINCT cd_code) = 2");
    $stmt->execute([$F_cd, $T_cd]);
    $same_person = $stmt->fetchColumn();
    if (!$same_person) {
        echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-check"></i> Cant do share posting to different individual. </div></div>';
        exit();
    }

    $save = $dbh->prepare("INSERT into cds_transfer(from_acc, to_acc, symbol_id, trs_vol, remarks, user_name) VALUES(:fcd, :tcd, :sym, :volume, :remarks, :usr)");
    $save->bindParam(":fcd", $F_cd);
    $save->bindParam(":tcd", $T_cd);
    $save->bindParam(":sym", $sy);
    $save->bindParam(":volume", $vol);
    $save->bindParam(":remarks", $rm);
    $save->bindParam(":usr", $user_name);
    if($save->execute()) {
        //Select FROM Account (Transfer from)
        $q = $dbh->prepare("SELECT * FROM cds_holding where cd_code=:cd AND symbol_id=:id");
        $q->bindParam(':cd', $F_cd);
        $q->bindParam(':id', $sy);
        if($q->execute()) {
           $row = $q->fetch();
           $F_e_vol = $row['volume'];
           $F_vol = $F_e_vol - $vol;

           $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol where cd_code=:cd AND symbol_id=:id ");
           $save->bindParam(':cd',$F_cd);
           $save->bindParam(':vol',$F_vol);
           $save->bindParam(':id',$sy);
           $save->execute();

           //SELECT TO Account (Transfer To)
           $q2 = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cd AND symbol_id=:id");
           $q2->bindParam(':cd', $T_cd);
           $q2->bindParam(':id', $sy);
           if($q2->execute()) {
                if ($q2->rowCount() > 0) {
                    $row = $q2->fetch();
                    $T_e_vol = $row['volume'];
                    $T_vol = $vol + $T_e_vol;

                    $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol WHERE cd_code=:cd AND symbol_id=:id ");
                    $save->bindParam(':cd', $T_cd);
                    $save->bindParam(':vol', $T_vol);
                    $save->bindParam(':id', $sy);
                    $save->execute();
                    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
                } else {
                    $save = $dbh->prepare("INSERT INTO cds_holding(cd_code, volume, symbol_id, user_name, institution_id) 
                                           VALUES(:tcd, :volume, :sym, :usr, :inst_id)");
                    $save->bindParam(":tcd", $T_cd);
                    $save->bindParam(":volume", $vol);
                    $save->bindParam(":sym", $sy);
                    $save->bindParam(":usr", $username);
                    $save->bindParam(":inst_id", $institution_id);
                    $save->execute();
                    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
                          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check">
                           </i> Operation Successfully Completed.</div></div></div>';
               }
            } else {
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation.</div></div></div>';
            }
        } else {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation.</div></div></div>';
        }
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation.</div></div></div>';
    }
    $dbh = null;
    exit();
}
//transfer end
//pledge contract start
elseif (isset($_POST['pledge_contract'])) {
    //variable declaration
    $cc = $_POST['cc'];
    $pl = $_POST['pl'];
    $ac = $_POST['ac'];
    $rm = $_POST['remarks'];
    $user_name = $_POST['userName'];
    $pl_name = 'Pledge by CD Code holder : '.$ac. ', with Pledge Contract Code :'. $cc .' , On :'.date('d-m-Y').' with : '.$pl;
    // !- variable declaration

    $save = $dbh->prepare("SELECT count(pledge_contract) as rs FROM cds_pledge_contract WHERE pledge_contract=:cc");
    $save->bindParam(':cc', $cc);
    $save->execute();
    $count = $save->fetchColumn();
    if ($count <= 0) {
        $save = $dbh->prepare("INSERT INTO cds_pledge_contract(pledge_name, pledge_contract, pledgee, cd_code, remarks, user_name) 
            VALUES(:plg_name, :plg_con, :pledgee, :cd_code, :remark, :usr)");
        $save->bindParam(":plg_name", $pl_name);
        $save->bindParam(":plg_con", $cc);
        $save->bindParam(":pledgee", $pl);
        $save->bindParam(":cd_code", $ac);
        $save->bindParam(":remark", $rm);
        $save->bindParam(":usr", $user_name);
        if ($save->execute()) {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
        } else {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">
            </i> Message!&nbsp;&nbsp Oops Sorry! Cannot Create Pledge Contract with same code.</div></div></div>';
    }
    exit();
}
//pledge  end
//pledge start
elseif (isset($_POST['pledge'])) {
    //variable declaration
    $cc = $_POST['cc'];
    $pl = $_POST['pl'];
    $ac = $_POST['ac'];
    $sy = $_POST['sy'];
    $rm = $_POST['remarks'];
    $vol_pl = $_POST['trs1'];
    $user_name = $_POST['userName'];
    $type = 'PLEDGE';
    // !- variable declaration

    $q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd AND symbol_id=:id ");
    $q->bindParam(':cd',$ac);
    $q->bindParam(':id',$sy);
    $q->execute();
    $row=$q->fetch();

    $P_vol = $row['pledge_volume'];
    $avl_vol = $row['volume'];
    $new_pl_vol = $P_vol + $vol_pl;
    $new_avl_vol = $avl_vol - $vol_pl;

    if($avl_vol < $vol_pl){
        echo'
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                    Insufficient Volume to be Pledged
                </div>
            </div>
        </div>';
        die();
    }

   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cd_code, :sym, :vol, :usr, :inst_id, :remarks, :typ)");
   $saveT->bindParam(":cd_code", $ac);
   $saveT->bindParam(":sym", $sy);
   $saveT->bindParam(":vol", $vol_pl);
   $saveT->bindParam(":usr", $username);
   $saveT->bindParam(":inst_id", $institution_id);
   $saveT->bindParam(":remarks", $rm);
   $saveT->bindParam(":typ", $type);
   if($saveT->execute()) {
    $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id");
    $save->bindParam(':cd',$ac);
    $save->bindParam(':vol',$new_avl_vol);
    $save->bindParam(':pl_vol',$new_pl_vol);
    $save->bindParam(':id',$sy);
    if($save->execute()) {
        $save = $dbh->prepare("INSERT into cds_pledge(pledge_contract, pledgee, cd_code, symbol_id, pledge_volume, remarks, user_name) VALUES (:plg_conrt, :plg, :cd_code, :sym, :vol, :remarks, :usr)");
        $save->bindParam(":plg_conrt", $cc);
        $save->bindParam(":plg", $pl);
        $save->bindParam(":cd_code", $ac);
        $save->bindParam(":sym", $sy);
        $save->bindParam(":vol", $vol_pl);
        $save->bindParam(":remarks", $rm);
        $save->bindParam(":usr", $user_name);
        if ($save->execute()) {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
        } else {
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }
    } else {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
   } else {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
   }
   exit();
}
//pledge  end
//pledge edit start
elseif (isset($_POST['edit_plg'])) {
    //variable declaration
    $cc = $_POST['cc'];
    $pl = $_POST['pl'];
    $ac = $_POST['ac'];
    $sy = $_POST['sy'];
    $vol_pl = $_POST['trs'];
    $old_pl_vol = $_POST['old_pl_vol'];
    $pl_id = $_POST['edit_plg'];
    $rm = "Pledge Edited for pledge Contract ". $cc;
    $type = "PLEDGE EDIT";
    //$user_name=$_POST['pledge'];
    // !- variable declaration

    $q = $dbh->prepare("SELECT symbol_id FROM symbol WHERE symbol = :sy");
    $q->bindParam(':sy',$sy);
    $q->execute();
    $ro = $q->fetch();
    $sy_id = $ro['symbol_id'];

    $r = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cd and symbol_id=:id ");
    $r->bindParam(':cd',$ac);
    $r->bindParam(':id',$sy_id);
    $r->execute();
    $row=$r->fetch();

    $P_vol = $row['pledge_volume'];
    $avl_vol = $row['volume'];

    //$new_pl_vol=$P_vol-$old_pl_vol+$vol_pl;
    $new_pl_vol = $vol_pl;

    $updated_avl_vol = $avl_vol + $old_pl_vol;
    $new_avl_vol = $updated_avl_vol - $vol_pl;
    
    if ($new_avl_vol >= 0) {
        try {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();

            $saveT = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cd_code, :symbol_id, :volume, :user_name, :institution_id, :remarks, :type)");
            $saveT->bindParam(":cd_code", $ac);
            $saveT->bindParam(":symbol_id", $sy_id);
            $saveT->bindParam(":volume", $vol_pl);
            $saveT->bindParam(":user_name", $username);
            $saveT->bindParam(":institution_id", $institution_id);
            $saveT->bindParam(":remarks", $rm);
            $saveT->bindParam(":type", $type);
            $saveT->execute();

            $up_pl = $dbh->prepare("UPDATE cds_pledge SET pledge_volume=:vol_pl, pledgee=:pl WHERE pledge_id=:pl_id");
            $up_pl->bindParam(':vol_pl', $vol_pl);
            $up_pl->bindParam(':pl', $pl);
            $up_pl->bindParam(':pl_id', $pl_id);
            $up_pl->execute();

            $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol, pledge_volume=:pl_vol WHERE cd_code=:cd AND symbol_id=:id");
            $save->bindParam(':vol', $new_avl_vol);
            $save->bindParam(':pl_vol', $new_pl_vol);
            $save->bindParam(':cd', $ac);
            $save->bindParam(':id', $sy_id);
            $save->execute();

            $dbh->commit();
            $dbh = null;

            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Operation Successfully Completed.</div></div></div>';
          } catch(PDOException $e) {
            $dbh->rollBack();
            $dbh = null;
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Oops Sorry! There was an error while operation.</div></div></div>';
        }
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Pledge volume cannot be more than available volume.</div></div></div>';
    }
    die();
}

//pledge edit end
//pledge release start
elseif (isset($_POST['pledge_release'])) {
    //variable declaration
    $cc = $_POST['cc'];
    $pl = $_POST['pl'];
    $ac = $_POST['ac'];
    $sy = $_POST['sy'];
    $pname = $_POST['pname'];
    $rm = $_POST['remarks'];
    $vol_pl_rls = $_POST['rls'];
    $user_name = $_POST['userName'];
    $type = "PLEDGE RELEASE";
    // !- variable declaration

    $q = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cd AND symbol_id=:id ");
    $q->bindParam(':cd', $ac);
    $q->bindParam(':id', $sy);
    if($q->execute()) {
      $row = $q->fetch();
      $P_vol = $row['pledge_volume'];
      $avl_vol = $row['volume'];
      $new_pl_vol = $P_vol - $vol_pl_rls;
      $new_avl_vol = $avl_vol + $vol_pl_rls;

        if($P_vol < $vol_pl_rls) {
            echo'
            <div class="row">
                <div class="col-lg-12 col-xs-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                        Insufficient Volume to be released
                    </div>
                </div>
            </div>';
            die();
        }

        try {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();

             $saveT = $dbh->prepare("INSERT INTO cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) VALUES(:cdCode, :symId, :volume, :usr, :inst_id, :remarks, :typ)");
            $saveT->bindParam(":cdCode", $ac);
            $saveT->bindParam(":symId", $sy);
            $saveT->bindParam(":volume", $vol_pl_rls);
            $saveT->bindParam(":usr", $username);
            $saveT->bindParam(":inst_id", $institution_id);
            $saveT->bindParam(":remarks", $rm);
            $saveT->bindParam(":typ", $type);
            $saveT->execute();

            $stmt = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol WHERE cd_code=:cd and symbol_id=:id ");
            $stmt->bindParam(':cd',$ac);
            $stmt->bindParam(':vol',$new_avl_vol);
            $stmt->bindParam(':pl_vol',$new_pl_vol);
            $stmt->bindParam(':id',$sy);
            $stmt->execute();

            $save = $dbh->prepare("INSERT into cds_pledge (pledge_contract,pledgee,cd_code,symbol_id,pledge_volume,remarks,user_name,pledge_name) 
                      VALUES (:plg_cont, :plg, :cd_code, :sym_id, -:vol, :remarks, :usr, :plg_name)");
            $save->bindParam(":plg_cont", $cc);
            $save->bindParam(":plg", $pl);
            $save->bindParam(":cd_code", $ac);
            $save->bindParam(":sym_id", $sy);
            $save->bindParam(":vol", $vol_pl_rls);
            $save->bindParam(":remarks", $rm);
            $save->bindParam(":usr", $user_name);
            $save->bindParam(":plg_name", $pname);
            $save->execute();

            $dbh->commit();
            $dbh = null;

            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';

        } catch(PDOException $e) {
            $dbh->rollBack();
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }
    } else {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
    exit();
}
//pledge release end
//pledge release edit start
elseif (isset($_POST['edit_plg_rls']))
{
    //variable declaration
    $cc = $_POST['cc'];
    //$pl = $_POST['pl'];
    $ac = $_POST['ac'];
    $sy = $_POST['sy'];
    $vol_pl = $_POST['trs'];
    $old_pl_vol = $_POST['old_pl_vol'] * -1;
    $pl_id = $_POST['pl_id'];
    $rm = "Pledge release edited for contract code:  ".$cc." , and pledge id : ".$pl_id;
    $type = "PLEDGE RELEASE EDIT";
    //$user_name=$_POST['pledge'];
    // !- variable declaration

    $q = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cd AND symbol_id=:id ");
    $q->bindParam(':cd',$ac);
    $q->bindParam(':id',$sy);
    $q->execute();
    $row = $q->fetch();

    $P_vol = $row['pledge_volume'];
    $avl_vol = $row['volume'];
    $new_pl_vol = $P_vol - $old_pl_vol + $vol_pl;
    $new_cds_pl_vol = $vol_pl * -1;
    $new_avl_vol = $avl_vol + $old_pl_vol - $vol_pl;

    if ($new_avl_vol >= 0) {
       $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code, symbol_id, volume, user_name, institution_id, remarks, type) 
            VALUES(:cdCode, :symId, -:volume, :usr, :inst_id, :remarks, :typ)");
        $saveT->bindParam(":cdCode", $ac);
        $saveT->bindParam(":symId", $sy);
        $saveT->bindParam(":volume", $vol_pl);
        $saveT->bindParam(":usr", $username);
        $saveT->bindParam(":inst_id", $institution_id);
        $saveT->bindParam(":remarks", $rm);
        $saveT->bindParam(":typ", $type);
        if($saveT->execute()) {
            $up_pl=$dbh->prepare("UPDATE cds_pledge SET pledge_volume=:vol_pl where pledge_id=:pl_id");
            $up_pl->bindParam(':vol_pl', $new_cds_pl_vol);
            $up_pl->bindParam(':pl_id', $pl_id);

            if($up_pl->execute()) {
               $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id ");
               $save->bindParam(':cd', $ac);
               $save->bindParam(':vol', $new_avl_vol);
               $save->bindParam(':pl_vol', $new_pl_vol);
               $save->bindParam(':id', $sy);

               if($save->execute()) {
                      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
                      exit();                          
                } else {
                      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
                      exit();
                }
            } else {
                echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
                exit();

            }
        } else {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
          exit();

       }
    } else {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Pledge release volume cannot be more than available release volume.</div></div></div>';
      exit();
   }
}
//pledge release edit end
//corporate announcement start
elseif (isset($_POST['save_corporate_announcement'])) {
    //variable declaration
    $symbol = $_POST['sy'];
    $record_date = $_POST['record_date'];
    $exdate = $_POST['exdate'];
    $announcement_date=$_POST['announcement_date'];
    $rate = $_POST['rate'];
    $type = $_POST['type'];
    $type = $_POST['type'];
    $announcement_type = $_POST['announcement_type'];
    $price = $_POST['price'];
    $status = 1;
    // !- variable declaration

    // check exist record
    $check = $dbh->prepare("SELECT COUNT(*) FROM corporate_announcement c 
        WHERE c.symbol_id = ?
        AND c.record_date = ?
        AND c.announcement_type = ? 
        AND c.status = 1
    ");
    $check->bindParam(1, $symbol);
    $check->bindParam(2, $record_date);
    $check->bindParam(3, $announcement_type);
    $check->execute();
    $count = $check->fetchColumn();
    if ($count > 0) {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"> </i> Record already existed with same symbol, record date and announcement type</div></div></div>';
        die();
    }

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $save = $dbh->prepare("INSERT INTO corporate_announcement(symbol_id, record_date, ex_date, announcement_date, rate, type, announcement_type, price, status, modifier_username) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $save->bindParam(1, $symbol);
        $save->bindParam(2, $record_date);
        $save->bindParam(3, $exdate);
        $save->bindParam(4, $announcement_date);
        $save->bindParam(5, $rate);
        $save->bindParam(6, $type);
        $save->bindParam(7, $announcement_type);
        $save->bindParam(8, $price);
        $save->bindParam(9, $status);
        $save->bindParam(10, $username);
        $save->execute();

        $dbh->commit();
        $dbh = null;

        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
    exit();
}
//corporate announcement end
//pledgee start
elseif (isset($_POST['save_pledge'])) {
    //variable declaration
    $pledgee_name = $_POST['pledgee_name'];
    $address = $_POST['address'];

    // !- variable declaration
    $save = $dbh->prepare("INSERT INTO cds_pledgee(pledgee, address) VALUES(:name, :add)");
    $save->bindParam(":name", $pledgee_name);
    $save->bindParam(":add", $address);
    if ($save->execute()) {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
    exit();
}
elseif (!empty ($_POST['delete_pledegee'])) {
  $id = $_POST['delete_pledegee'];

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $delete = $dbh->prepare("DELETE FROM cds_pledgee WHERE pledgee_id=:p_id");
    $delete->bindParam(':p_id', $id);
    $delete->execute();

    $dbh->commit();
    $dbh = null;

    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    error_log("Error ===>> ".$e->getMessage().", Code => ".$e->getCode().", line => ".$e->getLine());
    echo'
        <div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation. Please contact RSEB support.
        </div></div></div>';
  }
  exit();
}
//pledgee end
//bank start
elseif (isset($_POST['save_bank'])) {
    $bank = $_POST['bank_name'];

    $save = $dbh->prepare("INSERT into banks (bank_name) VALUES (:bak)");
    $save->bindParam(":bak", $bank);
    if ($save->execute()) {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
    exit();
}
//bank end
//bank edit start
elseif (!empty ($_POST['edit_bank_name']) && !empty($_POST['edit_cli']))
{
    //variable declaration
    $bank = $_POST['edit_bank_name'];
    $bank_id = $_POST['edit_bank'];
    // !- variable declaration
    $save = $dbh->prepare("UPDATE banks  SET bank_name=:bank_name where bank_id=:id");
    $save->bindParam(':bank_name', $bank);
    $save->bindParam(':id', $bank_id);
    $save->execute();
}
//bank edit end
//bank branch start
elseif (isset($_POST['save_branch'])) {
    //variable declaration
    $bank_id = $_POST['bank_id'];
    $branch_name = $_POST['branch_name'];
    $branch_address = $_POST['branch_address'];
    // !- variable declaration

    $save = $dbh->prepare("INSERT INTO bank_branch (BANK_ID, BRANCH_NAME, BRANCH_ADDRESS) VALUES(:ban_id, :brn_name, :brn_address)");
    $save->bindParam(":ban_id", $bank_id);
    $save->bindParam(":brn_name", $branch_name);
    $save->bindParam(":brn_address", $branch_address);
    $result = $save->execute();
    if ($result) {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation.</div></div></div>';
    }
    exit();
}
//bank branch end
//bank branch start
elseif (isset($_POST['save_occ'])) {
    $occ_name = $_POST['occ_name'];

    $save = $dbh->prepare("INSERT into occupation (occupation_name) VALUES (:name)");
    $save->bindParam(":name", $occ_name);
    if($save->execute()){
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
    
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
    exit();
}
//bank branch end
//dividend start
elseif (isset($_POST['exe_corporate_announcement'])) {
    $announcement_type = clean($_POST['announcement_type']);
    $announcementId = clean($_POST['corp_announcement_id']);

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $stmt = $dbh->prepare("SELECT ca.symbol_id, ca.record_date, ca.rate, cla.client_id, ch.cds_holding_id, ch.volume, ch.pledge_volume, ch.block_volume, ch.pending_out_vol, ca.corp_announcement_id
            FROM corporate_announcement ca 
            JOIN cds_holding ch ON ca.symbol_id = ch.symbol_id
            JOIN client_account cla ON ch.cd_code = cla.cd_code
            WHERE ca.corp_announcement_id = :aid
        ");
        $stmt->bindParam(':aid', $announcementId);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $status = 1;

        foreach ($rows as $row) {
            $symbol_id = $row['symbol_id'];
            $rec_date = $row['record_date'];
            $client_id = $row['client_id'];
            $cds_holding_id = $row['cds_holding_id'];
            $corp_announcement_id = $row['corp_announcement_id'];
            $rate = $row['rate'];

            $volume = $row['volume'] + $row['pledge_volume'] + $row['pending_out_vol'] + $row['block_volume'];
            
            $new_vol = ($announcement_type == 3) ? 0 : round(($volume * $rate) / 100);
            
            $save = $dbh->prepare("INSERT INTO spot_date_holding(symbol_id, record_date, client_id, ribon_volume, volume, corp_announcement_id, announcement_type, status) 
                VALUES(:symId, :r_date, :cli_id, :n_vol, :vol, :cor_ann_id, :ann_type, :sta)");
            $save->bindParam(":symId", $symbol_id);
            $save->bindParam(":r_date", $rec_date);
            $save->bindParam(":cli_id", $client_id);
            $save->bindParam(":n_vol", $new_vol);
            $save->bindParam(":vol", $volume);
            $save->bindParam(":cor_ann_id", $corp_announcement_id);
            $save->bindParam(":ann_type", $announcement_type);
            $save->bindParam(":sta", $status);
            $save->execute();
        }

        //update corporate announceent status as 0
        $save = $dbh->prepare("UPDATE corporate_announcement SET status = 0 WHERE corp_announcement_id = :id");
        $save->bindParam(':id', $announcementId);
        $save->execute();

        $dbh->commit();

        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
    } catch(PDOException $e) {
        $dbh->rollBack();
        error_log("Error ===>> ".$e->getMessage().", line => ".$e->getLine());
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation. Please contact RSEB support.</div></div></div>';
    }
    $dbh = null;
    exit();
}
elseif(isset($_POST['save_hol'])) {
   $hol_name = clean($_POST['hol_name']);
   $hol_date = clean($_POST['hol_date']);
   $sysDateTime = date("Y-m-d H:i:s");
   $current_date = date("Y-m-d");

   if ($hol_date < $current_date) {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Please enter a valid holiday date.</div></div></div>';
      exit();
   }

   $stmt = $dbh->prepare("SELECT 1 FROM holiday WHERE holiday_date = ?");
   $stmt->execute([$hol_date]);
   $check = $stmt->fetchColumn();

   if ($check) {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Cannot set duplicate holiday on the same date.</div></div></div>';
      exit();
   }

   $save = $dbh->prepare("INSERT INTO holiday (holiday_date, hol_name, status, created_at) VALUES(?, ?, 1, ?)");
   $result = $save->execute([$hol_date, $hol_name, $sysDateTime]);
   if($result){
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.</div></div></div>';
   }
   else{
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation.</div></div></div>';
   }
   exit();
}
//holiday end
//settlement cycle start
elseif(isset($_POST['save_sett'])) {
    $set_name = clean($_POST['set_name']);
    $set_day = clean($_POST['set_day']);

    $save = $dbh->prepare("INSERT INTO css_settlement_cycle (name, days) VALUES(:name, :day)");
    $save->bindParam(":name", $set_name);
    $save->bindParam(":day", $set_day);
    if ($save->execute()) {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
    }
    exit();
}
//Settlement start
elseif(!empty($_POST["SETT"]))
{
 echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Settlement Successful.</div></div></div>';exit();
}
elseif (isset($_POST['block_vol'])) {
    //variable declaration
    $cd_code = clean($_POST['cdcode']);
    $symbol_id = clean($_POST['sy']);
    $bv = clean($_POST['block_vol']);
    $rm = clean($_POST['rm']);
    $user_name = clean($_POST['user_name']);
    $type = 'BLOCK/UNBLOCK';
    // !- variable declaration

    //To check existing volume
    $q22 = $dbh->prepare("SELECT volume FROM cds_holding WHERE cd_code=:cd and symbol_id=:id");
    $q22->bindParam(':cd',$cd_code);
    $q22->bindParam(':id',$symbol_id);
    $q22->execute();
    $row22 = $q22->fetch();
    $vol_existing = $row22['volume'];

    if($vol_existing < $bv){
        echo'
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                    Insufficient Volume to be Blocked
                </div>
            </div>
        </div>';
        die();
    }

    $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type) 
            VALUES(:cdCode, :symId, -:volume, :usr, :inst_id, :remarks, :typ)");
    $saveT->bindParam(":cdCode", $cd_code);
    $saveT->bindParam(":symId", $symbol_id);
    $saveT->bindParam(":volume", $bv);
    $saveT->bindParam(":usr", $user_name);
    $saveT->bindParam(":inst_id", $institution_id);
    $saveT->bindParam(":remarks", $rm);
    $saveT->bindParam(":typ", $type);

   if($saveT->execute()){
        $save = $dbh->prepare("UPDATE cds_holding SET block_volume=block_volume+:bv,volume=volume-:bv where cd_code=:cd and symbol_id=:id ");
        $save->bindParam(':cd',$cd_code);
        $save->bindParam(':bv',$bv);
        $save->bindParam(':id',$symbol_id);
        if( $save->execute()) {
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
                exit();
        } else {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            exit();
        }
   } else {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
   }
}
elseif (isset($_POST['unblock_vol'])) {
    //variable declaration
    $cd_code=clean($_POST['cdcode']);
    $symbol_id=clean($_POST['sy']);
    $ubv=clean($_POST['unblock_vol']);
    $rm=clean($_POST['rm']);
    $user_name=clean($_POST['user_name']);
    $type='UNBLOCK';
    // !- variable declaration

    //To check existing volume
    $q22=$dbh->prepare("SELECT block_volume FROM cds_holding WHERE cd_code=:cd and symbol_id=:id");
    $q22->bindParam(':cd',$cd_code);
    $q22->bindParam(':id',$symbol_id);
    $q22->execute();
    $row22=$q22->fetch();
    $aval_block_vol=$row22['block_volume'];

    if($aval_block_vol < $ubv){
        echo'
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                    Insufficient Volume to be Unblocked
                </div>
            </div>
        </div>';
        die();
    }

    $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type) 
            VALUES(:cdCode, :symId, :volume, :usr, :inst_id, :remarks, :typ)");
    $saveT->bindParam(":cdCode", $cd_code);
    $saveT->bindParam(":symId", $symbol_id);
    $saveT->bindParam(":volume", $ubv);
    $saveT->bindParam(":usr", $user_name);
    $saveT->bindParam(":inst_id", $institution_id);
    $saveT->bindParam(":remarks", $rm);
    $saveT->bindParam(":typ", $type);

    if ($saveT->execute()) {
        $save = $dbh->prepare("UPDATE cds_holding SET block_volume=block_volume-:ubv,volume=volume+:ubv where cd_code=:cd and symbol_id=:id ");
        $save->bindParam(':cd',$cd_code);
        $save->bindParam(':ubv',$ubv);
        $save->bindParam(':id',$symbol_id);
        if($save->execute()){
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
        } else {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }
    } else {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
   }
   exit();
}
//settlement end
elseif (isset($_POST['backup'])) {
  $date = $_POST['cdate'];
  $link = '../../BACKUP/'.$date.''.'.xls';

  $insert = $dbh->prepare("INSERT INTO backupsr (name, link, created_by) VALUES(:dte, :lnk, :user)");
  $insert->bindParam(":dte", $date);
  $insert->bindParam(":lnk", $link);
  $insert->bindParam(":user", $username);
  $insert->execute();

  $symbol = $dbh->query("SELECT symbol_id FROM symbol");
  $symbol->execute();
  $values = $symbol->fetchAll(PDO::FETCH_ASSOC);
  foreach ($values as $value) {
        $sid = $value['symbol_id'];
        $back_up = $dbh->prepare("SELECT c.cd_code, a.title, a.f_name, a.l_name, a.tpn, a.phone, a.ID, a.address, a.bank_account, ban.bank_name, c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol as total, s.symbol
            FROM cds_holding c, client_account a,banks ban, symbol s
            WHERE c.cd_code = a.cd_code 
            AND c.symbol_id = s.symbol_id
            AND c.symbol_id = :sid
            AND a.bank_id = ban.bank_id 
            AND (c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) != 0 ORDER BY a.bank_account ASC
        ");

        $back_up->bindParam(':sid',$sid);
        $back_up->execute();
        $replace   = array("\n","\r\n","\r");
        $search  = array('','','');
        $columnHeader = '';
        $i = 1;
        $columnHeader = "CD CODE" . "\t". "NAME" . "\t". "TPN" . "\t". "PHONE" . "\t". "ADDRESS" . "\t". "ID" . "\t". "VOLUME" . "\t". "BANK Account" . "\t"."Security_Symbol" . "\t";
        $setData = '';
        while ($rec=$back_up->fetch()) {
        if($back_up->rowCount() <= 0)
        {}
        $rowData = '';
        $value = str_replace($search,$replace,$rec['cd_code'])."\t".
                str_replace($search,$replace,trim($rec['title'])." ".$rec['f_name'].' '.$rec['l_name'])."\t".
                str_replace($search,$replace,$rec['tpn']) . "\t". 
                str_replace($search,$replace,$rec['phone']) ."\t". 
                str_replace($search,$replace,$rec['address']) . "\t". 
                str_replace($search,$replace,$rec['ID']). "\t". 
                str_replace($search,$replace,$rec['total']) . "\t". 
                str_replace($search,$replace,trim($rec['bank_account']). " -") ."\t". 
                str_replace($search,$replace,$rec['symbol']) . "\t";
        $rowData .= $value;
        $setData .= trim($rowData) . "\n";
      }
      file_put_contents('C:/inetpub/wwwroot/RSEB/BACKUP/'.$date.'.xls', ucwords($columnHeader) . "\n" . $setData . "\n" . PHP_EOL, FILE_APPEND);
      // file_put_contents('../../BACKUP/'.$date.'.xls', ucwords($columnHeader) . "\n" . $setData . "\n" . PHP_EOL, FILE_APPEND);
  }
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
  exit();
}
elseif (isset($_POST['update_cid'])) {
    //variable declaration
    $new_cidno = $_POST['new_cidNo'];
    $cdCode = $_POST['cdCode'];
    $name = $_POST['name'];
    $oldcid = $_POST['oldcid'];
    $remark = $_POST['remark'];
    $part_code = $_POST['part_code'];
    $institute_id = $_POST['institute_id'];

    // to check whether new cid no already exist for the same CD Code.
    $sql = $dbh->prepare("SELECT a.ID FROM client_account a WHERE a.cd_code = :cd");
    $sql->bindParam(':cd', $cdCode);
    $sql->execute();
    $cidExit = $sql->fetchColumn();
    if ($cidExit == $new_cidno) {
        echo'
        <div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="icon fa fa-check"> </i> A CID number already exists for the CD code '.$cdCode.'.</div>
        </div>';
        die();
    }

    // To check whether a CD code already exists for a new CID no. under the same member except RSEB
    if ($part_code != 'EMPRSEB') {
        $stmt = $dbh->prepare("SELECT a.cd_code FROM client_account a WHERE a.institution_id = ? AND a.cd_code != ? AND a.ID = ?");
        $stmt->execute([$institute_id, $cdCode, $new_cidno]);
        $cdcode_exist = $stmt->fetchColumn();
        if ($cdcode_exist) {
            echo'
            <div class="col-lg-12 col-xs-12">
              <div class="alert alert-danger alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                  <i class="icon fa fa-exclamation"></i> A CD code <strong>'.$cdcode_exist.'</strong> already exists for the CID <strong>'.$new_cidno.'</strong>
              </div>
            </div>';
            die();
        }
    }

    // Insert into log and update ID in client_account table
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $stmt = $dbh->prepare("INSERT INTO update_cid_log(cd_code, name, old_cid, new_cid, remark) VALUES(?, ?, ?, ?, ?)");
        $stmt->execute([$cdCode, $name, $oldcid, $new_cidno, $remark]);

        $save = $dbh->prepare("UPDATE client_account a SET a.ID = :id WHERE a.cd_code = :cdCode");
        $save->bindParam(':id', $new_cidno);
        $save->bindParam(':cdCode', $cdCode);
        $save->execute();

        $dbh->commit();

        echo'
        <div class="col-lg-12 col-xs-12">
          <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
              <i class="icon fa fa-check"> </i> CID updated Successfully.
          </div>
        </div>';
        exit;
    } catch (Exception $e) {
        $dbh->rollBack();
        error_log("Error ===>> ".$e->getMessage().", line => ".$e->getLine());
        echo'
        <div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
          <i class="icon fa fa-check"> </i> There was an error while operation. Please contact RSEB support.</div>
        </div>';
    }
    
    $dbh = null;
    exit();
}
elseif (isset($_POST['admApprove'])) {
    $sysDateTime = date("Y-m-d H:i:s");
    $cid = clean($_POST['cid']);
    $name = clean($_POST['name']);
    $role = clean($_POST['role']);
    $pCode = clean($_POST['pCode']);
    $phone = clean($_POST['phone']);
    $email = clean($_POST['email']);
    $status = clean($_POST['status']);
    $address = clean($_POST['address']);

    $un = clean($_POST['online_un']);
    // $pwd = md5($un);
    $onlineUsrId = clean($_POST['onlineUsrId']);
    $user_name = clean($_POST['username']);
    $a_code = clean($_POST['admApprove']);
    $log_check = "1";

    $emailBroker = '';
    $participantEmails = array(
        "MEMBNBL" => "karmachoden@bnb.bt",
        "MEMBOBL" => "sonam.peldon2956@bob.bt",
        "MEMDSBP" => "drukyulsecurities@gmail.com",
        "MEMLDSB" => "lekpaydolmashares@gmail.com",
        "MEMSERS" => "sershingsecurities@gmail.com",
        "MEMRICB" => "sangay_tenzin2@ricb.bt",
        "MEMBPCL" => "ugyen.tshomo@bhutanpost.bt",
        "MEMRINS" => "rinsecurities@gmail.com",
        "MEMBDBL" => "kencho.wangmo@bdb.bt",
    );

    if (isset($participantEmails[$pCode])) {
        $emailBroker = $participantEmails[$pCode];
    }                                

    //select broker user name and CD code from client account;
    $selectSql= "SELECT c.cd_code, c.user_name, c.ID FROM client_account c WHERE c.ID=:cid AND c.user_name LIKE '%{$pCode}%'";
    $select = $dbh->prepare($selectSql);
    $select->bindParam(':cid', $cid);
    $select->execute();
    $result = $select->fetch();
    $cd_Code = $result['cd_code'];
    $brokerUser = $result['user_name'];

    //variable declaration
    $save = $dbh->prepare("SELECT username FROM users WHERE username=:uname");
    $save->bindParam(':uname', $un);
    $save->execute();
    if($row = $save->fetch() == 0) {
        try {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();

            $sql=$dbh->prepare("INSERT INTO api_online_terminal_audit(user_online_id, cid, cd_code, name, participant_code, phone, email, address, declaration, broker_user, status, app_fee, fee_status, order_no, created_date)
              SELECT a.user_online_id, a.cid, a.cd_code, a.name, a.participant_code, a.phone,a.email, a.address, a.declaration, a.broker_user, a.status, a.app_fee, a.fee_status, a.order_no,  a.created_date FROM api_online_terminal a WHERE a.user_online_id=:id");
            $sql->bindParam(':id', $onlineUsrId);
            $sql->execute();

            $query = $dbh->prepare("UPDATE api_online_terminal a SET a.status=:ap, a.broker_user=:usn, a.created_date=:sysDaTi WHERE a.user_online_id=:id");
            $query->bindParam(':ap', $a_code);
            $query->bindParam(':usn', $user_name);
            $query->bindParam(':sysDaTi', $sysDateTime);
            $query->bindParam(':id', $onlineUsrId);
            $query->execute();

            $hashedPassword = password_hash($un, PASSWORD_BCRYPT);
            $isBcrypt = 1;

            $insert = $dbh->prepare("INSERT INTO users(name, username, password, role_id, participant_code, phone, email, status, log_check, address, cid, is_bcrypt) 
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$name, $un, $hashedPassword, $role, $pCode, $phone, $email, $status, $log_check, $address, $cid, $isBcrypt]);

            $link = $dbh->prepare("INSERT INTO linkuser(participant_code, client_code, username, broker_user_name) VALUES(?, ?, ?, ?)");
            $link->execute([$pCode, $cd_Code, $un, $brokerUser]);

            $dbh->commit();
            
            include('emailLink.php');

            $dbh = null;
            header('location: ../FILES/userList.php?ms=1');
        } catch(PDOException $e) {
            error_log("Error ==> " . $e->getMessage() . ", Line ==> ".$e->getLine());
            $dbh->rollBack();
            header('location: ../FILES/userList.php?ms=3');
        }
    } else {
        header('location: ../FILES/userList.php?ms=4');
    }
    exit();
} elseif (isset($_POST['admReject'])) {
  $sysDateTime = date("Y-m-d H:i:s");

  $onlineUsrId = clean($_POST['onlineUsrId']);
  $user_name = clean($_POST['username']);
  $a_code = clean($_POST['admReject']);
  $log_check = "1";

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $sql=$dbh->prepare("INSERT INTO api_online_terminal_audit(user_online_id, cid, cd_code, name, participant_code, phone, email, address, declaration, broker_user, status, app_fee, fee_status, order_no, created_date)
    SELECT a.user_online_id, a.cid, a.cd_code, a.name, a.participant_code, a.phone,a.email, a.address, a.declaration, a.broker_user, a.status, a.app_fee, a.fee_status, a.order_no, a.created_date FROM api_online_terminal a WHERE a.user_online_id=:id");
    $sql->bindParam(':id', $onlineUsrId);
    $sql->execute();

    $query = $dbh->prepare("UPDATE api_online_terminal a SET a.status=:ap, a.broker_user=:usn, a.created_date=:sysDaTi WHERE a.user_online_id=:id");
    $query->bindParam(':ap', $a_code);
    $query->bindParam(':usn', $user_name);
    $query->bindParam(':sysDaTi', $sysDateTime);
    $query->bindParam(':id', $onlineUsrId);
    $query->execute();

    $dbh->commit();
    $dbh = null;

    header('location: ../FILES/userList.php?ms=2');

  } catch(PDOException $e) {
    $dbh->rollBack();
    header('location: ../FILES/userList.php?ms=3');
  }
  exit();
}
elseif(isset($_POST['online_terminal_verification']))
{
    $sysDateTime = date("Y-m-d H:i:s"); 
    $username = $_POST['username'];
    $bvCode = $_POST['online_terminal_verification'];
    $onlineUsrId = $_POST['onlineUsrId'];

    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $cdCode = $_POST['cdCode'];

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        // insert into audit table
        $sql = $dbh->prepare("INSERT INTO api_online_terminal_audit(user_online_id, cid, cd_code, name, participant_code, phone, email, address, declaration, broker_user, status, app_fee, fee_status, order_no, created_date) 
            SELECT a.user_online_id, a.cid, a.cd_code, a.name, a.participant_code, a.phone,a.email, a.address, a.declaration, a.broker_user, a.status, a.app_fee, a.fee_status, a.order_no, a.created_date FROM api_online_terminal a WHERE a.user_online_id=:id");
        $sql->bindParam(':id', $onlineUsrId);
        $sql->execute();

        // update email and phone if any
        $updateApiTable = $dbh->prepare("UPDATE api_online_terminal a SET a.email = :email, a.phone = :phn_no WHERE a.user_online_id = :usrId AND a.cd_code = :cdCode");
        $updateApiTable->bindParam(':email', $email);
        $updateApiTable->bindParam(':phn_no', $phone);
        $updateApiTable->bindParam(':usrId', $onlineUsrId);
        $updateApiTable->bindParam(':cdCode', $cdCode);
        $updateApiTable->execute();

        // update email and phone in client_account table
        $updateClientAccTable = $dbh->prepare("UPDATE client_account a SET a.email = :email, a.phone = :phn_1 WHERE a.cd_code = :cdCode");
        $updateClientAccTable->bindParam(':email', $email);
        $updateClientAccTable->bindParam(':phn_1', $phone);
        $updateClientAccTable->bindParam(':cdCode', $cdCode);
        $updateClientAccTable->execute();

        // update status as BV
        $query = $dbh->prepare("UPDATE api_online_terminal a SET a.status = :status, a.broker_user = :usn, a.created_date = :sysDaTi WHERE a.user_online_id = :id");
        $query->bindParam(':status', $bvCode);
        $query->bindParam(':usn', $username);
        $query->bindParam(':sysDaTi', $sysDateTime);
        $query->bindParam(':id', $onlineUsrId);
        $query->execute();

        $dbh->commit();
        $dbh = null;

        header('location: ../FILES/user_list_bv.php?ms=1');
    } catch(PDOException $e) {
        $dbh->rollBack();
        header('location: ../FILES/user_list_bv.php?ms=3');
    }
    exit();
}
elseif (isset($_POST['nrb_verification'])) {
    include ('../../CONNECTIONS/db_config_website.php');
    $sysDateTime = date("Y-m-d H:i:s");

    $nrb_verification = $_POST['nrb_verification'];
    $app_id = $_POST['app_id'];
    $user_name = $_POST['user_name'];
    $cid = $_POST['cid'];
    $title = $_POST['title'];
    $name = $_POST['name'];
    $passport = $_POST['passport'];
    $dob = $_POST['dob'];
    $local_phone_no = $_POST['local_phone_no'];
    $email = $_POST['email'];
    $oversea_phone_no = $_POST['oversea_phone_no'];
    $bank = $_POST['bank'];
    $account_no = $_POST['account_no'];
    $account_type = $_POST['account_type'];
    $permanent_address = $_POST['permanent_address'];
    $oversea_address = $_POST['oversea_address'];
    $institute_id = $_POST['institution_id'];
    $comm_id = $_POST['commission'];
    $bank_id = 0;
    $cd_code = '';
    if($bank=='BOBL' || $bank=='BOB'){
        $bank_id=2;
    }else if($bank=='BNBL' || $bank=='BNB'){
        $bank_id=1;
    }else if($bank=='BDBL' || $bank=='BDB'){
        $bank_id=3;
    }else if($bank=='DPNB' || $bank=='PNB'){
        $bank_id=4;
    }else if($bank=='TBANK'){
        $bank_id=5;
    }

    try{
        $dbh_site->beginTransaction();
        $dbh_site->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if($nrb_verification == 'APPROVED'){
            $checkExist = $dbh->prepare("SELECT a.cd_code, a.institution_id FROM client_account a WHERE a.cd_code LIKE 'NR%' AND a.ID=:cid");
            $checkExist->bindParam(':cid', $cid);
            $checkExist->execute();
            if($checkExist->rowCount() < 1){
                //insert into audit table non_resident_bhutanese_audits
                $sql=$dbh_site->prepare("INSERT INTO non_resident_bhutanese_audits(nrb_id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, nrb_created_at, nrb_updated_at)
                SELECT id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, created_at, updated_at FROM non_resident_bhutaneses WHERE id=:app_id");
                $sql->bindParam(':app_id', $app_id);
                $sql->execute();

                $getLastCdCode = $dbh->prepare("SELECT a.cd_code FROM client_account a WHERE a.institution_id=:insti_id ORDER BY a.client_id DESC LIMIT 1");
                $getLastCdCode->bindParam(':insti_id', $institute_id);
                $getLastCdCode->execute();
                if($getLastCdCode->rowCount() < 1){
                    $cd_code='NR00000001';
                }else{
                    $last_cdCode = $getLastCdCode->fetch();
                    $last_number = substr($last_cdCode['cd_code'], 2);
                    $new_number = $last_number+1;
                    $new_number_with_zeros = str_pad($new_number, 8, '0', STR_PAD_LEFT);
                    $new_codeCode = 'NR'.$new_number_with_zeros;
                    $cd_code = $new_codeCode;
                }

                //update app status as APPROVED
                $update = $dbh_site->prepare("UPDATE non_resident_bhutaneses SET app_status='APPROVED', user_name=:usr, updated_at=:uptDate WHERE id=:app_id");
                $update->bindParam(':app_id', $app_id);
                $update->bindParam(':usr', $user_name);
                $update->bindParam(':uptDate', $sysDateTime);
                $update->execute();

                /*Insert record into client account at cms2*/
                $insert = $dbh->prepare("INSERT INTO client_account(acc_type, cd_code, f_name, ID, nationality, phone, email, bank_id, bank_account, bro_comm_id, address, institution_id, title, bank_account_type, passport, dob, oversea_phone_no, permanent_address, user_name) VALUES('I','$cd_code', '$name', '$cid','Bhutanese','$local_phone_no','$email','$bank_id','$account_no','$comm_id','$oversea_address','$institute_id','$title','$account_type','$passport','$dob','$oversea_phone_no','$permanent_address','$user_name')");
                $insert->execute();

                $getPartCode = $dbh->prepare("SELECT p.participant_code FROM adm_participants p WHERE p.institution_id=:ins_id");
                $getPartCode->bindParam(':ins_id', $institute_id);
                $getPartCode->execute();
                $partCode = $getPartCode->fetch();
                $memParticipateCode = $partCode['participant_code'];

                $clientUserName = $memParticipateCode.$cid;
                $usrPwd = md5($clientUserName);
                $roleId=4; $status=1; $log_check=1;

                $instLinkUsr = $dbh->prepare("INSERT INTO linkuser(participant_code, client_code, username, broker_user_name) VALUES('$memParticipateCode', '$cd_code', '$clientUserName','$user_name')");
                $instLinkUsr->execute();

                $insertUser = $dbh->prepare("INSERT INTO users(name, username, password, role_id, participant_code, phone, email, status, log_check, cd_code, address, cid) VALUES('$name', '$clientUserName', '$usrPwd', '$roleId', '$memParticipateCode', '$oversea_phone_no', '$email', '$status', '$log_check', '$cd_code', '$oversea_address', '$cid')");
                $insertUser->execute();

                include('emailLink_NRB.php');

                $dbh_site->commit();
                $dbh->commit();

                $dbh_site = null;
                $dbh = null;

                header('location: ../FILES/nrb_app_list.php?ms=1');
                die();
            }else{
                $dbh_site = null;
                $dbh = null;

                header('location: ../FILES/nrb_app_list.php?ms=4');
                die();
            }
        }else{
            $remarks = $_POST['remarks'];

            //insert into audit table non_resident_bhutanese_audits
            $sql=$dbh_site->prepare("INSERT INTO non_resident_bhutanese_audits(nrb_id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, nrb_created_at, nrb_updated_at)
            SELECT id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, created_at, updated_at FROM non_resident_bhutaneses WHERE id=:app_id");
            $sql->bindParam(':app_id', $app_id);
            $sql->execute();

            //update app status as APPROVED
            $update = $dbh_site->prepare("UPDATE non_resident_bhutaneses SET app_status='REJECTED', remarks=:remrk, user_name=:usr, updated_at=:uptDate WHERE id=:app_id");
            $update->bindParam(':remrk', $remarks);
            $update->bindParam(':usr', $user_name);
            $update->bindParam(':uptDate', $sysDateTime);
            $update->bindParam(':app_id', $app_id);
            $update->execute();

            $dbh_site->commit();
            $dbh->commit();

            $dbh_site = null;
            $dbh = null;

            header('location: ../FILES/nrb_app_list.php?ms=2');
            die();
        }
    } catch(PDOException $e){
        $dbh_site->rollBack();
        $dbh->rollBack();
        error_log("Error ===>> ".$e->getMessage().", line => ".$e->getLine());
        header('location: ../FILES/nrb_app_list.php?ms=3');
        die();
    }
}
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
    $save->bindParam(':isin', $isin);
    $save->bindParam(':sy', $sy);
    $save->bindParam(':name', $name);
    $save->bindParam(':sector', $sector);
    $save->bindParam(':fv', $fv);
    $save->bindParam(':pv', $pv);
    $save->bindParam(':stype', $stype);
    $save->bindParam(':status', $status);
    $save->bindParam(':bl', $bl);
    $save->bindParam(':pus', $pus);
    $save->bindParam(':dol', $dol);
    $save->bindParam(':doe', $doe);
    $save->bindParam(':matDate', $matDate);
    $save->bindParam(':matPeriod', $matPeriod);
    $save->bindParam(':issueDate', $issueDate);
    $save->bindParam(':cpnRate', $cpnRate);
    $save->bindParam(':cpnPayable', $cpnPayable);
    $save->bindParam(':id', $symbol_id);
    $save->execute();
    
    $dbh->commit();
    $dbh = null;
    $save = null;

    $message = '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Record Updated Successfully.</div></div>';
  } catch(PDOException $e) {
    $dbh->rollBack();
    error_log("Error ===>> ".$e->getMessage().", line => ".$e->getLine());
    $message = '
        <div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> There was an error while operation. Please contact RSEB support.
        </div></div>';
  }
  echo $message;
  die();
}
elseif (isset($_POST['account_unlock'])) {
        $usr_nam = clean($_POST['usr_name']);

        $stmt = $dbh->prepare("DELETE FROM login_attempts WHERE username = ?");
        $stmt->bindParam(1, $usr_nam);
        $result = $stmt->execute();

        if($result) {
            $data = [
                "status" => 1,
                "message" => '<div class="alert alert-success"><i class="icon fa fa-check"></i> Successfully unlocked</div>',
            ];
        } else { 
            $data = [
                "status" => 0,
                "message" => '<div class="alert alert-danger"><i class="icon fa fa-times"></i> There was an error while operation. Please try again later.</div>',
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
}
elseif (isset($_POST['reset_mcams_user_password'])) {
        $usr_nam = clean($_POST['usr_name']);
        $data = [];

        $year = date("Y"); 
        $newPassword = 'adMin@'.$year;
        $message = '';

        try {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();

            // Hash the password using bcrypt
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $updatestmt = $dbh->prepare("UPDATE users SET password = :pwd, is_bcrypt = 1, log_check = 0 WHERE username = :user_nnn AND role_id = 4");
            $updatestmt->bindParam(':pwd', $hashedPassword, PDO::PARAM_STR);
            $updatestmt->bindParam(':user_nnn', $usr_nam, PDO::PARAM_STR);
            $updatestmt->execute();

            $dbh->commit();
            $dbh = null;

            $data = [
                "status" => 1,
                "message" => '<div class="alert alert-success"><i class="icon fa fa-check"></i> Successfully reset password as <b>'.$newPassword.'</div>',
            ];
        } catch(PDOException $e) {
            // error_log($e->getMessage());
            $dbh->rollBack();
            $data = [
                "status" => 0,
                "message" => '<div class="alert alert-danger"><i class="icon fa fa-times"></i> There was an error while operation. Please try again later.</div>',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        die();
}
elseif (isset($_POST['load-data']))
{
  $id=$_POST['id'];
  
  $sql= "SELECT a.*,b.name, (SUM(CAST(REPLACE(b.amount, ',', '') AS DOUBLE))) AS amountPayable FROM uc_payment a LEFT JOIN unclaimed_dividend b ON a.cd_code=b.cd_code WHERE a.id=:id";
  $stmt = $dbh->prepare($sql);
  $stmt->execute(['id' => $id]); 
  $uc = $stmt->fetchALL(PDO::FETCH_ASSOC);
  
  $dataVal = array();
  foreach($uc as $val){
    $dataVal[]=$val;
  }
  echo json_encode($dataVal);
}
elseif (isset($_POST['update-payment']))
{
  $cd_code=$_POST['cd_code'];
  $amount_paid=$_POST['paid_amt'];
  $acc_no=$_POST['acc_no'];
  $bank_name=$_POST['bank_name'];
  $branch_name=$_POST['branch_name'];
  $company_name=$_POST['company_name'];
  $tpn=$_POST['tpn'];

  $data = $dbh->prepare("SELECT amount FROM unclaimed_dividend WHERE cd_code=:cd_code AND company_name=:company_name");
  $data->bindParam(':cd_code',$cd_code);
  $data->bindParam(':company_name',$company_name);
  $data->execute(); // Execute the query
  $datares=$data->fetch();
  $amount_tot=$datares['amount'];
  $am = str_replace(",", "", $amount_tot);
  $a = floatval($am);

  $save = $dbh->prepare("SELECT SUM(amount) AS tot_paid_amt FROM uc_payment WHERE cd_code=:cd_code AND company_name=:company_name");
  $save->bindParam(':cd_code',$cd_code);
  $save->bindParam(':company_name',$company_name);
  $save->execute(); // Execute the query
  $res=$save->fetch();
  $amount=$res['tot_paid_amt'];
  $amt = str_replace(",", "", $amount);
  $at = floatval($amt);

  if($a-$amt <= 0)
  {
    header('location: ../FILES/payment.php?ms=5'); die();
  }
  else
  {
    $sql = $dbh->prepare("INSERT INTO uc_payment (cd_code, amount, account_number, bank_id, branch_id, company_name,tpn, payment_date) VALUES (:cd_code, :amount, :acc_no, :bank_name, :branch_name, :company_name,:tpn, NOW())");

    $sql->bindParam(':cd_code', $cd_code);
    $sql->bindParam(':amount', $amount_paid);
    $sql->bindParam(':acc_no', $acc_no);
    $sql->bindParam(':bank_name', $bank_name);
    $sql->bindParam(':branch_name', $branch_name);
    $sql->bindParam(':company_name', $company_name);
    $sql->bindParam(':tpn', $tpn);

  }
  
  if($sql->execute())
  {
      header('location: ../FILES/payment.php?ms=3'); die();
  }  
  else
  {
      header('location: ../FILES/payment.php?ms=4'); die();
  }
} 
elseif (isset($_POST['edit-payment']))
{
  $edit_id=$_POST['edit_id'];
  $cd_code=$_POST['edit_cd_code'];
  $amount_paid=$_POST['edit_paid_amt'];
  $acc_no=$_POST['edit_acc_no'];
  $bank_name=$_POST['edit_bank_name'];
  $company_name=$_POST['edit_company_name'];
  $branch_name=$_POST['edit_branch_name'];
  $tpn=$_POST['edit_tpn'];

  $data = $dbh->prepare("SELECT amount FROM unclaimed_dividend WHERE cd_code=:cd_code AND company_name=:company_name");
  $data->bindParam(':cd_code', $cd_code);
  $data->bindParam(':company_name', $company_name);
  $data->execute(); // Execute the query
  $datares=$data->fetch();
  $amount_tot=$datares['amount'];
  $am = str_replace(",", "", $amount_tot);
  $a = floatval($am);

  if($a-$amount_paid < 0)
  {
    header('location: ../FILES/payment.php?ms=5'); die();
  }
  else
  {
    $sql=$dbh->prepare("UPDATE uc_payment set cd_code=:cd_code, amount=:amount, account_number=:acc_no, bank_id=:bank_name,branch_id=:branch_name, company_name=:company_name, tpn=:tpn WHERE id=:id"); 
    $sql->bindParam(':id', $edit_id);
    $sql->bindParam(':cd_code', $cd_code);
    $sql->bindParam(':amount', $amount_paid);
    $sql->bindParam(':acc_no', $acc_no);
    $sql->bindParam(':bank_name', $bank_name);
    $sql->bindParam(':branch_name', $branch_name);
    $sql->bindParam(':company_name', $company_name);
    $sql->bindParam(':tpn', $tpn);
  }
  
  if($sql->execute())
  {
      header('location: ../FILES/payment.php?ms=3'); die();
  }  
  else
  {
      header('location: ../FILES/payment.php?ms=4'); die();
  }
} 
elseif (isset($_POST['get-total-amt'])) {
    $cd_code = trim($_POST['cd_code']);

    // Helper function to get summed amount from a table
    function getTotalAmount($dbh, $table, $cd_code) {
        $sql = $dbh->prepare("SELECT SUM(CAST(REPLACE(amount, ',', '') AS DOUBLE)) AS total FROM {$table} WHERE cd_code = :cd_code");
        $sql->bindParam(':cd_code', $cd_code);
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        return isset($result['total']) ? (float)$result['total'] : 0;
    }

    $unclaimed_total = getTotalAmount($dbh, 'unclaimed_dividend', $cd_code);
    $paid_total = getTotalAmount($dbh, 'uc_payment', $cd_code);
    $final_amount = round($unclaimed_total - $paid_total, 2);

    // Get name from unclaimed_dividend
    $sql = $dbh->prepare("SELECT name FROM unclaimed_dividend WHERE cd_code = :cd_code LIMIT 1");
    $sql->bindParam(':cd_code', $cd_code);
    $sql->execute();
    $name_res = $sql->fetch(PDO::FETCH_ASSOC);
    $name = $name_res['name'] ?? '';

    echo json_encode([
        'name' => $name,
        'final_amount' => $final_amount
    ]);
    die(); 
}

elseif(isset($_POST['update_cdcode']))
{
  $cid_cd = $_POST['cid_cd'];

  if($cid_cd=='cid')
  {
    $ncid = $_POST['ncid'];
    $ocd = $_POST['olcdCode'];

    $q=$dbh->prepare("UPDATE unclaimed_dividend SET cid_no=:ncid WHERE cd_code=:ocd"); 
    $q->bindParam(':ncid', $ncid);
    $q->bindParam(':ocd', $ocd);

    if($q->execute())
    {
      echo'
      <div class="col-lg-8 col-xs-8">
        <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Record updated Successfully.</div>
      </div>';
    }else{
      echo'
      <div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div>
      </div>';
    }
  }
  else
  {
    $ocd = $_POST['ocd'];
    $ncd = $_POST['ncd'];

    $q=$dbh->prepare("UPDATE unclaimed_dividend SET cd_code=:ncd WHERE cd_code=:ocd"); 
    $q->bindParam(':ncd', $ncd);
    $q->bindParam(':ocd', $ocd);

    if($q->execute())
    {
      echo'
      <div class="col-lg-8 col-xs-8">
        <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Record updated Successfully.</div>
      </div>';
    }else{
      echo'
      <div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div>
      </div>';
    }
  }
}
elseif(isset($_POST['get-cd-cid']))
{
  $cd_code = $_POST['cd_code'];

  $q=$dbh->prepare("SELECT cid_no FROM unclaimed_dividend a WHERE a.cd_code = :cd_code"); 
  $q->bindParam(':cd_code', $cd_code);
  $q->execute();
  $res=$q->fetch();

  $cid = $res['cid_no'];

  echo $cid;
  die();

}
elseif (isset($_GET['get-cd-code'])) {
    header('Content-Type: application/json'); // Make sure JSON is the response type
    ini_set('display_errors', 0);             // Hide any PHP warnings/notices in response

    try {
        $comp_name = $_GET['comp_name'];

        $sql = "SELECT a.cd_code 
                FROM unclaimed_dividend a 
                LEFT JOIN uc_companies b ON a.company_name = b.short_desc
                LEFT JOIN uc_payment c ON a.cd_code = c.cd_code
                WHERE a.company_name = :comp_name AND c.cd_code IS NULL
                ORDER BY a.cd_code ASC";
                
        $stmt = $dbh->prepare($sql);
        $stmt->execute([':comp_name' => $comp_name]); 
        $dataVal = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($dataVal);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to fetch data']);
    }

    exit; // Stop further output
}
elseif (isset($_GET['get-bank_branch']))
{
    $bank_id=$_GET['bank_id'];
    $sql= "SELECT * from bank_branch WHERE BANK_ID=:bank_id ORDER BY bank_id ASC";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([':bank_id' => $bank_id]); 
    
    $dataVal = $stmt->fetchALL(PDO::FETCH_ASSOC);
    echo json_encode($dataVal);
    die();
}
elseif (isset($_POST["excelUpload"])) {

    ini_set('upload_max_filesize', '50M');
    ini_set('post_max_size', '50M');

    $vpb_file_name = $_FILES['vasplus_multiple_files']['name'];
    $vpb_uploaded_files_location = 'C:/inetpub/wwwroot/RSEB/unclaimedUploads/' . date('YmdHis') . '/';

    // Create directory if not exists
    if (!is_dir($vpb_uploaded_files_location)) {
        mkdir($vpb_uploaded_files_location, 0777, true);
    }

    // Final path
    $vpb_final_location = $vpb_uploaded_files_location . $vpb_file_name;
    $vpb_final_location_mysql = str_replace("\\", "/", $vpb_final_location); // Convert to MySQL-compatible path

    // Detect delimiter
    $file_ext = strtolower(pathinfo($vpb_file_name, PATHINFO_EXTENSION));
    $delimiters = array(',' => 0, ';' => 0, "\t" => 0, '|' => 0);
    $firstLine = '';
    $handle = fopen($_FILES['vasplus_multiple_files']['tmp_name'], 'r');
    if ($handle) {
        $firstLine = fgets($handle);
        fclose($handle);
    }

    if ($firstLine) {
        foreach ($delimiters as $delimiterVal => &$count) {
            $count = count(str_getcsv($firstLine, $delimiterVal));
        }
        $delimiter = array_search(max($delimiters), $delimiters);
    } else {
        $delimiter = ','; // default
    }

    // Move uploaded file to final location
    move_uploaded_file($_FILES['vasplus_multiple_files']['tmp_name'], $vpb_final_location);

    // Load data into database using LOCAL INFILE
    $sql = "LOAD DATA LOCAL INFILE '" . $vpb_final_location . "' 
        INTO TABLE unclaimed_dividend 
        CHARACTER SET utf8mb4 
        FIELDS TERMINATED BY '" . $delimiter . "' 
        ENCLOSED BY '\"' 
        LINES TERMINATED BY '\r\n'
        IGNORE 1 ROWS
        (@col1, @col2, @col3, @col4, @col5, @col6, @col7)
        SET
        cd_code = TRIM(REPLACE(REPLACE(REPLACE(@col1, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        name = TRIM(REPLACE(REPLACE(REPLACE(@col2, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        amount = TRIM(REPLACE(REPLACE(REPLACE(@col3, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        year = TRIM(REPLACE(REPLACE(REPLACE(@col4, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        company_name = TRIM(REPLACE(REPLACE(REPLACE(@col5, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        CID = TRIM(REPLACE(REPLACE(REPLACE(@col6, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        remarks = TRIM(REPLACE(REPLACE(REPLACE(@col7, CHAR(10), ''), CHAR(13), ''), ', , ', '')),
        uploaded_date = CURDATE();";

    // Execute query
    $data = $dbh->prepare($sql);
    if ($data->execute()) {
        header('Location: ../FILES/upload.php?ms=1'); // Success
        exit();
    } else {
        header('Location: ../FILES/upload.php?ms=2'); // Failure
        exit();
    }
}
else if (isset($_POST["get_escrow_cid_details"])) {
    // Validate and sanitize input
    $cid = filter_input(INPUT_POST, 'cid_no');
    if (empty($cid)) {
        echo '<div class="alert alert-danger">CID number is required</div>';
        exit;
    }

    try {
        // Use a more efficient query with explicit column selection
        $sql = "SELECT a.name, a.cd_code, a.cid, a.amount, a.company_name, a.year
                FROM unclaimed_dividend a 
                LEFT JOIN uc_payment c ON a.CID = c.cid_no AND c.cid_no = :cid
                WHERE a.CID = :cid AND c.cid_no IS NULL
                ORDER BY a.CID ASC";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([':cid' => $cid]);
        
        if ($stmt->rowCount() === 0) {
            echo '<div class="alert alert-warning">No records found for CID: ' . htmlspecialchars($cid) . '</div>';
            exit;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_amount = array_sum(array_column($rows, 'amount'));
        $firstRow = $rows[0];
        $name = htmlspecialchars($firstRow['name'] ?? 'NA');
        $company_name = htmlspecialchars($firstRow['company_name'] ?? 'NA');

        // Generate table HTML
        $tableHtml = '<table border="1" class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>CD Code</th>
                    <th>CID</th>
                    <th>Amount</th>
                    <th>Company Name</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($rows as $row) {
            $tableHtml .= '<tr>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['cd_code']) . '</td>
                <td>' . htmlspecialchars($row['cid']) . '</td>
                <td>' . htmlspecialchars($row['amount']) . '</td>
                <td>' . htmlspecialchars($row['company_name']) . '</td>
                <td>' . htmlspecialchars($row['year']) . '</td>
            </tr>';
        }

        $tableHtml .= '<tr style="font-weight:bold;">
                <td colspan="3">Total Amount</td>
                <td>' . htmlspecialchars(number_format($total_amount, 2)) . '</td>
                <td colspan="2"></td>
            </tr>
            </tbody></table>';

        // Get banks and branches in single queries
        $banks = $dbh->query("SELECT bank_id, bank_name FROM banks ORDER BY bank_name ASC")->fetchAll();
        $branches = $dbh->query("SELECT BANK_ID, BRANCH_NAME FROM bank_branch ORDER BY BRANCH_NAME ASC")->fetchAll();

        // Generate modal HTML
        $modalHtml = '
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal" data-cid="' . htmlspecialchars($cid) . '">
            Make Payment
        </button>

        <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="paymentForm" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="process_payment" value="1">
                            <input type="hidden" name="cid_no" value="' . htmlspecialchars($cid) . '">
                            <input type="hidden" name="amount" value="' . $total_amount . '">
                            <input type="hidden" name="company_name" value="' . $company_name . '">

                            <div class="row mb-3">
                                <div class="col-md-6"><strong>Name:</strong> ' . $name . '</div>
                                <div class="col-md-6"><strong>CID:</strong> ' . htmlspecialchars($cid) . '</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6"><strong>Company:</strong> ' . $company_name . '</div>
                                <div class="col-md-6"><strong>Total Amount:</strong> ' . htmlspecialchars(number_format($total_amount, 2)) . '</div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="bank_name">Payment Bank</label>
                                <select class="form-control select2bs4" name="bank_id" required>
                                    <option value="">--SELECT--</option>';
        
        foreach ($banks as $bank) {
            $modalHtml .= '<option value="' . htmlspecialchars($bank['bank_id']) . '">' . 
                          htmlspecialchars($bank['bank_name']) . '</option>';
        }

        $modalHtml .= '</select>
                            </div>

                            <div class="form-group">
                                <label for="branch_name">Payment Bank Branch</label>
                                <select class="form-control select2bs4" name="branch_id" required>
                                    <option value="">--SELECT--</option>';
        
        foreach ($branches as $branch) {
            $modalHtml .= '<option value="' . htmlspecialchars($branch['BANK_ID']) . '">' . 
                          htmlspecialchars($branch['BRANCH_NAME']) . '</option>';
        }

        $modalHtml .= '</select>
                            </div>

                            <div class="form-group">
                                <label for="acc_no">Account Number</label>
                                <input type="text" class="form-control" name="account_no" pattern="[0-9]+" title="Numbers only" required>
                            </div>
                            <div class="form-group">
                                <label for="tpn">TPN</label>
                                <input type="text" class="form-control" name="tpn" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="confirm-payment">Confirm Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

        echo $tableHtml . $modalHtml;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo '<div class="alert alert-danger">An error occurred while processing your request</div>';
    }
}
elseif (isset($_POST["get_escrow_cid_tds_details"])) {
    $cid = $_POST['cid_no'] ?? '';

    if (empty($cid)) {
        echo '<div class="alert alert-danger">CID number is required</div>';
        return;
    }

    try {
        // Get payment details with total amount > 30000 (from payment table only)
        $payment_stmt = $dbh->prepare("
            SELECT 
                cid_no,
                SUM(amount) AS total_payment
            FROM uc_payment
            WHERE cid_no = :cid
            AND remarks LIKE '%dividend%'
            GROUP BY cid_no
            HAVING SUM(amount) > 30000
        ");
        $payment_stmt->execute([':cid' => $cid]);
        
        $eligible_payment = $payment_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$eligible_payment) {
            echo '<div class="alert alert-warning">No eligible payments found for CID: ' . 
                 htmlspecialchars($cid) . ' (Total payment must be > 30,000)</div>';
            return;
        }

        // Get payment details from payment table only (without joining unclaimed_dividend)
        $payment_details_stmt = $dbh->prepare("
            SELECT 
                id,
                cid_no,
                amount,
                tpn,
                payment_date,
                company_name
            FROM uc_payment
            WHERE cid_no = :cid
            ORDER BY payment_date DESC
        ");
        $payment_details_stmt->execute([':cid' => $cid]);
        
        $rows = $payment_details_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo '<div class="alert alert-warning">No payment records found for CID: ' . 
                 htmlspecialchars($cid) . '</div>';
            return;
        }

        // Prepare output
        $output = [];
        $output[] = '<div class="table-responsive">';
        $output[] = '<table class="table table-bordered">';
        $output[] = '<thead><tr>
                        <th>CID</th>
                        <th>Amount</th>
                        <th>TPN</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Remarks</th>
                        <th>Action</th>
                     </tr></thead><tbody>';
        
        foreach ($rows as $row) {
            $output[] = '<tr>';
            $output[] = '<td>' . htmlspecialchars($row['cid_no'] ?? 'NA') . '</td>';
            $output[] = '<td>' . number_format($row['amount'] ?? 0, 2) . '</td>';
            $output[] = '<td>' . htmlspecialchars($row['tpn'] ?? 'NA') . '</td>';
            $output[] = '<td>' . htmlspecialchars($row['payment_date'] ?? 'NA') . '</td>';
            $output[] = '<td>' . htmlspecialchars($row['payment_method'] ?? 'NA') . '</td>';
            $output[] = '<td>' . htmlspecialchars($row['remarks'] ?? 'NA') . '</td>';
            $output[] = '<td><a href="load.php?id=' . $row['id'] . '&get_payment_tds=get_payment_tds" 
                                         target="_blank" 
                                         class="btn btn-default btn-border">
                                        <i class="fa fa-fw fa-download"></i>
                                      </a></td>';
            $output[] = '</tr>';
        }

        $output[] = '<tr class="table-primary">
                        <td><strong>Total Payment</strong></td>
                        <td><strong>' . number_format($eligible_payment['total_payment'], 2) . '</strong></td>
                        <td colspan="5"></td>
                     </tr>';
        $output[] = '</tbody></table></div>';

        echo implode("\n", $output);
        
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database error: ' . 
             htmlspecialchars($e->getMessage()) . '</div>';
    }
}
elseif (isset($_POST['process_payment'])) {
    $cid_no     = $_POST['cid_no'];
    $amount     = $_POST['amount'];
    $bank_id    = $_POST['bank_id'];
    $branch_id  = $_POST['branch_id'];
    $account_no = $_POST['account_no'];
    $tpn        = $_POST['tpn'];
    $company_name = $_POST['company_name'];

    try {
        $sql = "INSERT INTO uc_payment (cid_no, amount, account_number, bank_id, branch_id, tpn,company_name, payment_date)
                VALUES (:cid_no, :amount, :account_no, :bank_id, :branch_id, :tpn,:company_name, NOW())";

        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':cid_no'     => $cid_no,
            ':amount'     => $amount,
            ':account_no' => $account_no,
            ':bank_id'    => $bank_id,
            ':branch_id'  => $branch_id,
            ':tpn'        => $tpn,
            ':company_name' => $company_name
        ]);

        echo '<div class="alert alert-success">Payment successfully recorded.</div>';

    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
elseif (isset($_POST['share__transfer__submit'])) {
    $f_cd = strtoupper($_POST['F_cd']);
    $t_cd = strtoupper($_POST['T_cd']);
    $sy = $_POST['sy'];
    $rm = $_POST['remarks'];
    $vol = $_POST['trs'];
    $user_name = $_POST['userName'];

    //To check existing volume
    $q22 = $dbh->prepare("SELECT volume FROM cds_holding WHERE cd_code = ? AND symbol_id = ?");
    $q22->execute([$f_cd, $sy]);
    $vol_existing = $q22->fetchColumn();

    if ($vol_existing < $vol) {
        echo'
        <div class="row">
            <div class="col-lg-12 col-xs-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
                    Insufficient Volume to be Transferred
                </div>
            </div>
        </div>';
        die();
    }

    // check if same ID exist or not
    $stmt = $dbh->prepare("SELECT 1 FROM client_account WHERE cd_code IN (?, ?) GROUP BY ID HAVING COUNT(DISTINCT cd_code) = 2");
    $stmt->execute([$f_cd, $t_cd]);
    $same_person = $stmt->fetchColumn();
    if ($same_person) {
        echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-check"></i> Cannot transfer to same individual. </div></div>';
        exit();
    }

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        // insert into 
        $stmt = $dbh->prepare("INSERT INTO application_cds_transfers (from_account, to_account, symbol_id, vol_transfer, remarks, status, user_name, type) VALUES(?, ?, ?, ?, ?, 'SUBMITTED', ?, 'ST')");
        $stmt->execute([$f_cd, $t_cd, $sy, $vol, $rm, $user_name]);

        //minus vol to be transferred from CD Code (keep it in pending_out_vol)
        $update = $dbh->prepare("UPDATE cds_holding SET volume = volume - ?, pending_out_vol = pending_out_vol + ? WHERE cd_code = ? AND symbol_id = ?");
        $update->execute([$vol, $vol, $f_cd, $sy]);

        if ($update->rowCount() === 0) {
            throw new Exception("UPDATE query affected 0 rows. Possible invalid cd_code or symbol_id.");
        }

        $dbh->commit();

        echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><i class="icon fa fa-check"></i> Successfully Submitted.</div></div>';
    } catch (Exception $e) {
        $dbh->rollBack();
        error_log("Exception occurred ==> {$e->getMessage()}, at {$e->getLine()}");
        echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-times"></i> Exception occurred. Please contact RSEB IT support.</div></div>';
    }
    $dbh = null;
    exit();
}
elseif (isset($_POST['share__transfer__approval'])) {
    $id = $_POST['id'];
    $user_name = $_SESSION['sess_username'];
    $update_date = date('Y:m:d H:i:s');

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        $get_dtls =  $dbh->prepare("SELECT c.from_account, c.to_account, c.symbol_id, c.vol_transfer, c.remarks, c.`status`, c.user_name, c.`type`, c.created_at  
                          FROM application_cds_transfers c 
                          WHERE c.id = ?
        ");
        $get_dtls->execute([$id]);
        $row = $get_dtls->fetch(PDO::FETCH_ASSOC);

        // check transferer vol (pending_out_vol) available or not
        $check = $dbh->prepare("SELECT 1 FROM cds_holding WHERE cd_code = ? AND symbol_id = ? AND pending_out_vol >= ?");
        $check->execute([
            $row['from_account'], $row['symbol_id'], $row['vol_transfer']
        ]);
        $count = $check->fetchColumn();

        if ($count) {
            // insert into logs
            $insert_log = $dbh->prepare("INSERT INTO application_cds_transfer_logs (transfer_id, from_account, to_account, symbol_id, vol_transfer, remarks, status, user_name, type, created_at) 
              SELECT id, from_account, to_account, symbol_id, vol_transfer, remarks, status, user_name, type, created_at FROM application_cds_transfers WHERE id = ?");
            $insert_log->execute([ $id ]);

            $update_from_holding = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = pending_out_vol - ? WHERE symbol_id = ? AND cd_code = ?");
            $update_from_holding->execute([
                $row['vol_transfer'],  $row['symbol_id'], $row['from_account']
            ]);

            // Check if receiver account exist or not in cds holding
            $exist = $dbh->prepare("SELECT 1 FROM cds_holding WHERE cd_code = ? AND symbol_id = ?");
            $exist->execute([
                $row['to_account'], $row['symbol_id']
            ]);
            $result = $exist->fetchColumn();

            if ($result) {
                $update_to_holding = $dbh->prepare("UPDATE cds_holding SET volume = volume + ? WHERE symbol_id = ? AND cd_code = ?");
                $update_to_holding->execute([
                    $row['vol_transfer'],  $row['symbol_id'], $row['to_account']
                ]);
            } else {
                $save = $dbh->prepare("INSERT INTO cds_holding (cd_code, volume, symbol_id, user_name, institution_id) VALUES(?, ?, ?, ?, ?)");
                $save->execute([
                     $row['to_account'], $row['vol_transfer'], $row['symbol_id'],  $user_name,  $institution_id
                ]);
            }

            // insert to cds_transfer to record
            $save = $dbh->prepare("INSERT INTO cds_transfer (from_acc, to_acc, symbol_id, trs_vol, remarks, user_name, type) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $save->execute([
                $row['from_account'], $row['to_account'], $row['symbol_id'],  $row['vol_transfer'],  $row['remarks'], $user_name, $row['type']
            ]);

            // update application status
            $update = $dbh->prepare("UPDATE application_cds_transfers SET status = 'APPROVED', updated_at = ? WHERE id = ?");
            $update->execute([ $update_date,  $id ]);

        } else {
            echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-check"></i> Insufficient Volume, cannot transfer.</div></div>';
            exit();
        }

        $dbh->commit();

        echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><i class="icon fa fa-check"></i> Successfully Approved Application.</div></div>';
    } catch (Exception $e) {
        $dbh->rollBack();
        error_log("Exception occurred ==> {$e->getMessage()}, at {$e->getLine()}");
        echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><i class="icon fa fa-times"></i> Exception occurred. Please contact RSEB IT support.</div></div>';
    }
    $dbh = null;
    exit();
}
else {
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was no function</div></div></div>';
  exit();
}
?>
