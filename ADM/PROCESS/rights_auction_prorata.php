<?php
die();
include ('../../CONNECTIONS/db.php');

$totalVol = $dbh->prepare("SELECT sum(ribon_volume) AS total FROM spot_date_holding WHERE announcement_type = 1 and status = 0");
$totalVol->execute();
$vol = $totalVol->fetch();

$orders = $dbh->prepare("SELECT sum(order_size) AS orders FROM rights_issue WHERE type IN ('S', 'R') AND status = 0");
$orders->execute();
$ord = $orders->fetch();

$TOTALAVLVOL = $vol['total'] + 6;
$TOTALAVLVOL = $TOTALAVLVOL - $ord['orders'];

$ords = $dbh->prepare("SELECT sum(order_size) orders from rights_issue where type='B' and status=0");
$ords->execute();
$ord = $ords->fetch();
$TOTALORDERS = $ord['orders'];

$TV=$TOTALAVLVOL;
if($TOTALORDERS < $TV)
{
	$price = $dbh->prepare('SELECT bid_price from rights_issue where type="B" order by bid_price ASC limit 1');
	$price->execute();
	$pr=$price->fetch();

	$all = $dbh->prepare('SELECT order_size,order_id from rights_issue where type="B" and bid_price >= :price order by bid_price DESC');
	$all->bindParam(':price',$pr['bid_price']);
	$all->execute();
	$n=0;
	foreach($all as $orders)
	{
		$update = $dbh->prepare('UPDATE rights_issue set allocated_size=:al,price_discovered=:price where order_id=:orid ');
		$update->bindParam(':al',$orders['order_size']);
		$update->bindParam(':price',$pr['bid_price']);
		$update->bindParam(':orid',$orders['order_id']);
		$update->execute();

		$n += $orders['order_size'];
	}
	echo $n;

}
else{

$price = $dbh->prepare('SELECT distinct(bid_price) from rights_issue where type="B" and status=0 order by bid_price DESC');
$price->execute();
foreach($price as $volume){
$sum = $dbh->prepare('SELECT sum(order_size)  total from rights_issue where type="B" and status=0 and bid_price >= :price');
$sum->bindParam(':price',$volume['bid_price']);
$sum->execute();
$res=$sum->fetch();
$totalVoldis = $res['total']; 
if($totalVoldis >= $TV){
$price = $volume['bid_price'];
$volume = $res['total'];
break;
}
else{
echo "Price couldnt not be discovered";
}
}


/*echo "<br/> total initial vol".$TOTALAVLVOL."dfd";*/
/*echo $price."dfa".$volume;
echo $allocationValue."<br>";*/

$allocationValue = $TV/$volume;

$all = $dbh->prepare('SELECT order_size,order_id from rights_issue where type="B" and status=0 and bid_price >= :price order by bid_price DESC');
$all->bindParam(':price',$price);
$all->execute();
$n=0;
foreach($all as $orders)
{
	$allocation = $allocationValue*$orders['order_size'];
	$al = floor($allocation);
	$update = $dbh->prepare('UPDATE rights_issue set allocated_size=:al,price_discovered=:price where order_id=:orid ');
	$update->bindParam(':al',$al);
	$update->bindParam(':price',$price);
	$update->bindParam(':orid',$orders['order_id']);
	$update->execute();

	$n += $al;
}
$avlvol = $TV-$n;

for($x=$avlvol;$x>0;)
{
	if($avlvol > 0)
	{
		$sel = $dbh->prepare('SELECT count(*) as count  from rights_issue where type="B" and status=0 and bid_price >= :price');
		$sel->bindParam(':price',$price);
		$sel->execute();
		$res = $sel->fetch();
		$rowcount=$res['count'];
		if($rowcount >= $avlvol)
		{
			$select = $dbh->prepare('SELECT order_id from rights_issue where type="B" and status=0 and bid_price >= :price order by order_date ASC limit :st ');
			$select->bindParam(':price',$price);
			$select->bindParam(':st',$avlvol, PDO::PARAM_INT);
			$select->execute();
			foreach($select as $select1)
			{
				$update = $dbh->prepare('UPDATE rights_issue set allocated_size=allocated_size+1,price_discovered=:price where order_id=:orid');
				$update->bindParam(':price',$price);
				$update->bindParam(':orid',$select1['order_id']);
				$update->execute();
				$avlvol -= 1;
			}
		}
		else
		{
			$floor = round($avlvol/$rowcount);
			$select = $dbh->prepare('SELECT order_id from rights_issue where type="B" and status=0 and bid_price >= :price order by order_date ASC');
			$select->bindParam(':price',$price);
			$select->execute();
			foreach($select as $select1)
			{
				$update = $dbh->prepare('UPDATE rights_issue set allocated_size=allocated_size+:floor,price_discovered=:price where order_id=:orid');
				$update->bindParam(':floor',$select1['floor']);
				$update->bindParam(':price',$price);
				$update->bindParam(':orid',$select1['order_id']);
				$update->execute();
				$avlvol -= $floor;
			}
		}
	}
	else
	{
		break;
	}
}
}
?>