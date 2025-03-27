<?php
require 'vendor/autoload.php';
require 'models/Seller.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only GET is allowed.'
    ]);
    http_response_code(405);
    exit();
}

if (!isset($_GET['sellerId']) || empty($_GET['sellerId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid sellerId parameter.'
    ]);
    http_response_code(400);
    exit();
}

$sellerId = $_GET['sellerId'];
$sellerModel = new SellerModel();

try {
    $response = $sellerModel->getAllProperties($sellerId);
    if ($response['success']) {
        echo json_encode($response);
    } else {
        echo json_encode($response);
        http_response_code(400);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing the request.',
        'error' => $e->getMessage()
    ]);
    http_response_code(500);
}
?>
