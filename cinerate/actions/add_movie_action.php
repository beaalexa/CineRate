<?php
/* ============================================================
   CINERATE — add_movie_action.php
   Action de processamento do formulário de adição de filme.

   Recebe os dados via POST de add_movie.php e:
     1. Verifica autenticação e papel de admin
     2. Valida e sanitiza os campos de texto
     3. Valida e faz upload do poster (se fornecido)
     4. Insere o filme na base de dados
     5. Redireciona para a lista de filmes

   Segurança:
     - Controlo de acesso duplo (sessão + papel)
     - mime_content_type() para validar o tipo real do ficheiro
     - Limite de tamanho de upload (2MB)
     - Nome de ficheiro gerado pelo servidor com uniqid()
     - Query parametrizada com PDO — previne SQL Injection
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Apenas administradores autenticados podem adicionar filmes.
   http_response_code(403) antes de qualquer output é boa prática. */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("Acesso negado. Apenas administradores podem adicionar filmes.");
}

/* --- Verificação do método HTTP ---
   Esta action só aceita POST — redireciona qualquer outro método */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/add_movie.php");
    exit();
}

/* ============================================================
   1. SANITIZAÇÃO DOS CAMPOS DE TEXTO
   trim() remove espaços em branco no início e no fim de cada campo
   O operador ?? garante que nunca há erro se o campo não vier no POST
   ============================================================ */
$title       = trim($_POST['title']       ?? '');
$year        = trim($_POST['year']        ?? '');
$genre       = trim($_POST['genre']       ?? '');
$description = trim($_POST['description'] ?? '');

/* O título é o único campo obrigatório */
if ($title === '') {
    header("Location: ../pages/add_movie.php?erro=titulo");
    exit();
}

/* Ano: converte para inteiro se válido, ou NULL para omitido/inválido
   Guarda NULL na BD em vez de string vazia para consistência */
$year = ($year !== '' && is_numeric($year)) ? (int) $year : null;

/* ============================================================
   2. PROCESSAMENTO DO UPLOAD DE IMAGEM (campo opcional)
   ============================================================ */
$imageName = null;   /* Fica NULL se não for fornecida imagem */

/* UPLOAD_ERR_OK (valor 0) confirma que o ficheiro chegou sem erros */
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

    /* Tipos MIME permitidos */
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    /* mime_content_type() lê os "magic bytes" do ficheiro temporário —
       muito mais seguro que $_FILES['image']['type'], que vem do browser
       e pode ser facilmente falsificado pelo utilizador */
    $realType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($realType, $allowedTypes)) {
        die("Formato de imagem inválido. Usa JPG, PNG ou WEBP.");
    }

    /* Limite de tamanho: 2MB */
    $maxSize = 2 * 1024 * 1024;
    if ($_FILES['image']['size'] > $maxSize) {
        die("A imagem não pode ter mais de 2MB.");
    }

    /* Gera um nome de ficheiro único e seguro com uniqid().
       NUNCA usar o nome original — pode conter caracteres especiais perigosos
       ou sobrescrever ficheiros existentes. */
    $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $imageName = uniqid('movie_', true) . '.' . $extension;

    $uploadPath = '../assets/uploads/' . $imageName;

    /* move_uploaded_file() move o temporário para o destino e confirma
       que o ficheiro veio mesmo de um upload HTTP (validação extra) */
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        die("Erro ao fazer upload da imagem. Verifica as permissões da pasta uploads/.");
    }
}

/* ============================================================
   3. INSERÇÃO NA BASE DE DADOS
   Todos os valores passam como parâmetros — nunca interpolados na SQL
   ============================================================ */
$stmt = $pdo->prepare("
    INSERT INTO movies (title, year, genre, description, image)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$title, $year, $genre, $description, $imageName]);

/* Redireciona para a lista de filmes após inserção com sucesso */
header("Location: ../pages/movies.php");
exit();