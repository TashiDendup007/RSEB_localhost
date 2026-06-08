<?php  
    include ('bond_yeild_to_maturity_cal.php');

    $cleanPrice   = 950;
    $faceValue    = 1000;
    $couponRate   = 10;
    $frequency    = 1; // 1=Annual, 2=Semi-Annual
    $tradeDate    = "2026-02-17";
    $lastCoupon   = "2025-06-05";
    $nextCoupon   = "2027-06-05";
    $maturity     = "2035-06-05";

    /* Step 1: Accrued Interest */
    $accrued = calculateAccruedInterest(
        $faceValue,
        $couponRate,
        $frequency,
        $lastCoupon,
        $tradeDate
    );

    /* Step 2: Dirty Price */
    $dirtyPrice = $cleanPrice + $accrued;

    /* Step 3: Cashflows */
    $cashflows = generateCashFlows(
        $faceValue,
        $couponRate,
        $frequency,
        $nextCoupon,
        $maturity
    );

    /* Step 4: YTM */
    $ytm = calculateYTM(
        $dirtyPrice,
        $cashflows,
        $tradeDate,
        $frequency
    );

    echo "Accrued Interest: " . round($accrued,2) . PHP_EOL;
    echo "<br>Clean Price: " . round($cleanPrice,2) . PHP_EOL;
    echo "<br>Dirty Price: " . round($dirtyPrice,2) . PHP_EOL;
    echo "<br>Yield to Maturity (YTM): " . round($ytm,4) . "%";


?>