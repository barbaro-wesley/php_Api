<?php
// api/clientes.php

// DESABILITAR WARNINGS E NOTICES
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    include_once '../config/Database.php';
    include_once '../models/Clientes.php';
    include_once '../models/Auth.php';
    include_once '../middleware/AuthMiddleware.php';

    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);  // ✅ Auth precisa de $db
    $middleware = new AuthMiddleware($auth);  // ✅ CRIADA UMA VEZ SÓ AQUI!

    if(!$db) {
        throw new Exception("Erro na conexão com o banco de dados");
    }
    
    $cliente = new Clientes($db);
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            $payload = $middleware->verifyToken();
            
            if(isset($_GET['id'])) {
                // GET ONE
                $cliente->id = $_GET['id'];
                
                if($cliente->readOne()) {
                    $item = array(
                        "id" => $cliente->id,
                        "nome" => $cliente->nome,
                        "rg" => $cliente->rg,
                        "cpf" => $cliente->cpf,
                        "nome_pai" => $cliente->nome_pai,
                        "nome_mae" => $cliente->nome_mae,
                        "data_nascimento" => $cliente->data_nascimento,
                        "endereco" => $cliente->endereco,
                        "cidade" => $cliente->cidade,
                        "estado" => $cliente->estado,
                        "estado_civil" => $cliente->estado_civil,
                        "profissao_trabalho" => $cliente->profissao_trabalho,
                        "telefone" => $cliente->telefone,
                        "plano" => $cliente->plano,
                        "forma_pagamento" => $cliente->forma_pagamento,
                        "data_cadastro" => $cliente->data_cadastro
                    );
                    
                    http_response_code(200);
                    echo json_encode($item);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Cliente não encontrado."));
                }
            } elseif(isset($_GET['plano'])) {
                // FILTRAR POR PLANO
                $stmt = $cliente->readByPlano($_GET['plano']);
                $count = $stmt->rowCount();

                if($count > 0) {
                    $items = array();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        array_push($items, $row);
                    }
                    
                    http_response_code(200);
                    echo json_encode($items);
                } else {
                    http_response_code(200);
                    echo json_encode(array());
                }
            } else {
                // GET ALL
                $stmt = $cliente->read();
                $count = $stmt->rowCount();

                if($count > 0) {
                    $items = array();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        array_push($items, $row);
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

            if(!empty($data->nome) && !empty($data->cpf) && !empty($data->data_nascimento)) {
                // Validar CPF duplicado
                $cliente->cpf = $data->cpf;
                if($cliente->readByCpf()) {
                    http_response_code(409);
                    echo json_encode(array("message" => "CPF já cadastrado no sistema."));
                    break;
                }

                $cliente->nome = $data->nome;
                $cliente->rg = isset($data->rg) ? $data->rg : '';
                $cliente->nome_pai = isset($data->nome_pai) ? $data->nome_pai : '';
                $cliente->nome_mae = isset($data->nome_mae) ? $data->nome_mae : '';
                $cliente->data_nascimento = $data->data_nascimento;
                $cliente->endereco = isset($data->endereco) ? $data->endereco : '';
                $cliente->cidade = isset($data->cidade) ? $data->cidade : '';
                $cliente->estado = isset($data->estado) ? $data->estado : '';
                $cliente->estado_civil = isset($data->estado_civil) ? $data->estado_civil : '';
                $cliente->profissao_trabalho = isset($data->profissao_trabalho) ? $data->profissao_trabalho : '';
                $cliente->telefone = isset($data->telefone) ? $data->telefone : '';
                $cliente->plano = isset($data->plano) ? $data->plano : '';
                $cliente->forma_pagamento = isset($data->forma_pagamento) ? $data->forma_pagamento : '';

                if($cliente->create()) {
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Cliente cadastrado com sucesso.",
                        "id" => $cliente->id
                    ));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível cadastrar o cliente."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Dados incompletos. Nome, CPF e Data de Nascimento são obrigatórios."));
            }
            break;

        case 'PUT':
            $payload = $middleware->verifyToken();

            // UPDATE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id) && !empty($data->nome) && !empty($data->cpf)) {
                $cliente->id = $data->id;
                $cliente->nome = $data->nome;
                $cliente->rg = isset($data->rg) ? $data->rg : '';
                $cliente->cpf = $data->cpf;
                $cliente->nome_pai = isset($data->nome_pai) ? $data->nome_pai : '';
                $cliente->nome_mae = isset($data->nome_mae) ? $data->nome_mae : '';
                $cliente->data_nascimento = $data->data_nascimento;
                $cliente->endereco = isset($data->endereco) ? $data->endereco : '';
                $cliente->cidade = isset($data->cidade) ? $data->cidade : '';
                $cliente->estado = isset($data->estado) ? $data->estado : '';
                $cliente->estado_civil = isset($data->estado_civil) ? $data->estado_civil : '';
                $cliente->profissao_trabalho = isset($data->profissao_trabalho) ? $data->profissao_trabalho : '';
                $cliente->telefone = isset($data->telefone) ? $data->telefone : '';
                $cliente->plano = isset($data->plano) ? $data->plano : '';
                $cliente->forma_pagamento = isset($data->forma_pagamento) ? $data->forma_pagamento : '';

                if($cliente->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Cliente atualizado com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível atualizar o cliente."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Dados incompletos. ID, nome e CPF são obrigatórios."));
            }
            break;

        case 'DELETE':
            $payload = $middleware->verifyToken();

            // DELETE
            $data = json_decode(file_get_contents("php://input"));

            if(!empty($data->id)) {
                $cliente->id = $data->id;

                if($cliente->delete()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Cliente excluído com sucesso."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Não foi possível excluir o cliente."));
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