<?php
// models/Clientes.php

class Clientes {
    private $conn;
    private $table = "clientes";

    public $id;
    public $nome;
    public $rg;
    public $cpf;
    public $nome_pai;
    public $nome_mae;
    public $data_nascimento;
    public $endereco;
    public $cidade;
    public $estado;
    public $estado_civil;
    public $profissao_trabalho;
    public $telefone;
    public $plano;
    public $forma_pagamento;
    public $data_cadastro;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nome = :nome, 
                      rg = :rg, 
                      cpf = :cpf, 
                      nome_pai = :nome_pai, 
                      nome_mae = :nome_mae, 
                      data_nascimento = :data_nascimento, 
                      endereco = :endereco, 
                      cidade = :cidade, 
                      estado = :estado, 
                      estado_civil = :estado_civil, 
                      profissao_trabalho = :profissao_trabalho, 
                      telefone = :telefone, 
                      plano = :plano, 
                      forma_pagamento = :forma_pagamento";

        $stmt = $this->conn->prepare($query);

        // Sanitização
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->rg = htmlspecialchars(strip_tags($this->rg));
        $this->cpf = htmlspecialchars(strip_tags($this->cpf));
        $this->nome_pai = htmlspecialchars(strip_tags($this->nome_pai));
        $this->nome_mae = htmlspecialchars(strip_tags($this->nome_mae));
        $this->data_nascimento = htmlspecialchars(strip_tags($this->data_nascimento));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        $this->cidade = htmlspecialchars(strip_tags($this->cidade));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->estado_civil = htmlspecialchars(strip_tags($this->estado_civil));
        $this->profissao_trabalho = htmlspecialchars(strip_tags($this->profissao_trabalho));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->plano = htmlspecialchars(strip_tags($this->plano));
        $this->forma_pagamento = htmlspecialchars(strip_tags($this->forma_pagamento));

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":rg", $this->rg);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->bindParam(":nome_pai", $this->nome_pai);
        $stmt->bindParam(":nome_mae", $this->nome_mae);
        $stmt->bindParam(":data_nascimento", $this->data_nascimento);
        $stmt->bindParam(":endereco", $this->endereco);
        $stmt->bindParam(":cidade", $this->cidade);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":estado_civil", $this->estado_civil);
        $stmt->bindParam(":profissao_trabalho", $this->profissao_trabalho);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":plano", $this->plano);
        $stmt->bindParam(":forma_pagamento", $this->forma_pagamento);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // READ ALL
    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nome = $row['nome'];
            $this->rg = $row['rg'];
            $this->cpf = $row['cpf'];
            $this->nome_pai = $row['nome_pai'];
            $this->nome_mae = $row['nome_mae'];
            $this->data_nascimento = $row['data_nascimento'];
            $this->endereco = $row['endereco'];
            $this->cidade = $row['cidade'];
            $this->estado = $row['estado'];
            $this->estado_civil = $row['estado_civil'];
            $this->profissao_trabalho = $row['profissao_trabalho'];
            $this->telefone = $row['telefone'];
            $this->plano = $row['plano'];
            $this->forma_pagamento = $row['forma_pagamento'];
            $this->data_cadastro = $row['data_cadastro'];
            return true;
        }
        return false;
    }

    // READ BY CPF (validação duplicidade)
    public function readByCpf() {
        $query = "SELECT id FROM " . $this->table . " WHERE cpf = :cpf LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, 
                      rg = :rg, 
                      cpf = :cpf, 
                      nome_pai = :nome_pai, 
                      nome_mae = :nome_mae, 
                      data_nascimento = :data_nascimento, 
                      endereco = :endereco, 
                      cidade = :cidade, 
                      estado = :estado, 
                      estado_civil = :estado_civil, 
                      profissao_trabalho = :profissao_trabalho, 
                      telefone = :telefone, 
                      plano = :plano, 
                      forma_pagamento = :forma_pagamento 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitização
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->rg = htmlspecialchars(strip_tags($this->rg));
        $this->cpf = htmlspecialchars(strip_tags($this->cpf));
        $this->nome_pai = htmlspecialchars(strip_tags($this->nome_pai));
        $this->nome_mae = htmlspecialchars(strip_tags($this->nome_mae));
        $this->data_nascimento = htmlspecialchars(strip_tags($this->data_nascimento));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        $this->cidade = htmlspecialchars(strip_tags($this->cidade));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->estado_civil = htmlspecialchars(strip_tags($this->estado_civil));
        $this->profissao_trabalho = htmlspecialchars(strip_tags($this->profissao_trabalho));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->plano = htmlspecialchars(strip_tags($this->plano));
        $this->forma_pagamento = htmlspecialchars(strip_tags($this->forma_pagamento));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":rg", $this->rg);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->bindParam(":nome_pai", $this->nome_pai);
        $stmt->bindParam(":nome_mae", $this->nome_mae);
        $stmt->bindParam(":data_nascimento", $this->data_nascimento);
        $stmt->bindParam(":endereco", $this->endereco);
        $stmt->bindParam(":cidade", $this->cidade);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":estado_civil", $this->estado_civil);
        $stmt->bindParam(":profissao_trabalho", $this->profissao_trabalho);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":plano", $this->plano);
        $stmt->bindParam(":forma_pagamento", $this->forma_pagamento);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ BY PLANO (filtro)
    public function readByPlano($plano) {
        $query = "SELECT * FROM " . $this->table . " WHERE plano LIKE :plano ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($query);
        $plano = "%{$plano}%";
        $stmt->bindParam(":plano", $plano);
        $stmt->execute();
        return $stmt;
    }
}
?>
