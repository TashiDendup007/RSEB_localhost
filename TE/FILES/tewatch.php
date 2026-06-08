<?php
include ('sessionStartFile_client.php');
    include ('../../CONNECTIONS/db.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php include('../NAV/components.php') ?>
</head>
<body class="hold-transition skin-black sidebar-mini">
  <?php include('../../GifLoader/gifComponent.php'); ?>
<!-- Site wrapper -->
<div class="wrapper">
<?php include('../NAV/navigation.php') ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header"><div class="modal fade" id="myModal" role="dialog"></div>
      <h1>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="bbo-landing.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Best Orders</a></li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <!-- /.box -->
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <!--startof code body -->
              <?php
function compare($symbolt)
{
             include ('../../CONNECTIONS/db.php');
             $symbolr=$symbolt;
             $val=0;$pric=0;
                    $save = $dbh->prepare('SELECT distinct price from orders where symbol_id=:sy and order_size > 0 order by price DESC  ');
                    $save->bindParam(':sy',$symbolr);
                    $save->execute();
                    foreach($save as $price) {
                      $sell=$dbh->prepare('SELECT sum(sell_vol)as totals from orders where symbol_id=:sy ');
                      $sell->bindParam(':sy',$symbolr);
                      $sell->execute();
                      $s=$sell->fetch();
                      $stotal=$s['totals'];
                      $buy=$dbh->prepare('SELECT sum(buy_vol)as totalb from orders where symbol_id=:sy  ');
                      $buy->bindParam(':sy',$symbolr);
                      $buy->execute();
                      $b=$buy->fetch();
                      $btotal=$b['totalb'];
                      $q2=$dbh->prepare('SELECT sum(buy_vol) as su from orders where price < :p and symbol_id=:sy');
                      $q2->bindParam(':sy',$symbolr);
                      $q2->bindParam(':p', $price['price']);
                      $q2->execute();
                      $se= $q2->fetch();
                      $total_buy=$se['su'];
                      $q2=$dbh->prepare('SELECT sum(sell_vol) as ss from orders where price > :p and symbol_id=:sy');
                      $q2->bindParam(':sy',$symbolr);
                      $q2->bindParam(':p', $price['price']);
                      $q2->execute();
                      $ss= $q2->fetch();
                      $total_sell=$ss['ss'];
                      $bt=$btotal-$total_buy;
                      $st=$stotal-$total_sell;
                      if($st<=$bt){
                        $t=$st;
                      }
                      elseif($bt<=$st){
                        $t=$bt;
                      }
                      $val_n=$t;
                      $pric_n=$price['price'];
                          if($val_n>$val){
                            $val=$val_n;
                            $pric=$pric_n;
                          }
                          elseif($val_n=$val){
                            if($pric>$pric_n){
                              $val=$val;
                              $pric=$pric;
                            }
                            elseif($pric<$pric_n){
                              $val=$val_n;
                              $pric=$pric_n;
                            }
                            elseif($pric=$pric_n){
                              $val=$val_n;
                              $pric=$pric_n;
                            }
                          }
                          elseif($val_n<$val){
                            $val=$val;
                            $pric=$pric;
                          }
                    }return $pric;
 }
                $save = $dbh->prepare('SELECT distinct a.symbol_id , b.symbol, b.symbol_id from orders a,symbol b where a.symbol_id=b.symbol_id ');
                $save->execute();
                 foreach($save as $symbol){
                $total = $dbh->prepare('SELECT
                sum(buy_vol) as buy_vol_total, sum(sell_vol) as sell_vol_total from orders where symbol=:sy ');
                $total->bindParam(':sy',$symbol['symbol']);
                $total->execute();
                $res= $total->fetch();
                      echo" <table class='table' style='background-color:#0d3c55; color: white;'>
                      <td ><b>TOTAL BID</b></td>
                      <td ><b>BID VOL</b></td>
                      <td ><b>PRICE</b></td>
                      <td ><b>OFFER VOL</b></td>
                      <td ><b>TOTAL OFFER</b></td>
                      <td ><b>TRADABLE VOL.</b></td>";
                    $save = $dbh->prepare('SELECT distinct price from orders where symbol_id=:sy and order_size > 0 order by price DESC');
                    $save->bindParam(':sy',$symbol['symbol_id']);
                    $save->execute();
                    foreach($save as $price){
                      $sell=$dbh->prepare('SELECT sum(sell_vol)as total from orders where symbol_id=:sy ');
                      $sell->bindParam(':sy',$symbol['symbol_id']);
                      $sell->execute();
                      $s=$sell->fetch();
                      $stotal=$s['total'];
                      $buy=$dbh->prepare('SELECT sum(buy_vol)as total from orders where symbol_id=:sy  ');
                      $buy->bindParam(':sy',$symbol['symbol_id']);
                      $buy->execute();
                      $b=$buy->fetch();
                      $btotal=$b['total'];
                      $q2=$dbh->prepare('SELECT sum(buy_vol) as su,price from orders where price < :p and symbol_id=:sy');
                      $q2->bindParam(':sy',$symbol['symbol_id']);
                      $q2->bindParam(':p', $price['price']);
                      $q2->execute();
                      $se= $q2->fetch();
                      $total_buy=$se['su'];
                      $q2=$dbh->prepare('SELECT sum(sell_vol) as ss,price from orders where price > :p and symbol_id=:sy');
                      $q2->bindParam(':sy',$symbol['symbol_id']);
                      $q2->bindParam(':p', $price['price']);
                      $q2->execute();
                      $ss= $q2->fetch();
                      $total_sell=$ss['ss'];
                      $bt=$btotal-$total_buy;
                      $st=$stotal-$total_sell;
                      if($st<=$bt){
                        $t=$st;
                      }
                      elseif($bt<=$st){
                        $t=$bt;
                      }
                      $symbolt=$symbol['symbol_id'];
                      $symbolname=$symbol['symbol'];
                      $pric= compare($symbolt);
                      if($price['price']==$pric){
                     $save = $dbh->prepare(' SELECT  sum(buy_vol) as buy_vol_total from orders where side="B" and price=:p and symbol_id=:a');
                     $save->bindParam(':a', $symbol['symbol_id']);
                     $save->bindParam(':p', $price['price']);
                     $save->execute();
                     $row = $save->fetch();
                     echo"<tr class='blink' style=' background-color: #c8c8c8 ;'>";
                     echo"<td style=' color: black;'><b>".$bt."</b></td>";
                     echo"<td style=' color: black;'><b>".$row['buy_vol_total']."</td>";
                     echo"<td style=' color: black;' ><b><i><p >".$price['price']."</p></i></b></td>";

                     $save = $dbh->prepare(' SELECT sum(sell_vol) as sell_vol_total from orders where side="S" and price=:p and symbol_id=:a');
                     $save->bindParam(':a', $symbol['symbol_id']);
                     $save->bindParam(':p', $price['price']);
                     $save->execute();
                        while( $row1= $save->fetch()){
                         echo"<td style=' color: black;'><b>".$row1['sell_vol_total']."</td>";
                         echo"<td style=' color: black;'><b>".$st."</b></td>";
                         echo"<td style=' color: black;'><b>".$t."</b></td>";
                       } echo'</tr>';
                     }
                     else{
                     $save = $dbh->prepare('SELECT  sum(buy_vol) as buy_vol_total from orders where side="B" and price=:p and symbol_id=:a');
                     $save->bindParam(':a', $symbol['symbol_id']);
                     $save->bindParam(':p', $price['price']);
                     $save->execute();
                     $row = $save->fetch();
                     echo"<tr >";
                         echo"<td style=' color: white;'><b>".$bt."</b></td>";
                         if($price['price']>$pric && $row['buy_vol_total'] >0){
                         echo"<td style=' color: LightSkyBlue;'>".$row['buy_vol_total']."</td>";
                         }
                         else{
                          echo"<td style=' color: LightSkyBlue;'><b>".$row['buy_vol_total']."</td>";
                         }
                     echo"<td style=' color: Lime;'><b><i>".$price['price']."</i></b></td>";
                     $save = $dbh->prepare('SELECT sum(sell_vol) as sell_vol_total from orders where side="S" and price=:p and symbol_id=:a');
                     $save->bindParam(':a', $symbol['symbol_id']);
                     $save->bindParam(':p', $price['price']);
                     $save->execute();
                        while( $row1= $save->fetch()){
                         if($price['price']<$pric && $row1['sell_vol_total'] > 0 ){
                        echo"<td  style=' color: black;  background-color: #c8c8c8  ;'>".$row1['sell_vol_total']."</td>";
                         }
                         else{
                          echo"<td style=' color: Magenta; '><b>".$row1['sell_vol_total']."</td>";
                         }
                         echo"<td style=' color: white; '><b>".$st."</b></td>";
                         echo"<td style=' color: white;'><b>".$t."</b></td>";
                       }echo "</tr>";
                     }
                     }
                    echo '<font style="font-weight: 900;""> '.$symbolname.'</font>';
                    echo"</table>";
 }
?>
              <!--endof code body-->
            </div>
          </div>
      </div>
    </section>
  </div>
  <!-- /.content-wrapper -->
<?php include('../NAV/footer.php') ?>
</body>
</html>
