<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  $id = isset($_GET['ms']) ? $_GET['ms'] : 0;
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
      
      <div class="col-sm-12 col-xs-12" id="successMsg" style="display: none;">
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Successfully Approved and an email has been sent.
        </div>
      </div>
      
      <div class="col-sm-12 col-xs-12" id="rejectMsg" style="display: none;">
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Successfully Rejected.
        </div>
      </div>
      
      <div class="col-sm-12 col-xs-12" id="errorMsg" style="display: none;">
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"> </i> Error occurred. Please, try again later.
        </div>
      </div>
      
      <div class="col-sm-12 col-xs-12" id="infoMsg" style="display: none;">
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
                    echo'
                    <table id="listTableId" class="table table-striped table-bordered" style="width:100%">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">CID No</th>
                          <th scope="col">Name</th>
                          <th scope="col">Phone</th>
                          <th class="text-center" scope="col">Status</th>
                          <th scope="col">Action Date</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>';
                    $i = 1;
                    $sql = $dbh->prepare("SELECT a.user_online_id, a.status, a.cid, a.name, a.phone, a.cd_code, a.participant_code, a.created_date  
                      FROM api_online_terminal a 
                      WHERE a.status IN ('BV') AND a.fee_status = 1 
                      ORDER BY a.created_date DESC
                    ");
                    $sql->execute();
                    foreach($sql as $res){
                      echo'
                      <tr>
                        <td>'.$i.'</td>';
                        if ($res['status'] == 'AP') {
                          echo'<td>'.$res['cid'].'</td>';
                        } else {
                          echo'<td><a href="userDtls.php?id='.$res['user_online_id'].'">'.$res['cid'].'</a></td>';
                        }
                        echo'
                        <td>'.$res['name'].'</td>
                        <td>'.$res['phone'].'</td>
                        <td class="text-center btn-primary">VERIFIED</td>
                        <td>'.$res['created_date'].'</td>';
                        
                        if($res['status'] == 'AP'){
                          echo'<td class="btn btn-default"> 
                          <a href="../REPORTS/loadReportPrint.php?cidNo='.$res['cid'].'&cdCode='.$res['cd_code'].'&pCode='.$res['participant_code'].'&op=terminal_report" target="_blank"> View</a>
                          </td>';
                        }else{
                          echo'<td></td>';
                        }
                        echo'
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
      setTimeout(function() { 
        $('#successMsg').fadeOut('fast'); 
      }, 6000); 
    } 
    else if(id == 2) {
      $("#rejectMsg").show();
      setTimeout(function() { 
        $('#rejectMsg').fadeOut('fast'); 
      }, 6000); 
    } 
    else if(id == 3) {
      $("#errorMsg").show();
      setTimeout(function() { 
        $('#errorMsg').fadeOut('fast'); 
      }, 6000); 
    } 
    else if(id == 4) {
      $("#infoMsg").show();
      setTimeout(function() { 
        $('#infoMsg').fadeOut('fast'); 
      }, 6000); 
    }
  });
</script>
</html>
