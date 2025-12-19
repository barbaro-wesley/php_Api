<?php
// models/Auth.php

class Auth {
    private $conn;
    private $table = "usuarios";
    private $secret_key = "3d4fa4ae43cbaf9a141d3aa88fb1074e318cf70a119cf7cce3656c6cdb4a3d27"; // MUDE ISTO!
    private $token_expire = 86400; // 24 horas em segundos

    public $id;
    public $username;
    public $password;
    public $email;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
        // Em produção, carregue de variável de ambiente
        // $this->secret_key = $_ENV['JWT_SECRET_KEY'];
    }

    // LOGIN
    public function login() {
        $query = "SELECT id, username, password, email, status 
                  FROM " . $this->table . " 
                  WHERE username = :username 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(":username", $this->username);
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->status = $row['status'];
                return true;
            }
        }
        return false;
    }

    // GERAR JWT TOKEN
    public function generateJWT() {
        $now = time();
        $expire = $now + $this->token_expire;

        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $payload = json_encode([
            'iat' => $now,
            'exp' => $expire,
            'iss' => $_SERVER['SERVER_NAME'],
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'status' => $this->status
        ]);

        $header_encoded = $this->base64url_encode($header);
        $payload_encoded = $this->base64url_encode($payload);
        $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $this->secret_key, true);
        $signature_encoded = $this->base64url_encode($signature);

        return "$header_encoded.$payload_encoded.$signature_encoded";
    }

    // VERIFICAR JWT TOKEN
    public function verifyJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

        // Verificar assinatura
        $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $this->secret_key, true);
        $signature_expected = $this->base64url_encode($signature);

        if ($signature_encoded !== $signature_expected) {
            return false;
        }

        // Decodificar payload
        $payload = json_decode($this->base64url_decode($payload_encoded), true);

        if (!$payload) {
            return false;
        }

        // Verificar expiração
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token expirado
        }

        // Retornar dados do token
        return $payload;
    }

    // DECODIFICAR TOKEN (sem verificar assinatura - apenas para leitura)
    public function decodeJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        $payload = json_decode($this->base64url_decode($payload_encoded), true);

        return $payload ?: false;
    }

    // BASE64URL ENCODE
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // BASE64URL DECODE
    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    // LOGOUT (invalidar token)
    public function logout() {
        // Em produção, adicione o token a uma "blacklist" no Redis ou banco de dados
        // Por enquanto, o logout é feito removendo o token no frontend
        session_start();
        session_destroy();
        return true;
    }

    // REFRESH TOKEN (gerar novo token)
    public function refreshToken($token) {
        $payload = $this->verifyJWT($token);

        if (!$payload) {
            return false;
        }

        // Restaurar dados e gerar novo token
        $this->id = $payload['id'];
        $this->username = $payload['username'];
        $this->email = $payload['email'];
        $this->status = $payload['status'];

        return $this->generateJWT();
    }
}