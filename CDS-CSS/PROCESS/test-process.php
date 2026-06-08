<?php 
include ('../../CONNECTIONS/db.php');
include ('../../CONNECTIONS/function-sanitize.php');
session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="3")
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
if (isset($_POST['test']))
{ 
//variable declaration  
$atype = clean($_POST['atype']);
$cdcode=clean($_POST['cdcode']);
$fn = clean($_POST['fn']);
$ln=clean($_POST['ln']);
$nat = clean($_POST['nat']);
$id=clean($_POST['id']);
$dz=clean($_POST['dz']);
$tpn=clean($_POST['tpn']);
$phone=clean($_POST['phone']);
$email = clean($_POST['email']);
$bank=clean($_POST['bank']);
$accno=clean($_POST['accno']);
$add=clean($_POST['add']);
$username=clean($_POST['save_client']);
// !- variable declaration  


$n = $_POST['name'];
$c = $_POST['cid'];
$r = $_POST['relationship'];



for($i=0;$i<count($n);$i++)
{
  echo $n[$i].$c[$i].$r[$i]."<br />";
}

}
elseif (isset($_POST['edit_cli']))
{ 
//variable declaration  
$atype = clean($_POST['atype']);
$fn = clean($_POST['fn']);
$ln=clean($_POST['ln']);
$nat = clean($_POST['nat']);
$dz=clean($_POST['dz']);
$tpn=clean($_POST['tpn']);
$phone=clean($_POST['phone']);
$email = clean($_POST['email']);
$bank=clean($_POST['bank']);
$accno=clean($_POST['accno']);
$add=clean($_POST['add']);
$cli_id=clean($_POST['edit_cli']);
// !- variable declaration   
$save = $dbh->prepare("UPDATE client_account SET acc_type=:atype,f_name=:fn,l_name=:ln,nationality=:nat,DzongkhagID=:dz,tpn=:tpn,phone=:phone,email=:email,bank_id=:bank,bank_account=:accno,address=:add where client_id=:id");
$save->bindParam(':atype',$atype);
$save->bindParam(':fn',$fn);
$save->bindParam(':ln',$ln);
$save->bindParam(':nat',$nat);
$save->bindParam(':dz',$dz);
$save->bindParam(':tpn',$tpn);
$save->bindParam(':phone',$phone);
$save->bindParam(':email',$email);
$save->bindParam(':bank',$bank);
$save->bindParam(':accno',$accno);
$save->bindParam(':commis',$commis);
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
//bbo finance start
elseif (isset($_POST['mat']))
{ 
//variable declaration  
$cd_code=clean($_POST['cdcode']);
$sy=clean($_POST['sy']);
$hol=clean($_POST['hol']);
$rm=clean($_POST['rm']);
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd ");  
$q->bindParam(':cd',$cd_code);
$q->execute();
if($q->rowCount() > 0){
   $row=$q->fetch();
   $e_vol=$row['volume'];
   $new_vol=$e_vol+$hol;
   $save = $dbh->prepare("UPDATE cds_holding volume=:vol where cd_code=:cd");
   $save->bindParam(':vol',$new_vol);
   $save->bindParam(':cd',$cd_code)
   if($save->execute()){
          header('location: ../FILES/cds-css-landing.php?ms=1');
          exit();}  
   else{
          header('location: ../FILES/cds-css-landing.php?ms=2');
          exit();}
}
else{
    $save = $dbh->prepare("INSERT into cds_holding (cd_code,volume,remarks,user_name,institution_id,symbol) 
                       VALUES ('$cd_code','$hol','$rm','$username','$institution_id','$sy')");
   if($save->execute()){
          header('location: ../FILES/cds-css-landing.php?ms=1');exit();}  
   else{
          header('location: ../FILES/cds-css-landing.php?ms=2');exit();}
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
   $save = $dbh->prepare("UPDATE cds_holding volume=:vol where cd_code=:cd");
   $save->bindParam(':vol',$new_vol);
   $save->bindParam(':cd',$cd_code) 
   if($save->execute()){
          header('location: ../FILES/cds-css-landing.php?ms=1');
          exit();}  
   else{
          header('location: ../FILES/cds-css-landing.php?ms=2');
          exit();}
    }
    elseif ($new_vol < 0){
          header('location: ../FILES/cds-css-landing.php?ms=6&cd='.$cd_code.'&e_vol='.$e_vol.'&sy='.$symbol);
          exit();}
    }
else{
    $save = $dbh->prepare("INSERT into cds_holding (cd_code,volume,remarks,user_name,institution_id,symbol) 
                       VALUES ('$cd_code','$hol','$rm','$username','$institution_id','$sy')");
   if($save->execute()){
          header('location: ../FILES/cds-css-landing.php?ms=1');exit();}  
   else{
          header('location: ../FILES/cds-css-landing.php?ms=2');exit();}
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
$user_name=clean($_POST['transfer']);
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$q->bindParam(':cd',$F_cd);$q->bindParam(':id',$sy);
$q->execute();
$row=$q->fetch();
$F_e_vol=$row['volume'];
$F_vol=$vol-$e_vol;

$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$q->bindParam(':cd',$T_cd);$q->bindParam(':id',$sy);
$q->execute();
$row=$q->fetch();
$T_e_vol=$row['volume'];
$T_vol=$vol+$T_e_vol;

   $save = $dbh->prepare("UPDATE cds_holding volume=:vol where cd_code=:cd and symbol_id=:id ");
   $save->bindParam(':cd',$F_cd);
   $save->bindParam(':vol',$F_vol);
   $q->bindParam(':id',$sy);
   $save->execute();

   $save = $dbh->prepare("UPDATE cds_holding volume=:vol where cd_code=:cd and symbol_id=:id ");
   $save->bindParam(':cd',$T_cd);
   $save->bindParam(':vol',$T_vol);
   $q->bindParam(':id',$sy);
   $save->execute();

   $save = $dbh->prepare("INSERT into cds_transfer (from_acc,to_acc,symbol_id,trs_vol,remarks,user_name) VALUES ('$F_cd','$T_cd','$sy','$vol','$rm','$user_name')");
   if($save->execute()){
          header('location: ../FILES/cds-css-landing.php?ms=1');
          exit();}  
   else{
          header('location: ../FILES/cds-css-landing.php?ms=2');
          exit();}
}
//transfer end
//pledge start
elseif (isset($_POST['pledge']))
{ 
//variable declaration  
$cc=clean($_POST['cc']);
$pl=clean($_POST['pl']);
$ac=clean($_POST['ac']);
$sy=clean($_POST['sy']);
$rm=clean($_POST['remarks']);
$vol_pl=clean($_POST['trs']);
$user_name=clean($_POST['pledge']);
// !- variable declaration
$q=$dbh->prepare("SELECT * from cds_holding where cd_code=:cd and symbol_id=:id ");  
$q->bindParam(':cd',$ac);$q->bindParam(':id',$sy);
$q->execute();
$row=$q->fetch();
$P_vol=$row['pledge_volume'];
$avl_vol=$row['volume'];

$new_pl_vol=$P_vol+$vol_pl;
$new_avl_vol=$avl_vol-$vol_pl;

   $save = $dbh->prepare("UPDATE cds_holding volume=:vol,pledge_volume=:pl_vol where cd_code=:cd and symbol_id=:id ");
   $save->bindParam(':cd',$ac);
   $save->bindParam(':vol',$new_avl_vol);
   $save->bindParam(':pl_vol',$new_pl_vol);
   $q->bindParam(':id',$sy);
   $save->execute();

   $save = $dbh->prepare("INSERT into cds_pledge (pledge_contract,pledgee,cd_code,symbol_id,pledge_volume,remarks,user_name) VALUES ('$cc','$pl','$ac','$sy','$vol_pl','$rm','$user_name')");
   if($save->execute()){
          header('location: ../FILES/cds-css-landing.php?ms=1');
          exit();}  
   else{
          header('location: ../FILES/cds-css-landing.php?ms=2');
          exit();}
}
//pledge end
else
{
  header('location: ../FILES/cds-css-landing.php?ms=2');exit();
}
?>