<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
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
          <li><a href="#">Circuit Breaker</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
          <div class="box-header with-border">
            <h4 class="box-title">Circuit Breaker</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="../PROCESS/process.php" method="POST" onsubmit="showLoading();">
            <div class="box-body">
              <div class="row" ng-app="">
                <div class="col-lg-4 col-md-4">
                  <label for="name">Circuit Name <span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="name" id="name" required>
                </div>
                <div class="col-lg-4 col-md-4">
                  <label for="margin">Margin <span style="color:red;">*</span></label>
                  <input type="text" class="form-control" name="margin" id="margin" required>
                </div>
                <div class="col-lg-4 col-md-4">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="1">Active</option>
                    <option value="0">InActive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6">
                <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>
        
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-header with-border" style="font:8px;">
                <h4 class="box-title">Circuit Breaker List</h4>
              </div>
              <div class="box-body">
                <div id="tableList"></div>
              </div>
              <div class="box-footer"></div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript">
  $(document).ready( function () {
    showLoading();
    var op = 'get_circuitBreaker_list';
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_circuitBreaker_list='+op,
      cache: false,
      success: function(data) {
        hideloading();
        $('#tableList').html(data);
      }
    });
  });

  $("#save").click( function(event) { 
    event.preventDefault();
    showLoading();

    var nameField = $("#name").val();
    var marginField = $("#margin").val();
    var statusField = $("#status").val();
    var operation = "save_circuit_breaker";
    
    var data = {
      name: nameField,
      margin: marginField,
      status: statusField,
      save_circuit_breaker: operation
    };

    if(nameField === '' || marginField === '') {
      alert("Please Fill All Mandatory Fields");
      hideloading();
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();

          $.ajax({
            type: "POST",
            url: "load.php",
            data: "get_circuitBreaker_list=get_circuitBreaker_list",
            cache: false,
            success: function(data) {
              hideloading();
              $("#tableList").html(data);
            }
          });
          
        },
        error: function(xhr, status, error) {
          hideloading();
          console.log(error);
        }
      });
    }
  });

  function updateCircuitBreaker(id) {
    if (confirm("Do you want to continue update?")) {
      showLoading();
      var nameFld = $("#name"+id).val();
      var marginFld = $("#margin"+id).val();
      var statusFld = $("#status"+id).val();
      var op = 'update_cir_breaker';

      var data = {
        id: id,
        name: nameFld,
        margin: marginFld,
        status: statusFld,
        update_cir_breaker: op
      };

      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        success: function(response){
          hideloading();
          $("#message").html(response);
          showMessage();

          $.ajax({
            type: "POST",
            url: "load.php",
            data: "get_circuitBreaker_list=get_circuitBreaker_list",
            cache: false,
            success: function(data) {
              hideloading();
              $("#tableList").html(data);
            }
          });
        }
      });
    } else {
      return false;
      hideloading();
    }
  }
</script>
</html>