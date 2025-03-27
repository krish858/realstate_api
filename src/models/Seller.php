<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use MongoDB\Client;

class SellerModel {
    private $collection;
    private $jwtSecret = "1a2b3c4d";

    public function __construct() {
        $client = new Client("mongodb://mongodb:27017");
        $this->collection = $client->myDatabase->sellers;
        $this->propertyCollection = $client->myDatabase->properties;
    }

    public function createSeller($data) {
        if (!isset($data['name'], $data['email'], $data['password'], $data['phone'], $data['address'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        if ($this->collection->findOne(['email' => $data['email']])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['properties'] = [];

        try {
            $result = $this->collection->insertOne($data);

            $payload = [
                "iat" => time(),
                "exp" => time() + 3600,
                "data" => [
                    "id" => (string)$result->getInsertedId(),
                    "email" => $data['email'],
                    "name" => $data['name'],
                    "role" => "seller"
                ]
            ];

            $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

            return [
                'success' => true,
                'insertedId' => (string)$result->getInsertedId(),
                'token' => $jwt
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }

    public function login($email, $password) {
        try {
            $seller = $this->collection->findOne(['email' => $email]);

            if (!$seller) {
                return ['success' => false, 'message' => 'Seller not found'];
            }

            if (!password_verify($password, $seller['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            $payload = [
                "iat" => time(),
                "exp" => time() + 3600,
                "data" => [
                    "id" => (string)$seller['_id'],
                    "email" => $seller['email'],
                    "name" => $seller['name'],
                    "role" => "seller"
                ]
            ];

            $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

            return [
                'success' => true,
                'token' => $jwt
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }
    public function getAllProperties($sellerId) {
        try {
            if (!preg_match('/^[a-f\d]{24}$/i', $sellerId)) {
                return ['success' => false, 'message' => 'Invalid seller ID format'];
            }

            $properties = $this->propertyCollection->find(['sellerId' => $sellerId])->toArray();

            if (empty($properties)) {
                return [
                    'success' => true,
                    'message' => 'No properties found for this seller',
                    'properties' => []
                ];
            }

            return [
                'success' => true,
                'properties' => $properties
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while fetching properties. Please try again.'
            ];
        }
    }
}
?>
