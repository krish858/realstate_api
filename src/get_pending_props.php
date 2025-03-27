<?php
require 'vendor/autoload.php';
require './models/Property.php';

header('Content-Type: application/json');

$propertyModel = new PropertyModel();

try {
    $response = $propertyModel->getPendingProperties();
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching pending properties.'
    ]);
}
?>
