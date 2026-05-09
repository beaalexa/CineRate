<?php
/* ============================================================
   CINERATE — db.php
   Ficheiro de configuração e ligação à base de dados.

   Cria uma instância PDO ($pdo) reutilizável em todos os
   ficheiros que fazem require_once deste ficheiro.

   Boas práticas aplicadas:
     - PDO com prepared statements — previne SQL Injection
     - charset=utf8mb4 — suporte completo a Unicode (emojis, etc.)
     - ERRMODE_EXCEPTION — lança excepções em vez de falhar silenciosamente
     - FETCH_ASSOC por defeito — resultados como arrays associativos
     - EMULATE_PREPARES false — prepared statements reais no MySQL
     - Credenciais em constantes — mais fácil de alterar e auditar

   ATENÇÃO — Produção:
     - Nunca usar root sem password em produção
     - Criar um utilizador MySQL dedicado com permissões mínimas:
         CREATE USER 'cinerate_user'@'localhost' IDENTIFIED BY 'password_forte';
         GRANT SELECT, INSERT, UPDATE, DELETE ON cinerate.* TO 'cinerate_user'@'localhost';
     - Mover as credenciais para variáveis de ambiente ou ficheiro .env
     - Garantir que este ficheiro está fora da pasta pública (public_html)
   ============================================================ */

/* ============================================================
   CREDENCIAIS DE LIGAÇÃO
   Alterar aqui para configurar o ambiente (local / servidor)
   ============================================================ */
define('DB_HOST',    'localhost');   /* Endereço do servidor MySQL */
define('DB_NAME',    'cinerate');    /* Nome da base de dados */
define('DB_USER',    'root');        /* Utilizador MySQL */
define('DB_PASS',    '');            /* Password do utilizador MySQL */
define('DB_CHARSET', 'utf8mb4');     /* utf8mb4 suporta emojis e todos os caracteres Unicode
                                        (utf8 no MySQL é limitado a 3 bytes — usa utf8mb4) */

/* ============================================================
   OPÇÕES DO PDO
   Configuradas no array de opções do construtor —
   mais eficiente que chamar setAttribute() múltiplas vezes
   ============================================================ */
$options = [
    /* Lança PDOException em erros — permite apanhar com try/catch */
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    /* Resultados como arrays associativos por defeito (ex: $row['name'])
       em vez de arrays numéricos (ex: $row[0]) */
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    /* Prepared statements REAIS no servidor MySQL, não simulados pelo PHP.
       Mais seguro contra SQL Injection e mais eficiente para queries repetidas */
    PDO::ATTR_EMULATE_PREPARES   => false,
];

/* ============================================================
   LIGAÇÃO À BASE DE DADOS
   O DSN (Data Source Name) especifica o driver, host, nome da BD
   e charset numa única string
   ============================================================ */
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        $options
    );

} catch (PDOException $e) {

    /* Em caso de falha na ligação:
         - Regista o erro detalhado no log do servidor (para o programador)
         - Mostra uma mensagem genérica ao utilizador (não expõe credenciais)
       NUNCA mostrar $e->getMessage() directamente ao utilizador em produção —
       pode revelar o host, nome da BD, utilizador, etc. */
    error_log("Falha na ligação à base de dados [CineRate]: " . $e->getMessage());

    /* Termina o script com uma mensagem genérica e segura */
    http_response_code(500);
    die("Não foi possível ligar à base de dados. Tenta novamente mais tarde.");
}