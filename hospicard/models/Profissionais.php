<?php
// models/Profissionais.php

class Profissionais {
    private $conn;
    private $table = "profissionais";

    public $id;
    public $especialidade_id;
    public $nome;
    public $telefone;
    public $imagem;
    public $observacoes;
    public $ativo;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET especialidade_id = :especialidade_id, 
                      nome = :nome, 
                      telefone = :telefone, 
                      imagem = :imagem, 
                      observacoes = :observacoes, 
                      ativo = :ativo";

        $stmt = $this->conn->prepare($query);

        $this->especialidade_id = htmlspecialchars(strip_tags($this->especialidade_id));
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->imagem = htmlspecialchars(strip_tags($this->imagem));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        $this->ativo = isset($this->ativo) ? $this->ativo : 1;

        $stmt->bindParam(":especialidade_id", $this->especialidade_id);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":imagem", $this->imagem);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":ativo", $this->ativo);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ ALL
    public function read() {
        $query = "SELECT p.*, e.nome as especialidade_nome 
                  FROM " . $this->table . " p 
                  LEFT JOIN especialidades e ON p.especialidade_id = e.id 
                  ORDER BY p.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne() {
        $query = "SELECT p.*, e.nome as especialidade_nome 
                  FROM " . $this->table . " p 
                  LEFT JOIN especialidades e ON p.especialidade_id = e.id 
                  WHERE p.id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->especialidade_id = $row['especialidade_id'];
            $this->nome = $row['nome'];
            $this->telefone = $row['telefone'];
            $this->imagem = $row['imagem'];
            $this->observacoes = $row['observacoes'];
            $this->ativo = $row['ativo'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET especialidade_id = :especialidade_id, 
                      nome = :nome, 
                      telefone = :telefone, 
                      imagem = :imagem, 
                      observacoes = :observacoes, 
                      ativo = :ativo 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->especialidade_id = htmlspecialchars(strip_tags($this->especialidade_id));
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->imagem = htmlspecialchars(strip_tags($this->imagem));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        $this->ativo = htmlspecialchars(strip_tags($this->ativo));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":especialidade_id", $this->especialidade_id);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":imagem", $this->imagem);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":ativo", $this->ativo);
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

    // READ BY ESPECIALIDADE
    public function readByEspecialidade() {
        $query = "SELECT p.*, e.nome as especialidade_nome 
                  FROM " . $this->table . " p 
                  LEFT JOIN especialidades e ON p.especialidade_id = e.id 
                  WHERE p.especialidade_id = :especialidade_id 
                  ORDER BY p.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":especialidade_id", $this->especialidade_id);
        $stmt->execute();
        return $stmt;
    }

    // READ ATIVOS
    public function readAtivos() {
        $query = "SELECT p.*, e.nome as especialidade_nome 
                  FROM " . $this->table . " p 
                  LEFT JOIN especialidades e ON p.especialidade_id = e.id 
                  WHERE p.ativo = 1 
                  ORDER BY p.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}