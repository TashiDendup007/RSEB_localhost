<?php
  include ('sessionStartFile_client.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php'); ?>
<?php include('../../CONNECTIONS/confirmationMessage.php'); ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
<div class="wrapper">
  <?php include('../NAV/navigation.php'); ?>
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">change Order</a></li>
      </ol>
    </section>
    <div id="message"></div>

    <section class="content">
      <?php include('../NAV/orderNav.php'); ?>
      <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12">
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
    // load pending order if any
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

    function escapeUserInput(input) {
        return input.replace(/['"\\]/g, '\\$&');
    }

    var chgOrEl = document.getElementById('chg_or' + i);
    var syEl = document.getElementById('sy' + i);
    var cdCodeEl = document.getElementById('cd_code' + i);
    var fidEl = document.getElementById('fid' + i);
    var volEl = document.getElementById('v' + i);
    var sideEl = document.getElementById('side' + i);
    var ePEl = document.getElementById('e_p' + i);
    var eVEl = document.getElementById('e_v' + i);
    var syIdEl = document.getElementById('sy_id' + i);

    var val = escapeUserInput(chgOrEl.value);
    var val1 = escapeUserInput(syEl.value);
    var cd_code = escapeUserInput(cdCodeEl.value);
    var fid = escapeUserInput(fidEl.value);
    var vol = escapeUserInput(volEl.value);
    var side = escapeUserInput(sideEl.value);
    var e_p = escapeUserInput(ePEl.value);
    var e_v = escapeUserInput(eVEl.value);
    var sy_id = escapeUserInput(syIdEl.value);

    if (e_v === '0' || isNaN(parseInt(e_v)) || e_v === '') {
        $("#message").html('<div class="alert alert-warning alert-dismissible"> Volume is not valid, cannot be ZERO or blank! </div>');
        showMessage();
        return false;
    }

    if (!Number.isInteger(parseInt(e_v))) {
        $("#message").html('<div class="alert alert-warning alert-dismissible"> Volume should be a whole number! </div>');
        showMessage();
        return false;
    }


    if (confirm("Do you want to continue?")) {
        showLoading();

        $.ajax({
            type: "POST",
            url: "../PROCESS/process.php",
            data: {
                change_id: val,
                v: vol,
                fid: fid,
                side: side,
                cd_code: cd_code,
                e_p: e_p,
                e_v: e_v,
                sy_id: sy_id
            },
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
                  success: function(orders){
                    hideloading();
                    $('#table_pending_orders').html(orders);
                  }
                });
              }
                $("#message").html(response.message);
                showMessage();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                hideloading();
                console.log("Error: " + textStatus + " " + errorThrown);
            }
        });
    } else {
        hideloading();
        return false;
    }
  }
</script>
</html>
