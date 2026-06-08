<?php 
  include('sessionStartFile_admin.php');
  include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
  <script type="text/javascript" src="../../plugins/jQueryUI/jquery-ui.min.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
  <div class="wrapper">
    <?php include('../NAV/navigation.php'); include ('../../CONNECTIONS/confirmationMessage.php'); ?>
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
        <?php 
          $errors = array(1=>"Operation Successfully Completed.",2=>"Oops Sorry! There was an error while operation.",3=>"Record Updated Successfully.",4=>"Record Already Exists.",5=>"Record Deleted Successfully.");
          $error_id = isset($_GET['ms']) ? (int)$_GET['ms'] : 0;
          if ($error_id == 1) { 
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          } elseif ($error_id == 2) {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-ban"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          } elseif ($error_id == 3) {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          elseif ($error_id == 4) {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
          elseif ($error_id == 5) 
          {
          echo'<div class="row"><div class="col-lg-12 col-xs-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-warning"></i> Message!&nbsp;&nbsp'.$errors[$error_id].'</div></div></div>';
          }
        ?>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="row">
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3 id="symboldCount"></h3>
                <p>Symbols</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="#" class="small-box-footer" id="getSymbolList">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
              <div class="inner">
                <h3>53<sup style="font-size: 20px">%</sup></h3>
                <p>Bounce Rate</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3 id="userRegisterCount"></h3>
                <p>User Registrations</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-xs-6">
            
            <div class="small-box bg-red">
              <div class="inner">
                <h3 id="terminalUserCount"></h3>
                <p>Terminal User</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="#" class="small-box-footer" id="getTerminalUserList">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <div class="row">
          <section class="col-lg-6 connectedSortable">
            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs pull-right">
                <li class="pull-left header"><i class="fa fa-inbox"></i> Volume Traded</li>
              </ul>
              <div class="tab-content no-padding">
                <div class="chart tab-pane active" id="sales-chart" style="position: relative; height: 300px;"></div>
              </div>
            </div>
          </section>

          <section class="col-lg-6 connectedSortable">
            <div class="box box-solid bg-teal-gradient">
              <div class="box-header">
                <i class="fa fa-th"></i>
                <h3 class="box-title">Online Active User</h3>
              </div>
              <div class="box-body border-radius-none">
                <div class="chart" id="line-chart" style="height: 250px;"></div>
              </div>
              <div class="box-footer no-border">
                <div class="row">
                  <!-- <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                    <input type="text" class="knob" data-readonly="true" value="20" data-width="60" data-height="60" data-fgColor="#39CCCC">
                    <div class="knob-label">Mail-Orders</div>
                  </div>
                  <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                    <input type="text" class="knob" data-readonly="true" value="50" data-width="60" data-height="60" data-fgColor="#39CCCC">
                    <div class="knob-label">Online</div>
                  </div>
                  <div class="col-xs-4 text-center">
                    <input type="text" class="knob" data-readonly="true" value="30" data-width="60" data-height="60" data-fgColor="#39CCCC">
                    <div class="knob-label">In-Store</div>
                  </div> -->
                </div>
              </div>
            </div>
          </section>

          <!-- <section class="col-lg-12 connectedSortable">
            <div class="box box-solid bg-teal-gradient">
              <div class="box-header">
                <i class="fa fa-th"></i>
                <h3 class="box-title">Daily Trade</h3>
              </div>
              <div class="box-body border-radius-none">
                <div class="chart" id="daily_chart" style="height: 250px;"></div>
              </div>
              <div class="box-footer no-border">
              </div>
            </div>
          </section> -->

          <!-- <div class="col-lg-12">
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">Daily Trade</h3>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="box-body">
                <div class="chart">
                  <canvas id="daily_trade_chart" style="height:250px"></canvas>
                </div>
              </div>
            </div>
          </div> -->

        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
  <script type="text/javascript" src="../../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
  <script type="text/javascript" src="../../plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="../../plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript" src="../../plugins/knob/jquery.knob.js"></script>
  <script type="text/javascript" src="../../plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
  <script type="text/javascript" src="../../plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
  <script type="text/javascript" src="../../plugins/sparkline/jquery.sparkline.min.js"></script>
  <script type="text/javascript" src="../../plugins/morris/morris.min.js"></script>
  <script type="text/javascript" src="../../plugins/raphael/raphael-min.js"></script>
  <script type="text/javascript" src="../../plugins/chartjs/Chart.min.js"></script>
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $.ajax({
      type: "POST",
      url: "../PROCESS/get_dtls_onLoad.php",
      data:'get_count_dtls=get_count_dtls',
      // dataType: 'json',
      async: false,
      success: function(response) {
        $("#symboldCount").html(response.symbol_count);
        $("#userRegisterCount").html(response.user_register_count);
        $("#terminalUserCount").html(response.terminal_user_count);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log("error = "+errorThrown);
      }
    });
  });

  function sett_process() {
    var TRADE = document.getElementById('TRADE').value;
    if (confirm("Are you sure you want to Execute Trade ?")) {
      showLoading();
      $.ajax({
        type: "POST",
        url: "pd_mt.php",
        data:'TRADE='+TRADE,
        success: function(data){
          hideloading();
          $("#message").show().html(data).fadeOut(5000);
        }
      });
    } else {
      return false;
    }
  }

  $('#getSymbolList').click( function () {
    var val = 'get_symbol_list';
    showLoading();
    $.ajax({
      type: "POST",
      url: "../PROCESS/get_dtls_onLoad.php",
      data:{ get_symbol_list: val },
      dataType: 'html',
      success: function(response){
        hideloading();
        $("#myModal").modal('show').html(response);
      }
    });
  });

  $('#getTerminalUserList').click( function () {
    var val = 'get_terminaluser_list';
    showLoading();
    $.ajax({
      type: "POST",
      url: "../PROCESS/get_dtls_onLoad.php",
      data:{ get_terminaluser_list: val },
      dataType: 'html',
      success: function(response){
        hideloading();
        $("#myModal").modal('show').html(response);
      }
    });
  });
</script>
<script type="text/javascript" src="../NAV/chart.js"></script>
</html>
