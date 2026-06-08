<?php 
    session_start();
    $role = $_SESSION['sess_userrole'];
    if( $role!="3")
    {
      header('Location: ../../access.php?err=2');
    }
$inactive = 1500;
// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout'])) 
{
  $session_life = time() - $_SESSION['timeout'];
  if($session_life > $inactive)
        { 
          header("Location: ../../Authentication/Logout.php"); 
        }
}
$_SESSION['timeout'] = time();
    include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-green sidebar-mini"><div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
<!-- Site wrapper -->
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box-body" id="message" style='display: none;'></div>
    <section class="content-header">
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="cds-css-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
         <li><a href="#">Payment</a></li>      
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- Default box -->
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Payment</h4>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="box-body">
          <?php 
              $msg = array(
              3=>"Update Successful!",
              4=>"Error While Updating ! ",
              5=>"Insufficient Fund ! ",
              );
                $msg_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
                if ($msg_id == 3) 
                {
                echo'<div class="alert alert-success">'.$msg[$msg_id].'</div>';
                //echo'<p class="alert alert-success">'.$msg[$msg_id].'</p>';
                }
                elseif ($msg_id == 4) 
                {
                  echo '<div class="alert alert-error">'.$msg[$msg_id].' </div>';
                  //  echo '<p class="alert alert-danger">'.$msg[$msg_id].'</p>';
                }
                elseif ($msg_id == 5) 
                {
                  echo '<div class="alert alert-error">'.$msg[$msg_id].' </div>';
                  //  echo '<p class="alert alert-danger">'.$msg[$msg_id].'</p>';
                }
              ?>
            <div class="box-tools">
              <div class="input-group" style="width: 300px;">
                <input type="number" class="form-control" name="cid_no" id="cid_no" placeholder="Enter CID...">
                <span class="input-group-btn">
                  <button type="button" class="btn btn-primary" title="Search" id="search-btn">
                    <i class="fa fa-search"></i> Search
                  </button>
                </span>
              </div>
            </div>
            <br><br>

            <div id="search-dtls"></div>
              
        </div>
        <!-- /.box-body -->
        <!-- /.box-footer-->
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <?php include('../NAV/footer.php') ?>
  
  <script>
    $('#search-btn').click(function() {
      showLoading();
      var cid = $("#cid_no").val();
       var data = {
        get_escrow_cid_details : 'get_escrow_cid_details',
        cid_no : cid,
      }

      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response){
          hideloading();
          $("#search-dtls").html(response);

          setTimeout(function() {
              $(".alert").fadeOut("slow");
          }, 3000);
        }
      });

    });

   $(document).on('click', '#confirm-payment', function () {
    showLoading(); // Show loading indicator

    var cid_no     = $("input[name='cid_no']").val();
    var amount     = $("input[name='amount']").val();
    var bank_id    = $("select[name='bank_id']").val();
    var branch_id  = $("select[name='branch_id']").val();
    var account_no = $("input[name='account_no']").val();
    var tpn        = $("input[name='tpn']").val();
    var company_name = $("input[name='company_name']").val();

    $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: {
            process_payment: 'process_payment',
            cid_no: cid_no,
            amount: amount,
            bank_id: bank_id,
            branch_id: branch_id,
            account_no: account_no,
            tpn: tpn,
            company_name: company_name
        },
        dataType: "html",
        success: function (response) {
            hideloading(); // Hide loading
            $('#paymentModal').modal('hide'); // Hide modal

            // Show message
            $("#search-dtls").html(response);

        },
        error: function (xhr, status, error) {
            hideloading();
            alert("An error occurred: " + error);
        }
    });
});

  </script>
</body>
</html>
