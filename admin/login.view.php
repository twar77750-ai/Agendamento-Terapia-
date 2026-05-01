<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Painel Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css_login.view.css">
</head>
<body>
<div class="card">
    <div class="logo">
        <h1>Área <span>Admin</span></h1>
        <p>Painel do Terapeuta</p>
    </div>

    <!-- Caixa de feedback (erro ou sucesso) -->
    <div id="msg" class="msg" role="alert"></div>

    <form id="loginForm" novalidate>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group">
            <label for="senha">Senha</label>
            <input
                type="password"
                id="senha"
                name="senha"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="button" id="btnEntrar" onclick="Post_Parametros()">
            Entrar
            <span class="spinner" aria-hidden="true"></span>
        </button>
    </form>


</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function Post_Parametros() {
    console.log('Iniciando login...');
    // Limpa mensagem anterior
    $('#msg').removeClass('erro sucesso').text('').hide();

    let email = $('#email').val().trim();
    let senha = $('#senha').val();
    
    if (!email || !senha) {
        exibirMensagem('Preencha todos os campos.', 'erro');
        return;
    }

    //setCarregando(true);

    $.ajax({
        url: 'model/login.php',
        type: 'POST',
        data: {
            email: email,
            senha: senha
        },
        dataType: 'json',

        success: function (data) {
            if (data.sucesso) {
                //(data).mensagem, 'sucesso');
                console.log(data)   
                console.log(data.sucesso)   
                console.log(data.redirect)  

                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 800);

            } else {
                //exibirMensagem(data.mensagem, 'erro');
                setCarregando(false);
            }
        },

        error: function () {
            exibirMensagem('Erro de conexão. Tente novamente.', 'erro');
            setCarregando(false);
        }
    });
}
</script>

</body>
</html>