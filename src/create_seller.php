<?php
require './models/Seller.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$sellerModel = new SellerModel();
$response = $sellerModel->createSeller($input);

if ($response['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($response);
?>
