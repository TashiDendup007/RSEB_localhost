<?php
  include('sessionStartFile_cdscss.php');  
  include ('../../CONNECTIONS/db.php');

if(!empty($_POST["edit_cli"])) {
    $wc= $dbh->prepare("SELECT a.client_id, a.acc_type, a.cd_code, a.f_name, a.l_name, a.ID, a.nationality, a.occupation, a.phone, a.email, a.tpn, a.title, a.bank_account_type, a.bank_account, a.address, a.license_no, a.DzongkhagID, a.bank_id, a.gewog_id, a.village_id, a.gender, a.dob, a.marital_status, a.guardian_name
      FROM client_account a
      WHERE a.client_id=:id
    ");
    $wc->bindParam(':id', $_POST['edit_cli']);
    $wc->execute();
    $state = $wc->fetch(PDO::FETCH_ASSOC);
    echo'
    <div class="modal-dialog modal-lg">
      <form action="../PROCESS/process.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Client Details</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <input type="hidden" class="form-control" name="atype" id="atype" value="'.$state['acc_type'].'">
            <div class="col-lg-4 col-md-4 col-sm-12">
              <label>CID/DISN No.</label>
              <input type="text" class="form-control" name="id" id="id" value="'.$state['ID'].'" readonly>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12">
              <label>CD Code</label>
              <input type="text" class="form-control" name="cdcode" id="cdcode" maxlength="10" value="'.$state['cd_code'].'" readonly>
            </div>';
            if ($state['acc_type'] == "I") {
              echo'
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Title</label>
                <select id="title" name="title" class="form-control" required>
                  <option value="">-- Select --</option>';
                  $stmt = $dbh->prepare("SELECT id, title_name FROM title_master WHERE status = 1 ORDER BY title_name ASC");
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $row) {
                    $selected = ($row['title_name'] == $state['title']) ? ' selected' : '';
                    echo '<option value="' . $row['title_name'] . '"' . $selected . '>' . $row['title_name'] . '</option>';
                  }
                  echo'
                </select>
              </div>

               <div class="col-lg-4 col-md-4 col-sm-12">
                <label>First Name</label>
                <input type="text" class="form-control" name="fn" id="fn" value="'.$state['f_name'].'" required>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
               <label>Last Name</label>
                <input type="text" class="form-control" name="ln" id="ln" value="'.$state['l_name'].'">
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Nationality</label>
                <input type="text" class="form-control" name="nat" id="nat" value="'.$state['nationality'].'" required>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Gender</label>
                <select id="gender" name="gender" class="form-control" required>
                  <option value="">-- Select --</option>';
                  $stmt = $dbh->prepare("SELECT id, gender FROM tbl_gender_master WHERE status = 1 ORDER BY gender ASC");
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach($rows as $row) {
                    $selected = ($row['id'] == $state['gender']) ? ' selected' : '';
                    echo '<option value="'.$row['id'].'" ' . $selected . '>'.$row['gender'].'</option>';
                  }
                echo'
                </select>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Marital Status</label>
                <select id="marital" name="marital" class="form-control" required>
                  <option value="">-- Select --</option>';
                  $stmt = $dbh->prepare("SELECT id, name FROM tbl_marital_status WHERE status = 1 ORDER BY name ASC");
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach($rows as $row) {
                    $selected = ($row['id'] == $state['marital_status']) ? ' selected' : '';
                    echo '<option value="'.$row['id'].'" ' . $selected . '>'.$row['name'].'</option>';
                  }
                echo'
                </select>
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
               <label>DOB</label>
                <input type="date" class="form-control" name="dob" id="dob" value="'.$state['dob'].'" onchange="checkAge(this.value)" required>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Occupation</label>';
                $q = $dbh->prepare("SELECT * FROM occupation ORDER BY occupation_name ASC");
                $q->execute();
                $options = '';
                while ($res = $q->fetch()) {
                  $selected = '';
                  if ($res['occupation'] == $state['occupation']) {
                    $selected = 'selected';
                  }
                  $options .= '<option value="'.$res['occupation'].'" '.$selected.'>'.$res['occupation_name'].'</option>';
                }
                echo'<select name="occupation" id="occupation" class="form-control"> '.$options.' </select>
              </div>';
              } else {
              echo'
              <input type="hidden" class="form-control" name="title" id="title" value="">
              <input type="hidden" class="form-control" name="nat" id="nat" value="">
              <input type="hidden" class="form-control" name="occupation" id="occupation" value="101">
              <input type="hidden" class="form-control" name="dob" id="dob" value="">
              <input type="hidden" class="form-control" name="gender" id="gender" value="">
              <input type="hidden" class="form-control" name="marital" id="marital" value="">

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Company/Association Name <span style="color:red;">*</span></label>
                <input type="text" class="form-control" name="fn" id="fn" value="'.$state['f_name'].'" required>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Registration/License No <span style="color:red;">*</span></label>
                <input type="text" name="licenseNo" id="licenseNo" class="form-control" value="'.$state['license_no'].'" required>
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
               <label>Contact Person</label>
                <input type="text" class="form-control" name="ln" id="ln" value="'.$state['l_name'].'">
              </div>';
              }
              echo'
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>TPN</label>
                <input type="text" class="form-control" maxlength="9" name="tpn" id="tpn" value="'.$state['tpn'].'" required>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Phone No <span style="color:red;">*</span></label>
                <input type="number" onKeyPress="if(this.value.length==8) return false;" class="form-control" name="phone" value="'.$state['phone'].'" id="phone" required>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Email</label>
                <input type="text" class="form-control" name="email" value="'.$state['email'].'" id="email">
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12" id="guardian_div">
                <label>Guardian Name (If Minor)</label>
                <input type="text" class="form-control" name="guardian_name" id="guardian_name" value="'.$state['guardian_name'].'">
              </div>

              <div class="clearfix"></div>
              <p style="padding-left: 18px; font-weight: bold; margin-top: 10px; color: #09895b;" class="text-center">Permanent Address</p>
              <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Dzongkhag</label>
                <select id="dz" name="dz" class="form-control" onchange="populatelist(this.value, \'gewog_list\', \'\')" required>
                  <option value="">--Select--</option>';
                  $q = $dbh->prepare('SELECT DzongkhagID, DzongkhagName FROM tbldzongkhag ORDER BY DzongkhagName ASC');
                  $q->execute();
                  foreach($q as $st) {
                    $selected = ($st['DzongkhagID'] == $state['DzongkhagID']) ? ' selected' : '';
                    echo'<option value="'.$st['DzongkhagID'].'" ' . $selected . '>'.$st['DzongkhagName'].'</option>';
                  }
                  echo'
                </select>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Gewog</label>
                <select id="gewog_id" name="gewog_id" class="form-control" onchange="populatelist(this.value, \'village_list\', \'\')"  required>
                  <option value="">-- Select --</option>';
                  $q = $dbh->prepare("SELECT g.Gewog_Serial_No AS Serial_No, g.Gewog_Name AS Name FROM tbl_gewog_master g WHERE g.Dzongkhag_Serial_No = ? ORDER BY g.Gewog_Name ASC");
                  $q->bindParam(1, $state['DzongkhagID']);
                  $q->execute();
                  foreach($q as $st) {
                    $selected = ($st['Serial_No'] == $state['gewog_id']) ? ' selected' : '';
                    echo'<option value="'.$st['Serial_No'].'" ' . $selected . '>'.$st['Name'].'</option>';
                  }
                  echo'
                </select>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Village</label>
                <select id="village_id" name="village_id" class="form-control" required>
                  <option value="">-- Select --</option>';
                  $q = $dbh->prepare("SELECT v.Village_Serial_No AS Serial_No, v.Village_Name AS Name FROM tbl_village_master v WHERE v.Gewog_Serial_No = ? ORDER BY v.Village_Name ASC");
                  $q->bindParam(1, $state['gewog_id']);
                  $q->execute();
                  foreach($q as $st) {
                    $selected = ($st['Serial_No'] == $state['village_id']) ? ' selected' : '';
                    echo'<option value="'.$st['Serial_No'].'" ' . $selected . '>'.$st['Name'].'</option>';
                  }
                  echo'
                </select>
              </div>

              <div class="clearfix"></div>
              <p style="padding-left: 18px; font-weight: bold; margin-top: 10px; color: #09895b;" class="text-center">Bank Account Details</p>
              <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Bank Name</label>';
                $q = $dbh->prepare('SELECT bank_id, bank_name FROM banks');
                $q->execute();
                $options = '';
                while ($res = $q->fetch()) {
                  $selected = '';
                  if ($res['bank_id'] == $state['bank_id']) {
                    $selected = 'selected';
                  }
                  $options .= '<option value="'.$res['bank_id'].'" '.$selected.'>'.$res['bank_name'].'</option>';
                }
                echo'<select name="bank" id="bank" class="form-control"> '.$options.' </select>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Account Number</label>
                <input type="text" class="form-control" name="accno" id="accno" value="'.$state['bank_account'].'">
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Account Type</label>
                <select id="bankAccType" name="bankAccType" class="form-control" required>
                  <option value="">--Select--</option>';
                  $selectedBankAccType = $state['bank_account_type'];
                  $options = [
                      'Saving Account' => 'Saving Account',
                      'Current Account' => 'Current Account'
                  ];
                  foreach ($options as $value => $label) {
                    $selected = ($value == $selectedBankAccType) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
                  }
                  echo'
                </select>
              </div>

              <div class="clearfix"></div>
              <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">
              
              <div class="col-xs-12">
                <label>Address</label>
                <input type="text" class="form-control" name="add" id="add" value="'.$state['address'].'" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="edit_cli" id="edit_cli" value="'.$state['client_id'].'"><i class="fa fa-check"></i> Update</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          </div>
        </div>
      </form>
    </div>';
}
elseif(!empty($_POST["edit_fin"]))
{
    $wc= $dbh->prepare("SELECT * from bbo_finance where finance_id=:id");
    $wc->bindParam(':id',$_POST['edit_fin']);
    $wc->execute();
    $state=$wc->fetch();
    if($state['amount']<0){ $amt=$state['amount']*-1;}else{$amt=$state['amount'];}
    echo'<div class="modal-dialog">
      <!-- Modal content-->
      <form action="../PROCESS/process.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit </h4>
        </div>
        <div class="modal-body">
        <div class="box-body">
              <div class="row">
              <div class="col-xs-6">
                  <label>Account</label>
                  <input type="text" class="form-control"  value="'.$state['cd_code'].'" disabled>
                </div>
                <div class="col-xs-6">
                  <label>Amount</label>
                  <input type="number" class="form-control" name="amt" id="amt" min="1" value="'.$amt.'">
                </div>
                <div class="col-xs-12">
                 <label>remarks</label>
                  <input type="text" class="form-control" name="rm" id="rm" value="'.$state['remarks'].'">
                </div>
                <input type="hidden" class="form-control" name="flag" id="flag" value="'.$state['flag'].'">
              </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <div class="col-xs-4">
                <button type="submit" class="btn btn-primary" name="edit_fin" id="edit_fin"  value="'.$state['finance_id'].'">UPDATE</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      </form>
    </div>';
}
elseif(!empty($_POST["edit_val"]))
{
    $wc= $dbh->prepare("SELECT * from bbo_vault where vault_id=:id");
    $wc->bindParam(':id',$_POST['edit_val']);
    $wc->execute();
    $state=$wc->fetch();
    echo'<div class="modal-dialog">
      <!-- Modal content-->
      <form action="../PROCESS/process.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit </h4>
        </div>
        <div class="modal-body">
        <div class="box-body">
              <div class="row">
              <div class="col-xs-6">
                  <label>Account</label>
                  <input type="text" class="form-control"  value="'.$state['cd_code'].'" disabled>
                </div>
                <div class="col-xs-6">
                  <label>Holding</label>
                  <input type="number" class="form-control" name="hol" id="hol" min="1" value="'.$state['bbo_holding'].'">
                </div>
                <div class="col-xs-12">
                 <label>remarks</label>
                  <input type="text" class="form-control" name="rm" id="rm" value="'.$state['remarks'].'">
                </div>
              </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <div class="col-xs-4">
                <button type="submit" class="btn btn-primary" name="edit_val" id="edit_val"  value="'.$state['vault_id'].'">UPDATE</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      </form>
    </div>';
}
elseif(!empty($_POST["edit_link_user"]))
{
    $wc= $dbh->prepare("SELECT * FROM linkuser WHERE id = :id");
    $wc->bindParam(':id',$_POST['edit_link_user']);
    $wc->execute();
    $state=$wc->fetch();
    echo'<div class="modal-dialog">
      <!-- Modal content-->
      <form action="../PROCESS/process.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit </h4>
        </div>
        <div class="modal-body">
        <div class="box-body">
              <div class="row">
                <div class="col-xs-4">
                  <label>Participant Code</label>';
                            $wc= $dbh->prepare("select participant_id,participant_code from adm_participants");
                            $wc->execute();
                            echo '<select name="pcode" id="pcode"  class="form-control" disabled>';
                            echo '<option selected  value="'.$state['participant_code'].'">'. $state['participant_code'].'</option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['participant_code'].'">';
                            echo $res['participant_code'];
                            echo'</option>';
                            }
                            echo'</select>
                </div>
                <div class="col-xs-4">
                  <label>Client Account</label>';
                            $wc= $dbh->prepare("select username from users where role_id=4");
                            $wc->execute();
                            echo '<select name="ct" id="ct"  class="form-control" disabled>';
                            echo '<option selected value="'.$state['client_code'].'">'. $state['client_code'].'</option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['username'].'">';
                            echo $res['username'];
                            echo'</option>';
                            }
                            echo'</select>
                </div>
              </div>
            </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <div class="col-xs-4">
                <button type="submit" class="btn btn-primary" name="edit_tac" id="edit_tac"  value="'.$state['id'].'">UPDATE</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      </form>
    </div>';
}
elseif(!empty($_POST["edit_plg"]))
{
    $wc= $dbh->prepare("SELECT a.*, b.symbol,c.cd_code as ccd, c.symbol_id,c.volume,c.pledge_volume as cpl 
      from cds_pledge a,symbol b, cds_holding c
    where pledge_id=:id and a.symbol_id=b.symbol_id and a.symbol_id=c.symbol_id and a.cd_code=c.cd_code");
    $wc->bindParam(':id',$_POST['edit_plg']);
    $wc->execute();
    $state=$wc->fetch();
    $diff_pl=$state['cpl']-$state['pledge_volume'];
    $pl_able_vol=$state['volume']-$diff_pl;
    echo'
    <div class="modal-dialog">
      <form action="" method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit </h4>
          </div>
          <p class="statusMsg"></p>
          <div class="modal-body">
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-xs-6">
                  <input type="hidden" class="form-control" ng-model="old_pl_vol" name="old_pl_vol" id="old_pl_vol" value="'.$state['pledge_volume'].'" max="'.$state['pledge_volume'].'">
                  <label>Contract Code</label>
                  <input type="text" class="form-control" name="cc" id="cc" value="'.$state['pledge_contract'].'" readonly>
                </div>
                <div class="col-xs-6">
                  <label>CD Code</label>
                  <input type="text" class="form-control"  name="ac" id="ac"  value="'.$state['cd_code'].'" readonly>
                </div>
                <div class="col-xs-6">
                  <label>Symbol</label>
                  <input type="text" class="form-control" name="sy" id="sy" value="'.$state['symbol'].'"  readonly >
                </div>
                <div class="col-xs-6">
                 <label>Previous Pledged Volume</label>
                  <input type="number" class="form-control" name="old_pl_vol" id="old_pl_vol" value="'.$state['pledge_volume'].'" max="'.$state['pledge_volume'].'" readonly >
                </div>
                <div class="col-xs-6">
                 <label>New Pledged Volume</label>
                  <input type="number" class="form-control" name="trs" id="trs"  max="'.$pl_able_vol.'" placeholder="Max. Vol. releasable  is :'.$pl_able_vol.'">
                </div>
                <div class="col-xs-6">
                  <label>Pledgee</label>';
                  $wc = $dbh->prepare("SELECT * FROM cds_pledgee");
                  $wc->execute();
                  echo'
                  <select name="pl" id="pl"  class="form-control">
                  <option value="'.$state['pledgee'].'" selected>'.$state['pledgee'].'</option>';
                  while($res= $wc->fetch())
                  {
                    echo'<option value="'.$res['pledgee'].'">'.$res['pledgee'].'</option>';
                  }
                  echo'
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" name="edit_plg" id="edit_plg"  value="'.$state['pledge_id'].'"><i class="fa fa-check"></i> Update</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          </div>
        </div>
      </form>
    </div>';
}
elseif(!empty($_POST["edit_plg_contra"])) {
    $wc = $dbh->prepare("SELECT * FROM cds_pledge_contract WHERE cds_pledge_contract_id=:id");
    $wc->bindParam(':id', $_POST['edit_plg_contra']);
    $wc->execute();
    $state = $wc->fetch();
    echo'
    <div class="modal-dialog">
      <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit </h4>
        </div>
        <div class="modal-body">
          <div class="row" ng-app="">
             <div class="col-lg-12">
              <label>Name</label>
              <input type="text" class="form-control" value="'.$state['pledge_name'].'" readonly>
            </div>
            <div class="col-lg-4">
             <label>CD Code</label>
              <input type="text" class="form-control" value="'.$state['cd_code'].'" readonly>
            </div>
            <div class="col-lg-4">
              <label>Contract Code</label>
              <input type="text" class="form-control" value="'.$state['pledge_contract'].'" readonly>
            </div>
            <div class="col-lg-4">
              <label>Pledgee</label>
              <input type="text" class="form-control" value="'.$state['pledgee'].'"  readonly >
            </div>
            <div class="col-lg-4">
             <label>Pledged Date</label>
              <input type="text" class="form-control" " value="'.$state['pledge_date'].'"  readonly >
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal"> Close</button>
        </div>
      </div>
      </form>
    </div>';
}
elseif(!empty($_POST["edit_plg_rls"]))
{
    $wc= $dbh->prepare("SELECT a.*,b.symbol from cds_pledge a,symbol b where pledge_id=:id and a.symbol_id=b.symbol_id");
    $wc->bindParam(':id',$_POST['edit_plg_rls']);
    $wc->execute();
    $state=$wc->fetch();


    $wc1= $dbh->prepare("SELECT sum(pledge_volume) as plv from cds_pledge where pledge_contract=:cc and symbol_id=:id");
    $wc1->bindParam(':cc',$state['pledge_contract']);
    $wc1->bindParam(':id',$state['symbol_id']);
    $wc1->execute();
    $state1=$wc1->fetch();
    $sum=$state1['plv'];

    echo'
    <div class="modal-dialog">
      <form action="" method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit</h4>
          </div>
          <p class="statusMsg"></p>
          <div class="modal-body">
            <div class="box-body">
              <div class="row">
                <div class="col-xs-6">
                  <label>Contract Code</label>
                  <input type="text" class="form-control" name="cc" id="cc" value="'.$state['pledge_contract'].'" readonly>
                </div>
                 <div class="col-xs-6">
                  <label>CD Code</label>
                  <input type="text" class="form-control"  name="ac" id="ac" value="'.$state['cd_code'].'" readonly>
                </div>
                <div class="col-xs-6">
                  <label>Symbol</label>
                  <input type="text" class="form-control" name="" id="" value="'.$state['symbol'].'"  readonly >
                  <input type="hidden" class="form-control" name="sy" id="sy" value="'.$state['symbol_id'].'"  readonly >
                </div>
                <div class="col-xs-6">
                 <label>Pledgee</label>
                 <input type="text" class="form-control" name="pl" id="pl" value="'.$state['pledgee'].'" readonly>
                </div>
                <div class="col-xs-6">
                 <label>Previous Pledged Release Vol.</label>
                  <input type="number" class="form-control" name="old_pl_vol" id="old_pl_vol" value="'.$state['pledge_volume'].'"  readonly >
                </div>
                <div class="col-xs-6">
                 <label>Pledge Release Volume</label>
                  <input type="number" class="form-control" name="trs" id="trs" max="'.$sum.'" placeholder="Max. Vol. releasable  is :'.$sum.'" >
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" name="edit_plg_rls" id="edit_plg_rls"  value="'.$state['pledge_id'].'"><i class="fa fa-check"></i> Update</button>
              <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
          </div>
        </form>
      </div>';
}
//bank edit start
elseif (!empty ($_POST['bank_name']) && !empty($_POST['bank_id']))
{
  $bank_name = $_POST['bank_name'];
  $bank_id = $_POST['bank_id'];

  $save = $dbh->prepare("UPDATE banks SET bank_name=:bank_name where bank_id=:id");
  $save->bindParam(':bank_name', $bank_name);
  $save->bindParam(':id', $bank_id);
  $save->execute();

  exit();
}
//bank edit end
//bank branch edit start
elseif (!empty ($_POST['bra_id']) && !empty($_POST['bra_name']))
{
  //variable declaration
  $bra_name = $_POST['bra_name'];
  $bra_id = $_POST['bra_id'];
  // !- variable declaration

  $save = $dbh->prepare("UPDATE bank_branch  SET BRANCH_NAME=:bra_name where BRANCH_ID=:id");
  $save->bindParam(':bra_name', $bra_name);
  $save->bindParam(':id', $bra_id);
  $save->execute();
  exit();
}
//bank edit end
//occupation edit start
elseif (!empty ($_POST['occ_id']) && !empty($_POST['occ']))
{
  //variable declaration
  $occ_id = $_POST['occ_id'];
  $occ = $_POST['occ'];
  // !- variable declaration

  $save = $dbh->prepare("UPDATE occupation SET occupation_name=:occ where occupation=:id");
  $save->bindParam(':occ', $occ);
  $save->bindParam(':id', $occ_id);
  $save->execute();
  exit();
}
//occupation edit end
//occupation edit start
elseif (!empty ($_POST['pid']) && !empty($_POST['pname']) && !empty($_POST['padd']))
{
  //variable declaration
  $pid = $_POST['pid'];
  $pname = $_POST['pname'];
  $padd = $_POST['padd'];
  // !- variable declaration

  $save = $dbh->prepare("UPDATE cds_pledgee SET pledgee=:pname, address=:padd WHERE pledgee_id=:id");
  $save->bindParam(':pname', $pname);
  $save->bindParam(':padd', $padd);
  $save->bindParam(':id', $pid);
  $save->execute();

  exit();
}
//occupation edit end
//rights edit start
elseif (!empty ($_POST['corp_announcement_id']) && !empty($_POST['adate']) && !empty($_POST['rdate']) && !empty($_POST['edate']) && !empty($_POST['rate']))
{
  //variable declaration
  $corp_announcement_id = $_POST['corp_announcement_id'];
  $adate =date("Y-m-d",strtotime( $_POST['adate']));
  $rdate = date("Y-m-d",strtotime($_POST['rdate']));
  $edate = date("Y-m-d",strtotime($_POST['edate']));
  $rate = floatval($_POST['rate']);
  $type = $_POST['type'];
  $status = $_POST['status'];

  // !- variable declaration
  $save = $dbh->prepare("UPDATE corporate_announcement  SET record_date=:rdate,ex_date=:edate,rate=:rate,announcement_date=:adate,type=:type,status=:status where corp_announcement_id=:id");
  $save->bindParam(':rdate',$rdate);
  $save->bindParam(':edate',$edate);
  $save->bindParam(':rate',$rate);
  $save->bindParam(':adate',$adate);
  $save->bindParam(':id',$corp_announcement_id);
  $save->bindParam(':status',$status);
  $save->bindParam(':type',$type);
  if($save->execute()){
    echo "YES";
  }else{
    echo "NO";
  }
  die();
}
//rights edit end
//holiday edit start
elseif (!empty($_POST['hol_name']) && !empty($_POST['holiday_date'])  && !empty($_POST['hol_id']))
{
  //variable declaration
  $holiday_date = $_POST['holiday_date'];
  $hol_name = $_POST['hol_name'];
  $hol_id = $_POST['hol_id'];
  // !- variable declaration
  $save = $dbh->prepare("UPDATE holiday  SET hol_name=:hol_name,holiday_date=:hol_date where id=:id");
  $save->bindParam(':hol_name',$hol_name);
  $save->bindParam(':hol_date',$holiday_date);
  $save->bindParam(':id',$hol_id);
  $save->execute();
  die();
}
elseif (!empty($_POST['delete_holiday'])) {
  $hol_id = $_POST['hol_id'];

  $stmt = $dbh->prepare("DELETE FROM holiday WHERE id = :id");
  $stmt->bindParam(':id', $hol_id);
  $stmt->execute();

  die();
}
//holiday edit end
//sett edit start
elseif (!empty($_POST['set_name']) && !empty($_POST['set_id'])  && !empty($_POST['set_day']))
{
  //variable declaration
  $set_name = $_POST['set_name'];
  $set_id = $_POST['set_id'];
  $set_day = $_POST['set_day'];
  // !- variable declaration
  
  $save = $dbh->prepare("UPDATE css_settlement_cycle  SET name=:set_name,days=:set_day where sett_id=:id");
  $save->bindParam(':set_name',$set_name);
  $save->bindParam(':set_day',$set_day);
  $save->bindParam(':id',$set_id);
  $save->execute();
  die();
}
//sett edit end
else{
  echo 'no match';
}
?>
<script type="text/javascript">
  $("#edit_plg").click( function() {
    var pl_id = $("#edit_plg").val();
    var cc = $("#cc").val();
     if (confirm("Are you sure you want to update Contract Code # "+ cc + '?')) {
        var pl = $("#pl").val();
        var ac = $("#ac").val();
        var sy = $("#sy").val();
        var trs = $("#trs").val();
        var old_vol = $("#old_pl_vol").val();
        var operation = "edit_plg";

        var dataString = 'cc='+ cc +'&pl='+ pl + '&ac='+ ac +'&sy='+ sy + '&trs='+ trs + '&pl_id='+ pl_id + '&old_pl_vol='+ old_vol + '&edit_plg='+ operation;
          $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: dataString ,
            success: function(data){
              $('.statusMsg').html(data);
              $('.statusMsg').fadeOut(5000);
            }
          });
       } else {
           return false;
       }
  });

  $("#edit_plg_rls").click( function () {
    var pl_id = $("#edit_plg_rls").val();
    var cc = $("#cc").val();
     if (confirm("Are you sure you want to update Contract Code # "+ cc + '?')) {
        var ac = $("#ac").val();
        var sy = $("#sy").val();
        var trs = $("#trs").val();
        var old_vol = $("#old_pl_vol").val();
        var operation = "edit_plg_rls";
        var dataString = 'cc='+ cc +'&ac='+ ac +'&sy='+ sy + '&trs='+ trs + '&pl_id='+ pl_id + '&old_pl_vol='+ old_vol + '&edit_plg_rls='+ operation;
        $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: dataString ,
          success: function(data){
            $('.statusMsg').html(data);
            $('.statusMsg').fadeOut(5000);
          }
        });
     } else {
         return false;
     }
  });

  function funn(io) {
    var val= document.getElementById('delete_nom'+io).value;
    if (confirm("Are you sure you want to delete nominee Id # "+ val + '?')) {
        showLoading();
        var operation = "delete_nominee";
        var dataString = 'delete_nom='+ val +'&delete_nominee='+ operation;
          $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: dataString ,
          success: function(data){
            hideloading();
            $('.statusMsgDel').show();
            $('.statusMsgDel').show().html(data);
            $('.statusMsgDel').fadeOut(5000);
          }
          });
   }
   else
   {
       return false;
   }
 }
</script>
