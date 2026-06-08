<?php
date_default_timezone_set("Asia/Thimphu");

/**
 * Calculate Year Fraction (Actual/Actual)
 */
function yearFraction($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end   = new DateTime($endDate);
    $days  = $start->diff($end)->days;
    return $days / 365; // can modify to 360 if needed
}

/**
 * Calculate Accrued Interest
 */
function calculateAccruedInterest($face, $couponRate, $frequency, $lastCoupon, $tradeDate) {
    $annualCoupon = $face * ($couponRate / 100);
    $couponPerPeriod = $annualCoupon / $frequency;

    $accrual = yearFraction($lastCoupon, $tradeDate);
    return $couponPerPeriod * $accrual * $frequency;
}

/**
 * Generate Remaining Cashflows
 */
function generateCashFlows($face, $couponRate, $frequency, $nextCoupon, $maturity) {

    $cashflows = [];
    $annualCoupon = $face * ($couponRate / 100);
    $couponPerPeriod = $annualCoupon / $frequency;

    $date = new DateTime($nextCoupon);
    $maturityDate = new DateTime($maturity);

    while ($date <= $maturityDate) {

        $cf = $couponPerPeriod;

        if ($date == $maturityDate) {
            $cf += $face; // add principal at maturity
        }

        $cashflows[] = [
            'date' => $date->format('Y-m-d'),
            'amount' => $cf
        ];

        $date->modify('+' . (12 / $frequency) . ' months');
    }

    return $cashflows;
}

/**
 * Calculate YTM using Newton-Raphson
 */
function calculateYTM($dirtyPrice, $cashflows, $tradeDate, $frequency, $guess = 0.10) {

    $ytm = $guess;
    $tolerance = 1e-8;
    $maxIterations = 100;

    for ($i = 0; $i < $maxIterations; $i++) {

        $f = 0;
        $df = 0;

        foreach ($cashflows as $flow) {

            $t = yearFraction($tradeDate, $flow['date']);
            $periods = $t * $frequency;

            $discountFactor = pow(1 + $ytm / $frequency, $periods);

            $f += $flow['amount'] / $discountFactor;

            $df -= ($periods * $flow['amount']) /
                   (pow(1 + $ytm / $frequency, $periods + 1) * $frequency);
        }

        $f -= $dirtyPrice;

        $newYtm = $ytm - ($f / $df);

        if (abs($newYtm - $ytm) < $tolerance) {
            return $newYtm * 100;
        }

        $ytm = $newYtm;
    }

    return $ytm * 100;
}
