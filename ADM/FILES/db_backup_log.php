<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-purple sidebar-mini">
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
          <li><a href="#">DB Backup</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">User Creation</h4>
                <!-- <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
                </div> -->
              </div>
              <div class="box-body">
                 <div class="table-responsive">
                   <table id="db_table_log_id" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Path</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php 
                      $stmt = $dbh->prepare("SELECT b.status, b.backup_name, b.backup_path, b.backup_at FROM db_backup_logs b");
                      $stmt->execute();
                      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      $i = 1;
                      foreach($result as $res) {
                        $status_txt = $res['status'] == 1 ? 'Yes' : 'No';
                        echo'
                        <tr>
                            <td>' . $i . '</td>
                            <td>' . $status_txt . '</td>
                            <td>' . $res['backup_name'] . '</td>
                            <td>' . $res['backup_path'] . '</td>
                            <td>' . $res['backup_at'] . '</td>
                        </tr>';
                        $i++;
                      }
                    ?>
                    </tbody>
                  </table>
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
  $( function () {
    $("#db_table_log_id").DataTable();
  });
</script>
</html>