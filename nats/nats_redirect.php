<?php
include('../CONNECTIONS/db.php');
session_start();
header("Access-Control-Allow-Origin: https://cms.rsebl.org.bt/");
date_default_timezone_set('Asia/Thimphu');

// Check if thread_id is set in the session, if not, initialize it
if (!isset($_SESSION['thread_id']) && isset($_POST['thread_id'])) {
    $_SESSION['thread_id'] = $_POST['thread_id'];
}



// Use the session-based thread_id or the one from POST data if session doesn't have it yet
$thread_id = isset($_SESSION['thread_id']) ? $_SESSION['thread_id'] : (isset($_POST['thread_id']) ? $_POST['thread_id'] : '');

// Handle login with CID
if (isset($_POST['cid_no'])) {
    $cid_no = $_POST['cid_no'];


    // Prepare and execute the statement
    $stmt = $dbh->prepare("SELECT * FROM users WHERE cid = :cid AND status = 1");
    $stmt->bindParam(':cid', $cid_no);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rowCount = count($rows);

    if ($rowCount > 1) {
        // Multiple users found, handle role selection
        $_SESSION['usernames'] = array();
        foreach ($rows as $r) {
            $_SESSION['usernames'][] = $r['username'];
        }

        $absoluteSelectRoleUrl = buildUrl('/RSEB/select_role.php');
        $parameters = json_encode(array('usernames' => $_SESSION['usernames'], 'path' => $absoluteSelectRoleUrl));

        logLoginAttempt($dbh, $cid_no, $thread_id, null, $parameters);

        jsonResponse(200, array('url' => $absoluteSelectRoleUrl, 'uuid' => generateUuid($cid_no)));

    } elseif ($rowCount == 1) {
        // Single user found, handle direct login
        $row = $rows[0];

        // Initialize session variables for the user
        $_SESSION['users'][] = array(
            'sess_user_id' => isset($row['user_id']) ? $row['user_id'] : 0,
            'sess_part_code' => isset($row['participant_code']) ? $row['participant_code'] : 0,
            'sess_username' => isset($row['username']) ? $row['username'] : 0,
            'sess_userrole' => isset($row['role_id']) ? $row['role_id'] : 0,
            'sess_log_check' => isset($row['log_check']) ? $row['log_check'] : 0,
            'isNRB' => isset($row['isNRB']) ? $row['isNRB'] : 'N',
            'timeout' => time(),
        );

        // Define role-based redirects
        $roleRedirects = array(
            '1' => '/RSEB/ADM/FILES/Admin-dashboard.php',
            '2' => '/RSEB/BBO/FILES/bbo-landing.php',
            '3' => '/RSEB/CDS-CSS/FILES/cds-css-landing.php',
            '4' => '/RSEB/TE/FILES/te-landing.php',
            '5' => '/RSEB/IPO/FILES/ipo-landing.php',
            '6' => '/RSEB/PTRS/FILES/ptrs_landing.php',
            '7' => '/RSEB/CustodialService/FILES/custodial_landing.php',
        );

        $relativeRedirectUrl = isset($roleRedirects[$row['role_id']]) ? $roleRedirects[$row['role_id']] : '/access.php?err=1';
        $parameters = json_encode(array_merge($_SESSION['users'][0], array('path' => $relativeRedirectUrl)));

        logLoginAttempt($dbh, isset($row['cid']) ? $row['cid'] : 0, $thread_id, isset($row['username']) ? $row['username'] : null, $parameters);

        jsonResponse(200, array('url' => $relativeRedirectUrl, 'uuid' => generateUuid($row['cid'])));
    } else {
        // No matching users, redirect with error
        $absoluteRedirectUrl = buildUrl('/access.php?err=1');
        jsonResponse(200, array('url' => $absoluteRedirectUrl));
    }
}
// Handle username selection
elseif (isset($_POST['selectedUsername'])) {

    $username = isset($_POST['selectedUsername']) ? $_POST['selectedUsername'] : '';

    $stmt = $dbh->prepare("SELECT * FROM users WHERE username = :username AND status = 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($row)) {

        $_SESSION['sess_user_id'] = isset($row[0]['user_id']) ? $row[0]['user_id'] : 0;
        $_SESSION['sess_part_code'] = isset($row[0]['participant_code']) ? $row[0]['participant_code'] : 0;
        $_SESSION['sess_username'] = isset($row[0]['username']) ? $row[0]['username'] : 0;
        $_SESSION['sess_userrole'] = isset($row[0]['role_id']) ? $row[0]['role_id'] : 0;
        $_SESSION['sess_log_check'] = isset($row[0]['log_check']) ? $row[0]['log_check'] : 0;
        $_SESSION['isNRB'] = isset($row[0]['isNRB']) ? $row[0]['isNRB'] : 'N';
        $_SESSION['timeout'] = time();

        // Define role-based redirects
        $roleRedirects = array(
            '1' => '/RSEB/ADM/FILES/Admin-dashboard.php',
            '2' => '/RSEB/BBO/FILES/bbo-landing.php',
            '3' => '/RSEB/CDS-CSS/FILES/cds-css-landing.php',
            '4' => '/RSEB/TE/FILES/te-landing.php',
            '5' => '/RSEB/IPO/FILES/ipo-landing.php',
            '6' => '/RSEB/PTRS/FILES/ptrs_landing.php',
            '7' => '/RSEB/CustodialService/FILES/custodial_landing.php',
        );

        $relativeRedirectUrl = isset($roleRedirects[$_SESSION['sess_userrole']]) ? $roleRedirects[$_SESSION['sess_userrole']] : '/access.php?err=1';
        echo $relativeRedirectUrl;
        die();
    } else {
        $absoluteRedirectUrl = buildUrl('/access.php?err=1');
        jsonResponse(200, array('url' => $absoluteRedirectUrl));
    }
}

// Helper function to build a URL
function buildUrl($relativePath) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . $relativePath;
}

// Helper function to log login attempts
function logLoginAttempt($dbh, $cid, $thread_id, $username, $parameters) {
    $currentDateTime = date('Y-m-d H:i:s');
    $uuid = generateUuid($cid);

    $insstmt = $dbh->prepare("INSERT INTO ndi_login_counter(cid, datetime, endpoint, username, sessfield, uuid, threadId) 
                              VALUES(:cid, :datetime, :endpoint, :username, :sessfield, :uuid, :thread_id)");
    $insstmt->execute(array(
        ':cid' => $cid,
        ':datetime' => $currentDateTime,
        ':endpoint' => 'NDI',
        ':username' => $username,
        ':sessfield' => $parameters,
        ':uuid' => $uuid,
        ':thread_id' => $thread_id
    ));
}

// Helper function to generate UUID
function generateUuid($cid) {
    return $cid . rand(0, 10000);
}

// Helper function to send JSON response
function jsonResponse($statusCode, $data) {
    http_response_code($statusCode);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit();
}
?>
