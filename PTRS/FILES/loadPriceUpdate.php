<?php
  date_default_timezone_set("Asia/Thimphu");
  include('../FILES/session_file.php');
  include ('../../CONNECTIONS/db.php');
  
  if (!empty($_POST["getCurrentMarketPrice"])) {
    $id = $_POST['id'];

    $query=$dbh->prepare("SELECT m.market_price FROM market_price m WHERE m.symbol_id=:id ORDER BY m.date DESC LIMIT 1");
    $query->bindParam(':id',$id);
    $query->execute();
    $state=$query->fetch();
    //echo '<input type="text" name="curMarPrice" id="curMarPriceId" class="form-control" value="'.$state['market_price'].'" onClick="clearMessage();" readonly="true">';
    echo $state['market_price'];
  }
  elseif (!empty($_POST["getCurrentMarketPriceYear"])) {
    $id = $_POST['id'];

    $query=$dbh->prepare("SELECT m.market_price FROM market_price m WHERE m.symbol_id=:id ORDER BY m.date DESC LIMIT 1");
    $query->bindParam(':id',$id);
    $query->execute();
    $state=$query->fetch();
    echo '<input type="text" name="curMarPrice1" id="curMarPriceId1" class="form-control" value="'.$state['market_price'].'" onClick="clearMessage1();" readonly="true">';
  }
  //update new price of one month untraded
  elseif (!empty($_POST["updateMarketPrice"])) {
    $sysTime = date("Y-m-d H:i:s");
    $reason = $_POST['reason'];
    $symbolId = $_POST['symbolId'];
    $currentPrice = $_POST['currentPrice'];
    $newPrice = $_POST['newPrice'];
    $bonusPer = $_POST['bonusPer'];
    $subcPrice = $_POST['subcPrice'];
    $unit = $_POST['unit'];
    $rightsPerc = $_POST['rightsPerc'];
    $dvdPerShare = $_POST['dvdPerShare'];
    $bookVal = $_POST['bookVal'];

    //to get old market price and date
    $sql = $dbh->prepare("SELECT market_price, date FROM market_price p WHERE p.symbol_id = :syId");
    $sql->bindParam(':syId', $symbolId);
    $sql->execute();
    $result = $sql->fetch();
    $exPrice = $result['market_price'];
    $exDate = $result['date'];
    $message = '';
    try {
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->beginTransaction();

      $insertAudit = $dbh->prepare("INSERT INTO market_price_audit(id, symbol_id, market_price, date, ex_market_price, ex_date, reason, bonusPercentage, RightsPercentage, subcriptionPrice, unit, dividenPerShare, bookValue) 
        SELECT id, symbol_id, market_price, date, ex_market_price, ex_date, reason, bonusPercentage, RightsPercentage, subcriptionPrice, unit, dividenPerShare, bookValue FROM market_price WHERE symbol_id = :sId");
      $insertAudit->bindParam(':sId', $symbolId);
      $insertAudit->execute();

      $update = $dbh->prepare("UPDATE market_price m 
        SET m.market_price=:nPrice, date=:sysTime, ex_market_price=:exPrice, ex_date=:exDate, reason=:reas, bonusPercentage=:bonPer, RightsPercentage=:rigPerc, subcriptionPrice=:subPri, unit=:unit, dividenPerShare=:dvdPeSha, bookValue=:boVal 
        WHERE m.symbol_id=:id");
      $update->bindParam(':nPrice', $newPrice);
      $update->bindParam(':sysTime', $sysTime);
      $update->bindParam(':exPrice', $exPrice);
      $update->bindParam(':exDate', $exDate);
      $update->bindParam(':reas', $reason);
      $update->bindParam(':bonPer', $bonusPer);
      $update->bindParam(':rigPerc', $rightsPerc);
      $update->bindParam(':subPri', $subcPrice);
      $update->bindParam(':unit', $unit);
      $update->bindParam(':dvdPeSha', $dvdPerShare);
      $update->bindParam(':boVal', $bookVal);
      $update->bindParam(':id', $symbolId);
      $update->execute();

      $dbh->commit();

      $message = "success";
    } catch(PDOException $e) {
      $dbh->rollBack();
      $message = "fail";
    }
    $dbh = null;
    echo $message;
    exit();
  }
  elseif (!empty($_POST["getSymbols"])) {
    // load symbols 
    $id = $_POST['id'];
    $sql = "";

    if($id == 1 || $id == 2 || $id == 3) {
      $sql = "SELECT s.symbol_id, s.symbol, s.name 
        FROM symbol s WHERE s.security_type ='OS' AND s.status='1' AND s.trsstatus='1' 
        ORDER BY symbol ASC
      ";
    } elseif ($id == 4) {
      $sql = "SELECT t.symbol_id, m.symbol, m.name 
        FROM 
        (SELECT r.symbol_id, MAX(DATE(r.order_date)) Order_Date 
          FROM executed_orders r GROUP BY r.symbol_id ORDER BY r.symbol_id ASC
        ) t 
        LEFT JOIN symbol m ON t.symbol_id = m.symbol_id 
        LEFT JOIN market_price p ON t.symbol_id = p.symbol_id 
        WHERE DATE(t.Order_Date) < DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH)) 
        AND DATE(p.date) < DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH)) 
        AND m.security_type NOT IN ('GB', 'CP') AND m.status=1 AND m.trsstatus=1 GROUP BY t.symbol_id 
        UNION ALL
        SELECT s.symbol_id, s.symbol, s.name 
        FROM symbol s 
        join market_price mp on s.symbol_id=mp.symbol_id
        WHERE DATE(mp.date) < DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH)) AND s.symbol_id NOT IN (SELECT r.symbol_id FROM executed_orders r group by r.symbol_id) AND s.security_type ='OS' AND s.status=1 AND s.trsstatus=1 ORDER BY symbol ASC
      ";
    } else {
      $sql = "SELECT t.symbol_id, m.symbol, m.name 
        FROM 
        (SELECT r.symbol_id, MAX(DATE(r.order_date)) Order_Date 
          FROM executed_orders r GROUP BY r.symbol_id ORDER BY r.symbol_id ASC
        ) t 
        LEFT JOIN symbol m ON t.symbol_id = m.symbol_id 
        LEFT JOIN market_price p ON t.symbol_id = p.symbol_id 
        WHERE DATE(t.order_date) < DATE_SUB(NOW(), INTERVAL 1 YEAR) 
        AND m.security_type NOT IN ('GB', 'CP') AND m.status=1 AND m.trsstatus=1 GROUP BY t.symbol_id 
        UNION ALL
        SELECT s.symbol_id, s.symbol, s.name 
        FROM symbol s 
        JOIN market_price mp on s.symbol_id = mp.symbol_id 
        WHERE DATE(mp.date) < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND s.symbol_id NOT IN (SELECT r.symbol_id FROM executed_orders r group by r.symbol_id) AND s.security_type ='OS' AND s.status=1 AND s.trsstatus=1 ORDER BY symbol ASC";
    }
    $query= $dbh->prepare($sql);
    $query->execute();
    echo'
    <select name="symbol" id="symbolId" class="form-control" onclick="getCurMarketPrice(this.value);">
      <option value="0"> --Select-- </option>';
      while($res = $query->fetch()) {
        echo'<option value="'.$res['symbol_id'].'">'.$res['symbol'].'</option>';
      }
      echo'</select>';
  }
  elseif (isset($_POST['loadNewDivisor'])) {
    // to get market cap of all sybmol type OS and status = 1
    $getMakCap = $dbh->prepare("SELECT SUM((c.volume + c.pledge_volume + c.block_volume + c.pending_out_vol) * m.market_price) AS mak_cap
        FROM cds_holding c 
        JOIN symbol s on c.symbol_id = s.symbol_id 
        JOIN market_price m on c.symbol_id = m.symbol_id 
        WHERE s.status=1 and s.security_type = 'OS'
      ");
    $getMakCap->execute();
    $result = $getMakCap->fetch();
    $market_cap = $result['mak_cap'];

    // to get last index 
    $getIndex = $dbh->prepare("SELECT m.m_index
        FROM market_index m
        ORDER BY m.id DESC 
        LIMIT 1
    ");
    $getIndex->execute();
    $row = $getIndex->fetch();
    $old_index = $row['m_index'];

    // new divisor
    $new_divisor = $market_cap / $old_index;

    echo $new_divisor;
    exit();
  }
  else
  {  
  }
?>

