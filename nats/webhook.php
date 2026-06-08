<?php

// webhook.php
include('../CONNECTIONS/db.php');
session_start();
date_default_timezone_set('Asia/Thimphu');

try {
    // Allow CORS
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");

    // Allow only POST requests
    file_put_contents('webhook.log', date('Y-m-d H:i:s') . " - " . file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(array('error' => 'Only POST requests are allowed'));
        exit;
    }

    // Retrieve the JSON payload from the request body
    $cleaned_body = file_get_contents('php://input');
    $data = json_decode($cleaned_body, true);

    // Check if the data is valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON payload");
    }

    // Extract the requested presentation and revealed attributes
    $requested_presentation = isset($data['requested_presentation']) ? $data['requested_presentation'] : array();
    $revealed_attrs = isset($requested_presentation['revealed_attrs']) ? $requested_presentation['revealed_attrs'] : array();
    $thid = isset($data['thid']) ? $data['thid'] : array();

    // Get the ID Number from the revealed attributes
    $cid = isset($revealed_attrs['ID Number'][0]['value']) ? $revealed_attrs['ID Number'][0]['value'] : null;

    $data = array(
        'cid_no' => $cid,
        'thread_id' => $thid
    );

    // If ID Number is found, send a POST request to nats_redirect.php with CID in the headers
    if ($cid) {
        $ch = curl_init('https://cms.rsebl.org.bt/RSEB/nats/nats_redirect.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
        
        // Capture cURL error
        $response = curl_exec($ch);
        
        if ($response === false) {
            // Capture and handle cURL errors
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: $error");
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Handle the response from nats_redirect.php
        if ($httpCode == 200) {
            // Decode the JSON response
            $decodedResponse = json_decode($response, true);
            
            // Check if decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(array("error" => "Invalid JSON response"));
                exit();
            }

            // Extract the URL from the decoded response
            $url = isset($decodedResponse['url']) ? $decodedResponse['url'] : null;
            $payload = isset($decodedResponse['uuid']) ? $decodedResponse['uuid'] : null;

            $data = array(
                'payload' => $payload
            );
            
            // Encode data into URL parameters
            $queryString = http_build_query($data);
            
            // Define the redirect URL with parameters
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $redirectUrl = "{$url}?{$queryString}";

            header('HTTP/1.1 202 Accepted');
            header('Content-Type: application/json');

            // Create the response array
            $response = array(
                "statusCode" => "202",
                "statusDescription" => "Accepted"
            );

            // Output the JSON-encoded response
            echo json_encode($response);

            exit();
        } else {
            throw new Exception("Failed to redirect: HTTP $httpCode");
        }
    } else {
        throw new Exception("ID Number not found");
    }
} catch (Exception $e) {
    // Handle any errors
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array("statusCode" => "400", "statusDescription" => $e->getMessage()));
}