<?php
/* ============================================================
   CINERATE — notifications.php
   Endpoint AJAX — devolve as notificações não lidas do utilizador.

   Chamado por main.js (função checkNotifications) a cada 5 segundos.
   Após devolver as notificações, marca-as todas como lidas numa
   única query UPDATE para não as repetir na próxima chamada.

   Resposta: array JSON com as notificações não lidas.
   Array vazio [] se o utilizador não estiver autenticado ou
   não houver notificações pendentes.

   Segurança:
     - Devolve [] silenciosamente se não autenticado —
       não revela informação sobre outros utilizadores
     - user_id vem sempre da sessão, nunca de parâmetros externos
     - (int) cast no user_id previne qualquer injecção
     - SELECT só devolve notificações do utilizador da sessão activa
     - JSON_UNESCAPED_UNICODE para acentos correctos
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* Define o Content-Type como JSON antes de qualquer output */
header('Content-Type: application/json; charset=utf-8');

/* Só responde a pedidos GET */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([]);
    exit();
}

/* Utilizador não autenticado — devolve array vazio sem erro.
   O JavaScript trata [] como "sem notificações" e não mostra nada. */
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

/* Cast para inteiro — garante que é um ID válido */
$user_id = (int) $_SESSION['user_id'];

/* ============================================================
   QUERY 1 — Busca notificações não lidas (is_read = 0)
   Ordenadas da mais recente para a mais antiga
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT id, message, created_at
    FROM notifications
    WHERE user_id = ?
      AND is_read = 0
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   QUERY 2 — Marca todas como lidas (só se houver notificações)
   Feito APÓS o SELECT para garantir que a resposta JSON
   já contém as notificações antes de as marcar como lidas.
   UPDATE único em vez de um UPDATE por notificação — mais eficiente.
   ============================================================ */
if (count($notifications) > 0) {
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
          AND is_read = 0
    ");
    $stmt->execute([$user_id]);
}

/* Serializa o array para JSON e envia como resposta */
echo json_encode($notifications, JSON_UNESCAPED_UNICODE);