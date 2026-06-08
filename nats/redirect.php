<?php
error_reporting(1);
include('../CONNECTIONS/db.php');
session_start();
header("Access-Control-Allow-Origin: https://cms.rsebl.org.bt/");
date_default_timezone_set('Asia/Thimphu');

$defaultPath = 'https://cms.rsebl.org.bt/RSEB/access.php?err=9';

if (isset($_POST['threadId'])) {
    $thread_id = isset($_POST['threadId']) ? $_POST['threadId'] : '';

    $query = $dbh->prepare("SELECT sessfield, username FROM ndi_login_counter WHERE threadId = :thread_id LIMIT 1");
    $query->bindParam(':thread_id', $thread_id);
    $query->execute();

    $value = $query->fetch(PDO::FETCH_ASSOC);

    if ($value) {
        $jsonData = isset($value['sessfield']) ? $value['sessfield'] : '{}'; // Default to empty JSON object
        $data = json_decode($jsonData, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (empty($value['username'])) {
                // If no username, store usernames in session and set path if available
                $_SESSION['usernames'] = isset($data['usernames']) ? $data['usernames'] : [];
            } else {
                // Otherwise, assign session variables based on the JSON data
                $_SESSION['sess_user_id'] = isset($data['sess_user_id']) ? $data['sess_user_id'] : null;
                $_SESSION['sess_part_code'] = isset($data['sess_part_code']) ? $data['sess_part_code'] : null;
                $_SESSION['sess_username'] = isset($data['sess_username']) ? $data['sess_username'] : null;
                $_SESSION['sess_userrole'] = isset($data['sess_userrole']) ? $data['sess_userrole'] : null;
                $_SESSION['sess_log_check'] = isset($data['sess_log_check']) ? $data['sess_log_check'] : null;
                $_SESSION['isNRB'] = isset($data['isNRB']) ? $data['isNRB'] : null;
                $_SESSION['timeout'] = isset($data['timeout']) ? $data['timeout'] : null;
            }

            $path = isset($data['path']) ? $data['path'] : $defaultPath;
            echo $path;
            exit();
        }
    }
    echo $defaultPath;
}
?>
