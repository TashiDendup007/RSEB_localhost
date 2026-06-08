<?php 
  date_default_timezone_set("Asia/Thimphu");
  include('../FILES/session_file.php');
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
          <li><a href="ptrs_landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Unlock</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">User Unlock</h4>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body">
                <div class="col-xs-4">
                  <label>CID Number <font color="red">*</font></label>
                  <div>
                    <input type="number" class="form-control pull-right" name="cidNo" id="cidNo" required>
                    <span id="cidErrorMsg" style="color: red;"></span>
                  </div>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-xs-4">
                  <button type="button" class="btn btn-success" id="get_user_dtls" name="get_user_dtls"><i class="fa fa-tasks"></i> Check</button>
                </div>
              </div>

              <div id="details"></div>
              
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  $(document).ready( function() {
      $('#tableExpId').DataTable();
  });

  $('#get_user_dtls').click( function() {
    showLoading();
    var cidNo = $("#cidNo").val();
    if (cidNo == '') {
      hideloading();
      $('#cidErrorMsg').html('CID Number required');
    } else {
      var op = 'get_user_account_dtls';
      $.ajax({
        type: "POST",
        url: "load.php",
        data: 'cidNo=' + cidNo + '&get_user_account_dtls=' + op,
        dataType: 'html',
        success: function(data){
          hideloading();
          $("#details").html(data);
        }
      });
    }
  });

  $('#cidNo').click(function(){
    $('#cidErrorMsg').html('');
  });
</script>
</html>

