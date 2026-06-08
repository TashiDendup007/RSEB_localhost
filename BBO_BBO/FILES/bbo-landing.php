<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');

  $list = ins_id($username);
  $ins_id = $list[0];
  $p_code = $list[1];
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
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="#">Dashboard</a></li>
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <?php include('../NAV/orderNav.php'); ?>
        <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">Watch List</h3>
              </div>
              <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Symbol</th>
                      <th>Paid up shares</th>
                      <th>Market Price</th>
                      <th>View</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $wc = $dbh->prepare("
                            SELECT 
                              s.symbol_id, s.paid_up_shares, s.symbol, s.logo, s.name, s.sector, s.face_value, mp.market_price, mp.ex_market_price, 
                              (mp.market_price - mp.ex_market_price) AS cp
                            FROM symbol s
                            JOIN market_price mp ON mp.symbol_id = s.symbol_id
                            WHERE s.security_type = 'OS' AND s.status = 1 AND s.trsstatus = 1
                    ");
                    $wc->execute();
                    $results = $wc->fetchAll();
                    $price_movement = 0;
                    foreach ($results as $res) {
                      switch (true) {
                        case $res['cp'] == 0:
                          $class = 'black';
                          $price_movement = number_format(0,2);
                          break;
                        case $res['cp'] > 0:
                          $class = 'green';
                          $price_movement = '+'.$res['cp'];
                          break;
                        case $res['cp'] < 0:
                          $class = 'red';
                          $price_movement = $res['cp'];
                          break;
                      }
                      echo '
                      <tr>
                        <td><img src="' . $res['logo'] . '" height=30></td>
                        <td><a href="">' . $res['symbol'] . '</a></td>
                        <td>' . number_format($res['paid_up_shares']) . '</td>
                        <td>' . $res['market_price'] . ' (<b style="color:' . $class . '">' . $price_movement . '</b>)</td>
                        <td>
                          <a href="" data-toggle="modal" data-target="#myModal" onclick="GetSymbolModal(' . $res['symbol_id'] . ');">
                            <input type="hidden" value="' . $res['symbol'] . '" id="symbolName' . $res['symbol_id'] . '">
                            <span class="badge bg-blue">view</span>
                          </a>
                        </td>
                      </tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">Share Holding Details of Client</h3>
              </div>
              <div class="box-body">
                <form action="" method="POST">
                  <div class="input-group">
                    <input type="text" placeholder="Enter client's CD Code" class="form-control" name="cdcode" id="cdcode" required>
                    <span class="input-group-btn">
                      <button type="submit" name="search" id="searchShareDtls" class="btn btn-flat"><i class="fa fa-search"></i></button>
                    </span>
                  </div>
                  <span id="err_id" style="color: red;"></span>
                  <div id="showdetails"></div>
                </form>
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
  $("#searchShareDtls").click( function(event) { 
    event.preventDefault();
    var $cdcode = $("#cdcode");
    var cd_load_cli = $cdcode.val();

    if (cd_load_cli === '') {
      $("#err_id").html("Enter Cd Code");
      return false;
    } else {
      $.ajax({
        type: "POST",
        url: "load.php",
        data: { cd_load_cli: cd_load_cli },
        dataType: "html",
        success: function (response) {
          $("#showdetails").html(response);
        },
        error: function(xhr, status, error) {
          console.log("Error: " + error);
        }
      });
    }
  });

  $('#cdcode').click(function() {
    $("#err_id").html("");
  });

  function GetSymbolModal(val) {
    var SymbolLoad = "SymbolLoad";
    var symbolName = $('#symbolName'+val).val();

    $.ajax({
      type: "POST",
      url: "load.php",
      data:'SymbolLoad='+SymbolLoad+'&val='+symbolName,
      dataType: "html",
      success: function(response){
        $("#myModal").html(response);
      }
    });

    Highcharts.getJSON('../../TE/FILES/stock_prices.php?symbol='+symbolName, function(data) {
      // Create the chart
      Highcharts.stockChart('containerChart', {
        rangeSelector: {
          selected: 1
        },
        title: { text: 'Price Movement' },
        credits: { enabled: false },

        series: [{
          name: symbolName,
          data: data,
          tooltip: { valueDecimals: 2 }
        }]
      });
    });

  }
</script>
</html>
