<?php

    function calculateYTM(
        $cleanPrice,
        $faceValue,
        $couponRate,        // e.g. 0.10 for 10%
        $issueDate,
        $maturityDate,
        $tradeDate,
        $frequency = 1      // 1=annual, 2=semi, 4=quarterly
    ) {

        // 1️⃣ Basic values
        $coupon = $faceValue * $couponRate / $frequency;

        $issue = new DateTime($issueDate);
        $maturity = new DateTime($maturityDate);
        $trade = new DateTime($tradeDate);

        // 2️⃣ Generate coupon dates
        $intervalSpec = "P" . (12 / $frequency) . "M";
        $interval = new DateInterval($intervalSpec);

        $couponDates = [];
        $nextCoupon = clone $issue;

        while ($nextCoupon <= $maturity) {
            if ($nextCoupon > $trade) {
                $couponDates[] = clone $nextCoupon;
            }
            $nextCoupon->add($interval);
        }

        // 3️⃣ Find last coupon before trade
        $lastCoupon = clone $issue;
        $temp = clone $issue;

        while ($temp <= $trade) {
            $lastCoupon = clone $temp;
            $temp->add($interval);
        }

        // 4️⃣ Accrued Interest (ACT/365)
        $daysBetween = $lastCoupon->diff((clone $lastCoupon)->add($interval))->days;
        $daysAccrued = $lastCoupon->diff($trade)->days;

        $accruedInterest = $coupon * ($daysAccrued / $daysBetween);

        $dirtyPrice = $cleanPrice + $accruedInterest;

        // 5️⃣ Build cash flows
        $flows = [];
        foreach ($couponDates as $date) {
            $flows[] = $coupon;
        }

        // Add principal to final payment
        $flows[count($flows) - 1] += $faceValue;

        // 6️⃣ Time factors (fractional)
        $times = [];
        foreach ($couponDates as $date) {
            $days = $trade->diff($date)->days;
            $times[] = $days / 365;
        }

        // 7️⃣ Newton-Raphson YTM
        $ytm = $couponRate; // initial guess
        $maxIter = 100;
        $tol = 1e-10;

        for ($i = 0; $i < $maxIter; $i++) {

            $f = 0;
            $df = 0;

            for ($j = 0; $j < count($flows); $j++) {

                $f += $flows[$j] / pow(1 + $ytm, $times[$j]);
                $df -= ($times[$j] * $flows[$j]) / pow(1 + $ytm, $times[$j] + 1);
            }

            $f -= $dirtyPrice;

            $newYtm = $ytm - ($f / $df);

            if (abs($newYtm - $ytm) < $tol) {
                $ytm = $newYtm;
                break;
            }

            $ytm = $newYtm;
        }

        return [
            "accrued_interest" => round($accruedInterest, 4),
            "dirty_price" => round($dirtyPrice, 4),
            "ytm_percent" => round($ytm * 100, 4)
        ];
    }
?>