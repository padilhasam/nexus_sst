<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - Sistema de Relatórios</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS customizado -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/login.css">

    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/image/favicon.png?v=2">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/image/favicon.ico?v=2" type="image/x-icon">

</head>
<body class="login-page">

<div class="login-container">

    <div class="login-card">

        <div class="text-center mb-4">

            <img
                src="<?= BASE_URL ?>/image/logo.png"
                alt="NEXUS SST"
                class="logo-login"
            >

            <p class="text-muted">
                Inteligência para Levantamentos Técnicos
            </p>

        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger fade show" id="alertErro">
                <?= htmlspecialchars($erro) ?>
            </div>

            <script>
                setTimeout(() => {
                    const alerta = document.getElementById('alertErro');
                    if (alerta) {
                        alerta.style.transition = "opacity 0.5s ease";
                        alerta.style.opacity = "0";

                        setTimeout(() => alerta.remove(), 500);
                    }
                }, 3000); // 3 segundos
            </script>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login/autenticar">

            <div class="mb-3">
                <label class="form-label">E-mail</label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                    >
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Senha</label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>

                    <input
                        type="password"
                        name="senha"
                        id="senha"
                        class="form-control"
                        required
                    >

                    <button
                        class="btn btn-outline-secondary"
                        type="button"
                        onclick="toggleSenha()">

                        <i class="fas fa-eye"></i>

                    </button>

                </div>
            </div>

            <div class="d-grid">

                <button
                    type="submit"
                    class="btn btn-primary btn-login">

                    <i class="fas fa-sign-in-alt me-2"></i>
                    Entrar

                </button>

            </div>

        </form>

        <div class="text-center mt-4 small text-muted">
            NEXUS SST • Versão 1.0
        </div>

    </div>

</div>

<script>
function toggleSenha() {
    const campo = document.getElementById('senha');

    campo.type =
        campo.type === 'password'
        ? 'text'
        : 'password';
}
</script>

</body>
</html>
