<?php
include ('../../CONNECTIONS/db.php');

if (isset($_POST['sectorType'])) {
    $sectorType = $_POST['sectorType'];

    // Fetch the latest base divisor for the selected sector
    $query = $dbh->prepare("SELECT base FROM sector_index WHERE sector_type = :sectorType ORDER BY created_date DESC LIMIT 1");
    $query->bindParam(':sectorType', $sectorType, PDO::PARAM_STR);
    $query->execute();
    $res = $query->fetch(PDO::FETCH_ASSOC);

    // Return base value or empty string if not found
    echo isset($res['base']) ? $res['base'] : '';
}
?>
