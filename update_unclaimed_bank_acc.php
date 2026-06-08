<?php  
    date_default_timezone_set("Asia/Thimphu");
    $syste = date('Ymd_His');

    // DB Configurations (could be moved to a config file)
    $dbConfig = [
        'unclaimed' => ["192.168.20.5", "root", "MkmCsop@289123", "unclaimed"],
        'cms'       => ["192.168.10.100", "root", "MkmCsop@289", "cms2"]
    ];

    // Create connections
    $conn1 = new mysqli(...$dbConfig['unclaimed']);
    $conn2 = new mysqli(...$dbConfig['cms']);

    // Check connection errors
    if ($conn1->connect_error) die("Conn1 failed: " . $conn1->connect_error);
    if ($conn2->connect_error) die("Conn2 failed: " . $conn2->connect_error);

    // Bank mapping
    $bankMap = [
        1 => 1020, // BNB 9
        2 => 1010, // BOB 9
        3 => 1050, // BDB 12
        4 => 1030, // DPNB 12
        5 => 1040, // TBANK 9
        6 => 1060  // DK 12
    ];

    // Bank Name Mapping
    $bankNameMap = [
        1 => 'BNBL', // BNB 9
        2 => 'BOBL', // BOB 9
        3 => 'BDBL', // BDB 12
        4 => 'DPNB', // DPNB 12
        5 => 'TBANK', // TBANK 9
        6 => 'DK Bank'  // DK 12
    ];

    // Log file
    $logFile = __DIR__ . "/unclaimed_log/unclaimed_update_log_{$syste}.xls";

    try {
        $conn1->begin_transaction();


        $columnHeader = "DATE_TIME\t CID\t NAME\t BANK_ID\t BANK\t ACCOUNT_NO\t"; 
        $setData = '';
        $replace   = array("\n", "\r\n", "\r");
        $search  = array('', '', ''); 
        $i = 1;

        $result = $conn1->query("
            SELECT DISTINCT u.cid  
            FROM unclaimed_clients_dtls u 
            WHERE u.status IS NULL 
            AND u.bank_acc_check = 0
            LIMIT 500
        ");

        while ($row = $result->fetch_assoc()) {
            $cid_no = $row['cid'];

            // Fetch bank details from CMS DB
            $stmt = $conn2->prepare("
                SELECT a.bank_id, a.bank_account, a.ID, a.f_name, a.l_name, a.acc_type 
                FROM client_account a
                WHERE a.ID = ?
                  AND a.bank_account IS NOT NULL
                  AND a.bank_account <> ''
                ORDER BY a.client_id DESC
                LIMIT 1
            ");
            $stmt->bind_param("s", $cid_no);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();

            if ($res) {
                // Fix comparison operator
                $fl_name = ($res['acc_type'] == 'I') 
                    ? $res['f_name'] . ' ' . $res['l_name'] 
                    : $res['f_name'];

                $bank_id = $bankMap[$res['bank_id']] ?? 0;
                $bank_account = $res['bank_account'];
                $bank_name = $bankNameMap[$res['bank_id']] ?? '';

                $isValid = true;

                // Validate bank account number length
                if (($bank_id == 1010 || $bank_id == 1020 || $bank_id == 1040) && strlen($bank_account) != 9) {
                    $isValid = false;
                } elseif (($bank_id == 1050 || $bank_id == 1030 || $bank_id == 1060) && strlen($bank_account) != 12) {
                    $isValid = false;
                }

                //skip invalid and updating BDB and DPNB
                if ($isValid && !in_array($bank_id, [1030, 1050])) { 

                    // Update unclaimed DB
                    $update = $conn1->prepare("
                        UPDATE unclaimed_clients_dtls 
                        SET name_of_bank = ?, 
                            account_no = ?, 
                            account_holder_cid = ?, 
                            account_holder_name = ?, 
                            status = 'Under Verification'
                        WHERE cid = ? AND status IS NULL
                    ");
                    $update->bind_param("sssss", $bank_id, $bank_account, $cid_no, $fl_name, $cid_no);
                    $update->execute();

                    echo "Update Unclaimed Acc: CID = {$cid_no}, Name = {$fl_name}<br>";

                    $rowData = '';
                    $rowData .= str_replace($search, $replace, date("Y-m-d H:i:s")) . "\t";
                    $rowData .= str_replace($search, $replace, $cid_no) . "\t";
                    $rowData .= str_replace($search, $replace, $fl_name) . "\t";
                    $rowData .= str_replace($search, $replace, $bank_id) . "\t";
                    $rowData .= str_replace($search, $replace, $bank_name) . "\t";
                    $rowData .= "'" . str_replace($search, $replace, $bank_account) . "\t";
                    $setData .= trim($rowData) . "\n"; 
                }

                // Log changes
                /*$logMsg = date("Y-m-d H:i:s") . " | Updated CID: $cid_no | Bank ID: $bank_id | Bank Acc: $bank_account | Name: $fl_name" . PHP_EOL;
                file_put_contents($logFile, $logMsg, FILE_APPEND);*/
            }
            // update status
            $upd_status = $conn1->prepare("
                    UPDATE unclaimed_clients_dtls 
                    SET bank_acc_check = 1 
                    WHERE cid = ? 
            ");
            $upd_status->bind_param("s", $cid_no);
            $upd_status->execute();
        }
        // save in excel
        file_put_contents($logFile, ucwords($columnHeader) . "\n" . $setData . "\n"  . PHP_EOL, FILE_APPEND);

        $conn1->commit();
    } catch (Exception $e) {
        $conn1->rollback();
        echo "<br>Exception => {$e->getMessage()}";
    }

    // Close connections
    $conn1->close();
    $conn2->close();
?>
