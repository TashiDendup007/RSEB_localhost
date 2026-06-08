<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
if(!empty($_POST["edit_cli"]))
{
    $wc= $dbh->prepare("SELECT a.*,b.DzongkhagName,b.DzongkhagID,c.bank_name,c.bank_id from bbo_account a, tbldzongkhag b,banks c where  a.DzongkhagID=b.DzongkhagID and a.bank_id=c.bank_id and bbo_client_id=:id");
    $wc->bindParam(':id',$_POST['edit_cli']);
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
                  <label>Account Code</label>
                  <input type="text" class="form-control" name="cdcode" id="cdcode" maxlength="10"  value="'.$state['acc_code'].'" disabled>
                </div>
                 <div class="col-xs-4">
                  <label>First Name</label>
                  <input type="text" class="form-control" name="fn" id="fn" value="'.$state['f_name'].'" required>
                </div>
                <div class="col-xs-4">
                 <label>Last Name</label>
                  <input type="text" class="form-control" name="ln" id="ln" value="'.$state['l_name'].'">
                </div>
                <div class="col-xs-4">
                 <label>Nationality</label>
                  <input type="text" class="form-control" name="nat" id="nat" value="'.$state['nationality'].'" required>
                </div>
                <div class="col-xs-4">
                  <label>CID</label>
                  <input type="text" class="form-control" name="id" id="id" value="'.$state['ID'].'" disabled>
                </div>
                <div class="col-xs-4">
                  <label>Dzongkhag</label>';
                  $q=$dbh->prepare('SELECT * from tbldzongkhag');
                  $q->execute();
                  echo'<select id="dz" name="dz" class="form-control">';
                  echo '<option selected value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
                  foreach($q as $st)
                  {
                    echo '<option value="'.$st['DzongkhagID'].'">'.$st['DzongkhagName'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-4">
                  <label>TPN</label>
                  <input type="text" class="form-control" name="tpn" id="tpn" value="'.$state['tpn'].'" required>
                </div>
                <div class="col-xs-4">
                  <label>Phone No</label>
                  <input type="text" class="form-control" name="phone" value="'.$state['phone'].'" id="phone" >
                </div>
                <div class="col-xs-4">
                  <label>email</label>
                  <input type="text" class="form-control" name="email" value="'.$state['email'].'" id="email" >
                </div>
                <div class="col-xs-4">
                  <label>Bank Name</label>';
                  $q=$dbh->prepare('SELECT * from banks');
                  $q->execute();
                  echo'<select id="bank" name="bank" class="form-control">';
                  echo '<option selected value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
                  foreach($q as $st)
                  {
                    echo '<option value="'.$st['bank_id'].'">'.$st['bank_name'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-4">
                  <label>Account Number</label>
                  <input type="number" class="form-control" name="accno" id="accno" value="'.$state['bank_account'].'">
                </div>
                <div class="col-xs-4">
                  <label>Commission %</label>
                  <input type="number" class="form-control" name="commis" id="commis" value="'.$state['brokerage_commission'].'" step="any" min="1" max="100">
                </div>
                <div class="col-xs-8">
                  <label>Address</label>
                  <input type="text" class="form-control" name="add" id="add" value="'.$state['address'].'" >
                </div>
            </div>
        </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <div class="col-xs-4">
                <button type="submit" class="btn btn-primary" name="edit_cli" id="edit_cli"  value="'.$state['bbo_client_id'].'">UPDATE</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      </form>
    </div>';
}
if(!empty($_POST["edit_fin"]))
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
if(!empty($_POST["edit_val"]))
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
if(!empty($_POST["edit_link_user"]))
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
if(!empty($_POST["edit_symbol"]))
{
    $wc= $dbh->prepare("SELECT * FROM symbol WHERE symbol_id = :id");
    $wc->bindParam(':id',$_POST['edit_symbol']);
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
        <!-- /.box-body -->
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
}
?>
