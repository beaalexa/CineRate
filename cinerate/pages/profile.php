<?php
/* ============================================================
   CINERATE — profile.php
   Página de edição do perfil do utilizador autenticado.

   Funcionalidades:
     - Mostra foto de perfil actual (ou placeholder com inicial)
     - Exibe nome e email (não editáveis — identificadores da conta)
     - Permite editar: biografia, tema (claro/escuro) e foto de perfil
     - Formulário envia para update_profile.php via POST

   Acesso restrito: apenas utilizadores autenticados.

   Segurança:
     - Redirecionamento imediato se não autenticado
     - htmlspecialchars() em todos os dados de saída
     - enctype="multipart/form-data" para upload de foto
   ============================================================ */

global $pdo;
session_start();
require_once '../config/db.php';

/* --- Controlo de acesso ---
   Utilizadores não autenticados são redireccionados para o login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ============================================================
   QUERY — Dados do utilizador autenticado
   Usa o ID guardado na sessão para garantir que cada utilizador
   só vê e edita os seus próprios dados
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT name, email, bio, theme, photo
    FROM users
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* Mensagem de sucesso após actualização — vinda de update_profile.php via ?sucesso=1 */
$showSuccess = isset($_GET['sucesso']);
?>

<?php include '../includes/header.php'; ?>

    <main class="container">

        <section class="auth-card">

            <h2>O Meu Perfil</h2>
            <p class="subtitle">Gere as informações da tua conta</p>

            <!-- Mensagem de confirmação após guardar alterações -->
            <?php if ($showSuccess): ?>
                <p class="success">&#10003; Perfil actualizado com sucesso!</p>
            <?php endif; ?>

            <!-- Foto de perfil actual -->
            <?php if (!empty($user['photo'])): ?>
                <!-- Foto real do utilizador -->
                <img
                        class="profile-photo"
                        src="/cinerate/assets/uploads/profiles/<?= htmlspecialchars($user['photo']) ?>"
                        alt="A tua foto de perfil">
            <?php else: ?>
                <!-- Placeholder: círculo roxo com inicial do nome -->
                <div class="profile-placeholder">
                    <?= strtoupper(substr(htmlspecialchars($user['name']), 0, 1)) ?>
                </div>
            <?php endif; ?>

            <!-- Nome e email — mostrados mas não editáveis neste formulário
                 (são identificadores da conta e não devem ser alterados aqui) -->
            <div style="margin-bottom:16px; text-align:left;">
                <p style="margin-bottom:6px;">
                    <span class="text-muted" style="font-size:13px;">Nome</span><br>
                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                </p>
                <p>
                    <span class="text-muted" style="font-size:13px;">Email</span><br>
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                </p>
            </div>

            <hr class="divider">

            <!-- ============================================================
                 FORMULÁRIO DE EDIÇÃO DE PERFIL
                 enctype="multipart/form-data" — necessário para upload de foto
                 ============================================================ -->
            <form
                    action="../actions/update_profile.php"
                    method="POST"
                    class="form"
                    enctype="multipart/form-data"
                    novalidate>

                <!-- Biografia — textarea pré-preenchida com o valor actual
                     O valor vai entre as tags para preservar o texto existente -->
                <label style="text-align:left;">Biografia</label>
                <textarea
                        name="bio"
                        placeholder="Conta um pouco sobre ti e os teus gostos cinematográficos..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

                <!-- Selector de tema — o option correspondente ao tema actual fica seleccionado -->
                <label style="text-align:left;">Tema visual</label>
                <select name="theme">
                    <option value="dark"  <?= ($user['theme'] ?? 'dark') === 'dark'  ? 'selected' : '' ?>>
                        🌙 Modo Escuro
                    </option>
                    <option value="light" <?= ($user['theme'] ?? 'dark') === 'light' ? 'selected' : '' ?>>
                        ☀️ Modo Claro
                    </option>
                </select>

                <!-- Upload de nova foto de perfil
                     Se não for fornecida, update_profile.php mantém a foto existente.
                     accept limita ao seletor de ficheiros do sistema operativo -->
                <label style="text-align:left;">Foto de perfil</label>
                <input
                        type="file"
                        name="photo"
                        accept="image/jpeg,image/png,image/webp">
                <p class="text-muted" style="font-size:12px; text-align:left; margin-top:-8px;">
                    Formatos aceites: JPG, PNG, WEBP. Deixa em branco para manter a actual.
                </p>

                <button type="submit" class="btn">&#10003; Guardar Alterações</button>

            </form>

            <!-- Link para ver o perfil público -->
            <p class="mt-16">
                <a href="user_profile.php?id=<?= (int) $_SESSION['user_id'] ?>" class="btn btn-ghost btn-small">
                    Ver perfil público &#8594;
                </a>
            </p>

        </section>

    </main>

<?php include '../includes/footer.php'; ?>