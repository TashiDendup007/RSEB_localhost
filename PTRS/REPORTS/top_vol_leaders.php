<?php 
  include("../FILES/session_file.php");
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-yellow sidebar-mini">
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
            <h4 class="box-title">Top Volume Leaders</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-4 col-md-4">
              <label>Symbol</label>
              <input type="text" class="form-control" name="symbol" id="symbol" onChange="getState2(this.value);" required>
            </div>
            <div id="cd"></div>  
          </div>
          <div class="box-footer">
            <div class="col-lg-4">          
              <button type="button" class="btn btn-success" style='display: none;' id="tvl" name="tvl" value=""><i class="fa fa-list"></i>  Generate </button>
            </div>
          </div> 
        </div> 
        <div id="details"></div>  
      </section>
    </div>
    <?php include('../NAV/footer.php') ?> 
  </div>
</body>
<script type="text/javascript">
  function getState2(val) {
    $("#tvl").hide();
    $("#details").hide();
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'symbolTV='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  $('#tvl').click( function() { 
    showLoading();
    var symbol = $("#symbol").val();
    var top = $("#top").val();
    var op = 'topVolLead';
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'topVolLead='+ op +'&symbol='+symbol +'&top='+top,
      success: function(data){
        hideloading();
        $("#details").show().html(data);
      }
    });
  });
</script>
</html>