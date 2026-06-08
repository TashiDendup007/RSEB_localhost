<?php

if (isset($_GET['webhook_subscribe'])) {
    $threadId = $_GET['threadId'];
    $baseUrl = 'https://app.rsebl.org.bt/webhook/v1/subscribe';
    $token = get_token();

    
    // Ensure $threadId and $token are set
    if (empty($threadId) || empty($token)) {
        echo json_encode(["error" => "Thread ID or access token missing."]);
        http_response_code(400);
        exit();
    }

    
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];
    $postData = json_encode([
        "webhookId" => "rsebprodwebhookId",
        "threadId" => $threadId
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        echo json_encode(["error" => "Request error: " . $error_msg]);
        http_response_code(500);
        exit();
    }

    curl_close($ch);

    if (in_array($httpCode, [200, 201, 202])) {
        // 200 OK, 201 Created, and 202 Accepted are considered successful responses
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(["error" => "Invalid JSON response from the server."]);
            http_response_code(500);
            exit();
        }

        echo json_encode($responseData);
        http_response_code($httpCode); // Return the original HTTP status code
        

        exit();
    } else {
        echo json_encode(["error" => "HTTP error occurred: " . $httpCode]);
        http_response_code($httpCode);
        exit();
    }
}


function get_token()
{
    $authApiUrl = 'https://core.bhutanndi.com/authentication/authenticate';
    $clientId = '680nfodbgp7jifbk4qv7nr1phm'; // Hardcoded Client ID
    $clientSecret = 'sfqpb68j201ad69cv8rn7ff267p1fn1hf4h63mehne653mnigor'; // Hardcoded Client Secret

    $data = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
    ];

    $ch = curl_init($authApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Enable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verify the host

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['error' => curl_error($ch)]);
        curl_close($ch);
        exit();
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($responseData['access_token'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid JSON response or missing access token.']);
        exit();
    }

    return $responseData['access_token'];
}
