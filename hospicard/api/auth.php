<?php
// api/auth.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

include_once '../config/Database.php';
include_once '../models/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($method) {
    case 'POST':
        if($action === 'login') {
            // LOGIN
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->username) && !empty($data->password)) {
                $auth->username = $data->username;
                $auth->password = $data->password;

                if($auth->login()) {
                    if($auth->status == 'ativo') {
                        // Gerar JWT Token
                        $token = $auth->generateJWT();

                        http_response_code(200);
                        echo json_encode(array(
                            "success" => true,
                            "message" => "Login realizado com sucesso.",
                            "token" => $token,
                            "expiresIn" => 86400, // 24 horas em segundos
                            "user" => array(
                                "id" => $auth->id,
                                "username" => $auth->username,
                                "email" => $auth->email,
                                "status" => $auth->status
                            )
                        ));
                    } else {
                        http_response_code(401);
                        echo json_encode(array(
                            "success" => false,
                            "message" => "Usuário inativo."
                        ));
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Usuário ou senha incorretos."
                    ));
                }
            } else {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Usuário e senha são obrigatórios."
                ));
            }
        } else if($action === 'logout') {
            // LOGOUT
            $auth->logout();
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Logout realizado com sucesso."
            ));
        } else if($action === 'refresh') {
            // REFRESH TOKEN
            $headers = getallheaders();
            $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
            
            if(preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                $token = $matches[1];
                $new_token = $auth->refreshToken($token);

                if($new_token) {
                    http_response_code(200);
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Token renovado com sucesso.",
                        "token" => $new_token,
                        "expiresIn" => 86400
                    ));
                } else {
                    http_response_code(401);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Não foi possível renovar o token."
                    ));
                }
            } else {
                http_response_code(401);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Token não fornecido."
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Ação não especificada."
            ));
        }
        break;

    case 'GET':
        if($action === 'me') {
            // OBTER DADOS DO USUÁRIO AUTENTICADO
            $headers = getallheaders();
            $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
            
            if(preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                $token = $matches[1];
                $payload = $auth->verifyJWT($token);

                if($payload) {
                    http_response_code(200);
                    echo json_encode(array(
                        "success" => true,
                        "user" => array(
                            "id" => $payload['id'],
                            "username" => $payload['username'],
                            "email" => $payload['email'],
                            "status" => $payload['status']
                        )
                    ));
                } else {
                    http_response_code(401);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Token inválido ou expirado."
                    ));
                }
            } else {
                http_response_code(401);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Token não fornecido."
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Ação não especificada."
            ));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array(
            "success" => false,
            "message" => "Método não permitido."
        ));
        break;
}
?>