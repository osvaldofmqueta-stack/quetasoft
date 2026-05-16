<?php
require_once __DIR__ . '/../../database/init.php';
$db_t = getDb();
$escolas = $db_t->query("SELECT * FROM escolas WHERE ativo=1 ORDER BY ordem ASC, id ASC")->fetchAll();
?>
<section class="testimonials" id="depoimentos">
  <div class="container">
    <div class="section-header">
      <div class="section-badge">Escolas que já aderiram</div>
      <h2 class="section-title">O que dizem os nossos <span class="gradient-text">diretores</span></h2>
      <p class="section-desc">Mais de 50 escolas em Angola já confiam no Super Escola para gerir o seu dia-a-dia académico.</p>
    </div>

    <!-- School logos strip -->
    <div class="school-logos-strip">
      <?php foreach ($escolas as $e): ?>
      <div class="school-logo-item">
        <div class="school-logo-av" style="background:<?= htmlspecialchars($e['cor_avatar']) ?>;">
          <?php if (!empty($e['foto_url'])): ?>
            <img src="<?= htmlspecialchars($e['foto_url']) ?>" alt="<?= htmlspecialchars($e['nome_escola']) ?>">
          <?php else: ?>
            <?= htmlspecialchars($e['iniciais']) ?>
          <?php endif; ?>
        </div>
        <span class="school-logo-name"><?= htmlspecialchars($e['nome_escola']) ?></span>
        <span class="school-logo-city"><?= htmlspecialchars($e['cidade']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Testimonials grid -->
    <div class="testimonials-grid">
      <?php foreach ($escolas as $i => $e): ?>
      <div class="testimonial-card <?= $i === 1 ? 'testimonial-card--featured' : '' ?>">
        <div class="testimonial-stars">
          <?= str_repeat('★', (int)$e['estrelas']) ?><?= str_repeat('☆', 5 - (int)$e['estrelas']) ?>
        </div>
        <p class="testimonial-text"><?= htmlspecialchars($e['depoimento']) ?></p>
        <div class="testimonial-author">
          <div class="testimonial-avatar-wrap">
            <?php if (!empty($e['foto_url'])): ?>
              <img class="testimonial-photo" src="<?= htmlspecialchars($e['foto_url']) ?>" alt="<?= htmlspecialchars($e['nome_diretor']) ?>">
            <?php else: ?>
              <div class="testimonial-avatar" style="background:<?= htmlspecialchars($e['cor_avatar']) ?>;">
                <?= htmlspecialchars($e['iniciais']) ?>
              </div>
            <?php endif; ?>
          </div>
          <div>
            <strong><?= htmlspecialchars($e['nome_diretor']) ?></strong>
            <span><?= htmlspecialchars($e['cargo']) ?> — <?= htmlspecialchars($e['nome_escola']) ?>, <?= htmlspecialchars($e['cidade']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="testimonials-cta">
      <p>A sua escola ainda não está aqui? <a href="#pedido-gratuito" class="link-highlight">Junte-se a nós →</a></p>
    </div>
  </div>
</section>
