<?php
require 'vendor/autoload.php';
require 'models/Property.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit();
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authorization header is missing.']);
    exit();
}

$jwt = str_replace('Bearer ', '', $headers['Authorization']);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['propertyId'], $input['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields: propertyId and status.']);
    exit();
}

$propertyModel = new PropertyModel();
$response = $propertyModel->verifyProperty($input, $jwt);

http_response_code($response['success'] ? 200 : 400);
echo json_encode($response);
