<?php
// ============================================
// config.php — Configurações do sistema
// ============================================

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'terapia_db'); // <- altere para o nome do seu banco de dados
define('DB_USER', 'root');         // <- altere para seu usuário MySQL
define('DB_PASS', 'Tony34215');  // <- altere para sua senha MySQL


define('DB_CHARSET', 'utf8mb4');

// URL base do sistema (sem barra no final)
define('BASE_URL', 'http://localhost/terapia');

// Configurações de e-mail (SMTP)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'seu@gmail.com');     // <- seu e-mail
define('MAIL_PASS', 'sua_senha_app');     // <- senha de app do Gmail
define('MAIL_FROM', 'seu@gmail.com');
define('MAIL_FROM_NAME', 'Consultório Terapia');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Sessão
session_start();

// Conexão PDO
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
    }
    return $pdo;
}

// Resposta JSON helper
function jsonResponse(bool $success, $data = null, string $message = '', int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

// Auth admin
function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            jsonResponse(false, null, 'Não autorizado', 401);
        }
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
