<?php 
  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="1")
  {
    header('Location: ../../access.php?err=2'); die();
  }
  $inactive = 1500;
  // check to see if $_SESSION['timeout'] is set
  if(isset($_SESSION['timeout'])) 
  {
    $session_life = time() - $_SESSION['timeout'];
    if($session_life > $inactive)
    { 
      header("Location: ../../Authentication/Logout.php"); die();
    }
  }
  $_SESSION['timeout'] = time();
  $username=$_SESSION['sess_username'];

  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php') ?>
    <div class="content-wrapper">
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Task Schedular</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header"></div>
              <form class="form-horizontal" action="#" method="POST">
                <div class="box-body">
                  <div class="col-lg-6 col-md-6">
                    <span style=""><strong>Trade Schedular Mode:</strong><font color="red">*</font></span>&nbsp;&nbsp;
                    <label class="switch">
                      <input type="checkbox" name="trade_mode" id="trade_mode">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <!-- <div class="col-lg-6 col-md-6">
                    <label>Trade Schedular Task Code:<font color="red">*</font></label>
                    <select class="form-control" name="tradeTaskCode" id="tradeTaskCode" required>
                      <option value="">--Select Task Code--</option>
                      <option value="TS">Trade Schedular(TS)</option>
                      <option value="IS">Index Schedular(IS)</option>
                    </select>
                    <span id="tradeCodeError" style="color: red;"></span>
                  </div> -->
                  <div class="col-lg-6 col-md-6">
                    <span style=""><strong>Index Schedular Mode:</strong><font color="red">*</font></span>&nbsp;&nbsp;
                    <label class="switch">
                      <input type="checkbox" name="index_mode" id="index_mode">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <!-- <div class="col-lg-6 col-md-6">
                    <label>Index Schedular Task Code:<font color="red">*</font></label>
                    <select class="form-control" name="indexTaskCode" id="indexTaskCode" required>
                      <option value="">--Select Task Code--</option>
                      <option value="TS">Trade Schedular(TS)</option>
                      <option value="IS">Index Schedular(IS)</option>
                    </select>
                    <span id="indexCodeError" style="color: red;"></span>
                  </div> -->
                  <div class="col-lg-12">
                    <label>Remarks<font color="red">*</font></label>
                    <textarea name="remarks" id="remarks" class="form-control" required></textarea>
                    <span id="remarksError" style="color: red;"></span>
                  </div>
                </div>
                <div class="box-footer">
                  <div class="col-lg-6 col-md-6">
                    <button type="button" class="btn btn-primary" id="schedular_switch"><i class="fa fa-database"></i> Submit</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <form class="form-horizontal" action="../PROCESS/process" method="POST">
                <div class="box-header"></div>
                <div class="box-body">
                  <div class="col-lg-12">
                    <div class="input-group input-group-md">
                      <select class="form-control" name="row" id="row" required>
                        <option value="0">--Select rows to load--</option>
                        <option value="10">10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="ALL">ALL</option>
                      </select>
                      <span class="input-group-btn">
                        <button type="button" id="loadLogs" class="btn btn-info btn-flat"><i class="fa fa-download"></i> Load</button>
                      </span>
                    </div>
                    <span id="rowError" style="color: red;"></span>
                  </div>
                </div>
                <div class="box-footer">
                  <div id="log_details"></div>
                </div>
              </form>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php') ?>
</body>
<script type="text/javascript">
  $(document).ready(function(){
    showLoading();
    var op = 'check_schedular_existed';
    $.ajax({
      type: "POST",
      url: "../PROCESS/process.php",
      data:'check_schedular_existed='+op,
      cache: false,
      success: function(data){
        hideloading();
        if(data['status'] == 200){
          if(data['trade_mode'] == 'ON'){
            $("#trade_mode").prop("checked", true);
          }else{
            $("#trade_mode").prop("checked", false);
          }
          if(data['index_mode'] == 'ON'){
            $("#index_mode").prop("checked", true);
          }else{
            $("#index_mode").prop("checked", false);
          }
          // $('#tradeTaskCode').val(data['tradeCode']);
          // $('#indexTaskCode').val(data['indexCode']);
          $('#remarks').val(data['remarks']);
        }else{
          $("#trade_mode").prop("checked", false);
          $("#index_mode").prop("checked", false);
          // $('#tradeTaskCode').val('');
          // $('#indexTaskCode').val('');
          $('#remarks').val('');
        }
      }
    });
  });

  $('#loadLogs').click(function(){
    showLoading();
    var row = $('#row').val();
    var op = 'get_schedular_logs';
    if(row == 0){
      $('#rowError').html("Select row");
      return false;
    }else{
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data:'get_schedular_logs='+op+'&row='+row,
        cache: false,
        success: function(data){
          hideloading();
          $("#log_details").html(data);
        }
      });
    }
    hideloading();
  });

  $('#schedular_switch').click(function(){
    showLoading();
    var trade = '';
    var index = '';
    var option = '';
    var remarks = $('#remarks').val();
    // var tradeCode = $('#tradeTaskCode').val();
    // var indexCode = $('#indexTaskCode').val();
    if($('#trade_mode').is(":checked")){
      trade = 'ON';
      option = 'enable';
    }else{
      trade = 'OFF';
      option = 'disable';
    }
    if($('#index_mode').is(":checked")){
      index = 'ON';
    }else{
      index = 'OFF';
    }
    // if(tradeCode == ''){
    //   hideloading();
    //   $('#tradeCodeError').html("Select Trade Schedular Code");
    //   return false;
    // }
    // if(indexCode == ''){
    //   hideloading();
    //   $('#indexCodeError').html("Select Index Schedular Code");
    //   return false;
    // }
    if(remarks == ''){
      hideloading();
      $('#remarksError').html("Required remarks");
      return false;
    }
    if (confirm("Are you sure you want to "+option+" the Trade and Index Schedular?")){
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        // data:'set_schedular_mode='+trade+'&trade_mode='+trade+'&remarks='+remarks+'&index_mode='+index+'&tradeCode='+tradeCode+'&indexCode='+indexCode,
        data:'set_schedular_mode='+trade+'&trade_mode='+trade+'&remarks='+remarks+'&index_mode='+index,
        cache: false,
        success: function(data){
          hideloading();
          $("#message").html(data);
          location.reload();
        }
      });
    }else{
      hideloading();
      return false;
    }
  });

  $('#remarks').click(function(){
    $('#remarksError').html("");
  });

  $('#row').click(function(){
    $('#rowError').html("");
  });
  
  // $('#tradeTaskCode').click(function(){
  //   $('#tradeCodeError').html("");
  // });
  // $('#indexTaskCode').click(function(){
  //   $('#indexCodeError').html("");
  // });
</script>
<style>
  .switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
  }

  /* Hide default HTML checkbox */
  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  /* The slider */
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
  }

  input:checked + .slider {
    background-color: #2196F3;
  }

  input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
  }

  input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
  }

  /* Rounded sliders */
  .slider.round {
    border-radius: 34px;
  }

  .slider.round:before {
    border-radius: 50%;
  }
</style>
</html>
