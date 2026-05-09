<?php
/* ============================================================
   CINERATE — header.php
   Cabeçalho global incluído em todas as páginas.
   Responsabilidades:
     - Iniciar a sessão PHP
     - Ler o tema preferido do utilizador
     - Gerar o <head> HTML com meta tags, CSS e fonte
     - Renderizar a barra de navegação com links dinâmicos
       consoante o estado de autenticação e o papel do utilizador
   ============================================================ */


/* --- Sessão ---
   Só inicia sessão se ainda não estiver activa,
   para evitar o erro "session already started" */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* --- Tema visual ---
   Lê o tema guardado na sessão do utilizador.
   'dark' é o padrão — corresponde à classe no <body>.
   Se o utilizador escolheu tema claro, a classe 'light' é aplicada
   e o CSS trata do resto. */
$theme = $_SESSION['user_theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <!-- Codificação de caracteres — obrigatório para acentos e caracteres especiais -->
    <meta charset="UTF-8">

    <!-- Viewport — essencial para design responsivo em dispositivos móveis -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Compatibilidade com Internet Explorer (boa prática) -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Meta de descrição — ajuda no SEO e partilha em redes sociais -->
    <meta name="description" content="CineRate — Avalia, comenta e descobre filmes com a comunidade.">

    <!-- Título da página — pode ser sobrescrito em cada página individual -->
    <title>CineRate</title>

    <!-- *** FONTE: DM Sans do Google Fonts ***
         Carregada com display=swap para não bloquear o render da página.
         Pesos incluídos: 400 (normal), 600 (semi-bold), 700 (bold), 800 (extra-bold) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <!-- Folha de estilos principal — o ?v=time() força o browser a não usar cache
         em ambiente de desenvolvimento, garantindo que vê sempre a versão mais recente -->
    <link rel="stylesheet" href="/cinerate/assets/css/style.css?v=<?= time() ?>">
</head>

<!-- A classe do body define o tema activo (dark por defeito, light se o utilizador preferir).
     htmlspecialchars previne injecção de código malicioso via $_SESSION. -->
<body class="<?= htmlspecialchars($theme) ?>">

<!-- ============================================================
     CABEÇALHO / BARRA DE NAVEGAÇÃO
     ============================================================ -->
<header class="site-header">
    <nav class="navbar">

        <!-- Logo — clicável, regressa à página inicial -->
        <a href="/cinerate/index.php" class="logo">CineRate</a>

        <!-- Links de navegação principais -->
        <div class="nav-links">

            <!-- Link sempre visível: lista de filmes -->
            <a href="/cinerate/pages/movies.php">Filmes</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Bloco visível apenas para utilizadores autenticados -->

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <!-- Link exclusivo para administradores: adicionar filmes -->
                    <a href="/cinerate/pages/add_movie.php">Adicionar Filme</a>
                <?php endif; ?>

                <!-- Perfil e logout — disponíveis para qualquer utilizador com sessão -->
                <a href="/cinerate/pages/profile.php">Perfil</a>
                <a href="/cinerate/actions/logout.php">Sair</a>

            <?php else: ?>
                <!-- Bloco visível apenas para visitantes não autenticados -->
                <a href="/cinerate/pages/login.php">Login</a>
                <a href="/cinerate/pages/register.php">Registo</a>
            <?php endif; ?>

            <!-- Botão de alternância de tema claro/escuro.
                 O ícone é actualizado pelo JavaScript (main.js → toggleTheme).
                 aria-label garante acessibilidade para leitores de ecrã. -->
            <button
                    id="theme-toggle"
                    class="btn btn-ghost btn-small"
                    onclick="toggleTheme()"
                    aria-label="Mudar tema"
                    title="Alternar tema claro/escuro">
                <!-- O ícone correcto (🌙 ou ☀️) é definido pelo JavaScript ao carregar -->
                🌙
            </button>

        </div><!-- /.nav-links -->
    </nav>
</header>
<!-- Fim do cabeçalho — o conteúdo específico de cada página começa abaixo -->