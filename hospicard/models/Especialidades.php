<?php
// models/Especialidades.php

class Especialidades {
    private $conn;
    private $table = "especialidades";

    public $id;
    public $nome;
    public $slug;
    public $descricao;
    public $ativo;
    public $ordem;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nome = :nome, 
                      slug = :slug, 
                      descricao = :descricao, 
                      ativo = :ativo, 
                      ordem = :ordem";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->ativo = isset($this->ativo) ? $this->ativo : 1;
        $this->ordem = isset($this->ordem) ? $this->ordem : 0;

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":ordem", $this->ordem);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ ALL
    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY ordem ASC, nome ASC";
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
            $this->slug = $row['slug'];
            $this->descricao = $row['descricao'];
            $this->ativo = $row['ativo'];
            $this->ordem = $row['ordem'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, 
                      slug = :slug, 
                      descricao = :descricao, 
                      ativo = :ativo, 
                      ordem = :ordem 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->ativo = htmlspecialchars(strip_tags($this->ativo));
        $this->ordem = htmlspecialchars(strip_tags($this->ordem));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":ordem", $this->ordem);
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

    // READ ATIVOS
    public function readAtivos() {
        $query = "SELECT * FROM " . $this->table . " WHERE ativo = 1 ORDER BY ordem ASC, nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}