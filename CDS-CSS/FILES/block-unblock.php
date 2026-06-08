<?php 
    include ('sessionStartFile_cdscss.php');
    include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); include('../../CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
<div class="wrapper">
<?php include('../NAV/navigation.php'); ?>
  <div class="content-wrapper">
    <div class="box-body" id="message" style='display: none;'></div>
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Block/Unblock</a></li>      
      </ol>
    </section>
    <section class="content"><div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Block/Unblock</h4>
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
                <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="getState2(this.value);" required>
              </div> 
              <div id="cd"></div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">            
                <button type="button" class="btn btn-success"  id="block" name="block" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-plus"></i>  Block </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-warning"  id="unblock" name="unblock" value="<?php echo $_SESSION['sess_username'];?>"><i class="fa fa-minus"></i>  Unblock </button>
            </div>
          </div>
        </form>
      </div>
      
      <div class="row">
        <div class="col-lg-12">
          <div class="box">
            <div class="box-body">
              <div class="box-header with-border"></div>
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
                  <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" id="bl_unbl" name="bl_unbl" value=""><i class="fa fa-list"></i>  List </button>
              </div>
            </div>
            <div id="details"></div>
          </div>
        </div>
      </div>

    </section>
  </div>
</div>
<?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  function getState2(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'block='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  function loadsymbol(val)  {
    $("#block").show();
    var acc = document.getElementById('cdcode').value;
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'sydunblock='+val+'&accd_block='+acc,
      dataType: "html",
      success: function(data){ 
        $("#block_vol_avl").html(data);
      } 
    });
  }

$("#block").click( function() { 
  showLoading();
  var cdCode = $("#cdcode").val();
  var symbol = $("#sy").val();
  var volume = $("#blockhol").val();
  var maxblock=$("#available_volume_to_block").val();
  var remark = $("#rm").val();
  var user_name = $("#block").val();
  
  if (Number(maxblock) < Number(volume)) {
    alert('Unblock volume cannot be higher than blocked volume');
    hideloading();
    return false;
  }

  var dataString = 'cdcode='+ cdCode +'&sy='+ symbol + '&block_vol='+ volume +'&rm='+ remark + '&user_name='+ user_name; 

  if(cdCode === '' || symbol === '' || volume === '') {
    alert("Please Fill All Mandatory Fields ");
    hideloading();
    return false;
  } else {
    if (confirm("Are you sure you want to Continue ?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#cdcode").val("");
          $("#sy").val("");

          $("#message").html(response);
          showMessage();
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
});

$("#unblock").click( function() { 
  showLoading();
  var cdCode = $("#cdcode").val();
  var symbol = $("#sy").val();
  var volume = $("#blockhol").val();
  var maxblockun=$("#available_volume_to_unblock").val();
  var remark = $("#rm").val();
  var user_name = $("#block").val();
  if(Number(volume) > Number(maxblockun)) {
      alert('Unblock volume cannot be higher thn blocked volume');
      hideloading();
      return false;
  }
  
  var dataString = 'cdcode='+ cdCode +'&sy='+ symbol + '&unblock_vol='+ volume +'&rm='+ remark + '&user_name='+ user_name;  
  if(cdCode==''|| symbol==''|| volume =='' || remark == '') {
    alert("Please Fill All Mandatory Fields ");
    hideloading();
    return false;
  } else {
    if (confirm("Are you sure you want to Continue ?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString ,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#cdcode").val("");
          $("#sy").val("");

          $("#message").html(response);
          showMessage();
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
  return false;
});

 function checkDate() {
  var f = document.getElementById("to_date").value;
  var from = new Date(f);
  var t = document.getElementById("from_date").value;
  var to = new Date(t);
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

$('#bl_unbl').click( function() {
  showLoading();
  var toDate = $("#to_date").val();
  var fromDate = $("#from_date").val();
  var op = 'blockshow';
  $.ajax({
    type: "POST",
    url: "load.php",
    data: 'toDate='+toDate +'&fromDate='+fromDate +'&blockshow='+ op,
    dataType: "html",
    success: function(data){
      hideloading();
      $("#details").show().html(data);
    }
  });
});
</script>
</html>
