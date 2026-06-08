<?php
function calculateYTM(
    $cleanPrice,
    $faceValue,
    $couponRate,
    $issueDate,
    $maturityDate,
    $tradeDate,
    $frequency // 1=annual, 2=semiannual, 4=quarterly
) {
    // 1️⃣ Basic values
    $coupon = $faceValue * $couponRate / $frequency;

    $issue = new DateTime($issueDate);
    $maturity = new DateTime($maturityDate);
    $trade = new DateTime($tradeDate);

    // 2️⃣ Months per coupon
    $monthsPerCoupon = 12 / $frequency;

    // 3️⃣ Calculate last coupon before trade
    $diff = $issue->diff($trade);
    $totalMonths = ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0);

    $periodsPassed = floor($totalMonths / $monthsPerCoupon);

    // For first coupon after issue, periodsPassed can be 0
    $lastCoupon = clone $issue;
    $lastCoupon->add(new DateInterval('P' . ($periodsPassed * $monthsPerCoupon) . 'M'));

    // Ensure last coupon is not after trade date
    if ($lastCoupon > $trade) {
        $periodsPassed--;
        $lastCoupon = clone $issue;
        if ($periodsPassed > 0) {
            $lastCoupon->add(new DateInterval('P' . ($periodsPassed * $monthsPerCoupon) . 'M'));
        }
    }

    // 4️⃣ Calculate next coupon after last coupon
    $nextCoupon = clone $lastCoupon;
    $nextCoupon->add(new DateInterval('P' . $monthsPerCoupon . 'M'));

    // 5️⃣ Accrued Interest (ACT/365)
    $daysInPeriod = $lastCoupon->diff($nextCoupon)->days;
    $daysAccrued = $lastCoupon->diff($trade)->days;
    $accruedInterest = $coupon * ($daysAccrued / $daysInPeriod);

    $dirtyPrice = $cleanPrice + $accruedInterest;

    // 6️⃣ Remaining periods
    $diffMaturity = $nextCoupon->diff($maturity);
    $totalMonthsRemaining = ($diffMaturity->y * 12) + $diffMaturity->m + ($diffMaturity->d > 0 ? 1 : 0);
    $remainingPeriods = ceil($totalMonthsRemaining / $monthsPerCoupon) + 1; // include next coupon

    // 7️⃣ Time factors for discounting (fractional years)
    $times = [];
    for ($i = 0; $i < $remainingPeriods; $i++) {
        $times[] = ($i + ($daysAccrued / $daysInPeriod)) / $frequency;
    }

    // 8️⃣ Cash flows
    $flows = array_fill(0, $remainingPeriods, $coupon);
    $flows[$remainingPeriods - 1] += $faceValue; // add principal to last payment

    // 9️⃣ Newton-Raphson for YTM
    $ytm = $couponRate; // initial guess
    $maxIter = 100;
    $tol = 1e-10;

    for ($iter = 0; $iter < $maxIter; $iter++) {
        $f = 0;
        $df = 0;
        foreach ($flows as $j => $cf) {
            $f += $cf / pow(1 + $ytm, $times[$j]);
            $df -= ($times[$j] * $cf) / pow(1 + $ytm, $times[$j] + 1);
        }
        $f -= $dirtyPrice;
        $newYtm = $ytm - $f / $df;
        if (abs($newYtm - $ytm) < $tol) {
            $ytm = $newYtm;
            break;
        }
        $ytm = $newYtm;
    }

    return [
        'accrued_interest' => round($accruedInterest, 4),
        'dirty_price' => round($dirtyPrice, 4),
        'ytm_percent' => round($ytm * 100, 4)
    ];
}
?>