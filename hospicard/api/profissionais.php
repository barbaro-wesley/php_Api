<?php
// api/profissionais.php

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
    include_once '../models/Profissionais.php';
    include_once '../models/Auth.php';
    include_once '../middleware/AuthMiddleware.php';

    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);  // ✅ Auth precisa de $db
    $middleware = new AuthMiddleware($auth);  // ✅ CRIADA UMA VEZ SÓ AQUI!
    
    if(!$db) {
        throw new Exception("Erro na conexão com o banco de dados");
    }

    $profissional = new Profissionais($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            if(isset($_GET['id'])) {
                // GET ONE
                $profissional->id = $_GET['id'];
                
                if($profissional->readOne()) {
                    $item = array(
                        "id" => $profissional->id,
                        "especialidade_id" => $profissional->especialidade_id,
                        "nome" => $profissional->nome,
                        "telefone" => $profissional->telefone,
                        "imagem" => $profissional->imagem,
                        "observacoes" => $profissional->observacoes,
                        "ativo" => $profissional->ativo,
                        "created_at" => $profissional->created_at,
                        "updated_at" => $profissional->updated_at
                    );
                    
                    http_response_code(200);
                    echo json_encode($item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Profissional não encontrado."));
                }
            } elseif(isset($_GET['especialidade_id'])) {
                // GET BY ESPECIALIDADE
                $profissional->especialidade_id = $_GET['especialidade_id'];
                $stmt = $profissional->readByEspecialidade();
                $count = $stmt->rowCount();

                if($count > 0) {
                    $items = array();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        $item = array(
                            "id" => $id,
                            "especialidade_id" => $especialidade_id,
                            "especialidade_nome" => $especialidade_nome,
                            "nome" => $nome,
                            "telefone" => $telefone,
                            "imagem" => $imagem,
                            "observacoes" => $observacoes,
                            "ativo" => $ativo,
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
            } else {
                // GET ALL
                $stmt = $profissional->read();
                $count = $stmt->rowCount();

                if($count > 0) {
                    $items = array();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        $item = array(
                            "id" => $id,
                            "especialidade_id" => $especialidade_id,
                            "especialidade_nome" => $especialidade_nome,
                            "nome" => $nome,
                            "telefone" => $telefone,
                            "imagem" => $imagem,
                            "observacoes" => $observacoes,
                            "ativo" => $ativo,
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

            if(!empty($data->especialidade_id) && !empty($data->nome) && !empty($data->telefone)) {
                $profissional->especialidade_id = $data->especialidade_id;
                $profissional->nome = $data->nome;
                $profissional->telefone = $data->telefone;
                $profissional->imagem = isset($data->imagem) ? $data->imagem : null;
                $profissional->observacoes = isset($data->observacoes) ? $data->observacoes : null;
                $profissional->ativo = isset($data->ativo) ? $data->ativo : 1;

                if($profissional->create()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Profissional criado com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível criar o profissional."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Dados incompletos."));
            }
            break;

        case 'PUT':
            $payload = $middleware->verifyToken();

            // UPDATE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id) && !empty($data->especialidade_id) && !empty($data->nome) && !empty($data->telefone)) {
                $profissional->id = $data->id;
                $profissional->especialidade_id = $data->especialidade_id;
                $profissional->nome = $data->nome;
                $profissional->telefone = $data->telefone;
                $profissional->imagem = isset($data->imagem) ? $data->imagem : null;
                $profissional->observacoes = isset($data->observacoes) ? $data->observacoes : null;
                $profissional->ativo = isset($data->ativo) ? $data->ativo : 1;

                if($profissional->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Profissional atualizado com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível atualizar o profissional."));
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
                $profissional->id = $data->id;

                if($profissional->delete()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Profissional excluído com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível excluir o profissional."));
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