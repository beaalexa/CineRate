<?php
/* ============================================================
   CINERATE — update_profile.php
   Action de processamento do formulário de edição de perfil.

   Recebe os dados via POST de profile.php e:
     1. Verifica autenticação
     2. Sanitiza e valida os campos (bio, tema, foto)
     3. Processa o upload de nova foto (se fornecida)
        - Valida tipo MIME real
        - Apaga a foto antiga para não ocupar espaço
     4. Actualiza o registo do utilizador na base de dados
     5. Actualiza o tema na sessão (efeito imediato sem logout)
     6. Redireciona para o perfil com mensagem de sucesso

   Segurança:
     - user_id sempre da sessão — um utilizador só pode editar
       o seu próprio perfil, nunca o de outro
     - Tema validado contra lista de valores permitidos (whitelist)
     - mime_content_type() para validar tipo real da imagem
     - Limite de tamanho de upload (2MB)
     - uniqid() para nome de ficheiro seguro
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Só utilizadores autenticados podem editar o perfil */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

/* --- Verificação do método HTTP --- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/profile.php");
    exit();
}

/* ============================================================
   1. LEITURA E SANITIZAÇÃO DOS CAMPOS
   ============================================================ */

/* Biografia — trim() remove espaços desnecessários */
$bio = trim($_POST['bio'] ?? '');

/* Tema — validado contra whitelist de valores permitidos.
   Se o valor não for 'light' nem 'dark', usa 'dark' por defeito.
   Isto previne a inserção de valores arbitrários na BD. */
$theme = $_POST['theme'] ?? 'dark';
if (!in_array($theme, ['light', 'dark'], true)) {
    $theme = 'dark';
}

/* ============================================================
   2. LER OS DADOS ACTUAIS DO UTILIZADOR
   Necessário para manter a foto existente se não for fornecida nova
   e para apagar a foto antiga se for substituída
   ============================================================ */
$stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
$stmt->execute([(int) $_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

/* Começa com a foto actual (null se não tiver foto) */
$photoName = $currentUser['photo'] ?? null;

/* ============================================================
   3. PROCESSAMENTO DO UPLOAD DE NOVA FOTO (opcional)
   ============================================================ */
if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    /* mime_content_type() lê os magic bytes do ficheiro —
       mais fiável que $_FILES['photo']['type'] (valor do browser) */
    $realType = mime_content_type($_FILES['photo']['tmp_name']);

    if (!in_array($realType, $allowedTypes)) {
        die("Formato inválido. Usa JPG, PNG ou WEBP.");
    }

    /* Limite de tamanho: 2MB */
    $maxSize = 2 * 1024 * 1024;
    if ($_FILES['photo']['size'] > $maxSize) {
        die("A foto não pode ter mais de 2MB.");
    }

    /* Gera nome de ficheiro único e seguro */
    $extension    = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $newPhotoName = uniqid('profile_', true) . '.' . $extension;
    $uploadPath   = '../assets/uploads/profiles/' . $newPhotoName;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {

        /* Upload com sucesso — apaga a foto antiga do servidor */
        if (!empty($photoName)) {
            $oldPath = '../assets/uploads/profiles/' . $photoName;
            if (file_exists($oldPath)) {
                unlink($oldPath);   /* Remove o ficheiro físico do servidor */
            }
        }

        $photoName = $newPhotoName;

    } else {
        die("Erro ao fazer upload da foto. Verifica as permissões da pasta uploads/profiles/.");
    }
}

/* ============================================================
   4. ACTUALIZAÇÃO NA BASE DE DADOS
   WHERE id = ? com o ID da sessão garante que o utilizador
   só actualiza os seus próprios dados
   ============================================================ */
$stmt = $pdo->prepare("
    UPDATE users
    SET bio   = ?,
        theme = ?,
        photo = ?
    WHERE id  = ?
");
$stmt->execute([$bio, $theme, $photoName, (int) $_SESSION['user_id']]);

/* ============================================================
   5. ACTUALIZAR O TEMA NA SESSÃO
   Garante que o novo tema é aplicado imediatamente na próxima
   página sem necessidade de logout e login novamente
   ============================================================ */
$_SESSION['user_theme'] = $theme;

/* Redireciona para o perfil com mensagem de sucesso */
header("Location: ../pages/profile.php?sucesso=1");
exit();