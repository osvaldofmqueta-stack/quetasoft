<?php
session_start();
if (empty($_SESSION['admin_logged'])) { header('Location: /admin/login.php'); exit; }
require_once '../../database/init.php';
$db = getDb();

$msg = '';
$dev = getSetting($db, 'developer', [
    'nome' => '', 'cargo' => '', 'foto_url' => '', 'bio' => '',
    'localizacao' => '', 'whatsapp' => '', 'email' => '',
    'linkedin' => '', 'github' => '',
    'skills' => ['', '', '', '', '', '', '', ''],
    'experiencias' => [
        ['cargo' => '', 'empresa' => '', 'periodo' => '', 'descricao' => ''],
        ['cargo' => '', 'empresa' => '', 'periodo' => '', 'descricao' => ''],
        ['cargo' => '', 'empresa' => '', 'periodo' => '', 'descricao' => ''],
    ],
    'projetos' => [
        ['nome' => '', 'url' => '', 'descricao' => ''],
        ['nome' => '', 'url' => '', 'descricao' => ''],
        ['nome' => '', 'url' => '', 'descricao' => ''],
    ],
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills_raw = $_POST['skills'] ?? [];
    $skills = array_values(array_filter(array_map('trim', (array)$skills_raw)));

    $exps = [];
    foreach (($_POST['exp_cargo'] ?? []) as $i => $v) {
        $cargo = trim($v);
        $empresa = trim($_POST['exp_empresa'][$i] ?? '');
        $periodo = trim($_POST['exp_periodo'][$i] ?? '');
        $desc = trim($_POST['exp_descricao'][$i] ?? '');
        if ($cargo || $empresa) {
            $exps[] = ['cargo' => $cargo, 'empresa' => $empresa, 'periodo' => $periodo, 'descricao' => $desc];
        }
    }

    $projs = [];
    foreach (($_POST['proj_nome'] ?? []) as $i => $v) {
        $nome = trim($v);
        $url = trim($_POST['proj_url'][$i] ?? '');
        $desc = trim($_POST['proj_descricao'][$i] ?? '');
        if ($nome) {
            $projs[] = ['nome' => $nome, 'url' => $url, 'descricao' => $desc];
        }
    }

    $dev = [
        'nome'       => trim($_POST['nome'] ?? ''),
        'cargo'      => trim($_POST['cargo'] ?? ''),
        'foto_url'   => trim($_POST['foto_url'] ?? ''),
        'bio'        => trim($_POST['bio'] ?? ''),
        'localizacao'=> trim($_POST['localizacao'] ?? ''),
        'whatsapp'   => trim($_POST['whatsapp'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'linkedin'   => trim($_POST['linkedin'] ?? ''),
        'github'     => trim($_POST['github'] ?? ''),
        'skills'     => $skills,
        'experiencias' => $exps,
        'projetos'   => $projs,
    ];

    saveSetting($db, 'developer', $dev);
    $msg = 'success|Currículo guardado com sucesso!';
}

$e = $dev;
$skills_padded = array_pad($e['skills'] ?? [], 8, '');
$exps_padded   = array_pad($e['experiencias'] ?? [], 3, ['cargo'=>'','empresa'=>'','periodo'=>'','descricao'=>'']);
$projs_padded  = array_pad($e['projetos'] ?? [], 3, ['nome'=>'','url'=>'','descricao'=>'']);
[$msg_type, $msg_text] = $msg ? explode('|', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Currículo — Super Escola Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .dev-form { padding: 0 32px 48px; max-width: 900px; }
    .form-section { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:20px; }
    .form-section h3 { font-size:15px; font-weight:700; margin-bottom:18px; color:#1e293b; padding-bottom:12px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:8px; }
    .fg { display:grid; gap:14px; }
    .fg-2 { grid-template-columns:1fr 1fr; }
    .fg-3 { grid-template-columns:1fr 1fr 1fr; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.4px; }
    .form-group input, .form-group textarea { width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; font-family:inherit; box-sizing:border-box; transition:0.2s; }
    .form-group input:focus, .form-group textarea:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
    .form-group textarea { resize:vertical; min-height:80px; }
    .skills-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
    .exp-entry, .proj-entry { background:#f8fafc; border-radius:8px; padding:14px; margin-bottom:12px; }
    .exp-entry:last-child, .proj-entry:last-child { margin-bottom:0; }
    .entry-num { font-size:12px; font-weight:700; color:#94a3b8; margin-bottom:10px; text-transform:uppercase; }
    .fp-actions { display:flex; gap:12px; margin-top:20px; align-items:center; }
    .btn-view { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; background:#f1f5f9; color:#374151; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; }
    .btn-view:hover { background:#e2e8f0; }
    .alert { padding:12px 16px; border-radius:8px; margin:0 32px 16px; font-size:14px; font-weight:500; }
    .alert-success { background:#dcfce7; color:#166534; }
    .alert-error { background:#fee2e2; color:#991b1b; }
    @media(max-width:768px) { .fg-2,.fg-3,.skills-grid { grid-template-columns:1fr; } }
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
    <a href="/admin/pagamentos.php" class="snav-item">💳 Pagamentos</a>
    <a href="/admin/developer.php" class="snav-item active">👤 Currículo</a>
    <a href="/admin/company.php" class="snav-item">🏢 Empresa</a>
  </nav>
  <div class="sidebar-footer">
    <a href="/" class="snav-item" target="_blank">🌐 Ver site</a>
    <a href="/admin/logout.php" class="snav-item snav-logout">🚪 Sair</a>
  </div>
</aside>

<main class="admin-main">
  <header class="admin-header">
    <div>
      <h1 class="admin-title">Currículo do Desenvolvedor</h1>
      <p class="admin-subtitle">As informações preenchidas geram uma página de CV pública partilhável</p>
    </div>
    <a href="/cv.php" target="_blank" class="btn-primary-sm">👁 Ver CV público →</a>
  </header>

  <?php if ($msg_text): ?>
  <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg_text) ?></div>
  <?php endif; ?>

  <form method="POST" class="dev-form">

    <div class="form-section">
      <h3>👤 Informação Pessoal</h3>
      <div class="fg fg-2">
        <div class="form-group">
          <label>Nome completo</label>
          <input type="text" name="nome" value="<?= htmlspecialchars($e['nome']) ?>" placeholder="Ex: João Manuel">
        </div>
        <div class="form-group">
          <label>Cargo / Função</label>
          <input type="text" name="cargo" value="<?= htmlspecialchars($e['cargo']) ?>" placeholder="Ex: Desenvolvedor Full Stack">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>URL da foto de perfil</label>
          <input type="url" name="foto_url" value="<?= htmlspecialchars($e['foto_url']) ?>" placeholder="https://...">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>Bio / Resumo profissional</label>
          <textarea name="bio" placeholder="Escreva 2-4 frases sobre a sua experiência e especialidade..."><?= htmlspecialchars($e['bio']) ?></textarea>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3>📍 Contacto & Redes Sociais</h3>
      <div class="fg fg-3">
        <div class="form-group">
          <label>Localização</label>
          <input type="text" name="localizacao" value="<?= htmlspecialchars($e['localizacao']) ?>" placeholder="Ex: Luanda, Angola">
        </div>
        <div class="form-group">
          <label>WhatsApp</label>
          <input type="text" name="whatsapp" value="<?= htmlspecialchars($e['whatsapp']) ?>" placeholder="+244 9XX XXX XXX">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($e['email']) ?>" placeholder="email@exemplo.com">
        </div>
        <div class="form-group">
          <label>LinkedIn (URL)</label>
          <input type="url" name="linkedin" value="<?= htmlspecialchars($e['linkedin']) ?>" placeholder="https://linkedin.com/in/...">
        </div>
        <div class="form-group">
          <label>GitHub (URL)</label>
          <input type="url" name="github" value="<?= htmlspecialchars($e['github']) ?>" placeholder="https://github.com/...">
        </div>
      </div>
    </div>

    <div class="form-section">
      <h3>🛠 Competências / Skills</h3>
      <p style="font-size:13px;color:#94a3b8;margin-bottom:14px;">Preencha as que usar. Campos vazios são ignorados.</p>
      <div class="skills-grid">
        <?php for ($i = 0; $i < 8; $i++): ?>
        <div class="form-group">
          <label>Skill <?= $i+1 ?></label>
          <input type="text" name="skills[]" value="<?= htmlspecialchars($skills_padded[$i] ?? '') ?>" placeholder="Ex: PHP">
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <div class="form-section">
      <h3>💼 Experiência Profissional</h3>
      <?php foreach ($exps_padded as $i => $exp): ?>
      <div class="exp-entry">
        <div class="entry-num">Experiência <?= $i+1 ?></div>
        <div class="fg fg-3">
          <div class="form-group">
            <label>Cargo</label>
            <input type="text" name="exp_cargo[]" value="<?= htmlspecialchars($exp['cargo']) ?>" placeholder="Ex: Dev Backend">
          </div>
          <div class="form-group">
            <label>Empresa</label>
            <input type="text" name="exp_empresa[]" value="<?= htmlspecialchars($exp['empresa']) ?>" placeholder="Ex: TechLuanda">
          </div>
          <div class="form-group">
            <label>Período</label>
            <input type="text" name="exp_periodo[]" value="<?= htmlspecialchars($exp['periodo']) ?>" placeholder="Ex: 2022 – 2024">
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label>Descrição breve</label>
            <input type="text" name="exp_descricao[]" value="<?= htmlspecialchars($exp['descricao']) ?>" placeholder="O que fez nesta posição...">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="form-section">
      <h3>🚀 Projectos</h3>
      <?php foreach ($projs_padded as $i => $proj): ?>
      <div class="proj-entry">
        <div class="entry-num">Projecto <?= $i+1 ?></div>
        <div class="fg fg-3">
          <div class="form-group">
            <label>Nome</label>
            <input type="text" name="proj_nome[]" value="<?= htmlspecialchars($proj['nome']) ?>" placeholder="Ex: Super Escola">
          </div>
          <div class="form-group">
            <label>URL (opcional)</label>
            <input type="url" name="proj_url[]" value="<?= htmlspecialchars($proj['url']) ?>" placeholder="https://...">
          </div>
          <div class="form-group">
            <label>Descrição</label>
            <input type="text" name="proj_descricao[]" value="<?= htmlspecialchars($proj['descricao']) ?>" placeholder="Uma frase sobre o projecto">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="fp-actions">
      <button type="submit" class="btn-primary-sm">💾 Guardar currículo</button>
      <a href="/cv.php" target="_blank" class="btn-view">👁 Ver CV público →</a>
    </div>
  </form>
</main>
</body>
</html>
