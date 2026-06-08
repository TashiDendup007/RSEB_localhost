<!doctype html>
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'CONNECTIONS/db.php';

// Verify database connection
if (!$dbh) {
    die("Could not connect to database");
}
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Royal Securities Exchange of Bhutan - Stock Ticker</title>
    <style>
      body {
        background-color: #fff;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        height: 100vh;
        display: flex;
        flex-direction: column;
      }
      .header {
        text-align: center;
        padding: 20px 0;
        width: 100%;
        background-color: #fff;
      }
      .logo-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
      }
      .logo {
        height: 60px;
        margin-right: 15px;
      }
      .title {
        color: black;
        font-size: 28px;
        font-weight: bold;
      }
      .main-content {
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* Changed from center to move up */
        align-items: center;
        flex-grow: 1;
        width: 100%;
        padding-top: 5%; /* Added to push content down from header */
      }
      .ticker-container {
        width: 100%;
        overflow: hidden;
        background-color: #fff;
        padding: 20px 0;
        margin-top: 5vh; /* Added to position slightly above center */
      }
      .ticker {
        display: inline-block;
        white-space: nowrap;
        padding-left: 100%;
        animation: ticker-scroll 120s linear infinite;
      }
      .stock {
        display: inline-block;
        margin: 0 40px;
        color: black;
      }
      .symbol {
        font-weight: bold;
        font-size: 32px;
      }
      .price {
        font-size: 32px;
        margin-left: 10px;
      }
      .up {
        color: #3CFC0D;
      }
      .down {
        color: #F11E0D;
      }
      .unchanged {
        color: black;
      }
      @keyframes ticker-scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
      }
    </style>
  </head>
  <body>
    <div class="header">
      <div class="logo-container">
        <img src="/RSEB/img/rseb_logo2.png" alt="RSEB Logo" class="logo">
        <div class="title">Royal Securities Exchange of Bhutan</div>
      </div>
    </div>

    <div class="main-content">
      <div class="ticker-container">
        <div class="ticker">
          <?php
          try {
              $query = "SELECT s.symbol_id, s.symbol, SUBSTRING(mp.date,1,10) as date, 
                       mp.market_price, (mp.market_price - mp.ex_market_price) as diff 
                       FROM market_price mp
                       LEFT JOIN symbol s ON mp.symbol_id = s.symbol_id
                       WHERE s.security_type = 'OS'
                       AND s.status = 1 AND s.trsstatus = 1 
                       ORDER BY symbol ASC";
              
              $stmt = $dbh->query($query);
              
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $symbol = htmlspecialchars($row['symbol']);
                  $price = number_format($row['market_price'], 2);
                  $diff = $row['diff'];
                  
                  if ($diff > 0) {
                      $class = 'up';
                      $arrow = '▲';
                  } elseif ($diff < 0) {
                      $class = 'down';
                      $arrow = '▼';
                  } else {
                      $class = 'unchanged';
                      $arrow = '';
                  }
                  
                  // Highlight if traded today
                  $date = latestTrade($row['symbol_id']);
                  $highlight = ($date == date("Y-m-d")) ? 'border: 2px solid yellow; border-radius: 5px; padding: 5px;' : '';
                  
                  echo '<div class="stock" style="'.$highlight.'">';
                  echo '<span class="symbol">'.$symbol.'</span>';
                  echo '<span class="price '.$class.'">'.$price.' '.$arrow.abs($diff).'</span>';
                  echo '</div>';
              }
          } catch (PDOException $e) {
              die("Query failed: " . $e->getMessage());
          }
          
          function latestTrade($sym_id) {
              global $dbh;
              try {
                  $stmt = $dbh->prepare("SELECT SUBSTRING(MAX(e.order_date),1,10) as dat 
                                        FROM executed_orders e 
                                        WHERE e.side='S' AND e.symbol_id=?");
                  $stmt->execute([$sym_id]);
                  $row = $stmt->fetch(PDO::FETCH_ASSOC);
                  return $row['dat'];
              } catch (PDOException $e) {
                  return null;
              }
          }
          ?>
        </div>
      </div>
    </div>

    <script>
      // Simple page refresh every 5 minutes
      setTimeout(function() {
        location.reload();
      }, 300000);
    </script>
  </body>
</html>