<?php
/* ============================================================
   CINERATE — register_action.php
   Action de processamento do formulário de registo.

   Recebe os dados via POST de register.php e:
     1. Valida o método HTTP
     2. Sanitiza e valida todos os campos
     3. Verifica se o email já está registado
     4. Faz hash da password com bcrypt
     5. Insere o utilizador na base de dados
     6. Redireciona para o login com mensagem de sucesso

   Segurança:
     - session_start() para acesso à sessão e redirecionamentos seguros
     - filter_var() para validar o formato do email
     - Verificação de email duplicado antes do INSERT
     - password_hash() com PASSWORD_DEFAULT (bcrypt) — NUNCA guardar
       passwords em texto simples
     - try/catch com error_log() — erros de BD não são mostrados ao utilizador
     - htmlspecialchars() nos campos de texto (defesa em profundidade)
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Verificação do método HTTP ---
   Rejeita pedidos que não sejam POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/register.php");
    exit();
}

/* ============================================================
   1. LEITURA E SANITIZAÇÃO DOS CAMPOS
   ============================================================ */
$name             = trim(htmlspecialchars($_POST['name']             ?? ''));
$email            = trim($_POST['email']                             ?? '');
$password         = $_POST['password']                               ?? '';
$confirm_password = $_POST['confirm_password']                       ?? '';

/* ============================================================
   2. VALIDAÇÃO DOS CAMPOS
   ============================================================ */

/* Verifica que nenhum campo obrigatório está vazio */
if ($name === '' || $email === '' || $password === '') {
    header("Location: ../pages/register.php?erro=campos");
    exit();
}

/* Valida o formato do email com o filtro nativo do PHP */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/register.php?erro=email_invalido");
    exit();
}

/* Comprimento mínimo da password */
if (strlen($password) < 6) {
    header("Location: ../pages/register.php?erro=curta");
    exit();
}

/* As duas passwords têm de coincidir */
if ($password !== $confirm_password) {
    header("Location: ../pages/register.php?erro=passwords");
    exit();
}

/* ============================================================
   3. VERIFICAÇÃO DE EMAIL DUPLICADO
   Antes de tentar inserir, verifica se o email já existe —
   devolve um erro específico em vez de deixar a BD falhar
   ============================================================ */
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    /* Email já registado — redireciona com código de erro específico */
    header("Location: ../pages/register.php?erro=email_exists");
    exit();
}

/* ============================================================
   4. HASH DA PASSWORD
   PASSWORD_DEFAULT usa bcrypt — algoritmo seguro e com salt automático.
   O hash resultante tem ~60 caracteres e é diferente a cada chamada,
   mesmo para a mesma password (o salt é gerado aleatoriamente).
   ============================================================ */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* ============================================================
   5. INSERÇÃO NA BASE DE DADOS
   Query parametrizada — previne SQL Injection.
   O papel ('role') fica como 'user' por defeito (definido na BD).
   ============================================================ */
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$name, $email, $hashedPassword]);

    /* Redireciona para o login com mensagem de sucesso */
    header("Location: ../pages/login.php?sucesso=1");
    exit();

} catch (PDOException $e) {

    /* Regista o erro no log do servidor — não mostra ao utilizador
       (os detalhes do erro podem revelar a estrutura da base de dados) */
    error_log("Erro no registo [CineRate]: " . $e->getMessage());

    header("Location: ../pages/register.php?erro=geral");
    exit();
}