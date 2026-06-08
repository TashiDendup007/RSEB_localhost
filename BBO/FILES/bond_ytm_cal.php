<?php  
date_default_timezone_set("Asia/Thimphu");
// include ('session_start_file.php');
include ('../../CONNECTIONS/db.php');

$symbol_id = 118;
$security_type = 'CB';
$clean_price = 950;

if ($security_type != 'OS') {

    $stmt = $dbh->prepare("SELECT s.maturity_date, s.face_value, s.coupon_rates
        FROM symbol s
        WHERE s.symbol_id = ?
    ");
    $stmt->execute([$symbol_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    $maturity_date = $res['maturity_date'];
    $face_value = $res['face_value'];
    $coupon_rates = $res['coupon_rates'];
    $annual_coupon = $face_value * $coupon_rates / 100; // probably 100 
    $trade_date = date("Y-m-d");
    $present_year = date('Y');

    // $lastCoupon = '2025-06-05';
    // to get last coupon date
    $stmt = $dbh->prepare("SELECT d.date FROM coupon_payable_date d WHERE YEAR(d.date) = ? AND d.symbol_id = ?");
    $stmt->execute([$present_year, $symbol_id]);
    $coupon_date = $stmt->fetchColumn();
    echo'coupon_date=> ' . $coupon_date . '<br>';

    $lastCoupon = date('Y-m-d', strtotime('-1 year', strtotime($coupon_date)));
    echo'lastCoupon=> ' . $lastCoupon . '<br>';

    echo'maturity_date=> ' . $maturity_date . '<br>';
    echo'face_value=> ' . $face_value . '<br>';
    echo'coupon_rates=> ' . $coupon_rates . '<br>';
    echo'annual_coupon=> ' . $annual_coupon . '<br>';
    echo'trade_date=> ' . $trade_date . '<br>';
    echo'clean_price=> ' . $clean_price . '<br>';
    
    // calculattion ytm, accrued interest, next coupon
    $n = yearsToMaturity($trade_date, $maturity_date);
    $years_to_maturity = round($n, 4);
    echo'years_to_maturity=> ' . $years_to_maturity . '<br>';

    $next_coupon = nextCouponDate($trade_date, $maturity_date);
    echo'next_coupon=> ' . $next_coupon . '<br>';
    
    $accrued_interest = accruedInterestACT365($lastCoupon, $trade_date, $annual_coupon);
    echo'accrued_interest=> ' . $accrued_interest . '<br>';
    
    $dirty_price = round($clean_price + $accrued_interest, 2);

    echo'dirty_price=> ' . $dirty_price . '<br>';

    $flows = buildCashFlows($trade_date, $maturity_date, $annual_coupon, $face_value);
    $ytm = calculateYTM($dirty_price, $flows);
    echo 'YTM => ' . round($ytm * 100, 2) . "%";
}

function yearsToMaturity($tradeDate, $maturityDate) {
    $start = new DateTime($tradeDate);
    $end   = new DateTime($maturityDate);
    $days = $start->diff($end)->days;
    return $days / 365;
}

function nextCouponDate($tradeDate, $maturityDate) {
    $trade = new DateTime($tradeDate);
    $maturity = new DateTime($maturityDate);

    $coupon = new DateTime($trade->format('Y') . '-06-05');

    if ($coupon <= $trade) {
        $coupon->modify('+1 year');
    }

    if ($coupon > $maturity) {
        return null;
    }

    return $coupon->format('Y-m-d');
}

function accruedInterestACT365($lastCoupon, $tradeDate, $annualCoupon) {
    $last = new DateTime($lastCoupon);
    $trade = new DateTime($tradeDate);

    $daysAccrued = $last->diff($trade)->days;
    return ($annualCoupon * $daysAccrued) / 365;
}

function buildCashFlows($tradeDate, $maturityDate, $coupon, $faceValue) {
    $flows = [];
    $trade = new DateTime($tradeDate);
    $maturity = new DateTime($maturityDate);

    $year = (int)$trade->format('Y');

    for ($y = $year; $y <= (int)$maturity->format('Y'); $y++) {
        $couponDate = new DateTime("$y-06-05");
        if ($couponDate <= $trade) continue;
        if ($couponDate > $maturity) break;

        $t = $trade->diff($couponDate)->days / 365;
        $amount = ($couponDate == $maturity)
            ? $coupon + $faceValue
            : $coupon;

        $flows[] = [$t, $amount];
    }
    return $flows;
}

function calculateYTM($price, $flows) {
    $ytm = 0.11; // initial guess

    for ($i = 0; $i < 1000; $i++) {
        $pv = 0;
        foreach ($flows as [$t, $cf]) {
            $pv += $cf / pow(1 + $ytm, $t);
        }
        $ytm += ($pv - $price) / 100000;
    }
    return $ytm;
}

?>