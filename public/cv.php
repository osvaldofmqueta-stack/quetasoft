<?php
require_once '../database/init.php';
$db = getDb();
$dev = getSetting($db, 'developer', []);
$nome = $dev['nome'] ?? '';
$empty = empty($nome);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $nome ? htmlspecialchars($nome) . ' — Currículo' : 'Currículo — Super Escola' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{--primary:#4f46e5;--dark:#0f172a;--gray:#64748b;--border:#e2e8f0;--bg:#f8fafc;}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--dark);line-height:1.6;}
    a{text-decoration:none;color:inherit;}
    .cv-wrap{max-width:860px;margin:0 auto;padding:32px 20px 80px;}

    /* Header */
    .cv-topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;padding-bottom:16px;border-bottom:1px solid var(--border);}
    .cv-logo{font-size:15px;font-weight:700;color:var(--primary);}
    .cv-print-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;}
    .cv-print-btn:hover{background:#3730a3;}

    /* Hero */
    .cv-hero{background:#fff;border-radius:16px;padding:32px;display:flex;gap:28px;align-items:flex-start;border:1px solid var(--border);margin-bottom:20px;}
    .cv-photo{width:96px;height:96px;border-radius:50%;object-fit:cover;flex-shrink:0;background:linear-gradient(135deg,var(--primary),#818cf8);}
    .cv-photo-ph{width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#818cf8);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:36px;color:#fff;}
    .cv-hero-info{flex:1;}
    .cv-name{font-size:26px;font-weight:800;margin-bottom:4px;}
    .cv-role{font-size:16px;color:var(--primary);font-weight:600;margin-bottom:8px;}
    .cv-location{font-size:14px;color:var(--gray);margin-bottom:14px;}
    .cv-contacts{display:flex;flex-wrap:wrap;gap:10px;}
    .cv-contact-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;border:1.5px solid var(--border);font-size:13px;font-weight:500;color:var(--dark);transition:0.15s;}
    .cv-contact-btn:hover{border-color:var(--primary);color:var(--primary);}

    /* Cards */
    .cv-card{background:#fff;border-radius:12px;padding:24px;border:1px solid var(--border);margin-bottom:16px;}
    .cv-section-title{font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border);}

    /* Bio */
    .cv-bio{font-size:15px;color:#374151;line-height:1.75;}

    /* Skills */
    .cv-skills{display:flex;flex-wrap:wrap;gap:8px;}
    .cv-skill{padding:6px 14px;background:rgba(79,70,229,.08);color:var(--primary);border-radius:6px;font-size:13px;font-weight:600;border:1px solid rgba(79,70,229,.15);}

    /* Experience */
    .cv-exp-item{display:flex;gap:16px;padding-bottom:20px;border-bottom:1px solid var(--border);margin-bottom:20px;}
    .cv-exp-item:last-child{border-bottom:none;padding-bottom:0;margin-bottom:0;}
    .cv-exp-dot{width:10px;height:10px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:6px;}
    .cv-exp-info{flex:1;}
    .cv-exp-role{font-size:15px;font-weight:700;}
    .cv-exp-company{font-size:14px;color:var(--primary);font-weight:500;}
    .cv-exp-period{font-size:12px;color:var(--gray);margin-top:2px;}
    .cv-exp-desc{font-size:14px;color:#374151;margin-top:6px;}

    /* Projects */
    .cv-projects{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;}
    .cv-proj-card{background:var(--bg);border:1.5px solid var(--border);border-radius:10px;padding:16px;transition:0.15s;}
    .cv-proj-card:hover{border-color:var(--primary);transform:translateY(-2px);}
    .cv-proj-name{font-size:15px;font-weight:700;margin-bottom:6px;color:var(--primary);}
    .cv-proj-desc{font-size:13px;color:var(--gray);}
    .cv-proj-link{font-size:12px;color:var(--primary);font-weight:600;margin-top:8px;display:inline-block;}

    /* Empty state */
    .cv-empty{text-align:center;padding:80px 24px;color:var(--gray);}
    .cv-empty h2{font-size:20px;margin-bottom:8px;}

    @media print {
      .cv-topbar { display: none; }
      .cv-wrap { padding: 0; }
      body { background: #fff; }
    }
    @media(max-width:600px){
      .cv-hero{flex-direction:column;gap:16px;align-items:center;text-align:center;}
      .cv-contacts{justify-content:center;}
    }
  </style>
</head>
<body>
<div class="cv-wrap">
  <div class="cv-topbar">
    <a href="/" class="cv-logo">🎓 SuperEscola</a>
    <button class="cv-print-btn" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
  </div>

  <?php if ($empty): ?>
  <div class="cv-empty">
    <div style="font-size:60px;margin-bottom:16px;">👤</div>
    <h2>Currículo ainda não configurado</h2>
    <p>Aceda ao painel de administração para preencher o seu perfil.</p>
    <a href="/admin/developer.php" style="display:inline-block;margin-top:20px;padding:10px 20px;background:var(--primary);color:#fff;border-radius:8px;font-weight:600;">Configurar agora →</a>
  </div>
  <?php else: ?>

  <!-- Hero -->
  <div class="cv-hero">
    <?php if (!empty($dev['foto_url'])): ?>
    <img src="<?= htmlspecialchars($dev['foto_url']) ?>" class="cv-photo" alt="<?= htmlspecialchars($dev['nome']) ?>">
    <?php else: ?>
    <div class="cv-photo-ph"><?= mb_strtoupper(mb_substr($dev['nome'], 0, 1)) ?></div>
    <?php endif; ?>
    <div class="cv-hero-info">
      <div class="cv-name"><?= htmlspecialchars($dev['nome']) ?></div>
      <div class="cv-role"><?= htmlspecialchars($dev['cargo'] ?? '') ?></div>
      <?php if (!empty($dev['localizacao'])): ?>
      <div class="cv-location">📍 <?= htmlspecialchars($dev['localizacao']) ?></div>
      <?php endif; ?>
      <div class="cv-contacts">
        <?php if (!empty($dev['whatsapp'])): ?>
        <a href="https://wa.me/<?= preg_replace('/\D/','',$dev['whatsapp']) ?>" target="_blank" class="cv-contact-btn">💬 <?= htmlspecialchars($dev['whatsapp']) ?></a>
        <?php endif; ?>
        <?php if (!empty($dev['email'])): ?>
        <a href="mailto:<?= htmlspecialchars($dev['email']) ?>" class="cv-contact-btn">📧 <?= htmlspecialchars($dev['email']) ?></a>
        <?php endif; ?>
        <?php if (!empty($dev['linkedin'])): ?>
        <a href="<?= htmlspecialchars($dev['linkedin']) ?>" target="_blank" class="cv-contact-btn">🔗 LinkedIn</a>
        <?php endif; ?>
        <?php if (!empty($dev['github'])): ?>
        <a href="<?= htmlspecialchars($dev['github']) ?>" target="_blank" class="cv-contact-btn">⌨ GitHub</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bio -->
  <?php if (!empty($dev['bio'])): ?>
  <div class="cv-card">
    <div class="cv-section-title">Sobre mim</div>
    <p class="cv-bio"><?= nl2br(htmlspecialchars($dev['bio'])) ?></p>
  </div>
  <?php endif; ?>

  <!-- Skills -->
  <?php $skills = array_filter($dev['skills'] ?? []); ?>
  <?php if (!empty($skills)): ?>
  <div class="cv-card">
    <div class="cv-section-title">Competências</div>
    <div class="cv-skills">
      <?php foreach ($skills as $s): ?>
      <span class="cv-skill"><?= htmlspecialchars($s) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Experience -->
  <?php $exps = array_filter($dev['experiencias'] ?? [], fn($e) => !empty($e['cargo'])); ?>
  <?php if (!empty($exps)): ?>
  <div class="cv-card">
    <div class="cv-section-title">Experiência profissional</div>
    <?php foreach ($exps as $exp): ?>
    <div class="cv-exp-item">
      <div class="cv-exp-dot"></div>
      <div class="cv-exp-info">
        <div class="cv-exp-role"><?= htmlspecialchars($exp['cargo']) ?></div>
        <div class="cv-exp-company"><?= htmlspecialchars($exp['empresa'] ?? '') ?></div>
        <?php if (!empty($exp['periodo'])): ?>
        <div class="cv-exp-period">📅 <?= htmlspecialchars($exp['periodo']) ?></div>
        <?php endif; ?>
        <?php if (!empty($exp['descricao'])): ?>
        <div class="cv-exp-desc"><?= htmlspecialchars($exp['descricao']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Projects -->
  <?php $projs = array_filter($dev['projetos'] ?? [], fn($p) => !empty($p['nome'])); ?>
  <?php if (!empty($projs)): ?>
  <div class="cv-card">
    <div class="cv-section-title">Projectos</div>
    <div class="cv-projects">
      <?php foreach ($projs as $proj): ?>
      <div class="cv-proj-card">
        <div class="cv-proj-name"><?= htmlspecialchars($proj['nome']) ?></div>
        <?php if (!empty($proj['descricao'])): ?>
        <div class="cv-proj-desc"><?= htmlspecialchars($proj['descricao']) ?></div>
        <?php endif; ?>
        <?php if (!empty($proj['url'])): ?>
        <a href="<?= htmlspecialchars($proj['url']) ?>" target="_blank" class="cv-proj-link">🔗 Ver projecto →</a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>
</div>
</body>
</html>
