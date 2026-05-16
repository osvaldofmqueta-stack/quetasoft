<?php
session_start();
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'superescola2026');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
        header('Location: index.php');
        exit;
    }
    $erro = 'Credenciais incorretas. Tente novamente.';
}

if (!empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Super Escola</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
  <div class="login-box">
    <div class="login-logo">🎓 <strong>Super</strong>Escola</div>
    <div class="login-subtitle">Painel Administrativo</div>

    <?php if (!empty($erro)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <div class="form-group">
        <label>Utilizador</label>
        <input type="text" name="username" placeholder="admin" required autofocus>
      </div>
      <div class="form-group">
        <label>Palavra-passe</label>
        <input type="password" name="password" placeholder="••••••••••" required>
      </div>
      <button type="submit" class="btn-admin-primary btn-full">Entrar no painel</button>
    </form>
    <p class="login-back"><a href="/">← Voltar ao site</a></p>
  </div>
</body>
</html>
