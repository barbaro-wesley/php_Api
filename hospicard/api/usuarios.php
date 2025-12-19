<?php
// api/usuarios.php

// DESABILITAR WARNINGS E NOTICES
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

try {
    include_once '../config/Database.php';
    include_once '../models/Usuarios.php';
    include_once '../models/Auth.php';
    include_once '../middleware/AuthMiddleware.php';

    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);  // ✅ Auth precisa de $db
    $middleware = new AuthMiddleware($auth);  // ✅ CRIADA UMA VEZ SÓ AQUI!
    
    if(!$db) {
        throw new Exception("Erro na conexão com o banco de dados");
    }

    $usuario = new Usuarios($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            $payload = $middleware->verifyToken();

            if(isset($_GET['id'])) {
                // GET ONE
                $usuario->id = $_GET['id'];
                
                if($usuario->readOne()) {
                    $item = array(
                        "id" => $usuario->id,
                        "username" => $usuario->username,
                        "email" => $usuario->email,
                        "status" => $usuario->status,
                        "created_at" => $usuario->created_at
                    );
                    
                    http_response_code(200);
                    echo json_encode($item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Usuário não encontrado."));
                }
            } else {
                // GET ALL
                $stmt = $usuario->read();
                $count = $stmt->rowCount();

                if($count > 0) {
                    $items = array();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        $item = array(
                            "id" => $id,
                            "username" => $username,
                            "email" => $email,
                            "status" => $status,
                            "created_at" => $created_at
                        );
                        array_push($items, $item);
                    }
                    
                    http_response_code(200);
                    echo json_encode($items);
                } else {
                    http_response_code(200);
                    echo json_encode(array());
                }
            }
            break;

        case 'POST':
            // Verifica se é requisição de login (não precisa autenticação)
            if(isset($_GET['action']) && $_GET['action'] == 'login') {
                // LOGIN
                $data = json_decode(file_get_contents("php://input"));

                if(!empty($data->username) && !empty($data->password)) {
                    $usuario->username = $data->username;
                    $usuario->password = $data->password;

                    if($usuario->login()) {
                        if($usuario->status == 'ativo') {
                            http_response_code(200);
                            echo json_encode(array(
                                "message" => "Login realizado com sucesso.",
                                "id" => $usuario->id,
                                "username" => $usuario->username,
                                "email" => $usuario->email,
                                "status" => $usuario->status
                            ));
                        } else {
                            http_response_code(401);
                            echo json_encode(array("message" => "Usuário inativo."));
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode(array("message" => "Usuário ou senha incorretos."));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Dados incompletos."));
                }
            } else {
                // CREATE - Precisa de autenticação
                $payload = $middleware->verifyToken();

                $data = json_decode(file_get_contents("php://input"));

                if(!empty($data->username) && !empty($data->password) && !empty($data->email)) {
                    $usuario->username = $data->username;
                    $usuario->email = $data->email;

                    // Verifica se username já existe
                    if($usuario->usernameExists()) {
                        http_response_code(400);
                        echo json_encode(array("message" => "Username já existe."));
                        break;
                    }

                    // Verifica se email já existe
                    if($usuario->emailExists()) {
                        http_response_code(400);
                        echo json_encode(array("message" => "Email já cadastrado."));
                        break;
                    }

                    $usuario->password = $data->password;
                    $usuario->status = isset($data->status) ? $data->status : 'ativo';

                    if($usuario->create()) {
                        http_response_code(201);
                        echo json_encode(array("message" => "Usuário criado com sucesso."));
                    } else {
                        http_response_code(503);
                        echo json_encode(array("message" => "Não foi possível criar o usuário."));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Dados incompletos."));
                }
            }
            break;

        case 'PUT':
            $payload = $middleware->verifyToken();

            // UPDATE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id) && !empty($data->username) && !empty($data->email)) {
                $usuario->id = $data->id;
                $usuario->username = $data->username;
                $usuario->email = $data->email;
                $usuario->password = isset($data->password) ? $data->password : null;
                $usuario->status = isset($data->status) ? $data->status : 'ativo';

                if($usuario->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Usuário atualizado com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível atualizar o usuário."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Dados incompletos."));
            }
            break;

        case 'DELETE':
            $payload = $middleware->verifyToken();

            // DELETE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id)) {
                $usuario->id = $data->id;

                if($usuario->delete()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Usuário excluído com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível excluir o usuário."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "ID não fornecido."));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Método não permitido."));
            break;
    }

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "Erro no servidor",
        "error" => $e->getMessage()
    ));
}
?>