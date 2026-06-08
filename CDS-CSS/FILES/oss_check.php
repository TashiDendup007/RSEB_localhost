<?php 
  include('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
  $id = isset($_GET['id']) ? $_GET['id'] : 0;
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
          <li><a href="#">OSS</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Online Share Statment</h4>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                    <i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                    <i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6">
                  <label>CID Number <font color="red">*</font></label>
                  <div>
                    <input type="number" class="form-control pull-right" name="cidNo" id="cidNo" required>
                    <span id="cidErrorMsg" style="color: red;"></span>
                  </div>
                </div>
              </div> 
              <div class="box-footer">
                <div class="col-lg-6 col-md-6">
                  <button type="button" class="btn btn-success" id="checkOSSDtls" name="checkOSSDtls"><i class="fa fa-tasks"></i> Check</button>
                </div>
              </div>
              <div id="details"></div>
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
      $('#tableExpId').DataTable();
  });

  $('#checkOSSDtls').click(function(){
    showLoading();
    var cidNo = $("#cidNo").val();
    if(cidNo == ''){
      hideloading();
      $('#cidErrorMsg').html('CID Number required');
    }else{
      var op = 'online_share_statement';
      $.ajax({
        type: "POST",
        url: "load.php",
        data: 'cidNo='+cidNo+'&online_share_statement='+op,
        dataType: "html",
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
