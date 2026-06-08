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
    <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Pledge Release</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Pledge Release</h4>
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
                <label>Contract Code</label>
                <input type="text" class="form-control" name="cc" id="cc" onChange="contCode(this.value);"  required>
              </div>
              <div id="cd"></div>

              <div class="col-lg-4 col-md-4">
                <label>Symbol</label>
                <select name="sy" id="sy"  class="form-control" OnChange="symbplrl(this.value);">
                  <option value=""> Select Symbol </option>
                <?php
                  $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol");
                  $wc->execute();
                  while($res= $wc->fetch()) {
                    echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                  }
                ?>
                </select>
              </div>
              <div  id="vol_avl"></div>
            </div>
          </div>
          <div class="box-footer">
            <div class="col-lg-4 col-md-4">
                <button type="button" class="btn btn-primary" style="display:none;" value="<?php echo $_SESSION['sess_username'];?>" name="pledge_release" id="pledge_release"><i class="fa fa-save"></i> Save</button>
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
                  <input type="date" class="form-control pull-right" name="to_date" id="to_date" required>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">          
                <button type="button" class="btn btn-success" id="pl_rls" name="pl_rls" value=""><i class="fa fa-list"></i>  List </button>
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
  function symbplrl(val) {
    var acc = document.getElementById('ac1').value;
    var cc = document.getElementById('cc').value;
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'sy1='+val+'&acc_pl_rl='+acc+'&cc='+cc,
      dataType: "html",
      success: function(data){ 
        $("#vol_avl").html(data);
      } 
    });
  }

  function contCode(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'pl_release='+val,
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
      data:'edit_plg_rls='+val, 
      dataType: "html",
      success: function(data){ 
        $("#myModal").html(data);
      }
    });
  }

  $("#pledge_release").click( function() { 
    showLoading();
    var contractCode = $("#cc").val();
    var pledgee = $("#pl").val();
    var cd_code = $("#ac").val();
    var symbol = $("#sy").val();
    var remarks = $("#remarks").val();
    var pname = $("#pname").val();
    var vol_pl_rls= $("#rls").val();
    var userName = $("#pledge_release").val();
    var operation = "pledge_release";
    var dataString = 'cc='+ contractCode +'&pl='+ pledgee + '&ac='+ cd_code + '&sy='+ symbol + '&remarks='+ remarks + '&rls='+ vol_pl_rls + '&userName='+ userName + '&pledge_release='+ operation+ '&pname='+ pname;
    
    if(contractCode === '' || vol_pl_rls === '' || symbol === '' || remarks === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
       if (confirm("Are you sure you want to Continue ?")) {
          $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: dataString ,
            dataType: "html",
            success: function(data) {
              hideloading();

              $("#cc").val("");
              $("#sy").val("");

              $("#message").html(data);
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

  $('#pl_rls').click(function(){
    showLoading();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    var op = 'pledge_release';
    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&pledge_release='+ op,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });
</script>
</html>
