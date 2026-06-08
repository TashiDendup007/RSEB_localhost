<?php 
  date_default_timezone_set("Asia/Thimphu");
  $sys_date = date('Y-m-d');

  session_start();
  $role = $_SESSION['sess_userrole'];
  if( $role!="6")
  {
    header('Location: ../../access.php?err=2');
  }

  $inactive = 1500;
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
  <?php include('../NAV/components.php'); ?>
  <style>
    .circle-container {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .circle {
      width: 240px;
      height: 240px;
      border-radius: 50%;
      background: linear-gradient(to bottom, #4c2d9f 50%, #007fc9 50%);
      position: relative;
    }

    .worth {
      font-size: 13px;
      color: #FFFFFF;
      position: relative;
      top: 56%;
      left: 49%;
      transform: translate(-50%, -50%);
    }

    .money {
      width: 40px;
      position: absolute;
      top: 91%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .volume_traded {
      font-size: 13px;
      color: #FFFFFF;
      position: relative;
      top: 26%;
      left: 48%;
      transform: translate(-50%, -50%);
    }

    .logo {
      width: 80px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .arrow {
      width: 37px;
      position: absolute;
      top: 9%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .text_custome {
      font-size: 14px;
      color: #082591;
      font-family: inherit;
      font-weight: bold;
    }
  </style>
</head>
<body class="hold-transition skin-yellow sidebar-mini">
  <div id='loadingover' style='display: none;'><div id='loadingmsg' style='display: none;'></div></div>
  <div class="wrapper">

    <?php include('../NAV/navigation.php'); ?>
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
            <h4 class="box-title">Highlights By Numbers</h4>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
          </div>
          <form action="loadReport.php" method="POST">
            <div class="box-body">
              <div class="col-lg-6 col-md-6 col-sm-3 col-xs-3">
                <label>From Date<font color="red">*</font></label>
                <input type="date" class="form-control" name="start_date" id="start_date"required>
              </div>

              <div class="col-lg-6 col-md-6 col-sm-3 col-xs-3">
                <label>To Date<font color="red">*</font></label>
                <input type="date" class="form-control" name="end_date" id="end_date"required>
              </div>
            </div>
            <div class="box-footer">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <button type="button" class="btn btn-success" id="highlights_number" name="highlights_number"><i class="fa fa-list"></i>  Generate </button>
              </div>
            </div> 
          </form>
        </div>

        <div class="row" style="display: none;" id="detail_id">
          <div class="col-lg-12">
            <div class="box" style="max-width: 500px; margin: 0 auto;">
              <div class="box-body text-center">
                <h2 style="font-family: emoji;"><span style="color: #35356c;">MONTHLY MARKET</span> <span style="color: #002ffb;" id="monthOf"></span> <br><span style="color: #35356c;">HIGHLIGHTS BY NUMBERS</span></h2>

                <div class="circle-container">
                  <div class="circle">
                    <img src="../../img/market/arrow.png" alt="arrow" class="arrow">
                    <p class="volume_traded"><span id="traded_volume"></span><br> VOLUME TRADED</p>
                    <img src="../../img/market/logo_round.png" alt="Logo" class="logo">
                    <p class="worth">AMOUNT<br> Nu. <span id="traded_worth"></span></p>
                    <img src="../../img/market/money.png" alt="arrow" class="money">
                  </div>
                </div>
                
                <div class="col-lg-4 col-md-4 col-sm-4">
                  <p class="text_custome"><span id="trade_execute"></span><br>TRADES EXECUTED</p>
                  <img src="../../img/market/execute_trade.png" style="width: 40px;">
                </div>

                <div class="col-lg-4 col-md-4 col-sm-4">
                  <p class="text_custome"><span id="user_count"></span><br>USERS</p>
                  <img src="../../img/market/user.png" style="width: 40px;">
                </div>

                <div class="col-lg-4 col-md-4 col-sm-4">
                  <p class="text_custome"><span id="symbol_trade"></span><br>SYMBOLS TRADED</p>
                  <img src="../../img/market/symbol.png" style="width: 40px;">
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 text-center" style="margin-top: 2px;">
                  <div class="col-lg-4 col-md-4 col-sm-4" style="background-color: #4c2d9f; height: 5px; margin-top: 31px;"></div>
                  <div class="col-lg-4 col-md-4 col-sm-4">
                      <img src="../../img/market/mcams_logo.png" style="width: 70px;">
                  </div>
                  <div class="col-lg-4 col-md-4 col-sm-4" style="background-color: #4c2d9f; height: 5px; margin-top: 31px;"></div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-4" style="margin-top: -25px;">
                  <p class="text_custome"><span id="buy_vol"></span> <br>SHARES TRADED <br>AMOUNT <br> Nu. <span id="buy_worth"></span></p>
                  <img src="../../img/market/buy_1.png" style="width: 100%; height: 46px;">
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                  <p class="text_custome"><span id="mcams_user_count"></span><br>USERS</p>
                  <img src="../../img/market/mobile.png" style="width: 40px;">
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4" style="margin-top: -25px;">
                  <p class="text_custome"><span id="sell_vol"></span> <br>SHARES TRADED <br>AMOUNT <br> Nu. <span id="sell_worth"></span></p>
                  <img src="../../img/market/sell_1.png" style="width: 100%; height: 46px;">
                </div>

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
  function showLoading() {
      document.getElementById('loadingmsg').style.display = 'block';
      document.getElementById('loadingover').style.display = 'block';
  }
  function hideloading() {
      document.getElementById('loadingmsg').style.display = 'none';
      document.getElementById('loadingover').style.display = 'none';
  }

  $('#highlights_number').click( function () {
      showLoading();
      var from_date = $("#start_date").val();
      var to_date = $("#end_date").val();
      var op = 'get_highlights_details';

      if (from_date && to_date) {
        $.ajax({
          type: "POST",
          url: "loadReport.php",
          data: 'from_date='+from_date +'&to_date='+to_date +'&get_highlights_details='+op,
          dataType: "JSON",
          success: function(response) {
            var tradeData = response.data;

            $('#detail_id').show();
            $('#monthOf').text(tradeData.monthname);
            $('#traded_volume').text(tradeData.volume_traded);
            $('#traded_worth').text(tradeData.traded_worth);
            $('#trade_execute').text(tradeData.trade_execute);
            $('#user_count').text(tradeData.user_count);
            $('#symbol_trade').text(tradeData.symbol_count);
            $('#buy_vol').text(tradeData.buy_vol);
            $('#buy_worth').text(tradeData.buy_vol_worth);
            $('#mcams_user_count').text(tradeData.mCams_user);
            $('#sell_vol').text(tradeData.sell_vol);
            $('#sell_worth').text(tradeData.sell_vol_worth);
            hideloading();
          }
        });

      } else {
        hideloading();
        alert("Both From and To Date are required");
        return false;
      }
  });
</script>
</html>