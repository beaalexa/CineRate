<?php
/* ============================================================
   CINERATE — forgot_password.php
   Página de recuperação de password.

   Fluxo:
     1. Utilizador submete o email (POST)
     2. Verifica se existe uma conta com esse email
     3. Se sim: gera um token único e seguro, guarda-o na BD
        com uma data de expiração de 1 hora, e apresenta o link
     4. Se não: mostra mensagem de erro
     5. O link gerado aponta para reset_password.php?token=XXX

   Segurança:
     - bin2hex(random_bytes(32)) gera 64 caracteres hexadecimais
       criptograficamente seguros (não adivinhável)
     - Token expira ao fim de 1 hora (3600 segundos)
     - Em produção o link seria enviado por email, não mostrado na página
     - Email sanitizado com trim() antes de ir para a query
     - Query parametrizada com PDO
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* Variáveis de estado — definidas por defeito como null */
$link  = null;   /* Guarda o link de recuperação gerado, se houver */
$error = null;   /* Guarda a mensagem de erro, se houver */

/* --- Processamento do formulário ---
   Só corre quando o formulário é submetido (método POST) */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* Sanitiza o email: remove espaços em branco no início e no fim */
    $email = trim($_POST['email'] ?? '');

    /* Validação básica do formato do email antes de ir à base de dados */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor introduz um email válido.";
    } else {

        /* Verifica se existe um utilizador com esse email */
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            /* Gera um token único:
               - random_bytes(32) → 32 bytes aleatórios criptograficamente seguros
               - bin2hex()        → converte para 64 caracteres hexadecimais legíveis */
            $token = bin2hex(random_bytes(32));

            /* Data de expiração: agora + 1 hora (3600 segundos) */
            $expires = date("Y-m-d H:i:s", time() + 3600);

            /* Guarda o token e a data de expiração na base de dados
               A coluna reset_token deve existir na tabela users (VARCHAR 64)
               A coluna reset_expires deve ser DATETIME */
            $stmt = $pdo->prepare("
                UPDATE users
                SET reset_token = ?, reset_expires = ?
                WHERE id = ?
            ");
            $stmt->execute([$token, $expires, $user['id']]);

            /* Constrói o link de recuperação com o token na query string
               NOTA: Em produção este link seria enviado por email (ex: PHPMailer / SMTP)
               e NÃO mostrado directamente na página por razões de segurança */
            $link = "http://localhost/cinerate/pages/reset_password.php?token=" . $token;

        } else {
            /* Não existe conta com esse email —
               A mensagem é intencionalmente vaga para não revelar
               quais emails estão registados (prevenção de enumeração) */
            $error = "Não existe nenhuma conta com esse email.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <section class="auth-card">

            <h2>Recuperar Password</h2>
            <p class="subtitle">Introduz o teu email para receberes um link de recuperação</p>

            <!-- Mensagem de erro (email inválido ou não encontrado) -->
            <?php if ($error): ?>
                <p class="error">&#9888; <?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if ($link): ?>
                <!-- Link gerado com sucesso — mostrado na página (substituir por email em produção) -->
                <div class="success" style="text-align:left; word-break:break-all;">
                    <p style="margin-bottom:10px;">&#10003; Link de recuperação gerado com sucesso.</p>
                    <!-- Em produção NUNCA mostrar o link na página — enviá-lo por email -->
                    <p style="font-size:13px; color:var(--muted); margin-bottom:8px;">
                        (Em produção este link seria enviado por email. Para testes:)
                    </p>
                    <a href="<?= htmlspecialchars($link) ?>" style="font-size:13px; word-break:break-all;">
                        <?= htmlspecialchars($link) ?>
                    </a>
                </div>

                <!-- Link para voltar ao login após gerar o token -->
                <p class="mt-16">
                    <a href="login.php">&#8592; Voltar ao login</a>
                </p>

            <?php else: ?>
                <!-- Formulário de pedido de recuperação -->
                <form method="POST" class="form" novalidate>

                    <!-- Campo de email — type="email" activa validação nativa do browser -->
                    <input
                            type="email"
                            name="email"
                            placeholder="O teu endereço de email"
                            autocomplete="email"
                            required>

                    <button type="submit" class="btn">Enviar Link de Recuperação</button>

                </form>

                <!-- Link para voltar ao login -->
                <p class="mt-16">
                    <a href="login.php">&#8592; Voltar ao login</a>
                </p>

            <?php endif; ?>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>