<?php
session_start();
if (empty($_SESSION['admin_logged'])) { header('Location: /admin/login.php'); exit; }
require_once '../../database/init.php';
$db = getDb();

$msg = '';
$co = getSetting($db, 'company', [
    'nome' => 'Super Escola', 'slogan' => '', 'logo_url' => '',
    'descricao' => '', 'morada' => '', 'telefone' => '+244 926 219 731',
    'email' => 'geral@superescola.ao', 'website' => '',
    'facebook' => '', 'instagram' => '', 'linkedin' => '',
    'ano_fundacao' => '', 'missao' => '', 'visao' => '', 'valores' => '',
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $co = [
        'nome'          => trim($_POST['nome'] ?? ''),
        'slogan'        => trim($_POST['slogan'] ?? ''),
        'logo_url'      => trim($_POST['logo_url'] ?? ''),
        'descricao'     => trim($_POST['descricao'] ?? ''),
        'morada'        => trim($_POST['morada'] ?? ''),
        'telefone'      => trim($_POST['telefone'] ?? ''),
        'email'         => trim($_POST['email'] ?? ''),
        'website'       => trim($_POST['website'] ?? ''),
        'facebook'      => trim($_POST['facebook'] ?? ''),
        'instagram'     => trim($_POST['instagram'] ?? ''),
        'linkedin'      => trim($_POST['linkedin'] ?? ''),
        'ano_fundacao'  => trim($_POST['ano_fundacao'] ?? ''),
        'missao'        => trim($_POST['missao'] ?? ''),
        'visao'         => trim($_POST['visao'] ?? ''),
        'valores'       => trim($_POST['valores'] ?? ''),
    ];
    saveSetting($db, 'company', $co);
    $msg = 'success|Dados da empresa guardados com sucesso!';
}

[$msg_type, $msg_text] = $msg ? explode('|', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Empresa — Super Escola Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .co-form { padding: 0 32px 48px; max-width: 900px; }
    .form-section { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:20px; }
    .form-section h3 { font-size:15px; font-weight:700; margin-bottom:18px; color:#1e293b; padding-bottom:12px; border-bottom:1px solid #f1f5f9; }
    .fg { display:grid; gap:14px; }
    .fg-2 { grid-template-columns:1fr 1fr; }
    .fg-3 { grid-template-columns:1fr 1fr 1fr; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.4px; }
    .form-group input, .form-group textarea { width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; font-family:inherit; box-sizing:border-box; transition:0.2s; }
    .form-group input:focus, .form-group textarea:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
    .form-group textarea { resize:vertical; min-height:90px; }
    .fp-actions { display:flex; gap:12px; margin-top:20px; align-items:center; }
    .btn-view { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; background:#f1f5f9; color:#374151; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; }
    .btn-view:hover { background:#e2e8f0; }
    .alert { padding:12px 16px; border-radius:8px; margin:0 32px 16px; font-size:14px; font-weight:500; }
    .alert-success { background:#dcfce7; color:#166534; }
    @media(max-width:768px) { .fg-2,.fg-3 { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">🎓 <strong>Super</strong>Escola</div>
  <nav class="sidebar-nav">
    <a href="/admin/index.php" class="snav-item">📋 Pedidos / Leads</a>
    <a href="/admin/stats.php" class="snav-item">📊 Estatísticas</a>
    <a href="/admin/schools.php" class="snav-item">🏫 Escolas & Testemunhos</a>
    <a href="/admin/posts.php" class="snav-item">📝 Artigos do Blog</a>
    <a href="/admin/developer.php" class="snav-item">👤 Currículo</a>
    <a href="/admin/company.php" class="snav-item active">🏢 Empresa</a>
  </nav>
  <div class="sidebar-footer">
    <a href="/" class="snav-item" target="_blank">🌐 Ver site</a>
    <a href="/admin/logout.php" class="snav-item snav-logout">🚪 Sair</a>
  </div>
</aside>

<main class="admin-main">
  <header class="admin-header">
    <div>
      <h1 class="admin-title">Identificação da Empresa</h1>
      <p class="admin-subtitle">Dados institucionais da empresa — geram uma página pública de apresentação</p>
    </div>
    <a href="/empresa.php" target="_blank" class="btn-primary-sm">👁 Ver página pública →</a>
  </header>

  <?php if ($msg_text): ?>
  <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg_text) ?></div>
  <?php endif; ?>

  <form method="POST" class="co-form">

    <div class="form-section">
      <h3>🏢 Identidade da Empresa</h3>
      <div class="fg fg-2">
        <div class="form-group">
          <label>Nome da empresa</label>
          <input type="text" name="nome" value="<?= htmlspecialchars($co['nome']) ?>" placeholder="Ex: Super Escola">
        </div>
        <div class="form-group">
          <label>Slogan</label>
          <input type="text" name="slogan" value="<?= htmlspecialchars($co['slogan']) ?>" placeholder="Ex: A gestão escolar moderna">
        </div>
        <div class="form-group">
          <label>URL do logótipo</label>
          <input type="url" name="logo_url" value="<?= htmlspecialchars($co['logo_url']) ?>" placeholder="https://...">
        </div>
        <div class="form-group">
          <label>Ano de fundação</label>
          <input type="text" name="ano_fundacao" value="<?= htmlspecialchars($co['ano_fundacao']) ?>" placeholder="Ex: 2022">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>Descrição da empresa</label>
          <textarea name="descricao" placeholder="O que é a empresa, o que faz, a quem serve..."><?= htmlspecialchars($co['descricao']) ?></textarea>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3>📞 Contactos</h3>
      <div class="fg fg-3">
        <div class="form-group">
          <label>Telefone / WhatsApp</label>
          <input type="text" name="telefone" value="<?= htmlspecialchars($co['telefone']) ?>" placeholder="+244 9XX XXX XXX">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($co['email']) ?>" placeholder="geral@empresa.com">
        </div>
        <div class="form-group">
          <label>Website</label>
          <input type="url" name="website" value="<?= htmlspecialchars($co['website']) ?>" placeholder="https://...">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>Morada</label>
          <input type="text" name="morada" value="<?= htmlspecialchars($co['morada']) ?>" placeholder="Ex: Luanda, Angola">
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3>🌐 Redes Sociais</h3>
      <div class="fg fg-3">
        <div class="form-group">
          <label>Facebook (URL)</label>
          <input type="url" name="facebook" value="<?= htmlspecialchars($co['facebook']) ?>" placeholder="https://facebook.com/...">
        </div>
        <div class="form-group">
          <label>Instagram (URL)</label>
          <input type="url" name="instagram" value="<?= htmlspecialchars($co['instagram']) ?>" placeholder="https://instagram.com/...">
        </div>
        <div class="form-group">
          <label>LinkedIn (URL)</label>
          <input type="url" name="linkedin" value="<?= htmlspecialchars($co['linkedin']) ?>" placeholder="https://linkedin.com/...">
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3>🎯 Missão, Visão & Valores</h3>
      <div class="fg">
        <div class="form-group">
          <label>Missão <span style="font-weight:400;color:#94a3b8;">(por que existimos)</span></label>
          <textarea name="missao" placeholder="Ex: Simplificar a gestão das escolas angolanas através da tecnologia..."><?= htmlspecialchars($co['missao']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Visão <span style="font-weight:400;color:#94a3b8;">(onde queremos chegar)</span></label>
          <textarea name="visao" placeholder="Ex: Ser a plataforma de gestão escolar mais usada em Angola..."><?= htmlspecialchars($co['visao']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Valores <span style="font-weight:400;color:#94a3b8;">(separe com vírgulas)</span></label>
          <input type="text" name="valores" value="<?= htmlspecialchars($co['valores']) ?>" placeholder="Ex: Inovação, Simplicidade, Fiabilidade, Impacto">
        </div>
      </div>
    </div>

    <div class="fp-actions">
      <button type="submit" class="btn-primary-sm">💾 Guardar dados da empresa</button>
      <a href="/empresa.php" target="_blank" class="btn-view">👁 Ver página pública →</a>
    </div>
  </form>
</main>
</body>
</html>
