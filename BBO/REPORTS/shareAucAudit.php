<?php 
  include ('../FILES/session_start_file.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
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
      <!-- Main content -->
      <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Share Auction</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
            </div>
          </div>
          <br>
          <div class="col-md-6">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
              <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
            </div>
          </div>
          <div class="col-md-6">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
            </div>
          </div>
          <br><br><br><br>
          <div class="box-footer">
            <div class="col-md-6">          
              <button type="button"  class="btn btn-success" id="rightsaudit" name="rightsaudit"> Generate </button>
            </div>
          </div>    
         </div>
        <div id="details"></div>         
      </section>
    </div>
  </div>
<?php include('../NAV/footer.php') ?>  
<script type="text/javascript">
  function getState2(val) 
  {
    $.ajax({ type: "POST", url: "load.php", data:'mat='+val,success: function(data){ $("#cd").html(data);} });
  }
</script>
<script type="text/javascript">
  $('#rightsaudit').click(function(){
    showLoading();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    var op = 'shareAuctionAudit';
    $.ajax({
      type: "POST",
      url: "rights_load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&shareAuctionAudit='+ op,
      success: function(data){
        hideloading();
        $("#details").show().html(data);
      }
    });
  });
</script>
<script>
  function checkDate() {
    var f= document.getElementById("to_date").value;
    var from= new Date(f);
    var t= document.getElementById("from_date").value;
    var to= new Date(t);
     if (from < to)
     {
         alert("To date should be greater than From date ");
         return false;
     }
     else
     {
         return true;
     }
  }
</script>
</body>
</html>