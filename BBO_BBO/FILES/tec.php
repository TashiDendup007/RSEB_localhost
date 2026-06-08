<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
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
      <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Change Order</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-body">
                <div id="table_pending_orders" class="table-responsive"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?>  
</body>
<script>
  $( function () {
    $("#example1").DataTable();

    showLoading();
    $.ajax({
      type: "POST",
      url: "load.php",
      data:'get_pending_order_list=get_pending_order_list&usernmae=<?php echo addslashes($username); ?>',
      cache: false,
      success: function(data) {
        hideloading();
        $('#table_pending_orders').html(data);
      }
    });

  });

  function fun(i) {
    showLoading();
    var val = document.getElementById('chg_or'+i).value;
    var symbol = document.getElementById('sy'+i).value;
    var cd_code = document.getElementById('cd_code'+i).value;
    var fid = document.getElementById('fid'+i).value;
    var vol = document.getElementById('v'+i).value;
    var side = document.getElementById('side'+i).value;
    var e_p = document.getElementById('e_p'+i).value;
    var e_v = document.getElementById('e_v'+i).value;
    var sy_id = document.getElementById('sy_id'+i).value;

    var data = {
      change_id: val,
      v: vol,
      fid: fid,
      side: side,
      cd_code: cd_code,
      e_p: e_p,
      e_v: e_v,
      sy_id: sy_id
    };
    
    if (confirm("Are you sure you want to Change this order of "+ cd_code + ', of '+symbol+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        // dataType: 'html',
        dataType: 'JSON',
        success: function(response) {
          hideloading();
          if (response.status === 'success') {
            // reload the table
            showLoading();
            $.ajax({
              type: "POST",
              url: "load.php",
              data:'get_pending_order_list=get_pending_order_list&usernmae=<?php echo addslashes($username); ?>',
              cache: false,
              success: function(data){
                hideloading();
                $('#table_pending_orders').html(data);
              }
            });
          }

          $("#message").html(response.message);
          showMessage();
        }
      });
    } else {
      hideloading();
      return false;
    }
  }
</script>
</html>
