# 🌿 Sistema de Agendamento — Terapia

Aplicação web completa para agendamento de consultas terapêuticas com painel administrativo.

---

## 📁 Estrutura de Arquivos

```
terapia/
├── index.php                  ← Página pública de agendamento
├── config.php                 ← Configurações (DB, e-mail, etc.)
├── database/
│   └── schema.sql             ← Script do banco de dados
├── api/
│   ├── agendamento.php        ← API pública (horários, agendar, cancelar)
│   └── admin.php              ← API privada do painel admin
└── admin/
    ├── login.php              ← Tela de login do terapeuta
    ├── dashboard.php          ← Painel completo do admin
    └── logout.php             ← Logout
```

---

## ⚡ Instalação

### 1. Requisitos
- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10.4+
- Servidor web: Apache ou Nginx (ou XAMPP/Laragon localmente)

### 2. Configurar o banco de dados
```sql
-- Execute no MySQL:
source /caminho/para/terapia/database/schema.sql
```
Ou importe o arquivo `schema.sql` via phpMyAdmin.

### 3. Editar `config.php`
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'TerapiaDb');
define('DB_USER', 'root');
define('DB_PASS', 'Tony34215');

define('BASE_URL', 'http://localhost/terapia'); // ou seu domínio

// E-mail (configure com sua conta Gmail + Senha de App)
define('MAIL_USER', 'seu@gmail.com');
define('MAIL_PASS', 'xxxx xxxx xxxx xxxx'); // senha de app Gmail
```

### 4. Colocar os arquivos no servidor
- Copie a pasta `terapia/` para `htdocs/` (XAMPP) ou `www/` (Laragon)
- Ou envie via FTP para a raiz do seu domínio

---

## 🔐 Acesso Admin

| Campo | Valor padrão |
|-------|-------------|
| URL   | `http://seusite.com/admin/login.php` |
| E-mail | `admin@terapia.com` |
| Senha | `Admin@123` |

> ⚠️ **Troque a senha após o primeiro login!**
> Execute no MySQL:
> ```sql
> UPDATE admin SET senha = '$2y$10$NOVO_HASH' WHERE email = 'admin@terapia.com';
> ```
> Gere um hash com: `php -r "echo password_hash('SuaNovaSenha', PASSWORD_DEFAULT);"`

---

## 📧 Configurar E-mail (Gmail)

1. Ative a **verificação em duas etapas** na sua conta Google
2. Acesse: https://myaccount.google.com/apppasswords
3. Crie uma "Senha de app" para "Email"
4. Cole a senha gerada em `MAIL_PASS` no `config.php`

> Para produção, recomenda-se usar PHPMailer via Composer:
> ```bash
> composer require phpmailer/phpmailer
> ```
> E atualizar a função `enviarEmailConfirmacao()` em `api/agendamento.php`

---

## 🌟 Funcionalidades

### Paciente (público)
- ✅ Calendário interativo com datas disponíveis
- ✅ Horários dinâmicos por sessão configurada
- ✅ Formulário de agendamento em 3 passos
- ✅ E-mail de confirmação com link de cancelamento
- ✅ Cancelamento via link único (token seguro)

### Terapeuta (admin)
- ✅ Dashboard com estatísticas do dia/semana
- ✅ Lista de agendamentos com filtros
- ✅ Atualização de status (pendente/confirmado/cancelado/concluído)
- ✅ Gestão de disponibilidade semanal (dias e horários)
- ✅ Bloqueios por data (folgas, feriados, horários parciais)

---

## 🛠️ Personalização

### Alterar nome e dados do terapeuta
```sql
UPDATE admin SET
    nome = 'Dr. João Silva',
    crp  = 'CRP 06/123456',
    bio  = 'Sua bio aqui...',
    duracao_sessao = 50
WHERE id = 1;
```

### Alterar cores
Edite as variáveis CSS em `index.php` e `admin/dashboard.php`:
```css
:root {
    --sage:      #7a8c78;  /* verde sage */
    --accent:    #b5896a;  /* terracota */
    --cream:     #f5f0e8;  /* fundo creme */
    --warm-dark: #2c2c2a;  /* texto escuro */
}
```

---

## 🔒 Segurança (produção)

- [ ] Troque a senha padrão do admin
- [ ] Configure HTTPS (Let's Encrypt)
- [ ] Adicione rate limiting nas APIs
- [ ] Coloque o `config.php` fora da raiz pública
- [ ] Use variáveis de ambiente em vez de constantes no config

---

## 🐛 Problemas comuns

**Erro de conexão com BD:** Verifique as credenciais em `config.php`

**E-mail não chega:** Confirme que usou uma "Senha de App" do Gmail (não a senha normal)

**Horários não aparecem:** Verifique se há disponibilidade cadastrada para o dia da semana no painel admin

**Erro 404 nas APIs:** Confirme o `BASE_URL` e que o mod_rewrite está ativo (Apache)
