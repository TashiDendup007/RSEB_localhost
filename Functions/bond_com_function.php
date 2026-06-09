<?php
// ============================================================
// COMMISSION BRACKETS (Nu.)
// ============================================================
const COMMISSION_BRACKETS = [
    ['min' => 1000,      'max' => 100000,       'comm_min' => 10,    'comm_max' => 100   ],
    ['min' => 100001,    'max' => 250000,       'comm_min' => 105,   'comm_max' => 200   ],
    ['min' => 250001,    'max' => 500000,       'comm_min' => 210,   'comm_max' => 300   ],
    ['min' => 500001,    'max' => 1000000,      'comm_min' => 320,   'comm_max' => 450   ],
    ['min' => 1000001,   'max' => 2500000,      'comm_min' => 475,   'comm_max' => 600   ],
    ['min' => 2500001,   'max' => 5000000,      'comm_min' => 650,   'comm_max' => 750   ],
    ['min' => 5000001,   'max' => 10000000,     'comm_min' => 760,   'comm_max' => 1500  ],
    ['min' => 10000001,  'max' => 25000000,     'comm_min' => 1550,  'comm_max' => 2500  ],
    ['min' => 25000001,  'max' => 50000000,     'comm_min' => 2725,  'comm_max' => 4500  ],
    ['min' => 50000001,  'max' => 100000000,    'comm_min' => 5000,  'comm_max' => 10000 ],
    ['min' => 100000001, 'max' => PHP_INT_MAX,  'comm_min' => 20000, 'comm_max' => null  ],
];

// ============================================================
// CALCULATE COMMISSION — returns float (commission amount only)
// ============================================================
function calculateCommission(float $tradeValue): float
{
    if ($tradeValue <= 0) {
        error_log("Trade value must be greater than 0");
        return 0;
    }

    if ($tradeValue <= 1000) {
        // error_log("Trade value is less than Nu. 1,000");
        return 10;
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
    if ($bracket['comm_max'] === null) {
        return (float) $bracket['comm_min'];
    }

    // -------------------------------------------------------
    // Proportional interpolation within the bracket
    // formula: comm_min + ((tradeValue - bracket_min) / (bracket_max - bracket_min)) * (comm_max - comm_min)
    // -------------------------------------------------------
    
    // error_log("__________ Broker commision ___________");
    // error_log("Trade vale => {$tradeValue}, min => {$bracket['min']}, max => {$bracket['max']}, min => {$bracket['min']}");
    // error_log("comm_min => {$bracket['comm_min']}, comm_max => {$bracket['comm_max']}");
    
    $ratio      = ($tradeValue - $bracket['min']) / ($bracket['max'] - $bracket['min']);
    $commission = $bracket['comm_min'] + ($ratio * ($bracket['comm_max'] - $bracket['comm_min']));

    return (float) round($commission, 2);
}
?>