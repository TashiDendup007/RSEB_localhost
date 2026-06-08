<?php 
    session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="2")
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
<body class="hold-transition skin-red sidebar-mini"><div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
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
         <li><a href="#">Cash Transaction</a></li>      
      </ol>
    </section>
    <!-- Main content -->
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Cash Transaction</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
            <div class="box">
            <div class="col-xs-4">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date1" id="from_date1" onChange="return checkDate1();" required>
            </div>
            </div>
            <div class="col-xs-4">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date1" id="to_date1" required>
            </div>
            </div><br><br><br><br>
            <div class="box-footer">
                <div class="col-xs-4">          
                    <button type="button" class="btn btn-success" id="cashtrnx" name="cashtrnx" value="">  Generate </button>
            </div>
            </div>
            <div id="details">
            </div>
          </div>    
    </section>
  </div>
<?php include('../NAV/footer.php') ?>  
<script type="text/javascript">
function showLoading() {
    document.getElementById('loadingmsg').style.display = 'block';
    document.getElementById('loadingover').style.display = 'block';
}
function hideloading() {
    document.getElementById('loadingmsg').style.display = 'none';
    document.getElementById('loadingover').style.display = 'none';
}
</script>  
<script type="text/javascript">
 function checkDate1(){
            var f= document.getElementById("to_date1").value;
            var from= new Date(f);
            var t= document.getElementById("from_date1").value;
            var to= new Date(t);
             if (from < to){
                 alert("To date should be greater than From date ");
                 return false;
             }
             else{
                 return true;
             }
         }
         $('#cashtrnx').click(function(){
            showLoading();
            var toDate1 = $("#to_date1").val();
            var fromDate1 = $("#from_date1").val();
            var ctransaction = 'cashtrnx';
            $.ajax({
            type: "POST",
            url: "load_bbo_report.php",
            data: 'toDate1='+toDate1 +'&fromDate1='+fromDate1 +'&cashtrnx='+ ctransaction,
            success: function(data){
              hideloading();
              $("#details").html(data);
            }
            });
      });
</script>
</body>
</html>