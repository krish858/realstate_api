<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client;

class BuyerModel {
    private $collection;
    private $jwtSecret = "1a2b3c4d";

    public function __construct() {
        $client = new Client("mongodb://mongodb:27017");
        $this->collection = $client->myDatabase->buyers;
    }

    public function createBuyer($data) {
        if (!isset($data['email'], $data['name'], $data['password'], $data['phone'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        if ($this->collection->findOne(['email' => $data['email']])) {
            return ['success' => false, 'message' => 'User with this Email already exists'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        try {
            $result = $this->collection->insertOne(["name" => $data['name'],"email"=>$data['email'],"password"=>$data['password'],"phone"=>$data['phone']]);

            $payload = [
                "iat" => time(),
                "exp" => time() + 3600,
                "data" => [
                    "id" => (string)$result->getInsertedId(),
                    "email" => $data['email'],
                    "name" => $data['name'],
                    "role" => "buyer"
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
            $buyer = $this->collection->findOne(['email' => $email]);

            if (!$buyer) {
                return ['success' => false, 'message' => 'User not found'];
            }

            if (!password_verify($password, $buyer['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            $payload = [
                "iat" => time(),
                "exp" => time() + 3600,
                "data" => [
                    "id" => (string)$buyer['_id'],
                    "email" => $buyer['email'],
                    "name" => $buyer['name'],
                    "role" => "buyer"
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
}
?>
