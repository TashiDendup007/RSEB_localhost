<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
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
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">UserList</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-body">
              <div class="table-responsive">
                <?php 
                  $sql = $dbh->prepare("SELECT user_online_id, cid, cd_code, name, participant_code, phone, status, created_date 
                      FROM api_online_terminal a WHERE a.status IN ('AP') AND a.fee_status=1 ORDER BY a.created_date DESC");
                  $sql->execute();
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
                  $i=1;
                  foreach($sql as $res){
                    echo'
                    <tr>
                      <td>'.$i.'</td>';
                      if($res['status']=='AP'){
                        echo'<td>'.$res['cid'].'</td>';
                      }else{
                        echo'<td><a href="userDtls.php?id='.$res['user_online_id'].'">'.$res['cid'].'</a></td>';
                      }
                      echo'
                      <td>'.$res['name'].'</td>
                      <td>'.$res['phone'].'</td>';
                      if($res['status']=='SUB'){
                        echo'<td class="text-center btn-primary">SUBMITTED</td>';
                      }else if($res['status']=='BV'){
                        echo'<td class="text-center btn-success">VERIFIED</td>';
                      }else{
                        echo'<td class="text-center btn-danger">APPROVED</td>';
                      }
                      echo'<td>'.$res['created_date'].'</td>';
                      if($res['status']=='AP'){
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
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $('#listTableId').DataTable();

    // Show loader when page starts loading
    showLoading();

    // Hide loader when page finished loading
    $(window).on('load', function() {
      hideloading();
    });
  });
</script>
</html>
