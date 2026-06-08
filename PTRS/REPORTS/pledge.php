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
            <h4 class="box-title">Pledge Activity</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-2 col-md-2">
              <label>Pledge Contract Code</label>
              <input type="number" class="form-control" onKeyPress="if(this.value.length==15) return false;" name="pledgeContCode" id="pledgeContCode" onChange="getState2(this.value);" required>
            </div>
            <div id="cd"></div>
          </div>
          <div class="box-footer">
            <div class="col-xs-4">          
              <button type="button" class="btn btn-success" style='display: none;' id="pledge" name="pledge"><i class="fa fa-list"></i>  Generate </button>
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
    $("#pledge").hide();
    $("#details").hide();
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'pledge='+val,
      dataType: "html",
      success: function(response){ 
        $("#cd").html(response);
      } 
    });
  }

  $('#pledge').click(function(){ 
    showLoading();
    var pledgeContCode = $("#pledgeContCode").val();
    var op = 'pledgeActivity';
    $.ajax({
      type: "POST",
      url: "loadReport.php",
      data: 'pledgeActivity='+ op +'&pledgeContCode='+pledgeContCode,
      dataType: "html",
      success: function(response){
        hideloading();
        $("#details").show().html(response);
      }
    });
  });
</script>
</html>