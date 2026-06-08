<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode input data and prepare it for the request
        $data = [
            'client_id' => $_POST['client_id'],
            'client_secret' => $_POST['client_secret'],
            'grant_type' => $_POST['grant_type']
        ];

        //$ch = curl_init('https://core.bhutanndi.com/authentication/authenticate');
        $ch = curl_init('https://core.bhutanndi.com/authentication/authenticate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo json_encode(['error' => curl_error($ch)]);
        } else {
            // Ensure the response is valid JSON
            header('Content-Type: application/json');
            echo $response;
        }
        curl_close($ch);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
    }