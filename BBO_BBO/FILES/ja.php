<?php
include ('session_start_file.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');

if(!empty($_POST["cid"])) {
  $ac = $_POST['cid'];
  $tp = $_POST['tp'];
  $un = substr($username,0,7);

  $wc= $dbh->prepare("SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate 
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
  } else {
      $ac = $_POST['cid'];
      $cnt = 0;
      echo $cnt."|".$ac;
  }
  die();
}
elseif (!empty($_POST["sy"]) && !empty($_POST["cd_code"])) {
    $sy_id = $_POST['sy'];
    $cd_code = $_POST['cd_code'];
    $cap_name = 'CAP';

    $market_price = number_format(market_price($sy_id),2, '.', ''); 
    $cap = number_format(circuit($cap_name),2, '.', '');
    $cap_value = number_format(cap_compute($market_price,$cap),2, '.', '');

    $bondcheck = $dbh->prepare("SELECT security_type FROM symbol WHERE symbol_id=:sid");
    $bondcheck->bindParam(':sid',$sy_id);
    $bondcheck->execute();
    $bondornot = $bondcheck->fetch();
    $secType =  $bondornot['security_type'];

    $wc = $dbh->prepare("SELECT volume, pending_out_vol, pending_in_vol FROM cds_holding WHERE cd_code = :cd and symbol_id = :id");
    $wc->bindParam(':id',$sy_id);
    $wc->bindParam(':cd',$cd_code);
    $wc->execute();
    $state = $wc->fetch();
    if($wc->rowCount() > 0) {
      echo'
      <div class="col-lg-4 col-md-4 col-sm-12">
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
        <input type="text" class="form-control" name="vol" id="vol" required>
        <span id="sellVolMsg" style="color:red;" class="help-block"></span>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-12" id="p_div" >
        <label for="price" style="display:none;" id="pricel">Price</label>
        <input type="text" style="display:none;" class="form-control" name="price" id="price" required>
      </div>';
    } else {
      echo'
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Avl. Vol</label> 
        <input type="text" class="form-control" value="No Shares." readonly>
      </div>';
    }
}
elseif(!empty($_POST["sy"]) && !empty($_POST["ac"])) {
    $sy_id = $_POST['sy'];
    $ac = $_POST['ac'];

    /*$price=$_POST['price'];
    $vol=$_POST['buy_vol'];*/
    
    $b_commis = $_POST['b_commis'];
    $cap_name = 'CAP';
    $market_price = number_format(market_price($sy_id),2, '.', ''); 
    $cap = number_format(circuit($cap_name),2, '.', '');
    $cap_value = number_format(cap_compute($market_price,$cap),2, '.', '');

    $bondcheck = $dbh->prepare("SELECT security_type from symbol where symbol_id=:sid");
    $bondcheck->bindParam(':sid',$sy_id);
    $bondcheck->execute();
    $bondornot = $bondcheck->fetch();
    $secType =  $bondornot['security_type'];

    $wc = $dbh->prepare("SELECT a.cd_code, sum(a.amount) AS tot, b.cd_code, b.ID 
      FROM bbo_finance a, client_account b 
      WHERE a.cd_code=:ac AND b.cd_code=:ac AND a.status=1
    ");
    $wc->bindParam(':ac',$ac);
    $wc->bindParam(':un',$username);
    $wc->execute();
    $state = $wc->fetch();

    $bbo_amt = isset($state['tot']) ? number_format($state['tot'],2,".",",") : 0;

    if($wc->rowCount() > 0) {
    echo '  
    <div class="col-lg-4 col-md-4 col-sm-12" id="p_div">
      <label for="price"> Price</label>
      <input type="number" class="form-control" name="price" id="price" required>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12" id="v_div">
      <label for="buy_vol"> Volume</label>
      <input type="text" class="form-control" name="buy_vol" id="buy_vol" required>
      <span id="buyVolMsg" style="color:red;" class="help-block"></span>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
      <label>Avl. Amount</label> 
      <input type="hidden" id="cap"  value="'.$cap_value.'" >
      <input type="hidden" id="mp"  value="'.$market_price.'" >
      <input type="hidden" id="cash"  value="'.$state['tot'].'" >
      <input type="hidden" id="security_type"  value="'.$secType.'" >
      <input type="text" class="form-control" value="'.$bbo_amt.'" readonly>
    </div>';
    } else {
      echo '
      <div class="col-lg-4 col-md-4 col-sm-12">
        <label>Avl. Amount</label> 
        <input type="hidden" id="cap" value="'.$cap_value.'" >
        <input type="hidden" id="mp" value="'.$market_price.'" >
        <input type="text" class="form-control"  value="No Amount" readonly>
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

  $("#price").keyup('input', function() {
    var security_type = $("#security_type").val();
    var price = $("#price").val();
    var cap = $("#cap").val();
    var mp = $("#mp").val();

    var low_p = (parseFloat(mp) - parseFloat(cap)).toFixed(2);
    var high_p = (parseFloat(mp) + parseFloat(cap)).toFixed(2);

    if(security_type == 'OS') {
      if(parseFloat(price) < low_p || parseFloat(price) > high_p) {
        $(".submit").hide();
        $("#msg1").show().html('Min: ' +low_p+ ' Max: ' +high_p);
      } else {
        var buy_vol = $("#buy_vol").val();
        // buy_vol = buy_vol === '' ? 0 : buy_vol;
        if(buy_vol === '' || buy_vol === 0 ){
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

        var b_commis= $("#b_commis").val();
        var tot = parseFloat(price) * parseFloat(buy_vol);
        var com_amt=parseFloat(tot) * parseFloat(b_commis) / 100;
        com_amt = com_amt.toString(); 
        com_amt = com_amt.slice(0, (com_amt.indexOf(".")) + 3); 
        Number(com_amt); 
        var final_tot = parseFloat(com_amt) + parseFloat(tot);

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
        }
      }
    } else {
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

        var b_commis = $("#b_commis").val();
        var tot = parseFloat(price) * parseFloat(buy_vol);
        var com_amt = parseFloat(tot) * parseFloat(b_commis) / 100;
        com_amt = com_amt.toString(); 
        com_amt = com_amt.slice(0, (com_amt.indexOf(".")) + 3); 
        Number(com_amt); 
        var final_tot=parseFloat(com_amt)+parseFloat(tot);

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
      }
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

        var b_commis = $("#b_commis").val();
        var tot = parseFloat(price) * parseFloat(buy_vol);
        var com_amt = parseFloat(tot) * parseFloat(b_commis) / 100;
        com_amt = com_amt.toString(); 
        com_amt = com_amt.slice(0, (com_amt.indexOf(".")) + 3); 
        Number(com_amt); 
        var final_tot = parseFloat(com_amt) + parseFloat(tot);
        
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

      var b_commis = $("#b_commis").val();
      var tot = parseFloat(price) * parseFloat(buy_vol);
      var com_amt = parseFloat(tot) * parseFloat(b_commis) / 100;
      com_amt = com_amt.toString(); 
      com_amt = com_amt.slice(0, (com_amt.indexOf(".")) + 3); 
      Number(com_amt); 
      var final_tot = parseFloat(com_amt) + parseFloat(tot);

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