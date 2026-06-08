<?php
include ('session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');

if(!empty($_POST["cid"])) {
  $ac = $_POST['cid'];
  $tp = $_POST['tp'];
  $un = substr($username,0,7);

  $wc = $dbh->prepare("SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate 
      FROM client_account b, bbo_commission c 
      WHERE b.bro_comm_id = c.bro_comm_id AND cd_code = :ac AND substr(user_name, 1, 7) = :un
  ");
  $wc->bindParam(':ac', $ac);
  $wc->bindParam(':un', $un);
  $wc->execute();
  $state = $wc->fetch();
  
  if($wc->rowCount() > 0) {
    echo'
    <div class="col-lg-8 col-md-8 col-sm-12">
      <label>Client Details</label>
      <input type="text" class="form-control" value="'.$state['f_name'].' '.$state['l_name'].', '.$state['ID'].', '.$state['cd_code'].'" readonly>

      <input type="hidden" id="b_commis" value="'.$state['rate'].'">
      <input type="hidden" class="form-control" name="cd_code" id="cd_code"  value="'.$state['cd_code'].'" readonly>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-12">
      <label>Security Type<font color="red">*</font>:</label>
      <select name="sec_type" id="sec_type" class="form-control" onChange="get_symbols_list(this.value, \'' . $tp . '\');">
        <option value="">-Security Type-</option>';
        $stmt = $dbh->prepare("SELECT s.id, s.security_type, s.precise_name FROM security_type_masters s WHERE s.`status` = 1");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $key => $value) {
          echo'<option value="' . $value['precise_name'] . '">' . $value['security_type'] . '</option>';
        }
      echo'
      </select>  
    </div>

    <div id="sym_list_div" style="display: none;"></div>';

    echo'
    <script>
      function get_symbols_list(se_type, or_type) {
        if (se_type == "") {
            $("#sym_list_div").hide().html("");
            $("#bond_details_id").hide();
            $("#v_div").hide();
            $("#p_div").hide();
            $("#ytm_div_id").hide();
            $("#avl_amt_div_id").hide();
            $(".submit").hide();
        } else {
            const operation = "get_symbols_list";
            $.ajax({
              type: "POST",
              url: "ja.php",
              data: { get_symbols_list: operation, sec_type: se_type, ord_type: or_type },
              dataType: "html",
              success: function(response) {
                $(".submit").show();
                $("#sym_list_div").show().html(response);
              }
            });
        }
      }
    </script>
    ';

    /*echo'
      <div class="col-lg-4 col-md-4 col-sm-12" id="sy_div">
        <label>Symbol<font color="red">*</font></label>';
        $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE status = 1 AND trsstatus = 1 AND security_type IN ('OS', 'GB', 'CB')");
        $wc->execute();
        if ($tp == 'S') {
          echo'
          <select name="sy" id="sy"  class="form-control" onChange="tots2(this.value);">
            <option value="" selected>-Select symbol-</option>';
            while($res = $wc->fetch()) {
              echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
            }
          echo'</select>';
        } elseif ($tp == 'B') {
          echo'
          <select name="sy" id="sy"  class="form-control" onChange="tots3(this.value);">
            <option value="" selected>-Select symbol-</option>';
            while($res = $wc->fetch()) {
              echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
            }
            echo'</select>';
        }
      echo'</div>';
    */
  } else {
      $ac = $_POST['cid'];
      $cnt = 0;
      echo $cnt."|".$ac;
  }
  die();
}
elseif (isset($_POST['get_symbols_list'])) {
    $security_type = $_POST['sec_type'];
    $order_type = $_POST['ord_type'];
    $fun_name = ($order_type == 'B') ? 'tots3' : 'tots2';
    
    echo'
    <div class="col-lg-4 col-md-4 col-sm-12" id="sy_div">
      <label>Symbol<font color="red">*</font></label>
      <select name="sy" id="sy"  class="form-control" onchange="' . htmlspecialchars($fun_name, ENT_QUOTES) . '(this.value);">
        <option value="" selected>-Select symbol-</option>';
        $stmt = $dbh->prepare("SELECT symbol, symbol_id, name FROM symbol WHERE status = 1 AND trsstatus = 1 AND security_type = ?");
        $stmt->execute([$security_type]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo'<option value="' . (int)$row['symbol_id'] . '">' . htmlspecialchars($row['symbol'], ENT_QUOTES, 'UTF-8') . ' ('. htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') .')</option>';
        }
      echo'
      </select>
    </div>';
    exit();
}
elseif (!empty($_POST["sy"]) && !empty($_POST["cd_code"])) {
    $sy_id = $_POST['sy'];
    $cd_code = $_POST['cd_code'];
    $cap_name = 'CAP';
    $security_type = $_POST['sert_type'];

    $market_price = number_format(market_price($sy_id),2, '.', ''); 
    $cap = number_format(circuit($cap_name),2, '.', '');
    $cap_value = number_format(cap_compute($market_price,$cap),2, '.', '');

    $bondcheck = $dbh->prepare("SELECT security_type FROM symbol WHERE symbol_id = :sid");
    $bondcheck->bindParam(':sid', $sy_id);
    $bondcheck->execute();
    $bondornot = $bondcheck->fetch();
    $secType =  $bondornot['security_type'];

    $wc = $dbh->prepare("SELECT volume, pending_out_vol, pending_in_vol FROM cds_holding WHERE cd_code = :cd AND symbol_id = :id");
    $wc->bindParam(':id', $sy_id);
    $wc->bindParam(':cd', $cd_code);
    $wc->execute();
    $state = $wc->fetch();
    if($wc->rowCount() > 0) {

      if ($security_type == 'CB' || $security_type == 'GB') {
          $stmt = $dbh->prepare("SELECT s.maturity_date, s.face_value, s.coupon_rates
              FROM symbol s
              WHERE s.symbol_id = ?
          ");
          $stmt->execute([$sy_id]);
          $res = $stmt->fetch(PDO::FETCH_ASSOC);
          echo'
          <div id="bond_details_id">
            <div class="col-lg-4 col-md-4 col-sm-12" id="">
              <label for="face_value">Face Value</label>
              <input type="number" class="form-control" name="face_value" id="face_value" value="' . $res['face_value'] . '" readonly>
            </div>
            
            <div class="col-lg-4 col-md-4 col-sm-12" id="">
              <label for="coupon_rate">Rate</label>
              <input type="number" class="form-control" name="coupon_rate" id="coupon_rate" value="' . $res['coupon_rates'] . '" readonly>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-12" id="">
              <label for="maturity_date">Maruity Date</label>
              <input type="date" class="form-control" name="maturity_date" id="maturity_date" value="' . $res['maturity_date'] . '" readonly>
            </div>
          </div>
          ';
      }

      echo'
      <div class="col-lg-4 col-md-4 col-sm-12" id="avl_vol_div_id">
        <label>Available Vol</label> 
        <input type="hidden" id="cap" value="'.$cap_value.'" >
        <input type="hidden" id="mp" value="'.$market_price.'" >
        <input type="hidden" id="avl_vol" value="'.$state['volume'].'" >
        <input type="hidden" id="pov" value="'.$state['pending_out_vol'].'" >
        <input type="hidden" id="piv" value="'.$state['pending_in_vol'].'" >
        <input type="hidden" id="security_type" value="'.$secType.'" >
        <input type="text" class="form-control" value="'.number_format($state['volume'], 0, ".", ",").'" readonly>
      </div>

      <div class="col-lg-4 col-md-4 col-sm-12" id="v_div">
        <label for="vol">Volume</label>
        <input type="number" class="form-control" name="vol" id="vol" required>
        <span id="sellVolMsg" style="color:red;" class="help-block"></span>
      </div>

      <div class="col-lg-4 col-md-4 col-sm-12" id="p_div" >
        <label for="price" id="pricel">Price</label>
        <input type="number" class="form-control" name="price" id="price" required>
      </div>';
    } else {
      echo'
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Available Vol</label> 
        <input type="text" class="form-control" value="No Shares." readonly>
      </div>';
    }
}
elseif(!empty($_POST["sy"]) && !empty($_POST["ac"])) {
    $sy_id = $_POST['sy'];
    $ac = $_POST['ac'];

    $b_commis = $_POST['b_commis'];
    $cap_name = 'CAP';
    $security_type = $_POST['sert_type'];

    $market_price = number_format(market_price($sy_id),2, '.', ''); 
    $cap = number_format(circuit($cap_name),2, '.', '');
    $cap_value = number_format(cap_compute($market_price,$cap),2, '.', '');

    $bondcheck = $dbh->prepare("SELECT security_type FROM symbol WHERE symbol_id = ?");
    $bondcheck->execute([$sy_id]);
    $secType = $bondcheck->fetchColumn();

    $wc = $dbh->prepare("SELECT a.cd_code, sum(a.amount) AS tot, b.cd_code, b.ID 
            FROM bbo_finance a, client_account b 
            WHERE a.cd_code = :ac AND b.cd_code = :ac AND a.status = 1
    ");
    $wc->bindParam(':ac', $ac);
    $wc->bindParam(':un', $username);
    $wc->execute();
    $state = $wc->fetch(PDO::FETCH_ASSOC);

    $bbo_amt = isset($state['tot']) ? number_format($state['tot'],2,".",",") : 0;

    if($wc->rowCount() > 0) {

      if ($security_type == 'CB' || $security_type == 'GB') {
          $stmt = $dbh->prepare("
                SELECT 
                  s.maturity_date, s.face_value, s.coupon_rates, s.date_of_issue, s.coupon_payable AS frequency 
                FROM symbol s
                WHERE s.symbol_id = ?
          ");
          $stmt->execute([$sy_id]);
          $res = $stmt->fetch(PDO::FETCH_ASSOC);
          echo'
          <div id="bond_details_id">
            <div class="col-lg-4 col-md-4 col-sm-12" id="">
              <label for="face_value">Face Value:</label>
              <input type="number" class="form-control" name="face_value" id="face_value" value="' . $res['face_value'] . '" readonly>
            </div>
            
            <div class="col-lg-4 col-md-4 col-sm-12" id="">
              <label for="coupon_rate">Rate:</label>
              <input type="number" class="form-control" name="coupon_rate" id="coupon_rate" value="' . $res['coupon_rates'] . '" readonly>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-12" id="">
              <label for="maturity_date">Maruity Date:</label>
              <input type="date" class="form-control" name="maturity_date" id="maturity_date" value="' . $res['maturity_date'] . '" readonly>
            </div>

          </div>
          ';
      }
      /*if ($security_type == 'CB' || $security_type == 'GB') {
        echo'
        <div class="col-lg-4 col-md-4 col-sm-12" id="rfq_div_id">
          <label>Order Type<font color="red">*</font></label>
          <select name="order_type_id" id="order_type_id" class="form-control">
            <option value="" selected>-Select Order Type-</option>
            <option value="OTC">Over The Counter</option>
            <option value="RFQ">Request For Quote</option>
          </select>
        </div>
        ';
      }*/
      echo'
      <div class="col-lg-4 col-md-4 col-sm-12" id="v_div">
        <label for="buy_vol">Volume:<font color="red">*</font></label>
        <input type="number" class="form-control" name="buy_vol" id="buy_vol" required>
        <span id="buyVolMsg" style="color:red;" class="help-block"></span>
      </div>

      <div class="col-lg-4 col-md-4 col-sm-12" id="p_div">
        <label for="price">Price:<font color="red">*</font></label>
        <input type="number" class="form-control" name="price" id="price" required>
      </div>';

      if ($security_type == 'CB' || $security_type == 'GB') {
        echo'
        <div class="col-lg-4 col-md-4 col-sm-12" id="ytm_div_id">
          <label for="ytm_id">Yield To Maturity (YTM):</label>
          <input type="number" class="form-control" name="ytm_id" id="ytm_id" readonly>
          <input type="hidden" class="form-control" name="dirty_price" id="dirty_price" readonly>
          <input type="hidden" class="form-control" name="accrued_interest" id="accrued_interest" readonly>
        </div>';
      }

      echo'
      <div class="col-lg-4 col-md-4 col-sm-12" id="avl_amt_div_id">
        <label>Available Amount (Nu.):</label> 
        <input type="hidden" id="cap" value="'.$cap_value.'">
        <input type="hidden" id="mp" value="'.$market_price.'">
        <input type="hidden" id="cash" value="'.$state['tot'].'">
        <input type="hidden" id="security_type" value="'.$secType.'">
        <input type="text" class="form-control" value="'.$bbo_amt.'" readonly>
      </div>';
    } else {
      echo '
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Available Amount (Nu.):</label> 
        <input type="hidden" id="cap" value="'.$cap_value.'">
        <input type="hidden" id="mp" value="'.$market_price.'">
        <input type="text" class="form-control" value="No Amount" readonly>
      </div>';
    }
}
elseif (!empty($_POST["change_id"])) {
  $id = $_POST["change_id"];
  $fid = $_POST["fid"];
  $v = $_POST["v"];
  $side = $_POST["side"];
  $cd_code = $_POST["cd_code"];
  $sy_id = $_POST['sy_id'];

  $b_commis = $_POST['b_commis'];
  $cap_name = 'CAP';
  $market_price = market_price($sy_id); 
  $cap = circuit($cap_name);
  $cap_value = cap_compute($market_price, $cap);
  
  // $wc = $dbh->prepare("SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID FROM bbo_finance a,client_account b WHERE a.cd_code=:ac AND b.cd_code=:ac");
  $wc = $dbh->prepare("SELECT a.cd_code, SUM(a.amount) AS tot, b.cd_code, b.ID
      FROM bbo_finance a
      JOIN client_account b ON a.ID = b.ID
      WHERE a.cd_code = :ac AND b.cd_code = :ac
      GROUP BY a.cd_code, b.cd_code, b.ID
  ");
  $wc->bindParam(':ac',$cd_code);
  $wc->execute();
  $state = $wc->fetch();
}
?>
<script type="text/javascript">
  $("#vol").keyup('input', function() {
    var volume = $("#vol").val();
    var avail_vol = $("#avl_vol").val();

    if (volume % 1 !== 0) {
      $(".submit").show();
      $("#sellVolMsg").html('Volume cannot be decimal!');
    } else {
      $(".submit").show();
      $("#sellVolMsg").html('');
    }

    if(Number(avail_vol) >= Number(volume)) {
      $("#price").show();
      $("#pricel").show();
      $("#msg").hide();
      $("#msg1").hide();
      $("#msg2").hide();
      $("#msg3").hide();
    } else if(volume === '') {
      $(".submit").hide();
      $("#price").hide();
      $("#pricel").hide();
      $("#msg").show();
    } else {
      $(".submit").hide();
      $("#price").hide();
      $("#pricel").hide();
      $("#msg").show();
    }
  });

  /*$("#price, #vol").on("input", function () {
  
  });*/

  $("#price").keyup('input', function() {
    var security_type = $("#security_type").val();
    var price = $("#price").val();
    var cap = $("#cap").val();
    var mp = $("#mp").val();

    // Regex: integer or decimal with up to 2 decimal places
    /*var regex = /^\d+(\.\d{1,2})?$/;
    if (!regex.test(price)) {
        $(".submit").hide();
        $("#msg1").show().html("Please enter a valid price with at most 2 decimal places.");
        return false;
    } else {
        price = parseFloat(price).toFixed(2); // ensure proper 2 decimals
    }*/

    var low_p = (parseFloat(mp) - parseFloat(cap)).toFixed(2);
    var high_p = (parseFloat(mp) + parseFloat(cap)).toFixed(2);

    // if(security_type == 'OS') {
      if(parseFloat(price) < low_p || parseFloat(price) > high_p) {
        $(".submit").hide();
        $("#msg1").show().html('Min: ' + low_p + ' Max: ' + high_p);
      } else {
        var buy_vol = $("#buy_vol").val();
        // buy_vol = buy_vol === '' ? 0 : buy_vol;
        if (buy_vol === '' || buy_vol === 0 ) {
          buy_vol = 0;
          $("#msg3").show().html("Can't be 0.");
        }

        var sell_vol = $("#vol").val();
        // sell_vol = sell_vol === '' ? 0 : sell_vol;
        if(sell_vol === '' || sell_vol === 0) {
          sell_vol = 0;
          $("#msg3").show().html("Can't be 0.");
        }

        var cash = $("#cash").val();
        var mon = isNaN(cash) ? 0 : cash;

        // optimized and GST 5%
        const b_commis = parseFloat($("#b_commis").val()) || 0;
        const priceVal = parseFloat(price) || 0;
        const buyVol = parseFloat(buy_vol) || 0;

        // Base total
        const tot = priceVal * buyVol;
        // Commission (2 decimals)
        const com_amt = +(tot * b_commis / 100).toFixed(2);
        // GST = 5% of commission
        const gst = +(com_amt * 0.05).toFixed(2);
        // Final total
        const final_tot = +(tot + com_amt + gst).toFixed(2);

        if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt)) {
          $(".submit").hide();
          $("#msg1").hide();
          $("#msg2").show().html('Insuffecient cash');
        }

        if (buy_vol === 0 || sell_vol === 0) {
          $(".submit").hide();
          $("#msg").hide();
          $("#msg1").hide();
          $("#msg2").hide();
          $("#msg3").show().html('Please enter volume!');
        } else {
          $(".submit").show();
          $("#msg").hide();
          $("#msg1").hide();
          $("#msg2").hide();
          $("#msg3").hide();

          // calculate YTM
          if (security_type != 'OS') {
              const symbol_id = $("#sy").val();
              const dataString = {
                    calculate_yield_to_maturity : 'calculate_yield_to_maturity',
                    symbol_id,
                    security_type,
                    price,
              };

              $.ajax({
                type: 'POST',
                // url: 'bond_load_function.php',
                url: '../PROCESS/newton_raphson.php',
                data: dataString ,
                dataType: 'JSON',
                success: function(res) {
                  // console.log(res);
                  if(res.status) {
                      $("#ytm_id").val(res.data.ytm);
                      $("#dirty_price").val(res.data.dirtyPrice);
                      $("#accrued_interest").val(res.data.accrued);
                  }
                  else{
                      alert(res.message);
                  }
                }
              });
            }
        }
      }
    /*} else {
        var buy_vol = $("#buy_vol").val();
        // buy_vol = buy_vol === '' ? 0 : buy_vol;
        if(buy_vol === ''  || buy_vol === 0){
          buy_vol=0;
          $("#msg3").show().html("Can't be 0.");
        }

        var sell_vol = $("#vol").val();
        // sell_vol = sell_vol === '' ? 0 : sell_vol;
        if(sell_vol === ''  || sell_vol === 0){
          sell_vol=0;
          $("#msg3").show().html("Can't be 0.");
        }

        var cash = $("#cash").val();
        var mon = isNaN(cash) ? 0 : cash;

        // optimized and GST 5%
        const b_commis = parseFloat($("#b_commis").val()) || 0;
        const priceVal = parseFloat(price) || 0;
        const buyVol = parseFloat(buy_vol) || 0;

        // Base total
        const tot = priceVal * buyVol;
        // Commission (2 decimals)
        const com_amt = +(tot * b_commis / 100).toFixed(2);
        // GST = 5% of commission
        const gst = +(com_amt * 0.05).toFixed(2);
        // Final total
        const final_tot = +(tot + com_amt + gst).toFixed(2);

        if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt)) {    
          $(".submit").hide();
          $("#msg1").hide();
          $("#msg2").show().html('Insuffecient cash');
        }

        if(buy_vol === 0 || sell_vol === 0) {
          $(".submit").hide();
          $("#msg").hide();
          $("#msg1").hide();
          $("#msg2").hide();
          $("#msg3").show().html('Please enter volume!');
        } else {
          $(".submit").show();
          $("#msg").hide();
          $("#msg1").hide();
          $("#msg2").hide();
          $("#msg3").hide();
        }
      }*/
  });

  $("#buy_vol").keyup('input', function() {
    var security_type = $("#security_type").val();
    var price = $("#price").val();
    var cap = $("#cap").val();
    var mp = $("#mp").val();

    if($("#buy_vol").val() % 1 !== 0) {
      $(".submit").hide();
      $("#buyVolMsg").html('Volume cannot be decimal!');
    } else {
      $(".submit").show();
      $("#buyVolMsg").html('');
    }

    var low_p = (parseFloat(mp) - parseFloat(cap)).toFixed(2);
    var high_p = (parseFloat(mp) + parseFloat(cap)).toFixed(2);

    if(security_type == 'OS') {
      if(parseFloat(price) < low_p || parseFloat(price) > high_p) {
        $(".submit").hide();
        $("#msg1").show().html('Min: ' +low_p+ ' Max: ' +high_p);
      } else {
        var buy_vol = $("#buy_vol").val();
        // buy_vol = buy_vol === '' ? 0 : buy_vol;
        if(buy_vol === '' || Number(buy_vol) === 0) {
          buy_vol=0;
          $("#msg3").show().html("Invalid Volume !");
        }

        var cash = $("#cash").val();
        var mon = isNaN(cash) ? 0 : cash;

        /*var b_commis = $("#b_commis").val();
        var tot = parseFloat(price) * parseFloat(buy_vol);
        var com_amt = parseFloat(tot) * parseFloat(b_commis) / 100;
        com_amt = com_amt.toString(); 
        com_amt = com_amt.slice(0, (com_amt.indexOf(".")) + 3); 
        Number(com_amt); 
        var final_tot = parseFloat(com_amt) + parseFloat(tot);*/

        // optimized and GST 5%
        const b_commis = parseFloat($("#b_commis").val()) || 0;
        const priceVal = parseFloat(price) || 0;
        const buyVol = parseFloat(buy_vol) || 0;

        // Base total
        const tot = priceVal * buyVol;

        // Commission (2 decimals)
        const com_amt = +(tot * b_commis / 100).toFixed(2);

        // GST = 5% of commission
        const gst = +(com_amt * 0.05).toFixed(2);

        // Final total
        const final_tot = +(tot + com_amt + gst).toFixed(2);
        
        if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt)) {
          $(".submit").hide();
          $("#msg3").hide();
          $("#msg2").show().html('Insuffecient cash');
        } else if(buy_vol === 0) {
          $(".submit").hide();
          $("#msg2").hide();
          $("#msg3").show().html('Invalid volume!');
        } else {
          $(".submit").show();
          $("#msg2").hide();
          $("#msg3").hide();
        }
      }
    } else {
      var buy_vol = $("#buy_vol").val();
      // buy_vol = buy_vol === '' ? 0 : buy_vol;
      if(buy_vol === '' || Number(buy_vol) === 0){
        buy_vol = 0;
        $("#msg3").show().html("Invalid Volume !");
      }

      var cash = $("#cash").val();
      var mon = isNaN(cash) ? 0 : cash;

      /*var b_commis = $("#b_commis").val();
      var tot = parseFloat(price) * parseFloat(buy_vol);
      var com_amt = parseFloat(tot) * parseFloat(b_commis) / 100;
      com_amt = com_amt.toString(); 
      com_amt = com_amt.slice(0, (com_amt.indexOf(".")) + 3); 
      Number(com_amt); 
      var final_tot = parseFloat(com_amt) + parseFloat(tot);*/

      // optimized and GST 5%
      const b_commis = parseFloat($("#b_commis").val()) || 0;
      const priceVal = parseFloat(price) || 0;
      const buyVol = parseFloat(buy_vol) || 0;

      // Base total
      const tot = priceVal * buyVol;

      // Commission (2 decimals)
      const com_amt = +(tot * b_commis / 100).toFixed(2);

      // GST = 5% of commission
      const gst = +(com_amt * 0.05).toFixed(2);

      // Final total
      const final_tot = +(tot + com_amt + gst).toFixed(2);

      if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt)) {
        $(".submit").hide();
        $("#msg3").hide();
        $("#msg2").show().html('Insuffecient cash');
      } else if(buy_vol === 0) {
        $(".submit").hide();
        $("#msg2").hide();
        $("#msg3").show().html('Invalid volume');
      } else {
        $(".submit").show();
        $("#msg2").hide();
        $("#msg3").hide();
      }
    }
  });
</script>