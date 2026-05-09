<?php
/* ============================================================
   CINERATE — review_action.php
   Action de processamento do formulário de crítica de filme.

   Recebe os dados via POST de movie.php e:
     1. Verifica autenticação
     2. Valida e sanitiza os campos (rating e texto)
     3. Verifica se o utilizador já avaliou este filme (1 crítica por filme)
     4. Insere a crítica na base de dados
     5. Cria uma notificação para o próprio utilizador (confirmação)
     6. Redireciona para a página do filme

   Segurança:
     - user_id sempre da sessão, nunca de campos POST
     - rating validado como inteiro entre 1 e 10
     - review_text sanitizado com trim()
     - Prevenção de críticas duplicadas (1 por utilizador por filme)
     - Query parametrizada com PDO
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Utilizadores não autenticados são redireccionados para o login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

/* --- Verificação do método HTTP --- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/movies.php");
    exit();
}

/* ============================================================
   1. LEITURA E SANITIZAÇÃO DOS DADOS
   ============================================================ */

/* user_id vem sempre da sessão — nunca de campos POST */
$user_id     = (int) $_SESSION['user_id'];

/* movie_id convertido a inteiro — previne SQL Injection */
$movie_id    = (int) ($_POST['movie_id'] ?? 0);

/* rating convertido a inteiro para validação numérica */
$rating      = (int) ($_POST['rating']   ?? 0);

/* Texto da crítica — trim() remove espaços desnecessários */
$review_text = trim($_POST['review_text'] ?? '');

/* Valida que o movie_id é um ID positivo */
if ($movie_id <= 0) {
    header("Location: ../pages/movies.php");
    exit();
}

/* ============================================================
   2. VALIDAÇÃO DOS CAMPOS
   ============================================================ */

/* A nota tem de ser um inteiro entre 1 e 10 */
if ($rating < 1 || $rating > 10) {
    header("Location: ../pages/movie.php?id=" . $movie_id . "&erro=nota");
    exit();
}

/* O texto da crítica não pode estar vazio */
if ($review_text === '') {
    header("Location: ../pages/movie.php?id=" . $movie_id . "&erro=texto");
    exit();
}

/* ============================================================
   3. VERIFICAÇÃO DE CRÍTICA DUPLICADA
   Cada utilizador só pode escrever uma crítica por filme.
   Verifica antes de inserir para dar um erro mais claro.
   ============================================================ */
$stmtCheck = $pdo->prepare("
    SELECT id FROM reviews
    WHERE user_id = ? AND movie_id = ?
");
$stmtCheck->execute([$user_id, $movie_id]);

if ($stmtCheck->fetch()) {
    /* Utilizador já tem uma crítica para este filme */
    header("Location: ../pages/movie.php?id=" . $movie_id . "&erro=duplicada");
    exit();
}

/* ============================================================
   4. INSERÇÃO DA CRÍTICA NA BASE DE DADOS
   ============================================================ */
$stmt = $pdo->prepare("
    INSERT INTO reviews (user_id, movie_id, rating, review_text)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $movie_id, $rating, $review_text]);

/* Redireciona para a página do filme após publicar a crítica */
header("Location: ../pages/movie.php?id=" . $movie_id);
exit();