<?php
include('../../CONNECTIONS/db.php');

function market_price($symbolId) 
{
    global $dbh;
    $stmt = $dbh->prepare('SELECT market_price FROM market_price WHERE symbol_id = :symbol_id');
    $stmt->bindParam(':symbol_id', $symbolId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        return 0.0; // or any default value you prefer
    }
    return (float) $result['market_price'];
}

function circuit($cap_name)
{
    global $dbh;
    $pr = $dbh->prepare('SELECT margin FROM circuit_breaker WHERE name=:name');
    $pr->bindParam(':name', $cap_name, PDO::PARAM_STR);
    $pr->execute();
    if (!$pr) {
        throw new Exception("Error querying database.");
    }
    $value = $pr->fetch(PDO::FETCH_ASSOC);
    try {
        $cap = $value['margin'];
    } catch (Exception $e) {
        throw new Exception("Error fetching data from database.");
    }
    return $cap;
}

function circuit_breaker_margin($capName) 
{
    global $dbh;
    $stmt = $dbh->prepare('SELECT margin FROM circuit_breaker WHERE name = :name');
    $stmt->bindParam(':name', $capName);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['margin'];
}

function cap_compute($marketPrice, $cap) 
{
    return ($marketPrice * $cap) / 100;
}

function client_commission($cdCode, $username) 
{
    global $dbh;
    $stmt = $dbh->prepare('SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate
                   FROM client_account b
                   INNER JOIN bbo_commission c ON b.bro_comm_id = c.bro_comm_id
                   WHERE b.cd_code = :cd_code -- AND b.user_name = :user_name');
    $stmt->bindParam(':cd_code', $cdCode);
    // $stmt->bindParam(':user_name', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['rate'];
}

function client_commission_multiple_brokers($cdCode, $username) 
{
    global $dbh;
    $stmt = $dbh->prepare('SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate
                           FROM client_account b
                           INNER JOIN bbo_commission c ON b.bro_comm_id = c.bro_comm_id
                           WHERE b.cd_code = :cd_code AND substr(b.user_name, 1, 7) = :user_name');
    $stmt->bindParam(':cd_code', $cdCode);
    $stmt->bindParam(':user_name', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['rate'];
}

function client_commission_te($cdCode, $brokerUserName) 
{
    global $dbh;
    $stmt = $dbh->prepare('SELECT b.ID, b.bro_comm_id, b.f_name, b.l_name, b.cd_code, c.rate
                           FROM client_account b
                           INNER JOIN bbo_commission c ON b.bro_comm_id = c.bro_comm_id
                           WHERE b.cd_code = :cd_code AND b.user_name = :user_name');
    $stmt->bindParam(':cd_code', $cdCode);
    $stmt->bindParam(':user_name', $brokerUserName);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['rate'];
}

function pending_vol($cd_code, $sy_id) {
    global $dbh;
    $stmt = $dbh->prepare("SELECT volume, pending_out_vol, pending_in_vol FROM cds_holding WHERE cd_code = :cd AND symbol_id = :id");
    $stmt->bindParam(':cd', $cd_code);
    $stmt->bindParam(':id', $sy_id);
    $stmt->execute();
    $value = $stmt->fetch();
    $piv = isset($value['pending_in_vol']) ? $value['pending_in_vol'] : 0;
    $pov = isset($value['pending_out_vol']) ? $value['pending_out_vol'] : 0;
    $vol = isset($value['volume']) ? $value['volume'] : 0;
    //$dbh = null;
    return array($pov, $piv, $vol);
}

function cash_total_client($cd_code, $finance_type, $username) {
  global $dbh;
  switch ($finance_type) {
    case 'bbo':
    $query = "SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID FROM bbo_finance a,client_account b WHERE  a.cd_code=:ac and b.cd_code=:ac and a.user_name=:un and a.flag=1";
    break;
    case 'rights':
    $query = "SELECT a.cd_code, SUM(a.amount) AS tot FROM rights_finance a INNER JOIN client_account b ON a.cd_code = b.cd_code WHERE a.cd_code = :ac AND a.user_name = :un";
    break;
    case 'bond':
    $query = "SELECT a.cd_code, SUM(a.amount) AS tot FROM bond_finance a INNER JOIN client_account b ON a.cd_code = b.cd_code WHERE a.cd_code = :ac AND a.user_name = :un";
    break;
    case 'ipo':
    $query = "SELECT a.cd_code, SUM(a.amount) AS tot FROM ipo_finance a INNER JOIN client_account b ON a.cd_code = b.cd_code WHERE a.cd_code = :ac AND a.user_name = :un";
    break;
    case 'terminal':
    $query = "SELECT a.cd_code,sum(a.amount) as tot, b.cd_code,b.ID FROM bbo_finance a,client_account b WHERE  a.cd_code=:ac and b.cd_code=:ac and a.user_name=:un and a.flag=1";
    break;
    default:
    throw new Exception("Invalid finance type.");
  }
  $stmt = $dbh->prepare($query);
  $stmt->bindParam(':ac', $cd_code);
  $stmt->bindParam(':un', $username);
  $stmt->execute();
  $value = $stmt->fetch();
  $tot = $value['tot'];
  //$dbh = null;
  return $tot;
}
function prev_amt_ord($fid) {
    global $dbh;
    $wc = $dbh->prepare("SELECT * FROM bbo_finance WHERE flag_id=:fid");
    $wc->bindParam(':fid', $fid);
    $wc->execute();
    $value = $wc->fetch();
    return $value['amount'];
}

function check_orders($cdcode, $sy_id, $side, $p_code) {
    global $dbh;
    $wc = $dbh->prepare("SELECT * FROM orders WHERE cd_code = ? AND symbol_id = ? AND participant_code = ? -- AND side= ?");
    // $wc->execute([$cdcode, $sy_id, $p_code, $side]);
    $wc->execute([$cdcode, $sy_id, $p_code]);
    if($wc->rowCount() > 0){
       return 1;
    }
    else{
       return 0;
    }
}

function exe_vol($oidd) {
    global $dbh;
    $pr = $dbh->prepare('SELECT exe_vol FROM orders WHERE order_id = ?');
    $pr->execute([$oidd]);
    $value = $pr->fetch();
    return $value['exe_vol'];
}

function compare($pid) {
  global $dbh;
    $q222 = $dbh->prepare('SELECT diff_chk FROM price_table WHERE pid = ?');
    $q222->execute([$pid]);
    $value = $q222->fetch();
    return $value['diff_chk'];
}

function rowcountsell($op, $sym_id) {
  global $dbh;
    $pr = $dbh->prepare('SELECT * FROM orders WHERE price <= ? AND symbol_id = ? AND sell_vol > 0 AND side = "S" ORDER BY sell_vol DESC');
    $pr->execute([$op, $sym_id]);
    return $pr->rowCount();
}

function rowcountbuy($op, $sym_id) {
  global $dbh;
    $pr = $dbh->prepare('SELECT * FROM orders WHERE price >= ? AND symbol_id = ? AND buy_vol > 0 AND side = "B" ORDER BY buy_vol DESC');
    $pr->execute([$op, $sym_id]);
    return $pr->rowCount();
}

function ins_id($username) {
  global $dbh;
    $check = $dbh->prepare('SELECT a.institution_id, c.participant_code FROM adm_institution a, adm_participants b, users c WHERE c.participant_code = b.participant_code AND b.institution_id = a.institution_id AND c.username = ?');
    $check->execute([$username]);
    $res = $check->fetch();
    $institution_id = isset($res['institution_id']) ? $res['institution_id'] : 0;
    $participant_code = isset($res['participant_code']) ? $res['participant_code'] : 0;
    return [$institution_id, $participant_code];
}


function find_link_user_cd_code($username) 
{
    global $dbh;
    $check = $dbh->prepare('SELECT client_code FROM linkuser WHERE username = :un');
    $check->bindParam(':un', $username);
    $check->execute();
    $res = $check->fetch();
    return $res['client_code'];
}

function trade_confirm_sell($cd_code, $toDate, $fromDate) 
{
    global $dbh;
    $pr = $dbh->prepare('SELECT AVG(order_exe_price) as avgp, SUM(lot_size_execute) as lse FROM executed_orders WHERE cd_code = :cd AND :fromDate <= order_date AND order_date <= :toDate AND side = "S"');
    $pr->bindParam(':cd', $cd_code);
    $pr->bindParam(':fromDate', $fromDate);
    $pr->bindParam(':toDate', $toDate);
    $pr->execute();
    $row = $pr->fetch();
    $p = (float)$row['avgp'];
    $v = (float)$row['lse'];
    return $p * $v;
}

function trade_confirm_buy($cd_code, $toDate, $fromDate) 
{
    global $dbh;
    $pr = $dbh->prepare('SELECT AVG(order_exe_price) as avgp, SUM(lot_size_execute) as lse FROM executed_orders WHERE cd_code = :cd AND :fromDate <= order_date AND order_date <= :toDate AND side = "B"');
    $pr->bindParam(':cd', $cd_code);
    $pr->bindParam(':fromDate', $fromDate);
    $pr->bindParam(':toDate', $toDate);
    $pr->execute();
    $row = $pr->fetch();
    $p = (float)$row['avgp'];
    $v = (float)$row['lse'];
    return $p * $v;
}

function broker_user_name($username) 
{
    global $dbh;
    $pr = $dbh->prepare('SELECT broker_user_name FROM linkuser WHERE username = :un');
    $pr->bindParam(':un', $username);
    $pr->execute();
    $row = $pr->fetch();
    return $row['broker_user_name'];
}

function order_audit($cd_code, $participant_code, $order_entry, $buy_vol, $order_size, $symbol_id, $price, $side, $commis_amt, $flag_id, $member_broker)
{
    global $dbh;

    $stmt = $dbh->prepare("INSERT INTO orders_audit(cd_code, participant_code, order_entry, buy_vol, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker)
    VALUES (:cd_code, :participant_code, :order_entry, :buy_vol, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker)");
    $stmt->bindParam(':cd_code', $cd_code);
    $stmt->bindParam(':participant_code', $participant_code);
    $stmt->bindParam(':order_entry', $order_entry);
    $stmt->bindParam(':buy_vol', $buy_vol);
    $stmt->bindParam(':order_size', $order_size);
    $stmt->bindParam(':symbol_id', $symbol_id);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':side', $side);
    $stmt->bindParam(':commis_amt', $commis_amt);
    $stmt->bindParam(':flag_id', $flag_id);
    $stmt->bindParam(':member_broker', $member_broker);

    return $stmt->execute();
}

function order_auditTE($cd_code, $participant_code, $order_entry, $buy_vol, $order_size, $symbol_id, $price, $side, $commis_amt, $flag_id, $member_broker)
{
    global $dbh;

    $stmt = $dbh->prepare("INSERT INTO orders_audit(cd_code, participant_code, order_entry, buy_vol, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker)
    VALUES (:cd_code, :participant_code, :order_entry, :buy_vol, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker)");
    $stmt->bindParam(':cd_code', $cd_code);
    $stmt->bindParam(':participant_code', $participant_code);
    $stmt->bindParam(':order_entry', $order_entry);
    $stmt->bindParam(':buy_vol', $buy_vol);
    $stmt->bindParam(':order_size', $order_size);
    $stmt->bindParam(':symbol_id', $symbol_id);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':side', $side);
    $stmt->bindParam(':commis_amt', $commis_amt);
    $stmt->bindParam(':flag_id', $flag_id);
    $stmt->bindParam(':member_broker', $member_broker);

    return $stmt->execute();
}

$avlVolStmt = $dbh->prepare("SELECT sum(order_size) as sum from rights_issue WHERE type IN ('S', 'R', 'O') AND cd_code = :cd AND status = 0");

$avlAmtStmt = $dbh->prepare("SELECT sum(total_amount) as sum from rights_issue WHERE ((type='R' and renounce_cd_code=:cd and user_name=:un) OR (type='S' and cd_code=:cd and user_name=:un) OR (type='B' and cd_code=:cd and user_name=:un)) and status=0");

// $avilableAmtStmt = $dbh->prepare("SELECT sum(total_amount) as sum from rights_issue WHERE (type='R' and renounce_cd_code=:rcd and status=0) OR (type='S' and cd_code=:rcd and status=0) OR (type='B' and cd_code=:rcd and status=0)");
$avilableAmtStmt = $dbh->prepare("
        SELECT SUM(total_amount) AS sum
        FROM rights_issue
        WHERE status = 0
          AND (
            (type = 'R' AND renounce_cd_code = :rcd) OR
            (type IN ('S', 'B') AND cd_code = :rcd)
          );
");

$ipoavlAmtStmt = $dbh->prepare("SELECT sum(total_amount) as sum FROM ipo WHERE type='IPO' and cd_code=:cd and status=0");

$ipoAmtStmt = $dbh->prepare("SELECT sum(total_amount) as sum from orders_ipo WHERE cd_code=:cd");

$bondavlAmtStmt = $dbh->prepare("SELECT sum(total_amount) as sum from bond WHERE type='BOND' and cd_code=:cd and status=0");

function avlVol($cd) {
    global $avlVolStmt;
    $avlVolStmt->bindParam(':cd', $cd);
    $avlVolStmt->execute();
    $val = $avlVolStmt->fetch();
    return $val['sum'];
}

function avlAmt($cd, $username) {
    global $avlAmtStmt;
    $avlAmtStmt->bindParam(':cd', $cd);
    $avlAmtStmt->bindParam(':un', $username);
    $avlAmtStmt->execute();
    $val = $avlAmtStmt->fetch();
    return $val['sum'];
}

function avilableAmt($cd, $rcd) {
    global $avilableAmtStmt;
    // $avilableAmtStmt->bindParam(':cd', $cd);
    $avilableAmtStmt->bindParam(':rcd', $rcd);
    $avilableAmtStmt->execute();
    $val = $avilableAmtStmt->fetch();
    return $val['sum'];
}
function ipoavlAmt($cd) {
    global $avlVolStmt;
    $avlVolStmt->bindParam(':cd', $cd);
    $avlVolStmt->execute();
    $val = $avlVolStmt->fetch();
    return $val['sum'];
}
function ipoAmt($cd) {
    global $avlVolStmt;
    $avlVolStmt->bindParam(':cd', $cd);
    $avlVolStmt->execute();
    $val = $avlVolStmt->fetch();
    return $val['sum'];
}
function bondavlAmt($cd) {
    global $avlVolStmt;
    $avlVolStmt->bindParam(':cd', $cd);
    $avlVolStmt->execute();
    $val = $avlVolStmt->fetch();
    return $val['sum'];
}

function cash_total_ipo($cd_code, $username) {
    global $dbh;
    $wc= $dbh->prepare("SELECT sum(a.amount) as tot FROM ipo_finance a WHERE a.cd_code=:ac and a.user_name=:un");
    $wc->bindParam(':ac',$cd_code);
    $wc->bindParam(':un',$username);
    $wc ->execute();
    $value = $wc->fetch();
    $tot = $value['tot'];
    return $tot;

}

function check_bond_pending_orders($cd_code, $sy_id, $order_side, $part_code) {
    global $dbh;
    $stmt = $dbh->prepare("
        SELECT EXISTS (
            SELECT 1
            FROM bond_orders
            WHERE cd_code = ? AND symbol_id = ? AND participant_code = ? AND side = ?
        )
    ");
    $stmt->execute([$cd_code, $sy_id, $part_code, $order_side]);
    return (int) $stmt->fetchColumn();
}

function check_bond_orders_same_symbol_opposite_side($cd_code, $sy_id, $part_code) {
    global $dbh;
    $stmt = $dbh->prepare("
        SELECT EXISTS (
            SELECT 1
            FROM bond_orders
            WHERE cd_code = ? AND symbol_id = ? AND participant_code = ? 
        )
    ");
    $stmt->execute([$cd_code, $sy_id, $part_code]);
    return (int) $stmt->fetchColumn();
}

/*function order_bond_audit($cd_code, $participant_code, $order_entry, $buy_vol, $order_size, $symbol_id, $price, $side, $commis_amt, $flag_id, $member_broker)
{
    global $dbh;
    $stmt = $dbh->prepare("INSERT INTO bond_order_audits(cd_code, participant_code, order_entry, buy_vol, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
            $cd_code, $participant_code, $order_entry, $buy_vol, $order_size, $symbol_id, $price, $side, $commis_amt, $flag_id, $member_broker
    ]);
}*/

function order_bond_audit(
        PDO $dbh,
        string $cd_code,
        string $participant_code,
        string $order_entry,
        int $vol_col,
        int $order_size,
        int $symbol_id,
        float $price,
        string $side,
        string $order_date,
        float $commis_amt,
        int $flag_id,
        string $member_broker, 
        string $order_type,
        string $status,
        float $accur_int,
        float $dirty_price,
        float $ytm,
        string $buyer_code,
): bool {
        static $stmt = null;

        if ($stmt === null) {
            $col_name = ($side === 'S') ? 'sell_vol' : 'buy_vol';
            $stmt = $dbh->prepare("
                INSERT INTO bond_order_audits (cd_code, participant_code, order_entry, {$col_name}, order_size, symbol_id, price, side, commis_amt, flag_id, member_broker, acc_intrt, dirty_price, ytm, order_type, quoted_to, order_date, status)
                VALUES (:cd_code, :participant_code, :order_entry, :col_vol, :order_size, :symbol_id, :price, :side, :commis_amt, :flag_id, :member_broker, :ac_in, :dir_price, :ytm, :or_type, :buyer_cd_code, :ord_date, :statss)
            ");
        }
        
        return $stmt->execute([
            ':cd_code'          => $cd_code,
            ':participant_code'=> $participant_code,
            ':order_entry'     => $order_entry,
            ':col_vol'         => $vol_col,
            ':order_size'      => $order_size,
            ':symbol_id'       => $symbol_id,
            ':price'           => $price,
            ':side'            => $side,
            ':commis_amt'      => $commis_amt,
            ':flag_id'         => $flag_id,
            ':member_broker'   => $member_broker,
            ':ac_in'           => $accur_int,
            ':dir_price'       => $dirty_price,
            ':ytm'             => $ytm,
            ':or_type'         => $order_type,
            ':buyer_cd_code'   => $buyer_code,
            ':ord_date'        => $order_date,
            ':ord_date'        => $order_date,
            ':statss'          => $status,
        ]);
}

// function to check password strength
function validatePassword($password) {
    // Define the pattern for a strong password
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_\-]{8,}$/';
    
    // Check if the password matches the pattern
    return preg_match($pattern, $password) === 1;
}

?>
