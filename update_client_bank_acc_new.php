<?php
date_default_timezone_set("Asia/Thimphu");
$syste = date('Ymd_His');
$conn = new mysqli("localhost", "root", "root", "cms2_11july2025");

// Log file path
$logFile = __DIR__ . "/bank_update_log_{$syste}.txt";

try {
    $conn->begin_transaction();

    $result = $conn->query("
        SELECT DISTINCT c1.ID, c1.bank_account, c1.bank_id
        FROM client_account c1
        JOIN (
            SELECT ca.ID,
                   (SELECT bank_account 
                    FROM client_account 
                    WHERE ID = ca.ID 
                      AND bank_account IS NOT NULL 
                      AND bank_account <> ''
                      AND acc_type = 'I'
                    ORDER BY bank_account LIMIT 1) AS bank_account
            FROM client_account ca
            GROUP BY ca.ID
            HAVING COUNT(*) > 1
        ) c2 ON c1.ID = c2.ID 
         AND c1.bank_account = c2.bank_account
    ");

    while ($row = $result->fetch_assoc()) {
        $id = $row['ID'];
        $bankAcc = $row['bank_account'];
        $bankId = $row['bank_id'];

        // Update rows with missing bank account
        $stmt = $conn->prepare("
            UPDATE client_account
            SET bank_account = ?, bank_id = ?, bank_account_type = 'Saving Account'
            WHERE ID = ? AND (bank_account IS NULL OR bank_account = '')
        ");
        $stmt->bind_param("sss", $bankAcc, $bankId, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $logMsg = date("Y-m-d H:i:s") . " | Updated ID: $id | Bank ID: $bankId | Bank Acc: $bankAcc" . PHP_EOL;
            file_put_contents($logFile, $logMsg, FILE_APPEND);
            echo "<br>Updated ID = {$id}, Acc = {$bankAcc}, Bank Id = {$bankId} <br>";
        }
    }

    $conn->commit();
    echo "<br>✅ All updates complete. Log saved at: $logFile";

} catch (Exception $e) {
    $conn->rollback();
    echo "<br>❌ Exception => {$e->getMessage()}";
}
?>
