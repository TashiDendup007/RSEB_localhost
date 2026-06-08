<?php
date_default_timezone_set("Asia/Thimphu");

/*************** INPUT DATA ***************/
$faceValue   = 1000;
$couponRate  = 10;        // percent
$cleanPrice  = 950;
$frequency   = 1;         // 1=annual,2=semi
$issueDate   = "2025-06-06";
$maturity    = "2035-06-05";
$settlement  = "2026-03-09";

echo "faceValue => " . $faceValue;
echo "<br>couponRate => " . $couponRate;
echo "<br>cleanPrice => " . $cleanPrice;
echo "<br>frequency => " . $frequency;
echo "<br>issueDate => " . $issueDate;
echo "<br>maturity => " . $maturity;
echo "<br>settlement => " . $settlement;

/*************** PREPARE COUPON ***************/
$couponRateDecimal = $couponRate / 100;
$annualCoupon = $faceValue * $couponRateDecimal;
$couponPayment = $annualCoupon / $frequency;

/*************** LAST COUPON DATE ***************/
$lastCoupon = new DateTime($issueDate);
$lastCoupon->modify("-1 day");
$lastCoupon = $lastCoupon->format("Y-m-d");
echo "<br>Last coupon => {$lastCoupon}";

/*************** GENERATE COUPON SCHEDULE ***************/
$coupons = generateCouponSchedule($lastCoupon,$maturity,$frequency);

echo "<br><br>";
print_r($coupons);
echo "<br>";

/*************** ACCRUED INTEREST ***************/
$accrued = calculateAccruedInterest(
    $settlement,
    $lastCoupon,
    $coupons,
    $couponPayment
);

/*************** DIRTY PRICE ***************/
$dirtyPrice = $cleanPrice + $accrued;

/*************** CASHFLOWS ***************/
$flows = generateCashflows(
    $settlement,
    $coupons,
    $couponPayment,
    $faceValue
);

foreach ($flows as $key => $value) {
    echo "<br>Date: ".$value['date']->format("Y-m-d")." | Cash: ".$value['cash'];
}


/*************** YTM ***************/
$ytm = calculateYTM(
    $dirtyPrice,
    $flows,
    $settlement,
    $frequency
);

/*************** OUTPUT ***************/
echo "<br><br>Accrued Interest: ".number_format($accrued,2)."\n";
echo "<br>Dirty Price: ".number_format($dirtyPrice,2);
echo "<br>YTM: ".number_format($ytm*100,4)." %";


/******************************************************
                FUNCTIONS
******************************************************/


/*************** COUPON SCHEDULE ***************/
function generateCouponSchedule($lastCoupon,$maturity,$frequency)
{
    $dates = [];
    $months = 12/$frequency;

    $d = new DateTime($lastCoupon);
    $d->modify("+$months months");

    $mat = new DateTime($maturity);

    while($d <= $mat)
    {
        $dates[] = $d->format("Y-m-d");
        $d->modify("+$months months");
    }

    return $dates;
}


/*************** ACCRUED INTEREST ***************/
function calculateAccruedInterest($settlement,$lastCoupon,$couponDates,$couponPayment)
{
    $settle = new DateTime($settlement);

    $last = new DateTime($lastCoupon);
    $next = null;

    foreach($couponDates as $c)
    {
        $cd = new DateTime($c);

        if($cd > $settle)
        {
            $next = $cd;
            break;
        }

        $last = $cd;
    }

    if(!$next) return 0;

    $daysAccrued = $last->diff($settle)->days;
    $daysPeriod  = $last->diff($next)->days;

    return $couponPayment * ($daysAccrued/$daysPeriod);
}


/*************** CASHFLOW GENERATION ***************/
function generateCashflows($settlement,$couponDates,$couponPayment,$face)
{
    $flows = [];
    $settle = new DateTime($settlement);

    $lastCoupon = end($couponDates);

    foreach($couponDates as $c)
    {
        $d = new DateTime($c);

        if($d > $settle)
        {
            $cash = $couponPayment;

            if($c == $lastCoupon)
                $cash += $face;

            $flows[]=[
                "date"=>$d,
                "cash"=>$cash
            ];
        }
    }

    return $flows;
}


/*************** BOND PRICE ***************/
function bondPrice($ytm,$flows,$settlement,$frequency)
{
    $pv = 0;
    $settle = new DateTime($settlement);

    foreach($flows as $f)
    {
        $days = $settle->diff($f['date'])->days;
        $t = $days/365;

        $pv += $f['cash']/pow(1+$ytm/$frequency,$frequency*$t);
    }

    return $pv;
}


/*************** DERIVATIVE FOR NEWTON METHOD ***************/
function bondPriceDerivative($ytm,$flows,$settlement,$frequency)
{
    $derivative = 0;
    $settle = new DateTime($settlement);

    foreach($flows as $f)
    {
        $days = $settle->diff($f['date'])->days;
        $t = $days/365;

        $cf = $f['cash'];

        $base = pow(1+$ytm/$frequency,$frequency*$t);

        $derivative -= ($cf * $t) / ($base*(1+$ytm/$frequency));
    }

    return $derivative;
}


/*************** NEWTON-RAPHSON YTM ***************/
function calculateYTM($price,$flows,$settlement,$frequency)
{
    $ytm = 0.10;   // initial guess (10%)

    for($i=0;$i<20;$i++)
    {
        $f  = bondPrice($ytm,$flows,$settlement,$frequency) - $price;
        $fp = bondPriceDerivative($ytm,$flows,$settlement,$frequency);

        if(abs($fp) < 0.0000001)
            break;

        $ytm = $ytm - ($f/$fp);
    }

    return $ytm;
}

?>