<?php 
  date_default_timezone_set("Asia/Thimphu");
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
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
       <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Corporate Announcement</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Corporate Announcement</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-xs-12">
                <div class="table-responsive">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>Symbol</th>
                        <th>Rate</th>
                        <th>Accnouncement Type</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php 
                      $current_date = date("Y-m-d");
                      $query = $dbh->prepare('SELECT c.corp_announcement_id, s.symbol, c.rate, c.announcement_type, c.record_date 
                        FROM corporate_announcement c 
                        JOIN symbol s ON c.symbol_id = s.symbol_id 
                        where c.record_date >= ? and c.status = 1
                      ');
                      $query->bindParam(1, $current_date);
                      $query->execute();
                      $io = 1;
                      $a_type = '';
                      while ($result = $query->fetch()) {
                        switch ($result['announcement_type']) {
                            case 1:
                                $a_type = 'Rights';
                                break;
                            case 2:
                                $a_type = 'Bonus';
                                break;
                            case 3:
                                $a_type = 'Dividend';
                                break;
                            case 4:
                                $a_type = 'Buy Back';
                                break;
                            default:
                                $a_type = '';
                        }
                        echo'
                        <tr>
                          <td> '.$io.'</td>
                          <td> '.$result['symbol'].'</td>
                          <td> '.number_format($result['rate'], 2).'</td>
                          <td> '.$a_type.'</td>';

                          if ($result['record_date'] == $current_date) {
                            echo'
                            <input type="hidden" id="announcement_type'.$io.'" value="'.$result['announcement_type'].'">
                            <input type="hidden" id="corp_announcement_id'.$io.'" value="'.$result['corp_announcement_id'].'">
                            <input type="hidden" id="symbol'.$io.'" value="'.$result['symbol'].'">
                            <input type="hidden" id="red'.$io.'" value="'.$result['record_date'].'">
                            <td>
                              <button type="button" class="btn btn-info" style="display:block;" name="exe_corporate_announcement" onclick="return fun('.$io.');"><i class="fa fa-refresh"></i> Process</button>
                            </td>';
                          } else {
                            echo'
                            <td>
                              <button type="button" class="btn btn-default btn-box-tool"> Under Process</button>
                            </td>';
                          }
                          echo'
                          </tr>';
                        $io++;
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
<script type="text/javascript">
  function fun(io) {
    var announcement_type = document.getElementById('announcement_type'+io).value;
    var corp_announcement_id = document.getElementById('corp_announcement_id'+io).value;
    var symbol = document.getElementById('symbol'+io).value;
    var red = document.getElementById('red'+io).value;
    var exe_corporate_announcement = 'exe_corporate_announcement';
    showLoading();
    
    if (confirm("Are you sure you want to Process "+ symbol + ', CA for Record Date : '+red+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data:'announcement_type='+announcement_type+'&corp_announcement_id='+corp_announcement_id+'&exe_corporate_announcement='+exe_corporate_announcement,
        success: function (response) {
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
</script>
</body>
</html>