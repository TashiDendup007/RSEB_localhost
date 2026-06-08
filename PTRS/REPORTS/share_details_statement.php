<?php 
  include('../FILES/session_file.php');
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
           <li><a href="#">Reports</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Share History Statement</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <div class="box-body">
            <div class="col-lg-6 col-md-6">
              <label for="cid_no">CID/DISN No</label>
              <input type="text" class="form-control" name="cid_no" id="cid_no" onChange="getState2(this.value);" required>
            </div>
            <div id="cd"></div>
          </div>
          <div class="box-footer">
            <div class="col-lg-6 col-md-6">
              <button type="button" style="display:none;" class="btn btn-success" id="generate_button" name="generate_button" value=""><i class="fa fa-list"></i>  Generate </button>
            </div>
          </div>
        </div>
        <div id="details"></div>
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
      data:'get_dtls_cid='+val,
      dataType: "html",
      success: function(data){ 
        $("#cd").html(data);
      } 
    });
  }

  $('#generate_button').click( function () {
    showLoading();
    var cid = $("#cid_no").val();
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();

    if (cid === '' || from_date === '' || toDate === '') {
      hideloading();
      alert("Required All Fields");
      return false;
    } else {
      var data = {
        get_share_dtls_statement : 'get_share_dtls_statement',
        cid_no : cid,
        from_date : fromDate,
        to_date : toDate,
      }

      $.ajax({
        type: "POST",
        url: "loadReport.php",
        data: data,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#details").html(response);
        }
      });
    }
  });

  function showLoading() {
      document.getElementById('loadingmsg').style.display = 'block';
      document.getElementById('loadingover').style.display = 'block';
  }
  
  function hideloading() {
      document.getElementById('loadingmsg').style.display = 'none';
      document.getElementById('loadingover').style.display = 'none';
  }
</script>
</html>