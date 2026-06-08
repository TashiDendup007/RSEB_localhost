<?php
include ('sessionStartFile_cdscss.php');
include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
<?php include('../../CONNECTIONS/confirmationMessage.php') ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
<div class="wrapper">
  <?php include('../NAV/navigation.php') ?>
  <div class="content-wrapper">
    <div id="message" ></div>
    <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Pledge Contract</a></li>
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Pledge Contract</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <form action="../PROCESS/process.php" method="post" onsubmit="showLoading();">
          <div class="box-body">
            <div class="row" >
              <div class="col-lg-6 col-md-6 col-sm-12">
               <label>Contract Code</label>
                <input type="text" class="form-control" name="cc" id="cc"  value="<?php echo date("ymdhis");?>" readonly>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-12">
                <label>Pledgee</label>
                <select name="pl" id="pl"  class="form-control" >
                  <option value=""> Select Pledgee </option>
                  <?php
                    $wc = $dbh->prepare("SELECT * FROM cds_pledgee");
                    $wc->execute();
                    while ($res= $wc->fetch()) {
                      echo '<option value="'.$res['pledgee'].'">'.$res['pledgee'].'</option>';
                    }
                    echo'';
                  ?>
                </select>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-12">
               <label>CD Code</label>
                <input type="text" class="form-control" maxlength="10" name="ac" id="ac" onChange="cdCod(this.value);"  required>
              </div>
              <div  id="cd"></div>
              <div class="col-xs-12">
               <label>Remarks</label>
                <input type="text" class="form-control" name="remarks" id="remarks" required>
              </div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6 col-sm-12">
              <button type="button" class="btn btn-primary" value="<?php echo $_SESSION['sess_username'];?>" name="pledge" id="pledge"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border"></div>
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
              <button type="button" class="btn btn-success" id="pl_cont" name="pl_cont" value=""><i class="fa fa-list"></i>  Load </button>
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
  function symb(val) {
    var acc = document.getElementById('ac').value;
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'sy1='+val+'&acc1='+acc,
      dataType: "html",
      success: function(data){ 
        $("#vol_avl").html(data);
      } 
    });
  }

  function cdCod(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'fro1='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  function getState(val) {
    $.ajax({ 
      type: "POST",
      url: "e-cds-css.php",
      data:'edit_plg_contra='+val, 
      dataType: "html",
      success: function(data){ 
        $("#myModal").html(data);
      }
    });
  }

  $("#pledge").click( function() {
    showLoading();
    var contCode = $("#cc").val();
    var pledgee = $("#pl").val();
    var cdCode = $("#ac").val();
    var remarks = $("#remarks").val();
    var userName = $("#pledge").val();
    var operation = "pledge_contract";
    var dataString = 'cc='+ contCode +'&pl='+ pledgee + '&ac='+ cdCode + '&remarks='+ remarks + '&userName='+ userName + '&pledge_contract='+ operation;

    if(pledgee === '' || cdCode === '' || remarks === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      if (confirm("Are you sure you want to Continue ?")) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/process.php",
          data: dataString ,
          success: function(data){
            hideloading();
            $("#ac").val("");
            $("#remarks").val("");
            $("#message").html(data);
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
    
    if (from < to) {
         alert("To date should be greater than From date ");
         return false;
    } else {
         return true;
     }
  }

  $('#pl_cont').click(function(){
    showLoading();
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();
    var op = 'pledge_contract';
    
    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&pledge_contract='+ op,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });
</script>
</html>
