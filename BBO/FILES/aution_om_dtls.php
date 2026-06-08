<?php 
  date_default_timezone_set("Asia/Thimphu");
  include('../../CONNECTIONS/db.php');
  header('Access-Control-Allow-Origin: *');
  
  $dateselect = date("Y-m-d H:i:s");

  $stmt = $dbh->prepare("SELECT 
      a.symbol_id, a.symbol, a.offer_volume, a.offer_volume, a.start_price, a.max_price, a.auction_date, a.end_date,
      DATE_FORMAT(a.auction_date, '%W %M %e, %Y %h:%i %p') AS start_at_format,
      DATE_FORMAT(a.end_date, '%W %M %e, %Y %h:%i %p') AS end_at_format
      FROM share_auctions a 
      WHERE a.status = 'Y'
      ORDER BY a.id DESC LIMIT 1
  ");
  $stmt->execute();
  $result = $stmt->fetch();
  if ($stmt->rowcount() < 1) {
    echo"<h3>There are no active Share Auction</h3>"; 
    die();
  }

  if ($result['auction_date'] > $dateselect) {
    echo"<h3> The Share Auction Market will be available on : <b>".$result['start_at_format']."</b></h3>";
    die();
  }


  $totalAvailable1 = $result['offer_volume'];
  $auction_symbol_id = $result['symbol_id'];
  $auction_symbol = $result['symbol'];

  $getOrdersAndHighestPrice = $dbh->prepare("SELECT SUM(order_size) AS orders, MAX(bid_price) AS hp 
    FROM rights_issue WHERE type = 'SA' AND status = 0 AND symbol_id = ?
  ");
  $getOrdersAndHighestPrice->bindParam(1, $auction_symbol_id);
  $getOrdersAndHighestPrice->execute();
  $data = $getOrdersAndHighestPrice->fetch();

  $total_bid_orders = $data['orders'];
  $max_bid_price = $data['hp'];
?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb" style="font-size: 11px;">
    <li class="breadcrumb-item active" aria-current="page"><?php echo $auction_symbol; ?></li>
    <li  class="breadcrumb-item active" aria-current="page">
      <?php echo "<b>Available for Auction : &nbsp; &nbsp; &nbsp;".number_format($totalAvailable1)."</b><br>"; ?>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
      <?php echo"<b>Highest Bid : &nbsp;&nbsp;&nbsp; Nu. ".number_format($max_bid_price, 2)."</b>"; ?>
    </li>
  </ol>
</nav>
<?php 
  echo"
  <div class='table-responsive-sm'>
    <table class='table table-sm table-hover' style='font-size: 12px;'>
      <thead>
        <tr>
          <th>BID VOL</th>
          <th>PRICE</th>
          <th>TOTAL BID</th>
          <th>TRADABLE VOL</th>
        </tr>
      </thead>
      <tbody>";
      $ords1 = $dbh->prepare("SELECT SUM(order_size) AS orders FROM rights_issue WHERE type='SA' AND status = 0 AND symbol_id = ?");
      $ords1->bindparam(1, $auction_symbol_id);
      $ords1->execute();
      $ord1 = $ords1->fetch();
      $TOTALORDERS1 = $ord1['orders'];
      
      if($TOTALORDERS1 < $totalAvailable1) {
        $save1 = $dbh->prepare("SELECT bid_price FROM rights_issue 
          WHERE type = 'SA' AND status = 0 AND symbol_id = ?
          GROUP BY bid_price ORDER BY bid_price DESC 
          -- Limit 100
        ");
        $save1->bindparam(1, $auction_symbol_id);
        $save1->execute();
        $totalbid1 = 0;
        $i = 0;
        foreach ($save1 as $price1) {
          $save1 = $dbh->prepare('SELECT SUM(order_size) AS buy_vol_total FROM rights_issue WHERE type="SA" AND bid_price = ? AND status = 0 AND symbol_id = ?');
          $save1->bindParam(1, $price1['bid_price']);
          $save1->bindParam(2, $auction_symbol_id);
          $save1->execute();
          $row1 = $save1->fetch();
          if ($i == 0) {
            $totalbid1 = $row1['buy_vol_total'];
            echo"
            <tr>
              <td style='color: white;'><b>".number_format($row1['buy_vol_total'])."</td>
              <td style='color: Lime;'><b><i>".$price1['bid_price']."</i></b></td>
              <td style='color: white;'><b>".number_format($totalbid1)."</b></td>
              <td style='color: white;'><b><i></i></b></td>
            </tr>"; 
          } else {
            $totalbid1 = $row1['buy_vol_total'] + $totalbid1;
           echo"
            <tr>
              <td style='color: white;'><b>".number_format($row1['buy_vol_total'])."</td>
              <td style='color: Lime;'><b><i>".$price1['bid_price']."</i></b></td>
              <td style='color: white;'><b>".number_format($totalbid1)."</b></td>
              <td style='color: white;'><b><i></i></b></td>
            </tr>"; 
          }
          $i++;
        }
        echo'
          <font style="font-weight: 900;"></font>
        <tbody>
       </table>'; 
      } else {
        $price1 = $dbh->prepare('SELECT DISTINCT bid_price FROM rights_issue WHERE type = "SA" AND status = 0 and symbol_id = ? 
          order by bid_price DESC -- Limit 100
        ');
        $price1->bindParam(1, $auction_symbol_id);
        $price1->execute();
        foreach ($price1 as $volume1) {
          $sum1 = $dbh->prepare('SELECT sum(order_size) as total FROM rights_issue WHERE type = "SA" and bid_price >= ? AND status = 0 AND symbol_id = ?');
          $sum1->bindParam(1, $volume1['bid_price']);
          $sum1->bindParam(2, $auction_symbol_id);
          $sum1->execute();
          $res1 = $sum1->fetch();
          $totalVoldis1 = $res1['total']; 
        
          if ($totalVoldis1 >= $totalAvailable1) {
            $priced1 = $volume1['bid_price'];
            break;
          }
        }

        $totalbid1 = 0;
        $i = 0;
        $save1 = $dbh->prepare("SELECT bid_price FROM rights_issue WHERE type = 'SA' AND status = 0 AND symbol_id = ?
          GROUP BY bid_price 
          ORDER BY bid_price DESC
        ");
        $save1->bindParam(1, $auction_symbol_id);
        $save1->execute();
        foreach ($save1 as $price1) {
          $save1 = $dbh->prepare('SELECT SUM(order_size) as buy_vol_total FROM rights_issue WHERE type = "SA" AND bid_price = ? AND status = 0 AND symbol_id = ?');
          $save1->bindParam(1, $price1['bid_price']);
          $save1->bindParam(2, $auction_symbol_id);
          $save1->execute();
          $row1 = $save1->fetch();
          
          if ($i == 0) {
            $totalbid1 = $row1['buy_vol_total'];
            if($priced1 <= $price1['bid_price']) {
              echo"
              <tr class='blink'>
                <td style='color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
                if($priced1 == $price1['bid_price']) {
                  echo"<td style='color: red; background-color:black;'><b><i>".$price1['bid_price']." - Eligible Price</i></b></td>";
                } else {
                  echo"<td style='color: black;'><b><i>".$price1['bid_price']."</i></b></td>";
                }
                echo"<td style='color: black;'><b>".number_format($totalbid1)."</b></td>";
                if($priced1 == $price1['bid_price']) {
                  echo"<td style='color: black;'><b><i>".number_format($totalAvailable1)."</i></b></td>";
                } else {
                  echo"<td style='color: black;'><b><i></i></b></td>";
                }
                echo"</tr>"; 
            } else {
              echo"
              <tr>
                <td style='color: DarkBlue;'><b>".number_format($row1['buy_vol_total'])."</td>
                <td style='color: Lime;'><b><i>".$price1['bid_price']."-Price not eligible.</i></b></td>
                <td style='color: white;'><b>".number_format($totalbid1)."</b></td>
                <td style='color: Lime;'><b><i></i></b></td>
              </tr>"; 
             }
          } else {
            $totalbid1 = $row1['buy_vol_total'] + $totalbid1;
            if($priced1 <= $price1['bid_price']) {
              echo"
              <tr class='blink'>
                <td style=' color: white;'><b>".number_format($row1['buy_vol_total'])."</td>";
                if($priced1 == $price1['bid_price']) {
                  $dp1 = $price1['bid_price'];
                  echo"<td style=' color: red;  background-color:black;'><b><i>".$price1['bid_price']." - Eligible Price.</i></b></td>";
                } else {
                  echo"<td style=' color: black;'><b><i>".$price1['bid_price']."</i></b></td>";
                }
                echo"<td style=' color: black;'><b>".number_format($totalbid1)."</b></td>";
                
                if($priced1 == $price1['bid_price']) {
                  echo"<td style=' color: black;'><b><i>".number_format($totalAvailable1)."</i></b></td>";
                } else {
                  echo"<td style=' color: black;'><b><i></i></b></td>";
                }
                echo"</tr>"; 
            } else {
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
      echo'
        <font style="font-weight: 900;"></font>
      <tbody>
    </table> 
  </div>';
?>