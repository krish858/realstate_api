<?php
require './models/Property.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization token is missing']);
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$propertyModel = new PropertyModel();
$response = $propertyModel->createProperty($data, $token);

if ($response['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}
echo json_encode($response);
?>
