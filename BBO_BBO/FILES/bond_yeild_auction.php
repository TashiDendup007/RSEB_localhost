<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
  
  $list= ins_id($username);
  $ins_id=$list[0];
  $p_code=$list[1];
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
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
          <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Bond</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Bond Yeild</h4>
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
                 <div class="col-lg-3 col-md-3">
                    <label>CD Code<font color="red">*</font></label>
                    <input type="text" class="form-control" maxlength="10" name="cdcode" id="cdcode" onChange="cdepCode(this.value);" required>
                  </div>
                  <div id="cd"></div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-4 col-md-4">
                  <button type="button" class="btn btn-primary" style='display: none;' name="bondSave" id="bondSave"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header"></div>
              <div class="box-body">
                <div class="col-lg-6 col-md-6">
                  <label>From Date<font color="red">*</font></label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="date" class="form-control pull-right" name="from_date" id="from_date" required>
                  </div>
                </div>
                <div class="col-lg-6 col-md-6">
                  <label>To Date<font color="red">*</font></label>
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
                  <button type="button" class="btn btn-success" id="rightsI" name="rightsI" value=""><i class="fa fa-list"></i>  List</button>
                </div>
              </div>
            </div>
            <div class="box" width="100%" style="display: none;" id="tableId">
              <div class="box-body">
                <div id="details"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script type="text/javascript">
  function cdepCode(val) {
    $.ajax({ 
      type: "POST", 
      url: "load.php", 
      data:'bondCDYeildRate='+val,
      dataType: 'html',
      success: function(response) { 
        $("#cd").html(response);
      } 
    });
  }

  function checkDate() {
    var f= document.getElementById("record_date").value;
    var from= new Date(f);
    var t= document.getElementById("announcement_date").value;
    var to= new Date(t);
     if (from < to)
     {
         alert("Record date should be greater than Announcement date ");
         return false;
     }
     else
     {
         return true;
     }
   }

  $("#bondSave").click( function() { 
    showLoading();
    var cdFld = $("#cdcode").val();
    var cidFld = $("#cid").val();
    var symbol_idFld = $("#sy").val();
    var face_valueFld = $("#face_value").val();
    var bidpriceFld = $("#bidPrice").val();
    var volumeFld = $("#volume").val();
    var bondMechanismFld = 'YR';
    var operation = "save_bond";

    var data = {
      cdcode: cdFld,
      cid: cidFld,
      symbol_id: symbol_idFld,
      face_value: face_valueFld,
      bidprice: bidpriceFld,
      volume: volumeFld,
      bondMechanism: bondMechanismFld,
      save_bond: operation,
    };

    if(cdFld === '' || symbol_idFld === '' || bidpriceFld === '' || volumeFld === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/bond_process.php",
        data: data,
        dataType: 'html',
        success: function(response) {
          hideloading();
          $("#message").html(response);
          showMessage();
          $("#cdcode").val('');
          $("#sy").val('');
          $("#bidPrice").val('');
        }
      });
    }
    return false;
  });

  $('#rightsI').click(function(){
    showLoading();
    var toDate = $("#to_date").val();
    var fromDate = $("#from_date").val();
    var op = 'viewbond';
    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate='+toDate +'&fromDate='+fromDate +'&viewbond='+ op,
      dataType: "html",
      success: function(response) {
        hideloading();
        $("#tableId").show();
        $("#details").html(response);
      }
    });
  });
</script>
</html>
