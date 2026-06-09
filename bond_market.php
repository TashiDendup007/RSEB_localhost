<?php
error_reporting(1);
// define database related variables
$database = 'cms2';
$host = '192.168.10.100';
$user = 'root';
$pass = 'MkmCsop@289';
$port='3306';
// try to connect to database
try
{
  $dbh = new PDO("mysql:dbname={$database};host={$host};port={3306}", $user, $pass);
}
catch(PDOException $e){
  echo $e->getMessage();
  echo "<h2> Hi, There seems to be an issue with the Application. Please contact RSEB.</h2>";
  die();
}

// ── Helper functions ─────────────────────────────────────────────────────────

/**
 * Returns volume traded (sell side) for a symbol on a given date.
 */
function volumeTraded(PDO $dbh, int $symId, string $date): int|string
{
    $stmt = $dbh->prepare(
        "SELECT COALESCE(SUM(lot_size_execute), 0) AS total
         FROM bond_executed_orders
         WHERE side = 'S'
           AND symbol_id = :sym_id
           AND DATE(order_date) = :date"
    );
    $stmt->execute([':sym_id' => $symId, ':date' => $date]);
    $total = (int) $stmt->fetchColumn();
    return $total > 0 ? $total : 'No Trade';
}

/**
 * Returns value traded (volume × avg price) for a symbol on a given date.
 */
function valueTraded(PDO $dbh, int $symId, string $date): float
{
    $stmt = $dbh->prepare(
        "SELECT SUM(lot_size_execute) AS vol,
                AVG(order_exe_price)   AS avg_price
         FROM bond_executed_orders
         WHERE side = 'S'
           AND symbol_id = :sym_id
           AND DATE(order_date) = :date"
    );
    $stmt->execute([':sym_id' => $symId, ':date' => $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($row['vol'] ?? 0) * ($row['avg_price'] ?? 0);
}

/**
 * Returns the most recent trade date for a symbol (YYYY-MM-DD).
 */
function latestTradeDate(PDO $dbh, int $symId): string
{
    $stmt = $dbh->prepare(
        "SELECT DATE(MAX(order_date)) AS latest
         FROM bond_executed_orders
         WHERE side = 'S'
           AND symbol_id = :sym_id"
    );
    $stmt->execute([':sym_id' => $symId]);
    return (string) $stmt->fetchColumn();
}

// ── Fetch all symbols + market prices in one query ────────────────────────────

$rows = $dbh->query(
    "SELECT s.symbol_id,
            s.symbol,
            mp.exec_price,
            mp.exec_price - mp.last_price AS diff
     FROM   bond_trade_prices mp
     LEFT JOIN symbol s ON mp.symbol_id = s.symbol_id
     WHERE  s.security_type IN ('GB', 'CB')
       AND  s.status       = 1
       AND  s.trsstatus    = 1
     ORDER  BY s.symbol ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
// $today = '2026-06-08';

// ── Build table rows ──────────────────────────────────────────────────────────

$tableRows = [];
foreach ($rows as $row) {
    $symId = (int) $row['symbol_id'];
    $diff  = (float) $row['diff'];

    // Row colour + change cell class
    if ($diff > 0) {
        $rowClass  = 'up';
        $chgClass  = 'arr-up';
        $chgPrefix = '+';
    } elseif ($diff < 0) {
        $rowClass  = 'dn';
        $chgClass  = 'arr-dn';
        $chgPrefix = '';
    } else {
        $rowClass  = 'flat';
        $chgClass  = '';
        $chgPrefix = '';
    }

    $latestDate = latestTradeDate($dbh, $symId);
    $isToday    = ($latestDate === $today);

    $vol   = volumeTraded($dbh, $symId, $latestDate);
    $value = valueTraded($dbh, $symId, $latestDate);

    $shortDate = $latestDate ? date('d-m', strtotime($latestDate)) : '-';
    $dateClass = $isToday ? ' class="today"' : '';

    $tableRows[] = sprintf(
        '<tr class="%s">'
        . '<td>%s</td>'
        . '<td%s>%s</td>'
        . '<td>%s</td>'
        . '<td class="%s">%s%s</td>'
        . '<td>%s</td>'
        . '<td>%s</td>'
        . '</tr>',
        $rowClass,
        htmlspecialchars($row['symbol']),
        $dateClass,
        htmlspecialchars($shortDate),
        htmlspecialchars((string) $row['exec_price']),
        $chgClass,
        $chgPrefix,
        htmlspecialchars((string) $diff),
        is_int($vol) ? number_format($vol) : htmlspecialchars($vol),
        number_format($value)
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'self';">
  <!-- Fixed 390px wide — matches a typical mobile screenshot width -->
  <meta name="viewport" content="width=390, initial-scale=1">
  <title>RSEB | Capital Market Solution</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      /*background: #111;*/
      width: 390px;          /* fixed — screenshot always same width */
      padding: 6px;
      font-family: Arial, sans-serif;
    }

    /* ── Header banner ── */
    .banner {
      background: #1a1a2e;
      border: 1px solid #333;
      border-radius: 4px 4px 0 0;
      padding: 5px 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1px;
    }
    .banner-title {
      color: #f0c040;
      font-size: 10px;
      font-weight: bold;
      letter-spacing: .5px;
    }
    .banner-date {
      color: #dddada;
      font-size: 8px;
    }

    /* ── Table ── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5px;
    }
    thead tr {
      background: #1a1a2e;
    }
    thead th {
      color: #f0c040;
      padding: 3px 4px;
      text-align: right;
      font-weight: bold;
      border: 1px solid #333;
      white-space: nowrap;
    }
    thead th:first-child { text-align: left; }

    tbody td {
      padding: 4px 4px;
      border: 1px solid rgba(255,255,255,0.08);
      text-align: right;
      white-space: nowrap;
      color: #fff;
    }
    tbody td:first-child { text-align: left; font-weight: bold; }

    /* Status row colours */
    tr.up   { background: #117911; }
    tr.dn   { background: #771313; }
    tr.flat { background: #2a2a2a; }

    /* Highlight today's trade date */
    td.today { background: rgba(255,255,255,0.15); color: #fff; }

    /* Up/down arrows — pure CSS, no SVG needed */
    .arr-up::before  { content: "▲"; color: #4cff4c; margin-right: 2px; }
    .arr-dn::before  { content: "▼"; color: #ff4c4c; margin-right: 2px; }

    /* ── Footer ── */
    .footer {
      background: #1a1a2e;
      border: 1px solid #333;
      border-radius: 0 0 4px 4px;
      margin-top: 1px;
      padding: 3px 8px;
      font-size: 7px;
      color: #a7a6a6;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="banner">
    <span class="banner-title">RSEB · Capital Market Solution</span>
    <span class="banner-date"><?= date('d M Y') ?></span>
  </div>

  <table>
    <thead>
      <tr>
        <th>Sym</th>
        <th>Last Trade</th>
        <th>Closing <br>Price</th>
        <th>Chg</th>
        <th>Vol</th>
        <th>Value</th>
      </tr>
    </thead>
    <tbody>
      <?= implode("\n", $tableRows) ?>
    </tbody>
  </table>

  <div class="footer">© RSEB Capital Market Solution &nbsp;|&nbsp; All rights reserved</div>

</body>
</html>
