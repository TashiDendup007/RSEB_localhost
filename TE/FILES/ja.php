<?php
include ('sessionStartFile_client.php');
include ('../../CONNECTIONS/db.php');
include('../../Functions/f.php');
$username = $_SESSION['sess_username'];

$list = ins_id($username);
$ins_id  =$list[0];
$p_code = $list[1];
$broker_user_name = broker_user_name($username);

if(!empty($_POST["cid"])){
    $ac = $_POST['cid'];
    $un = $_POST['p_c'];
    $tp = $_POST['tp'];

    $wc= $dbh->prepare("SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate 
          FROM client_account b 
          JOIN bbo_commission c ON b.bro_comm_id = c.bro_comm_id
          WHERE b.cd_code = :ac AND b.user_name = :un
    ");
    $wc->bindParam(':ac',$ac);
    $wc->bindParam(':un',$un);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0){
      echo'
      <div class=col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <label>Client</label>
        <input type="hidden"  id="b_commis"  value="'.$state['rate'].'" >
        <input type="text" class="form-control" value="'.$state['f_name'].' '.$state['l_name'].' " readonly>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
       <label>CD Code</label>
        <input type="text" class="form-control" name="cd_code" id="cd_code"  value="'.$state['cd_code'].'" readonly>
      </div>';
      if($tp == 'S'){
        echo'
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="sy_div">
          <label>Symbol</label>
          <select name="sy" id="sy" class="form-control" onChange="tots2(this.value);">
            <option value="" selected> Select Symbol </option>';
            $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE security_type IN('OS', 'GB', 'CB') AND status = 1 AND trsstatus = 1");
            $wc->execute();
            while($res= $wc->fetch())
            {
              echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
            }
            echo'
            </select>
        </div>';
      }
      elseif($tp == 'B'){
          echo'
          <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="sy_div">
          <label>Symbol</label>
          <select name="sy" id="sy" class="form-control" onChange="tots3(this.value);">
            <option value="" selected> Select Symbol </option>';
            $wc = $dbh->prepare("SELECT symbol, symbol_id FROM symbol WHERE security_type IN('OS', 'GB', 'CB') AND status = 1 AND trsstatus = 1");
            $wc->execute();
            while($res= $wc->fetch())
            {
              echo '<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
            }
            echo'
            </select>
          </div>';
       }
    }
    else{
       $ac = $_POST['cid'];
       $cnt = 0;
       echo $cnt."|".$ac;
    }
}
elseif(!empty($_POST["sy"]) && !empty($_POST["cd_code"])){
    $sy_id=$_POST['sy'];
    $cd_code=$_POST['cd_code'];
    $cap_name='CAP';
    $market_price=number_format(market_price($sy_id),2, '.', '');
    $cap=number_format(circuit($cap_name),2, '.', '');
    $cap_value=number_format(cap_compute($market_price,$cap),2, '.', '');

    $bondcheck= $dbh->prepare("SELECT security_type from symbol where symbol_id=:sid");
    $bondcheck->bindParam(':sid',$sy_id);
    $bondcheck->execute();
    $bondornot = $bondcheck->fetch();
    $secType =  $bondornot['security_type'];

    $wc= $dbh->prepare("SELECT volume,pending_out_vol,pending_in_vol  from cds_holding where cd_code=:cd and symbol_id=:id");
    $wc->bindParam(':id',$sy_id);
    $wc->bindParam(':cd',$cd_code);
    $wc->execute();
    $state=$wc->fetch();
    if($wc->rowCount() > 0){
      echo'
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <label>Avl. Vol</label>
          <input type="hidden"  id="cap"  value="'.$cap_value.'" >
          <input type="hidden"  id="mp"  value="'.$market_price.'" >
          <input type="hidden"  id="avl_vol"  value="'.$state['volume'].'" >
          <input type="hidden"  id="pov"  value="'.$state['pending_out_vol'].'" >
          <input type="hidden"  id="piv"  value="'.$state['pending_in_vol'].'" >
          <input type="hidden"  id="security_type"  value="'.$secType.'" >
          <input type="text" class="form-control"  value="'.number_format($state['volume'],0,".",",").'" readonly>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="v_div">
            <label for="vol">Sell Volume</label>
            <input type="number" class="form-control" name="vol" id="vol" min="1" step="1" required>
            <span id="msg" style="display:none; color:red;" class="help-block"></span>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="p_div" >
          <label for="price" style="display:none;" id="pricel">Price</label>
          <input type="text" style="display:none;" class="form-control" name="price" id="price" required>
          <span id="msg1" style="display:none; color:red;" class="help-block"></span>
        </div>
        <script type="text/javascript">
          $(document).ready(function() {
              $("#sellsubmit").show();
          });
        </script>';
    }
    else{
      echo '
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <label>Avl. Vol</label>
          <input type="text" class="form-control" value="No Shares." readonly>
        </div>
        <script type="text/javascript">
          $(document).ready(function() {
              $("#sellsubmit").hide();
          });
        </script>';
    }
}
elseif(!empty($_POST["sy"]) && !empty($_POST["ac"])){
    $sy_id=$_POST['sy'];
    $ac=$_POST['ac'];
    $b_commis=$_POST['b_commis'];
    $cap_name='CAP';
    $market_price=number_format(market_price($sy_id),2, '.', '');
    $cap=number_format(circuit($cap_name),2, '.', '');
    $cap_value=number_format(cap_compute($market_price,$cap),2, '.', '');

    $bondcheck= $dbh->prepare("SELECT security_type from symbol where symbol_id=:sid");
    $bondcheck->bindParam(':sid',$sy_id);
    $bondcheck->execute();
    $bondornot = $bondcheck->fetch();
    $secType =  $bondornot['security_type'];

    $wc= $dbh->prepare("SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID
                        from bbo_finance a,client_account b
                        where  a.cd_code=:ac and b.cd_code=:ac and a.status=1");
    $wc->bindParam(':ac',$ac);
    $wc->bindParam(':un',$broker_user_name);
    $wc->execute();
    $state = $wc->fetch();
    if ($wc->rowCount() > 0) {
      $totalAmt = isset($state['tot']) ? $state['tot'] : 0;
      echo '
      <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <label>Avl. Amount</label>
        <input type="hidden" id="cap" value="'.$cap_value.'" >
        <input type="hidden" id="mp" value="'.$market_price.'" >
        <input type="hidden" id="cash" value="'.$state['tot'].'" >
        <input type="hidden" id="security_type" value="'.$secType.'" >
         <input type="text" class="form-control" value="'.number_format($totalAmt, 2, ".", ",").'" readonly>
         <span id="msg2" style="display:none; color:red;" class="help-block"></span>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="p_div">
          <label for="price"> Price</label>
          <input type="text" class="form-control" name="price" id="price" required>
          <span id="msg1" style="display:none; color:red;" class="help-block"></span>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="v_div">
          <label for="buy_vol"> Volume</label>
          <input type="number" step="1" min="1" class="form-control" name="buy_vol" id="buy_vol" required>
          <span id="msg3" style="display:none; color:red;" class="help-block"></span>
        </div>';
    }
    else{
      echo '
      <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <label>Avl. Amount</label>
        <input type="hidden"  id="cap"  value="'.$cap_value.'" >
        <input type="hidden"  id="mp"  value="'.$market_price.'" >
        <input type="text" class="form-control"   value="No Amount" readonly>
      </div>';
    }
}

?>
<script type="text/javascript">
// Cache selectors
var $vol = $("#vol");
var $avail_vol = $("#avl_vol");
var $msg = $("#msg");
var $submit = $(".submit");
var $pricel = $("#pricel");
var $price = $("#price");

$vol.on('input', function() {

  var volume = $(this).val();
  var avail_vol = $avail_vol.val();

  if(volume % 1 !== 0){
    $submit.hide();
    $msg.show().html('Volume cannot be decimal!');
    return false;
  }

  if (Number(avail_vol) >= Number(volume)) {
    $msg.hide();
    $submit.show();
    $pricel.show();
    $price.show();
  } else {
    $msg.show().html('Insufficient Volume');
    $submit.hide();
  }
  if (volume === '' || Number(volume) === 0) {
    $msg.show().html('Invalid Volume');
    $submit.hide();
  }
});

</script>
<script type="text/javascript">
      $("#price").keyup('input', function() {

            var security_type = $("#security_type").val();
            var price = $("#price").val();
            var cap = $("#cap").val();
            var mp = $("#mp").val();

            var low_p = (parseFloat(mp)-parseFloat(cap)).toFixed(2);
            var high_p = (parseFloat(mp) + parseFloat(cap)).toFixed(2);

            if(security_type == 'OS')
            {
              if(parseFloat(price) < low_p || parseFloat(price) > high_p)
              {
                $(".submit").hide();
                $("#msg1").show().html('Min: ' +low_p+ ' Max: ' +high_p);
              }
              else
              {
                var buy_vol = $("#buy_vol").val();
                if(buy_vol==='' || buy_vol==0){
                  buy_vol=0;
                  $("#msg3").show().html("Can't be 0.");
                }
                else{
                  buy_vol=buy_vol;
                }

                var sell_vol = $("#vol").val();
                if(sell_vol==='' || sell_vol==0){
                  sell_vol=0;
                  $("#msg3").show().html("Can't be 0.");
                }
                else{
                  sell_vol=sell_vol;
                }

                var cash = $("#cash").val();
                if(isNaN(cash)){var mon=0;}else{mon=cash;}
                var b_commis= $("#b_commis").val();
                var tot = parseFloat(price)*parseFloat(buy_vol);
                var com_amt=parseFloat(tot)*parseFloat(b_commis)/100;
                com_amt = com_amt.toString();
                com_amt = com_amt.slice(0, (com_amt.indexOf("."))+3);
                Number(com_amt);
                var final_tot=parseFloat(com_amt)+parseFloat(tot);
                /*alert('final total='+final_tot+'and cash in hand is='+cash+'price='+price+'buy_vol='+buy_vol+'b_commis='+b_commis+'tot='+tot+'com_amt is'+com_amt);*/
                if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt))
                {
                  $(".submit").hide();
                  $("#msg1").hide();
                  // $("#msg2").show().html('<p class="text-left">Insuffecient cash, you have only Nu.  ' + mon+'</p>');
                  $("#msg2").show().html('Insuffecient cash');
                }

                if(buy_vol===0 || sell_vol===0)
                {
                  $(".submit").hide();
                  $("#msg").hide();
                  $("#msg1").hide();
                  $("#msg2").hide();
                  $("#msg3").show().html('Please enter volume!');
                }
                else
                {
                  $(".submit").show();
                  $("#msg").hide();
                  $("#msg1").hide();
                  $("#msg2").hide();
                  $("#msg3").hide();
                }
                /*$(".submit").show();
                $("#msg1").hide();*/
              }
            }
            else
            {
                var buy_vol = $("#buy_vol").val();
                if(buy_vol==='' || buy_vol == 0){
                  buy_vol=0;
                  $("#msg3").show().html("Can't be 0.");
                }
                else{
                  buy_vol=buy_vol;
                }
                var sell_vol = $("#vol").val();
                if(sell_vol===''  || sell_vol == 0){
                  sell_vol=0;
                  $("#msg3").show().html("Can't be 0.");
                }
                else{
                  sell_vol=sell_vol;
                }
                var cash = $("#cash").val();
                if(isNaN(cash))
                {
                  var mon=0;
                }else
                {
                  mon=cash;
                }
                var b_commis= $("#b_commis").val();
                var tot = parseFloat(price)*parseFloat(buy_vol);
                var com_amt=parseFloat(tot)*parseFloat(b_commis)/100;
                com_amt = com_amt.toString();
                com_amt = com_amt.slice(0, (com_amt.indexOf("."))+3);
                Number(com_amt);
                var final_tot=parseFloat(com_amt)+parseFloat(tot);
                /*alert('final total='+final_tot+'and cash in hand is='+cash+'price='+price+'buy_vol='+buy_vol+'b_commis='+b_commis+'tot='+tot+'com_amt is'+com_amt);*/
                if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt))
                {
                  $(".submit").hide();
                  $("#msg1").hide();
                  // $("#msg2").show().html('<p class="text-left">Insuffecient cash, you have only Nu.  ' + mon+'</p>');
                  $("#msg2").show().html('Insuffecient cash');
                }

                if(buy_vol===0 || sell_vol===0)
                {
                  $(".submit").hide();
                  $("#msg").hide();
                  $("#msg1").hide();
                  $("#msg2").hide();
                  $("#msg3").show().html('Please enter volume!');
                }
                else
                {
                  $(".submit").show();
                  $("#msg").hide();
                  $("#msg1").hide();
                  $("#msg2").hide();
                  $("#msg3").hide();
                }
            }
      });
</script>
<script type="text/javascript">
      $("#buy_vol").keyup('input', function() {

            var security_type = $("#security_type").val();
            var price = $("#price").val();
            var cap = $("#cap").val();
            var mp = $("#mp").val();

            if($("#buy_vol").val() % 1 !== 0){
              $(".submit").hide();
              $("#msg3").show().html('Volume cannot be decimal!');
              return false;
            }

            /*var low_p = parseFloat(mp)-parseFloat(cap);
            var high_p = parseFloat(mp) + parseFloat(cap);*/
            var low_p = (parseFloat(mp)-parseFloat(cap)).toFixed(2);
            var high_p = (parseFloat(mp) + parseFloat(cap)).toFixed(2);

            //alert(cap+'-'+mp);

            if(security_type == 'OS')
            {
              if(parseFloat(price) < low_p || parseFloat(price) > high_p)
              {
                $(".submit").hide();
                $("#msg1").show().html('Min: ' +low_p+ ' Max: ' +high_p);
              }
              else
              {
                var buy_vol = $("#buy_vol").val();
                if(buy_vol === '' || Number(buy_vol) === 0)
                {
                  buy_vol=0;
                  $("#msg3").show().html("Invalid Volume !");
                }
                else
                {
                  buy_vol=buy_vol;
                }
                var cash = $("#cash").val();
                if(isNaN(cash)){var mon=0;}else{mon=cash;}
                var b_commis= $("#b_commis").val();
                var tot = parseFloat(price)*parseFloat(buy_vol);
                var com_amt=parseFloat(tot)*parseFloat(b_commis)/100;
                com_amt = com_amt.toString();
                com_amt = com_amt.slice(0, (com_amt.indexOf("."))+3);
                Number(com_amt);
                var final_tot=parseFloat(com_amt)+parseFloat(tot);
                /*alert('final total='+final_tot+'and cash in hand is='+cash+'price='+price+'buy_vol='+buy_vol+'b_commis='+b_commis+'tot='+tot+'com_amt is'+com_amt);*/
                if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt))
                {
                  $(".submit").hide();
                  $("#msg3").hide();
                  $("#msg2").show().html('Insuffecient cash');
                }
                else if(buy_vol===0)
                {
                  $(".submit").hide();
                  $("#msg2").hide();
                  $("#msg3").show().html('Invalid volume!');
                }
                else
                {
                  $(".submit").show();
                  $("#msg2").hide();
                  $("#msg3").hide();
                }
              }
            }
            else
            {
              var buy_vol = $("#buy_vol").val();
                if(buy_vol==='' || Number(buy_vol) === 0){
                  buy_vol=0;
                  $("#msg3").show().html("Invalid Volume !");
                }
                else{
                  buy_vol=buy_vol;
                }
                var cash = $("#cash").val();
                if(isNaN(cash)){var mon=0;}else{mon=cash;}
                var b_commis= $("#b_commis").val();
                var tot = parseFloat(price)*parseFloat(buy_vol);
                var com_amt=parseFloat(tot)*parseFloat(b_commis)/100;
                com_amt = com_amt.toString();
                com_amt = com_amt.slice(0, (com_amt.indexOf("."))+3);
                Number(com_amt);
                var final_tot=parseFloat(com_amt)+parseFloat(tot);
                /*alert('final total='+final_tot+'and cash in hand is='+cash+'price='+price+'buy_vol='+buy_vol+'b_commis='+b_commis+'tot='+tot+'com_amt is'+com_amt);*/
                if(final_tot > cash || isNaN(final_tot) || isNaN(com_amt))
                {
                  $(".submit").hide();
                  $("#msg3").hide();
                  $("#msg2").show().html('Insuffecient cash');
                }
                else if(buy_vol===0)
                {
                  $(".submit").hide();
                  $("#msg2").hide();
                  $("#msg3").show().html('Invalid volume');
                }
                else
                {
                  $(".submit").show();
                  $("#msg2").hide();
                  $("#msg3").hide();
                }

            }


      });
</script>
