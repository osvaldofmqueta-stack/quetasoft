<?php
session_start();
if (empty($_SESSION['admin_logged'])) { header('Location: /admin/login.php'); exit; }
require_once '../../database/init.php';
$db = getDb();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        $titulo    = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'Outros');
        $resumo    = trim($_POST['resumo'] ?? '');
        $intro     = trim($_POST['intro'] ?? '');
        $pontos_raw = array_values(array_filter(array_map('trim', (array)($_POST['pontos'] ?? []))));
        $pontos_json = json_encode($pontos_raw, JSON_UNESCAPED_UNICODE);
        $autor     = trim($_POST['autor'] ?? 'Equipa Super Escola');
        $ativo     = isset($_POST['ativo']) ? 1 : 0;
        $media_type = $_POST['media_type'] ?? 'imagem';
        $imagem    = ($media_type === 'imagem') ? trim($_POST['imagem_url'] ?? '') : '';
        $video     = ($media_type === 'video')  ? trim($_POST['video_url'] ?? '') : '';

        // Build backward-compat conteudo HTML
        $conteudo = '';
        if ($intro) {
            foreach (explode("\n", $intro) as $para) {
                $p = trim($para);
                if ($p) $conteudo .= '<p>' . htmlspecialchars($p) . '</p>';
            }
        }
        if (!empty($pontos_raw)) {
            $conteudo .= '<ul>';
            foreach ($pontos_raw as $pt) $conteudo .= '<li>' . htmlspecialchars($pt) . '</li>';
            $conteudo .= '</ul>';
        }

        if (!$titulo) {
            $msg = 'error|O título é obrigatório.';
        } else {
            if ($action === 'create') {
                $db->prepare("INSERT INTO posts (titulo,categoria,resumo,conteudo,intro,pontos,imagem_url,video_url,autor,ativo) VALUES (?,?,?,?,?,?,?,?,?,?)")
                   ->execute([$titulo,$categoria,$resumo,$conteudo,$intro,$pontos_json,$imagem,$video,$autor,$ativo]);
                $msg = 'success|Artigo publicado com sucesso!';
            } else {
                $id = (int)$_POST['id'];
                $db->prepare("UPDATE posts SET titulo=?,categoria=?,resumo=?,conteudo=?,intro=?,pontos=?,imagem_url=?,video_url=?,autor=?,ativo=? WHERE id=?")
                   ->execute([$titulo,$categoria,$resumo,$conteudo,$intro,$pontos_json,$imagem,$video,$autor,$ativo,$id]);
                $msg = 'success|Artigo actualizado com sucesso!';
            }
        }
    }

    if ($action === 'delete') {
        $db->prepare("DELETE FROM posts WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = 'success|Artigo removido.';
    }

    if ($action === 'toggle') {
        $db->prepare("UPDATE posts SET ativo = 1 - ativo WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: /admin/posts.php'); exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM posts WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}

$posts = $db->query("SELECT * FROM posts ORDER BY publicado_em DESC")->fetchAll();
$cats = ['Finanças','Gestão Escolar','Pedagógico','Tecnologia Educacional','Vendas','Outros'];

// Prepare edit values
$e_titulo    = $edit['titulo'] ?? '';
$e_categoria = $edit['categoria'] ?? 'Outros';
$e_resumo    = $edit['resumo'] ?? '';
$e_intro     = $edit['intro'] ?? '';
$e_pontos    = json_decode($edit['pontos'] ?? '[]', true) ?: [];
$e_pontos    = array_pad($e_pontos, 5, '');
$e_autor     = $edit['autor'] ?? 'Equipa Super Escola';
$e_ativo     = $edit['ativo'] ?? 1;
$e_imagem    = $edit['imagem_url'] ?? '';
$e_video     = $edit['video_url'] ?? '';
$e_media     = !empty($e_video) ? 'video' : 'imagem';

[$msg_type, $msg_text] = $msg ? explode('|', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Artigos — Super Escola Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .posts-grid { display:flex; flex-direction:column; gap:10px; padding:0 32px 32px; }
    .post-row { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:14px; }
    .post-thumb { width:72px; height:50px; border-radius:8px; object-fit:cover; flex-shrink:0; background:#f1f5f9; }
    .post-thumb-ph { width:72px; height:50px; border-radius:8px; background:linear-gradient(135deg,#4f46e5,#818cf8); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:18px; }
    .post-info { flex:1; min-width:0; }
    .post-row-title { font-weight:700; font-size:14px; color:#1e293b; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .post-row-meta { font-size:12px; color:#94a3b8; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .post-row-actions { display:flex; gap:6px; flex-shrink:0; }
    .cat-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; background:rgba(79,70,229,.1); color:#4f46e5; }
    .pub-badge { font-size:10px; background:#dcfce7; color:#16a34a; padding:2px 8px; border-radius:20px; }
    .unp-badge { font-size:10px; background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:20px; }

    /* Form styles */
    .form-panel { background:#fff; border:1px solid #e2e8f0; border-radius:12px; margin:0 32px 24px; overflow:hidden; }
    .fp-header { padding:18px 24px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
    .fp-header h3 { font-size:16px; font-weight:700; }
    .fp-body { padding:24px; display:flex; flex-direction:column; gap:20px; }
    .fp-section { }
    .fp-section-title { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.6px; margin-bottom:12px; }
    .fp-grid { display:grid; gap:14px; }
    .fp-grid-2 { grid-template-columns:1fr 1fr; }
    .fp-grid-3 { grid-template-columns:2fr 1fr 1fr; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; }
    .form-group small { display:block; font-size:11px; color:#94a3b8; margin-top:4px; }
    .form-group input, .form-group textarea, .form-group select { width:100%; padding:9px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; font-family:inherit; box-sizing:border-box; transition:0.15s; background:#fff; }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline:none; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.08); }
    .form-group textarea { resize:vertical; min-height:80px; }
    .pontos-list { display:flex; flex-direction:column; gap:8px; }
    .ponto-row { display:flex; align-items:center; gap:10px; }
    .ponto-num { font-size:13px; font-weight:700; color:#4f46e5; width:20px; flex-shrink:0; text-align:center; }
    .ponto-row input { flex:1; }
    /* Media toggle */
    .media-toggle { display:flex; gap:12px; margin-bottom:16px; }
    .media-opt { display:flex; align-items:center; gap:8px; cursor:pointer; padding:10px 16px; border-radius:10px; border:1.5px solid #e2e8f0; flex:1; font-size:14px; font-weight:600; color:#64748b; transition:0.15s; }
    .media-opt input { display:none; }
    .media-opt.selected { border-color:#4f46e5; background:rgba(79,70,229,.06); color:#4f46e5; }
    .media-field { display:none; }
    .media-field.active { display:block; }
    .img-preview { margin-top:8px; border-radius:8px; max-height:160px; object-fit:cover; width:100%; display:none; }
    .fp-footer { padding:16px 24px; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
    .fp-footer-left { display:flex; align-items:center; gap:10px; }
    .fp-footer-right { display:flex; gap:10px; }
    .ativo-toggle { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:500; color:#374151; cursor:pointer; }
    .ativo-toggle input { width:16px; height:16px; }
    .alert { padding:12px 16px; border-radius:8px; margin:0 32px 16px; font-size:14px; font-weight:500; }
    .alert-success { background:#dcfce7; color:#166534; }
    .alert-error { background:#fee2e2; color:#991b1b; }
    @media(max-width:768px) { .fp-grid-2,.fp-grid-3 { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">🎓 <strong>Super</strong>Escola</div>
  <nav class="sidebar-nav">
    <a href="/admin/index.php" class="snav-item">📋 Pedidos / Leads</a>
    <a href="/admin/stats.php" class="snav-item">📊 Estatísticas</a>
    <a href="/admin/schools.php" class="snav-item">🏫 Escolas & Testemunhos</a>
    <a href="/admin/posts.php" class="snav-item active">📝 Artigos do Blog</a>
    <a href="/admin/pagamentos.php" class="snav-item">💳 Pagamentos</a>
    <a href="/admin/developer.php" class="snav-item">👤 Currículo</a>
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
      <h1 class="admin-title">Artigos do Blog</h1>
      <p class="admin-subtitle">Crie e gira os artigos que aparecem no site</p>
    </div>
    <?php if (!$edit && !isset($_GET['new'])): ?>
    <a href="/admin/posts.php?new=1" class="btn-primary-sm">+ Novo artigo</a>
    <?php endif; ?>
  </header>

  <?php if ($msg_text): ?>
  <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg_text) ?></div>
  <?php endif; ?>

  <?php if (isset($_GET['new']) || $edit): ?>
  <div class="form-panel">
    <div class="fp-header">
      <h3><?= $edit ? '✏️ Editar artigo' : '✍️ Novo artigo' ?></h3>
      <a href="/admin/posts.php" style="font-size:13px;color:#94a3b8;text-decoration:none;">← Cancelar</a>
    </div>

    <form method="POST">
      <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>

      <div class="fp-body">

        <!-- Info básica -->
        <div class="fp-section">
          <div class="fp-section-title">Informação básica</div>
          <div class="fp-grid fp-grid-3">
            <div class="form-group" style="grid-column:1">
              <label>Título do artigo *</label>
              <input type="text" name="titulo" value="<?= htmlspecialchars($e_titulo) ?>" placeholder="Ex: 5 formas de reduzir a inadimplência" required>
            </div>
            <div class="form-group">
              <label>Categoria</label>
              <select name="categoria">
                <?php foreach ($cats as $c): ?>
                <option value="<?= $c ?>" <?= $e_categoria === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Autor</label>
              <input type="text" name="autor" value="<?= htmlspecialchars($e_autor) ?>">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
              <label>Resumo <span style="font-weight:400;color:#94a3b8">— aparece no cartão do blog</span></label>
              <input type="text" name="resumo" value="<?= htmlspecialchars($e_resumo) ?>" placeholder="Uma frase que resume o tema do artigo (max. 160 caracteres)" maxlength="180">
            </div>
          </div>
        </div>

        <!-- Conteúdo -->
        <div class="fp-section">
          <div class="fp-section-title">Conteúdo do artigo</div>
          <div class="fp-grid">
            <div class="form-group">
              <label>Introdução <span style="font-weight:400;color:#94a3b8">— parágrafo de abertura (2-4 frases)</span></label>
              <textarea name="intro" placeholder="Escreva uma introdução directa sobre o tema. Seja preciso e objectivo."><?= htmlspecialchars($e_intro) ?></textarea>
            </div>
          </div>
          <div class="fp-section-title" style="margin-top:16px;">Pontos principais <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#94a3b8;font-size:12px;">— aparecem como lista numerada no artigo</span></div>
          <div class="pontos-list">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="ponto-row">
              <span class="ponto-num"><?= $i+1 ?></span>
              <div class="form-group" style="margin:0;flex:1;">
                <input type="text" name="pontos[]" value="<?= htmlspecialchars($e_pontos[$i] ?? '') ?>" placeholder="<?= ['Apresente o valor principal', 'Segunda ideia concreta', 'Terceira dica ou passo', 'Quarto argumento', 'Conclusão ou chamada à acção'][$i] ?>">
              </div>
            </div>
            <?php endfor; ?>
          </div>
        </div>

        <!-- Média -->
        <div class="fp-section">
          <div class="fp-section-title">Média do artigo</div>
          <div class="media-toggle">
            <label class="media-opt <?= $e_media === 'imagem' ? 'selected' : '' ?>" id="optImagem">
              <input type="radio" name="media_type" value="imagem" <?= $e_media === 'imagem' ? 'checked' : '' ?>>
              🖼 Imagem de capa
            </label>
            <label class="media-opt <?= $e_media === 'video' ? 'selected' : '' ?>" id="optVideo">
              <input type="radio" name="media_type" value="video" <?= $e_media === 'video' ? 'checked' : '' ?>>
              ▶️ Vídeo do YouTube
            </label>
          </div>

          <div class="media-field <?= $e_media === 'imagem' ? 'active' : '' ?>" id="mediaImagem">
            <div class="form-group">
              <label>URL da imagem</label>
              <input type="url" name="imagem_url" id="imgUrlInput" value="<?= htmlspecialchars($e_imagem) ?>" placeholder="https://images.unsplash.com/...">
              <small>Cole o endereço completo de uma imagem (JPG, PNG, WebP). Recomendado: 1200×630 px.</small>
              <img id="imgPreview" src="<?= htmlspecialchars($e_imagem) ?>" class="img-preview" style="<?= $e_imagem ? 'display:block;' : '' ?>">
            </div>
          </div>

          <div class="media-field <?= $e_media === 'video' ? 'active' : '' ?>" id="mediaVideo">
            <div class="form-group">
              <label>URL do vídeo YouTube</label>
              <input type="url" name="video_url" value="<?= htmlspecialchars($e_video) ?>" placeholder="https://www.youtube.com/watch?v=XXXXXXXXXX">
              <small>Cole o link da página do vídeo no YouTube. O sistema incorpora automaticamente o vídeo no artigo.</small>
            </div>
          </div>
        </div>

      </div><!-- fp-body -->

      <div class="fp-footer">
        <div class="fp-footer-left">
          <label class="ativo-toggle">
            <input type="checkbox" name="ativo" value="1" <?= $e_ativo ? 'checked' : '' ?>>
            Publicar no site
          </label>
        </div>
        <div class="fp-footer-right">
          <a href="/admin/posts.php" style="padding:10px 18px;background:#f1f5f9;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Cancelar</a>
          <button type="submit" class="btn-primary-sm"><?= $edit ? '💾 Guardar' : '🚀 Publicar' ?></button>
        </div>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="posts-grid">
    <?php if (empty($posts)): ?>
    <div class="empty-state"><div class="empty-icon">📝</div><h3>Nenhum artigo</h3><p>Clique em "+ Novo artigo".</p></div>
    <?php else: ?>
    <?php foreach ($posts as $p): ?>
    <div class="post-row">
      <?php if ($p['imagem_url']): ?>
        <img src="<?= htmlspecialchars($p['imagem_url']) ?>" class="post-thumb" alt="">
      <?php else: ?>
        <div class="post-thumb-ph">📝</div>
      <?php endif; ?>
      <div class="post-info">
        <div class="post-row-title"><?= htmlspecialchars($p['titulo']) ?></div>
        <div class="post-row-meta">
          <span class="cat-badge"><?= htmlspecialchars($p['categoria']) ?></span>
          <span><?= date('d/m/Y', strtotime($p['publicado_em'])) ?></span>
          <span class="<?= $p['ativo'] ? 'pub-badge' : 'unp-badge' ?>"><?= $p['ativo'] ? '✓ Publicado' : '✕ Oculto' ?></span>
          <a href="/post.php?id=<?= $p['id'] ?>" target="_blank" style="font-size:12px;color:#4f46e5;text-decoration:none;">👁 Ver →</a>
        </div>
      </div>
      <div class="post-row-actions">
        <a href="/admin/posts.php?edit=<?= $p['id'] ?>" class="abtn" title="Editar">✏️</a>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button type="submit" class="abtn" title="<?= $p['ativo'] ? 'Ocultar' : 'Publicar' ?>"><?= $p['ativo'] ? '👁' : '🔵' ?></button>
        </form>
        <form method="POST" style="display:inline;" onsubmit="return confirm('Apagar este artigo?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button type="submit" class="abtn abtn-red" title="Apagar">🗑</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<script>
// Media toggle
const opts = document.querySelectorAll('.media-opt');
const fields = { imagem: document.getElementById('mediaImagem'), video: document.getElementById('mediaVideo') };
opts.forEach(opt => {
  opt.addEventListener('click', () => {
    opts.forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    const val = opt.querySelector('input').value;
    Object.entries(fields).forEach(([k, el]) => {
      el.classList.toggle('active', k === val);
    });
  });
});
// Image preview
const imgInput = document.getElementById('imgUrlInput');
const imgPreview = document.getElementById('imgPreview');
if (imgInput && imgPreview) {
  imgInput.addEventListener('input', () => {
    if (imgInput.value) {
      imgPreview.src = imgInput.value;
      imgPreview.style.display = 'block';
      imgPreview.onerror = () => { imgPreview.style.display = 'none'; };
    } else {
      imgPreview.style.display = 'none';
    }
  });
}
</script>
</body>
</html>
