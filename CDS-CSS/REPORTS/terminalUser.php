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
           <li><a href="#">Terminal Users</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Terminal User Registered</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../REPORTS/loadReport.php" method="post" onsubmit="showLoading();">
            <div class="box-body">
              <div class="col-lg-4 col-md-4">
                <label>Broker</label>
                <select class="form-control" id="broker" name="broker" required>
                  <option value="">--Select Membership Code--</option>
                  <?php 
                    $sql = $dbh->prepare("SELECT p.participant_id, p.participant_code FROM adm_participants p WHERE p.status=1");
                    $sql->execute();
                    foreach ($sql as $res) {
                      echo'<option value="'.$res['participant_code'].'">'.$res['participant_code'].'</option>';
                    }
                  ?>
                  <option value="ALL">ALL</option>
                </select>
                <span id="brokerErrorMsg" style="color: red;"></span>
              </div>
              <div class="col-lg-6 col-md-6" id="cd"></div>
            </div>
            <div class="box-footer">
              <div class="col-lg-4 col-md-4">
                <button type="button" class="btn btn-success" id="terminalUser" name="terminalUser">Generate</button>
              </div>
            </div>
          </form> 
        </div> 
        <div id="details"></div>
      </section>
    </div>
    <?php include('../NAV/footer.php'); ?>
  </div>
</body>
<script type="text/javascript">
  $('#terminalUser').click(function(){
    showLoading();
    var broker = $("#broker").val();
    var op = 'TerminalUserReport';

    if (broker == '') {
      hideloading();
      $('#brokerErrorMsg').html('Select Participation Code');
    } else {
      $.ajax({
        type: "POST",
        url: "loadReport.php",
        data: 'broker='+broker+'&TerminalUserReport='+op,
        dataType: "html",
        success: function(data){
          hideloading();
          $("#details").show().html(data);
        }
      });
    }
  });
  
  $('#broker').click( function() {
    $('#brokerErrorMsg').html('');
  });
</script>
</html>