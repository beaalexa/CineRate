<?php
/* ============================================================
   CINERATE — update_movie_action.php
   Action de processamento do formulário de edição de filme.

   Recebe os dados via POST de edit_movie.php e:
     1. Verifica autenticação e papel de admin
     2. Valida e sanitiza os campos de texto
     3. Processa o novo upload de imagem (se fornecido)
        - Valida tipo MIME real com mime_content_type()
        - Apaga a imagem antiga para não ocupar espaço desnecessário
        - Gera novo nome de ficheiro único
     4. Actualiza o registo do filme na base de dados
     5. Redireciona para a página de detalhe do filme

   Segurança:
     - Controlo de acesso duplo (sessão + papel)
     - mime_content_type() para validar o tipo real do ficheiro
     - Limite de tamanho de upload (2MB)
     - uniqid() para nome de ficheiro seguro gerado pelo servidor
     - Query parametrizada com PDO
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso --- */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("Acesso negado. Apenas administradores podem editar filmes.");
}

/* --- Verificação do método HTTP --- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/movies.php");
    exit();
}

/* ============================================================
   1. LEITURA E SANITIZAÇÃO DOS CAMPOS DE TEXTO
   ============================================================ */
$movie_id    = (int) ($_POST['movie_id']    ?? 0);
$title       = trim($_POST['title']         ?? '');
$year        = trim($_POST['year']          ?? '');
$genre       = trim($_POST['genre']         ?? '');
$description = trim($_POST['description']   ?? '');

/* Valida que o ID do filme é válido */
if ($movie_id <= 0) {
    header("Location: ../pages/movies.php");
    exit();
}

/* O título é obrigatório */
if ($title === '') {
    header("Location: ../pages/edit_movie.php?id=" . $movie_id . "&erro=titulo");
    exit();
}

/* Ano: inteiro válido ou NULL */
$year = ($year !== '' && is_numeric($year)) ? (int) $year : null;

/* ============================================================
   2. LER A IMAGEM ACTUAL DA BASE DE DADOS
   Necessário para:
     a) Manter a imagem existente se não for fornecida nova
     b) Apagar o ficheiro antigo se for substituído
   ============================================================ */
$stmtCurrent = $pdo->prepare("SELECT image FROM movies WHERE id = ?");
$stmtCurrent->execute([$movie_id]);
$current = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

/* Começa com a imagem actual (pode ser null se não tiver poster) */
$imageName = $current['image'] ?? null;

/* ============================================================
   3. PROCESSAMENTO DO NOVO UPLOAD DE IMAGEM (opcional)
   ============================================================ */
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    /* mime_content_type() lê os magic bytes reais do ficheiro —
       mais seguro que $_FILES['image']['type'] (vem do browser) */
    $realType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($realType, $allowedTypes)) {
        die("Formato de imagem inválido. Usa JPG, PNG ou WEBP.");
    }

    /* Limite de tamanho: 2MB */
    $maxSize = 2 * 1024 * 1024;
    if ($_FILES['image']['size'] > $maxSize) {
        die("A imagem não pode ter mais de 2MB.");
    }

    /* Gera novo nome de ficheiro único e seguro */
    $extension    = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $newImageName = uniqid('movie_', true) . '.' . $extension;
    $uploadPath   = '../assets/uploads/' . $newImageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {

        /* Upload com sucesso — apaga a imagem antiga do servidor
           para não acumular ficheiros órfãos na pasta uploads/ */
        if (!empty($imageName)) {
            $oldPath = '../assets/uploads/' . $imageName;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        /* Actualiza a variável com o nome do novo ficheiro */
        $imageName = $newImageName;

    } else {
        die("Erro ao fazer upload da imagem. Verifica as permissões da pasta uploads/.");
    }
}

/* ============================================================
   4. ACTUALIZAÇÃO NA BASE DE DADOS
   WHERE id = ? garante que só o filme correcto é actualizado
   ============================================================ */
$stmt = $pdo->prepare("
    UPDATE movies
    SET title       = ?,
        year        = ?,
        genre       = ?,
        description = ?,
        image       = ?
    WHERE id = ?
");
$stmt->execute([$title, $year, $genre, $description, $imageName, $movie_id]);

/* Redireciona para a página de detalhe do filme editado */
header("Location: ../pages/movie.php?id=" . $movie_id);
exit();