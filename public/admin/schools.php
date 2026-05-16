<?php
session_start();
if (empty($_SESSION['admin_logged'])) { header('Location: /admin/login.php'); exit; }
require_once '../../database/init.php';
$db = getDb();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        $nome_escola  = trim($_POST['nome_escola'] ?? '');
        $cidade       = trim($_POST['cidade'] ?? '');
        $nome_diretor = trim($_POST['nome_diretor'] ?? '');
        $cargo        = trim($_POST['cargo'] ?? 'Diretor(a)');
        $iniciais     = mb_strtoupper(trim($_POST['iniciais'] ?? ''));
        $cor_avatar   = trim($_POST['cor_avatar'] ?? '#4f46e5');
        $foto_url     = trim($_POST['foto_url'] ?? '');
        $depoimento   = trim($_POST['depoimento'] ?? '');
        $estrelas     = (int)($_POST['estrelas'] ?? 5);
        $ativo        = isset($_POST['ativo']) ? 1 : 0;
        $ordem        = (int)($_POST['ordem'] ?? 0);

        if (!$nome_escola || !$cidade || !$nome_diretor || !$depoimento) {
            $msg = 'error|Preencha todos os campos obrigatórios.';
        } else {
            if ($action === 'create') {
                $db->prepare("INSERT INTO escolas (nome_escola,cidade,nome_diretor,cargo,iniciais,cor_avatar,foto_url,depoimento,estrelas,ativo,ordem) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                   ->execute([$nome_escola,$cidade,$nome_diretor,$cargo,$iniciais,$cor_avatar,$foto_url,$depoimento,$estrelas,$ativo,$ordem]);
                $msg = 'success|Escola adicionada com sucesso!';
            } else {
                $id = (int)$_POST['id'];
                $db->prepare("UPDATE escolas SET nome_escola=?,cidade=?,nome_diretor=?,cargo=?,iniciais=?,cor_avatar=?,foto_url=?,depoimento=?,estrelas=?,ativo=?,ordem=? WHERE id=?")
                   ->execute([$nome_escola,$cidade,$nome_diretor,$cargo,$iniciais,$cor_avatar,$foto_url,$depoimento,$estrelas,$ativo,$ordem,$id]);
                $msg = 'success|Escola actualizada com sucesso!';
            }
        }
    }

    if ($action === 'delete') {
        $db->prepare("DELETE FROM escolas WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = 'success|Escola removida.';
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE escolas SET ativo = 1 - ativo WHERE id=?")->execute([$id]);
        header('Location: /admin/schools.php'); exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $db->prepare("SELECT * FROM escolas WHERE id=?")->execute([(int)$_GET['edit']]) ? $db->query("SELECT * FROM escolas WHERE id=" . (int)$_GET['edit'])->fetch() : null;
}

$escolas = $db->query("SELECT * FROM escolas ORDER BY ordem ASC, id ASC")->fetchAll();

[$msg_type, $msg_text] = $msg ? explode('|', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerir Escolas — Super Escola Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .schools-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; padding: 24px 32px; }
    .school-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; }
    .school-card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .sc-av { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 16px; flex-shrink: 0; }
    .sc-av img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .sc-info { flex: 1; min-width: 0; }
    .sc-name { font-weight: 700; color: #1e293b; font-size: 15px; }
    .sc-director { font-size: 12px; color: #64748b; }
    .sc-city { font-size: 11px; color: #94a3b8; }
    .sc-text { font-size: 13px; color: #475569; line-height: 1.5; font-style: italic; margin-bottom: 14px; border-left: 3px solid #e2e8f0; padding-left: 10px; }
    .sc-stars { color: #f59e0b; font-size: 14px; margin-bottom: 10px; }
    .sc-actions { display: flex; gap: 8px; }
    .sc-btn { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-block; }
    .sc-btn-edit { background: #ede9fe; color: #5b21b6; }
    .sc-btn-toggle { background: #fef3c7; color: #92400e; }
    .sc-btn-del { background: #fee2e2; color: #991b1b; }
    .inactive-label { font-size: 11px; background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 20px; margin-left: auto; }
    .active-label { font-size: 11px; background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 20px; margin-left: auto; }
    .form-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin: 0 32px 24px; padding: 28px; }
    .form-panel h3 { margin-bottom: 20px; font-size: 18px; }
    .fp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .fp-grid .form-group { margin-bottom: 0; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
    .form-group textarea { resize: vertical; min-height: 90px; }
    .fp-actions { display: flex; gap: 12px; margin-top: 20px; }
    .alert { padding: 12px 16px; border-radius: 8px; margin: 0 32px 16px; font-size: 14px; font-weight: 500; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    .color-preview { display: flex; align-items: center; gap: 8px; }
    .color-preview .cp-dot { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #e2e8f0; }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">🎓 <strong>Super</strong>Escola</div>
    <nav class="sidebar-nav">
      <a href="/admin/index.php" class="snav-item">📋 Pedidos / Leads</a>
      <a href="/admin/stats.php" class="snav-item">📊 Estatísticas</a>
      <a href="/admin/schools.php" class="snav-item active">🏫 Escolas & Testemunhos</a>
    </nav>
    <div class="sidebar-footer">
      <a href="/" class="snav-item" target="_blank">🌐 Ver site</a>
      <a href="/admin/logout.php" class="snav-item snav-logout">🚪 Sair</a>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <div>
        <h1 class="admin-title">Escolas & Testemunhos</h1>
        <p class="admin-subtitle">Gerir as escolas que aparecem no site como depoimentos</p>
      </div>
      <?php if (!$edit): ?>
      <a href="schools.php?new=1" class="btn-primary-sm">+ Adicionar escola</a>
      <?php endif; ?>
    </header>

    <?php if ($msg_text): ?>
    <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg_text) ?></div>
    <?php endif; ?>

    <!-- Form: Create or Edit -->
    <?php if (isset($_GET['new']) || $edit): ?>
    <div class="form-panel">
      <h3><?= $edit ? 'Editar Escola' : 'Adicionar Nova Escola' ?></h3>
      <form method="POST">
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>

        <div class="fp-grid">
          <div class="form-group">
            <label>Nome da escola *</label>
            <input type="text" name="nome_escola" value="<?= htmlspecialchars($edit['nome_escola'] ?? '') ?>" placeholder="Ex: Colégio Estrela" required>
          </div>
          <div class="form-group">
            <label>Cidade *</label>
            <input type="text" name="cidade" value="<?= htmlspecialchars($edit['cidade'] ?? '') ?>" placeholder="Ex: Luanda" required>
          </div>
          <div class="form-group">
            <label>Nome do diretor *</label>
            <input type="text" name="nome_diretor" value="<?= htmlspecialchars($edit['nome_diretor'] ?? '') ?>" placeholder="Ex: Maria Domingos" required>
          </div>
          <div class="form-group">
            <label>Cargo</label>
            <input type="text" name="cargo" value="<?= htmlspecialchars($edit['cargo'] ?? 'Diretor(a)') ?>" placeholder="Ex: Diretor(a) Geral">
          </div>
          <div class="form-group">
            <label>Iniciais do avatar (2 letras)</label>
            <input type="text" name="iniciais" maxlength="2" value="<?= htmlspecialchars($edit['iniciais'] ?? '') ?>" placeholder="Ex: MD">
          </div>
          <div class="form-group">
            <label>Cor do avatar</label>
            <div class="color-preview">
              <input type="color" name="cor_avatar" value="<?= htmlspecialchars($edit['cor_avatar'] ?? '#4f46e5') ?>" style="width:60px;height:36px;padding:2px;border-radius:6px;cursor:pointer;">
            </div>
          </div>
          <div class="form-group" style="grid-column: 1/-1;">
            <label>URL da foto do diretor (opcional)</label>
            <input type="url" name="foto_url" value="<?= htmlspecialchars($edit['foto_url'] ?? '') ?>" placeholder="https://... (deixe em branco para usar as iniciais)">
          </div>
          <div class="form-group" style="grid-column: 1/-1;">
            <label>Depoimento *</label>
            <textarea name="depoimento" required><?= htmlspecialchars($edit['depoimento'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Classificação (estrelas)</label>
            <select name="estrelas">
              <?php for ($i = 5; $i >= 1; $i--): ?>
              <option value="<?= $i ?>" <?= ($edit['estrelas'] ?? 5) == $i ? 'selected' : '' ?>><?= str_repeat('★', $i) ?> <?= $i ?>/5</option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Ordem de exibição</label>
            <input type="number" name="ordem" value="<?= $edit['ordem'] ?? 0 ?>" min="0" max="99">
          </div>
          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="ativo" value="1" <?= ($edit['ativo'] ?? 1) ? 'checked' : '' ?>> Exibir no site
            </label>
          </div>
        </div>

        <div class="fp-actions">
          <button type="submit" class="btn-primary-sm"><?= $edit ? '💾 Guardar alterações' : '➕ Adicionar escola' ?></button>
          <a href="schools.php" class="abtn" style="padding:8px 18px;background:#f1f5f9;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Cancelar</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Schools grid -->
    <div class="schools-grid">
      <?php foreach ($escolas as $escola): ?>
      <div class="school-card">
        <div class="school-card-header">
          <div class="sc-av" style="background:<?= htmlspecialchars($escola['cor_avatar']) ?>;">
            <?php if (!empty($escola['foto_url'])): ?>
              <img src="<?= htmlspecialchars($escola['foto_url']) ?>" alt="<?= htmlspecialchars($escola['nome_diretor']) ?>">
            <?php else: ?>
              <?= htmlspecialchars($escola['iniciais']) ?>
            <?php endif; ?>
          </div>
          <div class="sc-info">
            <div class="sc-name"><?= htmlspecialchars($escola['nome_escola']) ?></div>
            <div class="sc-director"><?= htmlspecialchars($escola['nome_diretor']) ?> · <?= htmlspecialchars($escola['cargo']) ?></div>
            <div class="sc-city">📍 <?= htmlspecialchars($escola['cidade']) ?></div>
          </div>
          <span class="<?= $escola['ativo'] ? 'active-label' : 'inactive-label' ?>"><?= $escola['ativo'] ? 'Visível' : 'Oculto' ?></span>
        </div>
        <div class="sc-stars"><?= str_repeat('★', (int)$escola['estrelas']) ?></div>
        <div class="sc-text"><?= htmlspecialchars(mb_substr($escola['depoimento'], 0, 120)) ?>...</div>
        <div class="sc-actions">
          <a href="schools.php?edit=<?= $escola['id'] ?>" class="sc-btn sc-btn-edit">✏️ Editar</a>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $escola['id'] ?>">
            <button type="submit" class="sc-btn sc-btn-toggle"><?= $escola['ativo'] ? '👁 Ocultar' : '👁 Mostrar' ?></button>
          </form>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Remover esta escola dos testemunhos?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $escola['id'] ?>">
            <button type="submit" class="sc-btn sc-btn-del">🗑 Remover</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </main>
</body>
</html>
