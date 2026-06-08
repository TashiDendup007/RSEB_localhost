<?php 
  include('sessionStartFile_cdscss.php');
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
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Deposit/Withdraw</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Deposit/Withdraw</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="" method="post" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row"> 
                 <div class="col-lg-4 col-md-4">
                  <label>CD Code</label>
                  <input type="text" class="form-control" style="text-transform:uppercase;" maxlength="10" name="cdcode" id="cdcode" onChange="getState2(this.value);" required>
                </div> 
                <div id="cd"></div>                
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-4 col-md-4">            
                <button type="button" class="btn btn-success" style="display:none;" id="mat" name="mat" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Deposit </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-warning" style="display:none;" id="demat" name="demat" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-minus"></i>  Withdraw </button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-body">
                <div class="col-lg-6 col-md-6">
                  <label>From Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                  </div>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label>To Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="to_date" id="to_date"  required>
                  </div>
                </div>
              </div>
              <div class="box-footer">
                <div class="col-lg-6">          
                  <button type="button" class="btn btn-success" id="dep" name="dep" value=""><i class="fa fa-list"></i>  List </button>
                </div>
              </div>
              <div id="details"></div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>
<?php include('../NAV/footer.php') ?>  
<script type="text/javascript">
function getState2(val) {
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'mat='+val,
    dataType: "html",
    success: function(response) { 
      $("#cd").html(response);
    } 
  });
}

function loadsymbol(val) {
  $("#mat").show();
  var acc = document.getElementById('cdcode').value;
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'syd='+val+'&accd='+acc,
    dataType: "html",
    success: function(response){ 
      $("#vol_avl").html(response);
    } 
  });
}

$("#mat").click(function() { 
  showLoading();
  var cdCode = $("#cdcode").val();
  var symbol = $("#sy").val();
  var volume = $("#hol").val();
  var remark = $("#rm").val();
  var operation = "mat";
  var dataString = 'cdcode='+ cdCode +'&sy='+ symbol + '&hol='+ volume +'&rm='+ remark + '&mat='+ operation;

  if (cdCode==''|| symbol==''|| volume =='') {
    alert("Please Fill All Mandatory Fields");
    hideloading();
  } else {
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data: dataString ,
      success: function(data){
        hideloading();
        $("#message").html(data);
        showMessage();
        $("#cdcode").val("");
        // location.reload();
      }
    });
  }
  return false;
});

$("#demat").click(function(){ 
  showLoading();
  var cdCode = $("#cdcode").val();
  var symbol = $("#sy").val();
  var volume = $("#hol").val();
  var remark = $("#rm").val();
  var operation = "demat";
  var dataString = 'cdcode='+ cdCode +'&sy='+ symbol + '&hol='+ volume +'&rm='+ remark + '&demat='+ operation;
  if(cdCode === ''|| symbol === '' || volume === '') {
    alert("Please Fill All Mandatory Fields");
    hideloading();
  } else {
    $.ajax({
    type: "POST",
    url: "../PROCESS/process.php",
    data: dataString,
    success: function(data){
      hideloading();
      $("#message").html(data);
      showMessage();
      $("#cdcode").val("");
      // location.reload();
    }
    });
  }
  return false;
});

function checkDate() {
  var f= document.getElementById("to_date").value;
  var from= new Date(f);
  var t= document.getElementById("from_date").value;
  var to= new Date(t);
  if (from < to) {
       alert("To date should be greater than From date ");
       return false;
   } else {
       return true;
   }
}

$('#dep').click(function(){
  showLoading();
  var toDate = $("#to_date").val();
  var fromDate = $("#from_date").val();
  var op = 'dep';
  $.ajax({
    type: "POST",
    url: "load.php",
    data: 'toDate='+toDate +'&fromDate='+fromDate +'&dep='+ op,
    dataType: "html",
    success: function(data) {
      hideloading();
      $("#details").html(data);
    }
  });
});
</script>
</body>
</html>
