<?php 
  include('../FILES/sessionStartFile_cdscss.php');
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
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Netted Summary</a></li>      
      </ol>
    </section>
    <section class="content">
      <div class="modal fade" id="myModal" role="dialog"></div>
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Clearing & Settlement Instruction</h4>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">

          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>Security Type<font color="red">*</font></label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-file"></i>
              </div>
              <select name="sec_type" id="sec_type" class="form-control" required>
                <option value="">--Select--</option>
                <option value="OS">Equity</option>
                <option value="CGB">Bond/Debt</option>
              </select>
            </div>
            <span id="sectypeErr" style="color: red;"></span>
          </div>

          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>From Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="from_date1" id="from_date1" required>
            </div>
          </div>

          <div class="col-lg-4 col-md-4 col-sm-12">
            <label>To Date</label>
            <div class="input-group date">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="date" class="form-control pull-right" name="to_date1" id="to_date1" required>
            </div>
          </div>
        </div>
        <div class="box-footer">
            <div class="col-lg-6 col-md-6">          
              <button type="button" class="btn btn-success" id="detailNet" name="detailNet"><i class="fa fa-list"></i>  Generate </button>
            </div>
        </div>
        <div id="details"></div>
      </div>  
    </section>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</div>
<script type="text/javascript">
  $('#detailNet').click( function() {
    showLoading();
    var sec_type = $("#sec_type").val();
    var fromDate1 = $("#from_date1").val();
    var toDate1 = $("#to_date1").val();
    var Clearing = 'Clearing';

    if (sec_type == '') {
      hideloading();
      $("#sectypeErr").html("Select Security Type");
      return false;
    }

    $.ajax({
      type: "POST",
      url: "load.php",
      data: 'toDate1=' + toDate1 + '&fromDate1=' + fromDate1 + '&Clearing=' + Clearing + '&sec_type=' + sec_type,
      dataType: "html",
      success: function(data){
        hideloading();
        $("#details").html(data);
      }
    });
  });

  $('#sec_type').click(function() {
    $("#sectypeErr").html("");
  });
</script>
</body>
</html>