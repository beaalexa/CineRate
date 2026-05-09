<?php
/* ============================================================
   CINERATE — login.php
   Página de autenticação do utilizador.

   Fluxo:
     1. Se o utilizador já tem sessão activa, redireciona para index
     2. Mostra o formulário de login
     3. Em caso de erro (vindo de login_action.php via GET), mostra mensagem
     4. Fornece links para registo e recuperação de password
   ============================================================ */

/* Inicia a sessão para verificar se o utilizador já está autenticado */
session_start();

/* Redireciona para a página inicial se o utilizador já tem sessão activa —
   evita mostrar o login a quem já está dentro */
if (isset($_SESSION['user_id'])) {
    header("Location: /cinerate/index.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <!-- Card de autenticação centrado na página -->
        <section class="auth-card">

            <h2>Bem-vindo de volta</h2>
            <p class="subtitle">Entra na tua conta para continuar</p>

            <!-- Mensagem de erro — mostrada quando login_action.php redireciona com ?erro=1 -->
            <?php if (isset($_GET['erro'])): ?>
                <p class="error">&#9888; Email ou password incorretos. Tenta novamente.</p>
            <?php endif; ?>

            <!-- Mensagem de sucesso após registo — vinda de ?sucesso=1 -->
            <?php if (isset($_GET['sucesso'])): ?>
                <p class="success">&#10003; Conta criada com sucesso! Podes fazer login agora.</p>
            <?php endif; ?>

            <!-- Formulário de login
                 action: envia os dados para login_action.php via POST (mais seguro que GET)
                 novalidate: a validação é feita pelo main.js para melhor feedback visual -->
            <form action="../actions/login_action.php" method="POST" class="form" novalidate>

                <!-- Campo de email — type="email" activa a validação nativa do browser -->
                <input
                        type="email"
                        name="email"
                        placeholder="O teu email"
                        autocomplete="email"
                        required>

                <!-- Campo de password — autocomplete="current-password" ajuda os gestores de passwords -->
                <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        autocomplete="current-password"
                        required>

                <button type="submit" class="btn">Entrar</button>

            </form>

            <!-- Link para recuperação de password -->
            <p class="mt-16">
                <a href="forgot_password.php">Esqueceste-te da password?</a>
            </p>

            <!-- Link para criar conta nova -->
            <p class="text-muted mt-8">
                Ainda não tens conta? <a href="register.php">Criar conta</a>
            </p>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>