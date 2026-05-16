<?php
session_start();
if (empty($_SESSION['admin_logged'])) {
    header('Location: /admin/login.php');
    exit;
}
require_once '../../database/init.php';
$db = getDb();

$id = (int)($_GET['id'] ?? 0);

if (isset($_GET['acao']) && $_GET['acao'] === 'apagar') {
    $db->prepare("DELETE FROM leads WHERE id = ?")->execute([$id]);
    header('Location: /admin/index.php');
    exit;
}

$lead = $db->prepare("SELECT * FROM leads WHERE id = ?");
$lead->execute([$id]);
$lead = $lead->fetch();

if (!$lead) {
    header('Location: /admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'] ?? $lead['estado'];
    $db->prepare("UPDATE leads SET estado = ? WHERE id = ?")->execute([$estado, $id]);
    header('Location: /admin/lead.php?id=' . $id . '&saved=1');
    exit;
}

$labels = ['novo' => '🔵 Novo', 'contactado' => '🟡 Contactado', 'convertido' => '🟢 Convertido', 'cancelado' => '🔴 Cancelado'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lead #<?= $lead['id'] ?> — Super Escola Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">🎓 <strong>Super</strong>Escola</div>
    <nav class="sidebar-nav">
      <a href="/admin/index.php" class="snav-item active">📋 Pedidos / Leads</a>
      <a href="/admin/stats.php" class="snav-item">📊 Estatísticas</a>
      <a href="/admin/schools.php" class="snav-item">🏫 Escolas & Testemunhos</a>
      <a href="/admin/posts.php" class="snav-item">📝 Artigos do Blog</a>
    </nav>
    <div class="sidebar-footer">
      <a href="/" class="snav-item" target="_blank">🌐 Ver site</a>
      <a href="/admin/logout.php" class="snav-item snav-logout">🚪 Sair</a>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <div>
        <a href="/admin/index.php" class="back-link">← Voltar à lista</a>
        <h1 class="admin-title">Pedido #<?= $lead['id'] ?> — <?= htmlspecialchars($lead['nome']) ?></h1>
      </div>
    </header>

    <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">✅ Estado atualizado com sucesso!</div>
    <?php endif; ?>

    <div class="lead-detail-grid">
      <div class="lead-detail-card">
        <div class="detail-avatar"><?= mb_strtoupper(mb_substr($lead['nome'], 0, 2)) ?></div>
        <h2><?= htmlspecialchars($lead['nome']) ?></h2>
        <div class="detail-escola"><?= htmlspecialchars($lead['escola']) ?></div>
        <div class="detail-date">📅 <?= date('d/m/Y \à\s H:i', strtotime($lead['criado_em'])) ?></div>

        <div class="detail-contacts">
          <a href="https://wa.me/<?= preg_replace('/\D/', '', $lead['telefone']) ?>?text=Olá+<?= urlencode($lead['nome']) ?>!+Sou+da+equipa+Super+Escola.+Gostaria+de+agendar+a+sua+demonstração." target="_blank" class="contact-btn wa">
            💬 WhatsApp — <?= htmlspecialchars($lead['telefone']) ?>
          </a>
          <?php if ($lead['email']): ?>
          <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="contact-btn email">
            📧 <?= htmlspecialchars($lead['email']) ?>
          </a>
          <?php endif; ?>
        </div>

        <?php if ($lead['mensagem']): ?>
        <div class="detail-msg">
          <div class="detail-msg-label">💬 Mensagem do cliente</div>
          <p><?= nl2br(htmlspecialchars($lead['mensagem'])) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <div>
        <div class="lead-detail-card">
          <h3 class="detail-section-title">Atualizar Estado</h3>
          <form method="POST" class="estado-form">
            <div class="estado-options">
              <?php foreach ($labels as $val => $label): ?>
              <label class="estado-option <?= $lead['estado'] === $val ? 'selected' : '' ?>">
                <input type="radio" name="estado" value="<?= $val ?>" <?= $lead['estado'] === $val ? 'checked' : '' ?>>
                <?= $label ?>
              </label>
              <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-admin-primary">Guardar alteração</button>
          </form>
        </div>

        <div class="lead-detail-card">
          <h3 class="detail-section-title">Ações rápidas</h3>
          <div class="quick-actions">
            <a href="https://wa.me/<?= preg_replace('/\D/', '', $lead['telefone']) ?>?text=Olá+<?= urlencode($lead['nome']) ?>!+Sou+da+equipa+Super+Escola+e+gostaria+de+agendar+uma+demonstração+gratuita+do+nosso+sistema+para+a+<?= urlencode($lead['escola']) ?>.+Quando+teria+disponibilidade%3F" target="_blank" class="quick-btn green">
              💬 Enviar mensagem WhatsApp
            </a>
            <?php if ($lead['email']): ?>
            <a href="mailto:<?= htmlspecialchars($lead['email']) ?>?subject=Demonstração+Super+Escola&body=Olá+<?= urlencode($lead['nome']) ?>,%0D%0A%0D%0AObrigado+pelo+seu+interesse+no+Super+Escola.%0D%0A%0D%0AGostaríamos+de+agendar+uma+demonstração+gratuita+e+personalizada+para+a+<?= urlencode($lead['escola']) ?>.%0D%0A%0D%0AQuando+teria+disponibilidade%3F%0D%0A%0D%0AEquipa+Super+Escola" class="quick-btn blue">
              📧 Enviar email
            </a>
            <?php endif; ?>
            <a href="?acao=apagar&id=<?= $lead['id'] ?>" class="quick-btn red" onclick="return confirm('Apagar este pedido permanentemente?')" style="text-decoration:none;display:block;">
              🗑 Apagar pedido
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
