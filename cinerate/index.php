<?php
/* ============================================================
   CINERATE — index.php
   Página inicial da aplicação.

   Conteúdo apresentado:
     - Hero com título, subtítulo e botões de acção
       (adapta-se consoante o estado de autenticação)
     - Feed dinâmico de comentários recentes (carregado via AJAX
       pela função loadLatestComments() em main.js)

   Nota: session_start() é chamado aqui para verificar se o
   utilizador está autenticado e personalizar o hero.
   O header.php também chama session_start() mas só se a sessão
   ainda não estiver activa (session_status() === PHP_SESSION_NONE),
   pelo que não há conflito.
   ============================================================ */

session_start();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">

        <!-- ============================================================
             SECÇÃO HERO — Apresentação principal da plataforma
             O conteúdo adapta-se consoante o utilizador está autenticado
             ============================================================ -->
        <section class="hero">

            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- Utilizador autenticado — saudação personalizada com o nome -->
                <h1>
                    Olá, <?= htmlspecialchars($_SESSION['user_name']) ?>.<br>
                    <span class="highlight">O que vês hoje?</span>
                </h1>
                <p>Explora o catálogo, escreve críticas e descobre o que a comunidade está a ver.</p>

                <div class="hero-actions">
                    <a href="pages/movies.php" class="btn">Explorar Filmes &rarr;</a>
                    <a href="pages/profile.php" class="btn btn-secondary">O Meu Perfil</a>
                </div>

            <?php else: ?>

                <!-- Visitante não autenticado — apresentação da plataforma -->
                <h1>
                    Avalia.<br>
                    Comenta.<br>
                    <span class="highlight">Descobre.</span>
                </h1>
                <p>Partilha as tuas opiniões sobre filmes e vê o que a comunidade pensa.</p>

                <div class="hero-actions">
                    <a href="pages/movies.php"  class="btn">Explorar Filmes &rarr;</a>
                    <a href="pages/register.php" class="btn btn-secondary">Criar Conta</a>
                </div>

            <?php endif; ?>

        </section>


        <!-- ============================================================
             SECÇÃO FEED — Comentários mais recentes (dinâmico via AJAX)

             O elemento #latest-comments é o alvo da função
             loadLatestComments() definida em main.js.
             Essa função é chamada ao carregar a página e repetida
             a cada 7 segundos com setInterval(), actualizando o feed
             sem recarregar a página (requisito de conteúdo dinâmico).
             ============================================================ -->
        <section>

            <div class="section-header">
                <h2>Comentários recentes</h2>
                <!-- Indicador visual de actualização automática -->
                <span class="text-muted" style="font-size:12px;">
                &#8635; actualiza automaticamente
            </span>
            </div>

            <!-- Contentor do feed — preenchido e actualizado pelo JavaScript -->
            <div id="latest-comments" class="latest-feed">
                <!-- Mensagem de loading mostrada antes do primeiro pedido AJAX -->
                <p class="text-muted text-center" style="padding: 20px 0;">
                    A carregar comentários...
                </p>
            </div>

        </section>

    </main>

<?php include 'includes/footer.php'; ?>