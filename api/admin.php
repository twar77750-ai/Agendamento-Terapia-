<?php
// ============================================
// api/admin.php — API do Painel Administrativo
// ============================================
require_once __DIR__ . '/../config.php';
requireAdmin();

$action = $_GET['action'] ?? '';

match($action) {
    'agendamentos'         => listarAgendamentos(),
    'agendamento_update'   => atualizarStatus(),
    'disponibilidade'      => gerenciarDisponibilidade(),
    'bloqueios'            => gerenciarBloqueios(),
    'dashboard'            => getDashboard(),
    default                => jsonResponse(false, null, 'Ação inválida', 400)
};

// ── Dashboard ───────────────────────────────────────────────────────────────
function getDashboard(): void {
    $db = getDB();
    $hoje = date('Y-m-d');

    $stats = [];

    $stats['hoje'] = $db->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_sessao = ? AND status NOT IN ('cancelado')");
    $stats['hoje']->execute([$hoje]);
    $stats['hoje'] = (int)$stats['hoje']->fetchColumn();

    $stats['semana'] = $db->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_sessao BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY) AND status NOT IN ('cancelado')");
    $stats['semana']->execute([$hoje, $hoje]);
    $stats['semana'] = (int)$stats['semana']->fetchColumn();

    $stats['total_pacientes'] = (int)$db->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();

    $stats['pendentes'] = (int)$db->query("SELECT COUNT(*) FROM agendamentos WHERE status='pendente'")->fetchColumn();

    // Próximas consultas hoje
    $stmt = $db->prepare("
        SELECT a.id, a.hora_inicio, a.hora_fim, a.status, p.nome, p.email, p.telefone
        FROM agendamentos a JOIN pacientes p ON a.paciente_id = p.id
        WHERE a.data_sessao = ? AND a.status NOT IN ('cancelado')
        ORDER BY a.hora_inicio
    ");
    $stmt->execute([$hoje]);
    $stats['consultas_hoje'] = $stmt->fetchAll();

    jsonResponse(true, $stats);
}

// ── Listar Agendamentos ─────────────────────────────────────────────────────
function listarAgendamentos(): void {
    $db = getDB();
    $filtroData   = $_GET['data'] ?? '';
    $filtroStatus = $_GET['status'] ?? '';
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $where = ['1=1'];
    $params = [];

    if ($filtroData) {
        $where[] = 'a.data_sessao = ?';
        $params[] = $filtroData;
    }
    if ($filtroStatus) {
        $where[] = 'a.status = ?';
        $params[] = $filtroStatus;
    }

    $whereStr = implode(' AND ', $where);

    $total = $db->prepare("SELECT COUNT(*) FROM agendamentos a WHERE {$whereStr}");
    $total->execute($params);
    $total = (int)$total->fetchColumn();

    $stmt = $db->prepare("
        SELECT a.id, a.data_sessao, a.hora_inicio, a.hora_fim, a.status, a.observacoes, a.created_at,
               p.nome, p.email, p.telefone
        FROM agendamentos a JOIN pacientes p ON a.paciente_id = p.id
        WHERE {$whereStr}
        ORDER BY a.data_sessao DESC, a.hora_inicio DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute($params);

    jsonResponse(true, [
        'agendamentos' => $stmt->fetchAll(),
        'total'  => $total,
        'pages'  => ceil($total / $limit),
        'page'   => $page
    ]);
}

// ── Atualizar status de agendamento ────────────────────────────────────────
function atualizarStatus(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, null, 'Método inválido', 405);

    $body = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($body['id'] ?? 0);
    $status = $body['status'] ?? '';

    if (!$id || !in_array($status, ['pendente','confirmado','cancelado','concluido'])) {
        jsonResponse(false, null, 'Dados inválidos');
    }

    $db = getDB();
    $stmt = $db->prepare("UPDATE agendamentos SET status=? WHERE id=?");
    $stmt->execute([$status, $id]);
    jsonResponse(true, null, 'Status atualizado');
}

// ── Gerenciar Disponibilidade ───────────────────────────────────────────────
function gerenciarDisponibilidade(): void {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $rows = $db->query("SELECT * FROM disponibilidade ORDER BY dia_semana")->fetchAll();
        jsonResponse(true, $rows);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        $slots = $body['slots'] ?? [];

        $db->exec("DELETE FROM disponibilidade");
        $stmt = $db->prepare("INSERT INTO disponibilidade (dia_semana, hora_inicio, hora_fim, ativo) VALUES (?,?,?,?)");
        foreach ($slots as $s) {
            $stmt->execute([$s['dia_semana'], $s['hora_inicio'], $s['hora_fim'], $s['ativo'] ?? 1]);
        }
        jsonResponse(true, null, 'Disponibilidade salva');
    }
}

// ── Gerenciar Bloqueios ─────────────────────────────────────────────────────
function gerenciarBloqueios(): void {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->prepare("SELECT * FROM bloqueios WHERE data_bloqueio >= CURDATE() ORDER BY data_bloqueio");
        $stmt->execute();
        jsonResponse(true, $stmt->fetchAll());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        $acao = $body['acao'] ?? '';

        if ($acao === 'adicionar') {
            $stmt = $db->prepare("INSERT INTO bloqueios (data_bloqueio, hora_inicio, hora_fim, motivo, dia_inteiro) VALUES (?,?,?,?,?)");
            $stmt->execute([
                $body['data'],
                $body['hora_inicio'] ?? null,
                $body['hora_fim'] ?? null,
                $body['motivo'] ?? '',
                $body['dia_inteiro'] ? 1 : 0
            ]);
            jsonResponse(true, ['id' => $db->lastInsertId()], 'Bloqueio adicionado');
        }

        if ($acao === 'remover') {
            $stmt = $db->prepare("DELETE FROM bloqueios WHERE id=?");
            $stmt->execute([$body['id']]);
            jsonResponse(true, null, 'Bloqueio removido');
        }
    }
}
