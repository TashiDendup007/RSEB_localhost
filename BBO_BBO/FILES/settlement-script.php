<?php
include ('../../CONNECTIONS/db.php'); 
include('../../Functions/f.php');
$username=$_SESSION['sess_username'];
if(isset($_POST['SETT'])) 
{ 
            $b='Buyer_L';
            $l_charge = $dbh->prepare("SELECT rate from labour_charge where name=:b");
            $l_charge->bindParam(':b', $b);
            $l_charge->execute();
            $row = $l_charge->fetch();
            $b_l_c=$row['rate'];
            $s='Seller_L';
            $l_charge = $dbh->prepare("SELECT rate from labour_charge where name=:s");
            $l_charge->bindParam(':s', $s);
            $l_charge->execute();
            $row = $l_charge->fetch();
            $s_l_c=$row['rate'];
  /*---updating contract seller ---*/
  $q = $dbh->prepare("SELECT * from executed_orders where  status=0");
  $q->execute();
  foreach($q as $row){
                    $id=$row['exe_id'];
                    $cd_code=$row['cd_code'];
                    $p_code=$row['participant_code'];
                    $order_exe_price=$row['order_exe_price'];
                    $lot_size_execute=$row['lot_size_execute'];
                    $status=$row['status'];
                    $symbol_id=$row['symbol_id'];
                    $buy='B';
                    $sell='S';
                    $side=$row['side'];
                    $st=1;
                    if($side==$buy){ //buy orders
                        $q = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cdcode and symbol_id=:symbol_id");
                        $q->bindParam(':cd_code', $cd_code,PDO:: PARAM_STR); $q->bindParam(':symbol_id', $symbol_id,PDO:: PARAM_STR);
                        $q->execute();
                        foreach($q as $row){
                                  $q = $dbh->prepare("SELECT lot_check from executed_orders where exe_id=:id and status=0");
                                  $q->bindParam(':id', $id);$q->execute();
                                  $val = $q->fetch();
                                  $l_check=$val['lot_check'];
                                  $cd_code=$row['cd_code'];
                                  $existing_vol=$row['volume'];
                                  $pending_in_vol=$row['pending_in_vol'];
                                  $cds_holding_id=$row['cds_holding_id'];
                            if($l_check >0 && $pending_in_vol>0){
                                if($l_check ==$pending_in_vol){
                                    $pending_in_vol_new=0;
                                    $vol_new=$existing_vol+$pending_in_vol;
                                    $status=1;
                                    $l_check=0;
                                  }
                                  elseif($l_check < $pending_in_vol){
                                    $pending_in_vol_new=$pending_in_vol-$l_check;
                                    $vol_new=$existing_vol+$l_check;
                                    $status=1;
                                    $l_check=0;
                                  }
                                  elseif($l_check > $pending_in_vol && $pending_in_vol >0 ){
                                    $pending_in_vol_new=0;
                                    $vol_new=$existing_vol+$pending_in_vol;
                                    $status=0;
                                    $l_check=$l_check-$pending_in_vol;
                                  }
                                  $q = $dbh->prepare("UPDATE cds_holding SET pending_in_vol=:pending_in_vol_new,
                                  volume=:vol_new where cd_code=:cd_code and symbol_id=:symbol_id and cds_holding_id=:ccid");
                                  $q->bindParam(':pending_in_vol_new', $pending_in_vol_new);$q->bindParam(':vol_new', $vol_new);$q->bindParam(':cd_code', $cd_code);$q->bindParam(':ccid', $cds_holding_id);$q->bindParam(':symbol_id', $symbol_id);
                                  if($q->execute()){
                                        $q = $dbh->prepare("UPDATE executed_orders SET status=:st, lot_check=:lc where exe_id=:id ");
                                        $q->bindParam(':id', $id);
                                        $q->bindParam(':lc', $l_check);
                                        $q->bindParam(':st', $status);
                                        if($q->execute()){
                                                          //commission for the buyer start
                                                               $flag=4;
                                                               $remarks="Commission for the trade of".$lot_size_execute." share @ Nu.".$order_exe_price.;
                                                               $list= ins_id($username);
                                                               $ins_id=$list[0];$p_code=$list[1];
                                                               $b_fin = $dbh->prepare("INSERT into bbo_finance(cd_code,amount,user_name,remarks,flag,institution_id,flag_id) VALUES ('$cd_code','$amt','$username','$remarks','$flag','$ins_id','$id')");
                                                               $b_fin->execute());
                                                          //commission for the buyer end 
                                              }
                                        else{
                                           echo"error while commission insert";
                                        }
                                      }
                                 else{
                                    echo"error while updating cds_holding";
                                  }
                                }      
                            else{
                              echo"some error on lot check";
                             }
                        }       
                    }
                    elseif($side==$sell){  //sell orders
                        $q = $dbh->prepare("SELECT * FROM cds_holding WHERE cd_code=:cdcode and symbol_id=:symbol_id");
                        $q->bindParam(':cd_code', $cd_code,PDO:: PARAM_STR); $q->bindParam(':symbol_id', $symbol_id,PDO:: PARAM_STR);
                        $q->execute();
                        foreach($q as $row){
                          $q = $dbh->prepare("SELECT lot_check from executed_orders where exe_id=:id and status=0");
                          $q->bindParam(':id', $id);
                          $q->execute();
                          $val = $q->fetch();
                          $cd_code=$row['cd_code'];
                                  $existing_vol=$row['volume'];
                                  $pending_out_vol=$row['pending_out_vol'];
                                  $cds_holding_id=$row['cds_holding_id'];
                            if($l_check >0 && $pending_out_vol>0){ 
                                  if($l_check ==$pending_out_vol){
                                    $pending_out_vol_new=0;
                                    $vol_new=$existing_vol-$pending_out_vol;
                                    $status=1;
                                    $l_check=0;
                                  }
                                  elseif($l_check < $pending_out_vol){
                                    $pending_out_vol_new=$pending_out_vol-$l_check;
                                    $vol_new=$existing_vol-$l_check;
                                    $status=1;
                                    $l_check=0;
                                  }
                                  elseif($l_check > $pending_out_vol && $pending_out_vol >0 ){
                                    $pending_out_vol_new=0;
                                    $vol_new=$existing_vol-$pending_out_vol;
                                    $status=0;
                                    $l_check=$l_check-$pending_out_vol;
                                  }
                                   $q = $dbh->prepare("UPDATE cds_holding SET pending_out_vol=:pending_out_vol_new,
                                  volume=:vol_new where cd_code=:cd_code and symbol_id=:symbol_id and cds_holding_id=:ccid");
                                  $q->bindParam(':pending_out_vol_new', $pending_out_vol_new);$q->bindParam(':vol_new', $vol_new);$q->bindParam(':cd_code', $cd_code);$q->bindParam(':ccid', $cds_holding_id);$q->bindParam(':symbol_id', $symbol_id);
                                  if($q->execute()){
                                        $q = $dbh->prepare("UPDATE executed_orders SET status=:st, lot_check=:lc where exe_id=:id ");
                                        $q->bindParam(':id', $id);
                                        $q->bindParam(':lc', $l_check);
                                        $q->bindParam(':st', $status);
                                        if($q->execute()){
                                                          //commission for the seller start
                                                               $flag=4;
                                                               $remarks="Commission for the trade of".$lot_size_execute." share @ Nu.".$order_exe_price.;
                                                               $list= ins_id($username);
                                                               $ins_id=$list[0];$p_code=$list[1];
                                                               $b_fin = $dbh->prepare("INSERT into bbo_finance(cd_code,amount,user_name,remarks,flag,institution_id,flag_id) VALUES ('$cd_code','$amt','$username','$remarks','$flag','$ins_id','$id')");
                                                               $b_fin->execute());
                                                          //commission for the seller end 
                                        }
                                        else{
                                          echo"error while processing commission for seller";
                                        }
                                      }
                                     else
                                     {
                                       echo "error while updating cds holding of seller";
                                     }
                              }
                              else
                              {
                                echo"error while checking the lot";
                              }
                        }
                    }
  }   
}
?>