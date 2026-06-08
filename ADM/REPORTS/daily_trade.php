<?php 
  include('../FILES/sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-purple sidebar-mini">
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
          <li><a href="Admin-dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Trade Report</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Daily Trade - Min, Max & Market Price</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-lg-12">
                <form action="" method="POST">
                  <div class="box-body">
                    <div class="row" ng-app="">
                      <div class="col-lg-4 col-md-4">
                        <label for="symbol">Symbol<font color="red">*</font></label>
                        <?php
                          $wc= $dbh->prepare("SELECT symbol_id, symbol FROM symbol WHERE security_type IN ('OS') AND trsstatus = 1 ORDER BY symbol ASC");
                          $wc->execute();
                          echo'<select name="symbol" id="symbol" class="form-control">
                          <option value="">--Select symbol--</option>';
                          while($res = $wc->fetch()) {
                          echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                          }
                          echo'</select>';
                        ?>
                        <span id="symbolErr" style="color: red;"></span>
                      </div>
                      <div class="col-lg-4 col-md-4">
                        <label for="year">Start Date<font color="red">*</font></label>
                        <input type="date" name="startDate" id="startDate" class="form-control">
                        <span id="startDateErr" style="color: red;"></span>
                      </div>
                      <div class="col-lg-4 col-md-4">
                        <label for="year">End Date<font color="red">*</font></label>
                        <input type="date" name="endDate" id="endDate" class="form-control">
                        <span id="endDateErr" style="color: red;"></span>
                      </div>
                    </div>
                  </div>
                  <div class="box-footer">
                    <div class="col-lg-6 col-md-6">
                      <button type="button" class="btn btn-primary" id="get_report_dtls"><i class="fa fa-database"></i> Submit</button>
                    </div>
                  </div>
                </form>
                <div id="details"></div>
                <div id="buttonId" style="display: none;">
                  &emsp;&ensp;
                  <a href="#" class="" onClick ="$('#tableListId').tableExport({type:'excel',escape:'false',fileName:'DateWiseReport'});">
                    <button class="btn btn-success" type="button"> Excel</button></a>
                  <p><span style="color: red;">NOTE:</span> Select "All" from the list menu to download all</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?> 
</body>
<script type="text/javascript"> 
  $("#get_report_dtls").on("click", function() {
    showLoading();
    var $symbolField = $("#symbol");
    var $strDateField = $("#startDate");
    var $endDateField = $("#endDate");
    
    if ($symbolField.val() == '') {
      hideloading(); 
      $("#symbolErr").html("Select Symbol");
      return false;
    }
    if ($strDateField.val() == '') {
      hideloading(); 
      $("#startDateErr").html("Select Start Date");
      return false;
    }
    if ($endDateField.val() == '') {
      hideloading(); 
      $("#endDateErr").html("Select End Date");
      return false;
    }

    var data = {
      symbol: $symbolField.val(),
      startDate: $strDateField.val(),
      endDate: $endDateField.val(),
      get_daywise_trade_dtls: "get_daywise_trade_dtls"
    };

    $.ajax({ 
      type: "POST", 
      url: "loadReport.php",
      data: data , 
      dataType: 'html',
      success: function(response){ 
        hideloading(); 
        $('#details').html(response); 
        $('#buttonId').show(); 
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("Error: "+textStatus+' ,'+errorThrown);
      }
    });
  });

  function getState(val) { 
    $.ajax({ 
      type: "POST",  
      url: "bbo-adm.php",  
      data:'edit_user='+val,  
      success: function(data){ 
        $("#myModal").html(data);  
      }
    });
  }

  $("#symbol").click( function() {
    $("#symbolErr").html("");
  });

  $("#startDate").click( function() {
    $("#startDateErr").html("");
  });

  $("#endDate").click( function() {
    $("#endDateErr").html("");
  });

  function doExport(selector, params) {
    var options={
      tableName: 'tableListId',
      worksheetName: 'DateWiseReport',
      fileName: 'DateWiseReport'
    };
    $.extend(true, options, params);
    $(selector).tableExport(options);
  }
</script>
</html>