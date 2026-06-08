<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');

  $check = $dbh->prepare('SELECT a.institution_id,c.participant_code  from adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id AND c.username=:un');
  $check->bindParam(':un', $username);
  $check->execute();
  $res = $check->fetch();
  $institution_id = $res['institution_id'];
  $participant_code = $res['participant_code'];

if(!empty($_POST["edit_cli"])) {
    $sql1 = "SELECT a.client_id, a.ID, a.acc_type, a.cd_code, a.title, a.f_name, a.l_name, a.occupation, a.nationality, a.license_no, a.DzongkhagID,a.gewog_id, a.village_id, a.tpn, a.phone, a.email, a.bank_account, a.bank_id, a.bro_comm_id, a.address, a.institution_id, a.bank_account_type,  
      b.DzongkhagName, b.DzongkhagID,g.Gewog_Name, g.Gewog_Id,v.Village_Name, v.Village_Id, c.bank_name, c.bank_id, o.occupation_name, com.commission_name 
      FROM client_account a 
      JOIN tbldzongkhag b ON a.DzongkhagID = b.DzongkhagID
      LEFT JOIN tbl_gewog_master g ON a.gewog_id = g.Gewog_Id 
      LEFT JOIN tbl_village_master v ON a.village_id = v.Village_Id 
      JOIN banks c ON a.bank_id = c.bank_id 
      JOIN occupation o ON a.occupation = o.occupation 
      JOIN bbo_commission com ON a.bro_comm_id = com.bro_comm_id 
      WHERE a.client_id=:id";
    $wc= $dbh->prepare($sql1);
    $wc->bindParam(':id', $_POST['edit_cli']);
    $wc->execute();
    $state = $wc->fetch();
    echo'
    <div class="modal-dialog modal-lg">
      <form action="../PROCESS/process.php" method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><strong>Edit Client Account</strong></h4>
          </div>
          <div class="modal-body">
            <div class="box-body">
              <input type="hidden" class="form-control" name="id" id="id" value="'.$state['ID'].'" >
              <input type="hidden" class="form-control" name="atype" id="atype" value="'.$state['acc_type'].'" >
  
              <div class="col-lg-4 col-md-4">
                <label>CD Code</label>
                <input type="text" class="form-control" name="cdcode" id="cdcode" maxlength="10"  value="'.$state['cd_code'].'" readonly>
              </div>';
              if ($state['acc_type'] == "I") {
                echo'
                <input type="hidden" name="licenseNo" id="licenseNo" value="0">
                <div class="col-lg-4 col-md-4">
                  <label>Title<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="title" id="title" value="'.$state['title'].'" required>
                </div>
                <div class="col-lg-4 col-md-4">
                  <label>First Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="fn" id="fn" value="'.$state['f_name'].'" required>
                </div>
                <div class="col-lg-4 col-md-4">
                 <label>Last Name</label>
                  <input type="text" class="form-control" name="ln" id="ln" value="'.$state['l_name'].'">
                </div>
                <div class="col-lg-4 col-md-4">
                  <label>Occupation</label>';
                  $q = $dbh->prepare("SELECT * from occupation order by occupation_name ASC");
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
                </div>
                <div class="col-lg-4 col-md-4">
                 <label>Nationality<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="nat" id="nat" value="'.$state['nationality'].'" required>
                </div>
                <div class="col-lg-4 col-md-4">
                  <label>CID<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" maxlength="11" value="'.$state['ID'].'" readonly>
                </div>';
              } else {
                echo'
                <input type="hidden" class="form-control" name="title" id="title" value="">
                <input type="hidden" class="form-control" name="nat" id="nat" value="">
                <input type="hidden" class="form-control" name="occupation" id="occupation" value="101">
                <div class="col-lg-8 col-md-8">
                  <label>Asso.Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="fn" id="fn" value="'.$state['f_name'].'" required>
                </div>
                <div class="col-lg-4 col-md-4">
                 <label>Contact Person</label>
                  <input type="text" class="form-control" name="ln" id="ln" value="'.$state['l_name'].'">
                </div>
                <div class="col-lg-4 col-md-4">
                  <label>Registration/License No<span style="color:red;">*</span></label>
                  <input type="text" name="licenseNo" id="licenseNo" class="form-control" value="'.$state['license_no'].'" required>
                </div>
                <div class="col-lg-4 col-md-4">
                  <label>DISN<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" maxlength="11" value="'.$state['ID'].'" readonly>
                </div>';
              }
              
              
              // Permanent Address Section
              echo'
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>TPN</label>
                <input type="text" class="form-control" maxlength="20" name="tpn" id="tpn" value="'.$state['tpn'].'">
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Phone No<span style="color:red;">*</span></label>
                <input type="number" class="form-control" name="phone" value="'.$state['phone'].'" id="phone" onKeyPress="if(this.value.length==8) return false;" required>
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Email</label>
                <input type="text" class="form-control" name="email" value="'.$state['email'].'" id="email">
              </div>
              
             

              <div class="clearfix"></div>
              <p style="padding-left: 18px; font-weight: bold; margin-top: 10px; color: #09895b;" class="text-center">Permanent Address</p>
              <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label for="dz">Dzongkhag<span style="color:red;">*</span></label>';
                $q=$dbh->prepare('SELECT * FROM tbldzongkhag ORDER BY DzongkhagName ASC');
                $q->execute();
                $options = '';
                while ($res = $q->fetch()) {
                  $selected = '';
                  if ($res['DzongkhagID'] == $state['DzongkhagID']) {
                    $selected = 'selected';
                  }
                  $options .= '<option value="'.$res['DzongkhagID'].'" '.$selected.'>'.$res['DzongkhagName'].'</option>';
                }
                echo'<select name="dz" id="dz" class="form-control" onchange="get_gewog(this.value)" required> '.$options.' </select>
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label for="gewog_id">Gewog<span style="color:red;">*</span></label>';
                $q=$dbh->prepare('SELECT * FROM tbl_gewog_master WHERE Dzongkhag_Serial_No = ? ORDER BY Gewog_Name ASC');
                $q->bindParam(1, $state['DzongkhagID']);
                $q->execute();
                $options = '';
                if (empty($state['gewog_id'])) {
                    $options .= '<option value="" selected>--Select--</option>';
                } 
                while ($res = $q->fetch()) {
                  $selected = '';
                  if ($res['Gewog_Serial_No'] == $state['gewog_id']) {
                    $selected = 'selected';
                  }
                  $options .= '<option value="'.$res['Gewog_Serial_No'].'" '.$selected.'>'.$res['Gewog_Name'].'</option>';
                }
                echo'<select name="gewog_id" id="gewog_id" class="form-control" onchange="get_village(this.value)" required> '.$options.' </select>
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label for="village_id">Village<span style="color:red;">*</span></label>';
                $options = '<option value="">--Select--</option>';
                if (empty($state['village_id'])) {
                    
                    $options .= '<option value="" selected>--Select--</option>';
                } else {
                    $q = $dbh->prepare("
                        SELECT v.Village_Serial_No AS Serial_No, v.Village_Name AS Name 
                        FROM tbl_village_master v 
                        WHERE v.Gewog_Serial_No = ? 
                        ORDER BY v.Village_Name ASC
                    ");
                    $q->bindParam(1, $state['gewog_id']);
                    $q->execute();
                }
                while ($res = $q->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($res['Serial_No'] == $state['village_id']) ? 'selected' : '';
                    $options .= '<option value="' . htmlspecialchars($res['Serial_No']) . '" ' . $selected . '>' . htmlspecialchars($res['Name']) . '</option>';
                }
                echo'<select name="village_id" id="village_id" class="form-control" required> '.$options.' </select>
              </div>
              
              <!-- Other fields outside of Permanent Address section -->
              
              
              <!-- Bank Account Details Section -->
              <div class="clearfix"></div>
              <p style="padding-left: 18px; font-weight: bold; margin-top: 10px; color: #09895b;" class="text-center">Bank Account Details</p>
              <hr style="margin-top: 7px!important; margin-bottom: 5px!important;">
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label for="bank">Bank<span style="color:red;">*</span></label>
                <select id="bank" name="bank" class="form-control" required>';
                $q = $dbh->prepare('SELECT * FROM banks');
                $q->execute();
                while ($res = $q->fetch()) {
                  $selected = ($res['bank_id'] == $state['bank_id']) ? 'selected' : '';
                  echo'<option value="'.$res['bank_id'].'" '.$selected.'>'.$res['bank_name'].'</option>';
                }
                echo'</select>
                <span id="bankError" style="color: red;"></span>
              </div>
  
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label for="account_no">Account Number<span style="color:red;">*</span></label>
                <input type="text" class="form-control" name="accno" id="accno" value="'.$state['bank_account'].'" required>
              </div>
  
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label for="bankAccType">Account Type<font style="color:red;">*</font></label>
                <select id="bankAccType" name="bankAccType" class="form-control" required>
                  <option value="">--Select Account Type--</option>
                  <option value="Saving Account" '; if($state['bank_account_type'] == "Saving Account") echo 'selected="selected"'; echo'>Saving Account</option>
                  <option value="Current Account"'; if($state['bank_account_type'] == "Current Account") echo 'selected="selected"'; echo'>Current Account</option>
                </select>
              </div>
              
              <div class="col-lg-4 col-md-4 col-sm-12">
                <label>Commission<span style="color:red;">*</span></label>';
                $q = $dbh->prepare('SELECT * FROM bbo_commission WHERE institution_id=:iid ORDER BY bro_comm_id ASC');
                $q->bindParam(':iid',$institution_id);
                $q->execute();
                $options = '';
                while ($res = $q->fetch()) {
                  $selected = '';
                  if ($res['bro_comm_id'] == $state['bro_comm_id']) {
                    $selected = 'selected';
                  }
                  $options .= '<option value="'.$res['bro_comm_id'].'" '.$selected.'>'.$res['commission_name'].'</option>';
                }
                echo'<select name="commis" id="commis" class="form-control" required> '.$options.' </select>
              </div>
               <div class="col-lg-12 col-md-12 col-sm-12">
                <label>Address<span style="color:red;">*</span></label>
                <input type="text" class="form-control" name="add" id="add" value="'.$state['address'].'" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="edit_cli" id="edit_cli" value="'.$state['client_id'].'"><i class="fa fa-wrench" aria-hidden="true"></i> Update</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i> Close</button>
          </div>
        </div>
      </form>
    </div>';
    echo'
    <script type="text/javascript">
      $("#edit_cli").click(function(event) {
        event.preventDefault();
        showLoading();
        if (confirm("Are you sure you want to update ?")) { 
          var clientId = $("#edit_cli").val();
          var atype = $("#atype").val();
          var fn = $("#fn").val();
          var ln = $("#ln").val();
          var nat = $("#nat").val();
          var dz = $("#dz").val();
          var gewog_id = $("#gewog_id").val();
          var village_id = $("#village_id").val();
          var tpn = $("#tpn").val();
          var phone = $("#phone").val();
          var email = $("#email").val();
          var bank = $("#bank").val();
          var accno = $("#accno").val();
          var commis = $("#commis").val();
          var add = $("#add").val();
          var liceseNo = $("#licenseNo").val();
          var cid = $("#id").val();
          var occupation = $("#occupation").val();
          var title = $("#title").val();
          var bank_acc_type = $("#bankAccType").val();
  
          var operation = "edit_cli";
          var dataString = 
          "edit_cli=" + encodeURIComponent(operation) +
          "&atype=" + encodeURIComponent(atype) +
          "&fn=" + encodeURIComponent(fn) +
          "&ln=" + encodeURIComponent(ln) +
          "&nat=" + encodeURIComponent(nat) +
          "&bank=" + encodeURIComponent(bank) +
          "&accno=" + encodeURIComponent(accno) +
          "&dz=" + encodeURIComponent(dz) +
          "&village_id=" + encodeURIComponent(village_id) +
          "&gewog_id=" + encodeURIComponent(gewog_id) +
          "&tpn=" + encodeURIComponent(tpn) +
          "&phone=" + encodeURIComponent(phone) +
          "&email=" + encodeURIComponent(email) +
          "&commis=" + encodeURIComponent(commis) +
          "&add=" + encodeURIComponent(add) +
          "&client_id=" + encodeURIComponent(clientId) +
          "&licenseNo=" + encodeURIComponent(liceseNo) +
          "&cid_no=" + encodeURIComponent(cid) +
          "&occupation=" + encodeURIComponent(occupation) +
          "&title=" + encodeURIComponent(title) +
          "&bankAccType=" + encodeURIComponent(bank_acc_type);
  
          $("#myModal").modal("hide");
          
          $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: dataString ,
            dataType: "html",
            success: function(response) {
              hideloading();
              $("#message").html(response);
              showMessage();
            }
          });
         } else {
            hideloading();
            return false;
         }
      });
    </script>';
    die();
  }
else if(!empty($_POST["edit_fin"])) {
    $wc= $dbh->prepare("SELECT * from bbo_finance where finance_id=:id");
    $wc->bindParam(':id',$_POST['edit_fin']);
    $wc->execute();
    $state=$wc->fetch();
    if($state['amount']<0){ $amt=$state['amount']*-1;}else{$amt=$state['amount'];}
    echo'<div class="modal-dialog">
      <form action="../PROCESS/process.php" method="POST">
      <div class="modal-content">
        <div class="modal-header"><p class="statusMsg"></p>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit </h4>
        </div>
        <div class="modal-body">
        <div class="box-body">
              <div class="row">
              <div class="col-xs-6">
                  <label>Account</label>
                  <input type="text" class="form-control"  value="'.$state['acc_code'].'" disabled>
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
else if(!empty($_POST["edit_val"])) 
{
    $wc= $dbh->prepare("SELECT * from bbo_vault where vault_id=:id");
    $wc->bindParam(':id',$_POST['edit_val']);
    $wc->execute();
    $state=$wc->fetch();
    echo'<div class="modal-dialog">
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
                  <input type="text" class="form-control"  value="'.$state['acc_code'].'" disabled>
                </div>
                <div class="col-xs-6">
                  <label>Symbol</label>
                  <input type="text" class="form-control" name="sy" id="sy" value="'.$state['symbol'].'" readonly>
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
else if(!empty($_POST["edit_link_user"])) 
{
    $wc= $dbh->prepare("SELECT * FROM linkuser WHERE id = :id");
    $wc->bindParam(':id',$_POST['edit_link_user']);
    $wc->execute();
    $state=$wc->fetch();
    echo'<div class="modal-dialog">
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
                echo'<option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>';
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
                echo '<option value="'.$res['username'].'">'.$res['username'].'</option>';
                }
                echo'</select>
              </div>
            </div>
          </div>
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
else if(!empty($_POST["edit_symbol"])) {
    $wc= $dbh->prepare("SELECT * FROM symbol WHERE symbol_id = :id");
    $wc->bindParam(':id',$_POST['edit_symbol']);
    $wc->execute();
    $state=$wc->fetch();
    echo'<div class="modal-dialog">
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
                  <label for="exampleInputEmail1">ISIN</label>
                  <input type="number" class="form-control" name="isin" id="isin" required value="'.$state['isin'].'">
                </div>
                <div class="col-xs-4">
                  <label for="exampleInputEmail1">Symbol</label>
                  <input type="text" class="form-control" name="sy" id="sy" readonly value="'.$state['symbol'].'">
                </div>
                <div class="col-xs-4">
                  <label>Sector</label>
                  <select class="form-control" name="sector" id="sector">
                  <option selected value="'.$state['sector'].'">'.$state['sector'].'</option> 
                  <option value="Manufacturing">Manufacturing</option>
                    <option value="Mining">Mining</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Banking">Banking</option>
                    <option value="Technology">Technology</option>
                    <option value="Construction">Construction</option>
                  </select>
                </div>
                <div class="col-xs-12">
                  <label for="exampleInputEmail1">Company Name</label>
                  <input type="text" class="form-control" name="name" id="name" required value="'.$state['name'].'">
                </div>
                
                <div class="col-xs-3">
                  <label for="exampleInputEmail1">Face Value</label>
                  <input type="number" min="1" class="form-control" name="fv" id="fv" value="'.$state['face_value'].'" required>
                </div>
                <div class="col-xs-3">
                  <label for="exampleInputEmail1">Premium Value</label>
                  <input type="number" min="1" class="form-control" name="pv" id="pv" value="'.$state['premium_value'].'">
                </div>
                <div class="col-xs-3">
                  <label for="exampleInputEmail1">Board Lot</label>
                  <input type="number" min="1" class="form-control" name="bl" id="bl" required value="'.$state['board_lot'].'">
                </div>
                <div class="col-xs-3">
                  <label for="exampleInputEmail1">Paid up Shares</label>
                  <input type="number" min="1" class="form-control" name="pus" id="pus" value="'.$state['paid_up_shares'].'">
                </div>
                <div class="col-xs-3">
                  <label for="exampleInputEmail1">Year of Est.</label>
                  <input type="text"  class="form-control" name="yoe" id="yoe" value="'.$state['year_of_est'].'">
                </div>
                <div class="col-xs-3">
                  <label for="exampleInputEmail1">Year of Listing</label>
                  <input type="text" min="1" class="form-control" name="yol" id="yol" value="'.$state['year_of_listing'].'">
                </div>

                <div class="col-xs-6">
                  <label>Security Type</label>
                  <select class="form-control" name="stype" id="stype">';
                  if($state['security_type'] == 'OS'){$st='Ordinary Shares';}elseif($state['security_type'] == 'CB'){$st='Corporate Bonds';}elseif($state['security_type'] == 'GB'){$st='Government Bonds';}
               echo'<option selected value="'.$state['security_type'].'">'.$st.'</option> 
                    <option value="OS">Ordinary Shares</option>
                    <option value="CB">Corporate Bonds</option>
                    <option value="GB">Government Bonds</option>
                  </select>
                </div>
                <div class="col-xs-3">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status">';
                  if($state['status'] == 1){$status='Active';}elseif($state['status'] == 2){$status='InActive';}
               echo'<option selected value="'.$state['status'].'">'.$status.'</option>  
                    <option value="1">Active</option>
                    <option value="2">InActive</option>
                  </select>
                </div>
              </div>
        </div>
        <div class="box-footer">
          <div class="col-xs-4">
                <button type="submit" class="btn btn-primary" name="edit_symbol" id="edit_symbol"  value="'.$state['symbol_id'].'">UPDATE</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      </form>
    </div>';
} else if(!empty($_POST['id']) && !empty($_POST['c_name']) && !empty($_POST['c_rate'])) {
  $comm_id = $_POST['id'];
  $comm_name = $_POST['c_name'];
  $comm_rate = $_POST['c_rate'];

  try{
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("UPDATE bbo_commission SET commission_name=:comm_name,rate=:comm_rate where bro_comm_id=:id");
    $save->bindParam(':comm_name',$comm_name);
    $save->bindParam(':comm_rate',$comm_rate);
    $save->bindParam(':id',$comm_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    echo'
    <div class="row">
      <div class="col-lg-12 col-xs-12">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Record Updated Successfully
        </div>
      </div>';
    die();
  } catch(PDOException $e) {
    $dbh->rollBack();
    echo'
    <div class="row">
      <div class="col-lg-12 col-xs-12">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Oops Sorry! There was an error while operation.
        </div>
      </div>';
    die();
  }
} elseif(!empty("delete_commission")) {
  $commission_id = $_POST['commission_id'];
  try{
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $save = $dbh->prepare("DELETE FROM bbo_commission WHERE bro_comm_id=:id");
    $save->bindParam(':id',$commission_id);
    $save->execute();

    $dbh->commit();
    $dbh = null;
    header("location: commission.php?msg=1");
    die();
  } catch(PDOException $e) {
    $dbh->rollBack();
    header("location: commission.php?msg=2");
    die();
  }
} else {
  echo'
  <div class="row">
    <div class="col-lg-12 col-xs-12">
      <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Method / function Not Matched
      </div>
    </div>';
  die();
}
?>
<script type="text/javascript">
$(document).ready(function(){
$("#edit_fin").click(function(){

   var financeId = $("#edit_fin").val();
     if (confirm("Are you sure you want to update fincane Id # "+ financeId + '?'))
     {
          var flag = $("#flag").val();
          var amt = $("#amt").val();
          var rm = $("#rm").val();
          var operation = "edit_fin";
          var dataString = 'flag='+ flag +'&amt='+ amt + '&rm='+ rm + '&financeId='+ financeId + '&edit_fin='+ operation;
          $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: dataString ,
          success: function(data){
          $('.statusMsg').html(data);
          $('.statusMsg').fadeOut(5000);
          }
          });
     }
     else
     {
         return false;
     }
});
});
</script>
<script type="text/javascript">
 function fun(io) 
 {
            var val= document.getElementById('delete_nom'+io).value;
             if (confirm("Are you sure you want to delete nominee Id # "+ val + '?'))
             {
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

