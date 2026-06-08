<?php
// Set timezone
date_default_timezone_set("Asia/Thimphu");

// Include database connection and functions
include('./CONNECTIONS/db.php'); 
include('f.php');

header('Content-Type: application/json');

$response = [];

try {
    // Fetch distinct sectors from the symbol table where security_type is 'OS'
    $sectorQuery = $dbh->prepare("SELECT DISTINCT sector FROM symbol WHERE sector IS NOT NULL AND security_type = 'OS' AND status = 1");
    $sectorQuery->execute();
    $sectors = $sectorQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sectors as $sector) {
        $sectorType = $sector['sector'];

        // Fetch market capitalization for the current sector
        $query = $dbh->prepare("SELECT SUM(s.paid_up_shares * m.market_price) AS sum 
                                FROM market_price m 
                                JOIN symbol s ON m.symbol_id = s.symbol_id 
                                WHERE s.security_type = 'OS' AND s.status = 1 AND s.sector = :sectorType");
        $query->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
        $query->execute();
        $mcap = $query->fetch(PDO::FETCH_ASSOC);
        
        // Check if sum is set and not null, otherwise default to 0
        $sum = isset($mcap['sum']) ? $mcap['sum'] : 0;

        // Calculate base as Market Capitalization / 100
        $base = ($sum > 0) ? round($sum / 100, 2) : 0; // Ensure base is never zero
         
        // Calculate Sector Index
        $SectorIndex = round($sum / $base, 2);

        // Insert into sector_index table
        $save = $dbh->prepare("INSERT INTO sector_index (sector_type, base, s_index, market_cap, created_date) 
                               VALUES (:sectorType, :base, :s_index, :market_cap, NOW())");
        $save->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
        $save->bindParam(':base', $base, PDO::PARAM_STR);
        $save->bindParam(':s_index', $SectorIndex, PDO::PARAM_STR);
        $save->bindParam(':market_cap', $sum, PDO::PARAM_STR);

        if ($save->execute()) {
            $response[] = ["sector" => $sectorType, "status" => "INSERTED"];
        } else {
            $response[] = ["sector" => $sectorType, "status" => "ERROR"];
        }
    }
} catch (PDOException $e) {
    $response["error"] = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $response["error"] = "General Error: " . $e->getMessage();
}

echo json_encode($response);
?>
