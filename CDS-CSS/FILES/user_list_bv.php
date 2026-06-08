<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  
  $id = isset($_GET['ms']) ? $_GET['ms'] : 0;
  $pass_code = $_SESSION['sess_part_code'];
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
      <section class="content-header">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Online Terminal</a></li>
        </ol>
      </section>
      <div class="col-lg-12 col-md-12 col-sm-12" id="successMsg" style="display: none;">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Successfully Verified and please approve the application.
        </div>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12" id="rejectMsg" style="display: none;">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Successfully Rejected.
        </div>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12" id="errorMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Error occurred. Please, try again later.
        </div>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12" id="infoMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Username Already Exited. You cannot apply twice.
        </div>
      </div>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body">
                <div class="table-responsive">
                  <?php 
                    $sql = $dbh->prepare("SELECT * FROM api_online_terminal a WHERE a.status IN ('SUB') AND a.fee_status = 1 ORDER BY a.created_date DESC");
                    $sql->execute();
                    echo'
                    <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">CID No</th>
                          <th scope="col">Name</th>
                          <th scope="col">Phone</th>
                          <th scope="col">Email</th>
                          <th scope="col">Memeber</th>
                          <th class="text-center" scope="col">Status</th>
                          <th scope="col">Action Date</th>
                        </tr>
                      </thead>
                      <tbody>';
                    $i = 1;
                    foreach($sql as $res){
                      echo'
                      <tr>
                        <td>'.$i.'</td>
                        <td><a href="user_dtls_bv.php?id='.$res['user_online_id'].'">'.$res['cid'].'</a></td>
                        <td>'.$res['name'].'</td>
                        <td>'.$res['phone'].'</td>
                        <td>'.$res['email'].'</td>
                        <td>'.$res['participant_code'].'</td>
                        <td class="text-center btn-info">SUBMITTED</td>
                        <td>'.$res['created_date'].'</td>
                      </tr>';
                      $i++;
                    }
                    echo'
                    </tbody>
                  </table>';
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?> 
  </div>
</body>
<script type="text/javascript">
  $(document).ready(function() {
      $('#listTableId').DataTable();
  });
  
  $(document).ready( function() {
    var id = "<?php echo $id; ?>";
    if(id == 1){
      $("#successMsg").show();
      setTimeout(function() { $('#successMsg').fadeOut('fast'); }, 4000); 
    } else if(id == 2){
      $("#rejectMsg").show();
      setTimeout(function() { $('#rejectMsg').fadeOut('fast'); }, 4000); 
    } else if(id == 3){
      $("#errorMsg").show();
      setTimeout(function() { $('#errorMsg').fadeOut('fast'); }, 4000); 
    } else if(id == 4){
      $("#infoMsg").show();
      setTimeout(function() { $('#infoMsg').fadeOut('fast'); }, 4000); 
    }
  });
</script>
</html>
