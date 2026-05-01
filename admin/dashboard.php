<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Painel Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css_dashboard.css">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>Terapia<span>.</span></h2>
        <p>Painel Admin</p>
    </div>
    <nav>
        <a class="nav-item active" data-page="dashboard" href="#">
            <span class="nav-icon">▦</span> Dashboard
        </a>
        <a class="nav-item" data-page="agendamentos" href="#">
            <span class="nav-icon">📅</span> Agendamentos
        </a>
        <a class="nav-item" data-page="disponibilidade" href="#">
            <span class="nav-icon">🕐</span> Disponibilidade
        </a>
        <a class="nav-item" data-page="bloqueios" href="#">
            <span class="nav-icon">🚫</span> Bloqueios
        </a>
    </nav>
    <div class="sidebar-footer">
        <p style="color:rgba(255,255,255,0.5);font-size:0.8rem;margin-bottom:8px">
            <?= htmlspecialchars($_SESSION['admin_nome']) ?>
        </p>
        <a href="logout.php">Sair →</a>
    </div>
</aside>

<!-- Main -->
<main class="main">

    <!-- Dashboard -->
    <div class="page active" id="page-dashboard">
        <div class="page-header">
            <h1>Bom dia! 👋</h1>
            <p>Resumo do seu consultório</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><div class="label">Hoje</div><div class="value" id="stat-hoje">—</div><div class="sub">consultas</div></div>
            <div class="stat-card"><div class="label">Próximos 7 dias</div><div class="value" id="stat-semana">—</div><div class="sub">agendamentos</div></div>
            <div class="stat-card"><div class="label">Pacientes</div><div class="value" id="stat-pacientes">—</div><div class="sub">cadastrados</div></div>
            <div class="stat-card"><div class="label">Pendentes</div><div class="value" id="stat-pendentes">—</div><div class="sub">aguardando confirmação</div></div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Consultas de hoje</h3></div>
            <table id="table-hoje">
                <thead><tr><th>Horário</th><th>Paciente</th><th>Contato</th><th>Status</th><th>Ação</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Agendamentos -->
    <div class="page" id="page-agendamentos">
        <div class="page-header">
            <h1>Agendamentos</h1>
            <p>Gerencie todos os agendamentos</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>Lista de agendamentos</h3>
                <div class="filters">
                    <input type="date" id="filtro-data" placeholder="Filtrar por data">
                    <select id="filtro-status">
                        <option value="">Todos os status</option>
                        <option value="pendente">Pendente</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="cancelado">Cancelado</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>
            </div>
            <table id="table-agendamentos">
                <thead><tr><th>Data</th><th>Horário</th><th>Paciente</th><th>E-mail</th><th>Status</th><th>Ação</th></tr></thead>
                <tbody></tbody>
            </table>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <!-- Disponibilidade -->
    <div class="page" id="page-disponibilidade">
        <div class="page-header">
            <h1>Disponibilidade</h1>
            <p>Configure seus horários de atendimento semanal</p>
        </div>
        <div class="card">
            <div id="disponibilidade-form"></div>
            <br>
            <button class="btn btn-primary" onclick="salvarDisponibilidade()">Salvar Disponibilidade</button>
        </div>
    </div>

    <!-- Bloqueios -->
    <div class="page" id="page-bloqueios">
        <div class="page-header">
            <h1>Bloqueios</h1>
            <p>Bloqueie datas ou horários específicos (férias, feriados, etc.)</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>Adicionar bloqueio</h3></div>
            <div class="bloqueio-form">
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" id="bl-data">
                </div>
                <div class="form-group">
                    <label>Dia inteiro?</label>
                    <select id="bl-inteiro">
                        <option value="1">Sim</option>
                        <option value="0">Não (parcial)</option>
                    </select>
                </div>
                <div class="form-group" id="bl-hora-group" style="display:none">
                    <label>Hora início</label>
                    <input type="time" id="bl-hora-inicio">
                </div>
                <div class="form-group" id="bl-hora-fim-group" style="display:none">
                    <label>Hora fim</label>
                    <input type="time" id="bl-hora-fim">
                </div>
                <div class="form-group">
                    <label>Motivo</label>
                    <input type="text" id="bl-motivo" placeholder="Ex: Férias, Feriado...">
                </div>
            </div>
            <button class="btn btn-primary" onclick="adicionarBloqueio()">Adicionar Bloqueio</button>
        </div>
        <div class="card">
            <div class="card-header"><h3>Bloqueios futuros</h3></div>
            <table id="table-bloqueios">
                <thead><tr><th>Data</th><th>Período</th><th>Motivo</th><th>Ação</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</main>

<!-- Modal de Status -->
<div class="modal-overlay" id="modal-status">
    <div class="modal">
        <h3>Atualizar Status</h3>
        <input type="hidden" id="modal-ag-id">
        <div class="form-group">
            <label>Novo Status</label>
            <select id="modal-status-select">
                <option value="pendente">Pendente</option>
                <option value="confirmado">Confirmado</option>
                <option value="cancelado">Cancelado</option>
                <option value="concluido">Concluído</option>
            </select>
        </div>
        <div class="modal-actions">
            <button class="btn" onclick="fecharModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="salvarStatus()">Salvar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE = '<?= BASE_URL ?>';
let currentPage = 1;

const DIAS = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];

// Navegação
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', e => {
        e.preventDefault();
        const page = item.dataset.page;
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        item.classList.add('active');
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        document.getElementById('page-' + page).classList.add('active');
        if (page === 'dashboard')      carregarDashboard();
        if (page === 'agendamentos')   carregarAgendamentos();
        if (page === 'disponibilidade') carregarDisponibilidade();
        if (page === 'bloqueios')      carregarBloqueios();
    });
});

// Toast
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast show ${type}`;
    setTimeout(() => t.className = 'toast', 3000);
}

// API helper
async function api(url, opts = {}) {
    const r = await fetch(BASE + url, { headers: { 'Content-Type': 'application/json' }, ...opts });
    return r.json();
}

// ── Dashboard ────────────────────────────────────────────────────────────────
async function carregarDashboard() {
    const { data } = await api('/api/admin.php?action=dashboard');
    document.getElementById('stat-hoje').textContent      = data.hoje;
    document.getElementById('stat-semana').textContent    = data.semana;
    document.getElementById('stat-pacientes').textContent = data.total_pacientes;
    document.getElementById('stat-pendentes').textContent = data.pendentes;

    const tbody = document.querySelector('#table-hoje tbody');
    if (!data.consultas_hoje.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;padding:24px">Nenhuma consulta hoje</td></tr>';
        return;
    }
    tbody.innerHTML = data.consultas_hoje.map(a => `
        <tr>
            <td>${a.hora_inicio.slice(0,5)} — ${a.hora_fim.slice(0,5)}</td>
            <td>${a.nome}</td>
            <td>${a.email}<br><small>${a.telefone || ''}</small></td>
            <td><span class="badge badge-${a.status}">${a.status}</span></td>
            <td><button class="btn btn-sm btn-primary" onclick="abrirModal(${a.id},'${a.status}')">Status</button></td>
        </tr>
    `).join('');
}

// ── Agendamentos ─────────────────────────────────────────────────────────────
async function carregarAgendamentos(page = 1) {
    currentPage = page;
    const data   = document.getElementById('filtro-data').value;
    const status = document.getElementById('filtro-status').value;
    const r = await api(`/api/admin.php?action=agendamentos&page=${page}&data=${data}&status=${status}`);
    const d = r.data;

    const tbody = document.querySelector('#table-agendamentos tbody');
    if (!d.agendamentos.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#999;padding:24px">Nenhum resultado</td></tr>';
    } else {
        tbody.innerHTML = d.agendamentos.map(a => `
            <tr>
                <td>${formatarData(a.data_sessao)}</td>
                <td>${a.hora_inicio.slice(0,5)} — ${a.hora_fim.slice(0,5)}</td>
                <td>${a.nome}</td>
                <td>${a.email}</td>
                <td><span class="badge badge-${a.status}">${a.status}</span></td>
                <td><button class="btn btn-sm btn-primary" onclick="abrirModal(${a.id},'${a.status}')">Status</button></td>
            </tr>
        `).join('');
    }

    // Paginação
    const pg = document.getElementById('pagination');
    pg.innerHTML = '';
    for (let i = 1; i <= d.pages; i++) {
        const b = document.createElement('button');
        b.className = 'page-btn' + (i === page ? ' active' : '');
        b.textContent = i;
        b.onclick = () => carregarAgendamentos(i);
        pg.appendChild(b);
    }
}

document.getElementById('filtro-data').addEventListener('change', () => carregarAgendamentos());
document.getElementById('filtro-status').addEventListener('change', () => carregarAgendamentos());

// ── Disponibilidade ───────────────────────────────────────────────────────────
async function carregarDisponibilidade() {
    const { data } = await api('/api/admin.php?action=disponibilidade');
    const diasAtivos = {};
    data.forEach(d => { diasAtivos[d.dia_semana] = d; });

    const container = document.getElementById('disponibilidade-form');
    container.innerHTML = [1,2,3,4,5,6,0].map(dia => {
        const d = diasAtivos[dia] || {};
        const ativo = d.ativo ?? (dia >= 1 && dia <= 5 ? 1 : 0);
        return `
        <div class="day-row" data-dia="${dia}">
            <div class="day-name">${DIAS[dia]}</div>
            <div class="day-toggle">
                <input type="checkbox" id="d${dia}" ${ativo ? 'checked' : ''}>
                <label for="d${dia}"></label>
            </div>
            <div class="time-inputs">
                <input type="time" class="hi" value="${d.hora_inicio || '08:00'}">
                <span>às</span>
                <input type="time" class="hf" value="${d.hora_fim || '18:00'}">
            </div>
        </div>`;
    }).join('');
}

async function salvarDisponibilidade() {
    const slots = [];
    document.querySelectorAll('.day-row').forEach(row => {
        slots.push({
            dia_semana:  parseInt(row.dataset.dia),
            hora_inicio: row.querySelector('.hi').value,
            hora_fim:    row.querySelector('.hf').value,
            ativo:       row.querySelector('input[type=checkbox]').checked ? 1 : 0
        });
    });
    const r = await api('/api/admin.php?action=disponibilidade', {
        method: 'POST', body: JSON.stringify({ slots })
    });
    showToast(r.message, r.success ? 'success' : 'error');
}

// ── Bloqueios ─────────────────────────────────────────────────────────────────
document.getElementById('bl-inteiro').addEventListener('change', function() {
    const parcial = this.value === '0';
    document.getElementById('bl-hora-group').style.display     = parcial ? '' : 'none';
    document.getElementById('bl-hora-fim-group').style.display = parcial ? '' : 'none';
});

async function carregarBloqueios() {
    const { data } = await api('/api/admin.php?action=bloqueios');
    const tbody = document.querySelector('#table-bloqueios tbody');
    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#999;padding:24px">Nenhum bloqueio cadastrado</td></tr>';
        return;
    }
    tbody.innerHTML = data.map(b => `
        <tr>
            <td>${formatarData(b.data_bloqueio)}</td>
            <td>${b.dia_inteiro ? 'Dia inteiro' : `${b.hora_inicio?.slice(0,5)} — ${b.hora_fim?.slice(0,5)}`}</td>
            <td>${b.motivo || '—'}</td>
            <td><button class="btn btn-sm btn-danger" onclick="removerBloqueio(${b.id})">Remover</button></td>
        </tr>
    `).join('');
}

async function adicionarBloqueio() {
    const data = document.getElementById('bl-data').value;
    if (!data) { showToast('Selecione uma data', 'error'); return; }
    const inteiro = document.getElementById('bl-inteiro').value === '1';
    const payload = {
        acao: 'adicionar', data,
        motivo: document.getElementById('bl-motivo').value,
        dia_inteiro: inteiro,
        hora_inicio: inteiro ? null : document.getElementById('bl-hora-inicio').value,
        hora_fim:    inteiro ? null : document.getElementById('bl-hora-fim').value,
    };
    const r = await api('/api/admin.php?action=bloqueios', { method: 'POST', body: JSON.stringify(payload) });
    showToast(r.message, r.success ? 'success' : 'error');
    if (r.success) carregarBloqueios();
}

async function removerBloqueio(id) {
    if (!confirm('Remover este bloqueio?')) return;
    const r = await api('/api/admin.php?action=bloqueios', { method: 'POST', body: JSON.stringify({ acao: 'remover', id }) });
    showToast(r.message, r.success ? 'success' : 'error');
    if (r.success) carregarBloqueios();
}

// ── Modal Status ───────────────────────────────────────────────────────────────
function abrirModal(id, status) {
    document.getElementById('modal-ag-id').value = id;
    document.getElementById('modal-status-select').value = status;
    document.getElementById('modal-status').classList.add('open');
}
function fecharModal() {
    document.getElementById('modal-status').classList.remove('open');
}
async function salvarStatus() {
    const id     = document.getElementById('modal-ag-id').value;
    const status = document.getElementById('modal-status-select').value;
    const r = await api('/api/admin.php?action=agendamento_update', {
        method: 'POST', body: JSON.stringify({ id, status })
    });
    showToast(r.message, r.success ? 'success' : 'error');
    fecharModal();
    carregarDashboard();
    if (document.getElementById('page-agendamentos').classList.contains('active')) carregarAgendamentos(currentPage);
}

// Helpers
function formatarData(str) {
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
}

// Init
carregarDashboard();
</script>
</body>
</html>
