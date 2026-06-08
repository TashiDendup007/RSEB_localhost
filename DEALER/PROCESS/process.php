<?php 
date_default_timezone_set("Asia/Thimphu");
//include ('../../CONNECTIONS/function-sanitize.php');
include ('../FILES/session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php'); 
include ('../../CONNECTIONS/trading_hours.php');

$check = $dbh->prepare('SELECT a.institution_id, c.participant_code FROM adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code AND b.institution_id = a.institution_id AND c.username = :un');
$check->bindParam(':un', $username);
$check->execute();
$res = $check->fetch();
$institution_id = $res['institution_id'];
$particpant_code = $res['participant_code'];
//Saving Record

if (isset($_POST['save_client_dtls'])) { 
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

		// Check CID exists
		$checkId = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE ID = :id");
		$checkId->bindParam(':id', $id);
		$checkId->execute();
		$count_id = $checkId->fetchColumn();
		if ($count_id > 0) {
			echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> CID/DISN number already existed.</div></div></div>';
			die();
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

		$save = $dbh->prepare("INSERT INTO client_account (acc_type,cd_code,title,f_name,l_name,occupation,nationality,ID,DzongkhagID,tpn,phone,email,bank_id,bank_account,bank_account_type,bro_comm_id,address,institution_id,user_name, license_no) 
			VALUES(:atype,:cdcode,:title,:fn,:ln,:occupation,:nat,:id,:dz,:tpn,:phone,:email,:bank,:accno,:bankAccType,:commission,:add,:institution_id,:username,:licenseNo)");
		$save->bindParam(':atype', $atype);
		$save->bindParam(':cdcode', $cdcode);
		$save->bindParam(':title', $title);
		$save->bindParam(':fn', $fn);
		$save->bindParam(':ln', $ln);
		$save->bindParam(':occupation', $occupation);
		$save->bindParam(':nat', $nat);
		$save->bindParam(':id', $id);
		$save->bindParam(':dz', $dz);
		$save->bindParam(':tpn', $tpn);
		$save->bindParam(':phone', $phone);
		$save->bindParam(':email', $email);
		$save->bindParam(':bank', $bank);
		$save->bindParam(':accno', $accno);
		$save->bindParam(':bankAccType', $bankAccType);
		$save->bindParam(':commission', $commission);
		$save->bindParam(':add', $add);
		$save->bindParam(':institution_id', $institution_id);
		$save->bindParam(':username', $username);
		$save->bindParam(':licenseNo', $licenseNo);
		$save->execute();
		
		$dbh->commit();
		
		$message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Operation Successfully Completed.</div></div></div>';
	} catch(PDOException $e) {
		$dbh->rollBack();
		error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

		$message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> An error occurred. Please contact RSEB support.</div></div></div>';
	}
	$dbh = null;
	echo $message;
	die();
} 
elseif (isset($_POST['save_client_dtls_new'])) {
	// error_log(json_encode($_POST));
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

	if ($atype === 'I') {
		if (strlen($id) != 11) {
			echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> CID number must be 11 digits</div></div></div>';
			die();
		}
	}

	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		$user_mem_code = substr($username, 0, 7);

		// Check CID exists
		$checkId = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE ID = :id AND SUBSTRING(user_name, 1, 7) = :mem_code");
		$checkId->bindParam(':id', $id);
		$checkId->bindParam(':mem_code', $user_mem_code);
		$checkId->execute();
		$count_id = $checkId->fetchColumn();
		if ($count_id > 0) {
			echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> CID/DISN number already registered with the broker.</div></div></div>';
			die();
		}

		$stmt = $dbh->prepare("SELECT a.cd_code, a.ca_date, SUBSTRING(a.user_name, 1, 7) AS mem_code FROM client_account a 
				WHERE SUBSTRING(a.user_name, 1, 7) = ? 
				AND YEAR(a.ca_date) = ?
				ORDER BY a.client_id DESC LIMIT 1
		");
		$stmt->execute([$user_mem_code, $year]);
		$rows = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($rows) {
			$last_cd_code = $rows['cd_code'];
			$mem_code = $rows['mem_code'];

			$prefix_length = 0;

	    if (in_array($mem_code, ['MEMRICB', 'MEMBOBL', 'MEMBNBL'])) {
	        $prefix_length = 1;
	    } elseif (in_array($mem_code, ['MEMBDBL', 'MEMBPCL', 'MEMRINS'])) {
	        $prefix_length = 2;
	    } elseif (in_array($mem_code, ['MEMDSBP', 'MEMSERS'])) {
	        $prefix_length = 3;
	    } //elseif ($mem_code === 'MEMLDSB') {
	    else { 
	        $prefix_length = 4;
	    }

	    $prefix = substr($last_cd_code, 0, $prefix_length);
	    $numeric_part = intval(substr($last_cd_code, $prefix_length));
	    $cd_code_new_number = $numeric_part + 1;
	    $cdcode = $prefix . str_pad($cd_code_new_number, strlen($last_cd_code) - $prefix_length, '0', STR_PAD_LEFT);

		} else {
			$mem_code = substr($username, 0, 7);
			$short_year = date('y');

			switch ($mem_code) {
			    case 'MEMBOBL':
			        $cdcode = 'B' . $year . '00001';
			        break;
			    case 'MEMBNBL':
			        $cdcode = 'U' . $year . '00001';
			        break;
			    case 'MEMRICB':
			        $cdcode = 'R' . $year . '00001';
			        break;
			    case 'MEMBDBL':
			        $cdcode = 'BD' . $short_year . '000001';
			        break;
			    case 'MEMBPCL':
			        $cdcode = 'BP' . $short_year . '000001';
			        break;
			    case 'MEMRINS':
			        $cdcode = 'RN' . $short_year . '000001';
			        break;
			    case 'MEMSERS':
			        $cdcode = 'SER' . $short_year . '00001';
			        break;
			    case 'MEMDSBP':
			        $cdcode = 'DSB' . $short_year . '00001';
			        break;
			    case 'MEMLDSB':
			        $cdcode = 'LDSB' . $short_year . '0001';
			        break;
			    case 'MEMDKLT':
			        $cdcode = 'DKLT' . $short_year . '0001';
			        break;
			    default:
			    		$cd_prefix_char = substr($mem_code, -4);
			    		$cdcode = $cd_prefix_char . $short_year . '0001';
			        break;
			}

		}

		// Check CD Code exist
		$check_cdCode = $dbh->prepare("SELECT COUNT(*) FROM client_account WHERE cd_code = :cd");
		$check_cdCode->bindParam(':cd', $cdcode);
		$check_cdCode->execute();
		$count_cdCode = $check_cdCode->fetchColumn();
		if ($count_cdCode > 0) {
			echo '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> CD Code already existed.</div></div></div>';
			die();
		}

		$save = $dbh->prepare("INSERT INTO client_account (acc_type, cd_code, title, f_name, l_name, occupation, nationality, ID, DzongkhagID, tpn, phone, email, bank_id, bank_account, bank_account_type, bro_comm_id, address, institution_id, license_no, dob, guardian_name, gender, marital_status, gewog_id, village_id, user_name) 
			VALUES(:atype, :cdcode, :title, :fn, :ln, :occupation, :nat, :id, :dz, :tpn, :phone, :email, :bank, :accno, :bankAccType, :commission, :add, :institution_id, :licenseNo, :dob, :grd_name, :gender, :marital, :gwg_id, :vilge_id, :username)
		");
		$save->bindParam(':atype', $atype);
		$save->bindParam(':cdcode', $cdcode);
		$save->bindParam(':title', $title);
		$save->bindParam(':fn', $fn);
		$save->bindParam(':ln', $ln);
		$save->bindParam(':occupation', $occupation);
		$save->bindParam(':nat', $nat);
		$save->bindParam(':id', $id);
		$save->bindParam(':dz', $dz);
		$save->bindParam(':tpn', $tpn);
		$save->bindParam(':phone', $phone);
		$save->bindParam(':email', $email);
		$save->bindParam(':bank', $bank);
		$save->bindParam(':accno', $accno);
		$save->bindParam(':bankAccType', $bankAccType);
		$save->bindParam(':commission', $commission);
		$save->bindParam(':add', $add);
		$save->bindParam(':institution_id', $institution_id);
		$save->bindParam(':licenseNo', $licenseNo);
		$save->bindParam(':dob', $dob);
		$save->bindParam(':grd_name', $guardian_name);
		$save->bindParam(':gender', $gender);
		$save->bindParam(':marital', $marital);
		$save->bindParam(':gwg_id', $gewog_id);
		$save->bindParam(':vilge_id', $village_id);
		$save->bindParam(':username', $username);
		$save->execute();

		// error_log($stmt->debugDumpParams());

		$dbh->commit();
		
		$message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Successfully Registered. CD Code: '.$cdcode.'</div></div></div>';

	} catch(PDOException $e) {
		$dbh->rollBack();
		error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
		$message = '<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation. Please contact RSEB support.</div></div></div>';
	}
	$dbh = null;
	echo $message;
	die();
}
elseif (isset($_POST['edit_cli'])) { 
		$atype = $_POST['atype'];
		$title = $_POST['title'];
		$fn = $_POST['fn'];
		$ln = $_POST['ln'];
		$occupation = $_POST['occupation'];
		$nat = $_POST['nat'];
		$dz = $_POST['dz'];
		$gewog_id = $_POST['gewog_id'];
		$village_id = $_POST['village_id'];
		$tpn = $_POST['tpn'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		$bank = $_POST['bank'];
		$accno = $_POST['accno'];
		$bankAccType = $_POST['bankAccType'];
		$commission = $_POST['commis'];
		$address = $_POST['add'];
		$cli_id = $_POST['client_id'];
		$licenseNo = $_POST['licenseNo'];
		$cid_no = $_POST['cid_no'];
		$message = '';

		try {
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $dbh->beginTransaction();

	    $save = $dbh->prepare("UPDATE client_account 
		    	SET acc_type=:atype, title=:title, f_name=:fn, l_name=:ln, occupation=:occupation, nationality=:nat, DzongkhagID=:dz, gewog_id=:gewog_id, village_id=:village_id, tpn=:tpn, phone=:phone, email=:email, bank_id=:bank, bank_account=:accno, bank_account_type=:bankAccType, bro_comm_id=:comm, address=:add, license_no=:liceNo 
		    	WHERE client_id=:id
	    ");
			$save->bindParam(':atype', $atype);
			$save->bindParam(':title', $title);
			$save->bindParam(':fn', $fn);
			$save->bindParam(':ln', $ln);
			$save->bindParam(':occupation', $occupation);
			$save->bindParam(':nat', $nat);
			$save->bindParam(':dz', $dz);
			$save->bindParam(':village_id', $village_id);
			$save->bindParam(':gewog_id', $gewog_id);
			$save->bindParam(':tpn', $tpn);
			$save->bindParam(':phone', $phone);
			$save->bindParam(':email', $email); 
			$save->bindParam(':bank', $bank);
			$save->bindParam(':accno', $accno);
			$save->bindParam(':bankAccType', $bankAccType);
			$save->bindParam(':comm', $commission);
			$save->bindParam(':add', $address);
			$save->bindParam(':liceNo', $licenseNo);
			$save->bindParam(':id', $cli_id);
			$save->execute();

			$check = $dbh->prepare("SELECT * FROM users WHERE cid = :cid");
			$check->bindParam(":cid", $cid_no);
			$check->execute();
			if ($check->fetch()) {
				$update = $dbh->prepare("UPDATE users SET email = :eml WHERE cid = :cidd");
				$update->bindParam(":eml", $email);
				$update->bindParam(":cidd", $cid_no);
				$update->execute();
			}

	    $dbh->commit();
	    $dbh = null;
	    $message = '<div class="col-lg-12 col-sm-12">
	    							<div class="alert alert-success alert-dismissible">
											<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning">
											</i> Successfully Updated.
										</div>
									</div>';
	  } catch (PDOException $e) {
	    $dbh->rollBack();
	    error_log("Exception = > " . $e->getMessage() . "<br> Code => " . $e->getCode() . "<br> Line => " . $e->getLine());
	    $message = '<div class="col-lg-12 col-sm-12">
	    							<div class="alert alert-danger alert-dismissible">
											<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> 
											Exception occured. Please Contact RSEB support.
										</div>
									</div>';
	  }
	  echo $message;
	  die();
} 
elseif (isset($_POST['delete_nominee'])) {
	$id=$_POST['delete_nom'];

	$save = $dbh->prepare("DELETE from client_nominee where nominee_id=:id ");
	$save->bindParam(':id',$id);
	if($save->execute())
	{
		echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
		<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning">
		</i> Record Deleted Successfully.</div></div>';
		die();
	}else{
		echo'<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> There was an error while operation.</div></div></div>';
		die();
	} 
}
//bbo finance start
elseif (isset($_POST['deb'])) {
	$cd_code=$_POST['cdcode'];
	$amt = $_POST['amt'];
	$rm = $_POST['rm'];
	$flag_debit = 0;
	try{
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $check = $dbh->prepare("SELECT sum(amount) as amount FROM bbo_finance where cd_code=:cd");
		$check->bindParam(':cd',$cd_code);
		$check->execute();
		$res = $check->fetch();

		if ($res['amount'] >= $amt) {
			$amt = -abs($amt); // ensure amount is negative

			$save = $dbh->prepare("INSERT into bbo_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id) 
				VALUES (:cd_code, :amount, :remarks, :flag_debit, :flag_debit, :username, :institution_id)");
			$save->bindParam(':cd_code', $cd_code);
			$save->bindParam(':amount', $amt);
			$save->bindParam(':remarks', $rm);
			$save->bindParam(':flag_debit', $flag_debit);
			$save->bindParam(':username', $username);
			$save->bindParam(':institution_id', $institution_id);
			$save->execute();

			$dbh->commit();
    	$dbh = null;
			echo'
			<div class="col-lg-12 col-xs-12">
				<div class="alert alert-success alert-dismissible">
					<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Operation Successfully Completed.
				</div>
			</div>';
			die();
		} else {
			echo'
			<div class="col-lg-12 col-xs-12">
				<div class="alert alert-warning alert-dismissible">
					<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> Oops Sorry! Your Balance is '.$res['amount'].' , insufficient fund.
				</div>
			</div>';
			die();
		} 
  }catch(PDOException $e){
    $dbh->rollBack();
    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo'
  	<div class="col-lg-12 col-xs-12">
	  	<div class="alert alert-danger alert-dismissible">
	  		<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> There was an error while operation.
	  	</div>
  	</div>';
    die();
  }
} 
elseif (isset($_POST['cre'])) { 
	$cd_code = $_POST['cdcode'];
	$amt = $_POST['amt'];
	$rm = $_POST['rm'];
	$flag_debit = 1;
	try{
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("INSERT into bbo_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id) 
    	VALUES (:cd_code, :amount, :remarks, :flag_debit, :flag_debit, :username, :institution_id)");
    $save->bindParam(':cd_code', $cd_code);
		$save->bindParam(':amount', $amt);
		$save->bindParam(':remarks', $rm);
		$save->bindParam(':flag_debit', $flag_debit);
		$save->bindParam(':username', $username);
		$save->bindParam(':institution_id', $institution_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    echo'
    <div class="col-lg-12 col-xs-8">
    	<div class="alert alert-success alert-dismissible">
    		<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Operation Successfully Completed.
    	</div>
    </div>';
    die();
  } catch(PDOException $e) {
    $dbh->rollBack();
    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo'
    <div class="col-lg-12 col-xs-8">
    	<div class="alert alert-danger alert-dismissible">
    		<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> There was an error while operation.
    	</div>
    </div>';
    die();
  }
} 
elseif (isset($_POST['edit_fin'])) { 
	$flag=$_POST['flag'];
	$amt=$_POST['amt'];
	$rm=$_POST['rm'];
	$id=$_POST['financeId'];

	if($flag == 1){ $amt=$amt; }elseif($flag == 0){ $amt=-$amt; }elseif( $flag == 2){ $amt=-$amt; }
	
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE bbo_finance SET amount=:amt, remarks=:rm WHERE finance_id=:id");
		$save->bindParam(':amt',$amt);
		$save->bindParam(':rm',$rm);
		$save->bindParam(':id',$id);
		$save->execute();

    $dbh->commit();
    $dbh = null;

		echo'<div class="col-lg-12 col-xs-8"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Record Updated Successfully.</div></div>';
		die();
  }catch(PDOException $e){
    $dbh->rollBack();
    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"> </i> There was an error while operation.</div></div>';
    die();
  }
} 
elseif (isset($_POST['Dep'])) {
	$cd_code = $_POST['cdcode'];
	$hol = $_POST['hol'];
	$rm = $_POST['rm'];
	$sy = $_POST['sy'];
	$flag_debit = 1;

	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		$save = $dbh->prepare("INSERT into bbo_vault (cd_code, bbo_holding, remarks, user_name, institution_id, symbol) 
			VALUES (:cd_code, :hol, :remarks, :usr_name, :inst_id, :sym)");
		$save->bindParam(":cd_code", $cd_code);
		$save->bindParam(":hol", $hol);
		$save->bindParam(":remarks", $rm);
		$save->bindParam(":usr_name", $username);
		$save->bindParam(":inst_id", $institution_id);
		$save->bindParam(":sym", $sy);
		$save->execute();

		$dbh->commit();
    $dbh = null;

    header('location: ../FILES/bbo-landing.php?ms=1'); 
    die();
	}catch(PDOException $e){
		$dbh->rollBack();
		error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
		header('location: ../FILES/bbo-landing.php?ms=2');
		die();
	}
} 
elseif (isset($_POST['delete_val'])) { 
	$vault_id=$_POST['delete_val'];
	try{
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("DELETE FROM bbo_vault WHERE vault_id=:id");
		$save->bindParam('id', $vault_id);
		$save->execute();

    $dbh->commit();
    $dbh = null;

    header('location: ../FILES/bbo-landing.php?ms=5');
    die();
  }catch(PDOException $e){
    $dbh->rollBack();
    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    header('location: ../FILES/bbo-landing.php?ms=2');
    die();
  }
} 
elseif (isset($_POST['edit_val'])) { 
	$hol=$_POST['hol'];
	$rm=$_POST['rm'];
	$id=$_POST['edit_val'];

	try{
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE bbo_vault SET bbo_holding=:hol, remarks=:rm WHERE vault_id=:id");
		$save->bindParam(':hol',$hol);
		$save->bindParam(':rm',$rm);
		$save->bindParam(':id',$id);
		$save->execute();

    $dbh->commit();
    $dbh = null;

    header('location: ../FILES/bbo-landing.php?ms=3');
    die();
  }catch(PDOException $e){
    $dbh->rollBack();
    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    header('location: ../FILES/bbo-landing.php?ms=2');
    die();
  }
}
elseif (isset($_POST['save_commission'])) {
	$commission_name = $_POST['commission_name'];
	$rate = $_POST['rate'];
	try{
    $dbh->beginTransaction();

    $save = $dbh->prepare("INSERT INTO bbo_commission (commission_name, rate, institution_id) VALUES(:com_name, :rate, :inst_id)");
    $save->bindParam(":com_name", $commission_name);
    $save->bindParam(":rate", $rate);
    $save->bindParam(":inst_id", $institution_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    echo'
    <div class="col-lg-12 col-xs-12">
    	<div class="alert alert-success alert-dismissible">
    		<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Commission Saved Successfully.
    	</div>
    </div>';
		die();
  }catch(PDOException $e){
    $dbh->rollBack();
    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo'
    <div class="col-lg-12 col-xs-12">
    	<div class="alert alert-danger alert-dismissible">
    		<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> There was an error while operation. Please contact RSEB support.
    	</div>
    </div>';
		die();
  }
} 
elseif (isset($_POST['side_for_order'])) {
		if($_POST['vol'] == 0 || $_POST['vol'] == '' || $_POST['price'] == 0 || $_POST['price'] == '') {
			echo'
			<div class="col-lg-12 col-xs-12">
				<div class="alert alert-warning alert-dismissible">
					<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Please Fill All Mandatory Fields.
				</div>
			</div>';
			die();
		}

		//variable declaration 
		$cdcode = $_POST['cdcode'];
		$cdcode = strtoupper($cdcode);
		$p_code = $_POST['p_code'];
		$u_name = $_POST['u_name'];
		$vol = $_POST['vol'];
		$avl_vol = $_POST['avl_vol'];
		$pov = $_POST['pov'];
		$piv = $_POST['piv'];
		$sy_id = $_POST['sy_id'];
		$price = $_POST['price'];
		$side = $_POST['side_for_order'];
		$b_commis = $_POST['b_commis'];

		$n_pov = (int)$pov + (int)$vol;
		$n_piv = (int)$piv + (int)$vol;
		$new_vol_cds = (int)$avl_vol - (int)$vol;
		
		//$price = substr($price, 0, 5);
		$price = number_format((float)$price, 2, '.', '');
		
		/*$commis_amt = (int)$vol * (int)$price * (int)$b_commis / 100;
		$amt = ((int)$vol * (int)$price) + (int)$commis_amt;*/

		// Ensure volume is integer (whole number)
		$vol = (int)$vol;

		// Calculate total value
		$total_value = $vol * (float)$price;

		// Calculate commission amount
		$commis_amt = round($total_value * (float)$b_commis / 100, 2);

		// Calculate GST (5% of commission)
		$gst = round($commis_amt * 0.05, 2);

		// Final total amount
		// $amt = $total_value + $commis_amt + $gst;

		$find_existing_order = check_orders($cdcode, $sy_id, $side, $p_code);
		$flag_id = date("ymdhis");
		$financestatus = 0;

		// checks whethere GST registered or not
		$stmt = $dbh->prepare("SELECT p.gst_register
						FROM client_account a 
						LEFT JOIN adm_institution p ON a.institution_id = p.institution_id
						WHERE a.cd_code = ?
		");
		$stmt->execute([$cdcode]);
		$gst_register = $stmt->fetchColumn();

		// for seller minus commission and gst (if any) and vice versa for buyer
		$sign = ($side === 'S') ? -1 : 1;
		$gstAmt = ($gst_register === 'Y') ? $gst : 0;

		$amt = round($total_value + $sign * ($commis_amt + $gstAmt), 2);

		// check if the market is close
		$check = date("h:i:s");
		foreach ($trading_hours as $hour) {
	    if ($check > $hour['start'] && $check < $hour['end']) {
	      die('
	      	<div class="col-lg-12 col-md-12">
						<div class="alert alert-warning alert-dismissible">
							<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Market Closed. Please try again later.
						</div>
					</div>
				');
	    }
	  }

	  // to check price 
    $cap_name = 'CAP';
    $market_price = market_price($sy_id); 
    $cap = circuit($cap_name);
    $cap_value = cap_compute($market_price,$cap);
    $ceiling_price = round($market_price + $cap_value, 2);
    $floor_price = round($market_price - $cap_value, 2);

    if ($price > $ceiling_price || $price < $floor_price) {
      die('<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Price should be between <b>'.number_format($floor_price, 2).'</b> and <b>'.number_format($ceiling_price, 2).'</b></div></div>');
    }

    $flag = 0; 
    $t = '';
		switch ($side) {
	    case 'B':
	        $flag = 3;
	        $t = 'Buy';
	        break;
	    case 'S':
	        $flag = 2;
	        $t = 'Sell';
	        break;
	    default:
	        // do nothing
	        break;
		}

		$remarks = $t.' Order entry by user '.$u_name.' of member '.$p_code.' of volume '.$vol.' @ Nu. '.$price.'/share';
		// check if order already placed
		if ($find_existing_order == 1) {
			echo'
			<div class="col-lg-12 col-xs-12">
				<div class="alert alert-warning alert-dismissible">
					<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> order for this client with the same symbol has already been placed.
				</div>
			</div>'; 
			die();
		} else {
			// elseif($find_existing_order==0)
			if ($side !== 'B' && $side !== 'S') {
	        echo'
					<div class="col-lg-12 col-xs-12">
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Order Side Not Matched
						</div>
					</div>';
	        exit;
	    }

	    // check if amount available
		  if ($side == 'B') {
		  	$stmt = $dbh->prepare("SELECT SUM(m.amount) AS total_amount FROM bbo_finance m WHERE m.cd_code = ? AND m.status = 1");
				$stmt->execute([$cdcode]);
				$avail__holding__amount = $stmt->fetchColumn();
				if ($amt > $avail__holding__amount) {
					$avail__holding__amount = isset($avail__holding__amount) ? $avail__holding__amount : 0;
					die('
		      	<div class="col-lg-12 col-md-12">
							<div class="alert alert-warning alert-dismissible">
								<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Insufficient cash. You only have Nu. <b>'.number_format($avail__holding__amount, 2).'</b> available.
							</div>
						</div>
					');
				}
		  }

		  // check if real volume available
		  if ($side == 'S') {
		  	$stmt = $dbh->prepare("SELECT h.volume
						FROM cds_holding h 
						WHERE h.cd_code = ?
							AND h.symbol_id = ?
				");
				$stmt->execute([$cdcode, $sy_id]);
				$avail__holding__vol = $stmt->fetchColumn();
				if ($vol > $avail__holding__vol) {
					die('
		      	<div class="col-lg-12 col-md-12">
							<div class="alert alert-warning alert-dismissible">
								<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Insufficient shares available
							</div>
						</div>
					');
				}
		  }

			try {
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    $dbh->beginTransaction();

		    // enter into order auit
		    $order_audit = order_audit($cdcode, $p_code, $u_name, $vol, $vol, $sy_id, $price, $side, $commis_amt, $flag_id, $u_name);

		    // Prepare the statements
				$b_order = $dbh->prepare("INSERT INTO orders(cd_code, participant_code, order_entry, order_size, symbol_id, price,side, commis_amt, flag_id, member_broker, sell_vol, buy_vol) 
					VALUES (:cd_code, :participant_code, :order_entry, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker, IF(:side='S', :vol, 0), IF(:side='B', :vol, 0))");
				$b_order->bindParam(':cd_code', $cdcode);
				$b_order->bindParam(':participant_code', $p_code);
				$b_order->bindParam(':order_entry', $u_name);
				$b_order->bindParam(':order_size', $vol);
				$b_order->bindParam(':symbol_id', $sy_id);
				$b_order->bindParam(':price', $price);
				$b_order->bindParam(':side', $side);
				$b_order->bindParam(':commis_amt', $commis_amt);
				$b_order->bindParam(':flag_id', $flag_id);
				$b_order->bindParam(':member_broker', $u_name);
				$b_order->bindParam(':vol', $vol);
				$b_order->execute();

				$b_fin = $dbh->prepare("INSERT INTO bbo_finance (cd_code, user_name, remarks, flag, institution_id, flag_id, status, amount) VALUES(:cd_code, :user_name, :remarks, :flag, :institution_id, :flag_id, :financestatus, IF(:side='S', :amount, -:amount))");
				$b_fin->bindParam(':cd_code', $cdcode);
				$b_fin->bindParam(':user_name', $u_name);
				$b_fin->bindParam(':remarks', $remarks);
				$b_fin->bindParam(':flag', $flag);
				$b_fin->bindParam(':institution_id', $institution_id);
				$b_fin->bindParam(':flag_id', $flag_id);
				$b_fin->bindParam(':financestatus', $financestatus);
				$b_fin->bindParam(':side', $side);
				$b_fin->bindParam(':amount', $amt);
				$b_fin->execute();

				if ($side == 'S') {
					$cds_acc = $dbh->prepare("UPDATE cds_holding SET volume=:new_vol, pending_out_vol=:pov WHERE cd_code=:cdcode AND symbol_id=:sy_id");
					$cds_acc->bindParam(':new_vol', $new_vol_cds);
					$cds_acc->bindParam(':pov', $n_pov);
					$cds_acc->bindParam(':cdcode', $cdcode);
					$cds_acc->bindParam(':sy_id', $sy_id);
					$cds_acc->execute();
				}

				$dbh->commit();
		    $dbh = null;
		    echo'
				<div class="col-lg-12 col-xs-12">
					<div class="alert alert-success alert-dismissible">
						<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> '.$t.' Order Placed Successfully.
					</div>
				</div>';
		  } catch(PDOException $e) {
		    $dbh->rollBack();
		    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
		    echo'
				<div class="col-lg-12 col-xs-12">
					<div class="alert alert-danger alert-dismissible">
						<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> An error occurred. Please contact RSEB support.
					</div>
				</div>';
		  }
		}
		die();
} 
elseif(!empty($_POST["cancle_id"])) {
	$id = $_POST["cancle_id"];	// order id
	$fid = $_POST["fid"];
	$v = $_POST["v"];
	$side = $_POST["side"];
	$cd_code = $_POST["cd_code"];
	$sy_id = $_POST["sy_id"];

	// check if the market is close
	header('Content-Type: application/json');
	$check = date("h:i:s");
	$message = '';
	$status = 0;
	$data = [];
	foreach ($trading_hours as $hour) {
    if ($check > $hour['start'] && $check < $hour['end']) {
			$data = [
			    "status" => 2,
			    "message" => '
			    				<div class="col-lg-12 col-md-12">
										<div class="alert alert-warning alert-dismissible">
											<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Market Closed.
										</div>
									</div>'
			];
			echo json_encode($data);
      die();
    }
  }
	
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();

		// Update the cds_holding table
		if ($side == 'S') {
			// get existing order from db
	    $stmt = $dbh->prepare("SELECT order_size FROM orders WHERE order_id = ?");
	    $stmt->execute([$id]);
	    $v = $stmt->fetchColumn();

    	// get vol and pov to check negative error
			$get_val = $dbh->prepare("SELECT pending_out_vol, volume FROM cds_holding WHERE symbol_id = ? AND cd_code = ?");
      $get_val->bindParam(1, $sy_id);
      $get_val->bindParam(2, $cd_code);
      $get_val->execute();
      $row = $get_val->fetch();

      $old_pov = $row['pending_out_vol'];
      $old_volume = $row['volume'];

      $upd_pending_out_vol = $old_pov - $v;
      $upd_volume = $old_volume + $v;
      
      if ($upd_pending_out_vol < 0) {
				$data = [
				    "status" => 2,
				    "message" => '
									<div class="col-lg-12 col-md-12">
										<div class="alert alert-danger alert-dismissible">
											<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Negative Error. Please contact RSEB.
										</div>
									</div>'
				];
				echo json_encode($data);
        die();
      }

      $cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = ?, volume = ? WHERE cd_code = ? AND symbol_id = ?");
      $cds_acc->bindParam(1, $upd_pending_out_vol);
      $cds_acc->bindParam(2, $upd_volume);
      $cds_acc->bindParam(3, $cd_code);
      $cds_acc->bindParam(4, $sy_id);
      $cds_acc->execute();

      // commented due to negative issue
	 		// $cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol=pending_out_vol-:v,volume=volume+:v WHERE cd_code=:cdcode and symbol_id=:sy_id");
			// $cds_acc->bindParam(':v', $v);
			// $cds_acc->bindParam(':cdcode', $cd_code);
			// $cds_acc->bindParam(':sy_id', $sy_id);
			// $cds_acc->execute();
			// $cds_acc->execute(array(':v' => $v, ':cdcode' => $cd_code, ':sy_id' => $sy_id));
	 	}

	 	// Get the maximum order date
		$order_date1 = $dbh->prepare("SELECT max(order_date) AS od FROM orders_audit WHERE flag_id = :fid");
    $order_date1->bindParam(':fid', $fid);
    $order_date1->execute();
    $of = $order_date1->fetch();
    $o_date = $of['od'];

    // Update the order_audit table
    $order_cancle_status = $dbh->prepare("UPDATE orders_audit SET flag = 'C', username = :un WHERE flag_id = :fid AND order_date = :od");
    $order_cancle_status->bindParam(':un', $username);
    $order_cancle_status->bindParam(':fid', $fid);
    $order_cancle_status->bindParam(':od', $o_date);
    $order_cancle_status->execute();

    // Delete the order from the orders table
    $order_cancle = $dbh->prepare("DELETE FROM orders WHERE order_id = :id");
    $order_cancle->bindParam(':id', $id);
    $order_cancle->execute();

    // Delete from bbo_finance table
		$bbo_fin_del = $dbh->prepare("DELETE FROM bbo_finance WHERE flag_id = :fid");
		$bbo_fin_del->bindParam(':fid', $fid);
		$bbo_fin_del->execute();

		// Commit the transaction
		$dbh->commit();
		// close the database connection
		$dbh = null;

		// display the success message
		$message = '<div class="col-lg-12 col-xs-12">
									<div class="alert alert-success alert-dismissible">
										<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Order Deleted Successfully
									</div>
								</div>';
		$status = 1;
	} catch(PDOException $e) {
		$dbh->rollBack();
		error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
		$message = '<div class="col-lg-12 col-xs-12">
									<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> An error occurred. Please contact RSEB support.
									</div>
								</div>';
		$status = 2;
	}

	$data = [
	    "status" => $status,
	    "message" => $message
	];
	echo json_encode($data);
	die();
} 
elseif(!empty($_POST["change_id"])) {
		$id = $_POST["change_id"]; // order id
		$fid = $_POST["fid"];
		$ex_vol = $_POST["v"]; // previous order vol
		$e_v = $_POST["e_v"];  // new order vol
		
		$e_p = $_POST["e_p"];
		$e_p = round($e_p, 2);

		$side = $_POST["side"];
		$cd_code = $_POST["cd_code"];
		$sy_id = $_POST["sy_id"];

		$cap_name = 'CAP';
		$market_price = market_price($sy_id); 
		$cap = circuit($cap_name);
		$cap_value = cap_compute($market_price, $cap);
		
		$up = round($market_price + $cap_value, 2);
		$dw = round($market_price - $cap_value, 2);

		$b_commis = client_commission($cd_code, $username);
		// $tot=cash_total($cd_code, $username);
		$tot = cash_total_client($cd_code, 'bbo', $username);
		$list = pending_vol($cd_code, $sy_id);
		$pov = $list[0];
		$piv = $list[1];
		$vol = $list[2];

		// get previous order vol from db
		// replace $ex_vol which is pulling from the hidden input
		$stmt = $dbh->prepare("SELECT order_size FROM orders WHERE order_id = ?");
		$stmt->execute([$id]);
		$ex_vol = $stmt->fetchColumn();

		$data = [];
		header('Content-Type: application/json');
		// to check volume is 0 or not
		if ($e_v < 0 || $e_v == '') {
				$data = [
					"status" => "failure",
					"message" => '<div class="col-lg-12 col-xs-12">
													<div class="alert alert-warning alert-dismissible">
														<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"> </i> Volume can not be less than 1
													</div>
												</div>'
				];
				echo json_encode($data);
				die();
		}

		// check if the market is close
		$trade_time = date("h:i:s");
	  foreach ($trading_hours as $hour) {
	    if ($trade_time > $hour['start'] && $trade_time < $hour['end']) {
	    	$data = [
					"status" => "failure",
					"message" => '<div class="col-lg-12 col-md-12">
													<div class="alert alert-warning alert-dismissible">
														<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Market Closed. Please try again later.
													</div>
												</div>'
				];
				echo json_encode($data);
	      die();
	    }
	  }

		// checking price and exit if price is out of price range
		if($e_p > $up || $e_p < $dw) {
			$data = [
				"status" => "failure",
				"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
												<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
													<i class="icon fa fa-warning"> </i> Price should be between <b>'.$dw.'</b> and <b>'.$up.'</b>.
												</div>
											</div>'
			];
			echo json_encode($data);
      die();
		}

		// Check if order exists
    $order_size_count = $dbh->prepare("SELECT COUNT(*) FROM orders WHERE order_id = :ord_id");
    $order_size_count->execute([':ord_id' => $id]);
    $check_order_exists_or_not = $order_size_count->fetchColumn();

    if (!$check_order_exists_or_not) {
        echo json_encode([
            "status" => "failure",
            "message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
														<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
															<i class="icon fa fa-warning"></i> No order to be found.</b>.
														</div>
													</div>'
        ]);
        exit;
    }

		// set the remarks for change order
		$new_amt = 0; $remarks = '';
		switch ($side) {
	    case 'B':
	        $term = 'Buy';
	        break;
	    case 'S':
	        $term = 'Sell';
	        break;
	    default:
	        // do nothing
	        break;
		}
		$remarks = $term.' Order entry by user '.$username.' of member '.$particpant_code.', of volume '.$e_v.' @ Nu. '.$e_p.'/share';

		try {
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    	$dbh->beginTransaction();

	    	// checks whethere GST registered or not
			$stmt = $dbh->prepare("SELECT p.gst_register
							FROM client_account a 
							LEFT JOIN adm_institution p ON a.institution_id = p.institution_id
							WHERE a.cd_code = ?
			");
			$stmt->execute([$cd_code]);
			$gst_register = $stmt->fetchColumn();

			$new_amt = 0;
	    	if ($side == 'S') {
				$avl_vol_change = $vol + $ex_vol;

				if ($avl_vol_change >= $e_v) {
					$new_vol = $avl_vol_change - $e_v;
					$new_pov = ($pov - $ex_vol) + $e_v;

					$new_commis_amt = round($e_v * $e_p * $b_commis * 0.01, 2);
					$new_gst = round($new_commis_amt * 0.05, 2);
					// $new_amt = round(($e_v * $e_p) + $new_commis_amt, 2);

					$gstAmt = ($gst_register === 'Y') ? $new_gst : 0;
					$new_amt = round(($e_v * $e_p) - ($new_commis_amt + $gstAmt), 2);

					// update cds_holding table for sell order
					$cds_acc = $dbh->prepare("UPDATE cds_holding SET pending_out_vol = :new_pov, volume = :new_vol WHERE cd_code = :cdcode AND symbol_id = :sy_id");
					$cds_acc->execute([':new_pov' => $new_pov, ':new_vol' => $new_vol, ':cdcode' => $cd_code, ':sy_id' => $sy_id]);

				} else {
					$data = [
						"status" => "failure",
						"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
														<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
															<i class="icon fa fa-warning"> </i> Insufficient shares available
														</div>
													</div>'
					];
					echo json_encode($data);
					die();
				}
			}
			elseif ($side == 'B') {
				$e_amt = prev_amt_ord($fid);
				$avl_amt = $tot + $e_amt;

				$new_commis_amt = round($e_v * $e_p * $b_commis * 0.01, 2);
				$new_gst = round($new_commis_amt * 0.05, 2);
				// $new_amt = round(($e_v * $e_p) + $new_commis_amt + $new_gst, 2);
				
				$gstAmt = ($gst_register === 'Y') ? $new_gst : 0;
				$new_amt = round(($e_v * $e_p) + ($new_commis_amt + $new_gst), 2);

				$ex_comission = round($ex_vol * $e_p * $b_commis * 0.01, 2);
				$ex_gst = round($ex_comission * 0.05, 2);
				// $ex_amount = round(($ex_vol * $e_p) + $ex_comission + $ex_gst, 2);
				
				// add gst if GST registered.
				$gstAmt = ($gst_register === 'Y') ? $ex_gst : 0;
				$ex_amount = round(($ex_vol * $e_p) + $ex_comission + $gstAmt, 2);

				$ex_total_amount = $ex_amount + $tot;

			  if ($ex_total_amount >= $new_amt) {
					$new_amt = $new_amt * -1;
				} else {
					$data = [
						"status" => "failure",
						"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
														<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
															<i class="icon fa fa-warning"> </i> Insufficient cash available
														</div>
													</div>'
					];
					echo json_encode($data);
					die();
				}
			}

			// update bbo finance
			$bbo_fin_up = $dbh->prepare("UPDATE bbo_finance SET remarks = :remrks, amount = :new_amt WHERE flag_id = :fid");
			$bbo_fin_up->execute([':remrks' => $remarks, ':new_amt' => $new_amt, ':fid' => $fid,]);

			// get flag id
			$check = $dbh->prepare("SELECT flag_id FROM orders WHERE order_id = :id");
			$check->execute([':id' => $id]);
			$res = $check->fetch();
			$flag_id = $res['flag_id'];

			// insert into order audit
			$order_audit = order_audit($cd_code, $particpant_code, $username, $e_v, $e_v, $sy_id, $e_p, $side, $new_commis_amt, $flag_id, $username);

			// update order 
			$ord_up = $dbh->prepare("
						UPDATE orders 
			            SET sell_vol = CASE WHEN side = 'S' THEN :new_sell_vol ELSE sell_vol END,
			                buy_vol = CASE WHEN side = 'B' THEN :new_buy_vol ELSE buy_vol END,
			                order_size = CASE WHEN side = 'S' THEN :new_sell_vol ELSE :new_buy_vol END,
			                price = :new_price,
			                commis_amt = :new_commis_amt
			            WHERE order_id = :id
	      	");
			$ord_up->execute([':new_sell_vol' => $e_v, ':new_buy_vol' => $e_v, ':new_price' => $e_p, ':new_commis_amt' => $new_commis_amt, ':id' => $id]);

	    $dbh->commit();
	    $dbh = null;

			$data = [
				"status" => "success",
				"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
												<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
													<i class="icon fa fa-check"></i> Order updated successfully
												</div>
											</div>'
			];
	  } catch(PDOException $e) {
	    $dbh->rollBack();
	    error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
	    $data = [
				"status" => "success",
				"message" => '<div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
												<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
													<i class="icon fa fa-times"></i> An error occurred. Please contact RSEB support.
												</div>
											</div>'
			];
	  }
		echo json_encode($data);
		die();
}
elseif(isset($_POST['brokerVerify'])) { 
	// Online Terminal Application verification by Broker
	$sysDateTime = date("Y-m-d H:i:s"); 
	$username = $_POST['username'];
	$bvCode = $_POST['brokerVerify'];
	$onlineUsrId = $_POST['onlineUsrId'];
	$email = $_POST['email'];
	$cdCode = $_POST['cdCode'];
	
	try {
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    // insert into audit table
    $sql = $dbh->prepare("INSERT INTO api_online_terminal_audit(user_online_id, cid, cd_code, name, participant_code, phone, email, address, declaration, broker_user, status, app_fee, fee_status, order_no, created_date) 
    	SELECT a.user_online_id, a.cid, a.cd_code, a.name, a.participant_code, a.phone,a.email, a.address, a.declaration, a.broker_user, a.status, a.app_fee, a.fee_status, a.order_no, a.created_date 
    	FROM api_online_terminal a 
    	WHERE a.user_online_id=:id
    ");
		$sql->bindParam(':id', $onlineUsrId);
		$sql->execute();

		// update api online table
		$updateApiTable = $dbh->prepare("UPDATE api_online_terminal a 
			SET a.email=:email 
			WHERE a.user_online_id=:usrId AND a.cd_code=:cdCode
		");
		$updateApiTable->bindParam(':email', $email);
		$updateApiTable->bindParam(':usrId', $onlineUsrId);
		$updateApiTable->bindParam(':cdCode', $cdCode);
		$updateApiTable->execute();

		// update latest email if corrected
		$updateClientAccTable = $dbh->prepare("UPDATE client_account a SET a.email=:email WHERE a.cd_code=:cdCode");
		$updateClientAccTable->bindParam(':email', $email);
		$updateClientAccTable->bindParam(':cdCode', $cdCode);
		$updateClientAccTable->execute();
		
		// update status in api online table
		$query = $dbh->prepare("UPDATE api_online_terminal a 
			SET a.status = :status, a.broker_user = :usn, a.created_date = :sysDaTi 
			WHERE a.user_online_id=:id
		");
		$query->bindParam(':status', $bvCode);
		$query->bindParam(':usn', $username);
		$query->bindParam(':sysDaTi', $sysDateTime);
		$query->bindParam(':id', $onlineUsrId);
		$query->execute();

		$dbh->commit();
    $dbh = null;

    include('emailLink.php');

		header('location: ../FILES/userList.php?ms=1');

  } catch(PDOException $e) {
    $dbh->rollBack();
	  error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    header('location: ../FILES/userList.php?ms=3');
  }
  die();
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

    // Define the bank code to bank ID mapping
		$bankMapping = array(
		    'BOBL' => 2,
		    'BOB' => 2,
		    'BNBL' => 1,
		    'BNB' => 1,
		    'BDBL' => 3,
		    'BDB' => 3,
		    'DPNB' => 4,
		    'PNB' => 4,
		    'TBANK' => 5
		);

		// Get the bank ID from the bank code
		$bank_id = isset($bankMapping[$bank]) ? $bankMapping[$bank] : null;

    try {
        $dbh_site->beginTransaction();
        $dbh_site->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dbh->beginTransaction();
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($nrb_verification == 'APPROVED') {
            $checkExist = $dbh->prepare("SELECT a.cd_code, a.institution_id FROM client_account a WHERE a.cd_code LIKE 'NRB%' AND a.ID = :cid");
            $checkExist->bindParam(':cid', $cid);
            $checkExist->execute();
            
            if ($checkExist->rowCount() < 1) {
                //insert into audit table non_resident_bhutanese_audits
                $sql = $dbh_site->prepare("INSERT INTO non_resident_bhutanese_audits(nrb_id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, nrb_created_at, nrb_updated_at) 
                	SELECT id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, created_at, updated_at FROM non_resident_bhutaneses WHERE id = :app_id");
                $sql->bindParam(':app_id', $app_id);
                $sql->execute();

                // $getLastCdCode = $dbh->prepare("SELECT a.cd_code FROM client_account a WHERE a.institution_id=:insti_id ORDER BY a.client_id DESC LIMIT 1");
                $getLastCdCode = $dbh->prepare("SELECT MAX(cd_code) AS max_cd_code FROM client_account WHERE institution_id=:insti_id AND cd_code LIKE ('NRB%')");
                $getLastCdCode->bindParam(':insti_id', $institute_id);
                $getLastCdCode->execute();
                if ($getLastCdCode->rowCount() < 1) {
                    $cd_code = 'NRB0000001';
                } else {
                    $last_cdCode = $getLastCdCode->fetch();
                    $last_number = intval(substr($last_cdCode['max_cd_code'], 3));
                    $new_number = $last_number + 1;
                    $new_number_with_zeros = str_pad($new_number, 7, '0', STR_PAD_LEFT);
                    $new_codeCode = 'NRB'.$new_number_with_zeros;
                    $cd_code = $new_codeCode;
                }

                //update app status as APPROVED
                $update = $dbh_site->prepare("UPDATE non_resident_bhutaneses SET app_status = 'APPROVED', user_name = :usr, updated_at = :uptDate WHERE id = :app_id");
                $update->bindParam(':app_id', $app_id);
                $update->bindParam(':usr', $user_name);
                $update->bindParam(':uptDate', $sysDateTime);
                $update->execute();

                /*Insert record into client account at cms2*/
                $insert = $dbh->prepare("INSERT INTO client_account (acc_type, cd_code, f_name, ID, nationality, phone, email, bank_id, bank_account, bro_comm_id, address, institution_id, title, bank_account_type, passport, dob, oversea_phone_no, permanent_address, user_name) 
										VALUES ('I', :nr_cd_code, :full_name, :cid_no, 'Bhutanese', :loc_phone_no, :mail, :bank, :bank_acc_no, :com_id, :foriegn_add, :inst_id, :title, :account_type, :passport, :dob, :oversea_phone_no, :permanent_address, :usr_name)");
								$insert->bindParam(':nr_cd_code', $cd_code);
								$insert->bindParam(':full_name', $name);
								$insert->bindParam(':cid_no', $cid);
								$insert->bindParam(':loc_phone_no', $local_phone_no);
								$insert->bindParam(':mail', $email);
								$insert->bindParam(':bank', $bank_id);
								$insert->bindParam(':bank_acc_no', $account_no);
								$insert->bindParam(':com_id', $comm_id);
								$insert->bindParam(':foriegn_add', $oversea_address);
								$insert->bindParam(':inst_id', $institute_id);
								$insert->bindParam(':title', $title);
								$insert->bindParam(':account_type', $account_type);
								$insert->bindParam(':passport', $passport);
								$insert->bindParam(':dob', $dob);
								$insert->bindParam(':oversea_phone_no', $oversea_phone_no);
								$insert->bindParam(':permanent_address', $permanent_address);
								$insert->bindParam(':usr_name', $user_name);
								$insert->execute();

                $getPartCode = $dbh->prepare("SELECT p.participant_code FROM adm_participants p WHERE p.institution_id=:ins_id");
                $getPartCode->bindParam(':ins_id', $institute_id);
                $getPartCode->execute();
                $partCode = $getPartCode->fetch();
                $memParticipateCode = $partCode['participant_code'];

                $clientUserName = $memParticipateCode.$cid;
                // $usrPwd = md5($clientUserName);
                $usrPwd = password_hash($clientUserName, PASSWORD_BCRYPT);
            		$isBcrypt = 1;

                $roleId = 4; 
                $status = 1; 
                $log_check = 1;

                // Insert into Link User
                $instLinkUsr = $dbh->prepare("INSERT INTO linkuser (participant_code, client_code, username, broker_user_name) VALUES(:memParticipateCode, :cd_code, :clientUserName, :user_name)");
								$instLinkUsr->bindParam(':memParticipateCode', $memParticipateCode);
								$instLinkUsr->bindParam(':cd_code', $cd_code);
								$instLinkUsr->bindParam(':clientUserName', $clientUserName);
								$instLinkUsr->bindParam(':user_name', $user_name);
                $instLinkUsr->execute();

                $isNRB = 'Y';
                $insertUser = $dbh->prepare("INSERT INTO users(name, username, password, role_id, participant_code, phone, email, status, log_check, cd_code, address, cid, isNRB, is_bcrypt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
								$insertUser->bindParam(1, $name);
								$insertUser->bindParam(2, $clientUserName);
								$insertUser->bindParam(3, $usrPwd);
								$insertUser->bindParam(4, $roleId);
								$insertUser->bindParam(5, $memParticipateCode);
								$insertUser->bindParam(6, $oversea_phone_no);
								$insertUser->bindParam(7, $email);
								$insertUser->bindParam(8, $status);
								$insertUser->bindParam(9, $log_check);
								$insertUser->bindParam(10, $cd_code);
								$insertUser->bindParam(11, $oversea_address);
								$insertUser->bindParam(12, $cid);
								$insertUser->bindParam(13, $isNRB);
								$insertUser->bindParam(14, $isBcrypt);
                $insertUser->execute();

                $dbh_site->commit();
                $dbh->commit();

                include('emailLink_NRB.php');

                $dbh_site = null;
                $dbh = null;

                header('location: ../FILES/nrb_app_list.php?ms=1');
                die();
            } else {
                $dbh_site = null;
                $dbh = null;

                header('location: ../FILES/nrb_app_list.php?ms=4');
                die();
            }
        } else {
            $remarks = $_POST['remarks'];

            //insert into audit table non_resident_bhutanese_audits
            $sql = $dbh_site->prepare("INSERT INTO non_resident_bhutanese_audits(nrb_id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, nrb_created_at, nrb_updated_at) 
            	SELECT id, cid, title, name, passport, dob, local_phone_no, email, permanent_address, oversea_address, oversea_phone_no, bank, account_no, account_type, cd_code, flag, app_status, created_at, updated_at FROM non_resident_bhutaneses WHERE id=:app_id");
            $sql->bindParam(':app_id', $app_id);
            $sql->execute();

            //update app status as REJECTED
            $update = $dbh_site->prepare("UPDATE non_resident_bhutaneses SET app_status='REJECTED', remarks = :remrk, user_name = :usr, updated_at = :uptDate WHERE id = :app_id");
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
    } catch(PDOException $e) {
        $dbh_site->rollBack();
        $dbh->rollBack();
        error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        header('location: ../FILES/nrb_app_list.php?ms=3');
        die();
    }
}
elseif (isset($_POST['process_nrb_wallet'])) {
		$id = $_POST['wallet_id'];
		$cd_code = $_POST['cd_code'];
		$Amount = $_POST['amount'];
		$p_t_u = 'PAID';

		if($_POST['status'] == 'SELL') {
				$p_t_u = 'SOLD';
				$Amount = $Amount * -1;
				// Prepare the statement
				$b_order = $dbh->prepare("INSERT INTO mcams_wallet (cd_code, amount, type, trx_time, paid_to_user) VALUES (:cd_code, :amount, 'DR', CURRENT_TIMESTAMP, 'PAID')");
				$b_order->bindParam(':cd_code', $cd_code);
				$b_order->bindParam(':amount', $Amount);
				$result = $b_order->execute();
				if($result) {
						$save = $dbh->prepare("INSERT INTO bbo_finance (cd_code, amount, remarks, flag, flag_id, user_name, institution_id) 
						        VALUES (:cd_code, :amount, 'Sale Proceeds', 0, 0, :user_id, '230822044455')");
						$save->bindParam('cd_code', $cd_code);
						$save->bindParam(':amount', $Amount);
						$save->bindParam(':user_id', $username);
						$save->execute();
				}
		}

		// for status = PROCESSING no need to enter in bbo_finance as it will be done when user do withdraw request
		$stmt = $dbh->prepare("UPDATE mcams_wallet w SET w.paid_to_user = :p_t_u WHERE w.wallet_id = :id");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':p_t_u', $p_t_u);
		$result = $stmt->execute();
		echo '1';
		die();
}
elseif (isset($_POST['nrb_tr_content'])) {
		$id = $_POST['nrb_tr_content'];

		$stmt = $dbh->prepare("SELECT wallet_id, cid, cd_code, name, amount, bank_acc_number, type, paid_to_user, created_Date, trx_time FROM mcams_wallet w WHERE w.wallet_id = :id");
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		echo'
		<td>'.$result['wallet_id'].'</td>
	    <td>'.$result['cid'].'</td>
	    <td>'.$result['cd_code'].'</td>
	    <td>'.$result['name'].'</td>
	    <td>'.$result['amount'].'</td>
	    <td>'.$result['bank_acc_number'].'</td>
	    <td>'.$result['type'].'</td>
	    <td>'.$result['paid_to_user'].'</td>
	    <td>'.$result['created_Date'].'</td>
	    <td>'.$result['trx_time'].'</td>
	    <td></td>
		';

		echo json_encode($result);
		die();
}
elseif (isset($_POST['reverse__nrb__payment__process'])) {
		$id = $_POST['wallet_id'];
		$cd_code = $_POST['cd_code'];
		$amount = $_POST['amount'];
		$status = $_POST['status'];
		$data = [];

		if ($id) {
			try {
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbh->beginTransaction();

				// Delete record from bbo_finance
			    $stmt = $dbh->prepare("DELETE FROM bbo_finance WHERE cd_code = ? AND flag_id = ? AND amount = ?");
			    $stmt->execute([$cd_code, $id, $amount]);

			    if ($stmt->rowCount() === 0) {
			        throw new Exception("Failed to delete record from bbo_finance (possible missing flag_id)");
			    }

			    // Delete record from mcams_wallet
			    $stmt_del = $dbh->prepare("DELETE FROM mcams_wallet WHERE wallet_id = ? AND cd_code = ?");
			    $stmt_del->execute([$id, $cd_code]);

			    if ($stmt_del->rowCount() === 0) {
			        throw new Exception("Failed to delete record from mcams_wallet");
			    }

			    // Commit the transaction if both deletions were successful
			    $dbh->commit();
			    $data = [
			        "status" => 1,
			        "message" => "Reversed Payment Successfully",
			    ];
			} catch (Exception $e) {
				$dbh->rollBack();
				error_log("Error ==> " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
				$data = [
				    "status" => 2,
				    "message" => 'An error occurred. Please contact RSEB support.',
				];
			}

		} else {
			$data = [
			    "status" => 2,
			    "message" => 'Empty Wallet Id',
			];
		}

		header('Content-Type: application/json');
		echo json_encode($data);
		die();
}
elseif (isset($_POST['account_unlock'])) {
        $usr_nam = $_POST['usr_name'];
				$data = [];

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
elseif (isset($_POST['get__applicant__dtls'])) {
		$cid_no = $_POST['cid_no'];
		$response = [];
		$tbody = '';
		$status = 0;
		header('Content-Type: application/json');

		$get = $dbh->prepare("SELECT CONCAT_WS(' ', a.f_name, a.l_name) AS name, a.ID , a.phone, a.email, a.title FROM client_account a WHERE a.ID = ? LIMIT 1");
    $get->execute([$cid_no]);
    $row = $get->fetch(PDO::FETCH_ASSOC);
    	
    if ($row) {
	    	$check = $dbh->prepare("SELECT id, nominee_name, nominee_cid, nominee_relation, applicant_cid, security_type, ownership_percentage FROM client_account_nominees WHERE applicant_cid = ?");
		    $check->execute([$cid_no]);
		    $rows = $check->fetchAll(PDO::FETCH_ASSOC);

		    if ($rows) {
		    	$i = 1;
		    	foreach ($rows as $value) {
		    		$tbody .= '
		    		<tr>
			          <td>'.$i.'</td>
			          <td>
			            <input type="text" name="nom_name[]" id="nom_name[]" class="form-control" value="' . $value['nominee_name'] . '">
			          </td>
			          <td>
			            <input type="number" name="nom_cid[]" id="nom_cid[]" onKeyPress="if(this.value.length==11) return false;"  class="form-control" value="' . $value['nominee_cid'] . '">
			          </td>
			          <td>
			            <select name="nom_relation[]" id="nom_relation[]" class="form-control">
			              <option value="">--Select Relation--</option>';
			                  $stmt = $dbh->prepare("SELECT id, name FROM relationship_masters WHERE STATUS = 1");
			                  $stmt->execute();
			                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			                  foreach ($rows as $key => $val) {
			                  	$selected = ($val['name'] === $value['nominee_relation']) ? 'selected' : '';
		                      $tbody .= '<option value="' . $val['name'] . '" ' . $selected . '>' . $val['name'] . '</option>';
			                  }
			            $tbody .= '
			            </select>
			          </td>
			          <td>
			            <select name="nom_sec_type[]" id="nom_sec_type[]" class="form-control">
			              <option value="">--Select Type--</option>
		                <option value="SECURITIES" ' . ($value['security_type'] == 'SECURITIES' ? 'selected' : '') . '>SECURITIES</option>
		                <option value="BOND" ' . ($value['security_type'] == 'BOND' ? 'selected' : '') . '>BOND</option>
		                <option value="BOTH" ' . ($value['security_type'] == 'BOTH' ? 'selected' : '') . '>BOTH</option>
			            </select>
			          </td>
			          <td>
			            <input type="number" name="nom_percent[]" onKeyPress="if(this.value.length==5) return false;"  id="nom_percent[]" class="form-control" value="' . $value['ownership_percentage'] . '">
			          </td>
		        </tr>';
		        $i++;
		    	}
		    	$tbody .= '<tr>
                      <td colspan="6" class="text-left">
                        <button type="button" class="btn btn-info btn-sm" onclick="add_nominee()"><i class="fa fa-plus"></i> Add</button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="remove_nominee()"><i class="fa fa-minus"></i> Remove</button>
                      </td>
                    </tr>';
		    	$status = 300;
		    } else {
		    	$tbody .= '
		    		<tr>
			          <td>1</td>
			          <td>
			            <input type="text" name="nom_name[]" id="nom_name[]" class="form-control">
			          </td>
			          <td>
			            <input type="number" name="nom_cid[]" id="nom_cid[]" onKeyPress="if(this.value.length==11) return false;"  class="form-control">
			          </td>
			          <td>
			            <select name="nom_relation[]" id="nom_relation[]" class="form-control">
			              <option value="">--Select Relation--</option>';
			                  $stmt = $dbh->prepare("SELECT id, name FROM relationship_masters WHERE STATUS = 1");
			                  $stmt->execute();
			                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			                  foreach ($rows as $key => $val) {
		                      $tbody .= '<option value="' . $val['name'] . '">' . $val['name'] . '</option>';
			                  }
			            $tbody .= '
			            </select>
			          </td>
			          <td>
			            <select name="nom_sec_type[]" id="nom_sec_type[]" class="form-control">
			              <option value="">--Select Type--</option>
		                <option value="SECURITIES">SECURITIES</option>
		                <option value="BOND">BOND</option>
		                <option value="BOTH">BOTH</option>
			            </select>
			          </td>
			          <td>
			            <input type="number" name="nom_percent[]" onKeyPress="if(this.value.length==5) return false;"  id="nom_percent[]" class="form-control">
			          </td>
		        </tr>
		        <tr>
              <td colspan="6" class="text-left">
                <button type="button" class="btn btn-info btn-sm" onclick="add_nominee()"><i class="fa fa-plus"></i> Add</button>
                <button type="button" class="btn btn-warning btn-sm" onclick="remove_nominee()"><i class="fa fa-minus"></i> Remove</button>
              </td>
            </tr>';
		    	$status = 200;
		    }

		    $response = [
		    		'status'  => $status,
		    		'message' => '<label>Details</label> 
	      									<input type="text" class="form-control" value="'.$row['title'].' '.$row['name'].', '.$row['phone'].', '.$row['email'].' " readonly>',
		    		'tbody'   => $tbody,
	    	];
    } else {
    	$response = [
    		'status' => 400,
    		'message' => '<label>Details</label> 
      								<input type="text" class="form-control" value="No Details. Please open CD account." style="color: red;" readonly>',
      	'tbody' => '',
    	];
    }

    echo json_encode($response);
    exit;
}
elseif (isset($_POST['save__nominee_dtls'])) {
    $nominees = json_decode($_POST['nominees'], true);
    $applicant_cid = trim($_POST['appli_cid_no']);
    $sysDateTime = date("Y-m-d H:i:s"); 

    $message = '';
    $totalPercent = 0;
    $cidList = [];
    // validation
    foreach ($nominees as $index => $nominee) {
        $rowNum = $index + 1;

        $name = trim($nominee['name']);
        $cid = trim($nominee['cid']);
        $relation = trim($nominee['relation']);
        $secType = trim($nominee['secType']);
        $percent = $nominee['percent'];

        if (!$name || !$cid || !$relation || !$secType || $percent === null) {
            echo'<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> All fields are required for nominee row '.$rowNum.'.</div>';
	    			exit;
        }

        if (!ctype_digit($cid) || strlen($cid) != 11) {
        	echo'<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> CID must be exactly 11 digits in row '.$rowNum.'.</div>';
	    			exit;
        }

        if ($cid === $applicant_cid) {
            echo'<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Nominee CID cannot match applicants CID in row '.$rowNum.'.</div>';
	    			exit;
        }

        if (in_array($cid, $cidList)) {
        	echo'<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Duplicate CID found in row '.$rowNum.'.</div>';
	    			exit;
        } else {
            $cidList[] = $cid;
        }

        if (!is_numeric($percent) || $percent < 0 || $percent > 100) {
        		echo'<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Invalid percentage in row '.$rowNum.'.</div>';
	    			exit;
        } else {
            $totalPercent += floatval($percent);
        }
    }

    if (round($totalPercent, 2) != 100.00) {
	    	echo'<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Total ownership percentage must be exactly 100% in row '.$rowNum.'.</div>';
		    exit;
    }

    // insert nominee
    try {
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    $dbh->beginTransaction();

		    // check if any exist and delete it
		    $check = $dbh->prepare("SELECT 1 FROM client_account_nominees WHERE applicant_cid = ? LIMIT 1");
		    $check->execute([$applicant_cid]);
		    if ($check->fetchColumn()) {
		    	$delete = $dbh->prepare("DELETE FROM client_account_nominees WHERE applicant_cid = ?");
		    	$delete->execute([$applicant_cid]);
		    }
		    // save the new record
		    foreach ($nominees as $nominee) {
		        $name = $nominee['name'];
		        $cid = $nominee['cid'];
		        $relation = $nominee['relation'];
		        $secType = $nominee['secType'];
		        $percent = $nominee['percent'];

		        $stmt = $dbh->prepare("INSERT INTO client_account_nominees (nominee_name, nominee_cid, nominee_relation, security_type, ownership_percentage, applicant_cid, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
		        $stmt->execute([$name, $cid, $relation, $secType, $percent, $applicant_cid, $sysDateTime]);
		    }

		    $dbh->commit();
		    $dbh = null;
		    $message = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Nominee details saved successfully.</div>';
	  } catch(PDOException $e) {
	  		error_log("Exception ==>> ". $e->getMessage());
		    $dbh->rollBack();
		     $message = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-times"></i> Exception occured. Please try again later.</div>';
	  }

    echo $message;
    exit;
} 
else {
	header('location: ../FILES/bbo-landing.php?ms=2');
	die();
}
?>