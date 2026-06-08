<?php 
  include('connection/db1.php');
  
  $totalAvailable1=10000;

  $orders1 = $dbh1->prepare("SELECT sum(order_size) orders from rights_issue WHERE type='SA' and status=0 and symbol_id=18");
  $orders1->execute();
  $ord1 = $orders1->fetch();
  $TOTALAVLVOL1=$totalAvailable1 - $ord1['orders'];
  $TV1=$TOTALAVLVOL1;

 // echo "<b>TOTAL AVAILABLE SHARES FOR AUCTION&nbsp&nbsp&nbsp".number_format($TV,2)."</b><br>";

  $hip1 = $dbh1->prepare('SELECT max(bid_price) as hp from rights_issue where type="SA" and status=0 and symbol_id=18');
  $hip1->execute();
  $hip_data1 = $hip1->fetch();

  //echo "<b>Highest Bid &nbsp&nbsp&nbsp".number_format($hip_data['hp'],2)."</b>";

   ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb" style="font-size: 11px;">
      <li class="breadcrumb-item active" aria-current="page">
       RICB
      </li>
      <li  class="breadcrumb-item active" aria-current="page">
        <?php echo "<b>Available for Auction&nbsp&nbsp&nbsp".number_format($totalAvailable1)."</b><br>"; ?>
      </li>
      <li class="breadcrumb-item active" aria-current="page">
        <?php echo"<b>Highest Bid &nbsp&nbsp&nbsp Nu. ".number_format($hip_data1['hp'], 2)."</b>"; ?>
      </li>
    </ol>
  </nav>
  <?php 
    echo"
    <div class='table-responsive-sm'>
     <table class='table table-sm table-hover' style='font-size: 12px;'>
      <thead>
        <tr>
          <td>BID VOL</td>
          <td>PRICE</td>
          <td>TOTAL BID</td>
          <td>TRADABLE VOL</td>
        </tr>
      </thead>";

      $ords1 = $dbh1->prepare("SELECT sum(order_size) orders from rights_issue where type='SA' and status=0 and symbol_id=18");
      $ords1->execute();
      $ord1 = $ords1->fetch();
      $TOTALORDERS1 = $ord1['orders'];
      
      if($TOTALORDERS1 < $totalAvailable1)
      {                       
         $save1 = $dbh1->prepare("SELECT * from rights_issue where type='SA' and status=0 and symbol_id=18 group by bid_price order by bid_price DESC");
      $save1->execute();
      $totalbid1 = 0;
      $i=0;
      foreach($save1 as $price1){
        
       $save1 = $dbh1->prepare('SELECT  sum(order_size) as buy_vol_total from rights_issue where type="SA" and bid_price=:p and status=0 and symbol_id=18');
       $save1->bindParam(':p', $price1['bid_price']);
       $save1->execute();
       $row1 = $save1->fetch();
        if($i==0)
       {
           $totalbid1=$row1['buy_vol_total'];
           echo"<tr>";                           
           echo"<td style=' color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price1['bid_price']."</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid1)."</b></td>";
           echo"<td style=' color: white;'><b><i></i></b></td>";                        
           echo"</tr>"; 
       }
       else
       {
        $totalbid1=$row1['buy_vol_total']+$totalbid1;
           echo"<tr>";                           
           echo"<td style=' color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price1['bid_price']."</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid1)."</b></td>";
           echo"<td style=' color: white;'><b><i></i></b></td>";
           echo"</tr>"; 
       }
       $i++;
       }
       echo '<font style="font-weight: 900;""> </font>';
       echo"</table>"; 
      }
      else{
      $price1 = $dbh1->prepare('SELECT distinct(bid_price) from rights_issue where type="SA" and status=0 and symbol_id=18 order by bid_price DESC');
      $price1->execute();
      foreach($price1 as $volume1){
      $sum1 = $dbh1->prepare('SELECT sum(order_size) as total from rights_issue where type="SA" and bid_price >= :price and status=0 and symbol_id=18');
      $sum1->bindParam(':price',$volume1['bid_price']);
      $sum1->execute();
      $res1=$sum1->fetch();
      $totalVoldis1 = $res1['total']; 
      
      //if($totalVoldis >= $TOTALAVLVOL){
      
      if($totalVoldis1 >= $totalAvailable1){
      $priced1 = $volume1['bid_price'];
      break;
      }
      else{
      //echo "Price couldnt not be discovered";
      }
      
      }
      $save1 = $dbh1->prepare("SELECT * from rights_issue where type='SA' and status=0 and symbol_id=18 group by bid_price order by bid_price DESC");
      $save1->execute();
      $totalbid1 = 0;
      $i=0;
      foreach($save1 as $price1){
        
       $save1 = $dbh1->prepare('SELECT  sum(order_size) as buy_vol_total from rights_issue where type="SA" and bid_price=:p and status=0 and symbol_id=18');
       $save1->bindParam(':p', $price1['bid_price']);
       $save1->execute();
       $row1 = $save1->fetch();
        if($i==0)
       {
       $totalbid1=$row1['buy_vol_total'];
         if($priced1 <= $price1['bid_price'])
         {
           echo"<tr class='blink'>";
           echo"<td style=' color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
           if($priced1 == $price1['bid_price'])
           {
            echo"<td style=' color: red; background-color:black; '><b><i>".$price1['bid_price']." - Eligible Price</i></b></td>";
           }
           else
           {
            echo"<td style=' color: black;'><b><i>".$price1['bid_price']."</i></b></td>";
           }
           echo"<td style=' color: black;'><b>".number_format($totalbid1)."</b></td>";
           if($priced1 == $price1['bid_price'])
           {
            echo"<td style=' color: black;'><b><i>".number_format($totalAvailable1)."</i></b></td>";
           }
           else
           {
            echo"<td style=' color: black;'><b><i></i></b></td>";
           }                           
           echo"</tr>"; 
         }
         else
         {
           echo"<tr>";                           
           echo"<td style=' color: DarkBlue;'><b>".number_format($row1['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price1['bid_price']."-Price not eligible.</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid1)."</b></td>";
           echo"<td style=' color: Lime;'><b><i></i></b></td>";
           echo"</tr>"; 
         }
       }
       else
       {
       $totalbid1=$row1['buy_vol_total']+$totalbid1;
        if($priced1 <= $price1['bid_price'])
        {
           echo"<tr class='blink' >";                           
           echo"<td style=' color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
           if($priced1 == $price1['bid_price'])
           {
            $dp1=$price1['bid_price'];
            echo"<td style=' color: red;  background-color:black;'><b><i>".$price1['bid_price']." - Eligible Price.</i></b></td>";
           }
           else
           {
            echo"<td style=' color: black;'><b><i>".$price1['bid_price']."</i></b></td>";
           }
           echo"<td style=' color: black;'><b>".number_format($totalbid1)."</b></td>";
           if($priced1 == $price1['bid_price'])
           {
            echo"<td style=' color: black;'><b><i>".number_format($totalAvailable1)."</i></b></td>";
           }
           else
           {
            echo"<td style=' color: black;'><b><i></i></b></td>";
           }
           echo"</tr>"; 
        }else
        {
           echo"<tr>";                           
           echo"<td style=' color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price1['bid_price']." -Price not eligible.</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid1)."</b></td>";
           echo"<td style=' color: Lime;'><b><i></i></b></td>";
           echo"</tr>"; 
        }
       }
       $i++;
       }                    
    }
    echo '<font style="font-weight: 900;""> </font>';
    echo"</table> </div>";


    /*$datapricediscovered = array();
    $datapricediscovered = $dp;

    $data['data'] = $datapricediscovered;
    header("HTTP/1.1 200 ok");
    echo json_encode($data);*/
?>