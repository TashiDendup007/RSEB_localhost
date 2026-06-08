<?php  
function calculateCommission(float $tradeValue): float  // ← changed from array to float
{
    if ($tradeValue <= 0) {
        error_log("Trade value must be greater than 0");
        return 0;
    }

    if ($tradeValue <= 1000) {
        error_log("Trade value must be greater than Nu. 1,000");
        return 0;
    }

    $bracket = null;
    foreach (COMMISSION_BRACKETS as $row) {
        if ($tradeValue >= $row['min'] && $tradeValue <= $row['max']) {
            $bracket = $row;
            break;
        }
    }

    if (!$bracket) {
        error_log("No matching commission bracket for trade value: " . $tradeValue);
        return 0;
    }

    // Top bracket (>100M) — flat minimum, no upper cap
    // Change comm_min to comm_max if you want to charge maximum instead
    $commission = ($bracket['comm_max'] === null)
        ? $bracket['comm_min']
        : $bracket['comm_min'];

    return (float) $commission;
}
?>