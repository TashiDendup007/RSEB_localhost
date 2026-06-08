<?php 
  include ('session_start_file.php');
  include ('../../CONNECTIONS/db.php');

  $check = $dbh->prepare("SELECT c.cd_code FROM users c WHERE c.username = ?");
  $check->execute([$username]);
  $cd_code = $check->fetchColumn();
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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li>RFQ Order</li>      
        </ol>
      </section>
      
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title"><strong>Buyer Requests</strong></h4>
            </div>
            <div class="box-body table-responsive">
              <table id="buyer_rfq_order_id" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Buyer CD Code</th>
                    <th>Symbol</th>
                    <!-- <th>Price</th> -->
                    <th>Volume</th>
                    <th>Time</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                  $stmt = $dbh->prepare("
                      SELECT a.id AS order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id, b.security_type 
                      FROM bond_orders a
                      INNER JOIN symbol b ON a.symbol_id = b.symbol_id
                      WHERE a.order_type = 'RFQ'
                      AND a.order_entry != ? 
                      AND a.side = 'B'
                      ORDER BY a.symbol_id, a.order_date ASC
                  ");
                  $stmt->execute([ $username ]);
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  $i = 1;
                  foreach ($rows as $res) {
                    $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
                    echo'
                    <tr style="background-color: #dce4ee">
                      <input type="hidden" value="' . $res['symbol_id'] . '"  id="sy_id' . $res['order_id'] . '">
                      <input type="hidden" value="' . $res['cd_code'] . '"  id="cd_code' . $res['order_id'] . '">
                      <td>' . $i . '</td>
                      <td>' . $res['cd_code'] . '</td>
                      <td>' . $res['symbol'] . '</td>
                      <td>' . $res['buy_vol'] . '</td>
                      <td>' . $res['order_date'] . '</td>
                      <td>
                        <button type="button" class="btn btn-danger" onclick="return place_offer(' . $res['order_id'] . ');" data-toggle="tooltip" data-placement="top" title="Click here to place offer"><i class="fa fa-money"></i> Place Offer</button>
                      </td>
                    </tr>';
                    $i++;
                  }
                ?>
                </tbody>
              </table>
            </div>
            <div class="box-footer text-center"></div>
        </div>

        <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title"><strong>Seller Quotation</strong></h4>
            </div>
            <div class="box-body table-responsive">
              <table id="seller_rfq_order_id" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Symbol</th>
                    <th>Seller <br>CD Code</th>
                    <th>Price</th>
                    <th>Volume</th>
                    <th>Accured %</th>
                    <th>Payable <br> Price</th>
                    <th>YTM</th>
                    <th>Time</th>
                    <th>Best Price</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <?php  
                  $stmt = $dbh->prepare("
                    SELECT a.id AS order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id, b.security_type, a.dirty_price, a.acc_intrt, a.ytm, a.quoted_to 
                    FROM bond_orders a
                    INNER JOIN symbol b ON a.symbol_id = b.symbol_id
                    WHERE a.order_type = 'RFQ'
                    AND a.order_entry != ? 
                    AND a.side = 'S'
                    AND a.quoted_to = ?
                    ORDER BY a.symbol_id, a.order_date ASC
                  ");
                  $stmt->execute([$username, $cd_code]);
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  $i = 1;

                  // get best price 
                  $stmt_price = $dbh->prepare("SELECT MIN(price) AS bst_sell_price FROM bond_orders o WHERE o.symbol_id = ? AND o.order_type = 'RFQ' AND o.quoted_to = ?");

                  foreach ($rows as $res) {
                    $side = $res['side'] == 'S' ? 'SELL' : 'BUY';

                    $stmt_price->execute([$res['symbol_id'], $cd_code]);
                    $best_sell_price = $stmt_price->fetchColumn();

                    echo'
                    <tr style="background-color: #fa9aa8">
                      <input type="hidden" value="' . $res['symbol_id'] . '"  id="sy_id' . $res['order_id'] . '">
                      <input type="hidden" value="' . $res['cd_code'] . '"    id="cd_code' . $res['order_id'] . '">
                      <input type="hidden" value="' . $res['price'] . '"      id="price' . $res['order_id'] . '">
                      <input type="hidden" value="' . $res['quoted_to'] . '"  id="quoted_to' . $res['order_id'] . '">
                      <input type="hidden" value="' . $res['flag_id'] . '"    id="flag_id' . $res['order_id'] . '">
                      <input type="hidden" value="' . $res['sell_vol'] . '"   id="sell_vol' . $res['order_id'] . '">
                      <td>' . $i . '</td>
                      <td>' . $res['symbol'] . '</td>
                      <td>' . $res['cd_code'] . '</td>
                      <td>' . $res['price'] . '</td>
                      <td>' . $res['sell_vol'] . '</td>
                      <td>' . $res['acc_intrt'] . '</td>
                      <td>' . $res['dirty_price'] . '</td>
                      <td>' . $res['ytm'] . '</td>
                      <td>' . $res['order_date'] . '</td>
                      <td><strong>' . $best_sell_price . '</strong></td>
                      <td>
                        <button type="button" class="btn btn-primary" onclick="return execute_offer(' . $res['order_id'] . ');" data-toggle="tooltip" data-placement="top" title="Click here to purchase at best price"><i class="fa fa-credit-card"></i> Purchase</button>
                      </td>
                    </tr>';
                    $i++;
                  }
                ?>
                </tbody>
              </table>
            </div>
            <div class="box-footer text-center"></div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript" src="../js/bond_script.js"></script>
<script>
    $( function () {
      $("#buyer_rfq_order_id").DataTable();
      $("#seller_rfq_order_id").DataTable();
    });

    function execute_offer(id) {
      showLoading();
      const getVal = (prefix) => document.getElementById(prefix + id)?.value;
      const payload = {
        execute_offer_rfq: 'execute_offer_rfq',
        symbol_id: getVal('sy_id'),
        seller_cdcode: getVal('cd_code'),
        buyer_cdcode: getVal('quoted_to'),
        flag_id: getVal('flag_id'),
        sell_vol: getVal('sell_vol'),
        sell_price: getVal('price'),
      };

      if (confirm("Are you sure you want to purchase this order?")) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/bond_trading_process.php",
          data: payload,
          dataType: 'json'
        })
        .done(response => {
            hideloading();
            $("#message").show().html(response.message).css('color', '#07df07');
            showMessage();

            setTimeout(() => {
                $("#message").html('Reloading, please wait...').css('color', '#f0a500');
                setTimeout(() => location.reload(), 2000);
            }, 3000);

        })
        .fail((xhr, status, error) => {
          hideloading();
          // console.error("Request failed:", status, error);
          let message = "Something went wrong";
          // if PHP returned JSON
          if (xhr.responseJSON && xhr.responseJSON.message) {
              message = xhr.responseJSON.message;
          }
          // fallback for plain text response
          else if (xhr.responseText) {
              message = xhr.responseText;
          }
          $("#message").html(message).css('color', 'red');
          showMessage();
        });
      }
      hideloading();
    }

    function place_offer(id) {
      const getVal = (prefix) => document.getElementById(prefix + id)?.value;

      const payload = {
        placing_offer_rfq: 'placing_offer_rfq',
        symbol_id: getVal('sy_id'),
        cd_code: getVal('cd_code'),
        side_offer: 'S',
      };

      $.ajax({
        type: "POST",
        url: "bond_load_function.php",
        data: payload,
        dataType: 'html'
      })
      .done(response => {
          $("#myModal").html(response);
          $("#myModal").modal('show');
      })
      .fail((xhr, status, error) => {
        console.error("Request failed:", status, error);
        $("#message").html("Something went wrong");
        showMessage();
      });
    }

    $(document).on('input', '#offer_price', function () {
      let value = $(this).val();
      
      // Reset message
      $("#offer_price_error").hide().text('');
      $(this).css('border-color', '');

      if (value === '') {
          $("#ytm_id").val('');
          $("#dirty_price").val('');
          $("#accur_int").val('');
          return;
      }

      // Rule 1: must be > 0
      if (parseFloat(value) <= 0) {
          $("#offer_price_error").text("Price must be greater than 0").show();
          $(this).css('border-color', 'red');
          return;
      }

      // Rule 2: max 2 decimal places
      if (!/^\d+(\.\d{0,2})?$/.test(value)) {
          $("#offer_price_error").text("Only up to 2 decimal places allowed").show();
          $(this).css('border-color', 'red');
          return;
      }

      // If valid
      $(this).css('border-color', 'green');

      let price = $("#offer_price").val();
      let symbol_id = $("#symbol_id").val();
      // calculate ytm if price and symbol is not null
      if (value != '' && price != '') {

        get_ytm_calculate(price, symbol_id).done(function(res) {
            if (res.status) {
                $("#ytm_id").val(res.data.ytm);
                $("#dirty_price").val(res.data.dirtyPrice);
                $("#accur_int").val(res.data.accrued);
            } else {
                alert(res.message);
            }
        }).fail(function() {
            alert("Error fetching YTM");
        });

      }
    });

    $(document).on('input', '#offer_vol', function () {
        let value = $(this).val();

        // Reset message
        $("#offer_vol_error").hide().text('');
        $(this).css('border-color', '');

        // Rule 1: must be > 0
        if (parseFloat(value) < 10) {
            $("#offer_vol_error").text("Volume must be greater than 10").show();
            $(this).css('border-color', 'red');
            return;
        }

        // Rule 2: Check multiple of 10
        if (parseFloat(value) % 10 !== 0) {
            $("#offer_vol_error").text("Volume must be a multiple of 10").show();
            $(this).css('border-color', 'red');
            return;
        }

        // If valid
        $(this).css('border-color', 'green');
    });


    $(document).on('click', '#submit_offer_btn', function () {
      const $btn = $(this);

      $("#submit_offer_error").hide().text('');
      $(this).css('border-color', '');

      const payload = {
        submit_offer_rfq : 'submit_offer_rfq',
        cd_code          : $('#cd_code').val(),
        order_type       : $('#order_type').val(),
        symbol_id        : $('#symbol_id').val(),
        side             : $('#side').val(),
        offer_vol        : Number($('#offer_vol').val()),
        offer_price      : $('#offer_price').val(),
        accur_int        : $('#accur_int').val(),
        dirty_price      : $('#dirty_price').val(),
        ytm_id           : $('#ytm_id').val(),
        buyer_code       : $('#buyer_code').val(),
      };

      if (payload.offer_vol <= 0 || payload.offer_vol % 10 !== 0) {
        $("#submit_offer_error").text("Order should be a multiple of 10").show();
        $(this).css('border-color', 'red');
        return;
      }

      if (!isValidNumber(payload.offer_price)) {
        $("#submit_offer_error").text("Price should be at most 2 decimal places.").show();
        $(this).css('border-color', 'red');
        return;
      }

      // reset again to clear any previous error state
      $("#submit_offer_error").hide().text('');
      $(this).css('border-color', '');

      if (confirm("Are you sure you want to submit this offer?")) {
        $.ajax({
          type: "POST",
          url: "../PROCESS/bond_trading_process.php",
          data: payload,
          dataType: 'JSON'
        })
        .done(response => {
            $("#submit_offer_error").text(response.message).show();
            $(this).css('border-color', 'green');
            // location.reload();
        })
        .fail((xhr, status, error) => {
          $("#submit_offer_error").text("Something went wrong").show();
          $(this).css('border-color', 'red');
        });
      }
    });

    function isValidNumber(value) {
      return /^\d+(\.\d{1,2})?$/.test(value);
    }
  </script>
</html>
