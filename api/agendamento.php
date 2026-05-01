<?php
// ============================================
// api/agendamento.php — API de Agendamento
// ============================================
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

match($action) {
    'horarios'   => getHorariosDisponiveis(),
    'agendar'    => agendar(),
    'cancelar'   => cancelar(),
    default      => jsonResponse(false, null, 'Ação inválida', 400)
};

// ── Retorna horários disponíveis para uma data ──────────────────────────────
function getHorariosDisponiveis(): void {
    $data = $_GET['data'] ?? '';
    if (!$data || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        jsonResponse(false, null, 'Data inválida');
    }

    $db   = getDB();
    $date = new DateTime($data);
    $diaSemana = (int)$date->format('w'); // 0=Dom...6=Sáb

    // Verifica bloqueio total do dia
    $stmt = $db->prepare("SELECT id FROM bloqueios WHERE data_bloqueio = ? AND dia_inteiro = 1");
    $stmt->execute([$data]);
    if ($stmt->fetch()) {
        jsonResponse(true, ['horarios' => [], 'bloqueado' => true]);
    }

    // Busca disponibilidade do dia da semana
    $stmt = $db->prepare("SELECT hora_inicio, hora_fim FROM disponibilidade WHERE dia_semana = ? AND ativo = 1");
    $stmt->execute([$diaSemana]);
    $disp = $stmt->fetch();
    if (!$disp) {
        jsonResponse(true, ['horarios' => [], 'bloqueado' => false]);
    }

    // Busca agendamentos já existentes
    $stmt = $db->prepare("SELECT hora_inicio, hora_fim FROM agendamentos WHERE data_sessao = ? AND status NOT IN ('cancelado')");
    $stmt->execute([$data]);
    $ocupados = $stmt->fetchAll();

    // Busca bloqueios parciais do dia
    $stmt = $db->prepare("SELECT hora_inicio, hora_fim FROM bloqueios WHERE data_bloqueio = ? AND dia_inteiro = 0");
    $stmt->execute([$data]);
    $bloqueios = $stmt->fetchAll();

    // Busca duração da sessão
    $stmt = $db->query("SELECT duracao_sessao FROM admin LIMIT 1");
    $admin = $stmt->fetch();
    $duracao = $admin['duracao_sessao'] ?? 50;

    // Gera slots
    $inicio = strtotime($data . ' ' . $disp['hora_inicio']);
    $fim    = strtotime($data . ' ' . $disp['hora_fim']);
    $slots  = [];

    while ($inicio + ($duracao * 60) <= $fim) {
        $slotFim = $inicio + ($duracao * 60);
        $hIni = date('H:i', $inicio);
        $hFim = date('H:i', $slotFim);

        $disponivel = true;

        // Verifica conflito com agendamentos
        foreach ($ocupados as $oc) {
            $ocIni = strtotime($data . ' ' . $oc['hora_inicio']);
            $ocFim = strtotime($data . ' ' . $oc['hora_fim']);
            if ($inicio < $ocFim && $slotFim > $ocIni) {
                $disponivel = false;
                break;
            }
        }

        // Verifica conflito com bloqueios parciais
        if ($disponivel) {
            foreach ($bloqueios as $bl) {
                $blIni = strtotime($data . ' ' . $bl['hora_inicio']);
                $blFim = strtotime($data . ' ' . $bl['hora_fim']);
                if ($inicio < $blFim && $slotFim > $blIni) {
                    $disponivel = false;
                    break;
                }
            }
        }

        // Não permite agendar no passado
        if ($inicio <= time()) $disponivel = false;

        $slots[] = ['hora_inicio' => $hIni, 'hora_fim' => $hFim, 'disponivel' => $disponivel];
        $inicio = $slotFim;
    }

    jsonResponse(true, ['horarios' => $slots, 'bloqueado' => false, 'duracao' => $duracao]);
}

// ── Cria um novo agendamento ────────────────────────────────────────────────
function agendar(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, null, 'Método inválido', 405);

    $body = json_decode(file_get_contents('php://input'), true);
    $nome        = trim($body['nome'] ?? '');
    $email       = trim($body['email'] ?? '');
    $telefone    = trim($body['telefone'] ?? '');
    $data        = $body['data'] ?? '';
    $hora_inicio = $body['hora_inicio'] ?? '';
    $hora_fim    = $body['hora_fim'] ?? '';
    $obs         = trim($body['observacoes'] ?? '');

    if (!$nome || !$email || !$data || !$hora_inicio || !$hora_fim) {
        jsonResponse(false, null, 'Preencha todos os campos obrigatórios');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'E-mail inválido');
    }

    $db = getDB();

    // Verifica conflito em tempo real
    $stmt = $db->prepare("SELECT id FROM agendamentos WHERE data_sessao = ? AND hora_inicio = ? AND status NOT IN ('cancelado')");
    $stmt->execute([$data, $hora_inicio]);
    if ($stmt->fetch()) {
        jsonResponse(false, null, 'Este horário acabou de ser ocupado. Por favor, escolha outro.');
    }

    // Salva ou recupera paciente
    $stmt = $db->prepare("SELECT id FROM pacientes WHERE email = ?");
    $stmt->execute([$email]);
    $paciente = $stmt->fetch();

    if ($paciente) {
        $pacienteId = $paciente['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO pacientes (nome, email, telefone) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $telefone]);
        $pacienteId = $db->lastInsertId();
    }

    $token = bin2hex(random_bytes(32));

    $stmt = $db->prepare("INSERT INTO agendamentos (paciente_id, data_sessao, hora_inicio, hora_fim, observacoes, token_cancelamento) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$pacienteId, $data, $hora_inicio, $hora_fim, $obs, $token]);
    $agId = $db->lastInsertId();

    // Envia e-mail de confirmação
    enviarEmailConfirmacao($email, $nome, $data, $hora_inicio, $hora_fim, $token, $agId);

    jsonResponse(true, ['id' => $agId], 'Agendamento realizado com sucesso!');
}

// ── Cancela agendamento via token ───────────────────────────────────────────
function cancelar(): void {
    $token = $_GET['token'] ?? '';
    $id    = (int)($_GET['id'] ?? 0);
    if (!$token || !$id) jsonResponse(false, null, 'Parâmetros inválidos');

    $db = getDB();
    $stmt = $db->prepare("UPDATE agendamentos SET status='cancelado' WHERE id=? AND token_cancelamento=? AND status NOT IN ('cancelado','concluido')");
    $stmt->execute([$id, $token]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(false, null, 'Token inválido ou agendamento já cancelado');
    }
    jsonResponse(true, null, 'Agendamento cancelado com sucesso');
}

// ── Envio de e-mail (PHPMailer ou mail() nativo) ────────────────────────────
function enviarEmailConfirmacao(string $email, string $nome, string $data, string $hora, string $horaFim, string $token, int $id): void {
    $dataFormatada = (new DateTime($data))->format('d/m/Y');
    $linkCancelar  = BASE_URL . "/api/agendamento.php?action=cancelar&id={$id}&token={$token}";

    $assunto = "✅ Consulta confirmada — {$dataFormatada} às {$hora}";
    $corpo   = "
Olá, {$nome}!

Sua consulta foi agendada com sucesso. 🎉

📅 Data: {$dataFormatada}
🕐 Horário: {$hora} — {$horaFim}

Caso precise cancelar, clique no link abaixo (até 24h antes):
{$linkCancelar}

Qualquer dúvida, entre em contato conosco.
Até logo!
    ";

    // Tente usar PHPMailer se disponível (composer install phpmailer/phpmailer)
    // Por ora, usa mail() nativo como fallback
    $headers  = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    @mail($email, $assunto, $corpo, $headers);
}
