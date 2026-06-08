<?php

class BondCalculator
{
    public $face;
    public $couponRate;
    public $frequency;
    public $issueDate;
    public $maturityDate;
    public $dayCount;

    function __construct($face,$couponRate,$frequency,$issue,$maturity,$dayCount="ACT/365")
    {
        $this->face=$face;
        $this->couponRate=$couponRate;
        $this->frequency=$frequency;
        $this->issueDate=$issue;
        $this->maturityDate=$maturity;
        $this->dayCount=$dayCount;
    }

    function couponAmount()
    {
        return ($this->face*$this->couponRate)/$this->frequency;
    }

    function generateCoupons()
    {
        $dates=[];
        $months=12/$this->frequency;

        $d=new DateTime($this->issueDate);
        $d->modify("+$months months");

        while($d<=new DateTime($this->maturityDate))
        {
            $dates[]=$d->format("Y-m-d");
            $d->modify("+$months months");
        }

        return $dates;
    }

    function daysBetween($d1,$d2)
    {
        if($this->dayCount=="30/360")
        {
            $a=date_create($d1);
            $b=date_create($d2);

            $d1d=min(30,$a->format("d"));
            $d2d=min(30,$b->format("d"));

            return (($b->format("Y")-$a->format("Y"))*360)
            +(($b->format("m")-$a->format("m"))*30)
            +($d2d-$d1d);
        }

        return (new DateTime($d1))->diff(new DateTime($d2))->days;
    }

    function accruedInterest($settlement)
    {
        $coupons=$this->generateCoupons();

        $last=$this->issueDate;
        $next=null;

        foreach($coupons as $c)
        {
            if($c>$settlement)
            {
                $next=$c;
                break;
            }
            $last=$c;
        }

        if(!$next) return 0;

        $daysAcc=$this->daysBetween($last,$settlement);
        $daysPer=$this->daysBetween($last,$next);

        return $this->couponAmount()*($daysAcc/$daysPer);
    }

    function cashflows($settlement)
    {
        $flows=[];
        $coupons=$this->generateCoupons();
        $coupon=$this->couponAmount();

        foreach($coupons as $c)
        {
            if($c>$settlement)
            {
                $cash=$coupon;

                if($c==$this->maturityDate)
                    $cash+=$this->face;

                $flows[]=[
                    "date"=>$c,
                    "cash"=>$cash
                ];
            }
        }

        return $flows;
    }

    function yearFraction($settlement,$date)
    {
        $days=$this->daysBetween($settlement,$date);

        if($this->dayCount=="30/360")
            return $days/360;

        return $days/365;
    }

    function priceFromYTM($ytm,$settlement)
    {
        $pv=0;
        $flows=$this->cashflows($settlement);

        foreach($flows as $f)
        {
            $t=$this->yearFraction($settlement,$f["date"]);

            $pv += $f["cash"]/pow(1+$ytm/$this->frequency,$this->frequency*$t);
        }

        return $pv;
    }

    function solveYTM($dirtyPrice,$settlement)
    {
        $low=0.00001;
        $high=1;

        for($i=0;$i<100;$i++)
        {
            $mid=($low+$high)/2;

            $price=$this->priceFromYTM($mid,$settlement);

            if($price>$dirtyPrice)
                $low=$mid;
            else
                $high=$mid;
        }

        return $mid;
    }

    function duration($ytm,$settlement)
    {
        $flows=$this->cashflows($settlement);
        $price=$this->priceFromYTM($ytm,$settlement);

        $sum=0;

        foreach($flows as $f)
        {
            $t=$this->yearFraction($settlement,$f["date"]);

            $pv=$f["cash"]/pow(1+$ytm/$this->frequency,$this->frequency*$t);

            $sum += $t*$pv;
        }

        return $sum/$price;
    }

    function modifiedDuration($ytm,$settlement)
    {
        $mac=$this->duration($ytm,$settlement);

        return $mac/(1+$ytm/$this->frequency);
    }

    function convexity($ytm,$settlement)
    {
        $flows=$this->cashflows($settlement);
        $price=$this->priceFromYTM($ytm,$settlement);

        $sum=0;

        foreach($flows as $f)
        {
            $t=$this->yearFraction($settlement,$f["date"]);

            $pv=$f["cash"]/pow(1+$ytm/$this->frequency,$this->frequency*$t);

            $sum += $pv*$t*$t;
        }

        return $sum/$price;
    }
}

##############################
# Example Usage
##############################

$bond=new BondCalculator(
    1000,
    0.10,
    1,
    "2025-06-06",
    "2035-06-05",
    "ACT/365"
);

$cleanPrice=900;

$settlement=date("Y-m-d");

$accrued=$bond->accruedInterest($settlement);

$dirtyPrice=$cleanPrice+$accrued;

$ytm=$bond->solveYTM($dirtyPrice,$settlement);

$macDur=$bond->duration($ytm,$settlement);

$modDur=$bond->modifiedDuration($ytm,$settlement);

$convexity=$bond->convexity($ytm,$settlement);

echo "Accrued Interest: ".round($accrued,6).PHP_EOL;
echo "Dirty Price: ".round($dirtyPrice,6).PHP_EOL;
echo "YTM: ".round($ytm*100,6)."%".PHP_EOL;
echo "Macaulay Duration: ".round($macDur,6).PHP_EOL;
echo "Modified Duration: ".round($modDur,6).PHP_EOL;
echo "Convexity: ".round($convexity,6).PHP_EOL;

?>