<?php 
  include('../FILES/sessionStartFile_cdscss.php');
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
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Reports</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Pledge Release Report</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-4">
              <label>From Date <font color="red">*</font></label>
              <input type="date" name="from_date" id="from_date" class="form-control" required>
            </div>

            <div class="col-lg-4">
              <label>To Date <font color="red">*</font></label>
              <input type="date" name="to_date" id="to_date" class="form-control" required>
            </div>

            <div class="col-lg-4">
              <label>Pledgee Bank <font color="red">*</font></label>
              <select name="pledgee_bank" id="pledgee_bank" class="form-control" required>
                <option value="0">--ALL--</option>
                <?php  
                  $stmt = $dbh->prepare("SELECT pledgee_id, pledgee FROM cds_pledgee ORDER BY pledgee ASC");
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $key => $value) {
                    echo'<option value=" ' . $value['pledgee'] . ' "> ' . $value['pledgee'] . ' </option>';
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">          
              <button type="button" class="btn btn-success" id="get_release_dtls" name="get_release_dtls"><i class="fa fa-list"></i> Generate </button>
            </div>
          </div>
        </div>

        <div class="box">
          <div class="box-body">
            <div id="details"></div>
          </div>
        </div>
        
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  $("#get_release_dtls").click( function () {
      showLoading();
      var from_date = $("#from_date").val();
      var to_date = $("#to_date").val();
      var pledgee_bank = $("#pledgee_bank").val();
      var op = 'get_pledge_release_report';

      $.ajax({
        type: "POST",
        url: "loadReport.php",
        data: 'get_pledge_release_report=' + op + '&from_date=' + from_date + '&to_date=' + to_date + '&pledgee_bank=' + pledgee_bank,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#details").show().html(response);
        }
      });
  });
</script>
</html>