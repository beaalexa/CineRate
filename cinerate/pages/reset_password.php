<?php
/* ============================================================
   CINERATE — reset_password.php
   Página para definir uma nova password após recuperação.

   Fluxo:
     1. Recebe o token via GET (link enviado de forgot_password.php)
     2. Verifica se o token existe na BD e se ainda não expirou
     3. Mostra formulário para introduzir nova password
     4. Após POST: valida, faz hash e actualiza a password na BD
     5. Limpa o token e data de expiração após uso — token de uso único

   Segurança:
     - Token de 64 caracteres hex (random_bytes) — não adivinhável
     - Verificação de expiração (reset_expires > NOW()) na query
     - password_hash() com PASSWORD_DEFAULT (bcrypt) para armazenar a password
     - Token apagado da BD após uso — não pode ser reutilizado
     - Confirmação de password para evitar erros de digitação
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* Variáveis de estado */
$error   = null;
$success = null;

/* --- Leitura do token ---
   Pode vir via GET (link do email) ou via POST (resubmissão do form) */
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

/* Sem token válido, termina imediatamente */
if (!$token) {
    http_response_code(400);
    die("Token inválido ou em falta.");
}

/* ============================================================
   QUERY — Verificação do token
   Confirma que o token existe E que ainda não expirou (reset_expires > NOW())
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE reset_token = ?
      AND reset_expires > NOW()
");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* Token inválido ou expirado — não continua */
if (!$user) {
    http_response_code(400);
    die("Este link de recuperação é inválido ou já expirou. Solicita um novo em <a href='forgot_password.php'>recuperar password</a>.");
}

/* ============================================================
   PROCESSAMENTO DO FORMULÁRIO (POST)
   Só corre quando o utilizador submete a nova password
   ============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password         = $_POST['password']         ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    /* Validação 1: as duas passwords têm de ser iguais */
    if ($password !== $confirm_password) {
        $error = "As passwords não coincidem.";

        /* Validação 2: comprimento mínimo de segurança */
    } elseif (strlen($password) < 6) {
        $error = "A password deve ter pelo menos 6 caracteres.";

    } else {
        /* Faz hash da nova password com bcrypt (PASSWORD_DEFAULT) —
           NUNCA guardar passwords em texto simples */
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        /* Actualiza a password e apaga o token (uso único) */
        $stmt = $pdo->prepare("
            UPDATE users
            SET password      = ?,
                reset_token   = NULL,
                reset_expires = NULL
            WHERE id = ?
        ");
        $stmt->execute([$hashedPassword, $user['id']]);

        $success = "Password alterada com sucesso! Já podes fazer login com a nova password.";
    }
}
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <section class="auth-card">

            <h2>Definir Nova Password</h2>
            <p class="subtitle">Escolhe uma nova password segura para a tua conta</p>

            <!-- Mensagem de erro de validação -->
            <?php if ($error): ?>
                <p class="error">&#9888; <?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <!-- Mensagem de sucesso + botão para ir ao login -->
                <p class="success">&#10003; <?= htmlspecialchars($success) ?></p>
                <a href="login.php" class="btn" style="margin-top:10px;">Ir para Login</a>

            <?php else: ?>
                <!-- Formulário de nova password -->
                <form method="POST" class="form" novalidate>

                    <!-- Token passado como campo oculto para o POST —
                         mantém o contexto de qual utilizador está a fazer o reset -->
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <!-- Nova password -->
                    <input
                            type="password"
                            name="password"
                            placeholder="Nova password (mínimo 6 caracteres)"
                            autocomplete="new-password"
                            minlength="6"
                            required>

                    <!-- Confirmação — tem de ser idêntica à anterior -->
                    <input
                            type="password"
                            name="confirm_password"
                            placeholder="Confirmar nova password"
                            autocomplete="new-password"
                            required>

                    <button type="submit" class="btn">Alterar Password</button>

                </form>

            <?php endif; ?>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>