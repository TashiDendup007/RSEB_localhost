<?php
  include ('sessionStartFile_cdscss.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
  <?php include('../../CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); ?>
    <div class="content-wrapper">
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Transfer</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Account Transfers</h4>
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
                <div class="col-lg-6 col-md-6 col-sm-12 has-warning">
                   <label class="control-label" for="inputWarning">From Account</label>
                    <input type="text" class="form-control" maxlength="10" name="F_cd" id="F_cd" onChange="getState2(this.value);" required>
                  </div>
                  <div  id="cd"></div>
                </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-4 col-md-6 col-sm-12">
                  <button type="button" class="btn btn-primary" style="display:none;" value="<?php echo $_SESSION['sess_username'];?>" name="transfer" id="transfer"><i class="fa fa-save"></i> Submit</button>
              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header  with-border" >
                <div class="col-lg-8 col-md-6 col-sm-12">
                  <h4 class="box-title">Search Transfer Details</h4>
                </div>
              </div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6 col-sm-12">
                  <label>From Date</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" onChange="return checkDate();" required>
                  </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
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
                <button type="button" class="btn btn-success" id="tr" name="tr" value=""><i class="fa fa-list"></i>  Load Data </button>
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
    data:'fro='+val,
    dataType: "html",
      success: function(response){ 
      $("#cd").html(response);
    } 
  });
}

function toAccount(val) {
  var fromCdcode = $("#F_cd").val();
  if (val.toUpperCase() === fromCdcode.toUpperCase()) {
    $("#to_cdcode_error").html("Cannot transfer to same CD Code");
    return false;
  } else {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'to='+val,
      dataType: "html",
      success: function(response){ 
        $("#to").html(response);
      } 
    });
  }
}

function selectSymbol(val) {
  var acc = document.getElementById('F_cd').value;
  $.ajax({ 
    type: "POST", 
    url: "load.php", 
    data:'sy='+val+'&acc='+acc,
    dataType: "html",
    success: function(response) { 
      $("#vol_avl").html(response);
    } 
  });
}

$("#hol").keyup('input', function() {
  var volume = $("#hol").val();
  var avail_vol = $("#available_volume").val();
  if(Number(avail_vol) >= Number(volume)) {
    $("#demat").show();
  } else if(volume === '') {
    $("#demat").hide();
  } else {
    $("#demat").hide();
  }
});

$("#transfer").click( function () {
  showLoading();
  var fromCd = $("#F_cd").val();
  var toCd = $("#T_cd").val();
  var symbol = $("#sy").val();
  var remarks = $("#remarks").val();
  var trsVol = $("#trs").val();
  var userName = $("#transfer").val();
  var operation = "transfer";
  var dataString = 'F_cd='+ fromCd +'&T_cd='+ toCd + '&sy='+ symbol + '&remarks='+ remarks + '&trs='+ trsVol + '&userName='+ userName + '&transfer='+ operation;

  if(fromCd==''|| toCd==''|| symbol =='' || remarks =='' || trsVol =='') {
    alert("Please Fill All Mandatory Fields");
    hideloading();
  } else {
    if (confirm("Are you sure you want to Continue ?")) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: dataString,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#F_cd").val("");
          $("#cd").hide();
          $("#message").html(response);
          showMessage();
          
          setTimeout(function() {
              location.reload();
          }, 1000); // 1000 milliseconds = 1 second
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

$('#tr').click(function(){
  showLoading();
  var toDate = $("#to_date").val();
  var fromDate = $("#from_date").val();
  var op = 'trans';

  $.ajax({
    type: "POST",
    url: "load.php",
    data: 'toDate='+toDate +'&fromDate='+fromDate +'&trans='+ op ,
    success: function(data){
      hideloading();
      $("#details").show().html(data);
    }
  });
});
</script>
</html>
