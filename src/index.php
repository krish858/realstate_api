<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $response = [
        'status' => 'success',
        'message' => 'Server is running'
    ];

    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Only GET requests are allowed'
    ]);
}

?>
