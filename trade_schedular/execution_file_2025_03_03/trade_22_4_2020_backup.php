<?php
   // define database related variables
date_default_timezone_set("Asia/Thimphu");
include ('../../CONNECTIONS/db.php'); 
include('f.php');
//price discovery start
$deleting_record=$dbh->prepare('DELETE  from price_table');
if($deleting_record->execute()){ 
          $sell=$dbh->prepare('SELECT distinct symbol_id from orders ');
          $sell->execute();
          foreach ($sell as $value){
          $sym_id=$value['symbol_id'];
          $sell=$dbh->prepare('SELECT sum(sell_vol)as total from orders where symbol_id=:sy');
          $sell->bindParam(':sy',$sym_id);
          $sell->execute();
          $s=$sell->fetch();
          $stotal=$s['total'];
          $buy=$dbh->prepare('SELECT sum(buy_vol)as total from orders where symbol_id=:sy');
          $buy->bindParam(':sy',$sym_id);
          $buy->execute();
          $b=$buy->fetch();
          $btotal=$b['total'];
 //sell price entry
          $q2=$dbh->prepare('SELECT  price,symbol_id from orders where side="S" and symbol_id=:sy');
          $q2->bindParam(':sy',$sym_id);
          $q2->execute();
          while ($row =$q2->fetch()) {
                $a=$row['price'];
                $s=$row['symbol_id'];
                $q222 = $dbh->prepare("INSERT into price_table (prices,symbol_id) VALUES ('$a','$s')");
                $q222 ->execute();
          }
 //buy price entry
          $q2=$dbh->prepare('SELECT  price,symbol_id from orders where side="B" and symbol_id=:sy');
          $q2->bindParam(':sy',$sym_id);
          $q2->execute();
          while ($row =$q2->fetch()) {
                $a=$row['price'];
                $s=$row['symbol_id'];
                $q222 = $dbh->prepare("INSERT into price_table (prices,symbol_id) VALUES ('$a','$s')");
                $q222 ->execute();
          }
//sell lot entry
          $q2=$dbh->prepare('SELECT  prices from price_table where symbol_id=:sy');
          $q2->bindParam(':sy',$sym_id);
          $q2->execute();
          foreach ($q2 as $value){    
              $p=$value['prices'];  
              $q2=$dbh->prepare('SELECT sum(sell_vol) as su from orders where price > :p and symbol_id=:sy and side="S"');
              $q2->bindParam(':sy',$sym_id);
              $q2->bindParam(':p', $p);
              $q2->execute();
              $se= $q2->fetch(); 
              $su=$se['su'];
                $vs=$stotal-$su;
                $q222 = $dbh->prepare('UPDATE price_table set volume_sell=:vs where prices =:p and symbol_id=:sy');
                $q222->bindParam(':sy',$sym_id);
                $q222->bindParam(':vs', $vs);
                $q222->bindParam(':p', $p);
                $q222 ->execute();
          }
//buy lot entry
          $q2=$dbh->prepare('SELECT  prices from price_table where symbol_id=:sy');
          $q2->bindParam(':sy',$sym_id);
          $q2->execute();
         foreach ($q2 as $value){    
              $p=$value['prices']; 
              $q2=$dbh->prepare('SELECT sum(buy_vol) as su from orders where price < :p and symbol_id=:sy and side="B"');
              $q2->bindParam(':sy',$sym_id);
              $q2->bindParam(':p', $p);
              $q2->execute();
              $se= $q2->fetch(); 
              $su=$se['su'];
                $vs=$btotal-$su;
                $q222 = $dbh->prepare('UPDATE price_table set volume_buy=:vs where prices =:p and symbol_id=:sy');
                $q222->bindParam(':sy',$sym_id);
                $q222->bindParam(':vs', $vs);
                $q222->bindParam(':p', $p);
                $q222 ->execute();
          }
//finding the difference
          $sys=$dbh->prepare('SELECT distinct  symbol_id from price_table');
          $sys->execute();
          foreach ($sys as $value){
                   $sym_id=$value['symbol_id'];
                   $q2=$dbh->prepare('SELECT  prices from price_table where symbol_id=:sy ');
                   $q2->bindParam(':sy',$sym_id);
                   $q2->execute();
                   foreach ($q2 as $value){    
                        $p=$value['prices']; 
                        $q2=$dbh->prepare('SELECT volume_buy,volume_sell from price_table where prices = :p and symbol_id=:sy ');
                        $q2->bindParam(':sy',$sym_id);
                        $q2->bindParam(':p', $p);
                        $q2->execute();
                        $se= $q2->fetch(); 
                          $a=$se['volume_buy'];
                          $b=$se['volume_sell'];
                          if($a>$b){
                            $l=$b;
                          }
                          elseif ($a<$b){
                            $l=$a;
                          }
                          else{
                            $l=$a;
                          }
                          $q222 = $dbh->prepare('UPDATE price_table set difference=:l,diff_chk=:l where prices =:p and symbol_id=:sy');
                          $q222->bindParam(':sy',$sym_id);
                          $q222->bindParam(':l', $l);
                          $q222->bindParam(':p', $p);
                          $q222 ->execute();
                    }
             }
          }
 }//1st ifloop  end  
 else
 {
  echo "PRICES COULD NOT BE DISCOVERED";
 }       
//price discovery ends

       $q222 = $dbh->prepare('SELECT distinct symbol_id from price_table');
       $q222 ->execute();
foreach($q222 as $value){
       $sym_id=$value['symbol_id'];
       $q222 = $dbh->prepare('SELECT * from price_table where prices=(SELECT max(prices) FROM price_table where symbol_id=:sym_id and difference=(select max(difference) from price_table where symbol_id=:sym_id) ) and symbol_id=:sym_id');
       $q222 ->bindParam(':sym_id',$sym_id);
       $q222 ->execute();
       $value= $q222->fetch();
       $op=$value['prices'];
       $diff=$value['difference'];
       $sym_id=$value['symbol_id'];
       $vb=$value['volume_buy'];
       $vs=$value['volume_sell'];
       $pid=$value['pid'];
       $diff_chk=$value['diff_chk'];
      // echo $diff."---price--".$op."---".$sym_id."--machable--number of row<br>------------<br>";
       if($vb==$vs && $vb==$diff && $vs==$diff){
                     $pr = $dbh->prepare('SELECT * from orders WHERE price <= :op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol  DESC');
                     $pr ->bindParam(':op',$op);$pr ->bindParam(':sym_id',$sym_id);
                     $pr ->execute();
                     foreach ($pr as $value){   
                               $oidd=$value['order_id'];
                               $sell_vol=$value['sell_vol'];
                               $n=0;
                               $v=$sell_vol;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                      }  
                      $pr = $dbh->prepare('SELECT * from orders WHERE price >=:op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC');
                      $pr ->bindParam(':op',$op);$pr ->bindParam(':sym_id',$sym_id);
                      $pr ->execute();
                      foreach($pr as $value){   
                               $oiddb=$value['order_id'];
                               $buy_vol=$value['buy_vol'];
                               $n=0;
                               $v=$buy_vol;
                               $update_buy_lot = $dbh->prepare('UPDATE orders SET exe_vol=:v,buy_vol=:n,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_buy_lot ->bindParam(':oidd', $oiddb);
                               $update_buy_lot->bindParam(':n', $n);
                               $update_buy_lot->bindParam(':op', $op);
                               $update_buy_lot->bindParam(':v', $v);
                               $update_buy_lot->execute();
                      }
         }
         //elseif X
       elseif($vb<$vs && $vb==$diff){
          $diff_chk= compare($pid);
          for($x=$diff_chk; $x>0;){
                     $diff_chk= compare($pid);
                     $rowcountsell= rowcountsell($op,$sym_id);
                     $pr = $dbh->prepare('SELECT * from orders WHERE price <= :op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol DESC');
                     $pr ->bindParam(':op',$op);$pr ->bindParam(':sym_id',$sym_id);
                     $pr ->execute();
                     $allocation=floor($diff_chk/$rowcountsell);
                     foreach ($pr as $value){   
                        $oidd=$value['order_id'];
                        $sell_vol=$value['sell_vol'];
                        $exe_vol=$value['exe_vol'];
                        if($sell_vol == $allocation){           
                               $exe_vol=exe_vol($oidd);               
                               $n=$sell_vol-$allocation;
                               $v=$exe_vol+$allocation;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-$allocation;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                          }
                        elseif ($sell_vol < $allocation){
                               $exe_vol=exe_vol($oidd); 
                               $n=$sell_vol-$sell_vol;
                               $v=$exe_vol+$sell_vol;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-$sell_vol;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                          }
                        elseif($sell_vol > $allocation && $allocation > 0){
                               $exe_vol=exe_vol($oidd); 
                               $n=$sell_vol-$allocation;
                               $v=$exe_vol+$allocation;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-$allocation;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                          }
                        elseif($allocation==0 && $diff_chk < $rowcountsell){
                               $diff_chk= compare($pid);
                                if($diff_chk == 0){
                                }
                                else{
                               $exe_vol=exe_vol($oidd); 
                               $n=$sell_vol-1;
                               $v=$exe_vol+1;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET sell_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);$diff_new=$diff_chk-1;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                               }
                          }
                      } 
                      $x= compare($pid);
                 } 
                      $pr = $dbh->prepare('SELECT * from orders WHERE price >=:op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC');
                      $pr ->bindParam(':op',$op);$pr ->bindParam(':sym_id',$sym_id);
                      $pr ->execute();
                      foreach($pr as $value) {   
                               $oiddb=$value['order_id'];
                               $buy_vol=$value['buy_vol'];
                               $n=0;
                               $v=$buy_vol;
                               $update_buy_lot = $dbh->prepare('UPDATE orders SET exe_vol=:v,buy_vol=:n,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_buy_lot ->bindParam(':oidd', $oiddb);
                               $update_buy_lot->bindParam(':n', $n);
                               $update_buy_lot->bindParam(':op', $op);
                               $update_buy_lot->bindParam(':v', $v);
                               $update_buy_lot->execute();
                      }
         } //elseif X
       elseif($vs<$vb && $vs==$diff){
          $diff_chk= compare($pid);
          for($x=$diff_chk; $x>0;){
                     $diff_chk= compare($pid);
                     $rowcountbuy= rowcountbuy($op,$sym_id);
                     $pr = $dbh->prepare('SELECT * from orders WHERE price >= :op and symbol_id=:sym_id and buy_vol >0 and side="B" ORDER BY buy_vol DESC');
                     $pr ->bindParam(':op',$op);$pr ->bindParam(':sym_id',$sym_id);
                     $pr ->execute();
                     $allocation=floor($diff_chk/$rowcountbuy);
                     foreach ($pr as $value){   
                        $oidd=$value['order_id'];$buy_vol=$value['buy_vol'];$exe_vol=$value['exe_vol'];
                        if($buy_vol == $allocation){    
                               $exe_vol=exe_vol_b($oidd);                     
                               $n=$buy_vol-$allocation;
                               $v=$exe_vol+$allocation;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-$allocation;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                          }
                        elseif ($buy_vol < $allocation) {
                               $exe_vol=exe_vol_b($oidd);
                               $n=$buy_vol-$buy_vol;
                               $v=$exe_vol+$buy_vol;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                                $update_sell_lot->bindParam(':n', $n);
                                $update_sell_lot->bindParam(':op', $op);
                                 $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-$buy_vol;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                          }
                        elseif($buy_vol > $allocation && $allocation > 0) {
                              $exe_vol=exe_vol_b($oidd);$n=$buy_vol-$allocation;$v=$exe_vol+$allocation;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-$allocation;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid);
                                $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                          }
                        elseif($allocation==0 && $diff_chk < $rowcountbuy){
                               $diff_chk= compare($pid);
                                if($diff_chk == 0){
                                }
                                else{
                               $exe_vol=exe_vol_b($oidd); 
                               $n=$buy_vol-1;
                               $v=$exe_vol+1;
                               $update_sell_lot = $dbh->prepare('UPDATE orders SET buy_vol=:n, exe_vol=:v,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_sell_lot ->bindParam(':oidd', $oidd);
                               $update_sell_lot->bindParam(':n', $n);
                               $update_sell_lot->bindParam(':op', $op);
                               $update_sell_lot->bindParam(':v', $v);
                               $update_sell_lot->execute();
                               $diff_chk= compare($pid);
                               $diff_new=$diff_chk-1;
                               $update_diff_chk = $dbh->prepare('UPDATE price_table SET diff_chk=:dif where pid=:pid');
                               $update_diff_chk ->bindParam(':pid', $pid); 
                               $update_diff_chk ->bindParam(':dif', $diff_new);
                               $update_diff_chk->execute();
                                }
                          }
                      } 
                      $x= compare($pid);
                  } 
                      $pr = $dbh->prepare('SELECT * from orders WHERE price <=:op and symbol_id=:sym_id and sell_vol >0 and side="S" ORDER BY sell_vol DESC');
                      $pr ->bindParam(':op',$op); $pr ->bindParam(':sym_id',$sym_id);
                      $pr ->execute();
                      foreach($pr as $value) {   
                               $oiddb=$value['order_id'];$sell_vol=$value['sell_vol'];$n=0; $v=$sell_vol;
                               $update_buy_lot = $dbh->prepare('UPDATE orders SET exe_vol=:v,sell_vol=:n,exe_price=:op,lot_check=:v,order_size=:n where order_id=:oidd');
                               $update_buy_lot ->bindParam(':oidd', $oiddb);
                               $update_buy_lot->bindParam(':n', $n);
                               $update_buy_lot->bindParam(':op', $op);
                               $update_buy_lot->bindParam(':v', $v);
                               $update_buy_lot->execute();
                      }
         }
//allocation end
//echo "Allocation Completed";      
}
//updating executed orders table
$q222 = $dbh->prepare('SELECT a.*,b.institution_id,c.rate from orders a, adm_participants b , bbo_commission c,client_account ca 
                       where a.participant_code=b.participant_code and a.cd_code=ca.cd_code and c.bro_comm_id=ca.bro_comm_id and exe_vol > 0');
$q222 ->execute();
foreach ($q222 as  $value){    
           $oidd=$value['order_id'];
           $price=$value['price'];
           $p_code=$value['participant_code'];
           $order_entry=$value['order_entry'];
           $cd_code=$value['cd_code'];
           $order_exe_price=$value['exe_price'];
           $order_executed_time=date("Y-m-d h:i:s"); 
           $lot_size_execute=$value['exe_vol'];
           $pending_in_vol=$value['exe_vol'];
           $username=$value['order_entry'];
           $institution_id=$value['institution_id'];
           $flag_id=$value['flag_id'];
           $member_broker=$value['member_broker'];
           $status=0;
           $sym_id=$value['symbol_id'];
           $side=$value['side'];
           $s='S';
           $b='B';
           $b_commis=$value['rate'];
           $new_exe_amt=$order_exe_price*$lot_size_execute;
           echo "it reached here";
           $amt=$order_exe_price*$lot_size_execute*$b_commis/100;
           /*$b_commis=client_commission($cd_code);*/
           $executed_orders = $dbh->prepare("INSERT into executed_orders(member_broker,cd_code,order_exe_price,order_date,lot_size_execute,status,symbol_id,side,lot_check,order_id,participant_code,sub_user) VALUES 
                                           ('$member_broker','$cd_code','$order_exe_price','$order_executed_time','$lot_size_execute','$status','$sym_id','$side','$lot_size_execute','$oidd','$p_code','$order_entry')");
           if($executed_orders->execute()){
            echo "...and here";
                         $update_orders = $dbh->prepare('UPDATE orders SET exe_vol=0,exe_price=0 where order_id=:oidd');
                         $update_orders->bindParam(':oidd', $oidd);
                         $update_orders->execute();
                                 if($side===$s){   
                                       $new_exe_amt=$new_exe_amt;
                                       $finding_p_o= $dbh->prepare('SELECT * FROM cds_holding WHERE cd_code=:cd_code and symbol_id=:sym_id and volume > 0');
                                       $finding_p_o->bindParam(':sym_id', $sym_id);
                                       $finding_p_o->bindParam(':cd_code', $cd_code);
                                       $finding_p_o->execute();
                                       foreach ($finding_p_o as  $value)
                                       {
                                        $lo_check_q= $dbh->prepare('SELECT lot_check from orders where order_id=:oidd ');
                                        $lo_check_q->bindParam(':oidd', $oidd);
                                        $lo_check_q->execute();
                                        $loo_check_q= $lo_check_q->fetch();
                                        $lot_check=$loo_check_q['lot_check'];
                                        $exe_vol_new=$lot_check;
                                        $coid=$value['cds_holding_id'];
                                        $existing_pending_out_lot=$value['pending_out_vol'];
                                        $sum=$existing_pending_out_lot+$lot_check;
                                        $pending_out_vol= $value['volume'];

                                          if($exe_vol_new>=$value['volume'])
                                          {   
                                                           $new_block=$value['volume']-$existing_pending_out_lot;          
                                                           $exe_vol_new_s=$exe_vol_new-$new_block;
                                                           $update_lot_check= $dbh->prepare('UPDATE orders SET lot_check=:exe_vol_new_s where order_id=:oidd ');
                                                           $update_lot_check->bindParam(':exe_vol_new_s', $exe_vol_new_s);
                                                           $update_lot_check->bindParam(':oidd', $oidd);
                                                           $update_lot_check->execute();
                                          }  
                                          elseif($exe_vol_new<$value['volume'] && $exe_vol_new >0)
                                          { 
                                                  if($value['volume'] >= $sum){  
                                                             $exe_vol_new_l=0;
                                                             $update_lot_check= $dbh->prepare('UPDATE orders SET lot_check=:exe_vol_new_l where order_id=:oidd ');
                                                             $update_lot_check->bindParam(':exe_vol_new_l', $exe_vol_new_l);
                                                             $update_lot_check->bindParam(':oidd', $oidd);
                                                             $update_lot_check->execute();
                                                       }
                                                  elseif($value['volume']<$sum){
                                                             $new_block_s=$value['volume']-$existing_pending_out_lot;
                                                             $exe_vol_new_m=$exe_vol_new-$new_block_s;
                                                             $update_lot_check= $dbh->prepare('UPDATE orders SET lot_check=:exe_vol_new_m where order_id=:oidd ');
                                                             $update_lot_check->bindParam(':exe_vol_new_m', $exe_vol_new_m);
                                                             $update_lot_check->bindParam(':oidd', $oidd);
                                                             $update_lot_check->execute();
                                                       }
                                          }
                                          else{echo "no more";} 
                                       }   
                                                           $update_finance= $dbh->prepare('UPDATE bbo_finance SET amount=:new_exe_amt where flag_id=:flag_id ');
                                                           $update_finance->bindParam(':new_exe_amt', $new_exe_amt);
                                                           $update_finance->bindParam(':flag_id', $flag_id);
                                                           $update_finance->execute();

                                                           $flag_sell=2;
                                                           $finsellstatus =0;
                                          $remarks_sell="Amount for selling ".$lot_size_execute." share @ Nu.".$order_exe_price;
                                          $b_fin = $dbh->prepare("INSERT into bbo_finance (cd_code,amount,user_name,remarks,flag,institution_id,flag_id,status) VALUES 
                                            ('$cd_code','$new_exe_amt','$member_broker','$remarks_sell','$flag_sell','$institution_id','$oidd','$finsellstatus')");
                                          $b_fin->execute();
                                                           //commission for the seller start
                                                               $flag=4;
                                                               $remarks="Commission for the trade of ".$lot_size_execute." share @ Nu.".$order_exe_price;
                                                               /*$list= ins_id($member_broker);
                                                               $ins_id=$list[0];$p_code=$list[1];*/

                                             $finsellstatuscomm =0;                                                               
                                             $b_fin = $dbh->prepare("INSERT into bbo_finance(cd_code,amount,user_name,remarks,flag,institution_id,flag_id,status)
                                                  VALUES ('$cd_code','-$amt','$member_broker','$remarks','$flag','$institution_id','$oidd','$finsellstatuscomm')");
                                              $b_fin->execute();
                                                                //commission for the seller end 
                                 }
                                 elseif($side===$b){
                                            $new_exe_amt=$new_exe_amt*-1;
                                            $new_bbo_amt=$new_exe_amt*-1;
                                            $cds_client_check= $dbh->prepare("SELECT * from cds_holding where cd_code=:cd_code and symbol_id=:sym_id");
                                            $cds_client_check->bindParam(':cd_code', $cd_code);
                                            $cds_client_check->bindParam(':sym_id', $sym_id);
                                            $cds_client_check->execute(); 
                                            $res = $cds_client_check->fetch();
                                            $buyer_cd_code=$res['cd_code'];
                                            $pending_in_vol_existing=$res['pending_in_vol'];
                                            $pending_in_vol_new=$pending_in_vol_existing+$pending_in_vol;
                                            if($buyer_cd_code===$cd_code){//record update
                                               $save = $dbh->prepare("UPDATE cds_holding SET pending_in_vol=:pending_in_vol_new  where cd_code=:cd_code and symbol_id=:sym_id ");
                                               $save->bindParam(':pending_in_vol_new',$pending_in_vol_new);
                                               $save->bindParam(':cd_code', $cd_code);
                                               $save->bindParam(':sym_id', $sym_id);
                                               $save->execute();
                                            }
                                            else{                                                                                  
                                                 //create new cds_holding entry for pending in
                                              $vol=0;
                                              $type='Record First entered via buy of, '.$pending_in_vol.' number of shares';
                                              $save = $dbh->prepare("INSERT into cds_holding(cd_code,volume,user_name,institution_id,symbol_id,remarks,pending_in_vol)VALUES ('$cd_code','$vol','$username','$institution_id','$sym_id','$type','$pending_in_vol')");
                                              $save->execute();
                                            } 
                                                          $update_finance= $dbh->prepare('UPDATE bbo_finance SET amount=amount+:new_bbo_amt where flag_id=:flag_id ');
                                                           $update_finance->bindParam(':new_bbo_amt', $new_bbo_amt);
                                                           $update_finance->bindParam(':flag_id', $flag_id);
                                                           $update_finance->execute();

                                                            $flag_buy=3;
                                          $remarks_buy="Amount for buying".$lot_size_execute." share @ Nu.".$order_exe_price;
                                          $b_fin = $dbh->prepare("INSERT into bbo_finance (cd_code,amount,user_name,remarks,flag,institution_id,flag_id) VALUES ('$cd_code','$new_exe_amt','$member_broker','$remarks_buy','$flag_buy','$institution_id','$oidd')");
                                          $b_fin->execute();
                                                           //commission for the buyer start
                                                               $flag=4;
                                                               $remarks="Commission for the trade of".$lot_size_execute." share @ Nu.".$order_exe_price; 
                                              $b_fin = $dbh->prepare("INSERT into bbo_finance(cd_code,amount,user_name,remarks,flag,institution_id,flag_id)
                                                            VALUES ('$cd_code','-$amt','$member_broker','$remarks','$flag','$institution_id','$oidd')");
                                                               $b_fin->execute();
                                                               //commission for the buyer end 
                                     }
                                 else{
                                    echo"Message!! Something wrong with buy or sell order";
                                       }
         }
}
//price update market price start
$dateselect=date("Y-m-d");

$specifieddate = $dbh->prepare('SELECT SUBSTRING(min(e.order_date),1,16) dat from executed_orders e where e.side="S" and e.order_date  like "%'.$dateselect.'%"');
$specifieddate->execute();
$spdate = $specifieddate->fetch();

$conditiondate = $spdate['dat'];

if($spdate != 'NULL'){

  echo "TRADE";
  $get_symbol_id=$dbh->prepare('SELECT w.symbol_id,sum(w.lot_size_execute) s,w.order_date from executed_orders w where order_date and w.order_date like "%'.$conditiondate.'%" and w.side="S" group by w.symbol_id order by w.order_date ASC');
  $get_symbol_id->execute();

  //SELECT w.symbol_id,sum(w.lot_size_execute),w.order_date from executed_orders w where w.order_date like "%"(SELECT SUBSTR(min(e.order_date),1,16) from executed_orders e where e.order_date like '%2019-12-27%' and e.side='S')"%"  and w.side='S' group by w.symbol_id order by w.order_date AS
  /*foreach($get_symbol_id as $result)
  {
    echo 'sid->'.$result['symbol_id'].'<br>'.'summ->'.$result['s'].'<br>'; 
  }*/


  /*$get_symbol_id= $dbh->prepare('SELECT distinct symbol_id from executed_orders where order_date like "%'.$dateselect.'%"');
  $get_symbol_id->execute();*/
  foreach($get_symbol_id as $result){   

    if($result['s'] >= 1000)
    {
      $get_price=$dbh->prepare('SELECT order_exe_price,order_date from executed_orders where side="S" and symbol_id=:symbol_id and order_date like "%'.$conditiondate.'%" order by  order_date ASC');
      $get_price->bindParam(':symbol_id',$result['symbol_id']); 
      $get_price->execute();
      $price=$get_price->fetch();
      

      $get_mp=$dbh->prepare('SELECT * from market_price where symbol_id=:symbol_id');
      $get_mp->bindParam(':symbol_id',$result['symbol_id']); 
      $get_mp->execute();
      if($get_mp->rowcount() <= 0)
      {
        $price = $price['order_exe_price'];
        $symid = $result['symbol_id'];
        $up_insert=$dbh->prepare("INSERT into market_price (symbol_id,market_price) VALUES ('$symid','$price')");
        $up_insert->execute();
      }
      else
      {
        $up_insert=$dbh->prepare("UPDATE market_price SET ex_market_price=market_price,ex_date=date where symbol_id=:symbol_id");
        $up_insert->bindParam(':symbol_id',$result['symbol_id']);
        $up_insert->execute();

        $up_price=$dbh->prepare('UPDATE market_price SET market_price=:close_price,date=:dt where symbol_id=:symbol_id');
        $up_price->bindParam(':symbol_id',$result['symbol_id']);
        $up_price->bindParam(':close_price',$price['order_exe_price']);
        $up_price->bindParam(':dt',$price['order_date']); 
        $up_price->execute();
      }
    }
    else
    {

    }
  
}
}
else
{
  echo "NO TRADE";

}
//price update market price end
//delete those orders whose remaining orders are 
$get_orders=$dbh->prepare("SELECT * from orders where order_size=0");
$get_orders->execute();
foreach($get_orders as $del){
  $ooid=$del['order_id'];
  $fid=$del['flag_id'];
  $del=$dbh->prepare("DELETE from orders  where order_id=:ooid");
  $del->bindParam(':ooid',$ooid);
  $del->execute();
  
    $del1=$dbh->prepare("DELETE from bbo_finance  where flag_id=:fid");
    $del1->bindParam(':fid',$fid);
    $del1->execute();
}
//end order deletion
?>