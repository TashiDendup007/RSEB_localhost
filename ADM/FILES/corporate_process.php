<?php 
  include('sessionStartFile_admin.php'); 
  include ('../../CONNECTIONS/db.php');

  date_default_timezone_set("Asia/Thimphu"); 
  $sysDate = date("Y-m-d");
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
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
          <li><a href="#">Corporate List</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h4>Corporate Announcement List</h4>
              </div>
              <div class="box-body table-responsive">
                <table class="table table-bordered" id="corporateAnncmntTbl">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Symbol</th>
                      <th scope="col">Announcement Type</th>
                      <th scope="col">Record Date</th>
                      <th scope="col">Ex-Date</th>
                      <th scope="col">Rate</th>
                      <th scope="col">Type</th>
                      <th scope="col">Announcement Date</th>
                      <th scope="col">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $i=1;
                    $sql = $dbh->prepare("SELECT 
                      s.symbol, c.corp_announcement_id, c.symbol_id, c.announcement_type, m.corporate_name, c.record_date, c.ex_date, c.announcement_date, c.rate, c.type, c.status, c.created_date
                      FROM corporate_announcement c 
                      LEFT JOIN symbol s ON c.symbol_id = s.symbol_id 
                      LEFT JOIN corporate_action_masters m ON c.announcement_type = m.id 
                      WHERE c.announcement_type NOT IN (3) 
                      AND c.status = 1 
                      -- AND DATE(c.record_date) = :recordDate
                    ");
                    // $sql->bindParam(':recordDate', $sysDate);
                    $sql->execute();
                    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                      echo'
                      <tr data-id='.$row['corp_announcement_id'].'>
                        <td>'.$i.' - '.$row['corp_announcement_id'].'</td>
                        <td>'.$row['symbol'].'</td>
                        <td><strong>'.$row['corporate_name'].'</strong></td>
                        <td>'.$row['record_date'].'</td>
                        <td>'.$row['ex_date'].'</td>
                        <td>'.number_format($row['rate'], 2).'</td>
                        <td>'.$row['type'].'</td>
                        <td>'.$row['announcement_date'].'</td>
                        <td>
                          <button type="button" class="btn btn-primary" onclick="process('.$row['corp_announcement_id'].', '.$row['symbol_id'].', '.$row['announcement_type'].')" data-toggle="tooltip" data-placement="top" title="Click here to process"><i class="fa fa-refresh"></i> Process</button>
                        </td>
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
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $("#corporateAnncmntTbl").dataTable();
  });

  function process(ann_id, sym_id, ann_type) {
    if (confirm('Do you want to continue processing?')) {
      showLoading();
      var op = 'get_total_volume';
      var data = {
        get_total_volume: op,
        symbol_id: sym_id,
        announcement_id: ann_id,
        announcement_type: ann_type,
      };

      if (ann_type == 1) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/rights_subscription_migration.php",
          data: data,
          dataType: 'HTML',
          success: function(response) {
            hideloading();
            $("#message").html(response);
            showMessage();
          }
        });
      } else {

        $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: data,
          dataType: 'JSON',
          success: function(response) {

            if( confirm("Old volume = "+ response.old_vol + ", New volume = "+response.new_vol ) ) {
              var op1 = 'corporateActionProcess';
              var data1 = {
                corporateActionProcess: op1,
                symbol_id: sym_id,
                announcement_id: ann_id,
                announcement_type: ann_type,
              };

              $.ajax({
                type: "POST",
                url: "../PROCESS/process.php",
                data: data1,
                dataType: 'html',
                success: function(response) {
                  hideloading();
                  $("#message").html(response);
                  showMessage();
                }
              });
              
            } else {
              hideloading();
              return false;
            }

          }
        });
        
      }
    } else {
      hideloading();
      return false;
    }
  }

</script>
</html>
