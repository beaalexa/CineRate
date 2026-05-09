<?php
/* ============================================================
   CINERATE — latest_comments.php
   Endpoint AJAX — devolve os comentários mais recentes em JSON.

   Chamado por main.js (função loadLatestComments) a cada 7 segundos
   para actualizar o feed dinâmico na página inicial sem recarregar.

   Resposta: array JSON com os 5 comentários mais recentes.
   Cada objecto contém: name, title, movie_id, comment_text, created_at

   Segurança:
     - Só responde a pedidos GET (leitura, sem modificações)
     - header() define Content-Type correcto para JSON
     - Nenhum dado sensível é exposto (sem email, password, etc.)
     - LIMIT 5 garante que a resposta é sempre pequena e rápida
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* Define o Content-Type como JSON antes de qualquer output —
   o browser e o fetch() do JavaScript precisam disto para
   interpretar correctamente a resposta */
header('Content-Type: application/json; charset=utf-8');

/* Só responde a pedidos GET — qualquer outro método devolve erro */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit();
}

/* ============================================================
   QUERY — 5 comentários mais recentes
   JOIN com users para obter o nome do autor
   JOIN com movies para obter o título do filme e o ID (para o link)
   Apenas comentários raiz (parent_comment_id IS NULL) —
   as respostas não aparecem no feed global
   ============================================================ */
$stmt = $pdo->query("
    SELECT
        users.name,
        movies.title,
        movies.id       AS movie_id,
        comments.comment_text,
        comments.created_at
    FROM comments
    JOIN users  ON comments.user_id  = users.id
    JOIN movies ON comments.movie_id = movies.id
    WHERE comments.parent_comment_id IS NULL
    ORDER BY comments.created_at DESC
    LIMIT 5
");

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* json_encode() serializa o array PHP para JSON.
   JSON_UNESCAPED_UNICODE garante que acentos e caracteres
   especiais não são convertidos para \uXXXX */
echo json_encode($comments, JSON_UNESCAPED_UNICODE);