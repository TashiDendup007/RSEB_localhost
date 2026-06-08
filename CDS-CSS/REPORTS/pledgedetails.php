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
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Pledge Details</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-3 col-md-3">
              <label>Select Type</label>
              <select class="form-control" id="plType" name="plType" onChange="getStateSP(this.value);">
                <option value="">--Select Type--</option>
                <option value="S">By Symbol</option>
                <option value="P">By Pledgee</option>
              </select>
            </div>
            <div id="cd"></div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">          
              <button type="button" class="btn btn-success" style='display: none;' id="pldetails" name="pldetails"><i class="fa fa-list"></i>  Generate </button>
            </div>
          </div>
        </div>  
        <div id="details"></div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
function getStateSP(val) {
  $("#pldetails").hide();
  $("#details").hide();
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'symbolPlType='+val,
    dataType: "html",
    success: function(data){ 
      $("#cd").html(data);
    } 
  });
}

function getState2(val) {
  $("#pldetails").hide();
  $("#details").hide();
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'symbolPlDetails='+val,
    dataType: "html",
    success: function(data){ 
      $("#symbolDetails").html(data);
    } 
  });
}

function getState3(val) {
  $("#pldetails").hide();
  $("#details").hide();
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'plSybolDetails='+val,
    dataType: "html",
    success: function(data){ 
      $("#pledgeDetails").html(data);
    } 
  });
}

$('#pldetails').click( function() {
  showLoading();
  var symbol = $("#symbol").val();
  var plType = $("#plType").val();
  var op = 'pledgeDetails';

  $.ajax({
    type: "POST",
    url: "loadReport.php",
    data: 'pledgeDetails='+ op +'&symbol='+symbol +'&plType='+plType,
    dataType: "html",
    success: function(response){
      hideloading();
      $("#details").show().html(response);
    }
  });
});
</script>
</html>