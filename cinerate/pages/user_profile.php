<?php
/* ============================================================
   CINERATE — user_profile.php
   Perfil público de um utilizador — visível por qualquer visitante.

   Conteúdo apresentado:
     - Foto de perfil (ou placeholder com inicial)
     - Nome, biografia e data de registo
     - Lista de todas as críticas escritas pelo utilizador,
       com link para o filme correspondente

   Segurança:
     - ID do utilizador validado como inteiro
     - Todos os dados de saída escapados com htmlspecialchars()
     - A query não devolve a password nem o token de reset
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Validação do ID na URL ---
   Sem ?id= não há perfil para mostrar */
if (!isset($_GET['id'])) {
    header("Location: movies.php");
    exit();
}

/* Converte para inteiro para prevenir SQL Injection */
$user_id = (int) $_GET['id'];

/* ============================================================
   QUERY 1 — Dados públicos do utilizador
   A password, reset_token e reset_expires são intencionalmente
   excluídos do SELECT por segurança
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT id, name, bio, photo, created_at
    FROM users
    WHERE id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* Utilizador não encontrado */
if (!$user) {
    die("Utilizador não encontrado.");
}

/* ============================================================
   QUERY 2 — Críticas escritas pelo utilizador
   JOIN com movies para obter o título do filme
   Ordenadas da mais recente para a mais antiga
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT reviews.rating, reviews.review_text, reviews.created_at,
           movies.id AS movie_id, movies.title
    FROM reviews
    JOIN movies ON reviews.movie_id = movies.id
    WHERE reviews.user_id = ?
    ORDER BY reviews.created_at DESC
");
$stmt->execute([$user_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <!-- ============================================================
             SECÇÃO 1 — Informações do perfil público
             ============================================================ -->
        <section class="auth-card">

            <h2>Perfil de <?= htmlspecialchars($user['name']) ?></h2>

            <!-- Foto de perfil ou placeholder com inicial do nome -->
            <?php if (!empty($user['photo'])): ?>
                <img
                        class="profile-photo"
                        src="/cinerate/assets/uploads/profiles/<?= htmlspecialchars($user['photo']) ?>"
                        alt="Foto de perfil de <?= htmlspecialchars($user['name']) ?>">
            <?php else: ?>
                <!-- Placeholder: círculo com gradiente roxo e inicial do nome -->
                <div class="profile-placeholder">
                    <?= strtoupper(substr(htmlspecialchars($user['name']), 0, 1)) ?>
                </div>
            <?php endif; ?>

            <!-- Biografia — mostra texto padrão se não houver bio definida -->
            <p style="margin-bottom:8px;">
                <?= nl2br(htmlspecialchars($user['bio'] ?? 'Este utilizador ainda não escreveu uma biografia.')) ?>
            </p>

            <!-- Data de registo na plataforma -->
            <p class="text-muted" style="font-size:13px;">
                Membro desde <?= htmlspecialchars(date('F Y', strtotime($user['created_at']))) ?>
            </p>

            <!-- Estatísticas rápidas -->
            <p class="text-muted" style="font-size:13px; margin-top:6px;">
                <?= count($reviews) ?> <?= count($reviews) === 1 ? 'crítica escrita' : 'críticas escritas' ?>
            </p>

            <!-- Se o utilizador autenticado está a ver o seu próprio perfil,
                 mostra link para editar -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $user['id']): ?>
                <a href="profile.php" class="btn btn-secondary btn-small" style="margin-top:14px;">
                    ✏️ Editar o meu perfil
                </a>
            <?php endif; ?>

        </section>


        <!-- ============================================================
             SECÇÃO 2 — Críticas escritas pelo utilizador
             ============================================================ -->
        <section class="reviews">

            <div class="section-header">
                <h2>Críticas de <?= htmlspecialchars($user['name']) ?></h2>
                <span class="text-muted">
                <?= count($reviews) ?> <?= count($reviews) === 1 ? 'crítica' : 'críticas' ?>
            </span>
            </div>

            <?php if (count($reviews) === 0): ?>
                <p class="text-muted">Este utilizador ainda não escreveu nenhuma crítica.</p>
            <?php endif; ?>

            <?php foreach ($reviews as $review): ?>
                <article class="review-card">

                    <!-- Cabeçalho: título do filme (link) + nota -->
                    <div class="review-header">
                        <div class="review-user-info">
                            <!-- Ícone de filme -->
                            <div class="avatar" style="background: linear-gradient(135deg,#1e40af,#3b82f6);">
                                🎬
                            </div>
                            <!-- Título do filme clicável -->
                            <a href="movie.php?id=<?= (int) $review['movie_id'] ?>">
                                <strong><?= htmlspecialchars($review['title']) ?></strong>
                            </a>
                        </div>
                        <!-- Nota da crítica -->
                        <span class="review-score"><?= (int) $review['rating'] ?>/10</span>
                    </div>

                    <!-- Texto da crítica -->
                    <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>

                    <!-- Data de publicação -->
                    <div class="review-footer">
                        <small><?= htmlspecialchars($review['created_at']) ?></small>
                    </div>

                </article>
            <?php endforeach; ?>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>