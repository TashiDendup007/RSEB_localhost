<?php
  include ('../../CONNECTIONS/db.php');
  if(!empty($_POST["edit_user"])) {
    $wc= $dbh->prepare("SELECT 
        a.role_id, a.cid, a.name, a.phone, a.email, a.status, a.username, a.address, a.user_id, b.participant_code 
      FROM users a
      JOIN adm_participants b ON a.participant_code=b.participant_code where a.user_id=:id ");
    $wc->bindParam(':id',$_POST['edit_user']);
    $wc->execute();
    $state=$wc->fetch();
    echo'
    <div class="modal-dialog">
      <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit User</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="row" ng-app="">
              <div class="col-lg-8 col-md-8">
                <label for="name">First/Last Name</label>
                <input type="text" class="form-control" name="name" id="name" value="'.$state['name'].'" required>
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Role</label>
                <select class="form-control" name="role" id="role" disabled>
                  <option value="1" '; if($state['role_id'] == 1) echo 'selected="selected"'; echo'>Administrator</option>
                  <option value="2" '; if($state['role_id'] == 2) echo 'selected="selected"'; echo'>BackOffice & Order Mgmt.</option>
                  <option value="3" '; if($state['role_id'] == 3) echo 'selected="selected"'; echo'>CDS & CSS</option>
                  <option value="4" '; if($state['role_id'] == 4) echo 'selected="selected"'; echo'>Client Terminal</option>
                  <option value="5" '; if($state['role_id'] == 5) echo 'selected="selected"'; echo'>IPO</option>
                  <option value="6" '; if($state['role_id'] == 6) echo 'selected="selected"'; echo'>PTRS</option>
                  <option value="7" '; if($state['role_id'] == 7) echo 'selected="selected"'; echo'>Custodial</option>
                  <option value="8" '; if($state['role_id'] == 8) echo 'selected="selected"'; echo'>Non-Broker</option>
                </select>
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Participants</label>';
                $wc= $dbh->prepare("SELECT * FROM adm_participants");
                $wc->execute();
                echo '<select name="pcode" id="pcode"  class="form-control" disabled>';
                 echo '<option selected value="'.$state['participant_code'].'">'. $state['participant_code'].'</option>';
                 while($res= $wc->fetch())
                {
                echo'<option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>';
                }
                echo'</select>
                </div>
               <div class="col-lg-4 col-md-4">
                <label for="cid">CID</label>
                <input type="number" ng-model="cid" class="form-control" name="cid" id="cid" maxlength="11" value="'.$state['cid'].'" readonly>
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" id="email"  value="'.$state['email'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="phone">Phone</label>
                <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length==8) return false;" value="'.$state['phone'].'" >
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Status</label>
                <select class="form-control" name="status" id="status">
                  <option value="1" '; if($state['status'] == 1) echo 'selected="selected"'; echo'>Active</option>
                  <option value="0" '; if($state['status'] == 0) echo 'selected="selected"'; echo'>InActive</option>
                </select>
              </div>
              <div class="col-lg-4  col-md-4">
                <label for="un">Username</label>
                <input type="text" class="form-control" name="un" id="un" value="'.$state['username'].'" readonly>
              </div>
              <div class="col-lg-12">
                <label for="add">Address</label>
                <input type="text" class="form-control" name="add" id="add" value="'.$state['address'].'" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" name="edit_user" id="edit_user" value="'.$state['user_id'].'"><i class="fa fa-check"></i> Update</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          </div>
        </div>
      </form>
    </div>';
}
if (!empty($_POST["edit_part"])) { 
    $wc = $dbh->prepare("SELECT a.participant_id, a.participant_type, a.participant_code, a.contact_person, a.phone, a.email, a.clearing_account, a.status, a.address, b.name, b.institution_id 
      FROM adm_participants a 
      JOIN adm_institution b ON a.institution_id=b.institution_id
      WHERE a.participant_id = :id");
    $wc->bindParam(':id', $_POST['edit_part']);
    $wc->execute();
    $state=$wc->fetch();
    echo'
    <div class="modal-dialog">
      <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Participant</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="row"  ng-app="">
              <div class="col-lg-4 col-md-4">
                <label>Participant Type</label>
                <select class="form-control" ng-model="cn" name="Type" id="Type">
                 <option selected value="'.$state['participant_type'].'">'.$state['participant_type'].'</option>
                  <option value="MEMBER">MEMBER</option>
                  <option value="COMPANIES">COMPANIES</option>
                  <option value="GOVT">GOVT</option>
                  <option value="EMPLOYEE">EMPLOYEE</option>
                </select>
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="exampleInputEmail1">Participant Code</label>
                <input type="text" class="form-control" name="Pcode" id="Pcode"  readonly="" value="'.$state['participant_code'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Institution Name</label>';
                  $wc = $dbh->prepare("SELECT institution_id, name FROM adm_institution");
                  $wc->execute();
                  $options = '';
                  while ($res = $wc->fetch()) {
                    $selected = '';
                    if ($res['institution_id'] == $state['institution_id']) {
                      $selected = 'selected';
                    }
                    $options .= '<option value="'.$res['institution_id'].'" '.$selected.'>'.$res['name'].'</option>';
                  }
              echo'<select name="Ins" id="Ins" class="form-control"> '.$options.' </select>
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="cp">Contact Person</label>
                <input type="text" class="form-control" name="cp" id="cp" value="'.$state['contact_person'].'" required>
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="phone">Mob/Tel Phone</label>
                <input type="number" class="form-control" ng-model="phone" value="'.$state['phone'].'" name="phone" id="phone" onKeyPress="if(this.value.length==8) return false;" required>
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="'.$state['email'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="ca">Clearing Account</label>
                <input type="text" class="form-control" name="ca" id="ca" value="'.$state['clearing_account'].'" required>
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="status">Status</label>
                <select class="form-control" name="status" id="status">
                  <option value="1" ('.$state['status'].' == 1) ? "selected" : ""; >Active</option>
                  <option value="0" ('.$state['status'].' == 0) ? "selected" : ""; >Inactive</option>
                </select>
              </div>
              <div class="col-lg-12 col-md-12">
                <label for="add">Address</label>
                <input type="text" class="form-control" name="add" id="add" value="'.$state['address'].'" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" name="edit_participant" id="edit_participant" value="'.$state['participant_id'].'"><i class="fa fa-check"></i> Update</button>
            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
          </div>
        </div>
      </form>
    </div>';
}
if (!empty($_POST["edit_inst"])) {

  $wc= $dbh->prepare("SELECT institution_id, name, contact_person, phone, address, gst_register FROM adm_institution WHERE institution_id = :id");
  $wc->bindParam(':id', $_POST['edit_inst']);
  $wc->execute();
  $state = $wc->fetch();

  $gst_value = ($state['gst_register'] == 'Y') ? 'Yes' : 'No';
  
  echo'
  <div class="modal-dialog">
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Institute</h4>
        </div>
        <div class="modal-body">
        <div class="box-body">
          <div class="row">
            <div class="col-lg-12 col-md-12">
              <label for="ins_name">Institution Name</label>
              <input type="text" class="form-control" name="ins_name" id="ins_name" value="'.$state['name'].'" required>
            </div>

            <div class="col-lg-12 col-md-12">
              <label for="gst_register">GST Registered?</label>
              <select class="form-control" name="gst_register" id="gst_register" required>
                <option value="' . $state['gst_register'] . '">' . $gst_value. '</option>
                <option value="Y" ('.$state['gst_register'].' == "YES") ? "selected" : "";> Yes </option>
                <option value="N" ('.$state['gst_register'].' == "NO") ? "selected" : "";> No </option>
              </select>
            </div>

            <div class="col-lg-12 col-md-12">
              <label for="address">Address </label>
              <input type="text" class="form-control" name="address" id="address" value="'.$state['address'].'" required>
            </div>

          </div>  
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" name="edit_inst" id="edit_inst"  value="'.$state['institution_id'].'"><i class="fa fa-check"></i> Update</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
      </div>
    </form>
  </div>';
}
if (!empty($_POST["edit_link_user"])) {
  $wc = $dbh->prepare("SELECT * FROM linkuser WHERE id = :id");
  $wc->bindParam(':id', $_POST['edit_link_user']);
  $wc->execute();
  $state = $wc->fetch();
  echo'
  <div class="modal-dialog">
    <form action="../PROCESS/process.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit </h4>
        </div>
        <div class="modal-body"><div id="loadingover" style="display: none;"><div id="loadingmsg" style="display: none;"></div></div>
        <div class="box-body">
          <div class="row">
            <div class="col-lg-4">
              <label>Participant Code</label>';
                $wc = $dbh->prepare("SELECT participant_id, participant_code FROM adm_participants");
                $wc->execute();
                echo'
                <select name="pcode" id="pcode"  class="form-control" disabled>
                <option selected  value="'.$state['participant_code'].'">'. $state['participant_code'].'</option>';
                while($res= $wc->fetch(PDO::FETCH_ASSOC)) {
                  echo'<option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>';
                }
                echo'</select>
            </div>
            <div class="col-lg-4">
              <label>Client Account</label>';
                $wc = $dbh->prepare("select username from users where role_id=4");
                $wc->execute();
                echo'
                <select name="ct" id="ct"  class="form-control">
                <option selected value="'.$state['client_code'].'">'. $state['client_code'].'</option>';
                while ($res = $wc->fetch()) {
                  echo'<option value="'.$res['username'].'">'.$res['username'].'</option>';
                }
                echo'</select>
            </div>
          </div>
        </div>
        <div class="box-footer">
          <div class="col-lg-4">
                <button type="button" class="btn btn-primary" name="edit_linkuser" id="edit_linkuser"  value="'.$state['id'].'">UPDATE</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>';
}
if (!empty($_POST["edit_symbols"])) {
  $wc= $dbh->prepare("SELECT * FROM symbol WHERE symbol_id = :id");
  $wc->bindParam(':id',$_POST['edit_symbols']);
  $wc->execute();
  $state=$wc->fetch();
  echo'
  <div class="modal-dialog modal-lg">
    <form action="" method="POST">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Symbol</h4>
      </div>
      <div class="modal-body">
        <div class="box-body">
          <div class="row">
            <div class="col-lg-4 col-md-4">
              <label for="isin">ISIN</label>
              <input type="number" class="form-control" onKeyPress="if(this.value.length==11) return false;" name="isin" id="isin" required value="'.$state['isin'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="sy">Symbol</label>
              <input type="text" class="form-control" name="sy" id="sy" readonly value="'.$state['symbol'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label>Sector</label>';
                $wc = $dbh->prepare("SELECT m.id, m.name FROM sector_masters m WHERE m.status ORDER BY m.name ASC");
                $wc->execute();
                $options = '';
                while ($res = $wc->fetch()) {
                  $selected = '';
                  if (strcasecmp($res['name'], $state['sector']) == 0) {
                    $selected = 'selected';
                  }
                  $options .= '<option value="'.$res['name'].'" '.$selected.'>'.$res['name'].'</option>';
                }
              echo'<select name="sector" id="sector" class="form-control"> '.$options.' </select>
            </div>
            <div class="col-lg-12 col-md-12">
              <label for="name">Company Name</label>
              <input type="text" class="form-control" name="name" id="name" required value="'.$state['name'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="fv">Face Value</label>
              <input type="number" min="1" class="form-control" name="fv" id="fv" value="'.$state['face_value'].'" required>
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="pv">Premium Value</label>
              <input type="number" min="1" class="form-control" name="pv" id="pv" value="'.$state['premium_value'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="bl">Board Lot</label>
              <input type="number" min="1" class="form-control" name="bl" id="bl" required value="'.$state['board_lot'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="pus">Paid up Shares</label>
              <input type="number" min="1" class="form-control" name="pus" id="pus" value="'.$state['paid_up_shares'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="doe">Year of Est.</label>
              <input type="date"  class="form-control" name="doe" id="doe" value="'.$state['date_of_est'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label for="dol">Year of Listing</label>
              <input type="date"  class="form-control" name="dol" id="dol" value="'.$state['date_of_listing'].'">
            </div>
            <div class="col-lg-4 col-md-4">
              <label>Security Type</label>
              <select class="form-control" name="stype" id="stype">
                <option value="OS" '; if($state['security_type']=='OS') echo 'selected="selected"'; echo'>Ordinary Shares</option>
                <option value="CB" '; if($state['security_type']=='CB') echo 'selected="selected"'; echo'>Corporate Bonds</option>
                <option value="GB" '; if($state['security_type']=='GB') echo 'selected="selected"'; echo'>Government Bonds</option>
              </select>
            </div>';
            if ($state['security_type']=='GB' || $state['security_type']=='CB' || $state['security_type']=='CP') {
              echo'
              <div class="col-lg-4 col-md-4">
                <label for="matPeriod">Maturity Period</label>
                <input type="number" class="form-control" name="matPeriod" id="matPeriod" value="'.$state['maturity_period'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="matDate">Maturity Date</label>
                <input type="date" class="form-control" name="matDate" id="matDate" value="'.$state['maturity_date'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="issueDate">Date of Issue</label>
                <input type="date" class="form-control" name="issueDate" id="issueDate" value="'.$state['date_of_issue'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="couponRate">Coupon Rate</label>
                <input type="number" class="form-control" name="couponRate" id="couponRate" value="'.$state['coupon_rates'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label for="couponPayable">Coupon Payable</label>
                <select class="form-control" name="couponPayable" id="couponPayable">
                  <option value="1" '; if($state['coupon_payable']==1) echo 'selected="selected"'; echo'>Annually</option>
                  <option value="2" '; if($state['coupon_payable']==2) echo 'selected="selected"'; echo'>Semi-annually</option>
                  <option value="3" '; if($state['coupon_payable']==3) echo 'selected="selected"'; echo'>Quarterly</option>
                </select>
              </div>';
            } else {
              echo'
              <input type="hidden" class="form-control" name="matPeriod" id="matPeriod" value="0">
              <input type="hidden" class="form-control" name="matDate" id="matDate" value="0000-00-00">
              <input type="hidden" class="form-control" name="issueDate" id="issueDate" value="0000-00-00">
              <input type="hidden" class="form-control" name="couponRate" id="couponRate" value="0">
              <input type="hidden" class="form-control" name="couponPayable" id="couponPayable" value="0">
              ';
            }
            echo'
            <div class="col-lg-4 col-md-4">
              <label>Status</label>
              <select class="form-control" name="status" id="status">
                <option value="1" '; if($state['status']==1) echo 'selected="selected"'; echo'>Active</option>
                <option value="2" '; if($state['status']==2) echo 'selected="selected"'; echo'>InActive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" name="edit_sym" id="edit_sym" value="'.$state['symbol_id'].'"><i class="fa fa-check"></i> Update</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
      </div>
    </form>
  </div>';
}
if (!empty($_POST["edit_assign_broker"])) {
  $id = $_POST['edit_assign_broker'];
  $wc = $dbh->prepare("SELECT * FROM assign_broker WHERE id = :id");
  $wc->bindParam(':id', $id);
  $wc->execute();
  $state = $wc->fetch();
  echo'
  <div class="modal-dialog modal-lg">
    <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Assign Broker</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="row">
              <div class="col-lg-4 col-md-4">
                <label>Participant</label>';
                  $pc_list = $dbh->prepare("SELECT DISTINCT participant_code FROM users WHERE username LIKE 'MEM%'");
                  $pc_list->execute();
                  $options = '';
                  while ($res = $pc_list->fetch()) {
                    $selected = '';
                    if (strcasecmp($res['participant_code'], $state['participant_code']) == 0) {
                      $selected = 'selected';
                    }
                    $options .= '<option value="'.$res['participant_code'].'" '.$selected.'>'.$res['participant_code'].'</option>';
                  }
                  echo'<select name="participant" id="participant" class="form-control"> '.$options.' </select>
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Broker</label>';
                  $broker = $dbh->prepare("SELECT username FROM users WHERE participant_code=:pcode AND role_id=2");
                  $broker->bindParam(':pcode', $state['participant_code']);
                  $broker->execute();
                  $options = '';
                  while ($res = $broker->fetch()) {
                    $selected = '';
                    if (strcasecmp($res['username'], $state['username']) == 0) {
                      $selected = 'selected';
                    }
                    $options .= '<option value="'.$res['username'].'" '.$selected.'>'.$res['username'].'</option>';
                  }
                  echo'<select name="broker" id="broker" class="form-control"> '.$options.' </select>
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Type</label>
                <select class="form-control" name="type" id="type">
                  <option value="IPO" '; if($state['type'] == 'IPO') echo 'selected="selected"'; echo'> IPO </option>
                  <option value="RIGHTS" '; if($state['type'] == 'RIGHTS') echo 'selected="selected"'; echo'> RIGHTS </option>
                  <option value="BOND" '; if($state['type'] == 'BOND') echo 'selected="selected"'; echo'> BOND </option>
                </select>
              </div> 
               <div class="col-lg-4 col-md-4">
                <label>Symbol</label>';
                $symbols = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE status = :status ORDER BY symbol ASC");
                $symbols->bindValue(':status', 1, PDO::PARAM_INT);
                $symbols->execute();
                $options = array();
                while ($res = $symbols->fetch()) {
                    $selected = '';
                    if ($res['symbol_id'] == $state['symbol']) {
                        $selected = 'selected';
                    }
                    $options[] = '<option value="'.$res['symbol_id'].'" '.$selected.'>'.$res['symbol'].'</option>';
                }
                echo'<select name="symbol" id="symbol" class="form-control">'.implode('', $options).'</select>
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Rate</label>
                <input type="text" class="form-control" id="rate" name="rate" value="'.$state['rate'].'">
              </div>
              <div class="col-lg-4 col-md-4">
                <label>Status</label>
                <select class="form-control" name="status" id="status">
                  <option value="1" '; if($state['status'] == 1) echo 'selected="selected"'; echo'> Active </option>
                  <option value="0" '; if($state['status'] == 0) echo 'selected="selected"'; echo'> InActive </option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" name="edit_assignBroker" id="edit_assignBroker" value="'.$state['id'].'"><i class="fa fa-check"></i> Update</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
      </div>
    </form>
  </div>';
} 
?>
<script type="text/javascript"> 
  $("#edit_assignBroker").click(function() {
    showLoading();
    var assign_id = $("#edit_assignBroker").val();
    if (confirm("Are you sure you want to update Id # "+ assign_id + '?')) { 
      var $participantField = $("#participant");
      var $brokerField = $("#broker");
      var $typeField = $("#type");
      var $symbolField = $("#symbol");
      var $rateField = $("#rate");
      var $statusField = $("#status");
      var operation = "edit_assignBroker";

      var data = {
        participant: $participantField.val(),
        broker: $brokerField.val(),
        type: $typeField.val(),
        symbol: $symbolField.val(),
        rate: $rateField.val(),
        status: $statusField.val(),
        id: assign_id,
        edit_assignBroker: operation
      };

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/ipo_process.php", 
        data: data, 
        dataType: 'html',
        success: function(response){ 
          hideloading(); 
          $("#myModal").modal('hide');
          $("#message").html(response);
          showMessage();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log(textStatus);
        }
      });
    }else{ 
      hideloading(); 
      return false; 
    }
  });

  $("#edit_user").click(function(){
    showLoading();
    var user_id = $("#edit_user").val();
    if (confirm("Are you sure you want to update Id # "+ user_id + '?')) { 
      var $editUserBtn = $("#edit_user");
      var $nameField = $("#name");
      var $phoneField = $("#phone");
      var $emailField = $("#email");
      var $statusField = $("#status");
      var $addressField = $("#add");

      var data = {
        name: $nameField.val(),
        phone: $phoneField.val(),
        email: $emailField.val(),
        status: $statusField.val(),
        add: $addressField.val(),
        edit_user: $editUserBtn.val(),
        edit_users: "edit_users"
      };

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/process.php", 
        data: data, 
        dataType: 'html',
        success: function(response){ 
          hideloading(); 
          $("#myModal").modal('hide');
          $("#message").html(response);
          showMessage();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log(textStatus);
        }
      });
    }else{ 
      hideloading(); 
      return false; 
    }
  });

  $("#edit_participant").click( function() { 
    var participantId = $("#edit_participant").val();
    if (confirm("Are you sure you want to update record Id # "+ participantId + '?')) {
      showLoading(); 
      var data = {
        Type: $("#Type").val(),
        Pcode: $("#Pcode").val(),
        Ins: $("#Ins").val(),
        cp: $("#cp").val(),
        phone: $("#phone").val(),
        email: $("#email").val(),
        address: $("#add").val(),
        ca: $("#ca").val(),
        status: $("#status").val(),
        participantId: participantId,
        edit_parts: "edit_parts"
      };
      $.post("../PROCESS/process.php", data, function(response) {
        hideloading(); 
        $("#myModal").modal('hide');
        $("#message").html(response);
        showMessage();
      });
    } else { 
      return false; 
    }
  });

  $("#edit_inst").click( function() {
    showLoading();
    var instId = $("#edit_inst").val(); 

    if (confirm("Are you sure you want to update record Id # "+ instId + '?')) {
      var $insti_field = $("#edit_inst");
      var $inst_name = $("#ins_name");
      var $address = $("#address");
      var $gst_register = $("#gst_register");
      var $operation = 'edit_ins'; 
      
      var data = {
          inst_id: $insti_field.val(),
          ins_name: $inst_name.val(),
          address: $address.val(),
          gst_register: $gst_register.val(),
          edit_ins: $operation,
      };

      $.ajax({ 
        type: "POST", 
        url: "../PROCESS/process.php",
        data: data,
        dataType: 'html',
        success: function(response){
          hideloading(); 
          $("#myModal").modal('hide');
          $("#message").html(response);
          showMessage();
        } 
      });
    } else {
      hideloading();
      return false;
    }
  });

  $("#edit_sym").click(function() {
    var symId = $("#edit_sym").val();
    if (confirm("Are you sure you want to update record Id # "+ symId + '?')) {
      showLoading();
      var isin = $("#isin").val();
      var symbol = $("#sy").val();
      var name = $("#name").val();
      var sector = $("#sector").val();
      var faceValue = $("#fv").val();
      var premiumValue = $("#pv").val();
      var boardLot = $("#bl").val();
      var paidUpShares = $("#pus").val();
      var dateOfEst = $("#doe").val();
      var dateOfList = $("#dol").val();
      var securityType = $("#stype").val();
      var status = $("#status").val();
      var matPeriod = $("#matPeriod").val();
      var matDate = $("#matDate").val();
      var issueDate = $("#issueDate").val();
      var cpnRate = $("#couponRate").val();
      var cpnPayable = $("#couponPayable").val();

      if(dateOfEst==""){
        dateOfEst="0000-00-00";
      }
      if(dateOfList==""){
        dateOfList="0000-00-00";
      }
      var operation = "edit_symbol";

      var data = {
        isin: isin,
        sy: symbol,
        name: name,
        sector: sector,
        fv: faceValue,
        pv: premiumValue,
        bl: boardLot,
        pus: paidUpShares,
        doe: dateOfEst,
        dol: dateOfList,
        stype: securityType,
        status: status,
        symId: symId,
        matPeriod: matPeriod,
        matDate: matDate,
        issueDate: issueDate,
        cpnRate: cpnRate,
        cpnPayable: cpnPayable,
        edit_symbol: operation,
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data ,
        dataType: 'html',
        success: function(response) {
          hideloading();
          $("#myModal").modal('hide');
          $("#message").html(response);
          showMessage();
        }
      });
     } else {
         return false;
     }
  });

  $("#edit_linkuser").click(function() {
    var val = document.getElementById('edit_linkuser'+io).value;
    if (confirm("Are you sure you want to update record Id # "+ val + '?')) {
      var operation = "edit_linkuser";
      var dataString = 'edit_linkusr='+ val +'&edit_linkuser='+ operation;
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString ,
        success: function(response){
          $('#message').html(response);
          showMessage();
        }
      });
    } else {
       return false;
    }

    var pcode = $("#pcode").val();
    var clAccount = $("#ct").val();
    var luId = $("#edit_linkuser").val();
    var operation = "save_symbol";
    var dataString = 'isin='+ isin +'&sy='+ symbol + '&name='+ name +'&sector='+ sector + '&fv='+ faceValue + '&pv='+ premiumValue +
                   '&bl='+ boardLot + '&pus='+ paidUpShares + '&doe='+ dateOfEst + '&dol='+ dateOfList +
                   '&stype='+ securityType + '&status='+ status + '&symId='+ symId + '&save_symbol='+ operation;
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString ,
      success: function(data) {
        $('#message').html(response);
        showMessage();
      }
    });
  });
</script>
