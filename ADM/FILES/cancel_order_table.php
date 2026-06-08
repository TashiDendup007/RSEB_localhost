<?php 
  $wc= $dbh->prepare("SELECT a.*, b.symbol,b.symbol_id from symbol b,orders a where a.symbol_id=b.symbol_id  order by order_date desc");
  $wc->execute();
  $i=1;
  foreach($wc as $res){
    $cap_name='CAP';
    $market_price=market_price($res['symbol_id']); 
    $cap=circuit($cap_name);
    $cap_value=round(cap_compute($market_price,$cap),2);
    $low_p = round($market_price-$cap_value,2);
    $high_p = round($market_price+$cap_value,2);

    if($res['price'] < $low_p || $res['price'] >  $high_p)
    {
      if($res['side']=='S'){ 
      echo'
      <tr class="" style="background-color:red;">
        <input type="hidden" value="'.$res['symbol'].'"  id="sy'.$i.'">
        <input type="hidden" value="'.$res['symbol_id'].'"  id="sy_id'.$i.'">
        <input type="hidden" value="'.$res['cd_code'].'"  id="cd_code'.$i.'">
        <input type="hidden" value="'.$res['sell_vol'].'"  id="v'.$i.'">
        <input type="hidden" value="'.$res['flag_id'].'"  id="fid'.$i.'">
        <input type="hidden" value="'.$res['side'].'"  id="side'.$i.'">
        <td>'.$res['symbol'].'</td>
        <td>'.$res['cd_code'].'</td>
        <td>'.$res['member_broker'].'</td>
        <td>'.$res['order_entry'].'</td>
        <td>'.$res['price'].'</td>
        <td>'.$res['sell_vol'].'</td>
        <td>SELL</td>
        <td>'.$res['order_date'].'</td>
        <td><button name="del_or" id="del_or'.$i.'" value="'.$res['order_id'].'"  onclick="return fun('.$i.');" style="background-color:red;"><i class="fa fa-trash-o"></i>Delete</button></td>
      </tr>'; 
      }elseif($res['side']=='B'){
      echo'
      <tr class="" style="background-color:red;">
        <input type="hidden" value="'.$res['symbol'].'"  id="sy'.$i.'">
        <input type="hidden" value="'.$res['symbol_id'].'"  id="sy_id'.$i.'">
        <input type="hidden" value="'.$res['cd_code'].'"  id="cd_code'.$i.'">
        <input type="hidden" value="'.$res['buy_vol'].'"  id="v'.$i.'">
        <input type="hidden" value="'.$res['flag_id'].'"  id="fid'.$i.'">
        <input type="hidden" value="'.$res['side'].'"  id="side'.$i.'">
        <td>'.$res['symbol'].'</td>
        <td>'.$res['cd_code'].'</td>
        <td>'.$res['member_broker'].'</td>
        <td>'.$res['order_entry'].'</td>
        <td>'.$res['price'].'</td>
        <td>'.$res['buy_vol'].'</td>
        <td>BUY</td><td>'.$res['order_date'].'</td>
        <td><button name="del_or" id="del_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');" style="background-color:red;" ><i class="fa fa-trash-o"></i>Delete</button></td>
      </tr>';  
      } 
    }
    else
    {
      if($res['side']=='S'){ 
      echo'
      <tr style="background-color:#e8d4d7;">
        <input type="hidden" value="'.$res['symbol'].'"  id="sy'.$i.'">
        <input type="hidden" value="'.$res['symbol_id'].'"  id="sy_id'.$i.'">
        <input type="hidden" value="'.$res['cd_code'].'"  id="cd_code'.$i.'">
        <input type="hidden" value="'.$res['sell_vol'].'"  id="v'.$i.'">
        <input type="hidden" value="'.$res['flag_id'].'"  id="fid'.$i.'">
        <input type="hidden" value="'.$res['side'].'"  id="side'.$i.'">
        <td>'.$res['symbol'].'</td>
        <td>'.$res['cd_code'].'</td>
        <td>'.$res['member_broker'].'</td>
        <td>'.$res['order_entry'].'</td>
        <td>'.$res['price'].'</td>
        <td>'.$res['sell_vol'].'</td>
        <td>SELL</td><td>'.$res['order_date'].'</td>
        <td><button name="del_or" id="del_or'.$i.'" value="'.$res['order_id'].'"  onclick="return fun('.$i.');"><i class="fa fa-trash-o"></i>Delete</button></td>
      </tr>'; 
      }elseif($res['side']=='B'){
      echo'
      <tr style="background-color:#dce2e9;">
        <input type="hidden" value="'.$res['symbol'].'"  id="sy'.$i.'">
        <input type="hidden" value="'.$res['symbol_id'].'"  id="sy_id'.$i.'">
        <input type="hidden" value="'.$res['cd_code'].'"  id="cd_code'.$i.'">
        <input type="hidden" value="'.$res['buy_vol'].'"  id="v'.$i.'">
        <input type="hidden" value="'.$res['flag_id'].'"  id="fid'.$i.'">
        <input type="hidden" value="'.$res['side'].'"  id="side'.$i.'">
        <td>'.$res['symbol'].'</td>
        <td>'.$res['cd_code'].'</td>
        <td>'.$res['member_broker'].'</td>
        <td>'.$res['order_entry'].'</td>
        <td>'.$res['price'].'</td>
        <td>'.$res['buy_vol'].'</td>
        <td>BUY</td><td>'.$res['order_date'].'</td>
        <td><button name="del_or" id="del_or'.$i.'" value="'.$res['order_id'].'" onclick="return fun('.$i.');" ><i class="fa fa-trash-o"></i>Delete</button></td>
      </tr>';  
      }
    }
  $i++;
  }
  ?>