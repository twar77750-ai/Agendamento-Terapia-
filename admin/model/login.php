<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/terapia/config.php';




// ── Requisição AJAX (POST) ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        http_response_code(422);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT id, nome, senha FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    // ⚠️  Troque por password_verify($senha, $admin['senha']) se usar bcrypt
    if ($admin && $admin['senha'] === $senha) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];

        echo json_encode([
            'sucesso'  => true,
            'mensagem' => 'Login realizado com sucesso.',
            'redirect' => '/terapia/admin/dashboard.php',
            
        ]);
        exit;
    }

    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail ou senha incorretos.']);
    exit;
}
