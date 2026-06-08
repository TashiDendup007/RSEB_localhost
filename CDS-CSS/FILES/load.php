<?php
date_default_timezone_set("Asia/Thimphu");
include ('sessionStartFile_cdscss.php');
include ('../../CONNECTIONS/db.php');

if(!empty($_POST["val"])) {
    $type = $_POST['val'];
    if ($type == 'I') {
    echo'
    <div class="col-lg-3 col-md-3">
      <label>CD Code<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="cdcode" id="cdcode" maxlength="10" style="text-transform:uppercase" onChange="getState3(this.value);" required>
    </div>
    <div id="cd1"></div> ';
    } elseif ($type == 'J') {
    echo '
    <div class="col-lg-3 col-md-3">
      <label>CD Code<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="cdCodeAC" id="cdCodeAC" style="text-transform:uppercase" maxlength="10" onChange="getState4(this.value);" required>
    </div>
    <div id="cd1"></div>';
  }
} elseif (!empty($_POST["cdCode"])) {
    $cd = $_POST['cdCode'];

    $wc = $dbh->prepare("SELECT cd_code FROM client_account WHERE cd_code = :cd");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state = $wc->fetch();
    if ($wc->rowCount() > 0) {
      echo '
      <div class="col-lg-3 col-md-3">
        <label>Message</label>
        <input style="color:red; type="text" class="form-control" value="CD Code already exists" required>
      </div>';
    } else {
      echo '
      <input type="hidden" class="form-control" name="licenseNo" id="licenseNo" value="0">
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
        <label>CID<span style="color:red;">*</span></label>
        <input type="number" class="form-control" name="id" id="id" onKeyPress="if(this.value.length==11) return false;" required>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Occupation<span style="color:red;">*</span></label>';
        $q = $dbh->prepare("SELECT * FROM occupation ORDER BY occupation_name ASC");
        $q->execute();
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        echo'
        <select id="occupation" name="occupation" class="form-control" required>
          <option value="">-- Select --</option>';
          foreach ($rows as $state) {
            echo'<option value="'.$state['occupation'].'">'.$state['occupation_name'].'</option>';
          }
        echo'</select>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
       <label>Nationality<span style="color:red;">*</span></label>
        <input type="text" class="form-control" name="nat" id="nat" required>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Dzongkhag</label>';
        $q=$dbh->prepare("SELECT * FROM tbldzongkhag ORDER BY DzongkhagName ASC");
        $q->execute();
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        echo'
        <select id="dz" name="dz" class="form-control" required>
        <option value="">-- Select --</option>';
        foreach ($rows as $state) {
          echo '<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
        }
        echo'</select>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>TPN</label>
        <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn">
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Phone No<span style="color:red;">*</span></label>
        <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length==8) return false;" required>
        <span id="errln" style="color:red;display:none;">*Phone Number should be only 8 characters</span>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Email</label>
        <input type="text" class="form-control" name="email" id="email">
        <span id="errEmail" style="color:red;display:none;">*Please use only letters and numbers</span>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Bank Name</label>';
        $q = $dbh->prepare("SELECT * FROM banks");
        $q->execute();
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        echo'<select id="bank" name="bank" class="form-control">';
        foreach($rows as $state) {
          echo '<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
        }
        echo'</select>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Account Number<span style="color:red;">*</span></label>
        <input type="number" class="form-control" name="accno" id="accno" onKeyPress="if(this.value.length==13) return false;" required>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <label>Account Type</label>
        <select id="bankAccType" name="bankAccType" class="form-control">
          <option value="">--Select Account Type--</option>
          <option value="Saving Account">Saving Account</option>
          <option value="Current Account">Current Account</option>
        </select>
      </div>
      <div class="col-lg-12">
        <label>Address</label>
        <input type="text" class="form-control" name="add" id="add" required>
      </div>
      <script type="text/javascript">
        $( function () {
          $("#occupation").select2();
          $("#dz").select2();
        });
        $("#save_client").show();
      </script>';
  }
}
elseif (!empty($_POST["cdCodeAC"])) {
  $cd = $_POST['cdCodeAC'];

  $wc = $dbh->prepare("SELECT cd_code FROM client_account WHERE cd_code=:cd");
  $wc->bindParam(':cd',$cd);
  $wc->execute();
  $state = $wc->fetch();
  if ($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Message</label>
      <input style="color:red; type="text" class="form-control" value="CD Code already exists" required>
    </div>';
  } else {
    echo '
    <input type="hidden" class="form-control" name="nat" id="nat" value="">
    <input type="hidden" class="form-control" name="title" id="title" value="">
    <input type="hidden" class="form-control" name="occupation" id="occupation" value="">
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Asso. Name<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="fn" id="fn" required>
    </div>
    <div class="col-sm-3">
      <label>Registration/License No<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="licenseNo" id="licenseNo" required>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>DISN<span style="color:red;">*</span></label>
      <input type="text" maxlength="11" class="form-control" name="id" id="id" onKeyPress="if(this.value.length==11) return false;" required>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Dzongkhag</label>';
      $q = $dbh->prepare('SELECT * from tbldzongkhag order by DzongkhagName ASC');
      $q->execute();
      $rows = $q->fetchAll(PDO::FETCH_ASSOC);
      echo'
      <select id="dz" name="dz" class="form-control" required>
      <option value="">-- Select --</option>';
      foreach ($rows as $state) {
        echo '<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
      }
      echo'</select>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>TPN<span style="color:red;">*</span></label>
      <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn" required>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Contact Person<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="ln" id="ln" required>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Phone No<span style="color:red;">*</span></label>
      <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length==8) return false;">
      <span id="errln" style="color:red;display:none;">*Phone Number should be only 8 characters</span>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Email<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="email" id="email" required>
    </div>
     <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Bank Name</label>';
      $q = $dbh->prepare('SELECT * FROM banks');
      $q->execute();
      $rows = $q->fetchAll(PDO::FETCH_ASSOC);
      echo'
      <select id="bank" name="bank" class="form-control">';
      foreach ($rows as $state) {
        echo '<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
      }
      echo'</select>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Account Number<span style="color:red;">*</span></label>
      <input type="number" class="form-control" name="accno" id="accno"  onKeyPress="if(this.value.length==13) return false;"required>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
      <label>Account Type</label>
      <select id="bankAccType" name="bankAccType" class="form-control">
        <option value="">--Select Account Type--</option>
        <option value="Saving Account">Saving Account</option>
        <option value="Current Account">Current Account</option>
      </select>
    </div>
    <div class="col-xs-12">
      <label>Address<span style="color:red;">*</span></label>
      <input type="text" class="form-control" name="add" id="add" required>
       <br>
      NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
    <br>
  </div>';
  ?>
  <script type="text/javascript">
    $(function(){
      $("#dz").select2();
    });
    $('#save_client').show();
  </script>
  <?php
  }
}

if(!empty($_POST["mat"]))
{
    $cd=$_POST['mat'];
    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID,b.name 
        FROM client_account a 
        JOIN adm_institution b ON a.institution_id= b.institution_id 
        WHERE a.cd_code=:cd
    ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state = $wc->fetch();
    if ($state) {
    echo '      
    <div class="col-lg-4 col-md-4">
      <label>Details of Client</label>
      <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
    </div>
    <div class="col-lg-4 col-md-4">
      <label>Broker</label>
      <input type="text" class="form-control" value="'.$state['name'].'" readonly >
    </div>
    <div class="col-lg-4 col-md-4">
      <label>Symbol</label>
      <select name="sy" id="sy"  class="form-control" onChange="loadsymbol(this.value);">
      <option value=""> Select Symbol </option>';
      $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol");
      $wc->execute();
      while ($res= $wc->fetch()) {
        echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
      }
      echo'</select>
    </div>
    <div  id="vol_avl"></div>
    <div class="col-lg-4 col-md-4">
      <label>Volume</label>
      <input type="number" class="form-control" name="hol" id="hol" min="1" required>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12">
      <label>Remarks</label>
      <input type="text" class="form-control" name="rm" id="rm">
    </div> ';
    } else {
      echo '    
      <div class="col-lg-4">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
      </div>';
    }
}
if(!empty($_POST["block"]))
{
    $cd=$_POST['block'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0) {
      echo '
      <div class="col-lg-4 col-md-4">
        <label>Details of Client</label>
        <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
      </div>
      <div class="col-lg-4 col-md-4">
        <label>Broker</label>
        <input type="text" class="form-control" value="'.$state['name'].'" readonly >
      </div>
      <div class="col-lg-4 col-md-4">
        <label>Symbol</label>';
        $wc= $dbh->prepare("select symbol,symbol_id from symbol");
        $wc->execute();
        echo'
        <select name="sy" id="sy"  class="form-control" onChange="loadsymbol(this.value);">
        <option value=""> Select Symbol </option>';
        while($res= $wc->fetch())
        {
          echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
        }
        echo '</select>
      </div>
      <div id="block_vol_avl"></div>

      <div class="col-lg-4 col-md-4">
        <label>Volume</label>
        <input type="number" class="form-control" name="blockhol" id="blockhol" min="1" required>
      </div>
      <div class="col-lg-12">
        <label>Remarks</label>
        <input type="text" class="form-control" name="rm" id="rm">
      </div>';
    } else {
      echo '
      <div class="col-xs-4">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
      </div>';
    }
}
elseif (!empty($_POST["fro"])) {
    $cd = $_POST['fro'];

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, b.name FROM client_account a, adm_institution b where a.cd_code=:cd AND a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0) {
      echo'
      <div class="col-lg-6 col-md-6 col-sm-12">
        <label>Details of Transferer Client</label>
        <input type="text"  class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].' , '.$state['name'].'" readonly>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-12 has-success">
       <label class="control-label" for="inputSuccess">To Account</label>
        <input type="text" class="form-control" maxlength="10"  name="T_cd" id="T_cd" style="text-transform:uppercase" onChange="toAccount(this.value);" required>
        <span id="to_cdcode_error" style="color: red;"></span>
      </div>
      <div id="to"></div>

      <script type="text/javascript">
        $("#T_cd").click( function () {
          $("#to_cdcode_error").html("");
        });
      </script>';
    } else {
      echo'
      <div class="col-lg-6 col-md-6">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
      </div>';
    }
}
elseif (!empty($_POST["fro1"])) {
    $cd = $_POST['fro1'];

    $wc = $dbh->prepare("SELECT a.f_name, a.l_name, a.ID, b.name FROM client_account a, adm_institution b WHERE a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state = $wc->fetch();
    if($wc->rowCount() > 0) {
      echo'
        <div class="col-lg-6 col-md-6 col-sm-12">
          <label>Details of Pledger </label>
          <input type="text"  class="form-control" value="Mr/Mrs : '.$state['f_name'].' '.$state['l_name'].'" readonly>
        </div>';
    } else {
      echo'
      <div class="col-lg-6 col-md-6 col-sm-12">
        <label>Details of PLEDGER </label>
        <input type="text"  class="form-control" style="color:red;" value="Invalid CD Code !! Please Enter correct one." readonly>
      </div>';
    }
}
elseif(!empty($_POST["loadall"])) {
    $cc = $_POST['loadall'];
    $wc = $dbh->prepare("SELECT a.*, b.* FROM cds_pledge_contract a, client_account b WHERE a.pledge_contract=:c AND a.cd_code=b.cd_code");
    $wc->bindParam(':c',$cc);
    $wc->execute();
    $state = $wc->fetch();
    if($wc->rowCount() > 0) {
      echo'
      <div class="col-lg-6 col-md-6 col-sm-12">
        <label>Details of PLEDGER </label>
        <input type="text"  class="form-control" value="Mr/Mrs : '.$state['f_name'].' '.$state['l_name'].'" readonly>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-12">
       <label>Pledge Details</label>
        <input type="text"  class="form-control" value="'.$state['cd_code'].'/ '.$state['pledgee'].'" readonly>
        <input type="hidden" class="form-control"  name="cc" id="cc" value="'.$state['pledge_contract'].'">
        <input type="hidden" class="form-control"  name="ac" id="ac" value="'.$state['cd_code'].'">
        <input type="hidden" class="form-control"  name="pl" id="pl" value="'.$state['pledgee'].'">
      </div>';
    } else {
      echo'
      <div class="col-lg-6 col-md-6 col-sm-12">
        <label>Details of PLEDGER </label>
        <input type="text"  class="form-control" style="color:red;" value="Invalid Pledge Contract Code !! Please Enter correct one." readonly>
      </div>';
    }
}
elseif (!empty($_POST["to"])) {
    $cd = $_POST['to'];

    $wc = $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state = $wc->fetch();
    if ($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-6 col-md-6 col-sm-12">
      <label>Details of Receiver Client</label>
      <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].' , '.$state['name'].'" readonly>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
      <label>Symbol</label>';
      $wc= $dbh->prepare("SELECT symbol, symbol_id FROM symbol");
      $wc->execute();
      echo'
      <select name="sy" id="sy"  class="form-control" OnChange="selectSymbol(this.value);">
        <option value=""> Select Symbol </option>';
        while($res= $wc->fetch()) {
          echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
        }
        echo'
      </select>
    </div>
    <div id="vol_avl"></div>
    <div class="col-lg-6 col-md-6 col-sm-12">
      <label>Remarks</label>
      <input type="text" class="form-control" name="remarks" id="remarks" required>
    </div>';
    } else {
    echo'
    <div class="col-lg-4 col-md-6 col-sm-12">
      <label>Details of Receiver Client</label>
      <input type="text" class="form-control" style="color:red;" value="Invalid Client." readonly>
    </div>';
    }
}
elseif(!empty($_POST["sy"]) && !empty($_POST["acc"])) {
    $cd = $_POST['sy'];
    $acc = $_POST['acc'];

    $wc = $dbh->prepare("SELECT * from cds_holding  where symbol_id=:sy and cd_code=:acc ");
    $wc->bindParam(':sy',$cd);$wc->bindParam(':acc',$acc);
    $wc->execute();
    $state = $wc->fetch();

    // if($state['volume'] == NULL ){$state['volume']=0;}else {$state['volume'] = $state['volume'];}
    $volume = isset($state['volume']) ? $state['volume'] : 0;

    echo'
    <div class="col-lg-6 col-md-6 col-sm-12">
      <label>Available Volume</label>
      <input type="text" class="form-control" value="'.$volume.'" id="avl" readonly>
    </div>
     <div class="col-lg-6 col-md-6 col-sm-12">
     <label>Transfer Volume</label>
      <input type="number" max="'.$volume.'" class="form-control" name="trs" id="trs" required>
    </div>';
}
elseif(!empty($_POST["sy1"]) && !empty($_POST["acc1"]))
{
    $cd=$_POST['sy1'];
    $acc=$_POST['acc1'];
    $wc= $dbh->prepare("SELECT * from cds_holding  where symbol_id=:sy and cd_code=:acc ");
    $wc->bindParam(':sy',$cd);$wc->bindParam(':acc',$acc);
    $wc->execute();
    $state=$wc->fetch();
    if($state['volume'] == NULL ){$state['volume']=0;}else {$state['volume'] = $state['volume'];}
    echo '      <div class="col-lg-6 col-md-6 col-sm-12">
                  <label>Available Volume</label>
                  <input type="text" class="form-control" value="'.$state['volume'].'" id="avl1" readonly>
                </div>
                 <div class="col-lg-6 col-md-6 col-sm-12">
                 <label>Pledge Volume</label>
                  <input type="number" max="'.$state['volume'].'" class="form-control" name="trs1" id="trs1" required>
                </div>';
}
elseif (!empty($_POST["sy1"]) && !empty($_POST["acc_pl_rl"])) {
    $sy=$_POST['sy1'];
    $acc=$_POST['acc_pl_rl'];
    $cc=$_POST['cc'];

    $wc1= $dbh->prepare("SELECT sum(pledge_volume) as plv FROM cds_pledge WHERE pledge_contract=:cc and symbol_id=:id");
    $wc1->bindParam(':cc',$cc);
    $wc1->bindParam(':id',$sy);
    $wc1->execute();

    $state1 = $wc1->fetch();
    $sum = $state1['plv'];
    echo '
    <input type="hidden" class="form-control" value="'.$sy.'"  name= "sy" id="sy" >
    <div class="col-lg-4 col-md-4 col-sm-12">
      <label>Pledged Volume</label>
      <input type="text" class="form-control" value="'.$sum.'" name= "pl_vol" id="pl_vol" readonly>
    </div>
     <div class="col-lg-4 col-md-4 col-sm-12">
     <label>Pledge Release Volume</label>
      <input type="number" max="'.$sum.'" placeholder="Max. Vol. releasable  is : '.$sum.'" class="form-control" name="rls" id="rls" required>
    </div>
    <div class="col-lg-12">
     <label>Remarks</label>
      <input type="text" class="form-control" name="remarks" id="remarks" required>
    </div>';
}
elseif(!empty($_POST["pl_release"]))
{
    $cc=$_POST['pl_release'];
    $wc= $dbh->prepare("SELECT c.*, pn.pledge_name, pn.pledgee 
                        FROM  client_account c 
                        JOIN cds_pledge_contract pn ON c.cd_code = pn.cd_code
                        WHERE pn.pledge_contract=:cc");
    $wc->bindParam(':cc',$cc);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0) {
      echo '
     <input type="hidden" class="form-control" value="'.$state['cd_code'].'"  name= "ac" id="ac" >
     <input type="hidden" class="form-control" value="'.$state['pledge_name'].'"  name= "pname" id="pname" >
     <div class="col-lg-8 col-md-8 col-sm-12">
        <label>Pledge Name</label>
        <input type="text" class="form-control" value="'.$state['pledge_name'].'"  readonly>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Pledger Name</label>
        <input type="text" class="form-control" value="Mr./Mrs : '.$state['f_name'].' '.$state['l_name'].'" name= "pln" id="pln" readonly>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>CD Code</label>
        <input type="text" class="form-control" value="'.$state['cd_code'].'"  name= "ac1" id="ac1" readonly>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Pledgee</label>
        <input type="text" class="form-control" value="'.$state['pledgee'].'" name= "pl" id="pl" readonly>
      </div>';
    } else {
      echo'
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Message</label>
        <input type="text" class="form-control" style="color:red;" value="Contract code doesnt exist." readonly>
      </div>';

    }
}
elseif (!empty($_POST["syd"]) && !empty($_POST["accd"])) {
    $cd=$_POST['syd'];
    $acc=$_POST['accd'];

    $wc= $dbh->prepare("SELECT volume FROM cds_holding WHERE symbol_id=:sy AND cd_code=:acc");
    $wc->bindParam(':sy', $cd);
    $wc->bindParam(':acc', $acc);
    $wc->execute();
    $state = $wc->fetch();

    $volume = isset($state['volume']) ? $state['volume'] : 0;

    /*if($state['volume'] == NULL ){
      $state['volume'] = 0;
    } else {
      $state['volume'] = $state['volume'];
    }*/
    echo '
    <div class="col-lg-4 col-md-4">
      <label>Available Volume</label>
      <input type="text" id="available_volume" class="form-control" value="'.$volume.'" readonly>
    </div>';
}
elseif(!empty($_POST["sydunblock"]) && !empty($_POST["accd_block"])) {
    $cd = $_POST['sydunblock'];
    $acc = $_POST['accd_block'];

    $wc = $dbh->prepare("SELECT * from cds_holding  where symbol_id=:sy and cd_code=:acc ");
    $wc->bindParam(':sy', $cd);
    $wc->bindParam(':acc', $acc);
    $wc->execute();
    $state = $wc->fetch();
    
    $volume = isset($state['volume']) ? $state['volume'] : 0;

    echo'
    <div class="col-lg-4 col-md-4">
      <label>Max Volume Blockable</label>
      <input type="text" id="" class="form-control" value="'.$volume.'" readonly>
      <input type="hidden" id="available_volume_to_block" class="form-control" value="'.$volume.'" readonly>
    </div>
    <div class="col-lg-4 col-md-4">
      <label>Available Volume to Unblock</label>
      <input type="text" id="" class="form-control" value="'.$state['block_volume'].'" readonly>
      <input type="hidden" id="available_volume_to_unblock" class="form-control" value="'.$state['block_volume'].'" readonly>
    </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["dep"]))
{
           $toDate = $_POST['toDate'].' 23:59:00';
           $fromDate = $_POST['fromDate'].' 00:00:00';
            echo '<div class="col-xs-12">
            <div class="box-body">
            <div class="table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Id</th>
                  <th>Account</th>
                  <th>Name</th>
                  <th>Symbol</th>
                  <th>Volume</th>
                </tr>
                </thead>
                <tbody>';
                $query= $dbh->prepare('SELECT a.*,b.f_name,b.l_name,c.symbol from cds_dep_wit a,client_account b,symbol c where a.cd_code=b.cd_code and
                 a.symbol_id=c.symbol_id and a.entry_date >= :fdate and a.entry_date  <= :tdate and a.type != "B" and a.type != "S" and a.type != "PLEDGE"
                 and a.type != "PLEDGE RELEASE" and a.type != "BLOCK" and a.type != "UNBLOCK"');
                $query->bindParam(':fdate',$fromDate);$query->bindParam(':tdate',$toDate);
                $query->execute();
                $i=1;
                while($result=$query->fetch(PDO::FETCH_ASSOC))
                 {
                  if($result['volume'] < 0)
                    {
                          echo '<tr style="background-color:khaki;">';
                          echo '<td> '.$i++.'</td>';
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td> '.$result['f_name'].' '.$result['l_name'].'</td>';
                          echo '<td> '.$result['symbol'].'</td>';
                          echo '<td style="color:red;"> '.$result['volume'].'</td>';
                          echo '</tr>';
                    }
                    else
                    {
                          echo '<tr>';
                          echo '<td> '.$i++.'</td>';
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td> '.$result['f_name'].' '.$result['l_name'].'</td>';
                          echo '<td> '.$result['symbol'].'</td>';
                          echo '<td style="color:green;"> '.$result['volume'].'</td>';
                          echo '</tr>';
                    }
                  }
                $query->closeCursor();
                echo '</tbody>
              </table></div>
            </div>
          </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["accs"]))
{
   $toDate = $_POST['toDate'].' 23:59:00';
   $fromDate = $_POST['fromDate'].' 00:00:00';
    echo '
    <div class="table-responsive">
      <table id="example1" class="table table-bordered table-striped">
        <thead>
        <tr>
          <th>#</th>
          <th>CD Code</th>
          <th>Name</th>
          <th>CID</th>
        </tr>
        </thead>
        <tbody>';
        $query = $dbh->prepare('SELECT * FROM client_account where ca_date >= :fdate and ca_date  <= :tdate order by ca_date DESC');
        $query->bindParam(':fdate',$fromDate);$query->bindParam(':tdate',$toDate);
        $query->execute();
        $i=1;
        while ($result=$query->fetch(PDO::FETCH_ASSOC)) {
          echo'
          <tr>
            <td> '.$i.'</td>
            <td> '.$result['cd_code'].'</td>
            <td> '.$result['f_name'].' '.$result['l_name'].'</td>
            <td> '.$result['ID'].'</td>
          </tr>';
        $i++;
        }
        $query->closeCursor();
        echo '
        </tbody>
      </table>
    </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["blockshow"]))
{
   $toDate = $_POST['toDate'].' 23:59:00';
   $fromDate = $_POST['fromDate'].' 00:00:00';
    echo'
    <div class="box-body">
      <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Id</th>
              <th>Account</th>
              <th>Name</th>
              <th>Symbol</th>
              <th>Volume</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>';
          $query= $dbh->prepare('SELECT a.*, b.f_name, b.l_name, c.symbol 
            from cds_dep_wit a 
            JOIN client_account b ON a.cd_code = b.cd_code
            JOIN symbol c ON a.symbol_id=c.symbol_id
            where a.type="BLOCK/UNBLOCK" AND a.entry_date BETWEEN :fdate AND :tdate');
          $query->bindParam(':fdate', $fromDate);$query->bindParam(':tdate',$toDate);
          $query->execute();
          $i = 1;
          while($result=$query->fetch(PDO::FETCH_ASSOC)) {
            if($result['volume'] < 0) {
              echo '
              <tr style="background-color:khaki;">
                <td> '.$i++.'</td>
                <td> '.$result['cd_code'].'</td>
                <td> '.$result['f_name'].' '.$result['l_name'].'</td>
                <td> '.$result['symbol'].'</td>
                <td style="color:red;"> '.$result['volume'].' Blocked </td>
                <td> '.$result['entry_date'].'</td>
              </tr>';
            } else {
              echo'
              <tr>
                <td> '.$i++.' </td>
                <td> '.$result['cd_code'].' </td>
                <td> '.$result['f_name'].' '.$result['l_name'].' </td>
                <td> '.$result['symbol'].' </td>
                <td style="color:green;"> '.$result['volume'].' Unblocked</td>
                <td> '.$result['entry_date'].' </td>
              </tr>';
              }
            }
          $query->closeCursor();
          echo'
          </tbody>
        </table>
      </div>
    </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"])  && !empty($_POST["trans"]) )
{
           $toDate = $_POST['toDate'].' 23:59:00';
           $fromDate = $_POST['fromDate'].' 00:00:00';
          echo' <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                <div class="table-responsive">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>Id</th>
                      <th>From Acc</th>
                      <th>To Acc</th>
                      <th>Symbol</th>
                      <th>Volume</th>
                      <th>Remarks</th>
                      <th>User</th>
                      <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>';

                    $query= $dbh->prepare('SELECT a.*,c.symbol from cds_transfer a,symbol c where  a.symbol_id=c.symbol_id and a.trs_date  >= :fdate and a.trs_date  <= :tdate');
                    $query->bindParam(':fdate',$fromDate);$query->bindParam(':tdate',$toDate);
                    $query->execute();
                    $i=1;
                    while($result=$query->fetch(PDO::FETCH_ASSOC))
                     {
                              echo '<tr>';
                              echo '<td> '.$i++.'</td>';
                              echo '<td> '.$result['from_acc'].'</td>';
                              echo '<td> '.$result['to_acc'].'</td>';
                              echo '<td> '.$result['symbol'].'</td>';
                              echo '<td> '.$result['trs_vol'].'</td>';
                              echo '<td> '.$result['remarks'].'</td>';
                              echo '<td> '.$result['user_name'].'</td>';
                              echo '<td> '.$result['trs_date'].'</td>';
                              //echo '<td><button  data-toggle="modal" data-target="#myModal" name="edit_cli" id="edit_cli"  value="'.$result['pledge_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button></td>';
                              echo '</tr>';
                      }
                    $query->closeCursor();
                   echo ' </tbody>
                  </table></div>
                </div>
              </div>
            </div>
          </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"])  && !empty($_POST["pledge"]) )
{
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00';
    echo'
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header" style="font:8px;">
            <h4 class="box-title"><i class="fa fa-edit"></i><i class="fa fa-trash-o"></i></h4>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Id</th>
                    <th>Contract</th>
                    <th>Symbol</th>
                    <th>Pledged Volume</th>
                    <th>Pledgee</th>
                    <th>CD Code</th>
                    <th>Edit</th>
                  </tr>
                </thead>
              <tbody>';
              $query = $dbh->prepare('SELECT a.*, c.symbol 
                FROM cds_pledge a,symbol c 
                WHERE a.symbol_id=c.symbol_id AND a.pledge_volume > 0 AND a.pledge_date BETWEEN :fdate and :tdate');
              //$query->bindParam(':un',$_SESSION['sess_username']);
              $query->bindParam(':fdate', $fromDate);
              $query->bindParam(':tdate', $toDate);
              $query->execute();
              $i=1;
              while($result=$query->fetch(PDO::FETCH_ASSOC)) {
                echo'
                <tr>
                  <td> '.$i++.'</td>
                  <td> '.$result['pledge_contract'].'</td>
                  <td> '.$result['symbol'].'</td>
                  <td> '.$result['pledge_volume'].'</td>
                  <td> '.$result['pledgee'].'</td>
                  <td> '.$result['cd_code'].'</td>
                  <td>
                    <button data-toggle="modal" data-target="#myModal" name="edit_plg" id="edit_plg"  value="'.$result['pledge_id'].'" onClick="getState(this.value);">
                      <i class="fa fa-edit"></i></button>
                  </td>
                </tr>';
              }
              $query->closeCursor();
              echo'
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>';
}
elseif (!empty($_POST["toDate"]) && !empty($_POST["fromDate"])  && !empty($_POST["pledge_contract"]) ) {
    $toDate = $_POST['toDate'].' 23:59:00';
    $fromDate = $_POST['fromDate'].' 00:00:00';
    
    echo'
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-body">
            <div class="table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Id</th>
                    <th>Contract Code</th>
                    <th>Details</th>
                    <th>CD Code</th>
                    <th>Pledgee</th>
                    <th>Remarks</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>';
                $query = $dbh->prepare("SELECT * FROM cds_pledge_contract WHERE pledge_date BETWEEN :fdate AND :tdate");
                //$query->bindParam(':un',$_SESSION['sess_username']);
                $query->bindParam(':fdate',$fromDate);
                $query->bindParam(':tdate',$toDate);
                $query->execute();
                $i = 1;
                while($result=$query->fetch(PDO::FETCH_ASSOC)) {
                  echo'
                  <tr>
                    <td>'.$i++.'</td>
                    <td>'.$result['pledge_name'].'</td>
                    <td>'.$result['pledge_contract'].'</td>
                    <td>'.$result['cd_code'].'</td>
                    <td>'.$result['pledgee'].'</td>
                    <td>'.$result['remarks'].'</td>
                    <td>
                      <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal" name="edit_plg_contra" id="edit_plg_contra" value="'.$result['cds_pledge_contract_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i> </button>
                    </td>
                  </tr>';
                  }
                $query->closeCursor();
                echo'
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>';
}
elseif (!empty($_POST["toDate"]) && !empty($_POST["fromDate"])  && !empty($_POST["pledge_release"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';
  echo' 
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header" style="font:8px;">
          <h4 class="box-title"><i class="fa fa-edit"></i><i class="fa fa-trash-o"></i></h4>
        </div>
        <div class="box-body">
        <div class="table-responsive">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Id</th>
                <th>Contract</th>
                <th>Symbol</th>
                <th>Pledged Volume</th>
                <th>Pledgee</th>
                <th>CD Code</th>
                <th>Edit</th>
              </tr>
            </thead>
            <tbody>';
            $query= $dbh->prepare('SELECT a.*, c.symbol 
                FROM cds_pledge a 
                JOIN symbol c ON a.symbol_id=c.symbol_id 
                WHERE a.pledge_volume < 0 AND a.pledge_date BETWEEN :fdate AND :tdate');
            $query->bindParam(':fdate',$fromDate);
            $query->bindParam(':tdate',$toDate);
            $query->execute();
            $i=1;
            while($result=$query->fetch(PDO::FETCH_ASSOC)) {
              echo'
              <tr>
                <td> '.$i++.' </td>
                <td> '.$result['pledge_contract'].' </td>
                <td> '.$result['symbol'].' </td>
                <td> '.$result['pledge_volume'].' </td>
                <td> '.$result['pledgee'].' </td>
                <td> '.$result['cd_code'].' </td>
                <td>
                  <button  data-toggle="modal" data-target="#myModal" name="edit_cli" id="edit_cli"  value="'.$result['pledge_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button>
                </td>
              </tr>';
            }
            $query->closeCursor();
            echo '</tbody>
          </table></div>
        </div>
      </div>
    </div>
  </div>';
}
elseif(!empty($_POST["dDate"])) {
    $ddDate = $_POST['dDate'];
    $dDate = date("d-m-Y", strtotime($ddDate));

    echo'
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header" style="font:8px;">
          <h4 class="box-title">Back Up File</h4>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>File Name</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>';
              $query = $dbh->prepare("SELECT * FROM backupsr WHERE name=:ddate");
              $query->bindParam(':ddate',$dDate);
              $query->execute();
              $result = $query->fetch();
              if($result == NULL) {
                echo'
                <tr>
                  <td style="color:red">NO data</td>
                  <td></td>
                </tr>';
              } else {
                echo'
                <tr>
                  <td>'.$result['name'].'</td>
                  <td><a href="'.$result['link'].'">Download</a></td>
                </tr>';
              }
              $query->closeCursor();
              echo '
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>';
}

elseif(isset($_POST["priceAdjustment"]))
{
  $level = $_POST['level'];
  $levelName = "";

  if($_POST['level'] == '1'){
    $levelName = "One Month";
    $query= $dbh->prepare("SELECT t.symbol_id, m.symbol, m.name
        FROM
        (SELECT r.symbol_id, MAX(DATE(r.order_date)) Order_Date
          FROM executed_orders r GROUP BY r.symbol_id ORDER BY r.symbol_id ASC
        ) t
        LEFT JOIN symbol m ON t.symbol_id = m.symbol_id
        LEFT JOIN market_price p ON t.symbol_id = p.symbol_id
        WHERE DATE(t.Order_Date) < DATE(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND DATE(p.date) < DATE(DATE_SUB(NOW(), INTERVAL 1 MONTH))
        AND m.security_type NOT IN ('GB', 'CP') AND m.status=1 AND m.trsstatus=1 GROUP BY t.symbol_id
        UNION ALL
        SELECT s.symbol_id, s.symbol, s.name
        FROM symbol s WHERE s.symbol_id NOT IN (SELECT symbol_id FROM executed_orders r)
        AND s.security_type ='OS' AND s.status='1' AND s.trsstatus='2' ORDER BY symbol ASC");
  }else{
    $levelName = "One Year";
    $query= $dbh->prepare("SELECT t.symbol_id, m.symbol, m.name
          FROM
          (SELECT r.symbol_id, MAX(DATE(r.order_date)) Order_Date
            FROM executed_orders r GROUP BY r.symbol_id ORDER BY r.symbol_id ASC
          ) t
          LEFT JOIN symbol m ON t.symbol_id = m.symbol_id
          LEFT JOIN market_price p ON t.symbol_id = p.symbol_id
          WHERE DATE(t.Order_Date) < DATE_SUB(NOW(), INTERVAL 1 YEAR)
          AND DATE(p.date) < DATE_SUB(NOW(), INTERVAL 1 YEAR)
          AND m.security_type NOT IN ('GB', 'CP') AND m.status=1 AND m.trsstatus=1 GROUP BY t.symbol_id
          UNION ALL
          SELECT s.symbol_id, s.symbol, s.name
          FROM symbol s WHERE s.symbol_id NOT IN (SELECT symbol_id FROM executed_orders r) AND s.security_type ='OS' AND s.status='1' AND s.trsstatus='2' ORDER BY symbol ASC");
  }
  $query->execute();

  date_default_timezone_set("Asia/Thimphu");
  $sysTime = date("Y-m-d");
  echo '<br><br>
      <section class="invoice" style="background:rgb(248, 249, 249);">
          <div class="row">
            <div class="col-xs-12">
              <div class="page-header">
                &emsp;<img src="../../dist/img/Logo.png"> &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                <b style="font-size: 130%;">ROYAL SECURITIES EXCHANGE OF BHUTAN<b>
                 <center><div class="lead" style="font-size: 55%; margin-top:-25px;">Price Adjustment Report</div>
                 <div class="lead" style="font-size: 40%;  margin-top:-25px;">
                  Report generated on :'.$sysTime.' by '.$_SESSION['sess_username'].'</div>
                 </center>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-12">
              <div class="lead" style="font-size: 70%; margin-top:-10px;">Untraded For : <strong>'.$levelName.'</strong></div>
            </div>
          </div>';
      echo'<div class="row">
            <div class="col-xs-12 table-responsive">
                <table class="table table-striped" id="tableId">
                  <thead style="background-color: #D6EAF8; font-size: 80%;">
                  <tr>
                    <th style="text-align:right;">Sl. No</th>
                    <th style="text-align:right;">Symbol Name</th>
                    <th style="text-align:center;">Company Name</th>
                  </tr>
                  </thead>
                  <tbody>';
                  $i=1;
            foreach($query as $state){
                echo'<tr style="font-size: 70%;">
                    <td style="text-align:right;">'.$i.'</td>
                    <td style="text-align:right;">'.$state['symbol'].'</td>
                    <td style="text-align:center;">'.$state['name'].'</td>
                  </tr>';
                  $i=$i+1;
              }
              echo'</tbody>
                </table>
              </section>
              <div class="row no-print">

              </div>';
}
elseif(!empty($_POST["getCDCodeDetls"])) {
  $cd = $_POST['getCDCodeDetls'];

  $stmt = $dbh->prepare("SELECT a.f_name, a.l_name, a.phone, a.ID, a.institution_id, p.participant_code
        FROM client_account a
        LEFT JOIN adm_participants p ON a.institution_id = p.institution_id
        WHERE a.cd_code = ?
  ");
  $stmt->bindParam(1, $cd);
  $stmt->execute();
  $res = $stmt->fetch();

  if ($res) {
    echo'
    <input type="hidden" name="participate_code" id="participate_code" class="form-control" value="'.$res['participant_code'].'" readonly>
    <input type="hidden" name="institute_id" id="institute_id" class="form-control" value="'.$res['institution_id'].'" readonly>

    <div class="col-lg-6 col-md-6">
      <label for="name">Name:</label>
      <input type="text" class="form-control" name="name" id="name" value="'.$res['f_name'].' '.$res['l_name'].'" readonly>
    </div>

    <div class="col-lg-6 col-md-6">
      <label for="phone">Phone:</label>
      <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" readonly>
    </div>

    <div class="col-lg-6 col-md-6">
      <label for="cid">Old CID No:</label>
      <input type="text" class="form-control" name="cid" id="cid" value="'.$res['ID'].'" readonly>
    </div>

    <div class="col-lg-6 col-md-6">
      <label for="newcid">New CID No: <font color="red">*</font></label>
      <input type="number" class="form-control" name="newcid" id="newcid" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==11) return false;" required>
    </div>

    <div class="col-lg-12">
      <label for="remark">Remarks: <font color="red">*</font></label>
      <textarea class="form-control" name="remark" id="remark" required></textarea>
    </div>';
  } else {
    echo'
    <div class="col-lg-6 col-md-6">
      <label>Name</label>
      <input type="text" class="form-control" value="NO DATA" disabled>
    </div>';
  }
}
elseif(!empty($_POST["online_share_statement"])) {
  include ('../../CONNECTIONS/db_config_website.php');

  $cidNo = $_POST['cidNo'];

  $wc = $dbh_site->prepare("SELECT 
    -- s.email, 
    t.email, 
    t.cid_no, t.phone_no, t.amount, t.status, t.order_no, t.created_at 
    FROM online_stmt_pymt_temps t 
    -- JOIN sms_otp_logs s ON t.cid_no = s.cid_no 
    WHERE t.cid_no = :cdn 
    GROUP BY t.order_no 
    ORDER BY t.created_at DESC
  ");
  $wc->bindParam(':cdn', $cidNo);
  $wc->execute();
  if ($wc->rowCount() > 0) {
    echo'
    <div class="table-responsive">
      <table class="table table-bordered" id="tableExpId">
        <thead>
          <tr style="background-color:#333;color:#fff">
            <th scope="col">#</th>
            <th scope="col">CID No</th>
            <th scope="col">Phone No</th>
            <th scope="col">Email</th>
            <th scope="col">Amount</th>
            <th scope="col">Payment Status</th>
            <th scope="col">Order No</th>
            <th scope="col">Created At</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>';
        $i=1;
        foreach($wc as $res){
          echo'
          <tr>
            <td>'.$i.'</td>
            <td>'.$res['cid_no'].'</td>
            <td>'.$res['phone_no'].'</td>
            <td>'.$res['email'].' ';
            if($res['status'] == 1){
              echo'<button type="button" onclick="sendReportMail(\''.$res['cid_no'].'\', \''.$res['email'].'\')" class="btn btn-info">Send SS</button>';
            }
            echo'
            </td>
            <td>'.$res['amount'].'</td>';
            if($res['status'] == 1){
              echo'<td style="background-color: green;">Success</td>';
            }else{
              echo'<td style="background-color: red;">Fail</td>';
            }
            echo'
            <td>'.$res['order_no'].'</td>
            <td>'.$res['created_at'].'</td>
            <td>
              <a href="https://bhutancrowdfunding.rsebl.org.bt/process/ASMessageGenerator_OSS.php?ordNo='.$res['order_no'].'" target="_blank" class="btn btn-primary">Check Payment Status</a>
            </td>
          </tr>';
          $i++;
        }
     echo'</tbody>
     </table>
    </div>
    <script type="text/javascript">
      function sendReportMail(cid_no, email){
        showLoading();
        var op = "sendReportViaMail";
        $.ajax({
          type: "POST",
          url: "load.php",
          data: "cid_no="+cid_no+"&email="+email+"&sendReportViaMail="+op,
          success: function(data){
            hideloading();
            alert(data);
          }
        });
      }
    </script>';
  } else {
    echo'
    <div class="col-lg-12">
      <input type="text" class="form-control" value="NO DATA AVAILABLE" style="color: red;" disabled>
    </div>';
  }
}
elseif(!empty($_POST["sendReportViaMail"])){
  include('mail.php');
  die();
}
elseif (isset($_POST["operation"]) && $_POST["operation"] === 'get__DISN__No') {
    $acc_type = $_POST['acc_type'];
    $data = [];

    // get latest DISN number
    $sql = "SELECT ID FROM client_account WHERE ID LIKE ";
    if ($acc_type == 'J') {
      $sql .= "'II%' ";
    } elseif ($acc_type == 'A') {
      $sql .= "'AI%' ";
    } elseif ($acc_type == 'R') {
      $sql .= "'RI%' ";
    }
    $sql .= "ORDER BY client_id DESC LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    $last_DISN = $row['ID'];
    $next_DISN = '';

    // Extract numeric part
    $numeric_part = substr($last_DISN, 2);

    // Increment and pad with leading zeros
    $next_numeric_part = str_pad($numeric_part + 1, strlen($numeric_part), "0", STR_PAD_LEFT);

    // Form the next ID
    if ($acc_type == 'J') {
      $next_DISN = "II" . $next_numeric_part;
    } elseif ($acc_type == 'A') {
      $next_DISN = "AI" . $next_numeric_part;
    } elseif ($acc_type == 'R') {
      $next_DISN = "RI" . $next_numeric_part;
    }

    echo $next_DISN;

    exit;
}
elseif(!empty($_POST["get_user_account_dtls"])) {
  $cidNo = $_POST['cidNo'];

  $stmt = $dbh->prepare("SELECT u.participant_code, u.username, u.cid, u.phone, u.email, u.created_at, u.name, u.name, DATE_ADD(u.created_at, INTERVAL 1 YEAR) AS valid_until, u.status 
      FROM users u 
      WHERE u.cid =  ?
      AND u.role_id = 4
  ");
  $stmt->bindParam(1, $cidNo);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if($rows) {
    echo'
    <div class="table-responsive">
      <table class="table table-bordered" id="tableExpId" width="100%">
        <thead>
          <tr style="background-color:#333;color:#fff">
            <th scope="col">#</th>
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
        $i = 1;
        foreach ($rows as $res) {
          $status_ss = ($res['status'] == 1) ? 'Active' : 'In-active';
          echo'
          <tr>
            <td>'.$i.'</td>
            <td>'.$res['cid'].'</td>
            <td>'.$res['name'].'</td>
            <td>'.$res['phone'].'</td>
            <td>'.$res['email'].'</td>
            <td>'.$res['participant_code'].'</td>
            <td>'.$res['username'].'</td>
            <td>'.$res['created_at'].'</td>
            <td>'.$res['valid_until'].'</td>
            <td>'.$status_ss.'</td>
            <td>
              <button type="button" class="btn btn-primary" onclick="reset_lock(\''.$res['username'].'\')"><i class="fa fa-unlock"></i> Unlock</button>
              <button type="button" class="btn btn-danger" onclick="reset_password(\''.$res['username'].'\')"><i class="fa fa-refresh"></i> Reset</button>
            </td>
          </tr>';
          $i++;
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

      function reset_password(usr_name) {
        showLoading();
        if (confirm("Do you want to continue?")) {
          var op = "reset_mcams_user_password";
          $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: "usr_name=" + usr_name + "&reset_mcams_user_password=" + op,
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
  }else{
    echo'
    <div class="col-xs-12">
      <input type="text" class="form-control" value="NO DATA AVAILABLE" disabled>
    </div>';
  }
  exit;
}
elseif(!empty($_GET["get_payment_tds"])) {
    date_default_timezone_set("Asia/Thimphu");
    $sysTime = date("Y-m-d");

    $id = $_GET['id'];
    

    $wc = $dbh->prepare("SELECT  
        ud.name,
        up.tpn,
        up.payment_date,
        ud.year,
        up.amount
    FROM unclaimed_dividend ud
    LEFT JOIN uc_payment up 
        ON ud.cd_code = up.cd_code
    WHERE up.id = :id");
    $wc->bindParam(':id', $id, PDO::PARAM_INT);
    $wc->execute();
    $state = $wc->fetch();

    // Calculate TDS amount
    $tds_rate = 10; // TDS rate as 10%
    $tds_amount = $state['amount'] * ($tds_rate / 100);

    echo '
    <html>
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>TDS Certificate for Dividend</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
          }
          .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
          }
          .header, .footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
          }
          .content {
            flex-grow: 1;
            padding: 30px;
            text-align: left;
          }
          .invoice {
            background: rgb(248, 249, 249);
            padding: 20px;
            border: 1px solid #ddd;
          }
          .lead {
            font-size: 1.2em;
          }
          .page-header img {
            width: 100px;
          }
          .footer img {
            width: 200px; /* Adjust size as needed */
            margin-top: 10px;
          }
          .details-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
          }
          .details-table td {
            padding: 8px;
            border: 1px solid #ddd;
          }
          .details-table th {
            padding: 8px;
            background-color: #f2f2f2;
            text-align: left;
          }
        </style>
      </head>
      <body onload="window.print();">
        <div class="wrapper">
          <!-- Header -->
          <div class="header">
            <img src="../../dist/img/tds_header.png" alt="Header Image">
            <h2>ROYAL SECURITIES EXCHANGE OF BHUTAN</h2>
            <div class="lead">TDS Certificate for Dividend</div>
          </div>

          <!-- Content -->
          <div class="content">
            <section class="invoice">
              <table class="details-table">
                <tr>
                  <th>Particulars</th>
                  <th>Details</th>
                </tr>
                <tr>
                  <td>Name of Shareholder:</td>
                  <td>' . $state['name'] . '</td>
                </tr>
                <tr>
                  <td>Tax Payer Number:</td>
                  <td>' . $state['tpn'] . '</td>
                </tr>
                <tr>
                  <td>Financial Year:</td>
                  <td>' . $state['year'] . '</td>
                </tr>
                <tr>
                  <td>Date of Payment:</td>
                  <td>' . $state['payment_date'] . '</td>
                </tr>
                <tr>
                  <td>Total Dividend Paid:</td>
                  <td>' . $state['amount'] . '</td>
                </tr>
                <tr>
                  <td>Rate of TDS (%):</td>
                  <td>' . $tds_rate . '%</td>
                </tr>
                <tr>
                  <td>Amount of TDS Deducted:</td>
                  <td>' . number_format($tds_amount, 2) . '</td>
                </tr>
              </table>
            </section>
          </div>

          <div style="text-align: right; margin-top: 40px; padding-right: 50px;">
            <p><strong>Authorized Signatory</strong></p>
          </div>
          
            <img src="../../dist/img/tds_footer.png" alt="Footer Image">
        </div>
      </body>
    </html>';
}
else
{
}
?>


<script type="text/javascript">
function getState3(val)
{
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'cdCode='+val,
    dataType: "html",
    success: function(response){ 
      $("#cd1").html(response);
    } 
  });
}
</script>
<script type="text/javascript">
function getState4(val)
{
  $.ajax({ type: "POST", url: "load.php", data:'cdCodeAC='+val,success: function(data){ $("#cd1").html(data);} });
}
</script>


<style type="text/css">
  .errorClass { background:  #FADBD8  ; }
</style>
<script type="text/javascript">
      $("#phone").keyup('input', function() {
            var phoneLength = $("#phone").val();
            if(phoneLength.length > 8 )
            {
              $("#errln").show();
              $("#phone").addClass("errorClass");
            }
            else
            {
              $("#errln").hide(10);
              $("#phone").removeClass("errorClass");
            }
            });
      $("#accno").keyup('input', function() {
            var accountNumber = $("#accno").val();
            if(accountNumber.length > 10 )
            {
              $("#errAcno").show();
              $("#accno").addClass("errorClass");
            }
            else
            {
              $("#errAcno").hide(10);
              $("#accno").removeClass("errorClass");
            }
      });
      $("#id").keyup('input', function() {
            var cid = $("#id").val();
            var flag=/^[0-9]+$/.test(cid);
            if(!flag)
            {
              $("#errCid").show();
              $("#id").addClass("errorClass");
            }
            else
            {
              $("#errCid").hide(10);
              $("#id").removeClass("errorClass");
            }
      });
</script>
<script>
  $(function () {
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
</script>
<script type="text/javascript">
      $("#hol").keyup('input', function() {
            var volume = $("#hol").val();
            var avail_vol = $("#available_volume").val();

            if(Number(volume) <= Number(avail_vol))
            {
              $("#demat").show();
            }
            else if(volume === '')
            {
              $("#demat").hide();
            }
            else
            {
              $("#demat").hide();
            }
      });
</script>
<script type="text/javascript">
      $("#trs").keyup('input', function() {
            var volume = $("#avl").val();
            var avail_vol = $("#trs").val();

            if(Number(volume) >= Number(avail_vol))
            {
              $("#transfer").show();
            }
            else if(volume === '')
            {
              $("#transfer").hide();
            }
            else
            {
              $("#transfer").hide();
            }
      });
</script>
<script type="text/javascript">
      $("#trs1").keyup('input', function() {
            var volume = $("#avl1").val();
            var avail_vol = $("#trs1").val();

            if(Number(volume) >= Number(avail_vol))
            {
              $("#pledge").show();
            }
            else if(volume === '')
            {
              $("#pledge").hide();
            }
            else
            {
              $("#pledge").hide();
            }
      });
</script>
<script type="text/javascript">
      $("#rls").keyup('input', function() {
            var volume = $("#pl_vol").val();
            var avail_vol = $("#rls").val();

            if(Number(volume) >= Number(avail_vol))
            {
              $("#pledge_release").show();
            }
            else if(volume === '')
            {
              $("#pledge_release").hide();
            }
            else
            {
              $("#pledge_release").hide();
            }
      });
</script>
