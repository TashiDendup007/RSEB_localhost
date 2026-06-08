<?php 
    session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="3")
    {
      header('Location: ../../access.php?err=2');
    }
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
        { 
          header("Location: ../../Authentication/Logout.php"); 
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
<body class="hold-transition skin-green sidebar-mini"><div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
<!-- Site wrapper -->
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
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
    <section class="content">
      <!-- Default box -->
      <div class="box"><br>
        <div class="col-xs-3">
          <label>Dividend Year  <font color="red">*</font></label>
          <div class="input-group date">
            <div class="input-group-addon">
              <i class="fa fa-calendar"></i>
            </div>
            <input type="text" class="form-control date-pickeryear" name="div_year" id="div_year" placeholder="Choose Year..." readonly>
          </div>
        </div>
        <div class="col-xs-3">
          <label>Company Name</label>
          <?php
              $cn= $dbh->prepare("SELECT * from uc_companies");
              $cn->execute();
              echo'<select class="form-control" name="company_name" id="company_name" required="">';
              echo '<option value="ALL">-SELECT-</option>';
              while($res= $cn->fetch())
              {
                echo '<option value="'.$res['short_desc'].'">'.$res['company_name'].'</option>';
              }
              echo'</select>';
            ?>
        </div>
        <div class="col-xs-3">
          <label>Dividend Type</label>
          <select class="form-control" name="dt" id="dt">
            <option value="ALL">-SELECT-</option>
            <option value="Final">Final</option>
            <option value="Interim">Interim</option>
          </select>
        </div><br><br><br><br>
        <div class="box-footer">
            <div class="col-xs-4">          
            <button type="button" class="btn btn-success" id="escorDetails" name="escorDetails" value="">  Generate </button>
        </div>
        </div>
      </div>  
      <div id="details">
      </div>       
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>  
<script type="text/javascript">
  $(function() {
      $('.date-pickeryear').datepicker({
        format: "yyyy",
        startView: "years", // Show the year view first
        minViewMode: "years", // Only allow year selection
        autoclose: true,
        todayHighlight: true
      }).on('changeDate', function (ev) {
        $(this).datepicker('hide');
      });
    });

  function showLoading() {
      document.getElementById('loadingmsg').style.display = 'block';
      document.getElementById('loadingover').style.display = 'block';
  }
  function hideloading() {
      document.getElementById('loadingmsg').style.display = 'none';
      document.getElementById('loadingover').style.display = 'none';
  }
  $('#escorDetails').click(function(){
      showLoading();
      var div_year = $("#div_year").val();
      if(div_year == "")
      {
        hideloading();
        alert("Select Year")
      }
      else
      {
        var company_name = $("#company_name").val();
        var dt = $("#dt").val();
        var op = 'escorReport';
        $.ajax({
          type: "POST",
          url: "load.php",
          data: 'div_year='+div_year +'&company_name='+company_name+'&dt='+dt+'&escorReport='+op, 
          success: function(data){
            hideloading();
            $("#details").show().html(data);
          }
        });
      }
  });
</script>
</body>
</html>