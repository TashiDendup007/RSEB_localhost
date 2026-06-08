<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract the access token and verifierApiData from POST data
    
    $accessToken = $_POST['access_token'];
    $verifierApiData = json_decode($_POST['verifierApiData'], true);

    // Verify the access token and verifierApiData are not empty
    if (empty($accessToken) || empty($verifierApiData)) {
        echo json_encode(['error' => 'Missing access token or verifier API data']);
        exit;
    }

    // Make the API call to the verifier endpoint
    $ch = curl_init('https://app.rsebl.org.bt/verifier/v1/proof-request');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verifierApiData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
    } else {
        echo $response;
    }
    curl_close($ch);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
