<?php
/* ============================================================
   CINERATE — comment_action.php
   Action de processamento de comentários e respostas.

   Recebe os dados via POST de movie.php e:
     1. Verifica autenticação
     2. Valida e sanitiza os campos
     3. Insere o comentário (raiz ou reply) na base de dados
     4. Se for uma resposta (reply), cria uma notificação para
        o autor do comentário original
     5. Redireciona de volta para a página do filme

   Estrutura de comentários:
     - Comentário raiz:  parent_comment_id = NULL
     - Resposta (reply): parent_comment_id = ID do comentário pai

   Segurança:
     - Verificação de sessão activa antes de qualquer processamento
     - Todos os IDs convertidos a inteiro ((int) cast)
     - Texto sanitizado com trim()
     - Não cria notificação quando o utilizador responde a si próprio
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Utilizadores não autenticados não podem comentar */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

/* --- Verificação do método HTTP ---
   Só aceita POST — rejeita pedidos GET ou outros */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/movies.php");
    exit();
}

/* ============================================================
   1. LEITURA E VALIDAÇÃO DOS DADOS DO FORMULÁRIO
   ============================================================ */

/* ID do utilizador vem da sessão — nunca de campos escondidos do form
   (um utilizador malicioso poderia alterar um campo hidden no browser) */
$user_id  = (int) $_SESSION['user_id'];

/* ID do filme — convertido a inteiro para prevenir SQL Injection */
$movie_id = (int) ($_POST['movie_id'] ?? 0);

/* Texto do comentário — trim() remove espaços desnecessários */
$comment_text = trim($_POST['comment_text'] ?? '');

/* ID do comentário pai (apenas em replies) — NULL para comentários raiz */
$parent_comment_id = !empty($_POST['parent_comment_id'])
    ? (int) $_POST['parent_comment_id']
    : null;

/* Valida que temos um filme e um texto */
if ($movie_id === 0 || $comment_text === '') {
    header("Location: ../pages/movie.php?id=" . $movie_id . "&erro=comentario");
    exit();
}

/* ============================================================
   2. INSERÇÃO DO COMENTÁRIO NA BASE DE DADOS
   parent_comment_id é NULL para comentários raiz,
   ou o ID do comentário pai para respostas (replies)
   ============================================================ */
$stmt = $pdo->prepare("
    INSERT INTO comments (user_id, movie_id, parent_comment_id, comment_text)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $movie_id, $parent_comment_id, $comment_text]);

/* ============================================================
   3. NOTIFICAÇÃO AO AUTOR DO COMENTÁRIO ORIGINAL (só em replies)
   ============================================================ */
if ($parent_comment_id !== null) {

    /* Vai buscar o ID do autor do comentário original */
    $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$parent_comment_id]);
    $originalComment = $stmt->fetch(PDO::FETCH_ASSOC);

    /* Só cria notificação se:
         a) o comentário original existir
         b) o autor não for o próprio utilizador (evita auto-notificação) */
    if ($originalComment && (int) $originalComment['user_id'] !== $user_id) {

        /* Mensagem da notificação com o nome de quem respondeu */
        $message = htmlspecialchars($_SESSION['user_name']) . " respondeu ao teu comentário.";

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message)
            VALUES (?, ?)
        ");
        $stmt->execute([$originalComment['user_id'], $message]);
    }
}

/* Redireciona de volta para a página do filme após o comentário */
header("Location: ../pages/movie.php?id=" . $movie_id);
exit();