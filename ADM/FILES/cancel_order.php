<?php 
  include('sessionStartFile_admin.php'); 
  include ('../../CONNECTIONS/db.php');
  include('../../Functions/f.php');
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('../NAV/components.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
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
          <li><a href="#">Cancle Order</a></li>
        </ol>
      </section>
      <div id="message"></div>
      <section class="content">
        <div class="row">
          <div class="col-lg-12">
            <div class="box">
              <form class="form-horizontal" action="../PROCESS/process" method="POST">
                <div class="box-body">
                  <div class="col-lg-6 col-md-6">
                    <label>Symbol<font color="red">*</font></label>
                    <select class="form-control" name="symbol" id="symbol" required>
                      <option value="">--Select Symbol--</option>
                      <option value="ALL">All Symbols</option>
                      <?php 
                      $getSymbol = $dbh->prepare("SELECT s.symbol_id, s.symbol
                        FROM orders r 
                        LEFT JOIN symbol s ON r.symbol_id = s.symbol_id 
                        GROUP BY r.symbol_id 
                        ORDER BY s.symbol ASC");
                      $getSymbol->execute();
                      $symbolRows = $getSymbol->fetchAll(PDO::FETCH_ASSOC);
                        foreach($symbolRows as $res){
                          echo'
                          <option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
                        }
                      ?>
                    </select>
                    <span id="symbolError" style="color: red;"></span>
                  </div>
                  <div class="col-lg-6 col-md-6">
                    <label>Side<font color="red">*</font></label>
                    <select class="form-control" name="side" id="side" required>
                      <option value="BOTH">Both Sides</option>
                      <option value="B">Buy</option>
                      <option value="S">Sell</option>
                    </select>
                  </div>
                </div>
                <div class="box-footer">
                  <div class="col-lg-6 col-md-6">
                    <button type="button" class="btn btn-primary" id="getOrdersOutOfRange"><i class="fa fa-search"></i> Search</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="row" id="tableId" style="display: none;">
          <div class="col-lg-12">
            <div class="box">
              <div class="box-body">
                <div id="order_details"></div>
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
  $('#getOrdersOutOfRange').click( function() {
    showLoading();
    var symbolVal = $('#symbol').val();
    var sideVal = $('#side').val();
    var op = 'getOrdersOutOfRange';

    var data = {
      symbol: symbolVal,
      side: sideVal,
      getOrdersOutOfRange: op,
    };

    if (symbolVal == '') {
      hideloading();
      $('#symbolError').html('Select Symbol');
    } else {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        dataType: "html",
        success: function(response) {
          hideloading();
          $("#tableId").show();
          $("#order_details").html(response);
        }
      });
    }
  });

  $('#symbol').click(function(){
    $('#symbolError').html('');
  });

  // delete function
  /*function fun(i) {
    showLoading();
    var order_id = document.getElementById('del_or'+i).value;
    var symbol = document.getElementById('sy'+i).value;
    var cd_code = document.getElementById('cd_code'+i).value;
    var fid = document.getElementById('fid'+i).value;
    var vol = document.getElementById('v'+i).value;
    var side = document.getElementById('side'+i).value;
    var sy_id = document.getElementById('sy_id'+i).value;
    
    var data = {
      cancle_id: order_id, 
      v: vol, 
      fid: fid, 
      side: side, 
      cd_code: cd_code, 
      sy_id: sy_id
    };

    if (confirm("Are you sure you want to delete this order of "+ cd_code + ', of '+symbol+'?')) {
      $.ajax({
        type: "POST",
        url: "../PROCESS/process.php",
        data: data,
        cache: false,
        dataType: "html",
        success: function(response) {
          hideloading();
          var data = JSON.parse(response);

          $("#message").html(data.message);
          showMessage();

          if(data.status == 200){
            $("#del_row" + i).fadeOut('slow');
          }
        }
      });
    } else {
      hideloading();
      return false;
    }
  }*/
</script>
<style>
.blink {
  animation-duration: 5s;
  animation-name: blink;
  animation-iteration-count: infinite;
  animation-direction: alternate;
  animation-timing-function: ease-in-out;
}
@keyframes blink {
    from {
        opacity: 1;
    }
    to {
        opacity: 0.2;
    }
}
</style>
</html>
