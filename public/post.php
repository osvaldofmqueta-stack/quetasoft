<?php
require_once '../database/init.php';
$db = getDb();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM posts WHERE id = ? AND ativo = 1");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) { header('Location: /#artigos'); exit; }

$related = $db->prepare("SELECT id, titulo, imagem_url, categoria, resumo FROM posts WHERE ativo=1 AND id != ? AND categoria = ? ORDER BY publicado_em DESC LIMIT 3");
$related->execute([$id, $post['categoria']]);
$related = $related->fetchAll();

function getYoutubeEmbed(string $url): string {
    if (empty($url)) return '';
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return isset($m[1]) ? 'https://www.youtube.com/embed/' . $m[1] . '?rel=0' : '';
}
$embedUrl = getYoutubeEmbed($post['video_url'] ?? '');
$has_structured = !empty(trim($post['intro'] ?? ''));
$pontos = json_decode($post['pontos'] ?? '[]', true) ?: [];
$pontos = array_filter($pontos);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($post['titulo']) ?> — Super Escola</title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr($post['resumo'] ?? '', 0, 160)) ?>">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
</head>
<body>

<header class="header scrolled" id="header">
  <div class="container header-inner">
    <a href="/" class="logo">
      <span class="logo-icon">🎓</span>
      <span class="logo-text">Super<strong>Escola</strong></span>
    </a>
    <nav class="nav" id="nav">
      <a href="/#funcionalidades" class="nav-link">Funcionalidades</a>
      <a href="/#segmentos" class="nav-link">Segmentos</a>
      <a href="/#depoimentos" class="nav-link">Depoimentos</a>
      <a href="/#artigos" class="nav-link">Artigos</a>
      <a href="/#faq" class="nav-link">FAQ</a>
    </nav>
    <div class="header-actions">
      <a href="https://wa.me/244926219731?text=Olá!+Vim+pelo+site+e+quero+saber+mais+sobre+o+Super+Escola." class="btn btn-outline" target="_blank">Fale Connosco</a>
      <a href="https://wa.me/244926219731?text=Olá!+Quero+uma+demonstração+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir Demonstração</a>
    </div>
    <button class="menu-toggle" id="menuToggle" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</header>

<main class="post-page">

  <div class="post-layout">

    <!-- Left: article -->
    <article class="post-article">

      <div class="post-breadcrumb">
        <a href="/">Início</a>
        <span>›</span>
        <a href="/#artigos">Artigos</a>
        <span>›</span>
        <span><?= htmlspecialchars($post['categoria']) ?></span>
      </div>

      <span class="post-category"><?= htmlspecialchars($post['categoria']) ?></span>
      <h1 class="post-title"><?= htmlspecialchars($post['titulo']) ?></h1>

      <div class="post-meta-bar">
        <span class="pmet">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          <?= htmlspecialchars($post['autor']) ?>
        </span>
        <span class="pmet">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <?= date('d M Y', strtotime($post['publicado_em'])) ?>
        </span>
      </div>

      <?php if ($post['imagem_url'] && !$embedUrl): ?>
      <div class="post-hero-img">
        <img src="<?= htmlspecialchars($post['imagem_url']) ?>" alt="<?= htmlspecialchars($post['titulo']) ?>">
      </div>
      <?php endif; ?>

      <?php if ($embedUrl): ?>
      <div class="post-video-wrap">
        <iframe src="<?= htmlspecialchars($embedUrl) ?>" title="Vídeo" allowfullscreen loading="lazy"></iframe>
      </div>
      <?php endif; ?>

      <?php if ($has_structured): ?>
        <p class="post-intro"><?= nl2br(htmlspecialchars($post['intro'])) ?></p>
        <?php if (!empty($pontos)): ?>
        <div class="post-points">
          <?php foreach (array_values($pontos) as $i => $pt): ?>
          <div class="post-point">
            <div class="pp-num"><?= $i + 1 ?></div>
            <div class="pp-text"><?= htmlspecialchars($pt) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      <?php else: ?>
      <div class="post-content">
        <?= $post['conteudo'] ?>
      </div>
      <?php endif; ?>

      <div class="post-cta-box">
        <div class="pcta-icon">🎓</div>
        <div class="pcta-text">
          <strong>Pronto para transformar a gestão da sua escola?</strong>
          <span>Fale connosco hoje e receba uma demonstração gratuita.</span>
        </div>
        <a href="https://wa.me/244926219731?text=Olá!+Li+um+artigo+e+quero+saber+mais+sobre+o+Super+Escola." target="_blank" class="btn btn-primary">💬 Falar no WhatsApp</a>
      </div>

    </article>

    <!-- Right: sidebar -->
    <aside class="post-sidebar">
      <?php if (!empty($related)): ?>
      <div class="ps-block">
        <h4 class="ps-title">Artigos relacionados</h4>
        <?php foreach ($related as $r): ?>
        <a href="/post.php?id=<?= $r['id'] ?>" class="ps-item">
          <?php if ($r['imagem_url']): ?>
          <img src="<?= htmlspecialchars($r['imagem_url']) ?>" alt="" class="ps-thumb">
          <?php else: ?>
          <div class="ps-thumb-ph"></div>
          <?php endif; ?>
          <div class="ps-item-info">
            <span class="ps-item-cat"><?= htmlspecialchars($r['categoria']) ?></span>
            <span class="ps-item-title"><?= htmlspecialchars($r['titulo']) ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="ps-block ps-cta-mini">
        <div style="font-size:28px;margin-bottom:10px;">🎓</div>
        <strong>Experimente grátis</strong>
        <p>Demonstração sem compromisso para a sua escola.</p>
        <a href="https://wa.me/244926219731?text=Quero+uma+demonstração+gratuita." target="_blank" class="btn btn-primary" style="width:100%;text-align:center;margin-top:14px;font-size:14px;">Pedir demonstração</a>
      </div>
    </aside>

  </div>

</main>

<?php include 'components/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
