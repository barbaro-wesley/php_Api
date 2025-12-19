<?php
// middleware/AuthMiddleware.php

class AuthMiddleware {
    private $auth;

    public function __construct($auth) {
        $this->auth = $auth;
    }

    /**
     * Verifica se o usuário está autenticado
     * Extrai o token do header Authorization
     * Retorna os dados do token se válido, false caso contrário
     */
    public function verifyToken() {
        $headers = getallheaders();
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        // Extrai o token do header "Bearer {token}"
        if (!preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            http_response_code(401);
            echo json_encode(array(
                "success" => false,
                "message" => "Token não fornecido."
            ));
            exit;
        }

        $token = $matches[1];

        // Verifica se o token é válido
        $payload = $this->auth->verifyJWT($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(array(
                "success" => false,
                "message" => "Token inválido ou expirado."
            ));
            exit;
        }

        return $payload;
    }

    /**
     * Verifica se o usuário tem um status específico (opcional)
     */
    public function verifyStatus($required_status = 'ativo') {
        $payload = $this->verifyToken();

        if ($payload['status'] !== $required_status) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Usuário sem permissão."
            ));
            exit;
        }

        return $payload;
    }
}
?>