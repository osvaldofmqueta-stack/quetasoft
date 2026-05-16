<?php
require_once '../database/init.php';
$db = getDb();

$id = (int)($_GET['id'] ?? 0);
$post = $db->prepare("SELECT * FROM posts WHERE id = ? AND ativo = 1");
$post->execute([$id]);
$post = $post->fetch();

if (!$post) {
    header('Location: /#artigos');
    exit;
}

$related = $db->prepare("SELECT id, titulo, imagem_url, categoria, publicado_em FROM posts WHERE ativo=1 AND id != ? AND categoria = ? ORDER BY publicado_em DESC LIMIT 3");
$related->execute([$id, $post['categoria']]);
$related = $related->fetchAll();

function getYoutubeEmbedUrl(string $url): string {
    if (empty($url)) return '';
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return isset($m[1]) ? 'https://www.youtube.com/embed/' . $m[1] : '';
}
$embedUrl = getYoutubeEmbedUrl($post['video_url'] ?? '');
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
    <button class="menu-toggle" id="menuToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<main class="post-page">

  <?php if ($post['imagem_url']): ?>
  <div class="post-hero-wrap">
    <img src="<?= htmlspecialchars($post['imagem_url']) ?>" alt="<?= htmlspecialchars($post['titulo']) ?>" class="post-hero">
    <div class="post-hero-overlay"></div>
  </div>
  <?php endif; ?>

  <div class="post-container">
    <a href="/#artigos" class="post-back">← Voltar aos artigos</a>

    <span class="post-category"><?= htmlspecialchars($post['categoria']) ?></span>
    <h1 class="post-title"><?= htmlspecialchars($post['titulo']) ?></h1>

    <div class="post-meta">
      <span>👤 <?= htmlspecialchars($post['autor']) ?></span>
      <span>🗓 <?= date('d \d\e F \d\e Y', strtotime($post['publicado_em'])) ?></span>
    </div>

    <?php if ($embedUrl): ?>
    <div class="post-video">
      <iframe src="<?= htmlspecialchars($embedUrl) ?>" title="Vídeo demonstração" allowfullscreen loading="lazy"></iframe>
    </div>
    <?php endif; ?>

    <div class="post-content">
      <?= $post['conteudo'] ?>
    </div>

    <div class="post-cta-box">
      <h3>Pronto para transformar a gestão da sua escola?</h3>
      <p>Experimente o Super Escola gratuitamente. Sem compromisso, sem cartão de crédito.</p>
      <a href="https://wa.me/244926219731?text=Olá!+Li+o+artigo+e+quero+uma+demonstração+gratuita+do+Super+Escola." target="_blank" class="btn btn-primary">💬 Pedir demonstração gratuita</a>
    </div>
  </div>

  <?php if (!empty($related)): ?>
  <div class="post-related">
    <div class="container">
      <h3 class="post-related-title">Artigos relacionados</h3>
      <div class="blog-grid">
        <?php foreach ($related as $r): ?>
        <article class="blog-card">
          <a href="/post.php?id=<?= $r['id'] ?>" class="blog-card-img-wrap">
            <?php if ($r['imagem_url']): ?>
              <img src="<?= htmlspecialchars($r['imagem_url']) ?>" alt="<?= htmlspecialchars($r['titulo']) ?>" loading="lazy">
            <?php else: ?>
              <div class="blog-card-img-placeholder"></div>
            <?php endif; ?>
            <span class="blog-card-badge"><?= htmlspecialchars($r['categoria']) ?></span>
          </a>
          <div class="blog-card-body">
            <h3 class="blog-card-title">
              <a href="/post.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['titulo']) ?></a>
            </h3>
            <a href="/post.php?id=<?= $r['id'] ?>" class="blog-card-link">Leia o post completo →</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</main>

<?php include 'components/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
