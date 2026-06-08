<?php 
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');
date_default_timezone_set("Asia/Thimphu");
session_start();
$role = $_SESSION['sess_userrole'];
if( $role!="6")
{
  header('Location: ../../access.php?err=2');
  exit();
}
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
  { 
    header("Location: ../../Authentication/Logout.php"); 
    exit();
  }
} 
$_SESSION['timeout'] = time();
$username=$_SESSION['sess_username'];
$check= $dbh->prepare('SELECT a.institution_id from adm_institution a, adm_participants b,users c where c.participant_code=b.participant_code and b.institution_id=a.institution_id and c.username=:un');
$check->bindParam(':un',$username);
$check->execute();
$res=$check->fetch();
$institution_id=$res['institution_id'];
//Saving Record
if (isset($_POST['save_client']) && isset($_POST['accno']))
{ 
//variable declaration  
$atype = clean($_POST['atype']);
if($atype=="I")
{
  $cdcode=clean($_POST['cdcode']);
}
elseif($atype=="J")
{
  $cdcode=clean($_POST['cdcodeAC']);
}
$title = clean($_POST['title']);
$fn = clean($_POST['fn']);
$ln=clean($_POST['ln']);
$occupation = clean($_POST['occupation']);
$nat = clean($_POST['nat']);
$id=clean($_POST['id']);
$dz=clean($_POST['dz']);
$tpn=clean($_POST['tpn']);
$phone=clean($_POST['phone']);
$email = clean($_POST['email']);
$bank=clean($_POST['bank']);
$accno=clean($_POST['accno']);
$bankAccType=clean($_POST['bankAccType']);
$add=clean($_POST['add']);
$username=clean($_POST['save_client']);
// !- variable declaration  
//nominee start
if($atype=="I")
{
$n = clean($_POST['name']);
$c = clean($_POST['cid']);
$r = clean($_POST['relationship']);
for($i=0;$i<count($_POST['name']); $i++)
{
 $id=clean($_POST['id']);
 $names = clean($_POST['name'][$i]);
 $cid = clean($_POST['cid'][$i]);
 $relationship = clean($_POST['relationship'][$i]);
 $save = $dbh->prepare("INSERT INTO client_nominee(Nominee_name,Nominee_cid,Nominee_relation,ID) VALUES ('$names','$cid','$relationship','$id')");
 $save->execute();
}
}
else
{
}
//nominee end
$check= $dbh->prepare('SELECT institution_id,cd_code,ID from client_account  where institution_id=:ii and ID=:id or cd_code=:cd');
$check->bindParam(':ii',$institution_id);$check->bindParam(':id',$id);$check->bindParam(':cd',$cdcode);
$check->execute();
$res=$check->fetch();
    if($check->rowCount() == 0)
    {
        $save = $dbh->prepare("INSERT INTO client_account (acc_type,cd_code,title,f_name,l_name,occupation,nationality,ID,DzongkhagID,tpn,phone,email,bank_id,bank_account,bank_account_type,address,institution_id,user_name)
          VALUES ('$atype','$cdcode','$title','$fn','$ln','$occupation','$nat','$id','$dz','$tpn','$phone','$email','$bank','$accno','$bankAccType','$add','$institution_id','$username')");
        if($save->execute()) 
         {
            header('location: ../FILES/cds-css-landing.php?ms=1');
            exit();
         }  
        else 
        {
            header('location: ../FILES/cds-css-landing.php?ms=2');
            exit();
        }
      }
      else
      {
           header('location: ../FILES/cds-css-landing.php?ms=4');
           exit();
      } 
}
elseif (isset($_POST['edit_cli']))
{ 
//variable declaration  
$atype = clean($_POST['atype']);
$title = clean($_POST['title']);
$fn = clean($_POST['fn']);
$ln=clean($_POST['ln']);
$occupation=clean($_POST['occupation']);
$nat = clean($_POST['nat']);
$dz=clean($_POST['dz']);
$tpn=clean($_POST['tpn']);
$phone=clean($_POST['phone']);
$email = clean($_POST['email']);
$bank=clean($_POST['bank']);
$accno=clean($_POST['accno']);
$bankAccType=clean($_POST['bankAccType']);
$add=clean($_POST['add']);
$cli_id=clean($_POST['edit_cli']);
$id=clean($_POST['id']);
// !- variable declaration   

//nominee start
if($atype=="I")
{
$n = clean($_POST['name']);
$c = clean($_POST['cid']);
$r = clean($_POST['relationship']);
for($i=0;$i<count($_POST['name']); $i++)
{
 $names = clean($_POST['name'][$i]);
 $cid = clean($_POST['cid'][$i]);
 $relationship = clean($_POST['relationship'][$i]);
 $id=clean($_POST['id']);
 $save = $dbh->prepare("INSERT INTO client_nominee(Nominee_name,Nominee_cid,Nominee_relation,ID) VALUES ('$names','$cid','$relationship','$id')");
 $save->execute();
}
}else{}
//nominee end
$save = $dbh->prepare("UPDATE client_account 
  SET acc_type=:atype,title=:title,f_name=:fn,
  l_name=:ln,occupation=:occupation,nationality=:nat,
  DzongkhagID=:dz,tpn=:tpn,phone=:phone,email=:email,
  bank_id=:bank,bank_account=:accno,bank_account_type=:bankAccType,
  address=:add 
  where client_id=:id");
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
$save->bindParam(':id',$cli_id);
   if($save->execute())
      {
          header('location: ../FILES/cds-css-landing.php?ms=3');
          exit();
      }  
   else
      {
          header('location: ../FILES/cds-css-landing.php?ms=2');
          exit();
      }  
}
elseif (isset($_POST['delete_cli']))
{ 
//variable declaration  
$client_id=clean($_POST['delete_cli']);
// !- variable declaration   
$save = $dbh->prepare("DELETE from  client_account where client_id=:id ");
$save->bindParam(':id',$client_id);
   if($save->execute())
      {
          header('location: ../FILES/cds-css-landing.php?ms=5');
          exit();
      }  
   else
      {
           header('location: ../FILES/cds-css-landing.php?ms=2');
           exit();
      }  
}
elseif (isset($_POST['delete_nom']))
{ 
//variable declaration  
$id=clean($_POST['delete_nom']);
// !- variable declaration   
$save = $dbh->prepare("DELETE from  client_nominee where nominee_id=:id ");
$save->bindParam(':id',$id);
    if($save->execute())
      {
           echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp Record Deleted Successfully.</div></div></div>';
          exit();
      }  
   else
      {
           echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
           exit();
      }   
}
//bbo finance start
elseif (isset($_POST['mat']))
{ 
//variable declaration  
$cd_code=clean($_POST['cdcode']);
$sy=clean($_POST['sy']);
$hol=clean($_POST['hol']);
$rm=clean($_POST['rm']);
$type='DEPOSIT';
$cd_code=strtoupper($cd_code);
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:sy");  
$q->bindParam(':cd',$cd_code);
$q->bindParam(':sy',$sy);
$q->execute();
if($q->rowCount() > 0){
   $row=$q->fetch();
   $e_vol=$row['volume'];
   $new_vol=$e_vol+$hol;

   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$cd_code','$sy','$hol','$username','$institution_id','$rm','$type')");
   if($saveT->execute())
   {
       $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol where cd_code=:cd and symbol_id=:sy");
       $save->bindParam(':vol',$new_vol);
       $save->bindParam(':cd',$cd_code);
       $save->bindParam(':sy',$sy);

       if($save->execute()){
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
              //header('location: ../FILES/cds-css-landing.php?ms=1');
              exit();}  
       else{
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
              //header('location: ../FILES/cds-css-landing.php?ms=2');
              exit();}
   }else
   {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
      exit();
   }
}
else{

    $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$cd_code','$sy','$hol','$username','$institution_id','$rm','$type')");
    if($saveT->execute())
    {
            $save = $dbh->prepare("INSERT into cds_holding (cd_code,volume,user_name,institution_id,symbol_id,remarks) 
                             VALUES ('$cd_code','$hol','$username','$institution_id','$sy','$type')");
            if($save->execute()){
                echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
                exit();}  
            else{
                echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
                exit();}
    }else
    {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
    }    
    }
}
elseif (isset($_POST['demat']))
{ 
//variable declaration  
//variable declaration  
$cd_code=clean($_POST['cdcode']);
$sy=clean($_POST['sy']);
$hol=clean($_POST['hol']);
$rm=clean($_POST['rm']);
$type='WITHDRAW';
// !- variable declaration
$q=$dbh->prepare("SELECT a.*,b.symbol from cds_holding a,symbol b where a.cd_code=:cd and a.symbol_id=:sy and a.symbol_id=b.symbol_id");  
$q->bindParam(':cd',$cd_code);
$q->bindParam(':sy',$sy);
$q->execute();
if($q->rowCount() > 0){
   $row=$q->fetch();
   $e_vol=$row['volume'];
   $symbol=$row['symbol'];
   $new_vol=$e_vol-$hol;
   if($new_vol >= 0){

   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$cd_code','$sy','-$hol','$username','$institution_id','$rm','$type')");
   if($saveT->execute())                                                        
   {
    $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol where cd_code=:cd and symbol_id=:sy");
    $save->bindParam(':vol',$new_vol);
    $save->bindParam(':cd',$cd_code); 
    $save->bindParam(':sy',$sy);
    $row=$save->execute();
    if($save->execute())
        {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
          exit();}  
    else{
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
          exit();
       }    
   }
   else
   {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
          exit();
   }
   }
    elseif ($new_vol < 0){
          //header('location: ../FILES/cds-css-landing.php?ms=6&cd='.$cd_code.'&e_vol='.$e_vol.'&sy='.$symbol);
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp The Client ,'.$cd. ' has only '.$e_vol.' , of '.$sy.'</div></div></div>';
          exit();}
    }
else{
    
    $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks)VALUES('$cd_code','$sy','-$hol','$username','$institution_id','$rm')");
    if($saveT->execute())
    {
        $save = $dbh->prepare("INSERT into cds_holding (cd_code,volume,user_name,institution_id,symbol_id) 
                       VALUES ('$cd_code','$hol','$username','$institution_id','$sy')");
        if($save->execute())
         {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
          exit();}  
        else{
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
              exit();
           } 
    }
    else
    {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
    }    
}
}
//transfer start
elseif (isset($_POST['transfer']))
{ 
//variable declaration  
$F_cd=clean($_POST['F_cd']);
$T_cd=clean($_POST['T_cd']);
$sy=clean($_POST['sy']);
$rm=clean($_POST['remarks']);
$vol=clean($_POST['trs']);
$user_name=clean($_POST['userName']);
// !- variable declaration
  $save = $dbh->prepare("INSERT into cds_transfer (from_acc,to_acc,symbol_id,trs_vol,remarks,user_name) VALUES 
            ('$F_cd','$T_cd','$sy','$vol','$rm','$user_name')");
  if($save->execute())
    {
        $q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
        $q->bindParam(':cd',$F_cd);
        $q->bindParam(':id',$sy);
        if($q->execute())
        {
           $row=$q->fetch();
           $F_e_vol=$row['volume'];
           $F_vol=$F_e_vol-$vol;

           $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol where cd_code=:cd and symbol_id=:id ");
           $save->bindParam(':cd',$F_cd);
           $save->bindParam(':vol',$F_vol);
           $save->bindParam(':id',$sy);
           $save->execute();

           $q2=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id");  
           $q2->bindParam(':cd',$T_cd);
           $q2->bindParam(':id',$sy);
           if($q2->execute())
           {
             if($q2->rowCount() > 0){
                  $row=$q2->fetch();
                  $T_e_vol=$row['volume'];
                  $T_vol=$vol+$T_e_vol;
                  $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol where cd_code=:cd and symbol_id=:id ");
                  $save->bindParam(':cd',$T_cd);
                  $save->bindParam(':vol',$T_vol);
                  $save->bindParam(':id',$sy);
                  if($save->execute())
                  {
                  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
                  exit();
                  }
                  
               }
               else
               {
                 $save = $dbh->prepare("INSERT into cds_holding (cd_code,volume,symbol_id,user_name,institution_id) VALUES 
                  ('$T_cd','$vol','$sy','$username','$institution_id')");
                 if($save->execute())
                  {
                  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check">
                   </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
                  exit();
                  }
               }
            }
            else
            {
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            }
        }
        else
        { 
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        }                           
    }else{
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
    }                  
}
//transfer end
//pledge contract start
elseif (isset($_POST['pledge_contract']))
{ 
//variable declaration  
$cc=clean($_POST['cc']);
$pl=clean($_POST['pl']);
$ac=clean($_POST['ac']);
$rm=clean($_POST['remarks']);
$user_name=clean($_POST['userName']);
$pl_name='Pledge by CD Code holder : '.$ac. ', with Pledge Contract Code :'. $cc .' , On :'.date('d-m-Y').' with : '.$pl;
// !- variable declaration
 $save = $dbh->prepare("SELECT count(pledge_contract) as rs from cds_pledge_contract where pledge_contract=:cc");
 $save->bindParam(':cc',$cc);
 $res=$save->fetch();
      if($res['rs'] <= 0){
      $save = $dbh->prepare("INSERT into cds_pledge_contract (pledge_name,pledge_contract,pledgee,cd_code,remarks,user_name) VALUES ('$pl_name','$cc','$pl','$ac','$rm','$user_name')");
      if($save->execute()){
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
            exit();}  
      else{
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            exit();}
          }
      else{
           echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">  
           </i> Message!&nbsp;&nbsp Oops Sorry! Cannot Create Pledge Contract with same code.</div></div></div>';
            exit();}    
}
//pledge  end
//pledge start
elseif (isset($_POST['pledge']))
{ 
//variable declaration  
$cc=clean($_POST['cc']);
$pl=clean($_POST['pl']);
$ac=clean($_POST['ac']);
$sy=clean($_POST['sy']);
$rm=clean($_POST['remarks']);
$vol_pl=clean($_POST['trs1']);
$user_name=clean($_POST['userName']);
$type='PLEDGE';
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$q->bindParam(':cd',$ac);$q->bindParam(':id',$sy);
$q->execute();
$row=$q->fetch();
$P_vol=$row['pledge_volume'];
$avl_vol=$row['volume'];
$new_pl_vol=$P_vol+$vol_pl;
$new_avl_vol=$avl_vol-$vol_pl;


   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$ac','$sy','$vol_pl','$username','$institution_id','$rm','$type')");
   if($saveT->execute())
   {
    $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id ");
    $save->bindParam(':cd',$ac);
    $save->bindParam(':vol',$new_avl_vol);
    $save->bindParam(':pl_vol',$new_pl_vol);
    $save->bindParam(':id',$sy);
    if($save->execute())
    {
      $save = $dbh->prepare("INSERT into cds_pledge (pledge_contract,pledgee,cd_code,symbol_id,pledge_volume,remarks,user_name) VALUES ('$cc','$pl','$ac','$sy','$vol_pl','$rm','$user_name')");
      if($save->execute()){
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
            exit();}  
      else{
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            exit();}

    }else
    {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
    }
   }else
   {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
   }
}
//pledge  end
//pledge edit start
elseif (isset($_POST['edit_plg']))
{
//variable declaration  
$cc=clean($_POST['cc']);
$pl=clean($_POST['pl']);
$ac=clean($_POST['ac']);
$sy=clean($_POST['sy']);
$vol_pl=clean($_POST['trs']);
$old_pl_vol=clean($_POST['old_pl_vol']);
$pl_id=clean($_POST['edit_plg']);
$rm="Pledge Edited for pledge Contract ". $cc;
$type="PLEDGE EDIT";
//$user_name=clean($_POST['pledge']);
// !- variable declaration

$q=$dbh->prepare("SELECT symbol_id from symbol where symbol=:sy ");  
$q->bindParam(':sy',$sy);
$q->execute();
$ro=$q->fetch();
$sy_id = $ro['symbol_id'];

$r=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$r->bindParam(':cd',$ac);
$r->bindParam(':id',$sy_id);
$r->execute();
$row=$r->fetch();

$P_vol=$row['pledge_volume'];
$avl_vol=$row['volume'];

//$new_pl_vol=$P_vol-$old_pl_vol+$vol_pl;
$new_pl_vol=$vol_pl;

$updated_avl_vol=$avl_vol+$old_pl_vol;
$new_avl_vol=$updated_avl_vol-$vol_pl;
if($new_avl_vol >= 0)
{
   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$ac','$sy_id','$vol_pl','$username','$institution_id','$rm','$type')");
   if($saveT->execute())
   {
     $up_pl=$dbh->prepare("UPDATE cds_pledge SET pledge_volume=:vol_pl,pledgee=:pl where pledge_id=:pl_id");
     $up_pl->bindParam(':vol_pl',$vol_pl);
     $up_pl->bindParam(':pl_id',$pl_id);
     $up_pl->bindParam(':pl',$pl);
     if($up_pl->execute())
     {
       $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id ");
       $save->bindParam(':cd',$ac);
       $save->bindParam(':vol',$new_avl_vol);
       $save->bindParam(':pl_vol',$new_pl_vol);
       $save->bindParam(':id',$sy_id);
       if($save->execute())
       {
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
              exit();
       }else
       {
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
              exit();
       }
     }else
     {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
     }
   }else
   {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
   }  
}else
{
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Pledge volume cannot be more than available volume.</div></div></div>';
    exit();
}                    
}
//pledge edit end
//pledge release start
elseif (isset($_POST['pledge_release']))
{ 
//variable declaration 
$cc=clean($_POST['cc']);
$pl=clean($_POST['pl']);
$ac=clean($_POST['ac']);
$sy=clean($_POST['sy']);
$pname=clean($_POST['pname']);
$rm=clean($_POST['remarks']);
$vol_pl_rls=clean($_POST['rls']);
$user_name=clean($_POST['userName']);
$type="PLEDGE RELEASE";
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$q->bindParam(':cd',$ac);$q->bindParam(':id',$sy);
if($q->execute())
{
  $row=$q->fetch();
  $P_vol=$row['pledge_volume'];
  $avl_vol=$row['volume'];
  $new_pl_vol=$P_vol-$vol_pl_rls;
  $new_avl_vol=$avl_vol+$vol_pl_rls;

   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$ac','$sy','$vol_pl_rls','$username','$institution_id','$rm','$type')");
   if($saveT->execute())
   {
     $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id ");
     $save->bindParam(':cd',$ac);
     $save->bindParam(':vol',$new_avl_vol);
     $save->bindParam(':pl_vol',$new_pl_vol);
     $save->bindParam(':id',$sy);
     $save->execute();

     $save = $dbh->prepare("INSERT into cds_pledge (pledge_contract,pledgee,cd_code,symbol_id,pledge_volume,remarks,user_name,pledge_name) VALUES ('$cc','$pl','$ac','$sy','-$vol_pl_rls','$rm','$user_name','$pname')");
     if($save->execute()){
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
            exit();}  
     else{
            echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            exit();}

   }else
   {
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            exit();
   }
}else
{
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
            exit();
}
}
//pledge release end
//pledge release edit start
elseif (isset($_POST['edit_plg_rls']))
{ 
//variable declaration  
$cc=clean($_POST['cc']); 
//$pl=clean($_POST['pl']);
$ac=clean($_POST['ac']);
$sy=clean($_POST['sy']);
$vol_pl=clean($_POST['trs']);
$old_pl_vol=clean($_POST['old_pl_vol']) * -1;
$pl_id=clean($_POST['pl_id']);
$rm="Pledge release edited for contract code:  ".$cc." , and pledge id : ".$pl_id;
$type="PLEDGE RELEASE EDIT";
//$user_name=clean($_POST['pledge']);
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$q->bindParam(':cd',$ac);
$q->bindParam(':id',$sy);
$q->execute();
$row=$q->fetch();

$P_vol=$row['pledge_volume'];
$avl_vol=$row['volume'];
$new_pl_vol=$P_vol-$old_pl_vol+$vol_pl;
$new_cds_pl_vol=$vol_pl*-1;
//$updated_avl_vol=$avl_vol+$old_pl_vol;
$new_avl_vol=$avl_vol+$old_pl_vol-$vol_pl;
if($new_avl_vol >= 0){
   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$ac','$sy','-$vol_pl','$username','$institution_id','$rm','$type')");
   if($saveT->execute())
   {
     $up_pl=$dbh->prepare("UPDATE cds_pledge SET pledge_volume=:vol_pl where pledge_id=:pl_id");
     $up_pl->bindParam(':vol_pl',$new_cds_pl_vol);
     $up_pl->bindParam(':pl_id',$pl_id);
     if($up_pl->execute())
     {
       $save = $dbh->prepare("UPDATE cds_holding SET volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id ");
       $save->bindParam(':cd',$ac);
       $save->bindParam(':vol',$new_avl_vol);
       $save->bindParam(':pl_vol',$new_pl_vol);
       $save->bindParam(':id',$sy);

       if($save->execute())
       {
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
              exit();                          }  
       else{
              echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
              exit();}

     }else
     {
        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();

     }
   }else
   {
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
      exit();

   }
  }
   else{
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Pledge release volume cannot be more than available release volume.</div></div></div>';
          exit();
   }                     
}
//pledge release edit end
//corporate announcement start
elseif (isset($_POST['save_corporate_announcement']))
{ 
//variable declaration 
$symbol = clean($_POST['sy']);
$record_date = clean($_POST['record_date']);
$exdate = clean($_POST['exdate']);
$announcement_date=clean($_POST['announcement_date']);
$rate = clean($_POST['rate']);
$type=clean($_POST['type']);
$announcement_type=clean($_POST['announcement_type']);
$status=1;
// !- variable declaration 
$save = $dbh->prepare("INSERT into corporate_announcement (symbol_id,record_date,ex_date,announcement_date,rate,type,modifier_username,announcement_type,status) VALUES
('$symbol','$record_date','$exdate','$announcement_date','$rate','$type','$username','$announcement_type','$status')");
if($save->execute()){
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
  //header('location: ../FILES/cds-css-landing.php?ms=1');
exit();
} 
else{
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
  //header('location: ../FILES/cds-css-landing.php?ms=2');
exit();
}
}
//corporate announcement end
//pledgee start
elseif (isset($_POST['save_pledge']))
{
//variable declaration 
$pledgee_name = clean($_POST['pledgee_name']);
$address = clean($_POST['address']);

// !- variable declaration 
$save = $dbh->prepare("INSERT into cds_pledgee (pledgee,address) VALUES ('$pledgee_name','$address')");
if($save->execute())
{
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
exit();
} 
else{
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
exit();
} 
}
//pledgee end
//bank start
elseif (isset($_POST['save_bank']))
{
//variable declaration 
$bank = clean($_POST['bank_name']);
// !- variable declaration 
$save = $dbh->prepare("INSERT into banks (bank_name) VALUES ('$bank')");
if($save->execute()){
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
exit();
} 
else{
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
exit();
} 
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
$save->bindParam(':bank_name',$bank);
$save->bindParam(':id',$bank_id);
$save->execute(); 
}
//bank edit end
//bank branch start
elseif (isset($_POST['save_branch']))
{
//variable declaration 
$bank_id = clean($_POST['bank_id']);
$branch_name = clean($_POST['branch_name']);
$branch_address = clean($_POST['branch_address']);
// !- variable declaration 
$save = $dbh->prepare("INSERT into bank_branch (BANK_ID,BRANCH_NAME,BRANCH_ADDRESS) VALUES ('$bank_id','$branch_name','$branch_address')");
if($save->execute()){
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
exit();
} 
else{
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
exit();
}
}
//bank branch end
//bank branch start
elseif (isset($_POST['save_occ']))
{
//variable declaration 
$occ_name = clean($_POST['occ_name']);
// !- variable declaration 
$save = $dbh->prepare("INSERT into occupation (occupation_name) VALUES ('$occ_name')");
if($save->execute()){
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
exit();
} 
else{
echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
exit();
} 
}
//bank branch end
//dividend start
elseif (isset($_POST['exe_corporate_announcement']))
{ 
//variable declaration  
$announcement_type = clean($_POST['announcement_type']);
$announcementId = clean($_POST['corp_announcement_id']);
// !- variable declaration 
if($announcement_type == 1){
$q=$dbh->prepare("SELECT ca.symbol_id,ca.record_date,ca.rate,cla.client_id,ch.volume,ch.pledge_volume,ch.block_volume,
ch.pending_out_vol,ca.corp_announcement_id from corporate_announcement ca,cds_holding ch,client_account cla where ca.symbol_id=ch.symbol_id and 
cla.cd_code=ch.cd_code and ca.corp_announcement_id=:aid");  
$q->bindParam(':aid',$announcementId);
$q->execute();
          $status=0;
          foreach($q as $row){
            $symbol_id=$row['symbol_id'];
            $rec_date=$row['record_date'];
            $client_id=$row['client_id'];
            $volume=$row['volume']+$row['pledge_volume']+$row['pending_out_vol']+$row['block_volume'];
            $corp_announcement_id=$row['corp_announcement_id'];
            $rate=$row['rate'];
            $new_vol=round($volume*$rate)/100;
            $save = $dbh->prepare("INSERT into spot_date_holding
                      (symbol_id,record_date,client_id,ribon_volume,volume,corp_announcement_id,announcement_type,status) VALUES
                      ('$symbol_id','$rec_date','$client_id','$new_vol','$volume','$corp_announcement_id','$announcement_type','$status')");
            $save->execute();
            //update corporate announceent status as 0
          }
                      $save = $dbh->prepare("UPDATE corporate_announcement SET status=0 where corp_announcement_id=:id");
                      $save->bindParam(':id',$announcementId);
                      if($save->execute())
                      {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
           exit();
                      }
                     else {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Something went wrong.</div></div></div>';
           exit();
                      }
           

}
elseif($announcement_type == 3){
$q=$dbh->prepare("SELECT ca.symbol_id,ca.record_date,cla.client_id,ch.volume,ch.pledge_volume,ch.block_volume,
ch.pending_out_vol,ca.corp_announcement_id from corporate_announcement ca,cds_holding ch,client_account cla where ca.symbol_id=ch.symbol_id and 
cla.cd_code=ch.cd_code and ca.corp_announcement_id=:aid");  
$q->bindParam(':aid',$announcementId);
$q->execute();
          $status=1;
          foreach($q as $row){
            $symbol_id=$row['symbol_id'];
            $rec_date=$row['record_date'];
            $client_id=$row['client_id'];
            $volume=$row['volume']+$row['pledge_volume']+$row['pending_out_vol']+$row['block_volume'];
            $corp_announcement_id=$row['corp_announcement_id'];
            $save = $dbh->prepare("INSERT into spot_date_holding (symbol_id,record_date,client_id,volume,corp_announcement_id,announcement_type,status) VALUES
                    ('$symbol_id','$rec_date','$client_id','$volume','$corp_announcement_id','$announcement_type','$status')");
            $save->execute();
            //update corporate announceent status as 0
          }
                      $save = $dbh->prepare("UPDATE corporate_announcement SET status=0 where corp_announcement_id=:id");
                      $save->bindParam(':id',$announcementId);
                      if($save->execute())
                      {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
           exit();
                      }
                     else {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Something went wrong.</div></div></div>';
           exit();
                      }
           

}
elseif($announcement_type == 2){
            $q=$dbh->prepare("SELECT ca.symbol_id,ca.record_date, ca.rate,cla.client_id,ch.cds_holding_id,ch.volume,ch.pledge_volume,ch.block_volume,
          ch.pending_out_vol,ca.corp_announcement_id from corporate_announcement ca,cds_holding ch,client_account cla where ca.symbol_id=ch.symbol_id and 
          cla.cd_code=ch.cd_code and ca.corp_announcement_id=:aid");  
          $q->bindParam(':aid',$announcementId);
          $q->execute();
                    $status=0;
                    foreach($q as $row){
                      $symbol_id=$row['symbol_id'];
                      $rec_date=$row['record_date'];
                      $client_id=$row['client_id'];
                      $cds_holding_id=$row['cds_holding_id'];
                      $rate=$row['rate'];
                      $volume=$row['volume']+$row['pledge_volume']+$row['pending_out_vol']+$row['pending_in_vol']+$row['block_volume'];
                      $corp_announcement_id=$row['corp_announcement_id'];
                      $new_vol=($volume*$rate)/100;
                      $new_vol= round($new_vol);

                      $save = $dbh->prepare("INSERT into spot_date_holding
                      (symbol_id,record_date,client_id,ribon_volume,volume,corp_announcement_id,announcement_type,status) VALUES
                      ('$symbol_id','$rec_date','$client_id','$volume','$new_vol','$corp_announcement_id','$announcement_type','$status')");
                      $save->execute();

                      /*$save = $dbh->prepare("UPDATE cds_holding SET volume=volume+:v where cds_holding_id=:clid");
                      $save->bindParam(':v',$new_vol);
                      $save->bindParam(':clid',$cds_holding_id);
                      $save->execute(); */
                    
                    }
                     $save = $dbh->prepare("UPDATE corporate_announcement SET status=0 where corp_announcement_id=:id");
                      $save->bindParam(':id',$announcementId);
                      if($save->execute())
                      {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
           exit();
                      }
                     else {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Something went wrong.</div></div></div>';
           exit();
                      }
}
elseif($announcement_type == 4){
            $q=$dbh->prepare("SELECT ca.symbol_id,ca.record_date, ca.rate,cla.client_id,ch.cds_holding_id,ch.volume,ch.pledge_volume,ch.block_volume,
          ch.pending_out_vol,ca.corp_announcement_id from corporate_announcement ca,cds_holding ch,client_account cla where ca.symbol_id=ch.symbol_id and 
          cla.cd_code=ch.cd_code and ca.corp_announcement_id=:aid");  
          $q->bindParam(':aid',$announcementId);
          $q->execute();
                    $status=0;
                    foreach($q as $row){
                      $symbol_id=$row['symbol_id'];
                      $rec_date=$row['record_date'];
                      $client_id=$row['client_id'];
                      $cds_holding_id=$row['cds_holding_id'];
                      $rate=$row['rate'];
                      $volume=$row['volume']+$row['pledge_volume']+$row['pending_out_vol']+$row['block_volume'];
                      $corp_announcement_id=$row['corp_announcement_id'];
                      $new_vol=round($volume*$rate)/100;

                      $save = $dbh->prepare("INSERT into spot_date_holding
                      (symbol_id,record_date,client_id,volume,corp_announcement_id,announcement_type,status) VALUES
                      ('$symbol_id','$rec_date','$client_id','$new_vol','$corp_announcement_id','$announcement_type','$status')");
                      $save->execute();

                      /*$save = $dbh->prepare("UPDATE cds_holding SET volume=volume-:v where cds_holding_id=:clid");
                      $save->bindParam(':v',$new_vol);
                      $save->bindParam(':clid',$cds_holding_id);
                      $save->execute();*/ 
                    
                    }
                     $save = $dbh->prepare("UPDATE corporate_announcement SET status=0 where corp_announcement_id=:id");
                      $save->bindParam(':id',$announcementId);
                      if($save->execute())
                      {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
           exit();
                      }
                     else {
                        echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible">
           <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
           <i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Something went wrong.</div></div></div>';
           exit();
                      }
}
}
elseif(isset($_POST['save_hol']))
{
  $hol_name = clean($_POST['hol_name']);
  $hol_date = clean($_POST['hol_date']);

  $save = $dbh->prepare("INSERT into holiday (holiday_date,hol_name) VALUES ('$hol_date','$hol_name')");
  if($save->execute()){
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
  exit();
  } 
  else{
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
  exit();
  }
}
//holiday end
//settlement cycle start
elseif(isset($_POST['save_sett']))
{
  $set_name = clean($_POST['set_name']);
  $set_day = clean($_POST['set_day']);
  $save = $dbh->prepare("INSERT into css_settlement_cycle (name,days) VALUES ('$set_name','$set_day')");
  if($save->execute()){
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
  exit();
  } 
  else{
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
  exit();
    }
}
//Settlement start
elseif(!empty($_POST["SETT"])) 
{
 echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Settlement Successful.</div></div></div>';exit(); 
}
elseif (isset($_POST['block_vol']))
{ 
    //variable declaration  
    $cd_code=clean($_POST['cdcode']);
    $symbol_id=clean($_POST['sy']);
    $bv=clean($_POST['block_vol']);
    $rm=clean($_POST['rm']);
    $user_name=clean($_POST['user_name']);
    $type='BLOCK/UNBLOCK';
    // !- variable declaration
   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$cd_code','$symbol_id','-$bv','$user_name','$institution_id','$rm','$type')");
   if($saveT->execute()){
    $save = $dbh->prepare("UPDATE cds_holding SET block_volume=block_volume+:bv,volume=volume-:bv where cd_code=:cd and symbol_id=:id ");
    $save->bindParam(':cd',$cd_code);
    $save->bindParam(':bv',$bv);
    $save->bindParam(':id',$symbol_id);
    if($save->execute()){
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
            exit();
    }else{
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
    }
   }else{
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
   }
}
elseif (isset($_POST['unblock_vol']))
{ 
    //variable declaration  
    $cd_code=clean($_POST['cdcode']);
    $symbol_id=clean($_POST['sy']);
    $ubv=clean($_POST['unblock_vol']);
    $rm=clean($_POST['rm']);
    $user_name=clean($_POST['user_name']);
    $type='UNBLOCK';
    // !- variable declaration
   $saveT = $dbh->prepare("INSERT into cds_dep_wit(cd_code,symbol_id,volume,user_name,institution_id,remarks,type)VALUES('$cd_code','$symbol_id','$ubv','$user_name','$institution_id','$rm','$type')");
   if($saveT->execute()){
    $save = $dbh->prepare("UPDATE cds_holding SET block_volume=block_volume-:ubv,volume=volume+:ubv where cd_code=:cd and symbol_id=:id ");
    $save->bindParam(':cd',$cd_code);
    $save->bindParam(':ubv',$ubv);
    $save->bindParam(':id',$symbol_id);
    if($save->execute()){
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
            exit();
    }else{
      echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
    }
   }else{
    echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban">    </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
        exit();
   }
}
//settlement end
elseif (isset($_POST['backup'])) {  
  $date = $_POST['cdate'];
  $link = '../../BACKUP/'.$date.''.'.xls'; 

  $insert = $dbh->prepare("INSERT INTO backupsr (name,link,created_by) VALUES ('$date','$link','$username')");
  $insert->execute();

  $sql = "SELECT symbol_id FROM symbol";
  $symbol = $dbh->query($sql);
  $symbol->execute();
  foreach ($symbol as $value) {
  $sid = $value['symbol_id']; 
  $back_up = $dbh->prepare("SELECT c.cd_code,a.f_name,a.tpn,a.phone,a.ID,a.address,a.bank_account,ban.bank_name,
                     c.volume+c.pledge_volume+c.block_volume+c.pending_out_vol 
                        as total, s.symbol  
                        from cds_holding c, client_account a,banks ban, symbol s  
                        where 
                        c.cd_code=a.cd_code and c.symbol_id=s.symbol_id
                        and c.symbol_id=:sid
                        and a.bank_id=ban.bank_id and (c.volume+c.pledge_volume+c.block_volume+c.pending_out_vol) !=0 order by a.bank_account asc");
   $back_up->bindParam(':sid',$sid);
   $back_up->execute();     
   $replace   = array("\n","\r\n","\r");
   $search  = array('','',''); 
   $columnHeader = '';  
   $i=1;
   $columnHeader = "CD CODE" . "\t". "NAME" . "\t". "TPN" . "\t". "PHONE" . "\t". "ADDRESS" . "\t". "ID" . "\t". "VOLUME" . "\t". "BANK Account" . "\t"."Security_Symbol" . "\t"; 
   $setData = '';

         while ($rec=$back_up->fetch()) { 
         if($back_up->rowCount() <= 0) 
         {}
          $rowData = '';  
          $value = str_replace($search,$replace,$rec['cd_code']) . "\t". str_replace($search,$replace,$rec['f_name'])
           . "\t". str_replace($search,$replace,$rec['tpn']) . "\t". str_replace($search,$replace,$rec['phone']) . 
           "\t". str_replace($search,$replace,$rec['address']) . "\t". str_replace($search,$replace,$rec['ID'])
            . "\t". str_replace($search,$replace,$rec['total']) . "\t". str_replace($search,$replace,trim($rec['bank_account']). " -") .
             "\t". str_replace($search,$replace,$rec['symbol']) . "\t";  
          $rowData .= $value;  
          $setData .= trim($rowData) . "\n";  
  }
  file_put_contents('C:/inetpub/wwwroot/RSEB/BACKUP/'.$date.'.xls',ucwords($columnHeader) . "\n" . $setData . "\n" . PHP_EOL, FILE_APPEND);
  } 
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Operation Successfully Completed.</div></div></div>';
  exit();
}
elseif(isset($_POST['bsiUpdate'])) {
  $divisor = $_POST["divisor"];
  $corpAction = $_POST["cp"];
  $remarks = $_POST["remarks"];
  $divisorNew = $_POST["divisorNew"];

  if ($divisorNew != '') {
    $divisor = $divisorNew;
  }

  try {
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    $query = $dbh->prepare("SELECT sum(s.paid_up_shares * m.market_price) sum 
        from market_price m, symbol s WHERE m.symbol_id = s.symbol_id and s.security_type='OS' and s.status=1");
    $query->execute();
    $mcap = $query->fetch();
    $sum = $mcap['sum'];

    $sumround = round($sum, 2);
    $BaseDivisor = $divisor;
    $Index = round($index1 = $sumround/$BaseDivisor, 2);

    $save = $dbh->prepare("INSERT INTO market_index(base, m_index, corpType, remarks, market_cap) VALUES(:baseDiv, :ind, :corAct, :remaks, :sumRnd)");
    $save->bindParam(":baseDiv", $BaseDivisor);
    $save->bindParam(":ind", $Index);
    $save->bindParam(":corAct", $corpAction);
    $save->bindParam(":remaks", $remarks);
    $save->bindParam(":sumRnd", $sumround);
    $save->execute();

    $dbh->commit();
    $dbh = null;

    header('location: ../FILES/indexUpdate.php?ms=1');
  } catch(PDOException $e) {
    $dbh->rollBack();
    header('location: ../FILES/indexUpdate.php?ms=2');
  }
  exit();
}
else if(isset($_POST['corpAction']))
{
  $divisor=$_POST["divisor"];
  $corpAction=$_POST["cp"];
  $remarks=$_POST["remarks"];
  $divisorNew=$_POST["divisorNew"];

  if($divisorNew!=''){
    $divisor=$divisorNew;
  }else{
  }

  $query = $dbh->prepare("SELECT sum(s.paid_up_shares * m.market_price) sum from market_price m, symbol s WHERE m.symbol_id=s.symbol_id and s.security_type='OS' and s.status=1");
  $query->execute();
  $mcap = $query->fetch();
  $sum = $mcap['sum'];

  $sumround = round($sum,2);
  $BaseDivisor=$divisor;
  $Index=round($index1 = $sumround/$BaseDivisor,2);
  $save=$dbh->prepare("INSERT INTO market_index(base,m_index,corpType,remarks,market_cap) VALUES('$BaseDivisor','$Index','$corpAction','$remarks','$sumround')");
  if($save->execute()){
    header('location: ../FILES/indexUpdate.php?ms=1');
    exit();
  }
  else{
    header('location: ../FILES/indexUpdate.php?ms=2');
    exit();
  }
}
else if(isset($_POST['corpActionSector']))
{
    $divisor = $_POST["divisor"];
    $corpAction = $_POST["cp"];
    $remarks = $_POST["remarks"];
    $divisorNew = $_POST["divisorNew"];

    $sectorType = $_POST["sectorType"];

    if ($divisorNew != '') {
        $divisor =  $divisorNew;
    } else {
    }

    // Fetch market capitalization for the current sector
    $query = $dbh->prepare("
            SELECT SUM(s.paid_up_shares * m.market_price) AS sum 
            FROM market_price m 
            JOIN symbol s ON m.symbol_id = s.symbol_id 
            WHERE s.security_type = 'OS' 
            AND s.status = 1 
            AND s.sector = :sectorType
    ");
    $query->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
    $query->execute();
    $mcap = $query->fetch(PDO::FETCH_ASSOC);
    $sum = isset($mcap['sum']) ? $mcap['sum'] : 0;

    $sumround = round($sum, 2);
    $BaseDivisor = $divisor;
    $SectorIndex = round($index1 = $sumround / $BaseDivisor, 2);
    
    // Insert into sector_index table
    $save = $dbh->prepare("INSERT INTO sector_index (sector_type, base, s_index, corpType, market_cap, remarks, created_date) VALUES (:sectorType, :base, :s_index, :corpType, :market_cap, :remarks, NOW())");
    $save->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
    $save->bindParam(':base', $BaseDivisor, PDO::PARAM_STR);
    $save->bindParam(':s_index', $SectorIndex, PDO::PARAM_STR);
    $save->bindParam(':corpType', $corpAction, PDO::PARAM_STR);
    $save->bindParam(':market_cap', $sumround, PDO::PARAM_STR);
    $save->bindParam(':remarks', $remarks, PDO::PARAM_STR);
    if($save->execute()){
        header('location: ../FILES/SectorIndexUpdate.php?ms=1');
        exit();
    } else {
        header('location: ../FILES/SectorIndexUpdate.php?ms=2');
        exit();
    }

    // Fetch distinct sectors from the symbol table
    /*$sectorQuery = $dbh->prepare("SELECT DISTINCT sector FROM symbol WHERE sector IS NOT NULL AND security_type = 'OS' AND status = 1");
    $sectorQuery->execute();
    $sectors = $sectorQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sectors as $sector) {

        $sectorType = $sector['sector'];

        // Fetch market capitalization for the current sector
        $query = $dbh->prepare("
                                SELECT SUM(s.paid_up_shares * m.market_price) AS sum 
                                FROM market_price m 
                                JOIN symbol s ON m.symbol_id = s.symbol_id 
                                WHERE s.security_type = 'OS' AND s.status = 1 AND s.sector = :sectorType
        ");
        $query->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
        $query->execute();
        $mcap = $query->fetch(PDO::FETCH_ASSOC);
        $sum = isset($mcap['sum']) ? $mcap['sum'] : 0;

        $sumround = round($sum,2);
        $BaseDivisor=$divisor;
        $SectorIndex=round($index1 = $sumround/$BaseDivisor,2);

        // Insert into sector_index table
        $save = $dbh->prepare("INSERT INTO sector_index (sector_type, base, s_index, corpType, market_cap, remarks, created_date) VALUES (:sectorType, :base, :s_index, :corpType, :market_cap, :remarks, NOW())");
        $save->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
        $save->bindParam(':base', $BaseDivisor, PDO::PARAM_STR);
        $save->bindParam(':s_index', $SectorIndex, PDO::PARAM_STR);
        $save->bindParam(':corpType', $corpAction, PDO::PARAM_STR);
        $save->bindParam(':market_cap', $sumround, PDO::PARAM_STR);
        $save->bindParam(':remarks', $remarks, PDO::PARAM_STR);
        if($save->execute()){
            header('location: ../FILES/SectorIndexUpdate.php?ms=1');
            exit();
        }
        else{
            header('location: ../FILES/SectorIndexUpdate.php?ms=2');
            exit();
        }
    }*/ // end of forloop
}
elseif (isset($_POST['account_unlock'])) {
        $usr_nam = clean($_POST['usr_name']);

        $stmt = $dbh->prepare("DELETE FROM login_attempts WHERE username = ?");
        $stmt->bindParam(1, $usr_nam);
        $result = $stmt->execute();

        if($result) {
            echo'Successfully unlocked';
        } else { 
            echo'There was an error while operation.';
        }
        die();
}
else
{
  echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! There was an error while operation.</div></div></div>';
  exit();
}
?>