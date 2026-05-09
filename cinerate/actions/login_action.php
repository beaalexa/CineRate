<?php
/* ============================================================
   CINERATE — login_action.php
   Action de processamento do formulário de login.

   Recebe email e password via POST de login.php e:
     1. Valida que é um pedido POST
     2. Sanitiza o email recebido
     3. Procura o utilizador na base de dados pelo email
     4. Verifica a password com password_verify() (bcrypt)
     5. Se correcto: regenera o ID de sessão e guarda os dados
     6. Se incorrecto: redireciona com código de erro

   Segurança:
     - password_verify() compara com o hash bcrypt — nunca texto simples
     - session_regenerate_id(true) previne Session Fixation attacks
     - Mensagem de erro vaga ("email ou password incorretos") —
       não revela se o email existe ou não (previne enumeração)
     - htmlspecialchars() no email antes da query (defesa extra,
       mas a query parametrizada já protege contra SQL Injection)
     - try/catch para erros de base de dados
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Verificação do método HTTP ---
   Esta action só deve ser chamada por POST — redireciona qualquer outro */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/login.php");
    exit();
}

/* ============================================================
   PROCESSAMENTO DO LOGIN
   ============================================================ */
$email    = trim(htmlspecialchars($_POST['email']    ?? ''));
$password = $_POST['password'] ?? '';

/* Campos em branco — redireciona de volta com erro */
if ($email === '' || $password === '') {
    header("Location: ../pages/login.php?erro=campos");
    exit();
}

try {
    /* ============================================================
       QUERY — Procura o utilizador pelo email
       Usa query parametrizada — previne SQL Injection
       ============================================================ */
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    /* password_verify() compara a password em texto simples
       com o hash bcrypt guardado na base de dados.
       Devolve true apenas se a password estiver correcta. */
    if ($user && password_verify($password, $user['password'])) {

        /* session_regenerate_id(true) cria um novo ID de sessão e
           apaga a sessão antiga — previne Session Fixation:
           um atacante que tenha obtido o ID de sessão anterior
           fica sem acesso após o login */
        session_regenerate_id(true);

        /* Guarda os dados do utilizador na sessão.
           Estes valores ficam disponíveis em todas as páginas
           enquanto a sessão estiver activa. */
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_theme'] = $user['theme'] ?? 'dark';
        $_SESSION['user_role']  = $user['role']  ?? 'user';

        /* Redireciona para a página inicial após login com sucesso */
        header("Location: ../index.php");
        exit();

    } else {

        /* Email não encontrado OU password incorrecta.
           A mensagem de erro é intencionalmente vaga para não
           revelar se o email está ou não registado. */
        header("Location: ../pages/login.php?erro=1");
        exit();
    }

} catch (PDOException $e) {

    /* Erro de base de dados — regista no log do servidor
       mas não mostra detalhes ao utilizador (info sensível) */
    error_log("Erro no login [CineRate]: " . $e->getMessage());
    header("Location: ../pages/login.php?erro=geral");
    exit();
}