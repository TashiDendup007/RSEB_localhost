<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="2")
  {
    header('Location: ../../access.php?err=2'); die();
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); die();
    }
  }
  $_SESSION['timeout'] = time();
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
  <div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
  <div class="wrapper">
    <?php include('../NAV/navigation.php') ?>
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
            <h4 class="box-title">Pledge Activity</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
              <label>Pledge Contract Code</label>
              <input type="number" class="form-control" onKeyPress="if(this.value.length==15) return false;" name="pledgeContCode" id="pledgeContCode" onChange="getState2(this.value);" required>
            </div>
            <div id="cd"></div>
          </div>
          <div class="box-footer">
            <div class="col-lg-4">          
              <button type="button" class="btn btn-success" style='display: none;' id="pledge" name="pledge" value=""><i class="fa fa-bars"></i>  Generate </button>
            </div>
          </div> 
        </div>
        <div id="details"></div>    
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?>
</body>
<script type="text/javascript">
function showLoading() {
  document.getElementById('loadingmsg').style.display = 'block';
  document.getElementById('loadingover').style.display = 'block';
}
function hideloading() {
  document.getElementById('loadingmsg').style.display = 'none';
  document.getElementById('loadingover').style.display = 'none';
}

function getState2(val) 
{
  $("#pledge").hide();
  $("#details").hide();
  $.ajax({ 
    type: "POST", 
    url: "../../CDS-CSS/REPORTS/load.php", 
    data:'pledge='+val,
    success: function(data){ 
      $("#cd").html(data);
    } 
  });
}

$('#pledge').click(function(){ 
  showLoading();
  var pledgeContCode = $("#pledgeContCode").val();
  var op = 'pledgeActivity';
  $.ajax({
    type: "POST",
    url: "../../CDS-CSS/REPORTS/loadReport.php",
    data: 'pledgeActivity='+ op +'&pledgeContCode='+pledgeContCode,
    success: function(data){
      hideloading();
      $("#details").show().html(data);
    }
  });
});
</script>
</html>