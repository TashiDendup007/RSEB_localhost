<?php
date_default_timezone_set("Asia/Thimphu");
include('session_file.php'); 
include ('../../CONNECTIONS/db.php');

if(!empty($_POST["val"])) {
    $type=$_POST['val'];
    if($type=='I')
    {
    echo '      <div class="col-xs-3">
                  <label>CD Code<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="cdcode" id="cdcode" maxlength="10" onChange="getState3(this.value);" required>
                </div> 
                <div id="cd1">
                </div> ';
    }
    elseif($type=='J')
    {
    echo '<div class="col-xs-3">
                <input type="hidden" class="form-control" name="nat" id="nat" value="">
                <input type="hidden" class="form-control" name="title" id="title" value="">
                <input type="hidden" class="form-control" name="occupation" id="occupation" value="">           
                  <label>CD Code<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="cdcodeAC" id="cdcodeAC" maxlength="10" onChange="getState4(this.value);" required>
                </div> 
                <div id="cd1">
                </div>';

    }
}
elseif(!empty($_POST["cdCode"])) 
{
    $cd=$_POST['cdCode'];
    $wc= $dbh->prepare("SELECT cd_code from client_account where cd_code=:cd");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
      echo '<div class="col-xs-3">
                  <label>Message</label>
                  <input style="color:red; type="text" class="form-control" value="CD Code already exists" required>
                </div>';

    }else
    {
      echo '<div class="col-xs-3">
                  <label>Title</label>
                  <input type="text" class="form-control" name="title" id="title">
                </div>          
                 <div class="col-xs-3">
                  <label>First Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="fn" id="fn" required>
                </div>
                <div class="col-xs-3">
                 <label>Last Name</label>
                  <input type="text" class="form-control" name="ln" id="ln">
                </div>
                <div class="col-xs-3">
                  <label>Occupation</label>';
                  $q=$dbh->prepare('SELECT * from occupation order by occupation_name ASC');
                  $q->execute();
                  echo'<select id="occupation" name="occupation" class="form-control">';
                  foreach($q as $state)
                  {
                    echo '<option value="'.$state['occupation'].'">'.$state['occupation_name'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-3">
                 <label>Nationality<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="nat" id="nat" required>
                </div>
                <div class="col-xs-3">
                  <label>CID<span style="color:red;">*</span></label>
                  <input type="number" class="form-control" name="id" id="id" onKeyPress="if(this.value.length==11) return false;" required>
                </div>
                <div class="col-xs-3">
                  <label>Dzongkhag</label>';
                  $q=$dbh->prepare('SELECT * from tbldzongkhag order by DzongkhagName ASC');
                  $q->execute();
                  echo'<select id="dz" name="dz" class="form-control">';
                  foreach($q as $state)
                  {
                    echo '<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-3">
                  <label>TPN<span style="color:red;"></span></label>
                  <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn" >
                </div>
                <div class="col-xs-3">
                  <label>Phone No</label>
                  <input type="number" class="form-control" name="phone" id="phone" onKeyPress="if(this.value.length==8) return false;">
                  <span id="errln" style="color:red;display:none;">*Phone Number should be only 8 characters</span>
                </div>
                <div class="col-xs-3">
                  <label>email</label>
                  <input type="text" class="form-control" name="email" id="email" >
                  <span id="errEmail" style="color:red;display:none;">*Please use only letters and numbers</span>
                </div>
                <div class="col-xs-3">
                  <label>Bank Name</label>';
                  $q=$dbh->prepare('SELECT * from banks');
                  $q->execute();
                  echo'<select id="bank" name="bank" class="form-control">';
                  foreach($q as $state)
                  {
                    echo '<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-3">
                  <label>Account Number</label>
                  <input type="number" class="form-control" name="accno" id="accno" onKeyPress="if(this.value.length==13) return false;">
                </div>
                <div class="col-xs-3">
                  <label>Account Type</label>
                  <select id="bankAccType" name="bankAccType" class="form-control">
                    <option value="">--Select Account Type--</option>
                    <option value="Saving Account">Saving Account</option>
                    <option value="Current Account">Current Account</option>
                  </select>
                </div>
                <div class="col-xs-12">
                  <label>Address</label>
                  <input type="text" class="form-control" name="add" id="add" >
                </div> ';?>

                <div class="col-xs-12"><br>
                  <label>Details of the Nominee</label>         

                <div class="table-responsive">
                <div class="form-group">
                  <h5 align="center"><strong></strong></h5>
                  
                  <div class="col-md-12" align="center">
                    <table id="nominee" class="table table-striped table-bordered table-hover">
                      <thead>
                      </thead>
                      <tbody>
                        <tr>
                          <td>1</td>
                          <td> <input class="form-control" name="name[]" id="name0" type="text" placeholder="Name"></td>
                          <td> <input class="form-control" name="cid[]" id="cid0" type="text" maxlength="11" placeholder="CID"></td>
                          <td> <select class="form-control" id="relationship0" name="relationship[]">
                                  <option value="">--Select relationship--</option>
                                  <option value="Father">Father</option>
                                  <option value="Mother">Mother</option>
                                  <option value="Son">Son</option>
                                  <option value="Daughter">Daughter</option>
                                  <option value="Spouce">Spouce</option>
                                  <option value="Brother">Brother</option>
                                  <option value="Sister">Sister</option>
                                </select>
                           </td>
                        </tr>        
                      </tbody>
                    </table>
                    <div class="form-group" align="right">
                          <button type="button" class="btn btn-default" onclick="addProposedOwnership('nominee')"> Add More</button>
                          &nbsp;
                          <button type="button" class="btn btn-default" onclick="removeForeignInvestor('nominee')"> Remove</button>                    
                    </div>
                  </div>
                  </div>    
              </div><br>
            NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
            <br>
            </div>
            <script type="text/javascript">
            $('#save_client').show();
            </script>
<?php
    }

    
}
elseif(!empty($_POST["cdCodeAC"])) 
{
    $cd=$_POST['cdCodeAC'];
    $wc= $dbh->prepare("SELECT cd_code from client_account where cd_code=:cd");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
      echo '<div class="col-xs-3">
                  <label>Message</label>
                  <input style="color:red; type="text" class="form-control" value="CD Code already exists" required>
                </div>';

    }else
    {
      echo '<div class="col-xs-3">
                  <label>Asso. Name<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="fn" id="fn" required>
                </div>
                <div class="col-xs-3">
                  <label>DISN<span style="color:red;">*</span></label>
                  <input type="text" maxlength="11" class="form-control" name="id" id="id" onKeyPress="if(this.value.length==11) return false;" required>
                </div>
                <div class="col-xs-3">
                  <label>Dzongkhag</label>';
                  $q=$dbh->prepare('SELECT * from tbldzongkhag order by DzongkhagName ASC');
                  $q->execute();
                  echo'<select id="dz" name="dz" class="form-control">';
                  foreach($q as $state)
                  {
                    echo '<option value="'.$state['DzongkhagID'].'">'.$state['DzongkhagName'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-3">
                  <label>TPN<span style="color:red;"></span></label>
                  <input type="text" maxlength="9" class="form-control" name="tpn" id="tpn" >
                </div>
                <div class="col-xs-3">
                  <label>Contact Person<span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="ln" id="ln" required>
                </div>
                <div class="col-xs-3">
                  <label>Phone No</label>
                  <input type="number" class="form-control" name="phone" id="phone"  onKeyPress="if(this.value.length==8) return false;" >
                  <span id="errln" style="color:red;display:none;">*Phone Number should be only 8 characters</span>
                </div>
                <div class="col-xs-3">
                  <label>email</label>
                  <input type="text" class="form-control" name="email" id="email" >
                </div>
                 <div class="col-xs-3">
                  <label>Bank Name</label>';
                  $q=$dbh->prepare('SELECT * from banks');
                  $q->execute();
                  echo'<select id="bank" name="bank" class="form-control">';
                  foreach($q as $state)
                  {
                    echo '<option value="'.$state['bank_id'].'">'.$state['bank_name'].'</option>';
                  }
                  echo'</select>
                </div>
                <div class="col-xs-3">
                  <label>Account Number</label>
                  <input type="number" class="form-control" name="accno" id="accno"  onKeyPress="if(this.value.length==13) return false;" >
                </div>
                <div class="col-xs-3">
                  <label>Account Type</label>
                  <select id="bankAccType" name="bankAccType" class="form-control">
                    <option value="">--Select Account Type--</option>
                    <option value="Saving Account">Saving Account</option>
                    <option value="Current Account">Current Account</option>
                  </select>
                </div>
                <div class="col-xs-12">
                  <label>Address</label>
                  <input type="text" class="form-control" name="add" id="add" >
                   <br>
                NOTE: Fields marked (<span style="color:red;">*</span>) are mendatory
            <br>
                </div>';?>
                <script type="text/javascript">
                $('#save_client').show();
                </script>
<?php
    }
}
if(!empty($_POST["mat"])) 
{
    $cd=$_POST['mat'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
    echo '      <div class="col-xs-4">
                  <label>Details of Client</label>
                  <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
                </div>
                <div class="col-xs-4">
                  <label>Broker</label>
                  <input type="text" class="form-control" value="'.$state['name'].'" readonly >
                </div>
                <div class="col-xs-3">
                  <label>Symbol</label>';
                            $wc= $dbh->prepare("select symbol,symbol_id from symbol");
                            $wc->execute();
                            echo '<select name="sy" id="sy"  class="form-control" onChange="loadsymbol(this.value);">
                            <option value=""> Select Symbol </option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                            }
                            echo '</select>
                </div> 
                <div  id="vol_avl">
                </div>
                <div class="col-xs-3">
                  <label>Volume</label>
                  <input type="number" class="form-control" name="hol" id="hol" min="1" required>
                </div>
                <div class="col-xs-6">
                  <label>Remarks</label>
                  <input type="text" class="form-control" name="rm" id="rm">
                </div> ';
    }
    else
    {
      echo '    <div class="col-xs-4">
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
    if($wc->rowCount() > 0)
    {
    echo '      <div class="col-xs-4">
                  <label>Details of Client</label>
                  <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].'" readonly>
                </div>
                <div class="col-xs-4">
                  <label>Broker</label>
                  <input type="text" class="form-control" value="'.$state['name'].'" readonly >
                </div>
                <div class="col-xs-3">
                  <label>Symbol</label>';
                            $wc= $dbh->prepare("select symbol,symbol_id from symbol");
                            $wc->execute();
                            echo '<select name="sy" id="sy"  class="form-control" onChange="loadsymbol(this.value);">
                            <option value=""> Select Symbol </option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                            }
                            echo '</select>
                </div> 
                <div  id="block_vol_avl">
                </div>
                <div class="col-xs-3">
                  <label>Volume</label>
                  <input type="number" class="form-control" name="blockhol" id="blockhol" min="1" required>
                </div>
                <div class="col-xs-6">
                  <label>Remarks</label>
                  <input type="text" class="form-control" name="rm" id="rm">
                </div> ';
    }
    else
    {
      echo '    <div class="col-xs-4">
                  <label>Message</label>
                  <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
                </div>';
    }
}
elseif(!empty($_POST["fro"])) 
{
    $cd=$_POST['fro'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
    echo '      <div class="col-xs-6">
                  <label>Details of Transferer Client</label>
                  <input type="text"  class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].' , '.$state['name'].'" readonly>
                </div>
                <div class="col-xs-6 has-success">
                 <label class="control-label" for="inputSuccess">To Account</label>
                  <input type="text" class="form-control" maxlength="10"  name="T_cd" id="T_cd" onChange="toAccount(this.value);" required>
                </div>
                <div  id="to">
                </div>';
      }
      else
      {
      echo '    <div class="col-xs-4">
          <label>Message</label>
          <input type="text" class="form-control" style="color:red;" value="Invalid Client." name= "pln" id="pln" readonly>
        </div>';

      }
}
elseif(!empty($_POST["fro1"])) 
{
    $cd=$_POST['fro1'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
      echo '      <div class="col-xs-3">
                  <label>Details of PLEDGER </label>
                  <input type="text"  class="form-control" value="Mr/Mrs : '.$state['f_name'].' '.$state['l_name'].'" readonly>
                </div>';

    }else
    {
      echo '      <div class="col-xs-3">
                  <label>Details of PLEDGER </label>
                  <input type="text"  class="form-control" style="color:red;" value="Invalid CD Code !! Please Enter correct one." readonly>
                </div>';
    }
}
elseif(!empty($_POST["loadall"])) 
{
    $cc=$_POST['loadall'];
    $wc= $dbh->prepare("SELECT a.*,b.* from cds_pledge_contract a,client_account b where a.pledge_contract=:c and a.cd_code=b.cd_code ");
    $wc->bindParam(':c',$cc);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
      echo '      <div class="col-xs-3">
                  <label>Details of PLEDGER </label>
                  <input type="text"  class="form-control" value="Mr/Mrs : '.$state['f_name'].' '.$state['l_name'].'" readonly>
                </div>
                <div class="col-xs-4">
                 <label>Pledge Details</label>
                  <input type="text"  class="form-control" value="'.$state['cd_code'].'/ '.$state['pledgee'].'" readonly>
                  <input type="hidden" class="form-control"  name="cc" id="cc" value="'.$state['pledge_contract'].'">
                  <input type="hidden" class="form-control"  name="ac" id="ac" value="'.$state['cd_code'].'">
                  <input type="hidden" class="form-control"  name="pl" id="pl" value="'.$state['pledgee'].'">
                </div>';

    }else
    {
      echo '      <div class="col-xs-3">
                  <label>Details of PLEDGER </label>
                  <input type="text"  class="form-control" style="color:red;" value="Invalid Pledge Contract Code !! Please Enter correct one." readonly>
                </div>';
    }
}
elseif(!empty($_POST["to"])) 
{
    $cd=$_POST['to'];
    $wc= $dbh->prepare("SELECT a.f_name,a.l_name,a.ID,b.name from client_account a,adm_institution b where a.cd_code=:cd and a.institution_id= b.institution_id ");
    $wc->bindParam(':cd',$cd);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
    echo '      <div class="col-xs-6">
                  <label>Details of Receiver Client</label>
                  <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID/DISN# '.$state['ID'].' , '.$state['name'].'" readonly>
                </div>
                <div class="col-xs-6">
                  <label>Symbol</label>';
                            $wc= $dbh->prepare("select symbol,symbol_id from symbol");
                            $wc->execute();
                            echo '<select name="sy" id="sy"  class="form-control" OnChange="selectSymbol(this.value);">';
                            echo '<option value=""> Select Symbol </option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['symbol_id'].'">';
                            echo $res['symbol'];
                            echo'</option>';
                            }
                            echo'</select>
                </div>
                <div  id="vol_avl">
                </div>
               
                <div class="col-xs-6">
                 <label>Remarks</label>
                  <input type="text" class="form-control" name="remarks" id="remarks" required>
                </div>';
    }else
    {
      echo '     <div class="col-xs-6">
                  <label>Details of Receiver Client</label>
                  <input type="text" class="form-control" style="color:red;" value="Invalid Client." readonly>
                </div>';
    }
}
elseif(!empty($_POST["sy"]) && !empty($_POST["acc"])) 
{
    $cd=$_POST['sy'];
    $acc=$_POST['acc'];
    $wc= $dbh->prepare("SELECT * from cds_holding  where symbol_id=:sy and cd_code=:acc ");
    $wc->bindParam(':sy',$cd);$wc->bindParam(':acc',$acc);
    $wc->execute();
    $state=$wc->fetch();
    if($state['volume'] == NULL ){$state['volume']=0;}else {$state['volume'] = $state['volume'];}
    echo '      <div class="col-xs-6">
                  <label>Available Volume</label>
                  <input type="text" class="form-control" value="'.$state['volume'].'" id="avl" readonly>
                </div>
                 <div class="col-xs-6">
                 <label>Transfer Volume</label>
                  <input type="number" max="'.$state['volume'].'" class="form-control" name="trs" id="trs" required>
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
    echo '      <div class="col-xs-4">
                  <label>Available Volume</label>
                  <input type="text" class="form-control" value="'.$state['volume'].'" id="avl1" readonly>
                </div>
                 <div class="col-xs-4">
                 <label>Pledge Volume</label>
                  <input type="number" max="'.$state['volume'].'" class="form-control" name="trs1" id="trs1" required>
                </div>';
}
elseif(!empty($_POST["sy1"]) && !empty($_POST["acc_pl_rl"])) 
{
    $sy=$_POST['sy1'];
    $acc=$_POST['acc_pl_rl'];
    $cc=$_POST['cc'];

    $wc1= $dbh->prepare("SELECT sum(pledge_volume) as plv from cds_pledge where pledge_contract=:cc and symbol_id=:id");
    $wc1->bindParam(':cc',$cc);
    $wc1->bindParam(':id',$sy);
    $wc1->execute();
    $state1=$wc1->fetch();
    $sum=$state1['plv'];
    echo '      
                <input type="hidden" class="form-control" value="'.$sy.'"  name= "sy" id="sy" >
                <div class="col-xs-3">
                  <label>Pledged Volume</label>
                  <input type="text" class="form-control" value="'.$sum.'" name= "pl_vol" id="pl_vol" readonly>
                </div>
                 <div class="col-xs-3">
                 <label>Pledge Release Volume</label>
                  <input type="number" max="'.$sum.'" placeholder="Max. Vol. releasable  is : '.$sum.'" class="form-control" name="rls" id="rls" required>
                </div>                
                <div class="col-xs-9">
                 <label>Remarks</label>
                  <input type="text" class="form-control" name="remarks" id="remarks" required>
                </div>';
}
elseif(!empty($_POST["pl_release"]))
{
    $cc=$_POST['pl_release'];
    $wc= $dbh->prepare("SELECT c.*,pn.pledge_name,pn.pledgee from  client_account c,cds_pledge_contract pn
                        where pn.pledge_contract=:cc and c.cd_code=pn.cd_code");
    $wc->bindParam(':cc',$cc);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0)
    {
    echo '     
               <input type="hidden" class="form-control" value="'.$state['cd_code'].'"  name= "ac" id="ac" >
               <input type="hidden" class="form-control" value="'.$state['pledge_name'].'"  name= "pname" id="pname" >
               <div class="col-xs-6">
                  <label>Pledge Name</label>
                  <input type="text" class="form-control" value="'.$state['pledge_name'].'"  readonly>
                </div>
                <div class="col-xs-3">
                  <label>Pledger Name</label>
                  <input type="text" class="form-control" value="Mr./Mrs : '.$state['f_name'].' '.$state['l_name'].'" name= "pln" id="pln" readonly>
                </div>
                <div class="col-xs-3">
                  <label>CD Code</label>
                  <input type="text" class="form-control" value="'.$state['cd_code'].'"  name= "ac1" id="ac1" readonly>
                </div>
                <div class="col-xs-3">
                  <label>Pledgee</label>
                  <input type="text" class="form-control" value="'.$state['pledgee'].'" name= "pl" id="pl" readonly>
                </div>';

    }else
    {
      echo '    <div class="col-xs-6">
                  <label>Message</label>
                  <input type="text" class="form-control" style="color:red;" value="Contract code doesnt exist." name= "pln" id="pln" readonly>
                </div>';

    }
}
elseif(!empty($_POST["syd"]) && !empty($_POST["accd"])) 
{
    $cd=$_POST['syd'];
    $acc=$_POST['accd'];
    $wc= $dbh->prepare("SELECT * from cds_holding  where symbol_id=:sy and cd_code=:acc ");
    $wc->bindParam(':sy',$cd);$wc->bindParam(':acc',$acc);
    $wc->execute();
    $state=$wc->fetch();
    if($state['volume'] == NULL ){$state['volume']=0;}else {$state['volume'] = $state['volume'];}
    echo '      <div class="col-xs-4">
                  <label>Available Volume</label>
                  <input type="text" id="available_volume" class="form-control" value="'.$state['volume'].'" readonly>
                </div>';
}
elseif(!empty($_POST["sydunblock"]) && !empty($_POST["accd_block"])) 
{
    $cd=$_POST['sydunblock'];
    $acc=$_POST['accd_block'];
    $wc= $dbh->prepare("SELECT * from cds_holding  where symbol_id=:sy and cd_code=:acc ");
    $wc->bindParam(':sy',$cd);$wc->bindParam(':acc',$acc);
    $wc->execute();
    $state=$wc->fetch();
    if($state['volume'] == NULL ){$state['volume']=0;}else {$state['volume'] = $state['volume'];}
    echo '      <div class="col-xs-4">
                  <label>Max Volume Blockable</label>
                  <input type="text" id="" class="form-control" value="'.$state['volume'].'" readonly>
                  <input type="hidden" id="available_volume_to_block" class="form-control" value="'.$state['volume'].'" readonly>
                </div>
                <div class="col-xs-4">
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
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["accs"])) {
  $toDate = $_POST['toDate'].' 23:59:00';
  $fromDate = $_POST['fromDate'].' 00:00:00';

  echo '
  <div class="table-responsive" width="100%">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>CD Code</th>
          <th>Name</th>
          <th>CID</th>
          <th>MEMBER BROKER</th>
        </tr>
      </thead>
      <tbody>';
      $query = $dbh->prepare("SELECT client_id, cd_code, f_name, l_name, ID, phone, user_name 
        FROM client_account 
        WHERE ca_date BETWEEN :fdate AND :tdate 
        ORDER BY ca_date DESC");
      $query->bindParam(':fdate', $fromDate);
      $query->bindParam(':tdate',$toDate);
      $query->execute();
      $i=1;
      while($result = $query->fetch(PDO::FETCH_ASSOC)) {
        echo '
        <tr>
          <td> '.$i.'</td>
          <td> '.$result['cd_code'].'</td>
          <td> '.$result['f_name'].' '.$result['l_name'].'</td>
          <td> '.$result['ID'].'</td>
          <td> '.substr($result['user_name'], 0, 7).'</td>
        </tr>';
        $i++;
      }
      $query->closeCursor();
      echo '
      </tbody>
    </table>
  </div>';
  die();
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"]) && !empty($_POST["blockshow"])) 
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
                  <th>Date</th>
                </tr>
                </thead>
                <tbody>';
                $query= $dbh->prepare('SELECT a.*,b.f_name,b.l_name,c.symbol from cds_dep_wit a,client_account b,symbol c where a.cd_code=b.cd_code and
                 a.symbol_id=c.symbol_id and a.type="BLOCK/UNBLOCK" and a.entry_date >= :fdate and a.entry_date  <= :tdate');
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
                          echo '<td style="color:red;"> '.$result['volume'].' Blocked </td>';
                          echo '<td> '.$result['entry_date'].'</td>';
                          echo '</tr>';
                    }
                    else
                    {
                          echo '<tr>';
                          echo '<td> '.$i++.'</td>';
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td> '.$result['f_name'].' '.$result['l_name'].'</td>';
                          echo '<td> '.$result['symbol'].'</td>';
                          echo '<td style="color:green;"> '.$result['volume'].' Unblocked</td>';
                          echo '<td> '.$result['entry_date'].'</td>';
                          echo '</tr>';
                    }
                  }
                $query->closeCursor();
                echo '</tbody>
              </table></div>
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
                <div class="box-header" style="font:8px;">
                  <h4 class="box-title"><i class="fa fa-edit"></i><i class="fa fa-trash-o"></i></h4>
                </div>
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
          echo' <div class="row">
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
      
                $query= $dbh->prepare('SELECT a.*,c.symbol from cds_pledge a,symbol c where a.symbol_id=c.symbol_id and a.pledge_volume > 0 and
                 a.pledge_date >= :fdate and a.pledge_date <= :tdate');
                //$query->bindParam(':un',$_SESSION['sess_username']);
                $query->bindParam(':fdate',$fromDate);
                $query->bindParam(':tdate',$toDate);
                $query->execute();
                $i=1;
                while($result=$query->fetch(PDO::FETCH_ASSOC))
                 {
                          echo '<tr>';
                          echo '<td> '.$i++.'</td>';
                          echo '<td> '.$result['pledge_contract'].'</td>';
                          echo '<td> '.$result['symbol'].'</td>';
                          echo '<td> '.$result['pledge_volume'].'</td>';
                          echo '<td> '.$result['pledgee'].'</td>';
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td><button  data-toggle="modal" data-target="#myModal" name="edit_plg" id="edit_plg"  value="'.$result['pledge_id'].'" onClick="getState(this.value);">
                          <i class="fa fa-edit"></i></button></td>';
                          echo '</tr>';
                  }
                $query->closeCursor();
                echo '</tbody>
              </table></div>
            </div>
          </div>
        </div>
      </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"])  && !empty($_POST["pledge_contract"]) ) 
{
           $toDate = $_POST['toDate'].' 23:59:00';
           $fromDate = $_POST['fromDate'].' 00:00:00';
          echo' <div class="row">
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
                  <th>CD Code</th>
                  <th>Pledgee</th>
                  <th>Edit</th>
                </tr>
                </thead>
                <tbody>';
      
                $query= $dbh->prepare('SELECT * from cds_pledge_contract where pledge_date >= :fdate and pledge_date <= :tdate');
                //$query->bindParam(':un',$_SESSION['sess_username']);
                $query->bindParam(':fdate',$fromDate);
                $query->bindParam(':tdate',$toDate);
                $query->execute();
                $i=1;
                while($result=$query->fetch(PDO::FETCH_ASSOC))
                 {
                          echo '<tr>';
                          echo '<td> '.$i++.'</td>';
                          echo '<td> '.$result['pledge_contract'].'</td>';
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td> '.$result['pledgee'].'</td>';
                          echo '<td><button  data-toggle="modal" data-target="#myModal" name="edit_plg_contra" id="edit_plg_contra"  value="'.$result['cds_pledge_contract_id'].'" onClick="getState(this.value);">
                          <i class="fa fa-edit"></i></button></td>';
                          echo '</tr>';
                  }
                $query->closeCursor();
                echo '</tbody>
              </table></div>
            </div>
          </div>
        </div>
      </div>';
}
elseif(!empty($_POST["toDate"]) && !empty($_POST["fromDate"])  && !empty($_POST["pledge_release"]) ) 
{
           $toDate = $_POST['toDate'].' 23:59:00';
           $fromDate = $_POST['fromDate'].' 00:00:00';
          echo' <div class="row">
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
      
                $query= $dbh->prepare('SELECT a.*,c.symbol from cds_pledge a,symbol c where a.symbol_id=c.symbol_id and a.pledge_volume < 0 and :fdate <= a.pledge_date and a.pledge_date <= :tdate');
                /*$query->bindParam(':un',$_SESSION['sess_username']);*/
                $query->bindParam(':fdate',$fromDate);
                $query->bindParam(':tdate',$toDate);
                $query->execute();
                $i=1;
                while($result=$query->fetch(PDO::FETCH_ASSOC))
                 {
                          echo '<tr>';
                          echo '<td> '.$i++.'</td>';
                          echo '<td> '.$result['pledge_contract'].'</td>';
                          echo '<td> '.$result['symbol'].'</td>';
                          echo '<td> '.$result['pledge_volume'].'</td>';
                          echo '<td> '.$result['pledgee'].'</td>';
                          echo '<td> '.$result['cd_code'].'</td>';
                          echo '<td><button  data-toggle="modal" data-target="#myModal" name="edit_cli" id="edit_cli"  value="'.$result['pledge_id'].'" onClick="getState(this.value);"><i class="fa fa-edit"></i></button></td>';
                          echo '</tr>';
                  }
                $query->closeCursor();
                echo '</tbody>
              </table></div>
            </div>
          </div>
        </div>
      </div>';
}
elseif(!empty($_POST["dDate"])) 
{
           $ddDate = $_POST['dDate'];
           $dDate = date("d-m-Y", strtotime($ddDate));
          echo' <div class="row">
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
                  <th>File Name</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>';
      
                $query= $dbh->prepare('SELECT * FROM backupsr WHERE name=:ddate');
                $query->bindParam(':ddate',$dDate);
                $query->execute();
                $result = $query->fetch();
                if($result == NULL)
                {
                  echo '<tr>';
                          echo '<td style="color:red">NO data</td>';
                          echo '<td></td>';
                          echo '</tr>';

                }
                else
                {
                  echo '<tr>';
                          echo '<td> '.$result['name'].'</td>';
                          echo '<td><a href="'.$result['link'].'" >Download</a></td>';
                          echo '</tr>';

                }
                          
                $query->closeCursor();
                echo '</tbody>
              </table></div>
            </div>
          </div>
        </div>
      </div>';
}
elseif(!empty($_POST["get_user_account_dtls"])) 
{
  $cidNo = $_POST['cidNo'];

  $stmt = $dbh->prepare("SELECT u.participant_code, u.username, u.cid, u.phone, u.email, u.created_at, u.name
      FROM users u 
      WHERE u.cid =  ?
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
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($rows as $res) {
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
            <td>
              <button type="button" class="btn btn-primary" onclick="reset_lock(\''.$res['username'].'\')"><i class="fa fa-unlock"></i> Unlock</button>
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
            dataType: "html",
            success: function(data){
              hideloading();
              alert(data);
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
}
else
{  
}
?>



<script type="text/javascript">
function getState3(val) 
{
  $.ajax({ type: "POST", url: "load.php", data:'cdCode='+val,success: function(data){ $("#cd1").html(data);} });
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

