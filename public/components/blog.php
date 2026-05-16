<?php
require_once __DIR__ . '/../../database/init.php';
$db = getDb();
$posts = $db->query("SELECT * FROM posts WHERE ativo=1 ORDER BY publicado_em DESC LIMIT 9")->fetchAll();
$categorias = ['Todos', 'Finanças', 'Gestão Escolar', 'Pedagógico', 'Tecnologia Educacional', 'Vendas', 'Outros'];
?>
<section class="blog" id="artigos">
  <div class="container">
    <div class="section-header">
      <div class="section-badge">Artigos</div>
      <h2 class="section-title">Conhecimento para gerir melhor a sua escola</h2>
      <p class="section-desc">Dicas práticas, estratégias e novidades sobre gestão escolar, finanças e tecnologia educacional.</p>
    </div>

    <div class="blog-filters">
      <?php foreach ($categorias as $cat): ?>
      <button class="blog-filter-btn <?= $cat === 'Todos' ? 'active' : '' ?>" data-cat="<?= htmlspecialchars($cat) ?>">
        <?= htmlspecialchars($cat) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="blog-grid" id="blogGrid">
      <?php foreach ($posts as $post): ?>
      <article class="blog-card" data-cat="<?= htmlspecialchars($post['categoria']) ?>">
        <a href="/post.php?id=<?= $post['id'] ?>" class="blog-card-img-wrap">
          <?php if ($post['imagem_url']): ?>
            <img src="<?= htmlspecialchars($post['imagem_url']) ?>" alt="<?= htmlspecialchars($post['titulo']) ?>" loading="lazy">
          <?php else: ?>
            <div class="blog-card-img-placeholder"></div>
          <?php endif; ?>
          <span class="blog-card-badge"><?= htmlspecialchars($post['categoria']) ?></span>
        </a>
        <div class="blog-card-body">
          <h3 class="blog-card-title">
            <a href="/post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['titulo']) ?></a>
          </h3>
          <p class="blog-card-excerpt"><?= htmlspecialchars(mb_substr($post['resumo'] ?? '', 0, 130)) ?>...</p>
          <a href="/post.php?id=<?= $post['id'] ?>" class="blog-card-link">Leia o post completo →</a>
          <div class="blog-card-meta">
            <span>👤 <?= htmlspecialchars($post['autor']) ?></span>
            <span>🗓 <?= date('d M Y', strtotime($post['publicado_em'])) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if (empty($posts)): ?>
    <div style="text-align:center;padding:80px 0;color:#94a3b8;">
      <div style="font-size:48px;margin-bottom:16px;">📝</div>
      <p>Em breve novos artigos. Fique atento!</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function() {
  const btns = document.querySelectorAll('.blog-filter-btn');
  const cards = document.querySelectorAll('#blogGrid .blog-card');
  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const cat = btn.dataset.cat;
      cards.forEach(card => {
        if (cat === 'Todos' || card.dataset.cat === cat) {
          card.classList.remove('hidden');
        } else {
          card.classList.add('hidden');
        }
      });
    });
  });
})();
</script>
