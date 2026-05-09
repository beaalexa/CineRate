<?php
/* ============================================================
   CINERATE — add_movie.php
   Página para adicionar um novo filme à base de dados.

   Acesso restrito: apenas administradores autenticados.

   Segurança:
     - Verificação dupla: sessão activa + papel 'admin'
     - O processamento real é feito em add_movie_action.php
     - enctype="multipart/form-data" é obrigatório para upload de imagens
   ============================================================ */

session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Verifica se o utilizador está autenticado e tem papel de administrador.
   Qualquer outra situação resulta em acesso negado imediato. */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    /* Em produção seria melhor redirecionar para uma página de erro 403 */
    http_response_code(403);
    die("Acesso negado. Apenas administradores podem adicionar filmes.");
}
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <!-- Card centrado com o formulário de adição -->
        <section class="auth-card">

            <h2>Adicionar Filme</h2>
            <p class="subtitle">Preenche os dados para adicionar um novo filme ao catálogo</p>

            <!-- Formulário de adição de filme
                 method="POST"                     — dados enviados no corpo do pedido (não na URL)
                 enctype="multipart/form-data"     — obrigatório para enviar ficheiros (imagem do poster)
                 action: processa, valida e guarda na base de dados -->
            <form
                    action="../actions/add_movie_action.php"
                    method="POST"
                    class="form"
                    enctype="multipart/form-data"
                    novalidate>

                <!-- Título do filme — campo obrigatório -->
                <input
                        type="text"
                        name="title"
                        placeholder="Título do filme"
                        required>

                <!-- Ano de lançamento — número de 4 dígitos -->
                <input
                        type="number"
                        name="year"
                        placeholder="Ano (ex: 2024)"
                        min="1888"
                        max="2099">

                <!-- Género — texto livre (ex: "Ficção Científica, Aventura") -->
                <input
                        type="text"
                        name="genre"
                        placeholder="Género (ex: Drama, Acção)">

                <!-- Descrição / sinopse do filme -->
                <textarea
                        name="description"
                        placeholder="Sinopse / descrição do filme..."></textarea>

                <!-- Upload do poster
                     accept="image/*" filtra para apenas mostrar imagens no seletor de ficheiros
                     O servidor deve validar o tipo e tamanho do ficheiro em add_movie_action.php -->
                <label for="image" style="text-align:left; color:var(--muted); font-size:13px;">
                    Poster do filme (opcional)
                </label>
                <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/webp">

                <button type="submit" class="btn">
                    &#43; Adicionar Filme
                </button>

            </form>

            <!-- Link para voltar à lista de filmes sem adicionar -->
            <p class="mt-16">
                <a href="movies.php" class="btn btn-ghost btn-small">&#8592; Voltar aos filmes</a>
            </p>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>