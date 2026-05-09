<?php
/* ============================================================
   CINERATE — edit_movie.php
   Página para editar os dados de um filme existente.

   Acesso restrito: apenas administradores autenticados.

   Fluxo:
     1. Verifica autenticação e papel de admin
     2. Lê o ID do filme da URL (?id=X)
     3. Vai buscar os dados actuais do filme à base de dados
     4. Pré-preenche o formulário com os dados existentes
     5. O formulário envia para update_movie_action.php via POST

   Segurança:
     - Controlo de acesso antes de qualquer output
     - ID do filme validado como inteiro
     - Todos os valores de saída escapados com htmlspecialchars()
     - Queries parametrizadas com PDO
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Bloqueia imediatamente se não for admin autenticado */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("Acesso negado. Apenas administradores podem editar filmes.");
}

/* --- Validação do ID do filme ---
   Sem ID na URL não há filme para editar — redireciona */
if (!isset($_GET['id'])) {
    header("Location: movies.php");
    exit();
}

/* Converte para inteiro para prevenir SQL Injection */
$movie_id = (int) $_GET['id'];

/* ============================================================
   QUERY — Dados actuais do filme
   Usados para pré-preencher o formulário
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

/* Se o ID não corresponder a nenhum filme, termina com erro */
if (!$movie) {
    die("Filme não encontrado.");
}
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <section class="auth-card">

            <h2>Editar Filme</h2>
            <p class="subtitle">Altera os dados do filme e guarda as alterações</p>

            <!-- Formulário de edição
                 Pré-preenchido com os dados actuais do filme vindos da base de dados
                 enctype="multipart/form-data" — necessário para permitir substituir a imagem -->
            <form
                    action="../actions/update_movie_action.php"
                    method="POST"
                    enctype="multipart/form-data"
                    class="form"
                    novalidate>

                <!-- ID do filme passado como campo oculto — necessário para a query UPDATE -->
                <input type="hidden" name="movie_id" value="<?= (int) $movie['id'] ?>">

                <!-- Título — pré-preenchido com o valor actual -->
                <input
                        type="text"
                        name="title"
                        value="<?= htmlspecialchars($movie['title']) ?>"
                        placeholder="Título do filme"
                        required>

                <!-- Ano — pré-preenchido com o valor actual -->
                <input
                        type="number"
                        name="year"
                        value="<?= htmlspecialchars($movie['year']) ?>"
                        placeholder="Ano"
                        min="1888"
                        max="2099">

                <!-- Género — pré-preenchido com o valor actual -->
                <input
                        type="text"
                        name="genre"
                        value="<?= htmlspecialchars($movie['genre']) ?>"
                        placeholder="Género">

                <!-- Descrição — pré-preenchida com o valor actual
                     Nota: o valor da textarea vai entre as tags, não no atributo value -->
                <textarea
                        name="description"
                        placeholder="Sinopse / descrição..."><?= htmlspecialchars($movie['description']) ?></textarea>

                <!-- Poster actual — mostra a imagem existente para referência -->
                <?php if (!empty($movie['image'])): ?>
                    <div style="text-align:left;">
                        <p class="text-muted" style="font-size:13px; margin-bottom:8px;">Poster actual:</p>
                        <img
                                src="/cinerate/assets/uploads/<?= htmlspecialchars($movie['image']) ?>"
                                alt="Poster actual de <?= htmlspecialchars($movie['title']) ?>"
                                style="width:90px; border-radius:8px; display:block; margin-bottom:10px;">
                    </div>
                <?php endif; ?>

                <!-- Upload de nova imagem — opcional, substitui a actual se fornecida
                     Se nenhum ficheiro for enviado, update_movie_action.php mantém a imagem existente -->
                <label style="text-align:left; color:var(--muted); font-size:13px;">
                    Substituir poster (opcional)
                </label>
                <input
                        type="file"
                        name="image"
                        accept="image/jpeg,image/png,image/webp">

                <button type="submit" class="btn">
                    &#10003; Guardar Alterações
                </button>

            </form>

            <!-- Links de navegação secundária -->
            <div class="hero-actions" style="justify-content:center; margin-top:16px;">
                <a href="movie.php?id=<?= $movie_id ?>" class="btn btn-ghost btn-small">
                    &#8592; Ver filme
                </a>
                <a href="movies.php" class="btn btn-ghost btn-small">
                    Lista de filmes
                </a>
            </div>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>