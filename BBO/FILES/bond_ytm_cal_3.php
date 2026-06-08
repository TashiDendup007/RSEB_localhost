<?php
    date_default_timezone_set("Asia/Thimphu");
    include ('../../CONNECTIONS/db.php');

    $symbol_id    = 118;
    $clean_price  = 950;
    $trade_date   = date("Y-m-d");
    $present_year = date('Y');

    // symbol details
    $stmt = $dbh->prepare("
        SELECT maturity_date, face_value, coupon_rates, coupon_payable AS coupon_frequency
        FROM symbol 
        WHERE symbol_id = ?
    ");
    $stmt->execute([$symbol_id]);
    $bond = $stmt->fetch(PDO::FETCH_ASSOC);

    // to get last coupon date
    $stmt = $dbh->prepare("SELECT d.date, MONTH(d.date) AS coupon_month, DAY(d.date) AS coupon_day FROM coupon_payable_date d WHERE YEAR(d.date) = ? AND d.symbol_id = ?");
    $stmt->execute([$present_year, $symbol_id]);
    $coupons = $stmt->fetch(PDO::FETCH_ASSOC);

    $maturity_date   = $bond['maturity_date'];
    $face_value      = $bond['face_value'];
    $coupon_rate     = $bond['coupon_rates'] / 100;
    $frequency       = $bond['coupon_frequency']; // 1 or 2
    $coupon_month    = $coupons['coupon_month'];
    $coupon_day      = $coupons['coupon_day'];

    $annual_coupon   = $face_value * $coupon_rate;
    $period_coupon   = $annual_coupon / $frequency;

    $couponDates = generateCouponDates(
        $trade_date,
        $maturity_date,
        $coupon_month,
        $coupon_day,
        $frequency
    );

    $accrued = accruedInterest(
        $trade_date,
        $couponDates,
        $period_coupon,
        $frequency
    );

    $dirty_price = $clean_price + $accrued;

    $flows = buildCashFlows(
        $trade_date,
        $couponDates,
        $period_coupon,
        $face_value
    );

    $ytm = calculateYTM($dirty_price, $flows);

    echo "Clean Price : $clean_price <br>";
    echo "Accrued     : " . round($accrued,2) . "<br>";
    echo "Dirty Price : " . round($dirty_price,2) . "<br>";
    echo "YTM         : " . round($ytm*100,4) . "%";

    function generateCouponDates($tradeDate, $maturityDate, $month, $day, $frequency) {

        $dates = [];
        $maturity = new DateTime($maturityDate);
        $current  = new DateTime($maturityDate);

        $interval = ($frequency == 2) ? '6 months' : '1 year';

        while ($current > new DateTime($tradeDate)) {
            $dates[] = clone $current;
            $current->modify("-$interval");
        }

        return array_reverse($dates);
    }

    function accruedInterest($tradeDate, $couponDates, $periodCoupon, $frequency) {

        $trade = new DateTime($tradeDate);

        $last = null;
        $next = null;

        foreach ($couponDates as $date) {
            if ($date > $trade) {
                $next = $date;
                break;
            }
            $last = $date;
        }

        if (!$last || !$next) return 0;

        $daysAccrued = $last->diff($trade)->days;
        $daysPeriod  = $last->diff($next)->days;

        return $periodCoupon * ($daysAccrued / $daysPeriod);
    }

    function buildCashFlows($tradeDate, $couponDates, $periodCoupon, $faceValue) {

        $flows = [];
        $trade = new DateTime($tradeDate);

        foreach ($couponDates as $date) {

            $t = $trade->diff($date)->days / 365;

            $amount = $periodCoupon;

            if ($date == end($couponDates)) {
                $amount += $faceValue;
            }

            $flows[] = [$t, $amount];
        }

        return $flows;
    }

    function calculateYTM($price, $flows, $guess = 0.10) {

        $ytm = $guess;
        $maxIter = 100;
        $tolerance = 1e-8;

        for ($i = 0; $i < $maxIter; $i++) {

            $f = 0;
            $df = 0;

            foreach ($flows as [$t, $cf]) {
                $f  += $cf / pow(1 + $ytm, $t);
                $df += -$t * $cf / pow(1 + $ytm, $t + 1);
            }

            $newYTM = $ytm - ($f - $price) / $df;

            if (abs($newYTM - $ytm) < $tolerance) {
                return $newYTM;
            }

            $ytm = $newYTM;
        }

        return $ytm;
    }

?>