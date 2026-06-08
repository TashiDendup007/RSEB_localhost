<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db_config_website.php');

  $id = isset($_GET['ms']) ? $_GET['ms'] : 0;
  // $message = isset($_GET['message']) ? $_GET['message'] : 0;
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
          <li><a href="#">NRB</a></li>
        </ol>
      </section>
      <div class="col-sm-8 col-xs-8" id="successMsg" style="display: none;">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Application has been approved successfully. 
        </div>
      </div>
      <div class="col-sm-8 col-xs-8" id="rejectMsg" style="display: none;">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Application has been rejected successfully.
        </div>
      </div>
      <div class="col-sm-12 col-xs-12" id="errorMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Encountered with an error. Contact IT Division.
        </div>
      </div>
      <div class="col-sm-8 col-xs-8" id="infoMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> User Already Existed.
        </div>
      </div>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border"><strong>Non Resident Bhutanese</strong></div>
              <div class="box-body">
                <div class="table-responsive">
                <?php 
                  echo'
                  <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">CID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th class="text-center" scope="col">Status</th>
                        <th scope="col">Date</th>
                      </tr>
                    </thead>
                    <tbody>';
                    $i=1;
                    $sql = $dbh_site->prepare("SELECT a.id, a.cid, a.name, a.email, a.app_status, a.created_at 
                      FROM non_resident_bhutaneses a WHERE a.app_status IN ('SUBMITTED') ORDER BY a.created_at ASC");
                    $sql->execute();
                    foreach ($sql as $res) {
                      $status = ($res['app_status'] = 'SUBMITTED') ? 'SUBMITTED' : 'APPROVED';
                      $bg_color = ($res['app_status'] = 'SUBMITTED') ? 'btn-primary' : 'btn-success';
                      
                      echo'
                      <tr>
                        <td>'.$i.'</td>';
                        if( $res['app_status']=='APPROVED' ){
                          echo'<td>'.$res['cid'].'</td>';
                        }else{
                          echo'<td><a href="nrb_user_dtls.php?id='.$res['id'].'">'.$res['cid'].'</a></td>';
                        }
                        echo'
                        <td>'.$res['name'].'</td>
                        <td>'.$res['email'].'</td>
                        <td class="text-center '.$bg_color.'">'.$status.'</td>
                        <td>'.$res['created_at'].'</td>
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

  $(document).ready(function() {
    var id = "<?php echo $id; ?>";
    if(id == 1){
      $("#successMsg").show();
      setTimeout(function() { $('#successMsg').fadeOut('fast'); }, 4000); 
    }else if(id == 2){
      $("#rejectMsg").show();
      setTimeout(function() { $('#rejectMsg').fadeOut('fast'); }, 4000); 
    }else if(id == 3){
      $("#errorMsg").show();
      //setTimeout(function() { $('#errorMsg').fadeOut('fast'); }, 4000); 
    }else if(id == 4){
      $("#infoMsg").show();
      setTimeout(function() { $('#infoMsg').fadeOut('fast'); }, 4000); 
    }
  });
</script>
</html>
