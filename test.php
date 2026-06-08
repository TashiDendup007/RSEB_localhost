
<?php

/*include ('./CONNECTIONS/db.php'); 
include('f.php');

$orders = $dbh->prepare('SELECT * from cds_holding_copy where symbol_id=10 order by cds_holding_id DESC');
    $orders->execute();
    foreach($orders as $copy)
    {
     $shares = $dbh->prepare('UPDATE cds_holding_copy1 set volume=:v where cds_holding_id=:cd');
      //$shares = $dbh->prepare('INSERT into cds_holding set (volume,pledge_volume,block_volume,pending_in_vol,pending_out_vol) values (:v,:p,:b,:pi,:po)');
      $shares->bindParam(':v',$copy['volume']);
      $shares->bindParam(':cd',$copy['cds_holding_id']);
      $shares->execute();
            echo $copy['volume'];
    }
   $M = market_price(20);
   ECHO $M;*/

    
?>




















<script type="text/javascript">
      $("#vol").keyup('input', function() {
            var volume = $("#vol").val();
            var avail_vol = $("#avl_vol").val();
            if(Number(avail_vol) >= Number(volume))
            {                          
              $(".submit").show();
              $("#price").show();
              $("#pricel").show();
              $("#msg").hide();
            }
            else if(volume === '')
            {
              $(".submit").hide();
              $("#price").hide();
              $("#pricel").hide();
              $("#msg").show();
            }
            else
            {
              $(".submit").hide();
              $("#price").hide();
              $("#pricel").hide();
              $("#msg").show();
            }
      });
</script>
<script type="text/javascript">
      $("#price").keyup('input', function() {
            var price = $("#price").val();
            var cap = $("#cap").val();
            var mp = $("#mp").val();
            var low_p = parseFloat(mp)-parseFloat(cap);
            var high_p = parseFloat(mp) + parseFloat(cap);
            if(price < low_p || price >= high_p)
            {                       
              $(".submit").hide();
              $("#msg1").show().html('<p class="text-left">Price must be between ' +low_p+ ' and ' +high_p+'</p>');
            }
            else
            {
              $(".submit").show();
              $("#msg1").hide();
            }
      });
</script>
<script type="text/javascript">
      $("#buy_vol").keyup('input', function() {
            var price = $("#price").val();
            var buy_vol = $("#buy_vol").val();if(buy_vol===''){buy_vol=0}else{(buy_vol=buy_vol)}
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
              $("#msg2").show().html('<p class="text-left">Insuffecient cash, you have only Nu.  ' + mon+'</p>');
            }
            else if(buy_vol===0)
            {
              $(".submit").hide();
              $("#msg2").hide();
              $("#msg3").show().html('<p class="text-left">Please enter volume!</p>');
            }
            else
            {
              $(".submit").show();
              $("#msg2").hide();
              $("#msg3").hide();
            }
      });
</script>