<?php 
  include('connection/db1.php');
  
  $totalAvailable=100;

  $orders = $dbh1->prepare("SELECT sum(order_size) orders from rights_issue WHERE type='SA' and status=0 and symbol_id=5");
  $orders->execute();
  $ord = $orders->fetch();
  $TOTALAVLVOL=$totalAvailable - $ord['orders'];
  $TV=$TOTALAVLVOL;

 //echo"<b>TOTAL AVAILABLE SHARES FOR AUCTION&nbsp&nbsp&nbsp".number_format($TV,2)."</b><br>";

  $hip = $dbh1->prepare('SELECT max(bid_price) as hp from rights_issue where type="SA" and status=0 and symbol_id=5');
  $hip->execute();
  $hip_data = $hip->fetch();

  //echo "<b>Highest Bid &nbsp&nbsp&nbsp".number_format($hip_data['hp'],2)."</b>";

   ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb" style="font-size: 11px;">
      <li class="breadcrumb-item active" aria-current="page">
       BNBL
      </li>
      <li  class="breadcrumb-item active" aria-current="page">
        <?php echo"<b>Available for Auction&nbsp&nbsp&nbsp".number_format($totalAvailable)."</b><br>"; ?>
      </li>
      <li class="breadcrumb-item active" aria-current="page">
        <?php echo "<b>Highest Bid &nbsp&nbsp&nbsp Nu. ".number_format($hip_data['hp'], 2)."</b>"; ?>
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

      $ords = $dbh1->prepare("SELECT sum(order_size) orders from rights_issue where type='SA' and status=0 and symbol_id=5");
      $ords->execute();
      $ord = $ords->fetch();
      $TOTALORDERS = $ord['orders'];
      
      if($TOTALORDERS < $totalAvailable)
      {                       
      $save = $dbh1->prepare("SELECT * from rights_issue where type='SA' and status=0 and symbol_id=5 group by bid_price order by bid_price DESC");
      $save->execute();
      $totalbid = 0;
      $i=0;
      foreach($save as $price){
        
       $save = $dbh1->prepare('SELECT  sum(order_size) as buy_vol_total from rights_issue where type="SA" and bid_price=:p and status=0 and symbol_id=5');
       $save->bindParam(':p', $price['bid_price']);
       $save->execute();
       $row = $save->fetch();
        if($i==0)
       {
           $totalbid=$row['buy_vol_total'];
           echo"<tr>";                           
           echo"<td style=' color: white;'><b>".number_format($row['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price['bid_price']."</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid)."</b></td>";
           echo"<td style=' color: white;'><b><i></i></b></td>";                        
           echo"</tr>"; 
       }
       else
       {
        $totalbid=$row['buy_vol_total']+$totalbid;
           echo"<tr>";                           
           echo"<td style=' color: white;'><b>".number_format($row['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price['bid_price']."</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid)."</b></td>";
           echo"<td style=' color: white;'><b><i></i></b></td>";
           echo"</tr>"; 
       }
       $i++;
       }
       echo '<font style="font-weight: 900;""> </font>';
       echo"</table>"; 
      }
      else{
        $price = $dbh1->prepare('SELECT distinct(bid_price) from rights_issue where type="SA" and status=0 and symbol_id=5 order by bid_price DESC');
        $price->execute();
        foreach($price as $volume){
        $sum = $dbh1->prepare('SELECT sum(order_size) as total from rights_issue where type="SA" and bid_price >= :price and status=0 and symbol_id=5');
        $sum->bindParam(':price',$volume['bid_price']);
        $sum->execute();
        $res=$sum->fetch();
        $totalVoldis = $res['total']; 
        
        //if($totalVoldis >= $TOTALAVLVOL){
        
        if($totalVoldis >= $totalAvailable){
          $priced = $volume['bid_price'];
          break;
        }
        else{
        //echo "Price couldnt not be discovered";
        }
      
      }
      $save = $dbh1->prepare("SELECT * from rights_issue where type='SA' and status=0 and symbol_id=5 group by bid_price order by bid_price DESC");
      $save->execute();
      $totalbid = 0;
      $i=0;
      foreach($save as $price){
        
       $save = $dbh1->prepare('SELECT  sum(order_size) as buy_vol_total from rights_issue where type="SA" and bid_price=:p and status=0 and symbol_id=5');
       $save->bindParam(':p', $price['bid_price']);
       $save->execute();
       $row = $save->fetch();
        if($i==0)
       {
       $totalbid=$row['buy_vol_total'];
         if($priced <= $price['bid_price'])
         {
           echo"<tr class='blink' >";
           echo"<td style=' color: white;'><b>".number_format($row['buy_vol_total'])."</td>";
           if($priced == $price['bid_price'])
           {
            echo"<td style=' color: red; background-color:black; '><b><i>".$price['bid_price']." - Eligible Price</i></b></td>";
           }
           else
           {
            echo"<td style=' color: white;'><b><i>".$price['bid_price']."</i></b></td>";
           }
           echo"<td style=' color: white;'><b>".number_format($totalbid)."</b></td>";
           if($priced == $price['bid_price'])
           {
            echo"<td style=' color: white;'><b><i>".number_format($totalAvailable)."</i></b></td>";
           }
           else
           {
            echo"<td style=' color: white;'><b><i></i></b></td>";
           }                           
           echo"</tr>"; 
         }
         else
         {
           echo"<tr>";                           
           echo"<td style=' color: DarkBlue;'><b>".number_format($row['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price['bid_price']."-Price not eligible.</i></b></td>";
           echo"<td style=' color: black;'><b>".number_format($totalbid)."</b></td>";
           echo"<td style=' color: Lime;'><b><i></i></b></td>";
           echo"</tr>"; 
         }
       }
       else
       {
       $totalbid=$row['buy_vol_total']+$totalbid;
        if($priced <= $price['bid_price'])
        {
           echo"<tr class='blink'>";                           
           echo"<td style=' color: white;'><b>".number_format($row['buy_vol_total'])."</td>";
           if($priced == $price['bid_price'])
           {
            $dp=$price['bid_price'];
            echo"<td style=' color: red; background-color:black;'><b><i>".$price['bid_price']." - EligiblePrice.</i></b></td>";
           }
           else
           {
            echo"<td style=' color: white;'><b><i>".$price['bid_price']."</i></b></td>";
           }
           echo"<td style=' color: white;'><b>".number_format($totalbid)."</b></td>";
           if($priced == $price['bid_price'])
           {
            echo"<td style=' color: white;'><b><i>".number_format($totalAvailable)."</i></b></td>";
           }
           else
           {
            echo"<td style=' color: white;'><b><i></i></b></td>";
           }
           echo"</tr>"; 
        }else
        {
           echo"<tr>";                           
           echo"<td style=' color: white;'><b>".number_format($row['buy_vol_total'])."</td>";
           echo"<td style=' color: Lime;'><b><i>".$price['bid_price']." -Price not eligible.</i></b></td>";
           echo"<td style=' color: white;'><b>".number_format($totalbid)."</b></td>";
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