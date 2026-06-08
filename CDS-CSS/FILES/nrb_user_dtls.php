<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db_config_website.php');
  $app_id = isset($_GET['id']) ? $_GET['id'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Order</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title"><strong>User Details</strong></h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i> </button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
          <div class="box-body">
            <div class="box-body">
              <div class="row">
                <?php 
                  $sql = $dbh_site->prepare("SELECT a.cid, a.title, a.name, a.passport, a.dob, a.email, a.local_phone_no, a.oversea_phone_no, a.bank, a.account_no, a.account_type, a.permanent_address, a.oversea_address, a.flag, a.file_path 
                    FROM non_resident_bhutaneses a WHERE a.id=:app_id AND a.app_status='SUBMITTED'");
                  $sql->bindParam(':app_id', $app_id);
                  $sql->execute();
                  $res = $sql->fetch();
                  echo'
                  <input type="hidden" value="'.$username.'" name="user_name" id="user_name">
                  <input type="hidden" value="'.$res['flag'].'" name="flag" id="flag">
                  <input type="hidden" value="'.$app_id.'" name="app_id" id="app_id">
                  <div class="col-lg-4 col-md-4">
                    <label>CID No</label>
                    <input type="text" class="form-control" name="cid" id="cid" value="'.$res['cid'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" id="title" value="'.$res['title'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" id="name" value="'.$res['name'].'" readonly>
                  </div>

                  <div class="col-lg-4 col-md-4">
                    <label>Passport</label>
                    <input type="text" class="form-control" name="passport" id="passport" value="'.$res['passport'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>DOB</label>
                    <input type="date" class="form-control" name="dob" id="dob" value="'.$res['dob'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Local Phone No</label>
                    <input type="text" class="form-control" name="local_phone_no" id="local_phone_no" value="'.$res['local_phone_no'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Email</label>
                    <input type="text" class="form-control" name="email" id="email" value="'.$res['email'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Oversea Phone No</label>
                    <input type="text" class="form-control" name="oversea_phone_no" id="oversea_phone_no" value="'.$res['oversea_phone_no'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Bank</label>
                    <input type="text" class="form-control" name="bank" id="bank" value="'.$res['bank'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Account No</label>
                    <input type="text" class="form-control" name="account_no" id="account_no" value="'.$res['account_no'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Account Type</label>
                    <input type="text" class="form-control" name="account_type" id="account_type" value="'.$res['account_type'].'" readonly>
                  </div>
                  <div class="col-xs-12">
                    <label>Permanent Address</label>
                    <input type="text" class="form-control" name="permanent_address" id="permanent_address" value="'.$res['permanent_address'].'" readonly>
                  </div>
                  <div class="col-xs-12">
                    <label>Present Address</label>
                    <input type="text" class="form-control" name="oversea_address" id="oversea_address" value="'.$res['oversea_address'].'" readonly>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Membership Code</label>
                    <select class="form-control" name="institution_id" id="institution_id" required>
                      <option value="">-- Select --</option>';
                      $getMem = $dbh->prepare("SELECT i.id, i.institution_id, p.participant_id, p.participant_code
                          FROM adm_institution i 
                          JOIN adm_participants p ON i.institution_id = p.institution_id
                          WHERE p.status = 1 
                          ORDER BY p.participant_code ASC");
                      $getMem->execute();
                      foreach ($getMem as $mem) {
                        $selected = '';
                        if($mem['participant_code'] == 'MEMRSEB'){
                          $selected = "selected";
                        }
                        echo'<option value="'.$mem['institution_id'].'" '.$selected.'>'.$mem['participant_code'].'</option>';
                      }
                      echo'
                    </select>
                  </div>
                  <div class="col-lg-4 col-md-4">
                    <label>Commision</label>
                    <select class="form-control" name="commission" id="commission" required>';
                    $getComm = $dbh->prepare("SELECT b.bro_comm_id, b.commission_name, b.rate, p.participant_code
                      FROM bbo_commission b 
                      JOIN adm_institution a ON b.institution_id = a.institution_id
                      JOIN adm_participants p ON a.institution_id = p.institution_id AND p.participant_code='MEMRSEB'
                    ");
                    $getComm->execute();
                    foreach ($getComm as $com) {
                      echo'<option value="'.$com['bro_comm_id'].'">'.$com['commission_name'].' -:- Rate('.$com['rate'].')</option>';
                    }
                    echo'
                    </select>
                  </div>';
                  if ($res['flag'] == 'MANUAL') {
                    echo'
                    <div class="col-lg-4 col-md-4">
                      <label>Attachment</label><br>
                      <a href='.$res['file_path'].' target="_blank" class="btn btn-success">View Passport</a>
                    </div>';
                  }
                  echo'
                  <div class="col-xs-12" id="remarksDispaly" style="display: none;">
                    <label>Remarks</label>
                    <textarea class="form-control" name="remarks" id="remarks"></textarea>
                  </div>';
                ?>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-xs-12">
              <button type="submit" class="btn btn-primary btn-lg" id="nrb_verification" name="nrb_verification" value="APPROVED"><i class="fa fa-check"></i> Approve</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <button type="button" class="btn btn-danger btn-lg" id="nrb_verification_1" name="nrb_verification" value="REJECTED" onclick="showRemarks();"><i class="fa fa-times"></i> Reject</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <button type="button" class="btn btn-warning btn-lg" onclick="returnBack();"><i class="fa fa-times"></i> Cancel</button>
            </div>
          </div>
          </form><br>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>  
  </div>
</body>
<script type="text/javascript">
  function returnBack() {
    window.location.replace("nrb_app_list.php");
  }

  function showRemarks() {
    $("#remarksDispaly").show();
    $("#remarks").attr("required", "true");
    $("#nrb_verification_1").prop("onclick", null).off("click");
    $("#nrb_verification_1").removeAttr("type").attr("type", "submit");
  }

  function returnfun() {
    if(confirm('Are you sure to submit')){
      return true;
    }else{
      return false;
    }
  }
</script>
</html>
