<?php
// api/especialidades.php

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
    include_once '../models/Especialidades.php';
    include_once '../middleware/AuthMiddleware.php';

    $database = new Database();
    $db = $database->getConnection();
    $middleware = new AuthMiddleware($auth);  // ✅ CRIADA UMA VEZ SÓ AQUI!

    if(!$db) {
        throw new Exception("Erro na conexão com o banco de dados");
    }
    
    $especialidade = new Especialidades($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            
            if(isset($_GET['id'])) {
                // GET ONE
                $especialidade->id = $_GET['id'];
                
                if($especialidade->readOne()) {
                    $item = array(
                        "id" => $especialidade->id,
                        "nome" => $especialidade->nome,
                        "slug" => $especialidade->slug,
                        "descricao" => $especialidade->descricao,
                        "ativo" => $especialidade->ativo,
                        "ordem" => $especialidade->ordem,
                        "created_at" => $especialidade->created_at,
                        "updated_at" => $especialidade->updated_at
                    );
                    
                    http_response_code(200);
                    echo json_encode($item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Especialidade não encontrada."));
                }
            } else {
                // GET ALL
                $stmt = $especialidade->read();
                $count = $stmt->rowCount();

                if($count > 0) {
                    $items = array();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        $item = array(
                            "id" => $id,
                            "nome" => $nome,
                            "slug" => $slug,
                            "descricao" => $descricao,
                            "ativo" => $ativo,
                            "ordem" => $ordem,
                            "created_at" => $created_at,
                            "updated_at" => $updated_at
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
            $payload = $middleware->verifyToken();

            // CREATE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->nome) && !empty($data->slug)) {
                $especialidade->nome = $data->nome;
                $especialidade->slug = $data->slug;
                $especialidade->descricao = isset($data->descricao) ? $data->descricao : null;
                $especialidade->ativo = isset($data->ativo) ? $data->ativo : 1;
                $especialidade->ordem = isset($data->ordem) ? $data->ordem : 0;

                if($especialidade->create()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Especialidade criada com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível criar a especialidade."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Dados incompletos. Nome e slug são obrigatórios."));
            }
            break;

        case 'PUT':
            $payload = $middleware->verifyToken();

            // UPDATE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id) && !empty($data->nome) && !empty($data->slug)) {
                $especialidade->id = $data->id;
                $especialidade->nome = $data->nome;
                $especialidade->slug = $data->slug;
                $especialidade->descricao = isset($data->descricao) ? $data->descricao : null;
                $especialidade->ativo = isset($data->ativo) ? $data->ativo : 1;
                $especialidade->ordem = isset($data->ordem) ? $data->ordem : 0;

                if($especialidade->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Especialidade atualizada com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível atualizar a especialidade."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Dados incompletos. ID, nome e slug são obrigatórios."));
            }
            break;

        case 'DELETE':
            $payload = $middleware->verifyToken();

            // DELETE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id)) {
                $especialidade->id = $data->id;

                if($especialidade->delete()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Especialidade excluída com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível excluir a especialidade."));
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