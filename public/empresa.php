<?php
require_once '../database/init.php';
$db = getDb();
$co = getSetting($db, 'company', []);
$nome = $co['nome'] ?? '';
$empty = empty($nome);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $nome ? htmlspecialchars($nome) . ' — Empresa' : 'Empresa — Super Escola' ?></title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <style>
    .empresa-page{padding-top:80px;}
    .emp-hero{padding:72px 0 56px;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);text-align:center;color:#fff;}
    .emp-logo{width:80px;height:80px;border-radius:16px;margin:0 auto 16px;object-fit:contain;background:#fff;padding:8px;}
    .emp-logo-ph{width:80px;height:80px;border-radius:16px;margin:0 auto 16px;background:linear-gradient(135deg,#4f46e5,#818cf8);display:flex;align-items:center;justify-content:center;font-size:36px;}
    .emp-hero h1{font-size:36px;font-weight:900;margin-bottom:6px;}
    .emp-slogan{font-size:18px;opacity:0.75;margin-bottom:24px;}
    .emp-ano{display:inline-block;padding:6px 16px;background:rgba(255,255,255,.1);border-radius:20px;font-size:13px;font-weight:600;}
    .emp-container{max-width:860px;margin:0 auto;padding:56px 24px 80px;}
    .emp-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:20px;}
    .emp-section-title{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;}
    .emp-desc{font-size:16px;line-height:1.8;color:#374151;}
    .emp-mv-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .emp-mv-item{background:#f8fafc;border-radius:12px;padding:20px;border:1.5px solid #e2e8f0;}
    .emp-mv-label{font-size:11px;font-weight:700;color:#4f46e5;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;}
    .emp-mv-text{font-size:14px;color:#374151;line-height:1.7;}
    .emp-values{display:flex;flex-wrap:wrap;gap:10px;}
    .emp-value-pill{padding:8px 18px;background:rgba(79,70,229,.08);color:#4f46e5;border-radius:8px;font-size:13px;font-weight:600;border:1px solid rgba(79,70,229,.15);}
    .emp-contact-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;}
    .emp-contact-item{display:flex;gap:12px;align-items:flex-start;}
    .emp-contact-icon{font-size:20px;flex-shrink:0;margin-top:2px;}
    .emp-contact-label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:3px;}
    .emp-contact-value{font-size:14px;font-weight:500;color:#1e293b;}
    .emp-social{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;}
    .emp-social a{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;transition:.15s;}
    .emp-social a:hover{border-color:#4f46e5;color:#4f46e5;}
    .emp-empty{text-align:center;padding:80px 24px;color:#94a3b8;}
    @media(max-width:640px){.emp-mv-grid{grid-template-columns:1fr;}}
  </style>
</head>
<body>

<header class="header scrolled" id="header">
  <div class="container header-inner">
    <a href="/" class="logo"><span class="logo-icon">🎓</span><span class="logo-text">Super<strong>Escola</strong></span></a>
    <nav class="nav" id="nav">
      <a href="/#funcionalidades" class="nav-link">Funcionalidades</a>
      <a href="/#segmentos" class="nav-link">Segmentos</a>
      <a href="/#artigos" class="nav-link">Artigos</a>
      <a href="/empresa.php" class="nav-link">Empresa</a>
    </nav>
    <div class="header-actions">
      <a href="https://wa.me/244926219731" class="btn btn-outline" target="_blank">Fale Connosco</a>
      <a href="https://wa.me/244926219731?text=Olá!+Quero+uma+demonstração." class="btn btn-primary" target="_blank">Pedir Demonstração</a>
    </div>
    <button class="menu-toggle" id="menuToggle" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</header>

<main class="empresa-page">

  <?php if ($empty): ?>
  <div class="emp-empty" style="padding-top:120px;">
    <div style="font-size:60px;margin-bottom:16px;">🏢</div>
    <h2 style="font-size:22px;color:#1e293b;margin-bottom:8px;">Perfil da empresa ainda não configurado</h2>
    <p>Aceda ao painel de administração para preencher os dados da empresa.</p>
    <a href="/admin/company.php" style="display:inline-block;margin-top:20px;padding:12px 24px;background:#4f46e5;color:#fff;border-radius:8px;font-weight:600;">Configurar agora →</a>
  </div>
  <?php else: ?>

  <div class="emp-hero">
    <?php if (!empty($co['logo_url'])): ?>
    <img src="<?= htmlspecialchars($co['logo_url']) ?>" class="emp-logo" alt="<?= htmlspecialchars($co['nome']) ?>">
    <?php else: ?>
    <div class="emp-logo-ph">🎓</div>
    <?php endif; ?>
    <h1><?= htmlspecialchars($co['nome']) ?></h1>
    <?php if (!empty($co['slogan'])): ?>
    <div class="emp-slogan"><?= htmlspecialchars($co['slogan']) ?></div>
    <?php endif; ?>
    <?php if (!empty($co['ano_fundacao'])): ?>
    <span class="emp-ano">Fundada em <?= htmlspecialchars($co['ano_fundacao']) ?></span>
    <?php endif; ?>
  </div>

  <div class="emp-container">

    <?php if (!empty($co['descricao'])): ?>
    <div class="emp-card">
      <div class="emp-section-title">Sobre nós</div>
      <p class="emp-desc"><?= nl2br(htmlspecialchars($co['descricao'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($co['missao']) || !empty($co['visao'])): ?>
    <div class="emp-card">
      <div class="emp-section-title">Missão & Visão</div>
      <div class="emp-mv-grid">
        <?php if (!empty($co['missao'])): ?>
        <div class="emp-mv-item">
          <div class="emp-mv-label">🎯 Missão</div>
          <div class="emp-mv-text"><?= nl2br(htmlspecialchars($co['missao'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($co['visao'])): ?>
        <div class="emp-mv-item">
          <div class="emp-mv-label">🔭 Visão</div>
          <div class="emp-mv-text"><?= nl2br(htmlspecialchars($co['visao'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($co['valores'])): ?>
    <div class="emp-card">
      <div class="emp-section-title">Valores</div>
      <div class="emp-values">
        <?php foreach (array_filter(array_map('trim', explode(',', $co['valores']))) as $v): ?>
        <span class="emp-value-pill"><?= htmlspecialchars($v) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="emp-card">
      <div class="emp-section-title">Contactos</div>
      <div class="emp-contact-grid">
        <?php if (!empty($co['telefone'])): ?>
        <div class="emp-contact-item">
          <span class="emp-contact-icon">📞</span>
          <div><div class="emp-contact-label">Telefone</div><div class="emp-contact-value"><a href="tel:<?= htmlspecialchars($co['telefone']) ?>"><?= htmlspecialchars($co['telefone']) ?></a></div></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($co['email'])): ?>
        <div class="emp-contact-item">
          <span class="emp-contact-icon">📧</span>
          <div><div class="emp-contact-label">Email</div><div class="emp-contact-value"><a href="mailto:<?= htmlspecialchars($co['email']) ?>"><?= htmlspecialchars($co['email']) ?></a></div></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($co['morada'])): ?>
        <div class="emp-contact-item">
          <span class="emp-contact-icon">📍</span>
          <div><div class="emp-contact-label">Localização</div><div class="emp-contact-value"><?= htmlspecialchars($co['morada']) ?></div></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($co['website'])): ?>
        <div class="emp-contact-item">
          <span class="emp-contact-icon">🌐</span>
          <div><div class="emp-contact-label">Website</div><div class="emp-contact-value"><a href="<?= htmlspecialchars($co['website']) ?>" target="_blank"><?= htmlspecialchars($co['website']) ?></a></div></div>
        </div>
        <?php endif; ?>
      </div>
      <?php $socials = array_filter(['Facebook' => $co['facebook'] ?? '', 'Instagram' => $co['instagram'] ?? '', 'LinkedIn' => $co['linkedin'] ?? '']); ?>
      <?php if (!empty($socials)): ?>
      <div class="emp-social" style="margin-top:20px;">
        <?php foreach ($socials as $name => $url): ?>
        <a href="<?= htmlspecialchars($url) ?>" target="_blank"><?= $name ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
  <?php endif; ?>

</main>

<?php include 'components/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
