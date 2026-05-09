<?php
/* ============================================================
   CINERATE — register.php
   Página de criação de conta de utilizador.

   Fluxo:
     1. Se o utilizador já tem sessão activa, redireciona para index
     2. Mostra o formulário de registo
     3. Em caso de erro vindo de register_action.php (via GET), mostra mensagem
     4. Após registo com sucesso, register_action.php redireciona para login.php?sucesso=1

   Segurança:
     - Redirecionamento automático se já autenticado
     - novalidate no form — validação controlada pelo main.js
     - O processamento e hash da password são feitos em register_action.php
   ============================================================ */

session_start();

/* Redireciona para a página inicial se o utilizador já tem sessão activa —
   evita mostrar o registo a quem já está dentro */
if (isset($_SESSION['user_id'])) {
    header("Location: /cinerate/index.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <!-- Card de autenticação centrado na página -->
        <section class="auth-card">

            <h2>Criar Conta</h2>
            <p class="subtitle">Junta-te à comunidade CineRate</p>

            <!-- Mensagem de erro vinda de register_action.php via ?erro=CODIGO -->
            <?php if (isset($_GET['erro'])): ?>
                <?php
                /* Mapeia os códigos de erro para mensagens legíveis pelo utilizador */
                $erros = [
                        'email_exists' => 'Este email já está registado. Tenta fazer login.',
                        'passwords'    => 'As passwords não coincidem.',
                        'curta'        => 'A password deve ter pelo menos 6 caracteres.',
                        'campos'       => 'Preenche todos os campos obrigatórios.',
                        'geral'        => 'Ocorreu um erro inesperado. Tenta novamente.',
                ];
                $codigo = htmlspecialchars($_GET['erro']);
                $msg    = $erros[$codigo] ?? 'Erro desconhecido.';
                ?>
                <p class="error">&#9888; <?= $msg ?></p>
            <?php endif; ?>

            <!-- Formulário de registo
                 action: envia para register_action.php que valida,
                         faz hash da password e insere na base de dados
                 novalidate: a validação visual fica a cargo do main.js -->
            <form action="../actions/register_action.php" method="POST" class="form" novalidate>

                <!-- Nome completo do utilizador -->
                <input
                        type="text"
                        name="name"
                        placeholder="O teu nome completo"
                        autocomplete="name"
                        required>

                <!-- Email — usado como identificador único para login -->
                <input
                        type="email"
                        name="email"
                        placeholder="Endereço de email"
                        autocomplete="email"
                        required>

                <!-- Password — register_action.php usa password_hash() antes de guardar -->
                <input
                        type="password"
                        name="password"
                        placeholder="Password (mínimo 6 caracteres)"
                        autocomplete="new-password"
                        minlength="6"
                        required>

                <!-- Confirmação da password — comparada com o campo acima em register_action.php -->
                <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirmar password"
                        autocomplete="new-password"
                        required>

                <button type="submit" class="btn">Criar Conta</button>

            </form>

            <!-- Link para utilizadores que já têm conta -->
            <p class="text-muted mt-16">
                Já tens conta? <a href="login.php">Fazer login</a>
            </p>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>