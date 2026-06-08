<?php

class BondYTMCalculator {
    
    /**
     * Calculate YTM with exact date consideration
     * 
     * @param float $faceValue - Bond face/par value
     * @param float $couponRate - Annual coupon rate as decimal
     * @param float $currentPrice - Current market price (clean price)
     * @param string $settlementDate - Settlement date (YYYY-MM-DD)
     * @param string $maturityDate - Maturity date (YYYY-MM-DD)
     * @param string $dayCountConvention - 'actual/actual' or '30/360'
     * @return array - YTM and calculation details
     */
    public function calculateYTMWithDates(
        $faceValue,
        $couponRate,
        $currentPrice,
        $settlementDate,
        $maturityDate,
        $dayCountConvention = 'actual/actual'
    ) {
        $settlement = new DateTime($settlementDate);
        $maturity = new DateTime($maturityDate);
        
        // Calculate time to maturity in years
        $yearsToMaturity = $this->calculateYearsToMaturity(
            $settlement, 
            $maturity, 
            $dayCountConvention
        );
        
        // Calculate number of whole years (periods)
        $wholePeriods = floor($yearsToMaturity);
        $fractionalPeriod = $yearsToMaturity - $wholePeriods;
        
        // Annual coupon payment
        $couponPayment = $faceValue * $couponRate;
        
        // Calculate accrued interest
        $lastCouponDate = $this->getLastCouponDate($settlement, $maturity, $couponRate);
        $accruedInterest = $this->calculateAccruedInterest(
            $faceValue,
            $couponRate,
            $lastCouponDate,
            $settlement,
            $dayCountConvention
        );
        
        // Dirty price = Clean price + Accrued interest
        $dirtyPrice = $currentPrice + $accruedInterest;
        
        // Initial guess for YTM
        $ytm = ($couponPayment + ($faceValue - $dirtyPrice) / $yearsToMaturity) 
               / (($faceValue + $dirtyPrice) / 2);
        
        // Newton-Raphson iteration
        $maxIterations = 100;
        $tolerance = 0.00001;
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $price = $this->calculateBondPriceWithFraction(
                $faceValue,
                $couponPayment,
                $ytm,
                $wholePeriods,
                $fractionalPeriod
            );
            
            $priceDerivative = $this->calculatePriceDerivativeWithFraction(
                $faceValue,
                $couponPayment,
                $ytm,
                $wholePeriods,
                $fractionalPeriod
            );
            
            $ytmNew = $ytm - ($price - $dirtyPrice) / $priceDerivative;
            
            if (abs($ytmNew - $ytm) < $tolerance) {
                return [
                    'ytm' => $ytmNew,
                    'ytm_percent' => round($ytmNew * 100, 4),
                    'years_to_maturity' => $yearsToMaturity,
                    'whole_periods' => $wholePeriods,
                    'fractional_period' => $fractionalPeriod,
                    'accrued_interest' => $accruedInterest,
                    'clean_price' => $currentPrice,
                    'dirty_price' => $dirtyPrice,
                    'iterations' => $i + 1
                ];
            }
            
            $ytm = $ytmNew;
        }
        
        return [
            'ytm' => $ytm,
            'ytm_percent' => round($ytm * 100, 4),
            'years_to_maturity' => $yearsToMaturity,
            'whole_periods' => $wholePeriods,
            'fractional_period' => $fractionalPeriod,
            'accrued_interest' => $accruedInterest,
            'clean_price' => $currentPrice,
            'dirty_price' => $dirtyPrice,
            'iterations' => $maxIterations,
            'converged' => false
        ];
    }
    
    /**
     * Calculate years to maturity based on day count convention
     */
    private function calculateYearsToMaturity($settlement, $maturity, $convention) {
        if ($convention === 'actual/actual') {
            // Actual/Actual: Count actual days and divide by actual days in year
            $interval = $settlement->diff($maturity);
            $days = $interval->days;
            return $days / 365.25; // Using 365.25 to account for leap years
        } else if ($convention === '30/360') {
            // 30/360: Assume 30 days per month, 360 days per year
            $y1 = (int)$settlement->format('Y');
            $m1 = (int)$settlement->format('m');
            $d1 = min((int)$settlement->format('d'), 30);
            
            $y2 = (int)$maturity->format('Y');
            $m2 = (int)$maturity->format('m');
            $d2 = min((int)$maturity->format('d'), 30);
            
            $days = 360 * ($y2 - $y1) + 30 * ($m2 - $m1) + ($d2 - $d1);
            return $days / 360;
        }
        
        return 0;
    }
    
    /**
     * Get the last coupon payment date before settlement
     */
    private function getLastCouponDate($settlement, $maturity, $couponRate) {
        // Assuming annual coupons on the same day/month as maturity
        $maturityDay = (int)$maturity->format('d');
        $maturityMonth = (int)$maturity->format('m');
        $settlementYear = (int)$settlement->format('Y');
        
        // Try to create last coupon date in settlement year
        $lastCoupon = DateTime::createFromFormat(
            'Y-m-d',
            $settlementYear . '-' . str_pad($maturityMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($maturityDay, 2, '0', STR_PAD_LEFT)
        );
        
        // If that date is after settlement, go back one year
        if ($lastCoupon > $settlement) {
            $lastCoupon->modify('-1 year');
        }
        
        return $lastCoupon;
    }
    
    /**
     * Calculate accrued interest
     */
    private function calculateAccruedInterest($faceValue, $couponRate, $lastCouponDate, $settlement, $convention) {
        $nextCouponDate = clone $lastCouponDate;
        $nextCouponDate->modify('+1 year');
        
        if ($convention === 'actual/actual') {
            $daysSinceLastCoupon = $lastCouponDate->diff($settlement)->days;
            $daysInCouponPeriod = $lastCouponDate->diff($nextCouponDate)->days;
            $fraction = $daysSinceLastCoupon / $daysInCouponPeriod;
        } else { // 30/360
            $fraction = $this->calculate30360Fraction($lastCouponDate, $settlement);
        }
        
        return ($faceValue * $couponRate) * $fraction;
    }
    
    private function calculate30360Fraction($startDate, $endDate) {
        $y1 = (int)$startDate->format('Y');
        $m1 = (int)$startDate->format('m');
        $d1 = min((int)$startDate->format('d'), 30);
        
        $y2 = (int)$endDate->format('Y');
        $m2 = (int)$endDate->format('m');
        $d2 = min((int)$endDate->format('d'), 30);
        
        $days = 360 * ($y2 - $y1) + 30 * ($m2 - $m1) + ($d2 - $d1);
        return $days / 360;
    }
    
    /**
     * Calculate bond price with fractional period
     */
    private function calculateBondPriceWithFraction($faceValue, $couponPayment, $yield, $wholePeriods, $fractionalPeriod) {
        $price = 0;
        
        // First coupon (partial period)
        if ($fractionalPeriod > 0) {
            $price += $couponPayment / pow(1 + $yield, 1 - $fractionalPeriod);
            
            // Remaining coupons
            for ($t = 2; $t <= $wholePeriods + 1; $t++) {
                $price += $couponPayment / pow(1 + $yield, $t - $fractionalPeriod);
            }
            
            // Face value
            $price += $faceValue / pow(1 + $yield, $wholePeriods + 1 - $fractionalPeriod);
        } else {
            // Standard calculation when on coupon date
            for ($t = 1; $t <= $wholePeriods; $t++) {
                $price += $couponPayment / pow(1 + $yield, $t);
            }
            $price += $faceValue / pow(1 + $yield, $wholePeriods);
        }
        
        return $price;
    }
    
    /**
     * Calculate derivative with fractional period
     */
    private function calculatePriceDerivativeWithFraction($faceValue, $couponPayment, $yield, $wholePeriods, $fractionalPeriod) {
        $derivative = 0;
        
        if ($fractionalPeriod > 0) {
            // First coupon
            $t1 = 1 - $fractionalPeriod;
            $derivative -= ($t1 * $couponPayment) / pow(1 + $yield, $t1 + 1);
            
            // Remaining coupons
            for ($t = 2; $t <= $wholePeriods + 1; $t++) {
                $period = $t - $fractionalPeriod;
                $derivative -= ($period * $couponPayment) / pow(1 + $yield, $period + 1);
            }
            
            // Face value
            $finalPeriod = $wholePeriods + 1 - $fractionalPeriod;
            $derivative -= ($finalPeriod * $faceValue) / pow(1 + $yield, $finalPeriod + 1);
        } else {
            for ($t = 1; $t <= $wholePeriods; $t++) {
                $derivative -= ($t * $couponPayment) / pow(1 + $yield, $t + 1);
            }
            $derivative -= ($wholePeriods * $faceValue) / pow(1 + $yield, $wholePeriods + 1);
        }
        
        return $derivative;
    }
}

// ===== EXAMPLE USAGE =====

$calculator = new BondYTMCalculator();

// Your example:
// Face value = 1000
// Rate = 10%
// Issue date = June 6, 2025
// Maturity date = June 6, 2035
// Current price = ??? (let's assume 950 for demonstration)
// Today (settlement) = February 12, 2026

echo "=== Bond YTM Calculation with Exact Dates ===\n\n";

$result = $calculator->calculateYTMWithDates(
    $faceValue = 1000,
    $couponRate = 0.10,
    $currentPrice = 950,
    $settlementDate = '2026-02-12',
    $maturityDate = '2035-06-06',
    $dayCountConvention = 'actual/actual'
);

echo "Bond Details:\n";
echo "- Face Value: $" . number_format(1000, 2) . "\n";
echo "- Coupon Rate: 10%\n";
echo "- Annual Coupon: $" . number_format(100, 2) . "\n";
echo "- Maturity Date: June 6, 2035\n";
echo "- Settlement Date: February 12, 2026\n";
echo "- Clean Price: $" . number_format($result['clean_price'], 2) . "\n\n";

echo "Calculation Details:\n";
echo "- Years to Maturity: " . round($result['years_to_maturity'], 4) . " years\n";
echo "- Whole Periods: " . $result['whole_periods'] . "\n";
echo "- Fractional Period: " . round($result['fractional_period'], 4) . "\n";
echo "- Accrued Interest: $" . number_format($result['accrued_interest'], 2) . "\n";
echo "- Dirty Price: $" . number_format($result['dirty_price'], 2) . "\n";
echo "- Iterations: " . $result['iterations'] . "\n\n";

echo "RESULT:\n";
echo "YTM = " . $result['ytm_percent'] . "%\n\n";

// Compare with simple calculation (without exact dates)
echo "=== Comparison: Simple vs Exact Date Method ===\n";
echo "Simple (9 years): Would give different YTM\n";
echo "Exact (9.31 years): " . $result['ytm_percent'] . "%\n";

?>