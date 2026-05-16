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
        $conteudo  = trim($_POST['conteudo'] ?? '');
        $imagem    = trim($_POST['imagem_url'] ?? '');
        $video     = trim($_POST['video_url'] ?? '');
        $autor     = trim($_POST['autor'] ?? 'Equipa Super Escola');
        $ativo     = isset($_POST['ativo']) ? 1 : 0;

        if (!$titulo || !$conteudo) {
            $msg = 'error|Preencha o título e o conteúdo.';
        } else {
            if ($action === 'create') {
                $db->prepare("INSERT INTO posts (titulo,categoria,resumo,conteudo,imagem_url,video_url,autor,ativo) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([$titulo,$categoria,$resumo,$conteudo,$imagem,$video,$autor,$ativo]);
                $msg = 'success|Artigo publicado com sucesso!';
            } else {
                $id = (int)$_POST['id'];
                $db->prepare("UPDATE posts SET titulo=?,categoria=?,resumo=?,conteudo=?,imagem_url=?,video_url=?,autor=?,ativo=? WHERE id=?")
                   ->execute([$titulo,$categoria,$resumo,$conteudo,$imagem,$video,$autor,$ativo,$id]);
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
    .posts-grid { display: flex; flex-direction: column; gap: 12px; padding: 24px 32px 32px; }
    .post-row { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; }
    .post-thumb { width: 80px; height: 56px; border-radius: 8px; object-fit: cover; flex-shrink: 0; background: #f1f5f9; }
    .post-thumb-placeholder { width: 80px; height: 56px; border-radius: 8px; background: linear-gradient(135deg,#4f46e5,#818cf8); flex-shrink: 0; display:flex;align-items:center;justify-content:center;font-size:20px; }
    .post-info { flex: 1; min-width: 0; }
    .post-row-title { font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .post-row-meta { font-size: 12px; color: #94a3b8; display: flex; gap: 12px; flex-wrap: wrap; }
    .post-row-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .form-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin: 0 32px 24px; padding: 28px; }
    .form-panel h3 { margin-bottom: 20px; font-size: 18px; }
    .fp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
    .form-group textarea { resize: vertical; min-height: 120px; }
    .form-group textarea.tall { min-height: 300px; }
    .fp-actions { display: flex; gap: 12px; margin-top: 20px; }
    .alert { padding: 12px 16px; border-radius: 8px; margin: 0 32px 16px; font-size: 14px; font-weight: 500; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    .cat-badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; background: rgba(79,70,229,0.1); color: #4f46e5; }
    .pub-badge { font-size: 11px; background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 20px; }
    .unp-badge { font-size: 11px; background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 20px; }
    .post-preview { margin-top: 8px; }
    .post-preview a { font-size: 12px; color: #4f46e5; text-decoration: none; }
    .post-preview a:hover { text-decoration: underline; }
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
      <?php if (!$edit): ?>
      <a href="/admin/posts.php?new=1" class="btn-primary-sm">+ Novo artigo</a>
      <?php endif; ?>
    </header>

    <?php if ($msg_text): ?>
    <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg_text) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['new']) || $edit): ?>
    <div class="form-panel">
      <h3><?= $edit ? 'Editar Artigo' : 'Novo Artigo' ?></h3>
      <form method="POST">
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>

        <div class="fp-grid">
          <div class="form-group" style="grid-column:1/-1;">
            <label>Título do artigo *</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($edit['titulo'] ?? '') ?>" placeholder="Ex: 5 formas de reduzir a inadimplência" required>
          </div>
          <div class="form-group">
            <label>Categoria *</label>
            <select name="categoria">
              <?php foreach ($cats as $c): ?>
              <option value="<?= $c ?>" <?= ($edit['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Autor</label>
            <input type="text" name="autor" value="<?= htmlspecialchars($edit['autor'] ?? 'Equipa Super Escola') ?>">
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label>Resumo (aparece nos cartões do blog)</label>
            <textarea name="resumo" placeholder="Breve descrição do artigo (2-3 frases)..."><?= htmlspecialchars($edit['resumo'] ?? '') ?></textarea>
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label>Conteúdo completo * (pode usar HTML: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;)</label>
            <textarea name="conteudo" class="tall" placeholder="<p>Conteúdo do artigo...</p><h2>Subtítulo</h2><p>Mais conteúdo...</p>" required><?= htmlspecialchars($edit['conteudo'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>URL da imagem de capa</label>
            <input type="url" name="imagem_url" value="<?= htmlspecialchars($edit['imagem_url'] ?? '') ?>" placeholder="https://...">
          </div>
          <div class="form-group">
            <label>URL do vídeo YouTube (opcional)</label>
            <input type="url" name="video_url" value="<?= htmlspecialchars($edit['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
            <small style="color:#94a3b8;font-size:11px;display:block;margin-top:4px;">O vídeo aparece incorporado no artigo antes do texto.</small>
          </div>
          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="ativo" value="1" <?= ($edit['ativo'] ?? 1) ? 'checked' : '' ?>> Publicar no site
            </label>
          </div>
        </div>

        <div class="fp-actions">
          <button type="submit" class="btn-primary-sm"><?= $edit ? '💾 Guardar alterações' : '🚀 Publicar artigo' ?></button>
          <a href="/admin/posts.php" class="abtn" style="padding:8px 18px;background:#f1f5f9;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Cancelar</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="posts-grid">
      <?php if (empty($posts)): ?>
      <div class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Nenhum artigo ainda</h3>
        <p>Clique em "+ Novo artigo" para criar o primeiro.</p>
      </div>
      <?php else: ?>
      <?php foreach ($posts as $p): ?>
      <div class="post-row">
        <?php if ($p['imagem_url']): ?>
          <img src="<?= htmlspecialchars($p['imagem_url']) ?>" class="post-thumb" alt="">
        <?php else: ?>
          <div class="post-thumb-placeholder">📝</div>
        <?php endif; ?>
        <div class="post-info">
          <div class="post-row-title"><?= htmlspecialchars($p['titulo']) ?></div>
          <div class="post-row-meta">
            <span class="cat-badge"><?= htmlspecialchars($p['categoria']) ?></span>
            <span><?= date('d/m/Y', strtotime($p['publicado_em'])) ?></span>
            <span class="<?= $p['ativo'] ? 'pub-badge' : 'unp-badge' ?>"><?= $p['ativo'] ? '✓ Publicado' : '✕ Oculto' ?></span>
            <span class="post-preview"><a href="/post.php?id=<?= $p['id'] ?>" target="_blank">👁 Ver no site →</a></span>
          </div>
        </div>
        <div class="post-row-actions">
          <a href="/admin/posts.php?edit=<?= $p['id'] ?>" class="abtn" title="Editar">✏️</a>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="abtn" title="<?= $p['ativo'] ? 'Ocultar' : 'Publicar' ?>"><?= $p['ativo'] ? '👁' : '🔵' ?></button>
          </form>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Apagar este artigo permanentemente?')">
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
</body>
</html>
