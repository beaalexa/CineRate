<?php
/* ============================================================
   CINERATE — logout.php
   Termina a sessão do utilizador de forma segura e completa.

   Passos de destruição da sessão (ordem importante):
     1. Iniciar a sessão (necessário para a poder destruir)
     2. Limpar todas as variáveis da sessão (session_unset)
     3. Apagar o cookie de sessão do browser do utilizador
     4. Destruir os dados da sessão no servidor (session_destroy)
     5. Redirecionar para a página inicial

   Segurança:
     - Apaga o cookie de sessão explicitamente — sem isto, o cookie
       fica no browser mesmo após session_destroy(), e pode ser
       reutilizado se alguém tiver acesso ao browser
     - session_unset() + session_destroy() em conjunto garantem
       limpeza completa tanto no cliente como no servidor
     - Só aceita pedidos GET (logout via link — não precisa de POST)
   ============================================================ */

session_start();

/* ============================================================
   1. LIMPAR AS VARIÁVEIS DA SESSÃO
   session_unset() remove todas as variáveis guardadas em $_SESSION
   (user_id, user_name, user_theme, user_role, etc.)
   ============================================================ */
session_unset();

/* ============================================================
   2. APAGAR O COOKIE DE SESSÃO NO BROWSER
   Sem este passo, o cookie PHPSESSID fica no browser do utilizador
   mesmo após session_destroy(). Ao expirar o cookie com uma data
   no passado, o browser apaga-o imediatamente.

   session_get_cookie_params() lê os parâmetros actuais do cookie
   (path, domain, secure, httponly) para os reutilizar na expiração.
   ============================================================ */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),          /* Nome do cookie (ex: "PHPSESSID") */
        '',                      /* Valor vazio */
        time() - 42000,          /* Data de expiração no passado — apaga o cookie */
        $params["path"],         /* Mesmo path que o cookie original */
        $params["domain"],       /* Mesmo domain que o cookie original */
        $params["secure"],       /* HTTPS only se estava configurado assim */
        $params["httponly"]      /* HttpOnly — não acessível via JavaScript */
    );
}

/* ============================================================
   3. DESTRUIR OS DADOS DA SESSÃO NO SERVIDOR
   session_destroy() apaga os dados guardados no servidor
   (ficheiro de sessão em /tmp ou na base de dados, conforme config)
   ============================================================ */
session_destroy();

/* Redireciona para a página inicial após logout completo */
header("Location: ../index.php");
exit();