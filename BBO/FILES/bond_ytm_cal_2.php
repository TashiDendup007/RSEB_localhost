<?php
date_default_timezone_set("Asia/Thimphu");
include ('../../CONNECTIONS/db.php');

class BondCalculator
{
    private $faceValue;
    private $couponRate;
    private $couponFrequency;
    private $couponMonth;
    private $couponDay;
    private $dayCountBasis; // 365 default

    public function __construct(
        $faceValue,
        $couponRate,
        $couponFrequency = 1,
        $couponMonth = 6,
        $couponDay = 5,
        $dayCountBasis = 365
    ) {
        $this->faceValue = $faceValue;
        $this->couponRate = $couponRate;
        $this->couponFrequency = $couponFrequency;
        $this->couponMonth = $couponMonth;
        $this->couponDay = $couponDay;
        $this->dayCountBasis = $dayCountBasis;
    }

    /* ===============================
       PUBLIC METHODS
    =============================== */

    public function calculate($tradeDate, $maturityDate, $cleanPrice)
    {
        $annualCoupon = $this->faceValue * $this->couponRate / 100;
        $couponPerPeriod = $annualCoupon / $this->couponFrequency;

        $lastCoupon = $this->getLastCouponDate($tradeDate);
        $nextCoupon = $this->getNextCouponDate($tradeDate, $maturityDate);

        $accruedInterest = $this->accruedInterest(
            $lastCoupon,
            $tradeDate,
            $couponPerPeriod
        );

        $dirtyPrice = $cleanPrice + $accruedInterest;

        $flows = $this->buildCashFlows(
            $tradeDate,
            $maturityDate,
            $couponPerPeriod
        );

        $ytm = $this->calculateYTM($dirtyPrice, $flows);

        return [
            'last_coupon'       => $lastCoupon,
            'next_coupon'       => $nextCoupon,
            'accrued_interest'  => round($accruedInterest, 6),
            'dirty_price'       => round($dirtyPrice, 2),
            'ytm_percent'       => round($ytm * 100, 4)
        ];
    }

    /* ===============================
       COUPON DATES
    =============================== */

    private function getNextCouponDate($tradeDate, $maturityDate)
    {
        $trade = new DateTime($tradeDate);
        $maturity = new DateTime($maturityDate);

        $intervalMonths = 12 / $this->couponFrequency;

        $coupon = new DateTime(
            $trade->format('Y') . "-{$this->couponMonth}-{$this->couponDay}"
        );

        while ($coupon <= $trade) {
            $coupon->modify("+$intervalMonths months");
        }

        return ($coupon > $maturity)
            ? null
            : $coupon->format('Y-m-d');
    }

    private function getLastCouponDate($tradeDate)
    {
        $trade = new DateTime($tradeDate);
        $intervalMonths = 12 / $this->couponFrequency;

        $coupon = new DateTime(
            $trade->format('Y') . "-{$this->couponMonth}-{$this->couponDay}"
        );

        while ($coupon > $trade) {
            $coupon->modify("-$intervalMonths months");
        }

        return $coupon->format('Y-m-d');
    }

    /* ===============================
       ACCRUED INTEREST
    =============================== */

    private function accruedInterest($lastCoupon, $tradeDate, $couponPerPeriod)
    {
        $last  = new DateTime($lastCoupon);
        $trade = new DateTime($tradeDate);

        $daysAccrued = $last->diff($trade)->days;

        return ($couponPerPeriod * $daysAccrued)
            / ($this->dayCountBasis / $this->couponFrequency);
    }

    /* ===============================
       CASH FLOWS
    =============================== */

    private function buildCashFlows($tradeDate, $maturityDate, $couponPerPeriod)
    {
        $flows = [];

        $trade = new DateTime($tradeDate);
        $maturity = new DateTime($maturityDate);

        $intervalMonths = 12 / $this->couponFrequency;

        $coupon = new DateTime($this->getNextCouponDate($tradeDate, $maturityDate));

        while ($coupon && $coupon <= $maturity) {

            $t = $trade->diff($coupon)->days / $this->dayCountBasis;

            $amount = $couponPerPeriod;

            if ($coupon == $maturity) {
                $amount += $this->faceValue;
            }

            $flows[] = [$t, $amount];

            $coupon->modify("+$intervalMonths months");
        }

        return $flows;
    }

    /* ===============================
       NEWTON YTM
    =============================== */

    private function calculateYTM($price, $flows, $guess = 0.10)
    {
        $ytm = $guess;
        $maxIterations = 100;
        $tolerance = 1e-10;

        for ($i = 0; $i < $maxIterations; $i++) {

            $f = 0.0;
            $df = 0.0;

            foreach ($flows as [$t, $cf]) {

                $discount = pow(1 + $ytm / $this->couponFrequency,
                                $this->couponFrequency * $t);

                $f += $cf / $discount;

                $df -= ($t * $cf) /
                       pow(1 + $ytm / $this->couponFrequency,
                           $this->couponFrequency * $t + 1);
            }

            $f -= $price;

            if (abs($f) < $tolerance) {
                return $ytm;
            }

            $ytm -= $f / $df;
        }

        return $ytm;
    }
}

?>