<?php
require 'vendor/autoload.php';
require 'models/Property.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only GET is allowed.'
    ]);
    http_response_code(405);
    exit();
}

if (!isset($_GET['propertyId']) || empty($_GET['propertyId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid propertyId parameter.'
    ]);
    http_response_code(400);
    exit();
}

$propertyId = $_GET['propertyId'];
$propertyModel = new PropertyModel();

try {
    $response = $propertyModel->getPropertyById($propertyId);

    if ($response['success']) {
        echo json_encode($response);
    } else {
        echo json_encode($response);
        http_response_code(404);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing the request.',
        'error' => $e->getMessage()
    ]);
    http_response_code(500);
}
