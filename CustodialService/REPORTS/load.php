<?php
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="7")
  {
    header('Location: ../../access.php?err=2');
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
          { 
            header("Location: ../../Authentication/Logout.php"); 
          }
  }
  $_SESSION['timeout'] = time();
  include ('../../CONNECTIONS/db.php');

if(!empty($_POST["individual"])) 
{
  $cid=$_POST['individual'];
  $wc=$dbh->prepare("SELECT DISTINCT c.f_name, c.l_name, c.ID, a.name 
    FROM custodial_account c, adm_institution a, custodial_cds h 
    WHERE (h.cd_code=c.cd_code AND c.ID=:cid AND a.institution_id=c.institution_id 
    AND (h.volume+ h.pledge_volume + h.block_volume+ h.pending_in_vol+ h.pending_out_vol) != 0) 
    OR (h.cd_code=c.cd_code AND h.cd_code=:cid AND a.institution_id=c.institution_id 
    AND (h.volume+ h.pledge_volume+ h.block_volume+ h.pending_in_vol+ h.pending_out_vol) != 0)");
  $wc->bindParam(':cid',$cid);
  $wc->bindParam(':cid',$cid);
  $wc->execute();
  $state=$wc->fetch();
  if($wc->rowCount() > 0)
  {
    echo'
      <div class="col-xs-8">
        <label>Details of Client</label>
        <input type="text" class="form-control" value="NAME : '.$state['f_name'].' '.$state['l_name'].' , CID# '.$state['ID'].'" readonly>
      </div>
      ';
  ?>
    <script type="text/javascript">
      $("#individual").show();
    </script>
  <?php
  }
  else{
    echo'
      <div class="col-xs-4">
        <label>Details of Client</label>
        <input type="text" class="form-control" style="color:red;" value="Invalid CID/CD CODE" readonly>
      </div>';
  }
  exit();
}
else
{
  echo "No Method Matching";
  exit();
}
?>
