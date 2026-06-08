<?php 
date_default_timezone_set("Asia/Thimphu");
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
    <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Pledge</a></li>
      </ol>
    </section>
    <!-- Main content -->
      <section class="content">
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Pledge Securities</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
        <div class="box-body">
          <div class="box-body">
              <div class="row" >
              <div class="col-xs-3">
                 <label>Contract Code</label>
                  <input type="text" class="form-control"  onChange="loadall(this.value);" >
                </div>
                <div  id="cd">
                </div>
                 <div class="col-xs-4">
                  <label>Symbol</label>
                  <?php
                            $wc= $dbh->prepare("SELECT symbol,symbol_id FROM symbol WHERE security_type != 'OS'");
                            $wc->execute();
                            echo '<select name="sy" id="sy"  class="form-control" OnChange="symb(this.value);">';
                            echo '<option value=""> Select Symbol </option>';
                             while($res= $wc->fetch())
                            {
                            echo '<option value="'.$res['symbol_id'].'">';
                            echo $res['symbol'];
                            echo'</option>';
                            }
                            echo'</select>';
                    ?>
                </div>
                <div  id="vol_avl">
                </div>
                <div class="col-xs-12">
                 <label>Remarks</label>
                  <input type="text" class="form-control" name="remarks" id="remarks" required>
                </div>

              </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
        <div class="col-xs-4">
            <button type="button" class="btn btn-primary" style="display:none;" value="<?php echo $_SESSION['sess_username'];?>" name="pledge" id="pledge">SAVE</button>
        </div>
        </div>
        </form><br>
        <div class="row">
        <div class="col-xs-12">
          <div class="box"><br>

            <div class="col-xs-4">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
            </div>
            </div>
            <div class="col-xs-4">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date" id="to_date"  required>
            </div>
            </div><br><br><br><br>
            <div class="box-footer">
                <div class="col-xs-4">          
                    <button type="button" class="btn btn-success" id="pl" name="pl" value="">  List </button>
            </div>
            </div>
            <div id="details">
            </div>
          </div>
        </div>
      </div>
        <!-- /.box-footer-->
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>  
<script type="text/javascript">
function symb(val) 
{
  var acc = document.getElementById('ac').value;
  $.ajax({ type: "POST", url: "../../CDS-CSS/FILES/load.php", data:'sy1='+val+'&acc1='+acc,success: function(data){ $("#vol_avl").html(data);} });
}
</script>
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
function loadall(val) 
{
  $.ajax({ type: "POST", url: "../../CDS-CSS/FILES/load.php", data:'loadall='+val,success: function(data){ $("#cd").html(data);} });
}
</script>
<SCRIPT language=JavaScript>
function getState(val) 
{
  $.ajax({type: "POST",url: "../../CDS-CSS/FILES/e-cds-css.php",data:'edit_plg='+val, success: function(data){ $("#myModal").html(data);}});
}
</script>
<script type="text/javascript">
$(document).ready(function(){
$("#pledge").click(function(){ 
showLoading();
var contCode = $("#cc").val();
var pledgee = $("#pl").val();
var cdCode = $("#ac").val();
var symbol = $("#sy").val();
var remarks = $("#remarks").val();
var vol = $("#trs1").val();
var userName = $("#pledge").val();
var operation = "pledge";
var dataString = 'cc='+ contCode +'&pl='+ pledgee + '&ac='+ cdCode +'&sy='+ symbol + '&remarks='+ remarks + '&trs1='+ vol + 
'&userName='+ userName + '&pledge='+ operation;

if(pledgee==''|| cdCode==''|| symbol =='' || vol =='' || remarks =='')
{
  alert("Please Fill All Mandatory Fields");
  hideloading();
}
else
{
  if (confirm("Are you sure you want to Continue ?"))
   {
  $.ajax({
  type: "POST",
  url: "../../CDS-CSS/PROCESS/process.php",
  data: dataString ,
  success: function(data){
    hideloading();
    $("#message").show();
    $("#message").html(data);
    $('#message').fadeOut(5000);
    location.reload();
  }
  });
  }

else{
  hideloading();
  return false;

}
}
return false;
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
<script type="text/javascript">
$('#pl').click(function(){
            showLoading();
            var toDate = $("#to_date").val();
            var fromDate = $("#from_date").val();
            var op = 'pledge';
            var user= '<?php echo $_SESSION['sess_username'];?>';
            $.ajax({
            type: "POST",
            url: "../../CDS-CSS/FILES/load.php",
            data: 'toDate='+toDate +'&fromDate='+fromDate +'&pledge='+ op+'&user='+ user,
            success: function(data){
              hideloading();
              $("#details").show().html(data);
            }
            });
        });
</script>
</body>
</html>