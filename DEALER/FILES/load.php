<?php
date_default_timezone_set("Asia/Thimphu");
include ('session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');

$check = $dbh->prepare('SELECT a.institution_id,c.participant_code  from adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un');
$check->bindParam(':un', $username);
$check->execute();
$res = $check->fetch();
$institution_id = $res['institution_id'];
$participant_code = $res['participant_code'];

if(!empty($_POST["val"])) {
  $type = $_POST['val'];

  if ($type == 'I') {
    echo'
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>CD Code<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="cdcode" id="cdcode" maxlength="10" onChange="getState3(this.value);" style="text-transform:uppercase" required>
    </div> 
    <div id="cd1"></div>';
  } elseif($type == 'J') {
    echo'
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>CD Code<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="cdCodeAC" id="cdCodeAC" style="text-transform:uppercase" maxlength="10" onChange="getState4(this.value);" required>
    </div> 
    <div id="cd1"></div>';
  }
} 
elseif(!empty($_POST["cdCode"])) { 
  $cd = $_POST['cdCode'];

  $statement = $dbh->prepare("SELECT cd_code from client_account where cd_code=:cd");
  $statement->bindParam(':cd', $cd);
  $statement->execute();
  $state = $statement->fetch();
  if ($statement->rowCount() > 0) {
    echo'
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Message</label>
      <input style="color:red; type="text" class="form-control" value="CD Code already exists" required>
    </div>';
  } else {
  echo'
  <input type="hidden" class="form-control" name="licenseNo" id="licenseNo" value="0">
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>CID<span style="color:red;">*</span></label>
    <input type="number" class="form-control" name="id" id="id" onKeyPress="if(this.value.length==11) return false;" required>
    <span id="errCid" style="color: red;"></span>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Title<span style="color:red;">*</span></label>
    <input type="text" class="form-control" name="title" id="title" required>
  </div> 
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>First Name<span style="color:red;">*</span></label>
    <input type="text" class="form-control" name="fn" id="fn" required>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Last Name</label>
    <input type="text" class="form-control" name="ln" id="ln">
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Occupation<span style="color:red;">*</span></label>
    <select id="occupation" name="occupation" class="form-control" required>
      <option value="">-- Select --</option>';
      $q = $dbh->prepare('SELECT * FROM occupation ORDER BY occupation_name ASC');
      $q->execute();
      $occupation = $q->fetchAll(PDO::FETCH_ASSOC);
      foreach($occupation as $state) {
        echo'<option value="'.$state['occupation'].'">'.$state['occupation_name'].'</option>';
      }
    echo'
    </select>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Nationality<span style="color:red;">*</span></label>
    <input type="text" class="form-control" name="nat" id="nat" required>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Dzongkhag<span style="color:red;">*</span></label>';
    $q=$dbh->prepare('SELECT * FROM tbldzongkhag ORDER BY DzongkhagName ASC');
    $q->execute();
    $dzokhgs = $q->fetchAll(PDO::FETCH_ASSOC);
    echo'
    <select id="dz" name="dz" class="form-control" required>
      <option value="">-- Select --</option>';
      foreach($dzokhgs as $state) {
        echo'<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
      }
    echo'</select>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>TPN<span style="color:red;"></span></label>
    <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn" >
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Phone No<span style="color:red;">*</span></label>
    <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length === 8) return false;" required>
    <span id="errln" style="color:red;display:none;">*Phone Number should be only 8 characters</span>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Email</label>
    <input type="text" class="form-control" name="email" id="email">
    <span id="errEmail" style="color:red;display:none;">*Please use only letters and numbers</span>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Bank Name<span style="color:red;">*</span></label>';
    $stmt = $dbh->prepare("SELECT bank_id, bank_name FROM banks");
    $stmt->execute();
    $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo'<select id="bank" name="bank" class="form-control" required>';
    foreach($banks as $state) {
      echo'<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
    }
    echo'</select>
    <span id="bankError" style="color: red;"></span>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Account Number<span style="color:red;">*</span></label>
    <input type="number" class="form-control" name="accno" id="accno" onKeyPress="if(this.value.length==14) return false;" required>
    <span id="errAcno" style="color:red;display:none;">*Account Number should be only 10 characters</span>
    <span id="errAcno1" style="color:red;display:none;">*Account Number should be only integers</span>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Account Type<font style="color:red;">*</font></label>
    <select id="bankAccType" name="bankAccType" class="form-control" required>
      <option value="">--Select Account Type--</option>
      <option value="Saving Account">Saving Account</option>
      <option value="Current Account">Current Account</option>
    </select>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12">
    <label>Commission<font style="color:red;">*</font></label>';
    $q = $dbh->prepare("SELECT bro_comm_id, commission_name FROM bbo_commission WHERE institution_id=:iid ORDER BY bro_comm_id ASC");
    $q->bindParam(':iid', $institution_id);
    $q->execute();
    $comms = $q->fetchAll(PDO::FETCH_ASSOC);
    echo'
    <select id="commis" name="commis" class="form-control" step="any" min="1" max="100">
      <option value="">--Select Commission--</option>';
      foreach($comms as $state) {
        echo'<option value="'.$state['bro_comm_id'].'">'.$state['commission_name'].'</option>';
      }
    echo'
    </select>
  </div>
  <div class="col-lg-12">
    <label>Address<font style="color:red;">*</font></label>
    <input type="text" class="form-control" name="add" id="add" required>
  </div>
  <script type="text/javascript">
    $(function () {
      $("#occupation").select2();
      $("#dz").select2();
    });

    $("#save_client").show();

    $("#bank").click(function(){
      $("#bankError").html("");
    });
    
    $("#id").click(function(){
      $("#errCid").html("");
    });
  </script>';
  } 
} 
elseif(!empty($_POST["cdCodeAC"])) {
  $cd = $_POST['cdCodeAC'];

  $wc= $dbh->prepare("SELECT cd_code FROM client_account WHERE cd_code=:cd");
  $wc->bindParam(':cd',$cd);
  $wc->execute();
  $state = $wc->fetch();
  if($wc->rowCount() > 0) {
    echo'
    <div class="col-sm-3">
      <label>Message</label>
      <input style="color:red; type="text" class="form-control" value="CD Code already exists" required>
    </div>';
  } else {
    echo'
    <input type="hidden" class="form-control" name="nat" id="nat" value="">
    <input type="hidden" class="form-control" name="title" id="title" value="">
    <input type="hidden" class="form-control" name="occupation" id="occupation" value="101"> 
    <div class="col-sm-3">
      <label>Asso. Name<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="fn" id="fn" required>
    </div>
    <div class="col-sm-3">
      <label>Registration/License No<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="licenseNo" id="licenseNo" required>
    </div>
    <div class="col-sm-3">
      <label>DISN<span style="color:red;">*</span> <span style="font-size: 11px;">[Ask From RSEB Office]</span></label>
      <input type="text" maxlength="11" class="form-control" name="id" id="id" onKeyPress="if(this.value.length==11) return false;" required>
    </div>
    <div class="col-sm-3">
      <label>Dzongkhag<span style="color:red;">*</span></label>';
      $q = $dbh->prepare("SELECT * FROM tbldzongkhag ORDER BY DzongkhagName ASC");
      $q->execute();
      $dzongkhags = $q->fetchAll(PDO::FETCH_ASSOC);
      echo'
      <select id="dz" name="dz" class="form-control" required>
        <option value="">--Select Dzongkhag--</option>';
        foreach ($dzongkhags as $state) {
          echo'<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
        }
      echo'
      </select>
    </div>
    <div class="col-sm-3">
      <label>TPN<span style="color:red;">*</span></label>
      <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn" required>
    </div>
    <div class="col-sm-3">
      <label>Contact Person<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="ln" id="ln" required>
    </div>
    <div class="col-sm-3">
      <label>Phone No<span style="color:red;">*</span></label>
      <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length === 8) return false;" required>
      <span id="errln" style="color:red;display:none;">*Phone Number should be only 8 characters</span>
    </div>
    <div class="col-sm-3">
      <label>Email</label>
      <input type="text" class="form-control" name="email" id="email">
    </div>
    <div class="col-sm-3">
      <label>Bank Name<span style="color:red;">*</span></label>';
      $q=$dbh->prepare("SELECT * FROM banks");
      $q->execute();
      $bnks = $q->fetchAll(PDO::FETCH_ASSOC);
      echo'<select id="bank" name="bank" class="form-control" required>';
      echo'<option value="">--Select Bank--</option>';
      foreach ($bnks as $state) {
        echo'<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
      }
    echo'</select>
    </div>
    <div class="col-sm-3">
      <label>Account Number<span style="color:red;">*</span></label>
      <input type="number" class="form-control" name="accno" id="accno" onKeyPress="if(this.value.length==14) return false;" required>
    </div>
    <div class="col-sm-3">
      <label>Account Type<span style="color:red;">*</span></label>
      <select id="bankAccType" name="bankAccType" class="form-control" required>
        <option value="">--Select Account Type--</option>
        <option value="Saving Account">Saving Account</option>
        <option value="Current Account">Current Account</option>
      </select>
    </div>
    <div class="col-sm-3">
      <label>Commission<span style="color:red;">*</span></label>';
      $q = $dbh->prepare("SELECT * FROM bbo_commission ORDER BY bro_comm_id ASC");
      $q->execute();
      $commis = $q->fetchAll(PDO::FETCH_ASSOC);
      echo'
      <select id="commis" name="commis" class="form-control" step="any" min="1" max="100" required>
        <option value="">--Select Commission--</option>';
        foreach ($commis as $state) {
          echo '<option value="'.$state['bro_comm_id'].'">'.$state['commission_name'].'</option>';
        }
      echo'
      </select>
    </div>
    <div class="col-sm-12">
      <label>Address<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="add" id="add" required> 
    </div>
    <div class="col-sm-12">
      <br>NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory<br>
    </div>
    <script type="text/javascript">
      $(function(){
        $("#dz").select2();
      });
      $("#save_client").show();
    </script>';
  }
} 
elseif(!empty($_POST["fin"])) {
  $cd = $_POST['fin'];

  $wc = $dbh->prepare("SELECT f_name, l_name, ID FROM client_account WHERE cd_code = :cd AND institution_id = :insid");
  $wc->bindParam(':cd', $cd);
  $wc->bindParam(':insid', $institution_id);
  $wc->execute();
  $state = $wc->fetch();
  if($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-6 col-sm-12">
      <label>Client Details</label>
      <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
    </div>  
    <div class="col-lg-12">
     <label>Remarks<font color="red">*</font></label>
      <input type="text" class="form-control" name="rm" id="rm" >
    </div>';
  } else {
    echo'
    <div class="col-lg-6">
      <label>Message</label>
      <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
    </div>';
  }
} 
elseif(!empty($_POST["cid"])) {
  $cd = $_POST['cid'];

  $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.acc_code, b.cd_code FROM bbo_account a,client_account b WHERE a.ID = :cd");
  $wc->bindParam(':cd', $cd);
  $wc->execute();
  $state = $wc->fetch();
  echo'
  <div class="col-lg-6">
    <label>Details of Client</label>
    <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].'" readonly>
  </div> 
  <div class="col-lg-3">
   <label>Account Code</label>
    <input type="text" class="form-control" value="'.$state['acc_code'].'" readonly>
  </div> 
  <div class="col-lg-3">
   <label>Depository Code</label>
    <input type="text" class="form-control" value="'.$state['cd_code'].'" id="cd_code" readonly>
  </div>

  <div class="col-lg-9">
   <label>Holding</label>
   <textarea class="form-control" readonly> </textarea>
  </div>';
} 
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["finance"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  
  echo'
  <div class="box col-lg-12">
    <div class="box-body">
      <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
          <tr>
            <th>#</th>
            <th>CD Code</th>
            <th>Broker Name</th>
            <th>Amount</th>
            <th>Date</th>
          </tr>
          </thead>
          <tbody>';
          $query = $dbh->prepare('SELECT a.finance_id, a.cd_code, a.amount, a.finance_date, u.name 
            FROM bbo_finance a 
            JOIN users u ON a.user_name = u.username 
            WHERE 
              a.user_name=:un 
              AND a.finance_date BETWEEN :fdate AND :tdate');
          $query->bindParam(':un',$_SESSION['sess_username']);
          $query->bindParam(':fdate',$fromDate);
          $query->bindParam(':tdate',$toDate);
          $query->execute();
          $io = 1;
          while($result = $query->fetch(PDO::FETCH_ASSOC)) { 
            echo'
            <tr>
              <td>'.$io.'</td>
              <td>'.$result['cd_code'].'</td>
              <td>'.$result['name'].'</td>';
              if ($result['amount'] < 0) {
                echo'<td>( '.number_format($result['amount'], 2).' )</td>';
              } else {
                echo'<td>'.number_format($result['amount'], 2).'</td>';
              }
              echo'<td> '.$result['finance_date'].'</td>';
              /*echo '<td><button  data-toggle="modal" data-target="#myModal" name="edit_fin" id="edit_fin"  value="'.$result['finance_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button></td>
              <form action="../PROCESS/process.php" method="POST" onsubmit="return fun('.$io.');"><td><button  name="delete_fin" id="delete_fin'.$io.'" value="'.$result['finance_id'].'"><i class="fa fa-trash-o"></i></button>&nbsp;</td></form>';*/
              echo'</tr>';
              $io ++;
            }
          $query->closeCursor();
          echo'
          </tbody>
        </table>
      </div>
    </div>
  </div>';
}
else if(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["accs"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="box">
    <div class="box-body">
      <div class="table-responsive">
        <table id="account_register_list_id" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Id</th>
              <th>CD Code</th>
              <th>Name</th>
              <th>CID</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Bank</th>
              <th>Account No</th>
              <th>Member</th>
            </tr>
          </thead>
          <tbody>';
          // $query = $dbh->prepare('SELECT * FROM client_account WHERE institution_id=:ii and ca_date >= :fdate and ca_date  <= :tdate order by ca_date DESC');

          $query = $dbh->prepare("SELECT a.cd_code, a.ID, a.f_name, a.l_name, a.DzongkhagID, a.gewog_id, a.village_id, a.phone, a.email, b.bank_short_name, a.bank_account, a.bank_account_type, SUBSTRING(a.user_name, 1, 7) AS member_broker 
                FROM client_account a 
                LEFT JOIN banks b ON a.bank_id = b.bank_id
                WHERE a.ca_date BETWEEN ? AND ?
                AND a.institution_id = ?
          ");
          $query->bindParam(1, $fromDate);
          $query->bindParam(2, $toDate);
          $query->bindParam(3, $institution_id);
          $query->execute();
          $i = 1; 
          while($result = $query->fetch(PDO::FETCH_ASSOC)) {
            echo'
            <tr>
              <td> '.$i.' </td>
              <td> '.$result['cd_code'].' </td>
              <td> '.$result['f_name'].' '.$result['l_name'].' </td>
              <td> '.$result['ID'].' </td>
              <td> '.$result['phone'].' </td>
              <td> '.$result['email'].' </td>
              <td> '.$result['bank_short_name'].' </td>
              <td> '.$result['bank_account'].' </td>
              <td> '.$result['member_broker'].' </td>
            </tr>';
            $i++;
          }
          $query->closeCursor();
          echo'
          </tbody>
        </table>
        <div class="float-right">
          <a href="load.php?export_acc_list_excel=export_acc_list_excel&fromDate='.$fromDate.'&toDate='.$toDate.'&institution_id='.$institution_id.'" class="btn btn-success">  => <i class="fa fa-save"></i> Download Excel</a>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $( document ).ready(function() {
      $("#account_register_list_id").DataTable({
           "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]
      });
    });
  </script>';
  exit;
}
elseif(!empty($_GET['export_acc_list_excel'])) {
      $replace   = array('');
      $search    = array("\n");
      $fromDate  = $_GET['fromDate'];
      $toDate    = $_GET['toDate'];
      $institution_id = $_GET['institution_id']; // Ensure this is set

      $wc= $dbh->prepare("SELECT a.cd_code, a.ID, a.title, a.f_name, a.l_name, a.DzongkhagID, a.gewog_id, a.village_id, a.phone, a.email, b.bank_short_name, a.bank_account, a.bank_account_type, SUBSTRING(a.user_name, 1, 7) AS member_broker 
                  FROM client_account a 
                  LEFT JOIN banks b ON a.bank_id = b.bank_id
                  WHERE a.ca_date BETWEEN ? AND ?
                  AND a.institution_id = ?
      ");
      $wc->bindParam(1, $fromDate);
      $wc->bindParam(2, $toDate);
      $wc->bindParam(3, $institution_id);
      $wc->execute(); 

      $columnHeader = "SlNo\tCD Code\tNAME\tCID/DISN\tPhone\tEmail\tBank\tAccount No\tMember"; 
      $setData = '';  
      $i = 1;

      while ($rec = $wc->fetch(PDO::FETCH_ASSOC)) {
          $rowData = $i++ . "\t" .
              str_replace($search, $replace, $rec['cd_code']) . "\t" .
              str_replace($search, $replace, trim($rec['title'] ?? '')." ".trim($rec['f_name'])." ".trim($rec['l_name'] ?? '')) . "\t" .
              str_replace($search, $replace, $rec['ID']) . "\t" .
              str_replace($search, $replace, $rec['phone'] ?? '') . "\t" .
              str_replace($search, $replace, $rec['email'] ?? '') . "\t" .
              str_replace($search, $replace, $rec['bank_short_name'] ?? '') . "\t" .
              str_replace($search, $replace, $rec['bank_account'] ?? '') . "\t" .
              str_replace($search, $replace, $rec['member_broker'] ?? '') . "\n";
          $setData .= $rowData;
      }

      header("Content-type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=Account_Registration_list.xls");
      header("Pragma: no-cache");
      header("Expires: 0");

      echo chr(255) . chr(254) . mb_convert_encoding($columnHeader . "\n" . $setData, 'UTF-16LE', 'UTF-8');
      exit;

}
elseif(!empty($_POST["rights_fin"])) {
  $cd = $_POST['rights_fin'];

  $wc = $dbh->prepare("SELECT f_name, l_name, ID FROM client_account WHERE cd_code=:cd");
  $wc->bindParam(':cd',$cd);
  $wc->execute();
  $state = $wc->fetch();
  if($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-6 col-md-6">
      <label>Details of Client</label>
      <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
    </div>  
    <div class="col-lg-12 col-md-12">
     <label>Remarks<font color="red">*</font></label>
      <input type="text" class="form-control" name="rm" id="rm" >
    </div>';
  } else {
    echo'
    <div class="col-lg-6 col-md-6">
      <label>Message</label>
      <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
    </div>';
  }
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["financerights"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
      <tr>
        <th>Id</th>
        <th>Account</th>
        <th>Broker Name</th>
        <th>Amount</th>
        <th>Date</th>
      </tr>
      </thead>
      <tbody>';
      // if($username == 'MEMBNBL001' || $username == 'MEMRICB001' || $username == 'MEMBOBL001' || $username == 'MEMBDBL001' || $username == 'MEMDSBP001') {
      if(preg_match('/^MEM(BNBL|RICB|BOBL|BDBL|DSBP|LDSB|BPCL|RINS|SERS)/', $username)) {
        $user_code = substr($username, 0, 7);
        $query = $dbh->prepare("SELECT a.finance_id, a.cd_code, a.amount, a.finance_date, u.name 
          FROM rights_finance a 
          JOIN users u ON a.user_name = u.username
          WHERE substr(a.user_name, 1, 7)=:un AND a.finance_date BETWEEN :fdate and :tdate");
        $query->bindParam(':un', $user_code);
      } else {
        $query= $dbh->prepare('SELECT a.finance_id, a.cd_code, a.amount, a.finance_date, u.name, u.name 
          FROM rights_finance a 
          JOIN users u ON a.user_name = u.username WHERE a.user_name = u.username AND a.user_name=:un AND a.finance_date BETWEEN :fdate and :tdate');
        $query->bindParam(':un', $_SESSION['sess_username']);
      }
      $query->bindParam(':fdate',$fromDate);
      $query->bindParam(':tdate',$toDate);
      $query->execute();
      $io = 1;
      while($result = $query->fetch(PDO::FETCH_ASSOC)) {
        echo
        '<tr>
          <td>'.$io.'</td>
          <td>'.$result['cd_code'].'</td>
          <td>'.$result['name'].'</td>';
          if($result['amount'] < 0) {
            echo'<td>( '.$result['amount'].' )</td>';
          } else {
            echo'<td> '.$result['amount'].'</td>';
          }
          echo'<td> '.$result['finance_date'].'</td>
        </tr>';
        $io++;
      }
      $query->closeCursor();
      echo
      '</tbody>
    </table>
  </div>';
} 
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["ipo_finance"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
      <tr>
        <th>Id</th>
        <th>Account</th>
        <th>Broker Name</th>
        <th>Amount</th>
        <th>Date</th>
      </tr>
      </thead>
      <tbody>';
      $user_code = substr($username, 0, 7);
      $query = $dbh->prepare("SELECT a.finance_id, a.cd_code, a.amount, a.finance_date, u.name 
        FROM ipo_finance a 
        JOIN users u ON a.user_name = u.username
        WHERE substr(a.user_name, 1, 7) = :un AND a.finance_date BETWEEN :fdate and :tdate
      ");
      $query->bindParam(':un', $user_code);
      $query->bindParam(':fdate',$fromDate);
      $query->bindParam(':tdate',$toDate);
      $query->execute();
      $io = 1;
      while($result = $query->fetch(PDO::FETCH_ASSOC)) {
        echo
        '<tr>
          <td>'.$io.'</td>
          <td>'.$result['cd_code'].'</td>
          <td>'.$result['name'].'</td>';
          if($result['amount'] < 0) {
            echo'<td>( '.$result['amount'].' )</td>';
          } else {
            echo'<td> '.$result['amount'].'</td>';
          }
          echo'<td> '.$result['finance_date'].'</td>
        </tr>';
        $io++;
      }
      $query->closeCursor();
      echo
      '</tbody>
    </table>
  </div>';
} 
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["viewrights"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Sl#</th>                    
          <th>Type</th>
          <th>CD Code</th>
          <th>Renounce CD Code</th>
          <th>Order Volume</th>
          <th>Price</th>
          <th>User Name</th>
          <th>Order Time</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>';
      $un = substr($username, 0, 7);
      $query= $dbh->prepare("SELECT * FROM rights_issue WHERE substr(user_name, 1, 7) = :un AND order_date BETWEEN :fdate AND :tdate");
      $query->bindParam(':un', $un);
      $query->bindParam(':fdate', $fromDate);
      $query->bindParam(':tdate', $toDate);
      $query->execute();
      $io = 1;
      foreach($query as $state) {
        // if($state['type']=='S'){$side='SUBSCRIBE';}else if($state['type']=='R'){$side='RENOUNCE';}else{$side='BID';}
        $side = ($state['type'] == 'S') ? 'SUBSCRIBE' : (($state['type'] == 'R') ? 'RENOUNCE' : 'BID');
        echo'
        <tr data-id="'.$io.'">
          <td>'.$io.'</td>
          <td>'.$side.'</td>
          <td>'.$state['cd_code'].'</td>
          <td>'.$state['renounce_cd_code'].'</td>
          <td>'.$state['order_size'].'</td>';
          if($side == 'BID') {
            echo'<td>'.$state['bid_price'].'</td>';
          } else {
            echo'<td>'.$state['face_value'].'</td>';
          }
          echo'
          <td>'.$state['user_name'].'</td>
          <td>'.$state['order_date'].'</td>
          <td>
            <button type="button" class="btn btn-danger" name="delete_rights" id="delete_rights'.$io.'" value="'.$state['order_id'].'"  onclick="return fun('.$io.');" data-toggle="tooltip" data-placement="top" title="Click Here To Delete Order for '.$state['cd_code'].'"><i class="fa fa-trash-o"></i> Delete</button>
          </td>
        </tr>';
        $io=$io+1; 
      }
      $query->closeCursor();
      echo'
      </tbody>
    </table>
  </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["view_ipo_dtls"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Sl#</th>                    
          <th>Type</th>
          <th>CD Code</th>
          <th>Symbol</th>
          <th>Order Volume</th>
          <th>Price</th>
          <th>User Name</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>';
      $un = substr($username, 0, 7);
      $query= $dbh->prepare('SELECT i.type, i.cd_code, i.order_size, i.face_value, i.user_name, i.order_date, i.symbol_id, s.symbol
        FROM ipo i 
        JOIN symbol s ON i.symbol_id = s.symbol_id
        WHERE substr(user_name, 1, 7) = :un 
        AND order_date BETWEEN :fdate AND :tdate
      ');
      $query->bindParam(':un', $un);
      $query->bindParam(':fdate', $fromDate);
      $query->bindParam(':tdate', $toDate);
      $query->execute();
      $io = 1;
      foreach($query as $state) {
        echo'
        <tr>
          <td>'.$io.'</td>
          <td>'.$state['type'].'</td>
          <td>'.$state['cd_code'].'</td>
          <td>'.$state['symbol'].'</td>
          <td>'.$state['order_size'].'</td>
          <td>'.$state['face_value'].'</td>
          <td>'.$state['user_name'].'</td>
          <td>'.$state['order_date'].'</td>
        </tr>';
        $io++; 
      }
      $query->closeCursor();
      echo'
      </tbody>
    </table>
  </div>';
}
/*BOND FINANCE */
else if(!empty($_POST["bond_fin"])) {
    $cd=$_POST['bond_fin'];
    $wc= $dbh->prepare("SELECT f_name,l_name,ID  from client_account where cd_code=:cd");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-6 col-md-6">
      <label>Details of Client</label>
      <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
    </div>  
    <div class="col-lg-12 col-md-12">
      <label>remarks</label>
      <input type="text" class="form-control" name="rm" id="rm">
    </div>';
    } else {
      echo'
      <div class="col-lg-6 col-md-6">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
      </div>';
    }
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["viewbond"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Sl#</th>
          <th>Type</th>
          <th>CD Code</th>
          <th>Order Volume</th>
          <th>Price</th>
          <th>User Name</th>
          <th>Order Time</th>
        </tr>
      </thead>
      <tbody>';
      // if($username == 'MEMBNBL001' || $username == 'MEMRICB001' || $username == 'MEMBOBL001' || $username == 'MEMBDBL001' || $username == 'MEMDSBP001') 
      if (preg_match('/^MEM(BNBL|RICB|BOBL|BDBL|DSBP)/', $username)) {
        $un = substr($username, 0, 7);
        $query = $dbh->prepare('SELECT * FROM bond WHERE substr(user_name,1,7) = :un AND order_date BETWEEN :fdate AND :tdate');
        $query->bindParam(':un',$un);
      } else {
        $query = $dbh->prepare('SELECT * FROM bond WHERE order_date BETWEEN :fdate AND :tdate AND user_name = :un');
        $query->bindParam(':un',$_SESSION['sess_username']);
      }
      $query->bindParam(':fdate',$fromDate);
      $query->bindParam(':tdate',$toDate);
      $query->execute();
      $io = 1;
      foreach($query as $state) {
        // if($state['type']=='BOND'){$side='BOND';}else{$side='BID';}
        $side = ($state['type'] == 'BOND') ? 'BOND' : 'BID';
         echo'
         <tr>
          <td>'.$io.'</td>
          <td>'.$side.'</td>
          <td>'.$state['cd_code'].'</td>                                 
          <td>'.$state['order_size'].'</td>';
          if($side == 'BID') {
          echo'<td>'.$state['bid_price'].'</td>';
          } else {
          echo'<td>'.$state['face_value'].'</td>';
          }
          echo'
          <td>'.$state['user_name'].'</td>
          <td>'.$state['order_date'].'</td>
        </tr>';
        $io++; 
      }
      $query->closeCursor();
      echo'
      </tbody>
    </table>
  </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["financebond"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo'
  <div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Account</th>
          <th>Broker Name</th>
          <th>Amount</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>';
      // if($username == 'MEMBNBL001' || $username == 'MEMRICB001' || $username == 'MEMBOBL001' || $username == 'MEMBDBL001' || $username == 'MEMDSBP001')
      if (preg_match('/^MEM(BNBL|RICB|BOBL|BDBL|DSBP)/', $username)) {
        $user_code = substr($username, 0, 7);
        $query = $dbh->prepare('SELECT a.*, u.name FROM bond_finance a, users u where a.user_name=u.username and substr(a.user_name,1,7)=:un 
          AND a.finance_date BETWEEN :fdate AND :tdate
        ');
        $query->bindParam(':un', $user_code);
      } else {
        $query = $dbh->prepare('SELECT a.*,u.name from bond_finance a, users u where a.user_name=u.username and a.user_name=:un and a.finance_date BETWEEN  :fdate AND :tdate');
        $query->bindParam(':un', $_SESSION['sess_username']);
      }
      $query->bindParam(':fdate', $fromDate);
      $query->bindParam(':tdate', $toDate);
      $query->execute();
      $io = 1;
      while($result = $query->fetch(PDO::FETCH_ASSOC)) {
        echo'
        <tr>
          <td>'.$result['finance_id'].'</td>
          <td>'.$result['cd_code'].'</td>
          <td>'.$result['name'].'</td>';
          if($result['amount'] < 0) {
            echo '<td>( '.$result['amount'].' )</td>';
          } else {
            echo '<td> '.$result['amount'].'</td>';
          }
        echo'
          <td>'.$result['finance_date'].'</td>
        </tr>';
        $io++;
      }
      $query->closeCursor();
      echo'
    </tbody>
  </table>
  </div>';
}
else if(!empty($_POST["BUY"])) {
  echo'
  <div class="modal-dialog modal-lg">
    <form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#d0e4fe;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title text-center">BUY ORDER</h4>
        </div>
        <div class="modal-body">';
          include('../../GifLoader/gifComponent.php');
          echo'
          <div id="orderMessageB"></div>
          <div class="row" ng-app="">
            <div class="col-lg-4 col-md-4 col-sm-12">
              <label for="cid">CD Code<font color="red">*</font></label>
              <input type="text" class="form-control" maxlength="10" style="text-transform:uppercase;" name="cid" id="cid" onChange="tots1(this.value);" required>
              <input type="hidden" name="tp" id="tp" value="B">
              <input type="hidden" class="form-control" name="p_code" id="p_code" value="'.$participant_code.'">
              <input type="hidden" class="form-control" name="u_name" id="u_name" value="'.$username.'">
            </div>
            <div id="cd"></div>
            <div id="cdd"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary submit" name="buysubmit" id="buysubmit"><i class="fa fa-database"></i> Submit</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          <div class="col-sm-8">
            <span id="msg1" style="display:none; color:red;"></span><br/>
            <span id="msg2" style="display:none; color:red;"></span>
            <span id="msg3" style="display:none; color:red;"></span>
          </div>
        </div>
      </div>
    </form>
  </div>';
} 
elseif(!empty($_POST["SELL"])) {
  echo'
  <div class="modal-dialog modal-lg">
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#ffb3b3;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">SELL ORDER</h4>
        </div>
        <div id="message"></div>
        <div class="modal-body" style="color:red;">';
          include('../../GifLoader/gifComponent.php');
          echo'
          <div id="orderMessageS"></div>
          <div class="row" ng-app="">
            <div class="col-lg-4 col-md-4 col-sm-12">
              <label for="cid">CD Code<font color="red">*</font></label>
              <input type="text" class="form-control" maxlength="10" style="text-transform:uppercase;" name="cid" id="cid" onChange="tots1(this.value);" required>
              <input type="hidden"  name="tp" id="tp" value="S">
              <input type="hidden" class="form-control" name="p_code" id="p_code" value="'.$participant_code.'">
              <input type="hidden" class="form-control" name="u_name" id="u_name" value="'.$username.'">
            </div>
            <div id="cd"></div>
            <div id="cdd"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary submit" name="sellsubmit" id="sellsubmit"><i class="fa fa-database"></i> Submit</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          
          <div class="col-lg-8 col-md-8 col-sm-12">
            <span id="msg" style="display:none; color:red;"><p class="text-left">Insuffecient Volume.</p></span><br/>
            <span id="msg1" style="display:none; color:red;"></span>
          </div>

        </div>
      </div>
    </form>
  </div>';
}
elseif(!empty($_POST["cd_load_cli"])) {
    $cd = $_POST['cd_load_cli'];

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, a.phone, a.email, a.title FROM client_account a WHERE a.cd_code =:cd");
    $wc->bindParam(':cd', $cd);
    $wc->execute();
    $res = $wc->fetch();

    $title = isset($res['title']) ? $res['title'] : '';
    $f_name = isset($res['f_name']) ? $res['f_name'] : '';
    $l_name = isset($res['l_name']) ? $res['l_name'] : '';
    $CID = isset($res['ID']) ? $res['ID'] : '';
    $phone = isset($res['phone']) ? $res['phone'] : '';

    echo"</br>Name: <b>".$title." ".$f_name.' '.$l_name."</b>, CID :<b>" .$CID."</b>, Phone-No :<b>" .$phone."</b> </br></br>";

    $i = 1;
    $wc = $dbh->prepare("SELECT s.symbol, a.symbol_id, a.volume, a.pledge_volume, a.block_volume, a.pending_in_vol, a.pending_out_vol, (a.volume + a.pledge_volume + a.block_volume + a.pending_in_vol + a.pending_out_vol) AS total_vol
      FROM cds_holding a
      JOIN symbol s ON a.symbol_id = s.symbol_id 
      WHERE a.cd_code = :cdcode and s.status='1'
      AND (a.volume + a.pledge_volume + a.block_volume + a.pending_in_vol + a.pending_out_vol) > 0
    ");
    $wc->bindParam(':cdcode', $cd);
    $wc->execute();
    $rows = $wc->fetchAll(PDO::FETCH_ASSOC);
    if ($wc->rowCount() > 0) {
      echo'
      <div class="table-responsive">
        <table class="table table-condensed table-sm">
          <thead>
            <tr>
              <th>#</th>
              <th>symbol</th>
              <th>Volume</th>
              <th>pledged</th>
              <th>blocked</th>
              <th>pending-in</th>
              <th>pending-out</th>
              <th>total</th>
            </tr>
          </thead>
          <tbody>';
          foreach($rows as $res) {
            echo'
            <tr>
              <td>'.$i.'</td>
              <td>'.$res['symbol'].'</td>
              <td>'.$res['volume'].'</td>
              <td>'.$res['pledge_volume'].'</td>
              <td>'.$res['block_volume'].'</td>
              <td>'.$res['pending_in_vol'].'</td>
              <td>'.$res['pending_out_vol'].'</td>
              <td>'.$res['total_vol'].'</td>
            </tr>';
            $i++;
          }
          echo'  
          </tbody>
        </table>
      </div>';
    } else {
      echo "This Client has No Shares With This CD CODE.</br></br>";
    }
    $stmt = $dbh->prepare("SELECT a.cd_code, sum(a.amount) as tot
                    FROM bbo_finance a 
                    WHERE a.cd_code=:cd AND a.status = 1
    ");
    $stmt->bindParam(':cd', $cd);
    $stmt->execute();
    $res = $stmt->fetch();
    $avlCash = isset($res['tot']) ? $res['tot'] : 0;
    echo  "<code>Available Cash : </code> Nu. <b>".number_format($avlCash, 2)."</b>/-<br/>";
    die();
}
else if(!empty($_POST["toDate1"]) && !empty($_POST["fromDate1"]) && !empty($_POST["trade_details"])) {
  $toDate = $_POST['toDate1'].' 23:59:00';
  $fromDate = $_POST['fromDate1'].' 00:00:00';
  echo'
  <div class="col-lg-12">
    <div class="box-body table-responsive">';
      echo'Summary of Trade<br> From : '.$fromDate.' - To : '.$toDate;
      echo"<br/><br/><b>MEMBER : ".$participant_code."</b><br>";
      $executed_orders = $dbh->prepare('SELECT distinct a.symbol_id,b.symbol 
        FROM executed_orders a 
        INNER JOIN symbol b ON a.symbol_id = b.symbol_id 
        WHERE a.participant_code=:pc 
        AND a.order_date BETWEEN :fdate AND :tdate');
      $executed_orders->bindParam(':pc', $participant_code);
      $executed_orders->bindParam(':fdate', $fromDate);
      $executed_orders->bindParam(':tdate', $toDate);
      $executed_orders->execute();
      $rows = $executed_orders->fetchAll(PDO::FETCH_ASSOC);
      echo"
      <table class='table table-bordered'>
        <thead>
          <tr style='background-color:#333;color:#fff'>
            <th>SN</th>
            <th>ACCOUNT </th>
            <th>SIDE/DATE</th>
            <th>VOLUME</th>
            <th>PRICE</th>
            <th>AMOUNT</th>
          </tr>
        </thead>
        <tbody>";
        $i = 1; 
        foreach ($rows as $res1) {
          $amt1 = 0;
          $list_ord = $dbh->prepare("SELECT * FROM executed_orders WHERE participant_code=:pc AND symbol_id=:syid AND order_date BETWEEN :fdate AND :tdate");
          $list_ord->bindParam(':pc', $participant_code);
          $list_ord->bindParam(':syid', $res1['symbol_id']);
          $list_ord->bindParam(':fdate', $fromDate);
          $list_ord->bindParam(':tdate', $toDate);
          $list_ord->execute();
          $orders = $list_ord->fetchAll(PDO::FETCH_ASSOC);
          
          foreach ($orders as $res3) {
            $amt = $res3['lot_size_execute'] * $res3['order_exe_price'];
            echo'
            <tr>
              <td>'.$i++.' .'.$res1['symbol'].'</td>
              <td>'.$res3['cd_code'].'</td>
              <td>'.$res3['side'].' - '.$res3['order_date'].'</td>
              <td>'.number_format($res3['lot_size_execute'],2,".",",").'</td>
              <td>'.$res3['order_exe_price'].'</td>
              <td>Nu. '.number_format($amt,2,".",",").'</td>
            </tr>';
            $amt1 += $amt;
          }

          $getAmts = $dbh->prepare("SELECT
                      SUM(CASE WHEN side = 'B' THEN lot_size_execute ELSE 0 END) AS totbuylot,
                      CAST(AVG(CASE WHEN side = 'B' THEN order_exe_price ELSE NULL END) AS DECIMAL(13, 2)) AS avgbuyprice,
                      SUM(CASE WHEN side = 'S' THEN lot_size_execute ELSE 0 END) AS totselllot,
                      CAST(AVG(CASE WHEN side = 'S' THEN order_exe_price ELSE NULL END) AS DECIMAL(13, 2)) AS avgsellprice
                  FROM executed_orders
                  WHERE participant_code = :pc
                      AND symbol_id = :syid
                      AND order_date BETWEEN :fdate AND :tdate");
          $getAmts->bindParam(':pc', $participant_code);
          $getAmts->bindParam(':syid', $res1['symbol_id']);
          $getAmts->bindParam(':fdate', $fromDate);
          $getAmts->bindParam(':tdate', $toDate);
          $getAmts->execute();
          $res = $getAmts->fetch();

          $totbuyamt = $res['avgbuyprice'] * $res['totbuylot'];
          $totsellamt = $res['avgsellprice'] * $res['totselllot'];

          echo'
          <tr style="font-weight: bold;">
            <td>Total :</td>
            <td> Buy Vol : '.number_format($res['totbuylot'],0,".",",").'</td>
            <td> Sell Vol : '.number_format($res['totselllot'],0,".",",").'</td>
            <td></td>
            <td>Total Trade</td>
            <td>Nu. '.number_format($amt1,2,".",",").'</td>
          </tr>';    
          //<td>Nu. '.number_format($totsellamt,2,".",",").'</td></tr>';    
        }
        echo' 
        </tbody>
      </table>
    </div>
  </div>
  <div class="row no-print">
    <div class="col-lg-12">
      &emsp;&emsp;<a href="load.php?toDate1='.$toDate.'&fromDate1='.$fromDate.'&tradeDetails=tradeDetails" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
    </div>
  </div>
  <br>';
} 
else if(!empty($_GET["tradeDetails"])) {
  $toDate = $_GET['toDate1'].' 23:59:00';
  $fromDate = $_GET['fromDate1'].' 00:00:00';
  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");
  echo'
  <html>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Trade Confirmation Report</title>
    </head>
    <body onload="window.print();">
      <div class="wrapper">
        <section class="invoice" style="background:rgb(248, 249, 249);">
          <div class="row">
            <div class="col-lg-12">
              <div class="page-header">
                &emsp;<img src="../../dist/img/Logo.png"> &emsp; 
                <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                 <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Trade Confirmation Report</div> 
                 <div class="lead" style="font-size: 40%;  margin-top:-25px;">From Date :<b>'.$fromDate.'</b>&nbsp;To Date :<b>'.$toDate.'</b>
                 Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div></center>
              </div>
            </div>
          </div>';
          
          echo'
          <div class="row">
            <div class="col-lg-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">MEMBER : '.$res['participant_code'].'</div>
            </div>
          </div>';
          $executed_orders = $dbh->prepare("
              SELECT DISTINCT a.symbol_id, b.symbol 
              FROM executed_orders a, symbol b 
              WHERE a.participant_code=:pc AND a.symbol_id = b.symbol_id AND order_date BETWEEN :fdate AND :tdate
          ");
          $executed_orders->bindParam(':pc', $participant_code);
          $executed_orders->bindParam(':fdate', $fromDate);
          $executed_orders->bindParam(':tdate', $toDate);
          $executed_orders->execute();
          $all_executed_orders = $executed_orders->fetchAll(PDO::FETCH_ASSOC);
          echo"
          <table class='table'>
            <thead>
              <tr style='background-color:#333;color:#fff'>
                <th>SN</th>
                <th>ACCOUNT </th>
                <th>SIDE/DATE</th>
                <th>VOLUME</th>
                <th>PRICE</th>
                <th>AMOUNT</th>
              </tr>
            </thead>
            <tbody>";
            $i = 1;
            foreach($all_executed_orders as $res1) {
              $amt1 = 0;
              $list_ord= $dbh->prepare('SELECT * FROM executed_orders 
                WHERE participant_code=:pc AND symbol_id=:syid AND order_date BETWEEN :fdate AND :tdate');
              $list_ord->bindParam(':pc',$participant_code);
              $list_ord->bindParam(':syid',$res1['symbol_id']);
              $list_ord->bindParam(':fdate',$fromDate);
              $list_ord->bindParam(':tdate',$toDate);
              $list_ord->execute();
              foreach($list_ord as $res3){
                $amt = $res3['lot_size_execute'] * $res3['order_exe_price'];
                echo'
                <tr>
                    <td>'.$i++.' .'.$res1['symbol'].'</td>
                    <td>'.$res3['cd_code'].'</td>
                    <td>'.$res3['side'].' - '.$res3['order_date'].'</td>
                    <td>'.number_format($res3['lot_size_execute'],2,".",",").'</td>
                    <td>'.$res3['order_exe_price'].'</td>
                    <td>Nu. '.number_format($amt,2,".",",").'</td>
                </tr>';
                $amt1 += $amt;
              }
              $list_ord= $dbh->prepare('SELECT sum(lot_size_execute) AS totlot , cast(avg(order_exe_price) AS decimal(13,2)) AS avgp 
                FROM executed_orders 
                WHERE participant_code=:pc and symbol_id=:syid AND side="B" AND order_date >= :fdate  AND order_date <= :tdate
              ');
              $list_ord->bindParam(':pc',$participant_code);
              $list_ord->bindParam(':syid',$res1['symbol_id']);
              $list_ord->bindParam(':fdate',$fromDate);
              $list_ord->bindParam(':tdate',$toDate);
              $list_ord->execute();
              $res2 = $list_ord->fetch();
              $totbuyamt = $res2['avgp'] * $res2['totlot'];

              $list_ord= $dbh->prepare('SELECT sum(lot_size_execute) AS totlots , cast(avg(order_exe_price) AS decimal(13,2)) AS avgps 
                FROM executed_orders WHERE participant_code=:pc AND symbol_id=:syid AND side="S" AND order_date >= :fdate AND order_date <= :tdate
              ');
              $list_ord->bindParam(':pc',$participant_code);
              $list_ord->bindParam(':syid',$res1['symbol_id']);
              $list_ord->bindParam(':fdate',$fromDate);
              $list_ord->bindParam(':tdate',$toDate);
              $list_ord->execute();
              $res4 = $list_ord->fetch(); 
              $totsellamt = $res4['avgps'] * $res4['totlots'];
              echo'
              <tr style="font-weight: bold;">
                <td>Total :</td>
                <td> Buy Vol : '.number_format($res2['totlot'] ?? 0, 0, ".", ",").'</td>
                <td> Sell Vol : '.number_format($res4['totlots'] ?? 0, 0, ".", ",").'</td>
                <td></td>
                <td>Total Trade</td>
                <td>Nu. '.number_format($amt1,2,".",",").'</td>
              </tr>';   
            }
           echo"</tbody>
          </table>
        </section>    
      </div>
    </body>
  </html>";
}
if(!empty($_POST["vol"])) 
{
  $vol=$_POST['vol'];
  echo'
  <div class="col-lg-3">
   <label>Total Volume</label>
    <input type="text" class="form-control" name="volume" id="volume" value="'.$vol.'" readonly>
  </div>';

}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["buylist"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo '
  <div class="col-lg-12">
    <div class="box-body">
      <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
          <tr>
            <th>Sl</th>
            <th>CD CODE</th>
            <th>BUY VOL</th>
            <th>ORDER DATE</th>
          </tr>
          </thead>
          <tbody>';
          $query= $dbh->prepare('SELECT cd_code,order_size,order_date FROM orders_ipo WHERE order_date BETWEEN :fdate AND :tdate');
          $query->bindParam(':fdate', $fromDate);
          $query->bindParam(':tdate', $toDate);
          $query->execute();
          $io=1;
          while($result=$query->fetch(PDO::FETCH_ASSOC)) {
            echo'
            <tr>
              <td> '.$io.'</td>
              <td> '.$result['cd_code'].'</td>
              <td> '.$result['order_size'].'</td>
              <td> '.$result['order_date'].'</td>
            </tr>';
            $io++;
          }
          $query->closeCursor();
          echo'
          </tbody>
        </table>
      </div>
    </div>
  </div>';
} 
elseif(!empty($_POST["rightIsueCD"])) {
  $cd = $_POST['rightIsueCD'];
  
  $cdCod = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID FROM client_account a WHERE a.cd_code=:cd");
  $cdCod->bindParam(':cd', $cd);
  $cdCod->execute();
  $state1 = $cdCod->fetch();
  if ($cdCod->rowCount() < 1) {
    echo'
    <div class="col-lg-6 col-md-6 col-sm-12">
      <label>Client Details</label>
      <input type="text" class="form-control" style="color:red;" value="No Details. Please check CD CODE" readonly>
    </div>';
  } else {
    echo'
    <div class="col-lg-6 col-md-6 col-sm-12">
      <label>Client Details</label>
      <input type="hidden" class="form-control" name="cidNo" id="cidNo" value="'.$state1['ID'].'" readonly>
      <input type="hidden" class="form-control" name="cd_code" id="cd_code" value="'.$cd.'" readonly>
      <input type="text" class="form-control" value="NAME : '.$state1['f_name'].' '.$state1['l_name'].' , CID/DISN# '.$state1['ID'].'" readonly>
    </div>

    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Symbol<font color="red">*</font></label>
      <select class="form-control" name="rights_symbol_id" id="rights_symbol_id" onChange="showAnnType(this.value)" required>
        <option value="">--Select Symbol--</option>';
        $getSymbol = $dbh->prepare("SELECT r.symbol_id, s.symbol
          FROM rights_offers r 
          JOIN symbol s ON r.symbol_id = s.symbol_id
          WHERE r.status = 1 ORDER BY s.symbol ASC");
        $getSymbol->execute();
        $rows = $getSymbol->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $key) {
          echo'<option value="'.$key['symbol_id'].'">'.$key['symbol'].'</option>';
        }
        echo'
      </select>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12" id="rightsAnnTypeId" style="display: none;">
      <label>Announcement Type<font color="red">*</font></label>
      <select class="form-control" name="rights_ann_type" id="rights_ann_type" onChange="checkRightsOffer()" required>
        <option value="">--Select Announcement Type--</option>';
        $getCorp = $dbh->prepare("SELECT r.announcement_type, m.corporate_name  
            FROM rights_offers r 
            JOIN corporate_action_masters m ON r.announcement_type = m.id
            WHERE r.status = 1 ORDER BY m.corporate_name ASC");
        $getCorp->execute();
        $result = $getCorp->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $res) {
          echo'<option value="'.$res['announcement_type'].'">'.$res['corporate_name'].'</option>';
        }
        echo'
      </select>
    </div>
    <div id="details_Id"></div>

    <script type="text/javascript">
      function showAnnType(val) {
        if (val == "") {
          $("#rightsAnnTypeId").hide();
        } else {
          $("#rightsAnnTypeId").show();
        }
      }

      function checkRightsOffer() {
        var symbol_id = $("#rights_symbol_id").val();
        var ann_type = $("#rights_ann_type").val();
        var cid_no = $("#cidNo").val();
        var cd_code = $("#cd_code").val();
        var op = "check_rights_offer";
        var dataString = {
          symbol_id : symbol_id,
          ann_type : ann_type,
          cid_no : cid_no,
          cd_code : cd_code,
          check_rights_offer : op,
        };
        $.ajax({
          type: "POST",
          url: "load.php",
          data: dataString,
          dataType: "html",
          success: function (response) {
            $("#details_Id").html(response);
          }
        });
      }
    </script>';
  }
} 
elseif(!empty($_POST["check_rights_offer"])) {
  $symbol_id = $_POST['symbol_id'];
  $ann_type = $_POST['ann_type'];
  $cid = $_POST['cid_no'];
  $cd = $_POST['cd_code'];

  $check = $dbh->prepare("SELECT r.corp_announcement_id, r.symbol_id, r.announcement_type 
      FROM rights_offers r 
      WHERE r.symbol_id = :sym_id  AND r.announcement_type = :ann_typ AND r.status = 1
      ORDER BY r.id DESC LIMIT 1");
  $check->bindParam(':sym_id', $symbol_id);
  $check->bindParam(':ann_typ', $ann_type);
  $check->execute();
  $res = $check->fetch(PDO::FETCH_ASSOC);
  if ($check->rowCount() > 0) {

    $wc = $dbh->prepare("SELECT s.symbol_id, s.volume, s.ribon_volume, c.rate, a.f_name, a.l_name, a.ID, ai.name, sm.face_value 
      FROM corporate_announcement c
      JOIN spot_date_holding s ON c.corp_announcement_id = s.corp_announcement_id
      JOIN client_account a ON s.client_id = a.client_id
      JOIN symbol sm ON s.symbol_id = sm.symbol_id
      JOIN adm_institution ai ON a.institution_id = ai.institution_id
      WHERE a.cd_code = :cd 
        AND s.ribon_volume > 0 
        AND s.announcement_type = :annType
        AND s.status = 1
        AND s.corp_announcement_id = :corp_ann_id
    ");
    $wc->bindParam(':cd', $cd);
    $wc->bindParam(':annType', $res['announcement_type']);
    $wc->bindParam(':corp_ann_id', $res['corp_announcement_id']);
    $wc->execute();
    $state = $wc->fetch(PDO::FETCH_ASSOC);

    /*$amount = $dbh->prepare("SELECT sum(amount) as amount FROM rights_finance where cd_code = :cd AND user_name = :un and status = 0");
    $amount->bindParam(':cd', $cd);
    $amount->bindParam(':un', $username);
    $amount->execute();
    $amt = $amount->fetch();*/

    $facevalue= $dbh->prepare("SELECT rate FROM assign_broker WHERE username = :username and status = 1 and type = 'RIGHTS' AND symbol = :sym_id");
    $facevalue->bindParam(':username', $username);
    $facevalue->bindParam(':sym_id', $symbol_id);
    $facevalue->execute();
    $fv = $facevalue->fetch();

    // if ($wc->rowCount() > 0 && $amount->rowCount() > 0) {
    if($state) {
      $sum = avlVol($cd);
      $sumAmt = avlAmt($cd, $username);
      $availableVolume = 0;

      if($sum == "") {
        $availableVolume = isset($state['ribon_volume']) ? $state['ribon_volume'] : 0;
        // $availableAmt = $amt['amount'] - $sumAmt;
      } 
      elseif($sumAmt == "") {
        $availableVolume = $state['ribon_volume'] - $sum;
        // $availableAmt = $amt['amount'];
      } 
      else { 
        $availableVolume = $state['ribon_volume'] - $sum;
        // $availableAmt = $amt['amount'] - $sumAmt;
      }

      //this is by default the amount if fully subscribe
      $availableAmt = $fv['rate'] * $availableVolume;

      $maxsublimit = $availableAmt / $fv['rate'];

      echo'
      <div class="col-lg-3 col-md-3 col-sm-12 has-success">
       <label class="control-label" for="options">Options <font color="red">*</font></label>
        <select class="form-control" id="options" name="options" onChange="getState2(this.value);">
          <option value="">--Select--</option>
          <option value="S">Subscribe</option>
          <option value="R">Renounce</option>

          <!-- 
          <option value="B">Bid</option>
          <option value="O">Offer</option>
          <option value="SA">Share Auction</option> -->
        </select>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12 has-success" style="display: none;" name="volAvailable" id="volAvailable">
       <label class="control-label" for="inputSuccess">Rights Share Available</label>
        <input type="text" class="form-control" maxlength="10" name="availableVolume" id="availableVolume" value="'.$availableVolume.'" readonly>

        <input type="hidden" class="form-control" name="rights_issued" id="rights_issued" value="'.$state['ribon_volume'].'">
        <input type="hidden" class="form-control" name="symbol_id" id="symbol_id" value="'.$state['symbol_id'].'">
        <input type="hidden" class="form-control" name="face_value" id="face_value" value="'.$fv['rate'].'">
        <input type="hidden" class="form-control" name="cdCodeee" id="cdCodeee" value="'.$cd.'">
        <input type="hidden" class="form-control" name="cid" id="cid" value="'.$cid.'">
        <input type="hidden" class="form-control" name="announcement_id" id="announcement_id" value="'.$res['corp_announcement_id'].'">
      </div> 
      <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" name="amtAvailable" id="amtAvailable">
        <label for="availableAmount">Total Amount</label>
        <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" name="subscribe" id="subscribe">
        <label for="subscribe1">Subscribe <font color="red">*</font></label>
        <input type="number" class="form-control" name="subscribe1" id="subscribe1" max="'.$maxsublimit.'" min="1">
        <span id="subscribe1ErrMsg" style="color: red;"></span>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" name="renounce" id="renounce">
        <label for="rencdcode">CD Code</label>
        <input type="text" class="form-control" name="rencdcode" id="rencdcode" maxlength="10" onChange="renounceCDcode(this.value);">
      </div>  
      <div id="rencd" style="display: none;"></div>
        <div class="col-lg-3" style="display: none;" name="offerVol" id="offerVol">
        <label for="offerVol1">Offer Volume <font color="red">*</font></label>
        <input type="text" class="form-control" name="offerVol1" id="offerVol1">
      </div>
      
      <!-- <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" name="symbol" id="symbol">
        <label>Symbol <font color="red">*</font></label>';
          $wc = $dbh->prepare("SELECT c.symbol_id, s.symbol FROM corporate_announcement c, symbol s WHERE c.symbol_id=s.symbol_id and c.status = 0 and announcement_type = 1 AND YEAR(c.announcement_date) = YEAR(CURDATE())");
          $wc->execute();
          echo'
          <select name="sy" id="sy" class="form-control" onChange="selectsymbol(this.value);">
          <option value="">-Select symbol-</option>';
          while($res = $wc->fetch()) {
            echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
          }
          echo'</select>
      </div> -->
      
      <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" id="bid">
        <label for="bidPrice">Bid Price (per share) <font color="red">*</font></label>
        <input type="number" class="form-control" name="bidPrice" id="bidPrice" min="11" max="30" step="1" required oninput="calculateTotalBidAmount()">
      </div>

      <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" id="bid2">
       <label>Total Volume (No of Shares) <font color="red">*</font></label>
        <input type="number" class="form-control" name="volume" id="volume" min="100" step="1" required oninput="calculateTotalBidAmount()">
      </div>

      <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" id="bid_amount_id">
        <label for="totalBidAmt">Total Bid Amount</label>
        <input type="number" class="form-control" name="totalBidAmt" id="totalBidAmt" readonly>
      </div>

      <script type="text/javascript">
        function getState2(val) {
          if (val == "S") {
            $("#volAvailable").show();
            $("#subscribe").show();
            $("#amtAvailable").show();
            $("#offerVol").hide(); 
            $("#bid").hide();
            $("#bid1").hide();
            $("#bid2").hide();
            $("#bidVol").hide();
            $("#renounce").hide();
            $("#rencd").hide();
            $("#symbol").hide();
            $("#bid_amount_id").hide();
          } else if(val == "R") {
            $("#volAvailable").show();
            $("#renounce").show();
            $("#amtAvailable").hide();
            $("#offerVol").hide(); 
            $("#subscribe").hide();      
            $("#bid").hide();
            $("#bid1").hide();
            $("#bid2").hide();
            $("#bidVol").hide();
            $("#symbol").hide();
            $("#bid_amount_id").hide();
          } else if(val == "O") {
            $("#volAvailable").show();
            $("#offerVol").show();
            $("#amtAvailable").hide();      
            $("#bid").hide();
            $("#bid1").hide();
            $("#bid2").hide();
            $("#bidVol").hide();      
            $("#subscribe").hide();
            $("#renounce").hide();
            $("#rencd").hide();
            $("#symbol").hide();
            $("#bid_amount_id").hide();
          } else {
            $("#bid1").show();        
            $("#bidVol").show();
            $("#symbol").show(); 
            $("#amtAvailable").hide();
            $("#offerVol").hide();      
            $("#volAvailable").hide();
            $("#subscribe").hide();
            $("#rencd").hide();
            $("#renounce").hide();

            $("#bid").show();
            $("#bid2").show();
            $("#bid_amount_id").show();
          }
          $("#riSave").show();
        }

        function calculateTotalBidAmount() {
            var bidPrice = parseFloat(document.getElementById("bidPrice").value);
            var volume = parseFloat(document.getElementById("volume").value);
            var totalBidAmt = (bidPrice * volume * 0.02) + (bidPrice * volume);

            document.getElementById("totalBidAmt").value = totalBidAmt;
        }
      </script>';
    } else {
      echo'
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client" name="pln" id="pln" readonly>
      </div>
      <script type="text/javascript">
        $("#volAvailable").hide();
        $("#subscribe").hide();
        $("#bid").hide();
        $("#bid1").hide();
        $("#bid2").hide();
        $("#renounce").hide();
        $("#rencd").hide();
        $("#amtAvailable").hide();
      </script>';
    }
  } else {
    echo'
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="No Corporate Action for the selected symbol" readonly>
      </div>';
  }
} 
else if(!empty($_POST["renounceCD"])) {
    $cd = $_POST['CD'];
    $rcd = $_POST['renounceCD'];

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID FROM client_account a WHERE a.cd_code=:cd");
    $wc->bindParam(':cd', $rcd);
    $wc->execute();
    $state = $wc->fetch();

    $amount= $dbh->prepare("SELECT sum(amount) as amount FROM rights_finance WHERE cd_code=:cd AND status = 0");
    $amount->bindParam(':cd', $rcd);
    $amount->execute();
    $amt = $amount->fetch();

    $sumAmt = avilableAmt($cd, $rcd);
    
    if ($sumAmt == "") {
      $availableAmt = $amt['amount'];
    } else {
      $availableAmt = $amt['amount'] - $sumAmt; 
    }
    $maxrevol = $availableAmt / 10;

    if ($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-6">
      <label>Details of Client</label>
      <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
    </div>
    <div class="col-lg-3">
     <label class="control-label" for="renAvailableAmount">Available Amount</label>
      <input type="text" class="form-control" maxlength="10" name="renAvailableAmount" id="renAvailableAmount" value="'.$availableAmt.'" readonly>
    </div>
    <div class="col-lg-3">
     <label class="control-label" for="renounce1">Renounce Volume <font color="red">*</font></label>
      <input type="text" class="form-control" maxlength="10" name="renounce1" id="renounce1" value="" max="'.$maxrevol.'" min="1">
      <span id="renounce1ErrMsg" style="color: red;"></span>
    </div>';
    } else {
      echo'
      <div class="col-lg-6">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client" readonly>
      </div>';
    }
}
elseif(!empty($_POST["ipoCD"])) {
    $cd = $_POST['ipoCD'];
    
    $facevalue = $dbh->prepare("SELECT rate, symbol FROM assign_broker WHERE username = :username AND status = 1 and type = 'IPO'");
    $facevalue->bindParam(':username', $username);
    $facevalue->execute();
    $fv = $facevalue->fetch();

    $cdCod = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, b.name 
      FROM client_account a, adm_institution b WHERE a.cd_code=:cd AND a.institution_id= b.institution_id");
    $cdCod->bindParam(':cd',$cd);
    $cdCod->execute();
    $state1 = $cdCod->fetch();

    $amount = $dbh->prepare("SELECT sum(amount) as amount FROM ipo_finance WHERE cd_code=:cd AND user_name=:un and status=0");
    $amount->bindParam(':cd',$cd);
    $amount->bindParam(':un',$username);
    $amount->execute();
    $amt = $amount->fetch();

    $sumAmt = ipoavlAmt($cd);

    if($sumAmt == "") {
      $availableAmt = $amt['amount'];
    } else { 
      $availableAmt = $amt['amount'] - $sumAmt;
    }
    
    if($cdCod->rowCount() > 0 || $amount->fetch() > 0) {
      echo '      
      <div class="col-lg-6 col-md-6">
        <label>Client Details</label>
        <input type="text"  class="form-control" value="NAME : '.$state1['f_name'].' '.$state1['l_name'].' , CID/DISN# '.$state1['ID'].'" readonly>
      </div>';       
      $wc= $dbh->prepare("SELECT ID FROM client_account c WHERE c.cd_code = :cd");
      $wc->bindParam(':cd', $cd);
      $wc->execute();
      $ress = $wc->fetch();
      $cid = $ress['ID'];
      echo'
      <div class="col-lg-3 col-md-3 has-success" style="display: none;" name="volAvailable" id="volAvailable">
        <input type="hidden" class="form-control" name="symbol_id" id="symbol_id" value="'.$fv['symbol'].'">
        <input type="hidden" class="form-control" name="face_value" id="face_value" value="'.$fv['rate'].'">
        <input type="hidden" class="form-control" name="cdCodeee" id="cdCodeee" value="'.$cd.'">
        <input type="hidden" class="form-control" name="cid" id="cid" value="'.$cid.'">
      </div>';
      if($availableAmt >= $fv['rate']) {
        echo'
        <div class="col-lg-3 col-md-3" name="amtAvailable" id="amtAvailable">
          <label for="exampleInputEmail1">Available Amount</label>
          <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
        </div>
        <div class="col-lg-3 col-md-3" name="symbol" id="symbol">
          <label>Symbol</label>
          <select name="sy" id="sy" class="form-control" onChange="iposelectsymbol(this.value);">
            <option value=""> Select Symbol </option>';
            $wc = $dbh->prepare("SELECT DISTINCT i.symbol_id, s.symbol
                FROM ipo_offers i 
                JOIN symbol s ON i.symbol_id = s.symbol_id 
                WHERE i.status = 1 
            ");
            $wc->execute();
            while($res = $wc->fetch()) {
              echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
          }
          echo'
          </select>
        </div>
        <div class="col-lg-3 col-md-3" style="display: none;" name="bid" id="bid">
          <label for="bidPrice">Bid Price</label>
          <input type="text" class="form-control" value="'.$fv['rate'].'" name="bidPrice" id="bidPrice" readonly>
        </div>
        <div class="col-lg-3 col-md-3" style="display: none;" name="bid2" id="bid2">
         <label for="volume">Total Volume</label>
          <input type="text" class="form-control" name="volume" id="volume" readonly>                  
        </div> ';
      } else {
        if($availableAmt == '') {
          $availableAmt = 0;
        } else {
          $availableAmt = $availableAmt;
        }
        echo'
        <div class="col-lg-3 col-md-3" name="amtAvailable" id="amtAvailable">
          <label for="availableAmount">Available Amount</label>
          <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
        </div>    
        <div class="col-lg-3 col-md-3">
          <label>Message</label>
          <input type="text" class="form-control" style="color:red;" value="Your available amount is '.$availableAmt.'.Please deposit." name= "pln" id="pln" readonly>
        </div>';
      }
    } else {
      echo '    
      <div class="col-lg-4 col-md-4">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client" name= "pln" id="pln" readonly>
      </div>
      <script type="text/javascript">
        $("#ipoSave").hide();
        $("#bid").hide();
        $("#bid2").hide();
        $("#rencd").hide();
        $("#amtAvailable").hide();
      </script>';
    }
}
elseif(!empty($_POST["bondCD"])) {
    $cd = $_POST['bondCD'];
    
    $cdCod = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID FROM client_account a WHERE a.cd_code=:cd");
    $cdCod->bindParam(':cd', $cd);
    $cdCod->execute();
    $state1 = $cdCod->fetch();

    // $amount = $dbh->prepare("SELECT sum(amount) as amount FROM bond_finance WHERE cd_code = :cd AND user_name = :un AND status = 0");
    $amount = $dbh->prepare("SELECT sum(amount) as amount FROM bond_finance WHERE cd_code = :cd AND status = 0");
    $amount->bindParam(':cd', $cd);
    // $amount->bindParam(':un',$username);
    $amount->execute();
    $amt = $amount->fetch();

    $facevalue = $dbh->prepare("SELECT rate, symbol FROM assign_broker WHERE username = :username and status = 1 and type = 'BOND'");
    $facevalue->bindParam(':username', $username);
    $facevalue->execute();
    $fv = $facevalue->fetch();
    
    $sumAmt = bondavlAmt($cd);
    if($sumAmt == "") {
      $availableAmt = $amt['amount'];
    } else { 
      $availableAmt = $amt['amount'] - $sumAmt;
    }

    if($cdCod->rowCount() > 0 || $amount->rowCount() > 0) {
      echo'
        <div class="col-lg-6 col-md-6 col-sm-12">
          <label>Details of Client</label>
          <input type="text"  class="form-control" value="NAME : '.$state1['f_name'].' '.$state1['l_name'].' , CID/DISN# '.$state1['ID'].'" readonly>
        </div>';       
        $wc = $dbh->prepare("SELECT ID FROM client_account c WHERE c.cd_code=:cd");
        $wc->bindParam(':cd',$cd);
        $wc->execute();
        $ress = $wc->fetch();
        $cid = $ress['ID'];
        echo'
        <div class="col-lg-3 col-md-3  col-sm-12 has-success" style="display: none;" name="volAvailable" id="volAvailable">
          <input type="hidden" class="form-control" name="symbol_id" id="symbol_id" value="'.$fv['symbol'].'">
          <input type="hidden" class="form-control" name="face_value" id="face_value" value="'.$fv['rate'].'">
          <input type="hidden" class="form-control" name="cdCodeee" id="cdCodeee" value="'.$cd.'">
          <input type="hidden" class="form-control" name="cid" id="cid" value="'.$cid.'">
        </div>';
        if($availableAmt >= $fv['rate']) {
          echo ' 
          <div class="col-lg-3 col-md-3 col-sm-12" name="amtAvailable" id="amtAvailable">
            <label for="exampleInputEmail1">Available Amount</label>
            <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12" name="symbol" id="symbol">
            <label>Symbol</label>';
            // $wc = $dbh->prepare("SELECT s.symbol, s.symbol_id FROM assign_broker a, symbol s WHERE a.symbol=s.symbol_id AND a.status=1 and a.type='BOND' ORDER BY a.id DESC limit 1");
            $wc = $dbh->prepare("SELECT s.symbol, s.symbol_id 
                    FROM assign_broker a
                    JOIN symbol s on a.symbol=s.symbol_id
                    WHERE a.status=1 and a.type='BOND' and a.username = ?
                    ORDER BY a.id DESC 
                    -- limit 1
            ");
            $wc->execute([$username]);
            echo'
            <select name="sy" id="sy" class="form-control" onChange="bondselectsymbol(this.value);">
              <option value=""> -Select Symbol- </option>';
              while($res = $wc->fetch(PDO::FETCH_ASSOC)) {
                echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
              }
            echo'</select>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12" style="display: none;" name="bid" id="bid">
            <label for="exampleInputEmail1">Bid Price</label>
            <input type="text" class="form-control" value="'.$fv['rate'].'" name="bidPrice" id="bidPrice" readonly>
          </div>
          <div class="col-lg-3 col-md-3" style="display: none;" name="bid2" id="bid2">
           <label>Total Volume</label>
            <input type="text" class="form-control" name="volume" id="volume" readonly>                  
          </div>';
        } else {
          if($availableAmt == '') {
            $availableAmt = 0;
          } else {
            $availableAmt = $availableAmt;
          }
          echo '
          <div class="col-lg-3 col-md-3 col-sm-12" name="amtAvailable" id="amtAvailable">
            <label for="exampleInputEmail1">Available Amount</label>
            <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
          </div>    
          <div class="col-lg-3 col-md-3 col-sm-12">
            <label>Message</label>
            <input type="text" class="form-control" style="color:red;" value="Your available amount is '.$availableAmt.'.Please deposit." name= "pln" id="pln" readonly>
          </div>';
        }
      } else {
      echo'
        <div class="col-lg-6 col-md-6 col-sm-12">
          <label>Message</label>
          <input type="text" class="form-control" style="color:red;" value="Invalid Client" readonly>
        </div>
        <script type="text/javascript">
          $("#bondSave").hide();
          $("#bid").hide();
          $("#bid2").hide();
          $("#rencd").hide();
          $("#amtAvailable").hide();
        </script>';
      }
}
//bond based on yield
elseif(!empty($_POST["bondCDYeildRate"])) {
  $cd = $_POST['bondCDYeildRate'];
  
  $cdCod= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id");
  $cdCod->bindParam(':cd', $cd);
  $cdCod->execute();
  $state1 = $cdCod->fetch();


  $amount= $dbh->prepare("SELECT sum(amount) as amount from bond_finance where cd_code=:cd AND user_name=:un and status=0");
  $amount->bindParam(':cd', $cd);
  $amount->bindParam(':un', $username);
  $amount->execute();
  $amt = $amount->fetch();

  $facevalue = $dbh->prepare("SELECT rate,symbol FROM assign_broker where username = :username and status=1 and type='BOND'");
  $facevalue->bindParam(':username', $username);
  $facevalue->execute();
  $fv = $facevalue->fetch();
  
  $sumAmt = bondavlAmt($cd);
  if($sumAmt == "") {
    $availableAmt = $amt['amount'];
  } else { 
    $availableAmt = $amt['amount'] - $sumAmt;
  }

  if($cdCod->rowCount() > 0 || $amount->fetch() > 0) {      
  echo'
    <div class="col-lg-6 col-md-6">
      <label>Client Details</label>
      <input type="text"  class="form-control" value="NAME : '.$state1['f_name'].' '.$state1['l_name'].' , CID/DISN# '.$state1['ID'].'" readonly>
    </div>';       
    $wc= $dbh->prepare("SELECT ID from client_account c where c.cd_code=:cd");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $ress=$wc->fetch();
    $cid=$ress['ID'];
    echo'
    <div class="col-lg-3 col-md-3 has-success" style="display: none;" name="volAvailable" id="volAvailable">
      <input type="hidden" class="form-control" name="symbol_id" id="symbol_id" value="'.$fv['symbol'].'">
      <input type="hidden" class="form-control" name="face_value" id="face_value" value="'.$fv['rate'].'">
      <input type="hidden" class="form-control" name="cdCodeee" id="cdCodeee" value="'.$cd.'">
      <input type="hidden" class="form-control" name="cid" id="cid" value="'.$cid.'">
    </div>';
    if ($availableAmt >= $fv['rate']) {
      echo ' 
      <div class="col-lg-3 col-md-3" name="amtAvailable" id="amtAvailable">
        <label for="availableAmount">Available Amount</label>
        <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
      </div>
      <div class="col-lg-3 col-md-3" name="symbol" id="symbol">
        <label>Symbol <font color="red">*</font></label>';
        $wc= $dbh->prepare("SELECT s.symbol,s.symbol_id FROM assign_broker a, symbol s where a.symbol=s.symbol_id and a.status=1 and a.type='BOND' AND a.username LIKE 'MEMRMA%'");
        $wc->execute();
        echo'<select name="sy" id="sy" class="form-control" onChange="bondselectsymbolyeild(this.value);">
        <option value=""> -Select Symbol- </option>';
        while($res= $wc->fetch()) {
          echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
        }
        echo'</select>
      </div>
      <div class="col-lg-3 col-md-3" style="display: none;" name="bid" id="bid">
        <label for="bidPrice">Yeild Rate <font color="red">*</font></label>
        <input type="text" class="form-control"  name="bidPrice" id="bidPrice" required>
      </div>
      <div class="col-lg-3 col-md-3" style="display: none;" name="bid2" id="bid2">
       <label>Total Amount</label>
        <input type="text" class="form-control" name="volume" id="volume" readonly>                  
      </div>  ';
    } else {
      if($availableAmt == '') {
        $availableAmt = 0;
      } else {
        $availableAmt = $availableAmt;
      }
      echo'
      <div class="col-lg-3 col-md-3" name="amtAvailable" id="amtAvailable">
        <label for="availableAmount">Available Amount</label>
        <input type="number" class="form-control" name="availableAmount" id="availableAmount" value="'.$availableAmt.'" readonly>
      </div>    
      <div class="col-lg-3 col-md-3">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Your available amount is '.$availableAmt.'.Please deposit." name= "pln" id="pln" readonly>
      </div>';
    }
  } else {
    echo '    
      <div class="col-lg-6 col-md-6">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
      </div>
      <script type="text/javascript">
        $("#bondSave").hide();
        $("#bid").hide();
        $("#bid2").hide();
        $("#rencd").hide();
        $("#amtAvailable").hide();
      </script>';
    }
} //bond based on yield end
elseif(!empty($_POST["SymbolLoad"])) {
  // Initiate cURL
  $ch = curl_init();

  // Where you want to post data
  $url1 = "https://cms.rsebl.org.bt/RSEB2020/api2/indivclentholding.php";
  $url2 = "https://cms.rsebl.org.bt/RSEB2020/api2/MarketWatch_forcms.php";

  // Define the POST data
  $data1 = array(
      'ListedCompanies' => 'ListedCompanies',
      'symbol' => $_POST['val']
  );

  $data2 = array(
      'OrderForEachSymbol' => 'OrderForEachSymbol',
      'Symbol' => $_POST['val']
  );

  // Set cURL options for the first request
  curl_setopt($ch, CURLOPT_URL, $url1);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data1));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // added

  // Execute the first request
  $ListedCompanies = curl_exec($ch);
  $ListedCompanies = json_decode($ListedCompanies, true);

  // Set cURL options for the second request
  curl_setopt($ch, CURLOPT_URL, $url2);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data2));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // added

  // Execute the second request
  $output = curl_exec($ch);

  // Close cURL handle
  curl_close($ch);

  // Process the results
  if ($ListedCompanies[0]['symbol'] == 'No Data') {
      $SymbolDetails = $_POST['val'];
      $PaidUpShares = 'No Data';
  } else {
      $SymbolDetails = $ListedCompanies[0]['name'] . ' (' . $ListedCompanies[0]['sector'] . ')';
      $PaidUpShares = number_format($ListedCompanies[0]['paid_up_shares']);
  }

  // Output the HTML
  echo'
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">' . $SymbolDetails . '</h4>
        <span> Paid up Shares : ' . $PaidUpShares . '</span>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-lg-12 col-sm-12 col-md-12">
            <div id="containerChart"></div>
          </div>
          <div class="col-lg-6 col-sm-12 col-md-12">
            <table id="example1" class="table table-bordered table-striped table-condensed">
              <thead>
                <tr>
                  <th>Buy Vol</th>
                  <th>Price</th>
                  <th>Sell Vol</th>
                </tr>
              </thead>
              <tbody>';
              $values = json_decode($output, true);
              $maxTrade = 0;
              $valueSize = 0;
              /*if (is_countable($values)) {
                  $valueSize = count($values);
              }*/
              $valueSize = (is_array($values)) ? count($values) : 0;
              if ($valueSize > 0) {
                foreach ($values as $key) {
                  if ($key['Price'] == $key['Discovered']) {
                      $class = '#17202A';
                      $color = 'white';
                  } else {
                      $class = 'white';
                      $color = 'black';
                  }
                echo'
                <tr>
                  <td style="color:#5DADE2;background-color:'.$class.'">'.number_format($key['BuyVol']).'</td>
                  <td style="color:'.$color.';background-color:'.$class.'">'.$key['Price'].'</td>
                  <td style="color:red;background-color:'.$class.'">'.number_format($key['SellVol']).'</td>
                </tr>';
                }
              }
          echo'
          </tbody>
        </table>
        </div>
        <div class="col-lg-6 col-sm-12 col-md-12">
          <strong>Corporate Actions</strong>
          <table class="table table-bordered table-striped table-condensed">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Rate%</th>
              </tr>
            </thead>
            <tbody>';
            foreach ($ListedCompanies as $key) {
              echo'
              <tr>
                <td>'.$key['announcement_date'].'</td>
                <td>'.$key['Type'].'</td>
                <td>'.$key['rate'].'</td>
              </tr>';
            }
            echo
            '</tbody>
          </table>
        </div>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>';
}
elseif (isset($_POST['get_pending_order_list'])) {
  $username = $_POST['usernmae'];
  $sec_type = $_POST['sec_type'];

  /*error_log(print_r($_POST, true));
  exit();*/

  echo'
  <table id="pen_odr_tbl_id" class="table table-bordered table-striped" width="100%">
    <thead>
      <tr>
        <th>#</th>
        <th>Symbol</th>
        <th>CD Code</th>
        <th>Price</th>
        <th>Volume</th>
        <th>Side</th>
        <th>Time</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>';
      $tableMap = [
          'OS' => [
              'table' => 'orders',
              'id'    => 'order_id'
          ],
          'CB' => [
              'table' => 'bond_orders',
              'id'    => 'id'
          ],
          'GB' => [
              'table' => 'bond_orders',
              'id'    => 'id'
          ],
      ];

      if (!isset($tableMap[$sec_type])) {
          throw new InvalidArgumentException('Invalid security type');
      }

      $table   = $tableMap[$sec_type]['table'];
      $idField = $tableMap[$sec_type]['id'];

      $sql = "
          SELECT a.$idField AS order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id, b.security_type 
          FROM {$table} a
          INNER JOIN symbol b ON a.symbol_id = b.symbol_id
          WHERE a.order_entry = ?
            AND b.security_type = ?
          ORDER BY a.order_date DESC
      ";

      $stmt = $dbh->prepare($sql);
      $stmt->execute([$username, $sec_type]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $i = 1;
      foreach ($rows as $res) {
        $background_color = $res['side'] == 'S' ? '#eb8292' : '#bac2cb';
        $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
        echo'
        <tr style="background-color:' . $background_color . '">
          <input type="hidden" value="' . $res['symbol'] . '" id="sy' . $i . '">
          <input type="hidden" value="' . $res['symbol_id'] . '" id="sy_id' . $i . '">
          <input type="hidden" value="' . $res['cd_code'] . '" id="cd_code' . $i . '">
          <input type="hidden" value="' . $res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'] . '" id="v' . $i . '">
          <input type="hidden" value="' . $res['flag_id'] . '" id="fid' . $i . '">
          <input type="hidden" value="' . $res['side'] . '" id="side' . $i . '">
          <input type="hidden" value="' . $res['security_type'] . '" id="sec_type' . $i . '">
          <td>' . $i . '</td>
          <td>' . $res['symbol'] . '</td>
          <td>' . $res['cd_code'] . '</td>
          <td><input type="text" class="form-control" size="5" value="' . $res['price'] . '" id="e_p' . $i . '"></td>
          <td><input type="text" class="form-control" size="8" value="' . $res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'] . '" id="e_v' . $i . '"></td>
          <td>' . $side . '</td>
          <td>' . $res['order_date'] . '</td>
          <td>
            <button type="button" class="btn btn-primary" name="chg_or" id="chg_or' . $i . '" value="' . $res['order_id'] . '"  onclick="return fun(' . $i . ');" data-toggle="tooltip" data-placement="top" title="Click Here To Change Order for ' . $res['cd_code'] . ', ' . $res['symbol'] . ' Symbol"><i class="fa fa-wrench"></i> Change</button>
          </td>
        </tr>';
        $i++;
      }
    echo'
    </tbody>
  </table>

  <script>
    $( function () {
      $("#pen_odr_tbl_id").DataTable();
    });
  </script>';
  exit();
}
elseif (isset($_POST['get_order_list_to_delete'])) {
  $username = $_POST['usernmae'];
  $sec_type = $_POST['sec_type'];

  echo'
  <table id="del_odr_tbl_id" class="table table-bordered table-striped" width="100%">
    <thead>
      <tr>
        <th>#</th>
        <th>Symbol</th>
        <th>CD Code</th>
        <th>Price</th>
        <th>Volume</th>
        <th>Side</th>
        <th>Time</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>';
      $tableMap = [
          'OS' => [
              'table' => 'orders',
              'id'    => 'order_id'
          ],
          'CB' => [
              'table' => 'bond_orders',
              'id'    => 'id'
          ],
          'GB' => [
              'table' => 'bond_orders',
              'id'    => 'id'
          ],
      ];

      if (!isset($tableMap[$sec_type])) {
          throw new InvalidArgumentException('Invalid security type');
      }

      $table   = $tableMap[$sec_type]['table'];
      $idField = $tableMap[$sec_type]['id'];

      $sql = "
          SELECT a.$idField AS order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id, b.security_type 
          FROM {$table} a
          INNER JOIN symbol b ON a.symbol_id = b.symbol_id
          WHERE a.order_entry = ?
            AND b.security_type = ?
          ORDER BY a.order_date DESC
      ";
      $stmt = $dbh->prepare($sql);
      $stmt->execute([$username, $sec_type]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $i = 1;
      foreach ($rows as $res) {
        $background_color = $res['side'] == 'S' ? '#eb8292' : '#bac2cb';
        $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
        echo'
        <tr style="background-color:' . $background_color . '">
          <input type="hidden" value="' . $res['symbol'] . '" id="sy' . $i . '">
          <input type="hidden" value="' . $res['symbol_id'] . '" id="sy_id' . $i . '">
          <input type="hidden" value="' . $res['cd_code'] . '" id="cd_code' . $i . '">
          <input type="hidden" value="' . $res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'] . '" id="v' . $i . '">
          <input type="hidden" value="' . $res['flag_id'] . '" id="fid' . $i . '">
          <input type="hidden" value="' . $res['side'] . '" id="side' . $i . '">
          <input type="hidden" value="' . $res['security_type'] . '" id="sec_type' . $i . '">
          <td>' . $i . '</td>
          <td>' . $res['symbol'] . '</td>
          <td>' . $res['cd_code'] . '</td>
          <td>' . $res['price'] . '</td>
          <td>' . $res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'] . '</td>
          <td>' . $side . '</td>
          <td>' . $res['order_date'] . '</td>
          <td>
            <button type="button" class="btn btn-danger" name="del_or" id="del_or' . $i . '" value="' . $res['order_id'] . '"  onclick="return del_fun(' . $i . ');" data-toggle="tooltip" data-placement="top" title="Click Here To Delete Order for ' . $res['cd_code'] . ', ' . $res['symbol'] . ' Symbol"><i class="fa fa-trash-o"></i> Delete</button>
          </td>
        </tr>';
        $i++;
      }
    echo'
    </tbody>
  </table>

  <script>
    $( function () {
      $("#del_odr_tbl_id").DataTable();
    });
  </script>';
  exit();
}
elseif(!empty($_POST["get_user_account_dtls"])) {
  $cidNo = $_POST['cidNo'];
  $user_pass_code = $pass_code;

  $stmt = $dbh->prepare("SELECT u.participant_code, u.username, u.cid, u.phone, u.email, u.created_at, u.name, DATE_ADD(u.created_at, INTERVAL 1 YEAR) AS valid_until, u.status 
      FROM users u 
      WHERE u.cid =  ?
      AND u.participant_code = ?
      AND u.role_id = 4
  ");
  $stmt->bindParam(1, $cidNo);
  $stmt->bindParam(2, $user_pass_code);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if($rows) {
    echo'
    <div class="table-responsive">
      <table class="table table-bordered" id="tableExpId" width="100%">
        <thead>
          <tr style="background-color:#333;color:#fff">
            <th scope="col">CID No</th>
            <th scope="col">Name</th>
            <th scope="col">Phone</th>
            <th scope="col">Email</th>
            <th scope="col">Member</th>
            <th scope="col">Username</th>
            <th scope="col">Created At</th>
            <th scope="col">Expiry Date</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        foreach ($rows as $res) {
          $status_ss = ($res['status'] == 1) ? 'Active' : 'In-active';
          echo'
          <tr>
            <td>' . $res['cid'] . '</td>
            <td>' . $res['name'] . '</td>
            <td>' . $res['phone'] . '</td>
            <td>' . $res['email'] . '</td>
            <td>' . $res['participant_code'] . '</td>
            <td>' . $res['username'] . '</td>
            <td>' . $res['created_at'] . '</td>
            <td>' . $res['valid_until'] . '</td>
            <td>' . $status_ss . '</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="reset_lock(\'' . $res['username'] . '\')"><i class="fa fa-unlock"></i> Unlock</button>
            </td>
          </tr>';
        }
     echo'</tbody>
     </table>
    </div>
    <script type="text/javascript">
      function reset_lock(usr_name) {
        showLoading();
        if (confirm("Do you want to continue?")) {
          var op = "account_unlock";
          $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: "usr_name=" + usr_name + "&account_unlock=" + op,
            dataType: "JSON",
            success: function(response){
              hideloading();
              $("#message").html(response.message);
              showMessage();
            }
          });
        } else {
          hideloading();
          return false;
        }
      }

    </script>';
  } else {
    echo'
    <div class="col-xs-12">
      <input type="text" class="form-control" value="No MCAMS users found under your brokerage." disabled>
    </div>';
  }
}
else
{
}
?>

<script type="text/javascript">
  function renounceCDcode(val) { 
    var cd = $("#cdCodeee").val();
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data: { CD: cd, renounceCD: val },
      dataType: "html",
      success: function(data) { 
        $("#rencd").show().html(data);
      } 
    });
  }

  function selectsymbol(val) { 
    var symbol = val;
    if(symbol== '0') {
      $('#bid').hide();
      $('#bid2').hide();
      $("#riSave").hide();
    } else {
      $('#bid').show();
      $('#bid2').show();
      $("#riSave").show();
    }
  }

  function getState3(val) {
    if(val.length < 10) {
      $("#message").html("<div class='col-lg-12 col-sm-12'><div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert'aria-hidden='true'>&times;</button><i class='icon fa fa-warning'></i> CD Code should be 10 Digits</div></div>");
      showMessage();
      $("#cd1").hide();
      $('#save_client').hide();
    } else {
      $('#save_client').show();
      $("#cd1").show();
      $.ajax({ 
        type: "POST", 
        url: "load.php", 
        data:'cdCode='+val, 
        dataType: "html",
        success: function(data){ 
          $("#cd1").html(data);
        } 
      });
    }
  }

  function getState4(val) {
    if(val.length < 10) {
      $("#message").html("<div class='col-lg-12 col-sm-12'><div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert'aria-hidden='true'>&times;</button><i class='icon fa fa-warning'></i> CD Code should be 10 Digits</div></div>");
      showMessage();
      $("#cd1").hide();
      $("#save_client").hide();
    } else {
      $("#save_client").show();
      $("#cd1").show();
      $.ajax({
        type: "POST", 
        url: "load.php", 
        data:'cdCodeAC='+val, 
        dataType: "html",
        success: function(data) { 
          $("#cd1").html(data);
        } 
      });
    }
  }

  $( function () {
    $("#example1").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });

  function tots1(val) {
    const tp = $("#tp").val();
    
    $("#v_div").hide();
    $("#p_div").hide();
    $("#bond_details_id").hide();
    $("#avl_amt_div_id").hide();
    $("#ytm_div_id").hide();
    $(".submit").hide();

    $.ajax({
      type: "POST", 
      url: "ja.php", 
      data:'cid=' + val + '&tp=' + tp,
      dataType: "html",
      success: function(data) {
        const dd =  data.split('|');
        const result = dd[0];
        const ac = dd[1];
        
        if(result == 0) {
          $(".submit").hide();
          // $("#sy_div").hide();
          $("#v_div").hide();
          $("#p_div").hide();
          $("#bond_details_id").hide();
          $("#msg1").show().text(ac + ' , does not have a CD account');
        } else {
          $("#cd").html(data);
          $(".submit").show();
          // $("#sy_div").show();
          $("#v_div").show();
          $("#p_div").show();
          $("#msg1").hide();
        }
      }
    });
  }

  // sell side
  function tots2(val) {
    const cd = $("#cd_code").val();
    const secrType = $("#sec_type").val();

    $.ajax({
      type: "POST",
      url: "ja.php",
      data: {
          sy: val,
          cd_code: cd,
          sert_type: secrType
      },
      success: function (data) {
        $("#cdd").html(data);
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
      }
    });
  }

  // buy side
  function tots3(val) {
    const ac = $("#cid").val();
    const price = $("#price").val();
    const buy_vol = $("#buy_vol").val();
    const b_commis = $("#b_commis").val();
    const secrType = $("#sec_type").val();

    $.ajax({ 
      type: "POST", 
      url: "ja.php", 
      data: {
        sy: val,
        ac: ac,
        price: price,
        buy_vol: buy_vol,
        b_commis: b_commis,
        sert_type: secrType,
      },
      success: function(response) { 
        $("#cdd").html(response);
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
      }
    });
  }

  $("#sellsubmit").click( function() {
    $("#sellsubmit").prop("disabled", true);
    showLoading();
    var cdcode = $("#cid").val();
    var p_code = $("#p_code").val();
    var u_name = $("#u_name").val();
    var b_commis = $("#b_commis").val();
    var avl_vol = $("#avl_vol").val();
    var pov = $("#pov").val();
    var piv = $("#piv").val();
    var vol = $("#vol").val();
    var sy_id = $("#sy").val();
    var price = $("#price").val();
    var side = "S";
    const security_type = $("#sec_type").val();

    var dataString = '';
    var process_url = '';

    if (security_type == 'OS') {
        dataString = 'cdcode=' + cdcode + '&p_code=' + p_code + '&u_name=' + u_name + '&vol=' + vol + '&sy_id=' + sy_id + '&price=' + price + '&side_for_order=' + side + '&b_commis=' + b_commis + '&avl_vol=' + avl_vol + '&pov=' + pov + '&piv=' + piv;
        process_url = '../PROCESS/process.php';
    } 
    else if (security_type == 'GB' || security_type == 'CB') {
        dataString = {
          placing__bond__order : 'placing__bond__order',
          cdcode,
          p_code,
          u_name,
          vol,
          sy_id, 
          price,
          side,
          b_commis,
          avl_vol,
          pov,
          piv,
        };
        process_url = '../PROCESS/bond_trading_process.php';
    }

                     
    if(cdcode == ''|| vol == '' || price == '' || vol == 0) {
      alert("Please Fill All Mandatory Fields");
      $("#sellsubmit").prop("disabled", false);
      hideloading();
      return false;
    } else {
      $.ajax({
        type: "POST",
        url: process_url,
        data: dataString ,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#sellsubmit").prop("disabled", false);
          $("#orderMessageS").html(response).fadeIn();
          setTimeout(function() { $("#orderMessageS").fadeOut(); }, 5000);
        }
      });
    }
  });

  $("#buysubmit").click( function() {
    // showLoading();
    // $("#buysubmit").prop("disabled", true);
    var cdcode = $("#cid").val();
    var p_code = $("#p_code").val();
    var u_name = $("#u_name").val();
    var b_commis = $("#b_commis").val();
    var avl_vol = $("#avl_vol").val();
    var pov = $("#pov").val();
    var piv = $("#piv").val();
    var vol = $("#buy_vol").val();
    var sy_id = $("#sy").val();
    var price = $("#price").val();
    var side = "B";
    const security_type = $("#sec_type").val();

    const ytm_id = Number($("#ytm_id").val());
    const dirty_price = Number($("#dirty_price").val());
    const accrued_interest = Number($("#accrued_interest").val());

    console.log(dirty_price);
    console.log(accrued_interest);
    console.log(ytm_id);

    const payable_price = Number(vol) * Number(dirty_price);
    // alert("Payable price = " + payable_price.toFixed(2) + ", including accrued interest => " + accrued_interest);
    // return false;

    var dataString = '';
    var process_url = '';

    if (security_type == 'OS') {
      dataString = 'cdcode=' + cdcode + '&p_code=' + p_code + '&u_name=' + u_name + '&vol=' + vol + '&sy_id=' + sy_id + '&price=' + price + '&side_for_order=' + side + '&b_commis=' + b_commis + '&avl_vol=' + avl_vol + '&pov=' + pov + '&piv=' + piv;
      process_url = '../PROCESS/process.php';
    } 
    else if (security_type == 'GB' || security_type == 'CB') {
      dataString = {
        placing__bond__order : 'placing__bond__order',
        cdcode,
        p_code,
        u_name,
        vol,
        sy_id, 
        price,
        side,
        b_commis,
        avl_vol,
        pov,
        piv,
      };
      process_url = '../PROCESS/bond_trading_process.php';
    }

    // console.log(dataString);
    // return false;

    if(cdcode == ''|| vol == '' || price == ''|| vol == 0) {
      alert("Please Fill All Mandatory Fields");
      hideloading();
      $("#buysubmit").prop("disabled", false);
    } else {
      $.ajax({
        type: "POST",
        url: process_url,
        data: dataString ,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#buysubmit").prop("disabled", false);
          $("#orderMessageB").html(response).fadeIn();
          setTimeout(function() { $("#orderMessageB").fadeOut(); }, 5000);
        }
      });
    }
    return false;
  });

  $("#rm").keyup('input', function() {
    $("#cre").show();
    $("#deb").show();
  });

  $("#subscribe1").keyup('input', function() {
    var subscribe = $("#subscribe1").val();
    var facevalue = $("#face_value").val();
    var totalAmount = subscribe * facevalue;
    var avail_vol = $("#availableVolume").val();    
    var availableAmount = $("#availableAmount").val();        

    if(Number(avail_vol) >= Number(subscribe) && Number(availableAmount) >= Number(totalAmount)) {
      $("#riSave").show();
      $("#subscribe1ErrMsg").html("");
    } 
    else if(subscribe === '' ) {
      $("#riSave").hide();
      $("#subscribe1ErrMsg").html("Enter Volume");
    } 
    else {
      $("#riSave").hide();
      $("#subscribe1ErrMsg").html("Insufficient Amount / volume");
    }
  });

  $("#renounce1").keyup('input', function() {
    var renounce = $("#renounce1").val();
    var facevalue = $("#face_value").val();
    var totalAmount = renounce * facevalue;
    var avail_vol = $("#availableVolume").val();  
    var availableAmount = $("#renAvailableAmount").val();

    if(Number(avail_vol) >= Number(renounce) && Number(availableAmount) >= Number(totalAmount)) {
      $("#riSave").show();
      $("#renounce1ErrMsg").html("");
    }
    else if(renounce === '') {
      $("#riSave").hide();
      $("#renounce1ErrMsg").html("Enter Volume");
    }
    else {
      $("#riSave").hide();
      $("#renounce1ErrMsg").html("Insufficient Amount / volume");
    }
  });

  $("#buyAmountR").keyup('input', function() {
    //var bid = $("#bidPrice").val();
    var amount = $("#buyAmountR").val();           
    //var facevalue = $("#face_value").val();
    
    var availableAmount = $("#availableAmount").val();

    if(Number(availableAmount) >= Number(amount) && !isNaN(amount))
    {
                             
      $("#bid").show();
      $("#bid2").show();              
    } 
    else if(amount === '')
    {
      $("#bid").hide();
      $("#bid2").hide();
    }
    else
    {
      $("#bid").hide();
      $("#bid2").hide();
    }
  });

  //This was disabled to accomodate Auction on 3/6/2021, uncomment to auto calcualte total volume
  /*$("#bidPrice").keyup('input', function() {
    var bid = $("#bidPrice").val();
    var facevalue = $("#face_value").val();
    var amount = $("#availableAmount").val(); 
    var vol = Number(amount)/Number(bid);
    if(Number(bid) > Number(facevalue) && !isNaN(bid))
    {
      document.getElementById('volume').value=vol; 
      $("#riSave").show();              
    } 
    else if(bid === '')
    {
      $("#riSave").hide();
    }
    else
    {
      $("#riSave").hide();
    }
  });*/

  $("#offerVol1").keyup('input', function() {
    var offer = $("#offerVol1").val();
    var avail_vol = $("#availableVolume").val();
    if(Number(avail_vol) >= Number(offer))
    {
     $("#riSave").show();
    }
    else if(offer === '')
    {
      $("#riSave").hide();
    }
    else
    {
      $("#riSave").hide();
    }
  });

  $("#buyAmount").keyup('input', function() {
    var amount = $("#buyAmount").val();           
    var facevalue = $("#face_value").val();
    var vol = Number(amount)/Number(facevalue);
    var availableAmount = $("#availableAmount").val();

    if(Number(availableAmount) >= Number(amount) && !isNaN(vol))
    {
      document.getElementById('volume').value=vol;                        
      $("#buy").show();
    } 
    else if(amount === '') {
      $("#buy").hide();
    }
    else {
      $("#buy").hide();
    }
  });

  function iposelectsymbol(val) { 
    var symbol = val;
    if(symbol == '') {
      $('#bid').hide();
      $('#bid2').hide();
      $("#ipoSave").hide(); 
    } else {
      var bid = $("#bidPrice").val();
      var amount = $("#availableAmount").val(); 
      var vol = Number(amount) / Number(bid);
      document.getElementById('volume').value=vol; 
      $("#ipoSave").show(); 
      $('#bid').show();
      $('#bid2').show();             
    }
    
  }

  function bondselectsymbol(val) {
    var symbol = val;
    if(symbol == "") {
      $('#bid').hide();
      $('#bid2').hide();
      $("#bondSave").hide(); 
    } else {
      var bid = $("#bidPrice").val();
      var amount = $("#availableAmount").val(); 
      var vol = Number(amount) / Number(bid);
        document.getElementById('volume').value = vol; 
        $("#bondSave").show(); 
        $('#bid').show();
        $('#bid2').show();             
    }
    
  }

  function bondselectsymbolyeild(val) { 
    var symbol = val;
    if(symbol== '') {
      $('#bid').hide();
      $('#bid2').hide();
      $("#bondSave").hide(); 
    } else {
      var amount = $("#availableAmount").val(); 
        document.getElementById('volume').value=amount; 
        $("#bondSave").show(); 
        $('#bid').show();
        $('#bid2').show();             
    }
  }
</script>
 