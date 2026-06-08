<?php
session_start();
$role = $_SESSION['sess_userrole'];if( $role!="1"){header('Location: ../../access.php?err=2');}$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) {$session_life = time() - $_SESSION['timeout'];if($session_life > $inactive){header("Location: ../../Authentication/Logout.php");}}
$_SESSION['timeout'] = time();
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
$username=$_SESSION['sess_username'];
$list= ins_id($username);
$ins_id=$list[0];$p_code=$list[1];
if(!empty($_POST["pcode_link_user"])) 
{
$cid=$_POST['pcode_link_user'];
$wc= $dbh->prepare("SELECT a.username,a.participant_code,b.cd_code,b.user_name from users a , client_account b where a.participant_code=substring(b.user_name,1,7) and a.cid=b.ID and a.cid=:cid and a.status=1");
$wc->bindParam(':cid',$cid);
$wc->execute();
$res=$wc->fetch();
if($wc->rowCount() > 0){
  echo '       <div class="col-xs-4">
                  <label>User Name</label>
                  <input type="text" class="form-control" value="'.$res['username'].'" disabled>
                </div>
                <div class="col-xs-4">
                  <label>CD CODE</label>
                  <input type="hidden" class="form-control"  name="un" id="un" value="'.$res['username'].'">
                  <input type="hidden" class="form-control"  name="bun" id="bun" value="'.$res['user_name'].'">
                  <input type="text" class="form-control"  name="ct" id="ct" value="'.$res['cd_code'].'">
                  <input type="hidden" class="form-control"   value="'.$res['cd_code'].'" readonly>
                  <input type="hidden" class="form-control"  name="pcode" id="pcode" value="'.$res['participant_code'].'">
                </div>
                <div class="col-xs-4">
                  <label>Participant Code</label>
                  <input type="text" class="form-control"   value="'.$res['participant_code'].'" readonly>
                </div>';
}
else{echo'<div class="col-xs-4">
                  <label>User Name</label>
                  <input type="text" class="form-control"     value="NO DATA" disabled>
                  <input type="hidden" class="form-control"  id="ct" name="ct" value="" >
                  <input type="hidden" class="form-control" id="pcode" name="pcode" value="" >
                </div>';
              }
}
else if(!empty($_POST["load_brokers"])) 
{

$pcode=$_POST['load_brokers'];
$wc= $dbh->prepare("SELECT username,participant_code from users where participant_code=:pcode");
$wc->bindParam(':pcode',$pcode);
$wc->execute();
                echo '
                <div class="col-xs-3">
                  <label>BROKERS</label>
                  <select name="broker" id="broker"  class="form-control">
                <option value="">--Select Broker--</option>';
                 while($res= $wc->fetch())
                {
                echo '<option value="'.$res['username'].'">'.$res['username'].'</option>';
                }
                echo '</select></div>';
}
else if(!empty($_POST["load_rate"])) 
{

$type=$_POST['load_rate'];
  if($type == 'RIGHTS')
  {
        echo '
        <div class="col-xs-3" name="symbol" id="symbol">
          <label>Symbol</label>';
                    $wc= $dbh->prepare("SELECT symbol,symbol_id from symbol where security_type='OS' and status=1 ");
                    $wc->execute();
                    echo '<select name="sy" id="sy" class="form-control"';
                    echo '<option value=""> Select Symbol </option>';
                    echo '<option value="-Select symbol-" selected>-Select symbol-</option>';
                     while($res= $wc->fetch())
                    {
                    echo '<option value="'.$res['symbol_id'].'">';
                    echo $res['symbol'];
                    echo'</option>';
                    }
                    echo'</select>
        </div>

        <div class="col-xs-3">
          <label>Rate</label>
          <input type="text" class="form-control"  id="rate" name="rate" value="" >
        </div>';
  }
  else if($type == "IPO")
  {
        echo '
        <div class="col-xs-3" name="symbol" id="symbol">
          <label>Symbol</label>';
                    $wc= $dbh->prepare("SELECT symbol,symbol_id from symbol where security_type='OS' and status=1 ");
                    $wc->execute();
                    echo '<select name="sy" id="sy" class="form-control"';
                    echo '<option value=""> Select Symbol </option>';
                    echo '<option value="-Select symbol-" selected>-Select symbol-</option>';
                     while($res= $wc->fetch())
                    {
                    echo '<option value="'.$res['symbol_id'].'">';
                    echo $res['symbol'];
                    echo'</option>';
                    }
                    echo'</select>
        </div>
        <div class="col-xs-3">
          <label>Rate</label>
          <input type="text" class="form-control"  id="rate" name="rate" value="" >
        </div>';
  }
  else
  {
        echo '
        <div class="col-xs-3" name="symbol" id="symbol">
          <label>Symbol</label>';
                    $wc= $dbh->prepare("SELECT symbol,symbol_id from symbol where (security_type='GB' or security_type='CB') and status=1 ");
                    $wc->execute();
                    echo '<select name="sy" id="sy" class="form-control"';
                    echo '<option value=""> Select Symbol </option>';
                    echo '<option value="-Select symbol-" selected>-Select symbol-</option>';
                     while($res= $wc->fetch())
                    {
                    echo '<option value="'.$res['symbol_id'].'">';
                    echo $res['symbol'];
                    echo'</option>';
                    }
                    echo'</select>
        </div>

        <div class="col-xs-3">
          <label>Rate</label>
          <input type="text" class="form-control"  id="rate" name="rate" value="" >
        </div>';
  }

}
else if(!empty($_POST["getCID"]))
{
  $cdCod=$_POST['cdCod'];

  $sql=$dbh->prepare("SELECT * FROM client_account WHERE cd_code=:cd");
  $sql->bindParam(':cd', $cdCod);
  $sql->execute();
  $res=$sql->fetch();
  echo'
  <div class="modal fade" id="cidModalTarget" role="dialog">
    <div class="modal-dialog" role="document">
      <form action="" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Update CID Number </h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="row">
              <div class="col-xs-6">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" value="'.$res['f_name'].' '.$res['l_name'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="cdCode">CD Code</label>
                <input type="text" class="form-control" name="cdCode" id="cdCode" value="'.$res['cd_code'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="cid">CID No</label>
                <input type="text" class="form-control" name="cid" id="cid" value="'.$res['ID'].'" readonly>
              </div>
              <div class="col-xs-6">
                <label for="newcid">New CID no to be updated</label>
                <input type="text" class="form-control" name="newcid" id="newcid" maxlength="11">
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">
              <button type="button" class="btn btn-primary" name="update_cid" id="update_cid"  value="'.$cdCod.'">UPDATE</button>  
            </div>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </form>
    </div>
  </div>
  <script type="text/javascript"> 
    $("#update_cid").click(function(){
      showLoading();
      var cidNo = $("#newcid").val();
      var cdCode = $("#cdCode").val();
      if(cidNo==""){
        alert("Please enter new cid number");
        hideloading();
      }else{
        if (confirm("Are you sure you want to update?"))
        {
          var op="update_cid";
          $.ajax({ 
            type: "POST", 
            url: "../PROCESS/process.php", 
            data: "cidNo="+cidNo+"&update_cid="+op+"&cdCode="+cdCode, 
              success: function(data){  
              hideloading();
              $(".statusMsg").show();
              $(".statusMsg").html(data);
              $(".statusMsg").fadeOut(5000);
              //location.reload();
              $("#cidModalTarget").modal("hide");
              $(".modal-backdrop").remove();
              hideloading();
            } 
          });
        }else{ 
          $("#cidModalTarget").modal("hide");
          //$("body").removeClass("modal-open");
          $(".modal-backdrop").remove();
          hideloading();
          return false;
        }
      }
    });
  </script>
  ';
}
elseif(!empty($_POST["getCDCodeDetls"])) 
{
  $cd=$_POST['getCDCodeDetls'];

  $wc= $dbh->prepare("SELECT * FROM client_account WHERE cd_code=:cd");
  $wc->bindParam(':cd',$cd);
  $wc->execute();
  $res=$wc->fetch();
  if($wc->rowCount() > 0){
    echo'       
    <div class="col-xs-6">
      <label for="name">Name:</label>
      <input type="text" class="form-control" name="name" id="name" value="'.$res['f_name'].' '.$res['l_name'].'" readonly>
    </div>
    <div class="col-xs-6">
      <label for="phone">Phone:</label>
      <input type="text" class="form-control" name="phone" id="phone" value="'.$res['phone'].'" readonly>
    </div>
    <div class="col-xs-6">
      <label for="cid">Old CID No:</label>
      <input type="text" class="form-control" name="cid" id="cid" value="'.$res['ID'].'" readonly>
    </div>
    <div class="col-xs-6">
      <label for="newcid">New CID No:</label>
      <input type="number" class="form-control" name="newcid" id="newcid" maxlength="11" required>
    </div>';
  }else{
    echo'
    <div class="col-xs-4">
      <label>Name</label>
      <input type="text" class="form-control" value="NO DATA" disabled>
    </div>';
  }
}
?>