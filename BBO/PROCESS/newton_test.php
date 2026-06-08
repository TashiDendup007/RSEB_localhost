<?php
	date_default_timezone_set("Asia/Thimphu");
	include ('../../CONNECTIONS/db.php');

	$symbol_id = 118;
	$cleanPrice = 950;
	$security_type = 'CB';

	$stmt = $dbh->prepare("SELECT s.maturity_date, s.face_value, s.coupon_rates, s.date_of_issue, s.coupon_payable AS frequency 
	        FROM symbol s
	        WHERE s.symbol_id = ?
    ");
    $stmt->execute([$symbol_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($res);

    // get last coupon date
    $get = $dbh->prepare("SELECT c.date FROM coupon_payable_date c WHERE c.symbol_id = ? AND c.`status` = 1 ORDER BY c.id DESC LIMIT 1");
    $get->execute([$symbol_id]);
    $last_coupon_date = $get->fetchColumn();

	$faceValue = $res['face_value'];
	$couponRate = $res['coupon_rates'] / 100;
	$coupon = $faceValue * $couponRate;
	echo "<br>coupon => " . $coupon;

	// if no coupon has been paid yet, date_of_issue will be taken as last coupon date
	// substract 1 day to get exact date of next coupn
	if ($last_coupon_date === '' || $last_coupon_date === false) {
		$last_coupon_date = new DateTime($res['date_of_issue']);
		$last_coupon_date->modify('-1 day');
		$last_coupon_date = $last_coupon_date->format('Y-m-d');
	} 
	echo "<br>last_coupon_date => " . $last_coupon_date;

	$issueDate = $last_coupon_date;
	$maturityDate = $res['maturity_date'];
	$frequency = $res['frequency'];

	// date investor buys the bond.
	$settlement = date("Y-m-d");
	echo "<br>settlement => " . $settlement;

	$coupons = generateCouponDates($issueDate, $maturityDate, $frequency);
	echo "<br>coupons => " . implode(", ", $coupons);
	echo "<br>";

	$accrued = accruedInterest($settlement, $issueDate, $coupons, $coupon, $frequency);
	$dirtyPrice = $cleanPrice + $accrued;

	$flows = remainingCashflows($settlement, $coupons, $coupon, $faceValue, $frequency);
	foreach($flows as $f)
	{
	    echo "<br>Date: ".$f['date']->format("Y-m-d")." | Cash: ".$f['cash'];
	}

	$ytm = calculateYTM($dirtyPrice, $flows, $settlement, $frequency);
	
	echo "<br><br>accrued => " . number_format($accrued, 4);
	echo "<br>dirtyPrice => " . number_format($dirtyPrice, 4);
	echo "<br>YTM => " . number_format($ytm, 4);
	echo "<br><br>YTM => " . number_format($ytm * 100, 2) . "%";

	// function to generate coupons date.
	function generateCouponDates($issueDate, $maturityDate, $frequency)
	{
	    $dates = [];

	    $months = intval(12 / $frequency);

	    $d = new DateTime($issueDate);
	    $maturity = new DateTime($maturityDate);

	    $d->modify("+$months months");

	    while ($d <= $maturity)
	    {
	        $dates[] = $d->format("Y-m-d");
	        $d->modify("+$months months");
	    }

	    return $dates;
	}

	// interest earned by seller between last coupon and settlement date.
	function accruedInterest($settlement, $issueDate, $couponDates, $coupon, $frequency)
	{
	    $settle = new DateTime($settlement);

	    $last = new DateTime($issueDate);
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

	    $couponPayment = $coupon / $frequency;

	    echo "<br>daysAccrued -> " .$daysAccrued;
	    echo "<br>daysPeriod -> " .$daysPeriod . "<br>";

	    return $couponPayment * ($daysAccrued / $daysPeriod);
	}

	// future payments investor will receive. only dates after settlement/trade days are included.
	function remainingCashflows($settlement,$couponDates,$coupon,$face,$frequency)
	{
	    $flows = [];

	    $settle = new DateTime($settlement);
	    $lastCoupon = end($couponDates);

	    foreach($couponDates as $c)
	    {
	        $d = new DateTime($c);

	        if($d > $settle)
	        {
	            $cash = $coupon / $frequency;

	            if($c == $lastCoupon)
	                $cash += $face;

	            $flows[] = [
	                "date"=>$d,
	                "cash"=>$cash
	            ];
	        }
	    }

	    return $flows;
	}

	// Price = Σ Cashflow / (1 + YTM/frequency)^(frequency × time)
	// YTM cannot be solved directly, the code uses Binary Search.
	function calculateYTM($dirtyPrice,$flows,$settlement,$frequency)
	{
		$low  = 0.0001;
	    $high = 1;

	    for($i=0;$i<40;$i++)
	    {
	        $mid = ($low + $high)/2;

	        $price = bondPrice($mid,$flows,$settlement,$frequency);

	        if($price > $dirtyPrice)
	            $low = $mid;
	        else
	            $high = $mid;
	    }

	    return $mid;
	}

	// calcuate prseent value
	function bondPrice($ytm, $flows, $settlement, $frequency)
	{
	    $pv = 0;

	    $settle = new DateTime($settlement);

	    foreach($flows as $f)
	    {
	        $days = $settle->diff($f['date'])->days;

	        $t = $days / 365;

	        $pv += $f['cash'] / pow(1 + $ytm/$frequency, $frequency * $t);
	    }

	    return $pv;
	}
?>