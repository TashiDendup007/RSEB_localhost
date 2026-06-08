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
      <div id="message"></div>
      <section class="content-header">
        <h1>
          <small></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
           <li><a href="#">Pending Order</a></li>      
        </ol>
      </section>
      <section class="content">
        <div class="modal fade" id="myModal" role="dialog"></div>
        <div class="box">
            <div class="box-header with-border">
              <h4 class="box-title">Bond Pending Orders</h4>
            </div>
            <div class="box-body table-responsive">
              <table id="pen_odr_tbl_id" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Symbol</th>
                    <th>CD Code</th>
                    <th>Price</th>
                    <th>Volume</th>
                    <th>Side</th>
                    <th>Dirty Price</th>
                    <th>Accured %</th>
                    <th>YTM</th>
                    <th>Time</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <?php  
                  $stmt = $dbh->prepare(" SELECT a.id AS order_id, a.cd_code, a.participant_code, a.member_broker, a.order_size, a.order_entry, a.flag_id, a.sell_vol, a.buy_vol, a.price, a.side, a.commis_amt, a.order_date, b.symbol, b.symbol_id, b.security_type, a.dirty_price, a.acc_intrt, a.ytm 
                        FROM bond_orders a
                        INNER JOIN symbol b ON a.symbol_id = b.symbol_id
                        WHERE a.order_entry = ? 
                        ORDER BY a.order_date DESC
                  ");
                  $stmt->execute([ $username ]);
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  $i = 1;
                  foreach ($rows as $res) {
                    $background_color = $res['side'] == 'S' ? '#eb8292' : '#bac2cb';
                    $side = $res['side'] == 'S' ? 'SELL' : 'BUY';
                    $input_type = ($res['side'] === 'S') ? 'number' : 'hidden';
                    $dirty_price = ($res['side'] === 'S') ? $res['dirty_price'] : '';
                    $acc_intrt = ($res['side'] === 'S') ? $res['acc_intrt'] : '';
                    $ytm = ($res['side'] === 'S') ? $res['ytm'] : '';
                    echo'
                    <tr style="background-color:' . $background_color . '">
                      <input type="hidden" value="' . $res['symbol_id'] . '"  id="sy_id' . $i . '">
                      <input type="hidden" value="' . $res['cd_code'] . '"    id="cd_code' . $i . '">
                      <input type="hidden" value="' . $res['flag_id'] . '"    id="fid' . $i . '">
                      <input type="hidden" value="' . $res['side'] . '"       id="side' . $i . '">
                      <input type="hidden" value="' . $res['order_size'] . '" id="ex_vol' . $i . '">
                      <input type="hidden" value="' . $res['price'] . '"      id="ex_price' . $i . '">

                      <td>' . $i . '</td>
                      <td>' . $res['symbol'] . '</td>
                      <td>' . $res['cd_code'] . '</td>
                      <td>
                        <input type="'. $input_type .'" class="form-control" size="5" value="' . $res['price'] . '" id="new_price' . $i . '">
                      </td>
                      <td><input type="number" class="form-control" size="8" value="' . $res[$res['side'] == 'S' ? 'sell_vol' : 'buy_vol'] . '" id="new_vol' . $i . '"></td>
                      <td>' . $side . '</td>
                      <td>' . $dirty_price . '</td>
                      <td>' . $acc_intrt . '</td>
                      <td>' . $ytm . '</td>
                      <td>' . $res['order_date'] . '</td>
                      <td>
                        <button type="button" class="btn btn-primary" name="order_id" id="order_id' . $i . '" value="' . $res['order_id'] . '"  onclick="return change_order(' . $i . ');" data-toggle="tooltip" data-placement="top" title="Click Here To Change Order for ' . $res['cd_code'] . ', Symbol: ' . $res['symbol'] . '"><i class="fa fa-wrench"></i> Change</button>
                      </td>
                    </tr>';
                    $i++;
                  }
                ?>
                </tbody>
              </table>
              
            </div>
            <div class="box-footer text-center">
            </div>
        </div>

      </section>
    </div>
  </div>
  <?php include('../NAV/footer.php'); ?> 
</body>
<script type="text/javascript" src="../js/bond_script.js"></script>
<script>
    $( function () {
      $("#pen_odr_tbl_id").DataTable();
    });

    function change_order(id) {
        const getVal = (prefix) => document.getElementById(prefix + id)?.value;

        const payload = {
          change_bond_order: 'change_bond_order',
          order_id: getVal('order_id'),
          symbol_id: getVal('sy_id'),
          cd_code: getVal('cd_code'),
          flag_id: getVal('fid'),
          ex_vol: getVal('ex_vol'),
          ex_price: getVal('ex_price'),
          side: getVal('side'),
          new_price: getVal('new_price'),
          new_vol: getVal('new_vol'),
        };

        // Optional: basic validation
        if (!payload.order_id) {
          $("#message").html("<div class='alert alert-warning alert-dismissible'>Order ID missing</div>");
          showMessage();
          return;
        }

        if (payload.ex_vol == payload.new_vol && payload.ex_price == payload.new_price) {
          $("#message").html("<div class='alert alert-warning alert-dismissible'>No changes</div>");
          showMessage();
          return;
        }

        if (payload.new_vol <= 0 || payload.new_vol % 10 !== 0) {
          $("#message").html("<div class='alert alert-warning alert-dismissible'>Vol should be a multiple of 10</div>");
          showMessage();
          return;
        }

        if (payload.side == 'S') {
          if (!isValidNumber(payload.new_price)) {
            $("#message").html("<div class='alert alert-warning alert-dismissible'>Price should be at most 2 decimal places.</div>");
            showMessage();
            return;
          }
        }

        if (confirm("Do you want to continue?")) {
          $.ajax({
            type: "POST",
            url: "../PROCESS/bond_trading_process.php",
            data: payload,
            dataType: 'JSON'
          })
          .done(response => {
            $("#message").html(response?.message || "No response message");
            showMessage();
          })
          .fail((xhr, status, error) => {
            console.error("Request failed:", status, error);
            $("#message").html("Something went wrong");
            showMessage();
          });

          // reload for refresh
          setTimeout(() => {
              $("#message").html('Reloading, please wait...').css('color', '#f0a500');
              showMessage();
              setTimeout(() => location.reload(), 2000);
          }, 5000);

        } else {
          return false;
        }
    }

    function isValidNumber(value) {
      return /^\d+(\.\d{1,2})?$/.test(value);
    }
  </script>
</html>
