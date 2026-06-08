<?php
	$id=$_POST["change_id"];
	$fid=$_POST["fid"];
	$ex_vol=$_POST["v"];
	$e_v=$_POST["e_v"];
	$e_p=$_POST["e_p"];
	$e_p = round($e_p, 2);
	$side=$_POST["side"];
	$cd_code=$_POST["cd_code"];
	$sy_id=$_POST["sy_id"];

	$cap_name='CAP';
	$market_price=market_price($sy_id); 

	$cap=circuit($cap_name);
	$cap_value=cap_compute($market_price,$cap);
	$up = round($market_price+$cap_value,2);
	$dw = round($market_price-$cap_value,2);

	$b_commis=client_commission($cd_code,$username);
	$tot=cash_total($cd_code,$username);
	$list=pending_vol($cd_code,$sy_id);
	$pov=$list[0];
	$piv=$list[1];
	$vol=$list[2];

	$check = date("h:i:s");
  $trade10_a = date('h:i:s',strtotime("09:55:00"));
  $trade10_b = date('h:i:s',strtotime("10:05:00"));
  $trade11_a = date('h:i:s',strtotime("10:55:00"));
  $trade11_b = date('h:i:s',strtotime("11:05:00"));
  $trade12_a = date('h:i:s',strtotime("11:55:00"));
  $trade12_b = date('h:i:s',strtotime("12:05:00"));
  $trade2_a =  date('h:i:s',strtotime("01:55:00"));
  $trade2_b =  date('h:i:s',strtotime("02:05:00"));
  $trade3_a =  date('h:i:s',strtotime("02:55:00"));
  $trade3_b =  date('h:i:s',strtotime("03:05:00"));

  if(($check > $trade10_a && $check < $trade10_b) || ($check > $trade11_a && $check < $trade11_b) || 
    ($check > $trade12_a && $check < $trade12_b) || ($check > $trade2_a && $check < $trade2_b) || ($check > $trade3_a && $check < $trade3_b))
	{
		echo'<div class="col-lg-6 col-xs-6"><div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Market Closed.</div></div></div>';
		die();

	}
	else
	{
		if($side=='S'){
			$avl_vol_change = $vol+$ex_vol;

			if($e_p > $up || $e_p < $dw){
				echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
				<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
				<i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! Price should be between '.$dw.' & '.$up.'.</div></div>'; die();
			}else{
				if($avl_vol_change >= $e_v){
					$new_vol=$avl_vol_change-$e_v;
					$new_pov=$pov-$ex_vol+$e_v;
					$new_commis_amt=$e_v*$e_p*$b_commis/100;
					$new_amt=($e_v*$e_p)+$new_commis_amt; 

					try{
						$dbh->beginTransaction();

						$cds_acc=$dbh->prepare("UPDATE cds_holding SET pending_out_vol=:new_pov,volume=:new_vol WHERE cd_code=:cdcode and symbol_id=:sy_id");
						$cds_acc->bindParam(':new_pov',$new_pov);
						$cds_acc->bindParam(':new_vol',$new_vol);
						$cds_acc->bindParam(':cdcode',$cd_code);
						$cds_acc->bindParam(':sy_id',$sy_id);
						$cds_acc->execute();

						$bbo_fin_up=$dbh->prepare("UPDATE bbo_finance SET amount=:new_amt WHERE flag_id=:fid");
						$bbo_fin_up->bindParam(':fid',$fid);
						$bbo_fin_up->bindParam(':new_amt',$new_amt);
						$bbo_fin_up->execute();

						$check= $dbh->prepare('SELECT flag_id from orders where order_id=:id');
						$check->bindParam(':id',$id);
						$check->execute();
						$res=$check->fetch();
						$flag_id = $res['flag_id'];

						$order_audit=order_audit($cd_code,$particpant_code,$username,$e_v,$e_v,$sy_id,$e_p,$side,$new_commis_amt,$flag_id,$username);

						$ord_up=$dbh->prepare("UPDATE orders SET sell_vol=:new_sell_vol,order_size=:new_sell_vol,price=:new_price,commis_amt=:new_commis_amt WHERE order_id=:id");
						$ord_up->bindParam(':id',$id);
						$ord_up->bindParam(':new_price',$e_p);
						$ord_up->bindParam(':new_sell_vol',$e_v);
						$ord_up->bindParam(':new_commis_amt',$new_commis_amt);
						$ord_up->execute();

						$dbh->commit();
						$dbh = null;

						echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-success alert-dismissible">
							<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
							<i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Order Changed.</div></div>';
						exit();
					}
					catch(PDOException $e){
						$dbh->rollBack();

						echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
							<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
							<i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Error! == '.$e->getMessage().'</div></div>';
						exit();
					}
				}else{
					echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
					<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
					<i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! Not enough shares.</div></div>';exit();
				}
			}
		}
		elseif($side=='B'){
			$e_amt=prev_amt_ord($fid);
			$new_commis_amt=$e_v*$e_p*$b_commis/100;
			$new_amt=($e_v*$e_p)+$new_commis_amt; 
			$new_amt=$new_amt;
			$avl_amt=$tot+$e_amt;
			if($e_p > $up || $e_p < $dw)
			{
				echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
				<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
				<i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! Price should be between '.$dw.' & '.$up.'.</div></div>';exit();
				
			}
			else
			{
				try{
					$dbh->beginTransaction();
					$ex_comission = $ex_vol*$e_p*$b_commis/100;
				  $ex_amount = ($ex_vol*$e_p)+$ex_comission;
				  $ex_total_amount = $ex_amount+$tot;

				  if($ex_total_amount >= $new_amt){
						$new_amt=$new_amt*-1;
						$bbo_fin_up=$dbh->prepare("UPDATE bbo_finance SET amount=:new_amt WHERE flag_id=:fid");
						$bbo_fin_up->bindParam(':fid',$fid);
						$bbo_fin_up->bindParam(':new_amt',$new_amt);
						$bbo_fin_up->execute();

						$check= $dbh->prepare('SELECT flag_id from orders where order_id=:id');
						$check->bindParam(':id',$id);
						$check->execute();
						$res=$check->fetch();
						$flag_id = $res['flag_id'];

						$order_audit=order_audit($cd_code,$particpant_code,$username,$e_v,$e_v,$sy_id,$e_p,$side,$new_commis_amt,$flag_id,$username);
					
						$sql="UPDATE orders SET buy_vol=:new_buy_vol,order_size=:new_buy_vol,price=:new_price,commis_amt=:new_commis_amt WHERE order_id=:id";	
						$ord_up=$dbh->prepare($sql);
						$ord_up->bindParam(':id',$id);
						$ord_up->bindParam(':new_price',$e_p);
						$ord_up->bindParam(':new_buy_vol',$e_v);
						$ord_up->bindParam(':new_commis_amt',$new_commis_amt);
						$ord_up->execute();

						$dbh->commit();
						$dbh = null;

						echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
							</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Buy Order Updated.</div></div>';
						exit();

					}else{
						echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;
						</button><i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Oops Sorry! Not enough CASH.</div></div>';
						exit();
					}
				}catch(PDOException $e){
					$dbh->rollBack();
					echo'<div class="col-lg-8 col-xs-8"><div class="alert alert-danger alert-dismissible">
						<button type="button" class="close" data-dismiss="alert"aria-hidden="true">&times;</button>
						<i class="icon fa fa-check"> </i> Message!&nbsp;&nbsp Error! == '.$e->getMessage().'</div></div>';
					exit();
				}
				
			}
		}
	}
?>