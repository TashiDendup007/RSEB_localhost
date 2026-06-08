<?php
	date_default_timezone_set("Asia/Thimphu");
	include ('../../CONNECTIONS/db.php');

	if (isset($_POST['calculate_yield_to_maturity'])) {

		$symbol_id = $_POST['symbol_id'];
		$cleanPrice = $_POST['price'];
		$security_type = $_POST['security_type'];

		$stmt = $dbh->prepare("SELECT s.maturity_date, s.face_value, s.coupon_rates, s.date_of_issue, s.coupon_payable AS frequency 
		        FROM symbol s
		        WHERE s.symbol_id = ?
	    ");
	    $stmt->execute([$symbol_id]);
	    $res = $stmt->fetch(PDO::FETCH_ASSOC); 

	    // get last coupon date
	    $get = $dbh->prepare("SELECT c.date FROM coupon_payable_date c WHERE c.symbol_id = ? AND c.`status` = 1 ORDER BY c.id DESC LIMIT 1");
	    $get->execute([$symbol_id]);
	    $last_coupon_date = $get->fetchColumn();

		$faceValue = $res['face_value'];
		$couponRate = $res['coupon_rates'] / 100;
		$coupon = $faceValue * $couponRate;

		// if no coupon has been paid yet, date_of_issue will be taken as last coupon date
		// substract 1 day to get exact date of next coupn
		if ($last_coupon_date === '' || $last_coupon_date === false) {
			$last_coupon_date = new DateTime($res['date_of_issue']);
			$last_coupon_date->modify('-1 day');
			$last_coupon_date = $last_coupon_date->format('Y-m-d');
		} 

		$issueDate = $last_coupon_date;
		$maturityDate = $res['maturity_date'];
		$frequency = $res['frequency'];

		$settlement = date("Y-m-d");

		$coupons = generateCouponDates($issueDate, $maturityDate, $frequency);
		// echo "<br>coupons => " . implode(", ", $coupons);

		$accrued = accruedInterest($settlement, $issueDate, $coupons, $coupon, $frequency);
		$dirtyPrice = $cleanPrice + $accrued;

		$flows = remainingCashflows($settlement, $coupons, $coupon, $faceValue, $frequency);
		/*foreach($flows as $f)
		{
		    echo "<br>Date: ".$f['date']->format("Y-m-d")." | Cash: ".$f['cash'];
		}*/
		$ytm = calculateYTM($dirtyPrice, $flows, $settlement, $frequency);
		
		$response = [
		    'status' => true,
		    'message' => 'Calculation successful',
		    'data' => [
		        'ytm' => number_format($ytm, 2),
		        'accrued' => number_format($accrued, 2),
		        'dirtyPrice' => number_format($dirtyPrice, 2),
		    ]
		];
		
		header('Content-Type: application/json');
		echo json_encode($response);
		exit();
	}

	function generateCouponDates($issueDate, $maturityDate, $frequency)
	{
	    $dates=[];
	    $months = 12/$frequency;

	    $d = new DateTime($issueDate);
	    $d->modify("+$months months");

	    while($d <= new DateTime($maturityDate))
	    {
	        $dates[]=$d->format("Y-m-d");
	        $d->modify("+$months months");
	    }

	    return $dates;
	}

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

	    $couponPayment = $coupon/$frequency;

	    return $couponPayment*($daysAccrued/$daysPeriod);
	}

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

	function calculateYTM($dirtyPrice,$flows,$settlement,$frequency)
	{
	    $low = 0.00001;
	    $high = 1;

	    for($i=0; $i<100; $i++)
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

	function bondPrice($ytm,$flows,$settlement,$frequency)
	{
	    $pv  =0;
	    $settle = new DateTime($settlement);

	    foreach($flows as $f)
	    {
	        $days = $settle->diff($f['date'])->days;

	        $t = $days/365;

	        $pv += $f['cash']/pow(1+$ytm/$frequency,$frequency*$t);
	    }

	    return $pv;
	}
?>