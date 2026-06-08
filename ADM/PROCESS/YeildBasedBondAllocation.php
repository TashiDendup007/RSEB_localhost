<?php
include ('../../CONNECTIONS/db.php');
//echo "start".time();

$TOTALAVLVOL=700000;

$ords = $dbh->prepare("SELECT sum(order_size) orders from bond where symbol_id=69 and  status=0");
$ords->execute();
$ord = $ords->fetch();
$TOTALORDERS = $ord['orders'];

$TV=$TOTALAVLVOL;

if($TOTALORDERS < $TV)
{
  $price = $dbh->prepare('SELECT bid_price from bond where status=0 order by bid_price DESC limit 1');
  $price->execute();
  $pr=$price->fetch();

  $all = $dbh->prepare('SELECT order_size,order_id from bond where  status=0 and bid_price <= :price order by bid_price ASC');
  $all->bindParam(':price',$pr['bid_price']);
  $all->execute();
  $n=0;
  foreach($all as $orders)
  {
    $update = $dbh->prepare('UPDATE bond set allocated_size=:al,price_discovered=:price where order_id=:orid ');
    $update->bindParam(':al',$orders['order_size']);
    $update->bindParam(':price',$pr['bid_price']);
    $update->bindParam(':orid',$orders['order_id']);
    $update->execute();

    $n += $orders['order_size'];
  }
  echo $n;

}
else{

    $price = $dbh->prepare('SELECT distinct(bid_price) from bond where symbol_id=69 and  status=0 order by bid_price ASC');
    $price->execute();
    foreach($price as $volume){
        $sum = $dbh->prepare('SELECT sum(order_size) as total from bond where  status=0 and bid_price <= :price');
        $sum->bindParam(':price',$volume['bid_price']);
        $sum->execute();
        $res=$sum->fetch();
        $totalVoldis = $res['total']; 
        //echo $totalVoldis.'-'.$volume['bid_price'].'</br>';
        if($totalVoldis >= $TV){
        $price = $volume['bid_price'];
        $volume = $res['total'];
        break;
        }
        else{
        //echo "Price couldnt not be discovered";
        }
    }


/*echo "<br/> total initial vol".$TOTALAVLVOL."dfd";*/
echo $price."dfa".$volume;
//echo $allocationValue."<br>";

$all = $dbh->prepare('SELECT order_size,order_id,bid_price from bond where symbol_id=69 and status=0 and bid_price <= :price order by bid_price ASC');
$all->bindParam(':price',$price);
$all->execute();
$remaining=0;
$TOTALAVLVOLNEW=0;
foreach($all as $orders)
{
  if($orders['bid_price'] < $price)
  {
        $update = $dbh->prepare('UPDATE bond set allocated_size=order_size,price_discovered=:price,buy_vol=buy_vol-order_size,status=1 where order_id=:orid ');
        $update->bindParam(':price',$price);
        $update->bindParam(':orid',$orders['order_id']);
        if($update->execute()){
          echo'updated. price dis'.$price;
        }
        $TOTALAVLVOL = $TOTALAVLVOL - $orders['order_size'];
        $volume = $volume - $orders['order_size'];
   }
   else if($orders['bid_price'] == $price){
        $proratebase = $volume;
        if($remaining==1){
          $TOTALAVLVOLNEW=$TOTALAVLVOLNEW;
        }else{
          $TOTALAVLVOLNEW=$TOTALAVLVOL;
        }
        

        $all = $dbh->prepare('SELECT count(*) as cnt from bond where symbol_id=69 and bid_price=:price and status=0');
        $all->bindParam(':price',$price);
        $all->execute();
        $data = $all->fetch();
        if($data['cnt'] > 1){
             $prorata = floor(($orders['order_size']*$TOTALAVLVOL)/$proratebase);
             //echo $prorata;
             $update = $dbh->prepare('UPDATE bond set allocated_size=:prorata,price_discovered=:price,buy_vol=buy_vol-:prorata where order_id=:orid ');
              $update->bindParam(':price',$price);
              $update->bindParam(':prorata',$prorata);
              $update->bindParam(':orid',$orders['order_id']);
              if($update->execute()){
                echo '<br>--'.$orders['order_size'].'--pro->'.$prorata.'updated. price dis----'.$proratebase;
              }
              
              $TOTALAVLVOLNEW = $TOTALAVLVOLNEW - $prorata;
              echo '<br> ('.$TOTALAVLVOLNEW.' )--';
               $remaining=1;
        }else
        {

             $update = $dbh->prepare('UPDATE bond set allocated_size=:remaining,price_discovered=:price,buy_vol=buy_vol-:remaining where order_id=:orid ');
              $update->bindParam(':price',$price);
              $update->bindParam(':remaining',$TOTALAVLVOL);
              $update->bindParam(':orid',$orders['order_id']);
              if($update->execute()){
                echo'updated. price dis'.$price;
              }
              $TOTALAVLVOLNEW = $TOTALAVLVOLNEW - $TOTALAVLVOLNEW;

        }
   }

   else{

   }
}
echo 'polla-'.$TOTALAVLVOLNEW;
 //fractions remaining

if($TOTALAVLVOLNEW > 0){

        $overall = $dbh->prepare('SELECT * from bond where symbol_id=69 and status=0 and bid_price=:price order by order_size DESC');
        $overall->bindParam(':price',$price);
        $overall->execute();
        foreach($overall as $alloc)
        {
            if($TOTALAVLVOLNEW > 0){
            $update = $dbh->prepare('UPDATE bond set allocated_size=allocated_size+1,buy_vol=buy_vol-1,status=1 where order_id=:orid ');
            $update->bindParam(':orid',$alloc['order_id']);

            $update->execute();
            
            }
            $TOTALAVLVOLNEW = $TOTALAVLVOLNEW-1;
        }

               $status = $dbh->prepare('UPDATE bond set status=0 where symbol_id=69 and status=0  and symbol_id=67');
               $status->execute();
               
    }

}
?>