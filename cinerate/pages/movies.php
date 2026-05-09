<?php
/* ============================================================
   CINERATE — movies.php
   Página de listagem de todos os filmes com pesquisa.

   Funcionalidades:
     - Lista todos os filmes por ordem de mais recente
     - Filtragem por título ou género via barra de pesquisa
     - Administradores vêem botão de edição em cada card
     - Cards com poster, título, ano, género e descrição

   Segurança:
     - Pesquisa feita com query parametrizada (PDO) — previne SQL Injection
     - LIKE com parâmetro separado, não concatenado na string SQL
     - htmlspecialchars() em todos os dados de saída
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* Lê o termo de pesquisa da URL — trim() remove espaços acidentais */
$search = trim($_GET['search'] ?? '');

/* ============================================================
   QUERY — Lista de filmes
   Se houver pesquisa: filtra por título OU género (LIKE)
   Se não houver: devolve todos os filmes por data de criação
   ============================================================ */
if ($search !== '') {
    /* O % em volta do termo permite encontrar o texto em qualquer posição
       Ex: pesquisar "sci" encontra "Ficção Científica" */
    $searchTerm = '%' . $search . '%';

    $stmt = $pdo->prepare("
        SELECT * FROM movies
        WHERE title LIKE ? OR genre LIKE ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$searchTerm, $searchTerm]);

} else {
    /* Sem pesquisa — todos os filmes, mais recentes primeiro */
    $stmt = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC");
}

$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <!-- Título e contagem de resultados -->
        <div class="section-header">
            <h2>Filmes</h2>
            <span class="text-muted">
            <?= count($movies) ?> <?= count($movies) === 1 ? 'filme' : 'filmes' ?>
            <?= $search !== '' ? 'encontrados' : 'no catálogo' ?>
        </span>
        </div>

        <!-- ============================================================
             BARRA DE PESQUISA
             method GET para a pesquisa ficar na URL (partilhável e com back button)
             ============================================================ -->
        <form method="GET" action="movies.php" class="search-form" role="search">

            <input
                    type="text"
                    name="search"
                    placeholder="Pesquisar por título ou género..."
                    value="<?= htmlspecialchars($search) ?>"
                    aria-label="Pesquisar filmes">

            <button type="submit" class="btn">Pesquisar</button>

            <!-- Botão para limpar pesquisa — só aparece quando há um termo activo -->
            <?php if ($search !== ''): ?>
                <a href="movies.php" class="btn btn-secondary">Limpar</a>
            <?php endif; ?>

        </form>

        <!-- Legenda do resultado de pesquisa -->
        <?php if ($search !== ''): ?>
            <p class="search-result">
                Resultados para: <strong>"<?= htmlspecialchars($search) ?>"</strong>
            </p>
        <?php endif; ?>


        <!-- ============================================================
             GRELHA DE FILMES
             ============================================================ -->
        <section class="movie-grid">

            <?php if (count($movies) === 0): ?>
                <!-- Mensagem quando não há filmes ou a pesquisa não tem resultados -->
                <p class="text-muted" style="grid-column: 1 / -1; text-align:center; padding:40px 0;">
                    <?= $search !== ''
                            ? 'Nenhum filme encontrado para "' . htmlspecialchars($search) . '".'
                            : 'Ainda não existem filmes no catálogo.' ?>
                </p>
            <?php endif; ?>

            <?php foreach ($movies as $movie): ?>
                <article class="movie-card">

                    <!-- Poster do filme
                         Classes 'has-image' e 'no-image' permitem CSS condicional -->
                    <div class="movie-poster <?= !empty($movie['image']) ? 'has-image' : 'no-image' ?>">

                        <?php if (!empty($movie['image'])): ?>
                            <!-- Imagem real do poster -->
                            <img
                                    src="/cinerate/assets/uploads/<?= htmlspecialchars($movie['image']) ?>"
                                    alt="Poster de <?= htmlspecialchars($movie['title']) ?>"
                                    loading="lazy">
                        <?php else: ?>
                            <!-- Fallback: inicial do título quando não há poster -->
                            <?= strtoupper(substr(htmlspecialchars($movie['title']), 0, 1)) ?>
                        <?php endif; ?>

                    </div>

                    <!-- Informações e acções do card -->
                    <div class="movie-info">

                        <!-- Título do filme -->
                        <h3><?= htmlspecialchars($movie['title']) ?></h3>

                        <!-- Ano e género separados por bullet -->
                        <p>
                            <?= htmlspecialchars($movie['year']) ?>
                            &bull;
                            <?= htmlspecialchars($movie['genre']) ?>
                        </p>

                        <!-- Descrição truncada por CSS (-webkit-line-clamp) -->
                        <p><?= htmlspecialchars($movie['description']) ?></p>

                        <!-- Botão principal — ver página de detalhe -->
                        <a href="movie.php?id=<?= (int) $movie['id'] ?>" class="btn btn-small">
                            Ver detalhes
                        </a>

                        <!-- Botão de edição — visível apenas para administradores -->
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="edit_movie.php?id=<?= (int) $movie['id'] ?>" class="btn btn-secondary btn-small">
                                ✏️ Editar
                            </a>
                        <?php endif; ?>

                    </div>

                </article>
            <?php endforeach; ?>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>