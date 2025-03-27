<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client;

class PropertyModel {
    private $collection;
    private $sellerCollection;
    private $jwtSecret = "1a2b3c4d";

    public function __construct() {
        $client = new Client("mongodb://mongodb:27017");
        $this->collection = $client->myDatabase->properties;
        $this->sellerCollection = $client->myDatabase->sellers;
    }

    public function createProperty($data, $token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded->data;

            if ($userData['role'] !== 'seller') {
                return ['success' => false, 'message' => 'Unauthorized. Only sellers can create properties.'];
            }

            $sellerId = $userData['id'];
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ['success' => false, 'message' => 'Token has expired.'];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return ['success' => false, 'message' => 'Invalid token signature.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Invalid token.'];
        }

        $requiredFields = [
            'name', 'description', 'price', 'location', 'type','img',
            'carParking', 'carpetArea', 'noOfBedrooms', 'noOfBathrooms', 'typeOfProperty'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return ['success' => false, 'message' => "Missing required field: $field"];
            }
        }

        $propertyData = [
            'image' => $data['img'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'location' => $data['location'],
            'verified' => 'pending', 
            'type' => $data['type'],
            'carParking' => $data['carParking'],
            'carpetArea' => $data['carpetArea'],
            'noOfBedrooms' => $data['noOfBedrooms'],
            'noOfBathrooms' => $data['noOfBathrooms'],
            'typeOfProperty' => $data['typeOfProperty'],
            'sellerId' => $sellerId,
            'createdAt' => new MongoDB\BSON\UTCDateTime(),
        ];

        try {
            $result = $this->collection->insertOne($propertyData);
            $propertyId = (string) $result->getInsertedId();

            $updateResult = $this->sellerCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($sellerId)],
                ['$push' => ['properties' => $propertyId]]
            );

            if ($updateResult->getModifiedCount() === 0) {
                return ['success' => false, 'message' => 'Failed to update seller properties.'];
            }

            return [
                'success' => true,
                'message' => 'Property created successfully.',
                'propertyId' => $propertyId,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while creating the property. Please try again.',
            ];
        }
    }
    public function getPendingProperties() {
        try {
            $properties = $this->collection->find(['verified' => 'pending'])->toArray();
            return [
                'success' => true,
                'properties' => $properties
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getVerifiedProperties() {
        try {
            $properties = $this->collection->find(['verified' => 'true'])->toArray();
            return [
                'success' => true,
                'properties' => $properties
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function verifyProperty($data, $token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded->data;
    
            if ($userData['role'] !== 'admin') {
                return ['success' => false, 'message' => 'Unauthorized. Only admins can verify properties.'];
            }
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ['success' => false, 'message' => 'Token has expired.'];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return ['success' => false, 'message' => 'Invalid token signature.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Invalid token.'];
        }
    
        if (!isset($data['propertyId'], $data['status'])) {
            return ['success' => false, 'message' => 'Missing required fields: propertyId and status.'];
        }
    
        if (!in_array($data['status'], ['true', 'false'])) {
            return ['success' => false, 'message' => 'Invalid status value. Allowed values: "true" or "false".'];
        }
    
        if (!preg_match('/^[a-f\d]{24}$/i', $data['propertyId'])) {
            return ['success' => false, 'message' => 'Invalid property ID format.'];
        }
    
        try {
            $updateResult = $this->collection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($data['propertyId'])],
                ['$set' => ['verified' => $data['status']]]
            );
    
            if ($updateResult->getMatchedCount() === 0) {
                return ['success' => false, 'message' => 'Property not found or no changes made.'];
            }
    
            return [
                'success' => true,
                'message' => "Property verification status updated to '{$data['status']}'."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while updating the property status. Please try again.'
            ];
        }
    }
    public function getPropertyById($propertyId) {
        if (!preg_match('/^[a-f\d]{24}$/i', $propertyId)) {
            return [
                'success' => false,
                'message' => 'Invalid property ID format.'
            ];
        }
    
        try {
            $property = $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($propertyId)]);
    
            if (!$property) {
                return [
                    'success' => false,
                    'message' => 'Property not found.'
                ];
            }
    
            return [
                'success' => true,
                'property' => $property
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while fetching the property. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
