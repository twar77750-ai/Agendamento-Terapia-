<?php
require_once __DIR__ . '/config.php';
// Busca dados do admin para exibir no front
$db = getDB();
$admin = $db->query("SELECT nome, crp, bio, duracao_sessao FROM admin LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Consulta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css_index.css">
</head>
<body>

<!-- Hero -->
<section class="hero">
    <div class="hero-left">
        <p class="tag">Psicologia Clínica</p>
        <h1>
            Um espaço seguro para<br>
            <em>sua jornada</em><br>
            de cuidado
        </h1>
        <p><?= htmlspecialchars($admin['bio'] ?: 'Atendimento humanizado, respeitoso e comprometido com o seu bem-estar emocional.') ?></p>
        <div class="crp-badge">
            🎓 <?= htmlspecialchars($admin['nome']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($admin['crp']) ?>
        </div>
    </div>
    <div class="hero-right">
        <p class="tag">Próximo passo</p>
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:clamp(1.8rem,3vw,2.6rem);font-weight:300;line-height:1.2;margin-bottom:16px">
            Agende sua<br>primeira consulta
        </h2>
        <p style="color:var(--sage);font-size:0.9rem;line-height:1.8;margin-bottom:28px">
            Sessões de <?= $admin['duracao_sessao'] ?> minutos. Escolha o melhor dia e horário para você.
        </p>
        <a href="#agendar" class="btn btn-primary" style="display:inline-block;text-decoration:none;width:fit-content">
            Agendar agora →
        </a>
    </div>
</section>

<!-- Booking -->
<section class="booking-section" id="agendar">
    <div class="container">
        <div class="section-title">
            <p class="tag">Agendamento Online</p>
            <h2>Escolha seu horário</h2>
            <div class="divider"></div>
            <p>Processo simples em 3 etapas</p>
        </div>

        <!-- Stepper -->
        <div class="stepper">
            <div class="step active" data-step="1">
                <div class="step-num">1</div>
                <div class="step-label">Data & Hora</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-num">2</div>
                <div class="step-label">Seus dados</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-num">3</div>
                <div class="step-label">Confirmação</div>
            </div>
        </div>

        <!-- Step 1: Calendário + Horários -->
        <div class="form-step active" id="step1">
            <div class="calendar-wrap">
                <div class="cal-header">
                    <button class="cal-nav" id="prev-month">‹</button>
                    <h3 id="cal-title"></h3>
                    <button class="cal-nav" id="next-month">›</button>
                </div>
                <div class="cal-grid" id="cal-grid"></div>
                <div class="slots-wrap" id="slots-wrap" style="display:none">
                    <div class="slots-title">Horários disponíveis</div>
                    <div class="slots-grid" id="slots-grid"></div>
                </div>
            </div>
            <div class="btn-row" style="justify-content:flex-end">
                <button class="btn btn-primary" id="btn-step1" disabled onclick="irParaStep(2)">Continuar →</button>
            </div>
        </div>

        <!-- Step 2: Dados pessoais -->
        <div class="form-step" id="step2">
            <div class="error-msg" id="form-error"></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome completo *</label>
                    <input type="text" id="f-nome" placeholder="Seu nome" required>
                </div>
                <div class="form-group">
                    <label>E-mail *</label>
                    <input type="email" id="f-email" placeholder="seu@email.com" required>
                </div>
                <div class="form-group">
                    <label>WhatsApp / Telefone</label>
                    <input type="tel" id="f-tel" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group full">
                    <label>Observações (opcional)</label>
                    <textarea id="f-obs" placeholder="Ex: Primeira consulta, quero falar sobre ansiedade..."></textarea>
                </div>
            </div>
            <div class="btn-row">
                <button class="btn btn-outline" onclick="irParaStep(1)">← Voltar</button>
                <button class="btn btn-primary" onclick="irParaStep(3)">Continuar →</button>
            </div>
        </div>

        <!-- Step 3: Revisão + Confirmar -->
        <div class="form-step" id="step3">
            <div class="resumo">
                <h4>Resumo do agendamento</h4>
                <div class="resumo-row"><span class="key">Profissional</span><span><?= htmlspecialchars($admin['nome']) ?></span></div>
                <div class="resumo-row"><span class="key">Data</span><span id="r-data">—</span></div>
                <div class="resumo-row"><span class="key">Horário</span><span id="r-hora">—</span></div>
                <div class="resumo-row"><span class="key">Duração</span><span><?= $admin['duracao_sessao'] ?> minutos</span></div>
                <div class="resumo-row"><span class="key">Paciente</span><span id="r-nome">—</span></div>
                <div class="resumo-row"><span class="key">E-mail</span><span id="r-email">—</span></div>
            </div>
            <div class="error-msg" id="confirm-error"></div>
            <p style="font-size:0.82rem;color:var(--sage);margin-bottom:24px;text-align:center">
                Um e-mail de confirmação será enviado com o link para cancelamento, caso necessário.
            </p>
            <div class="btn-row">
                <button class="btn btn-outline" onclick="irParaStep(2)">← Voltar</button>
                <button class="btn btn-primary" id="btn-confirmar" onclick="confirmarAgendamento()">Confirmar Agendamento</button>
            </div>
        </div>

        <!-- Sucesso -->
        <div class="form-step" id="step-success">
            <div class="success-screen">
                <div class="success-icon">✓</div>
                <h2>Agendamento confirmado!</h2>
                <p>Verifique seu e-mail para os detalhes e o link de cancelamento, se precisar remarcá-lo.</p>
                <br><br>
                <button class="btn btn-outline" onclick="reiniciar()">Fazer novo agendamento</button>
            </div>
        </div>

    </div>
</section>

<footer>
    <p><?= htmlspecialchars($admin['nome']) ?> · <?= htmlspecialchars($admin['crp']) ?></p>
    <br>
    <a href="admin/login.view.php">Área administrativa</a>
</footer>

<script>
const BASE = '<?= BASE_URL ?>';
let selectedDate = null;
let selectedSlot = null;
const today = new Date();
today.setHours(0,0,0,0);
let calYear  = today.getFullYear();
let calMonth = today.getMonth();

const MESES = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const DIAS_SEMANA = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

// ── Calendário ────────────────────────────────────────────────────────────────
function renderCalendar() {
    document.getElementById('cal-title').textContent = `${MESES[calMonth]} ${calYear}`;
    const grid = document.getElementById('cal-grid');
    grid.innerHTML = DIAS_SEMANA.map(d => `<div class="cal-day-name">${d}</div>`).join('');

    const first = new Date(calYear, calMonth, 1).getDay();
    const days  = new Date(calYear, calMonth + 1, 0).getDate();
    const now   = new Date(); now.setHours(0,0,0,0);

    for (let i = 0; i < first; i++) grid.insertAdjacentHTML('beforeend', '<div class="cal-day empty"></div>');

    for (let d = 1; d <= days; d++) {
        const date = new Date(calYear, calMonth, d);
        const str  = toYMD(date);
        const past = date < now;
        const sel  = str === selectedDate;
        let cls = 'cal-day';
        if (past)        cls += ' disabled';
        if (sel)         cls += ' selected';
        if (date.getTime() === now.getTime()) cls += ' today';

        grid.insertAdjacentHTML('beforeend',
            `<div class="${cls}" data-date="${str}">${d}</div>`
        );
    }

    grid.querySelectorAll('.cal-day:not(.empty):not(.disabled)').forEach(el => {
        el.addEventListener('click', () => selecionarData(el.dataset.date));
    });
}

async function selecionarData(str) {
    selectedDate = str;
    selectedSlot = null;
    document.getElementById('btn-step1').disabled = true;
    renderCalendar();
    const wrap = document.getElementById('slots-wrap');
    const grid = document.getElementById('slots-grid');
    wrap.style.display = 'block';
    grid.innerHTML = '<div class="slot-loading">Carregando horários...</div>';

    const r = await fetch(`${BASE}/api/agendamento.php?action=horarios&data=${str}`).then(r => r.json());

    if (!r.success || r.data.bloqueado || !r.data.horarios.length) {
        grid.innerHTML = '<div class="slot-loading" style="color:#b0a090">Nenhum horário disponível para esta data.</div>';
        return;
    }

    grid.innerHTML = r.data.horarios.map(h => {
        const cls = h.disponivel ? 'slot' : 'slot occupied';
        return `<div class="${cls}" data-inicio="${h.hora_inicio}" data-fim="${h.hora_fim}">${h.hora_inicio}</div>`;
    }).join('');

    grid.querySelectorAll('.slot:not(.occupied)').forEach(el => {
        el.addEventListener('click', () => {
            grid.querySelectorAll('.slot').forEach(s => s.classList.remove('selected'));
            el.classList.add('selected');
            selectedSlot = { inicio: el.dataset.inicio, fim: el.dataset.fim };
            document.getElementById('btn-step1').disabled = false;
        });
    });
}

document.getElementById('prev-month').addEventListener('click', () => {
    calMonth--; if (calMonth < 0) { calMonth = 11; calYear--; }
    renderCalendar();
});
document.getElementById('next-month').addEventListener('click', () => {
    calMonth++; if (calMonth > 11) { calMonth = 0; calYear++; }
    renderCalendar();
});

// ── Stepper ───────────────────────────────────────────────────────────────────
function irParaStep(step) {
    if (step === 2) {
        if (!selectedDate || !selectedSlot) return;
    }
    if (step === 3) {
        const nome  = document.getElementById('f-nome').value.trim();
        const email = document.getElementById('f-email').value.trim();
        if (!nome || !email) {
            mostrarErro('form-error', 'Preencha nome e e-mail.');
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            mostrarErro('form-error', 'E-mail inválido.');
            return;
        }
        ocultarErro('form-error');

        // Preencher resumo
        const [y, m, d] = selectedDate.split('-');
        document.getElementById('r-data').textContent  = `${d}/${m}/${y} (${DIAS_SEMANA[new Date(selectedDate).getDay()]})`;
        document.getElementById('r-hora').textContent  = `${selectedSlot.inicio} — ${selectedSlot.fim}`;
        document.getElementById('r-nome').textContent  = nome;
        document.getElementById('r-email').textContent = email;
    }

    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById(`step${step}`).classList.add('active');

    document.querySelectorAll('.step').forEach(s => {
        const n = parseInt(s.dataset.step);
        s.classList.toggle('active', n === step);
        s.classList.toggle('done', n < step);
    });
}

// ── Confirmar Agendamento ─────────────────────────────────────────────────────
async function confirmarAgendamento() {
    const btn = document.getElementById('btn-confirmar');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    const payload = {
        data:        selectedDate,
        hora_inicio: selectedSlot.inicio,
        hora_fim:    selectedSlot.fim,
        nome:        document.getElementById('f-nome').value.trim(),
        email:       document.getElementById('f-email').value.trim(),
        telefone:    document.getElementById('f-tel').value.trim(),
        observacoes: document.getElementById('f-obs').value.trim(),
    };

    try {
        const r = await fetch(`${BASE}/api/agendamento.php?action=agendar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(r => r.json());

        if (r.success) {
            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
            document.getElementById('step-success').classList.add('active');
            document.querySelectorAll('.step').forEach(s => { s.classList.remove('active'); s.classList.add('done'); });
        } else {
            mostrarErro('confirm-error', r.message || 'Erro ao agendar. Tente novamente.');
            btn.disabled = false;
            btn.textContent = 'Confirmar Agendamento';
        }
    } catch {
        mostrarErro('confirm-error', 'Erro de conexão. Tente novamente.');
        btn.disabled = false;
        btn.textContent = 'Confirmar Agendamento';
    }
}

function reiniciar() {
    selectedDate = null; selectedSlot = null;
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step1').classList.add('active');
    document.querySelectorAll('.step').forEach((s, i) => { s.classList.toggle('active', i === 0); s.classList.remove('done'); });
    document.getElementById('slots-wrap').style.display = 'none';
    renderCalendar();
    window.scrollTo({ top: document.getElementById('agendar').offsetTop, behavior: 'smooth' });
}

function mostrarErro(id, msg) { const el = document.getElementById(id); el.textContent = msg; el.classList.add('show'); }
function ocultarErro(id) { document.getElementById(id).classList.remove('show'); }
function toYMD(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }

// Init
renderCalendar();
</script>
</body>
</html>
