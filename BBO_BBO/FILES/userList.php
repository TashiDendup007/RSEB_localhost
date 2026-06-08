<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');

  $id = isset($_GET['ms']) ? $_GET['ms'] : 0;
  // $errMsg = isset($_GET['err']) ? $_GET['err'] : '';
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
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
          <li><a href="#">User List</a></li>
        </ol>
      </section>
      <div class="col-lg-12 col-md-12" id="successMsg" style="display: none;">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Successfully Verified.
        </div>
      </div>
      <div class="col-lg-12 col-md-12" id="rejectMsg" style="display: none;">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Successfully Rejected.
        </div>
      </div>
      <div class="col-lg-12 col-md-12" id="errorMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> An error occurred. Please contact RSEB support
        </div>
      </div>
      <section class="content">
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <div class="box">
              <div class="box-body table-responsive">
                <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">CID No</th>
                      <th scope="col">Name</th>
                      <th scope="col">Phone</th>
                      <th class="text-center" scope="col">Status</th>
                      <th scope="col">Action Date</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php 
                    $sql = $dbh->prepare("SELECT a.user_online_id, a.cid, a.name, a.phone, a.status, a.created_date 
                      FROM api_online_terminal a 
                      WHERE a.participant_code=:pCode AND a.fee_status='1' AND a.status='SUB' ORDER BY a.created_date DESC");
                    $sql->bindParam(':pCode', $pass_code);
                    $sql->execute();
                    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
                    $i=1;
                    foreach($rows as $res){
                      echo'
                      <tr>
                        <td>'.$i.'</td>
                        <td><a href="userDtls.php?id='.$res['user_online_id'].'">'.$res['cid'].'</a></td>
                        <td>'.$res['name'].'</td>
                        <td>'.$res['phone'].'</td>';
                        if($res['status'] == 'SUB'){
                          echo'<td class="text-center btn-primary">SUBMITTED</td>';
                        } else if ($res['status'] == 'BV'){
                          echo'<td class="text-center btn-success">VERIFIED</td>';
                        }
                        echo'
                        <td>'.$res['created_date'].'</td>
                      </tr>';
                      $i++;
                    }
                    $dbh = null;
                  ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $('#listTableId').DataTable();
    
    var id = "<?php echo $id; ?>";
    if(id == 1){
      $("#successMsg").show();
      setTimeout(function() { $('#successMsg').fadeOut('fast'); }, 5000); 
    }else if(id == 2){
      $("#rejectMsg").show();
      setTimeout(function() { $('#rejectMsg').fadeOut('fast'); }, 5000); 
    }else if(id == 3){
      $("#errorMsg").show();
      setTimeout(function() { $('#errorMsg').fadeOut('fast'); }, 5000); 
    }
  });
</script>
</html>
