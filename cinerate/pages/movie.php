<?php
/* ============================================================
   CINERATE — movie.php
   Página de detalhe de um filme.

   Conteúdo apresentado:
     - Poster, título, ano, género, descrição
     - Média de rating e total de críticas
     - Formulário para adicionar crítica (só para utilizadores autenticados)
     - Lista de críticas da comunidade
     - Formulário para adicionar comentário (só para utilizadores autenticados)
     - Lista de comentários com respostas aninhadas (replies)

   Segurança:
     - ID do filme validado como inteiro
     - Todos os dados de saída escapados com htmlspecialchars()
     - Queries parametrizadas com PDO para prevenir SQL Injection
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Validação do parâmetro de URL ---
   Se não houver ?id= na URL, redireciona para a lista de filmes */
if (!isset($_GET['id'])) {
    header("Location: movies.php");
    exit();
}

/* Converte para inteiro para garantir que é um ID válido (previne SQL Injection) */
$movie_id = (int) $_GET['id'];

/* ============================================================
   QUERY 1 — Dados do filme
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

/* Se o filme não existir na base de dados, termina com mensagem de erro */
if (!$movie) {
    /* Em produção seria melhor redirecionar para uma página 404 personalizada */
    die("Filme não encontrado.");
}

/* ============================================================
   QUERY 2 — Críticas do filme (com nome do utilizador via JOIN)
   Ordenadas da mais recente para a mais antiga
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT reviews.*, users.id AS user_id, users.name
    FROM reviews
    JOIN users ON reviews.user_id = users.id
    WHERE reviews.movie_id = ?
    ORDER BY reviews.created_at DESC
");
$stmt->execute([$movie_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   QUERY 3 — Média de rating e total de críticas
   AVG() calcula a média, COUNT() conta o total
   ============================================================ */
$stmtAvg = $pdo->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
    FROM reviews
    WHERE movie_id = ?
");
$stmtAvg->execute([$movie_id]);
$ratingData = $stmtAvg->fetch(PDO::FETCH_ASSOC);

/* ============================================================
   QUERY 4 — Comentários de nível raiz (sem parent_comment_id)
   Os replies são buscados em separado dentro do loop HTML
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT comments.*, users.id AS user_id, users.name
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.movie_id = ?
      AND comments.parent_comment_id IS NULL
    ORDER BY comments.created_at DESC
");
$stmt->execute([$movie_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <!-- ============================================================
             SECÇÃO 1 — Detalhe do filme (poster + informações)
             ============================================================ -->
        <section class="movie-detail">

            <!-- Poster do filme -->
            <div class="detail-poster">
                <?php if (!empty($movie['image'])): ?>
                    <!-- Imagem real do filme — htmlspecialchars previne XSS nos atributos -->
                    <img
                            src="/cinerate/assets/uploads/<?= htmlspecialchars($movie['image']) ?>"
                            alt="Poster de <?= htmlspecialchars($movie['title']) ?>">
                <?php else: ?>
                    <!-- Fallback: primeira letra do título quando não há poster -->
                    <div class="movie-poster" style="height:100%; font-size:72px; display:grid; place-items:center;">
                        <?= strtoupper(substr(htmlspecialchars($movie['title']), 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Informações do filme -->
            <div class="movie-detail-info">

                <h1><?= htmlspecialchars($movie['title']) ?></h1>

                <!-- Ano e género separados por bullet -->
                <p class="text-muted">
                    <?= htmlspecialchars($movie['year']) ?>
                    &bull;
                    <?= htmlspecialchars($movie['genre']) ?>
                </p>

                <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>

                <!-- Linha de metadados: rating médio e total de críticas -->
                <div class="detail-meta" style="margin-top:14px;">

                <span>
                    <!-- Mostra a média arredondada a 1 casa decimal, ou mensagem vazia -->
                    <strong style="color:var(--gold); font-size:22px;">
                        <?= $ratingData['total_reviews'] > 0
                                ? round($ratingData['avg_rating'], 1) . '/10'
                                : '—' ?>
                    </strong>
                    &nbsp;média
                </span>

                    <span>
                    <!-- Total de críticas submetidas -->
                    <strong><?= (int) $ratingData['total_reviews'] ?></strong>
                    <?= $ratingData['total_reviews'] === 1 ? 'crítica' : 'críticas' ?>
                </span>

                </div>

                <!-- Botões de acção (admin pode editar) -->
                <div class="hero-actions" style="margin-top:20px;">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="edit_movie.php?id=<?= $movie_id ?>" class="btn btn-secondary btn-small">
                            ✏️ Editar Filme
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </section>


        <!-- ============================================================
             SECÇÃO 2 — Formulário para adicionar crítica
             Visível apenas para utilizadores com sessão activa
             ============================================================ -->
        <?php if (isset($_SESSION['user_id'])): ?>

            <section class="auth-card" style="margin-bottom:30px;">
                <h2>Escrever Crítica</h2>

                <!-- action: processa e guarda a crítica na base de dados -->
                <form action="../actions/review_action.php" method="POST" class="form" novalidate>

                    <!-- ID do filme passado como campo oculto -->
                    <input type="hidden" name="movie_id" value="<?= $movie_id ?>">

                    <!-- Nota numérica: min=1, max=10, validado também no main.js -->
                    <input
                            type="number"
                            name="rating"
                            min="1"
                            max="10"
                            placeholder="Nota de 1 a 10"
                            required>

                    <!-- Texto da crítica -->
                    <textarea
                            name="review_text"
                            placeholder="Partilha a tua opinião sobre o filme..."
                            required></textarea>

                    <button type="submit" class="btn">Publicar Crítica</button>

                </form>
            </section>

        <?php else: ?>
            <!-- Convida visitantes a fazer login para escrever críticas -->
            <p class="text-muted mt-16" style="margin-bottom:24px;">
                Faz <a href="login.php">login</a> para escrever uma crítica.
            </p>
        <?php endif; ?>


        <!-- ============================================================
             SECÇÃO 3 — Lista de críticas da comunidade
             ============================================================ -->
        <section class="reviews">

            <div class="section-header">
                <h2>Críticas da comunidade</h2>
                <!-- Contador de críticas -->
                <span class="text-muted"><?= count($reviews) ?> <?= count($reviews) === 1 ? 'crítica' : 'críticas' ?></span>
            </div>

            <?php if (count($reviews) === 0): ?>
                <p class="text-muted">Ainda não existem críticas para este filme. Sê o primeiro!</p>
            <?php endif; ?>

            <?php foreach ($reviews as $review): ?>
                <article class="review-card">

                    <!-- Cabeçalho da crítica: avatar + nome + nota -->
                    <div class="review-header">
                        <div class="review-user-info">
                            <!-- Avatar com inicial do nome -->
                            <div class="avatar">
                                <?= strtoupper(substr(htmlspecialchars($review['name']), 0, 1)) ?>
                            </div>
                            <!-- Nome clicável que leva ao perfil público do utilizador -->
                            <a href="user_profile.php?id=<?= (int) $review['user_id'] ?>" class="review-user">
                                <?= htmlspecialchars($review['name']) ?>
                            </a>
                        </div>
                        <!-- Nota da crítica -->
                        <span class="review-score"><?= (int) $review['rating'] ?>/10</span>
                    </div>

                    <!-- Texto da crítica — nl2br preserva as quebras de linha do utilizador -->
                    <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>

                    <!-- Rodapé com data de publicação -->
                    <div class="review-footer">
                        <small><?= htmlspecialchars($review['created_at']) ?></small>
                    </div>

                </article>
            <?php endforeach; ?>

        </section>


        <!-- ============================================================
             SECÇÃO 4 — Formulário para adicionar comentário
             Visível apenas para utilizadores com sessão activa
             ============================================================ -->
        <?php if (isset($_SESSION['user_id'])): ?>

            <section class="auth-card" style="margin-top:30px; margin-bottom:20px;">
                <h2>Deixar Comentário</h2>

                <form action="../actions/comment_action.php" method="POST" class="form" novalidate>

                    <!-- ID do filme passado como campo oculto -->
                    <input type="hidden" name="movie_id" value="<?= $movie_id ?>">

                    <textarea
                            name="comment_text"
                            placeholder="Partilha um comentário sobre o filme..."
                            required></textarea>

                    <button type="submit" class="btn">Comentar</button>

                </form>
            </section>

        <?php endif; ?>


        <!-- ============================================================
             SECÇÃO 5 — Lista de comentários com respostas (nested)
             ============================================================ -->
        <section class="comments-section">

            <div class="section-header">
                <h2>Comentários</h2>
                <span class="text-muted"><?= count($comments) ?> <?= count($comments) === 1 ? 'comentário' : 'comentários' ?></span>
            </div>

            <?php if (count($comments) === 0): ?>
                <p class="text-muted">Ainda não existem comentários. Começa a conversa!</p>
            <?php endif; ?>

            <?php foreach ($comments as $comment): ?>
                <article class="comment-card">

                    <!-- Cabeçalho do comentário: avatar + nome + data -->
                    <div class="review-header">
                        <div class="review-user-info">
                            <div class="avatar">
                                <?= strtoupper(substr(htmlspecialchars($comment['name']), 0, 1)) ?>
                            </div>
                            <a href="user_profile.php?id=<?= (int) $comment['user_id'] ?>">
                                <strong><?= htmlspecialchars($comment['name']) ?></strong>
                            </a>
                        </div>
                        <small><?= htmlspecialchars($comment['created_at']) ?></small>
                    </div>

                    <!-- Texto do comentário -->
                    <p><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>

                    <!-- Formulário de resposta — só para utilizadores autenticados -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="../actions/comment_action.php" method="POST" class="reply-form" novalidate>

                            <!-- ID do filme e ID do comentário pai (para aninhamento) -->
                            <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                            <input type="hidden" name="parent_comment_id" value="<?= (int) $comment['id'] ?>">

                            <textarea name="comment_text" placeholder="Responder a <?= htmlspecialchars($comment['name']) ?>..." required></textarea>

                            <button type="submit" class="btn btn-secondary btn-small" style="margin-top:8px;">
                                Responder
                            </button>

                        </form>
                    <?php endif; ?>

                    <?php
                    /* ============================================================
                       QUERY 5 — Replies do comentário actual
                       Buscadas dentro do loop para manter a estrutura aninhada.
                       Ordenadas do mais antigo para o mais recente (ordem cronológica)
                       ============================================================ */
                    $stmtReplies = $pdo->prepare("
                    SELECT comments.*, users.id AS user_id, users.name
                    FROM comments
                    JOIN users ON comments.user_id = users.id
                    WHERE comments.parent_comment_id = ?
                    ORDER BY comments.created_at ASC
                ");
                    $stmtReplies->execute([(int) $comment['id']]);
                    $replies = $stmtReplies->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <!-- Respostas ao comentário — recuadas visualmente pelo CSS (.reply-card) -->
                    <?php foreach ($replies as $reply): ?>
                        <div class="reply-card">

                            <div class="review-user-info" style="margin-bottom:6px;">
                                <div class="avatar" style="width:28px; height:28px; font-size:12px;">
                                    <?= strtoupper(substr(htmlspecialchars($reply['name']), 0, 1)) ?>
                                </div>
                                <a href="user_profile.php?id=<?= (int) $reply['user_id'] ?>">
                                    <strong><?= htmlspecialchars($reply['name']) ?></strong>
                                </a>
                            </div>

                            <p><?= nl2br(htmlspecialchars($reply['comment_text'])) ?></p>
                            <small><?= htmlspecialchars($reply['created_at']) ?></small>

                        </div>
                    <?php endforeach; ?>

                </article>
            <?php endforeach; ?>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>