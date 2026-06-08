<?php
include('../CONNECTIONS/db.php');
session_start();

$cid_no = isset($_POST['cid_no']) ? $_POST['cid_no'] : '';
$threadId = isset($_POST['threadId']) ? $_POST['threadId'] : '';
$relationshipid = isset($_POST['relationshipid']) ? $_POST['relationshipid'] : '';

$query = $dbh->prepare("SELECT count(*) FROM users WHERE cid = :cid AND status = 1");
$query->bindParam(':cid', $cid_no);
$query->execute();

// Get the count of rows
$rowCount = $query->fetchColumn();

if ($rowCount > 1) {
    $stmt = $dbh->prepare("SELECT a.username FROM users a  
        LEFT JOIN role_masters b ON a.role_id = b.id 
        WHERE a.cid = :cid AND a.STATUS = 1 AND b.role_name = 'Client Terminal'");
    $stmt->bindParam(':cid', $cid_no);
    $stmt->execute();

    // Fetch all the usernames
    $usernames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Store the usernames in a session variable for access in the redirected page
    $_SESSION['usernames'] = $usernames;
    $_SESSION['threadId'] = $threadId;
    $_SESSION['relationshipid'] = $relationshipid;

    $response = array(
        'redirect' => true,
        'url' => 'https://cms.rsebl.org.bt/RSEB/issuance_select_role.php'
    );
    echo json_encode($response);
    die();
} else {
    $_SESSION['threadId'] = $threadId;
    $_SESSION['relationshipid'] = $relationshipid;
    echo $rowCount;
}
?>
