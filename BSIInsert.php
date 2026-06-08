<?php
    declare(strict_types=1);

    date_default_timezone_set("Asia/Thimphu");

    include './CONNECTIONS/db.php'; 
    include 'f.php';

    // Fetch total market capitalization
    $query = $dbh->prepare("
        SELECT SUM(s.paid_up_shares * m.market_price) 
        FROM market_price m
        JOIN symbol s ON m.symbol_id = s.symbol_id
        WHERE s.security_type = 'OS' AND s.status = 1
    ");
    $query->execute();
    $sum = (float) $query->fetchColumn();

    // Fetch latest base index value
    $query1 = $dbh->prepare("
        SELECT m.base FROM market_index m 
        ORDER BY m.created_date DESC 
        LIMIT 1
    ");
    $query1->execute();
    $base = (float) $query1->fetchColumn();

    // Calculate market index
    $sumround = round($sum, 2);
    $index = round($sumround / $base, 2);

    // Insert new market index record
    $save = $dbh->prepare("
        INSERT INTO market_index (base, m_index, market_cap) 
        VALUES (:base, :index, :market_cap)
    ");
    $save->bindParam(':base', $base);
    $save->bindParam(':index', $index);
    $save->bindParam(':market_cap', $sum);

    if ($save->execute()) {
        echo "INSERTED";
    } else {
        echo "ERROR";
    }

?>