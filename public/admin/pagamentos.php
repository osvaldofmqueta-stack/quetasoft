<?php
session_start();
if (empty($_SESSION['admin_logged'])) { header('Location: /admin/login.php'); exit; }
require_once '../../database/init.php';
$db = getDb();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $escola = trim($_POST['escola_nome'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $pacote = trim($_POST['pacote'] ?? '');
        $valor  = (float)($_POST['valor'] ?? 0);
        $metodo = trim($_POST['metodo'] ?? 'Transferência Bancária');
        $pago_em = trim($_POST['pago_em'] ?? date('Y-m-d H:i:s'));

        if ($escola && $pacote) {
            $db->prepare("INSERT INTO pagamentos (escola_nome, cidade, pacote, valor, metodo, pago_em) VALUES (?,?,?,?,?,?)")
               ->execute([$escola, $cidade, $pacote, $valor, $metodo, $pago_em]);
            $msg = 'success|Pagamento registado com sucesso!';
        } else {
            $msg = 'error|Escola e pacote são obrigatórios.';
        }
    }

    if ($action === 'delete') {
        $db->prepare("DELETE FROM pagamentos WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: /admin/pagamentos.php?deleted=1'); exit;
    }
}

if (isset($_GET['deleted'])) $msg = 'success|Registo removido.';

// Stats
$total_recebido = $db->query("SELECT COALESCE(SUM(valor),0) FROM pagamentos")->fetchColumn();
$total_mes = $db->query("SELECT COALESCE(SUM(valor),0) FROM pagamentos WHERE strftime('%Y-%m',pago_em)=strftime('%Y-%m','now')")->fetchColumn();
$count_mes = $db->query("SELECT COUNT(*) FROM pagamentos WHERE strftime('%Y-%m',pago_em)=strftime('%Y-%m','now')")->fetchColumn();
$popular = $db->query("SELECT pacote, COUNT(*) as n FROM pagamentos GROUP BY pacote ORDER BY n DESC LIMIT 1")->fetch();
$count_total = $db->query("SELECT COUNT(*) FROM pagamentos")->fetchColumn();

// Pacote breakdown
$breakdown = $db->query("SELECT pacote, COUNT(*) as n, COALESCE(SUM(valor),0) as total FROM pagamentos GROUP BY pacote ORDER BY total DESC")->fetchAll();

$pagamentos = $db->query("SELECT * FROM pagamentos ORDER BY pago_em DESC")->fetchAll();
$pacotes = ['Básico' => 299, 'Profissional' => 499, 'Completo' => 799];
$metodos = ['Transferência Bancária', 'TPA', 'Multicaixa', 'Referência Multicaixa', 'Outro'];

[$msg_type, $msg_text] = $msg ? explode('|', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamentos — Super Escola Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .pay-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; padding:0 32px 24px; }
    .pay-stat { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; }
    .ps-label { font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; margin-bottom:6px; }
    .ps-value { font-size:26px; font-weight:800; color:#1e293b; }
    .ps-sub { font-size:12px; color:#94a3b8; margin-top:4px; }
    .ps-green { border-top:3px solid #10b981; }
    .ps-blue  { border-top:3px solid #3b82f6; }
    .ps-purple{ border-top:3px solid #8b5cf6; }
    .ps-yellow{ border-top:3px solid #f59e0b; }

    .pay-content { padding:0 32px 48px; display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:flex-start; }

    /* Table */
    .pay-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
    .pay-table-header { padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
    .pay-table-header h3 { font-size:15px; font-weight:700; }
    table { width:100%; border-collapse:collapse; }
    th { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; padding:10px 16px; background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0; }
    td { font-size:13px; padding:12px 16px; border-bottom:1px solid #f8fafc; color:#374151; vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fafbff; }
    .pacote-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
    .pacote-basico { background:#e0f2fe; color:#0369a1; }
    .pacote-profissional { background:#ede9fe; color:#6d28d9; }
    .pacote-completo { background:#fef3c7; color:#92400e; }

    /* Form */
    .add-form { background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
    .add-form-header { padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; cursor:pointer; }
    .add-form-header h3 { font-size:15px; font-weight:700; }
    .add-form-body { padding:20px; display:flex; flex-direction:column; gap:14px; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; }
    .form-group input, .form-group select { width:100%; padding:9px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; font-family:inherit; box-sizing:border-box; transition:0.15s; }
    .form-group input:focus, .form-group select:focus { outline:none; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.08); }

    /* Breakdown */
    .breakdown-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-top:16px; }
    .breakdown-card h4 { font-size:13px; font-weight:700; margin-bottom:14px; color:#1e293b; }
    .bd-row { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
    .bd-row:last-child { border-bottom:none; }
    .bd-label { font-weight:600; display:flex; align-items:center; gap:8px; }
    .bd-val { color:#94a3b8; font-weight:500; }
    .bd-amt { font-weight:700; color:#1e293b; }

    .alert { padding:12px 16px; border-radius:8px; margin:0 32px 16px; font-size:14px; }
    .alert-success { background:#dcfce7; color:#166534; }
    .alert-error { background:#fee2e2; color:#991b1b; }

    @media(max-width:1100px){ .pay-content { grid-template-columns:1fr; } }
    @media(max-width:768px){ .pay-stats { grid-template-columns:1fr 1fr; } }
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
    <a href="/admin/pagamentos.php" class="snav-item active">💳 Pagamentos</a>
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
      <h1 class="admin-title">Pagamentos</h1>
      <p class="admin-subtitle">Registo de escolas que adquiriram pacotes de instalação</p>
    </div>
  </header>

  <?php if ($msg_text): ?>
  <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg_text) ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="pay-stats">
    <div class="pay-stat ps-green">
      <div class="ps-label">Total recebido</div>
      <div class="ps-value">$<?= number_format($total_recebido, 0) ?></div>
      <div class="ps-sub"><?= $count_total ?> instalações</div>
    </div>
    <div class="pay-stat ps-blue">
      <div class="ps-label">Este mês</div>
      <div class="ps-value">$<?= number_format($total_mes, 0) ?></div>
      <div class="ps-sub"><?= $count_mes ?> pagamentos</div>
    </div>
    <div class="pay-stat ps-purple">
      <div class="ps-label">Ticket médio</div>
      <div class="ps-value">$<?= $count_total ? number_format($total_recebido / $count_total, 0) : 0 ?></div>
      <div class="ps-sub">por escola</div>
    </div>
    <div class="pay-stat ps-yellow">
      <div class="ps-label">Pacote popular</div>
      <div class="ps-value" style="font-size:18px;"><?= htmlspecialchars($popular['pacote'] ?? '—') ?></div>
      <div class="ps-sub"><?= $popular['n'] ?? 0 ?> instalações</div>
    </div>
  </div>

  <div class="pay-content">

    <!-- Table -->
    <div class="pay-table-wrap">
      <div class="pay-table-header">
        <h3>Histórico de pagamentos</h3>
        <span style="font-size:13px;color:#94a3b8;"><?= $count_total ?> registos</span>
      </div>
      <?php if (empty($pagamentos)): ?>
      <div class="empty-state"><div class="empty-icon">💳</div><h3>Sem pagamentos</h3><p>Registe o primeiro pagamento no formulário ao lado.</p></div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Escola</th>
            <th>Cidade</th>
            <th>Pacote</th>
            <th>Valor</th>
            <th>Método</th>
            <th>Data</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pagamentos as $p): ?>
          <tr>
            <td><strong><?= htmlspecialchars($p['escola_nome']) ?></strong></td>
            <td><?= htmlspecialchars($p['cidade']) ?></td>
            <td>
              <span class="pacote-badge pacote-<?= strtolower(explode('/', $p['pacote'])[0]) ?>">
                <?= htmlspecialchars($p['pacote']) ?>
              </span>
            </td>
            <td><strong style="color:#10b981;">$<?= number_format($p['valor'], 0) ?></strong></td>
            <td style="color:#64748b;"><?= htmlspecialchars($p['metodo']) ?></td>
            <td style="color:#64748b;"><?= date('d/m/Y H:i', strtotime($p['pago_em'])) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Remover este registo?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="abtn abtn-red" title="Remover">🗑</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Right panel: form + breakdown -->
    <div>
      <!-- Add form -->
      <div class="add-form">
        <div class="add-form-header" onclick="toggleForm(this)">
          <h3>➕ Registar pagamento</h3>
          <span id="formToggleIcon">▾</span>
        </div>
        <form method="POST" id="addForm">
          <div class="add-form-body">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
              <label>Nome da escola *</label>
              <input type="text" name="escola_nome" placeholder="Ex: Escola Primária São Lucas" required>
            </div>
            <div class="form-group">
              <label>Cidade</label>
              <input type="text" name="cidade" placeholder="Ex: Luanda, Benguela...">
            </div>
            <div class="form-group">
              <label>Pacote *</label>
              <select name="pacote" id="pacoteSelect" onchange="autoFillValor(this)" required>
                <option value="">— Seleccione —</option>
                <?php foreach ($pacotes as $nome => $preco): ?>
                <option value="<?= $nome ?>" data-valor="<?= $preco ?>"><?= $nome ?> ($<?= $preco ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Valor ($)</label>
              <input type="number" name="valor" id="valorInput" placeholder="0" min="0" step="1">
            </div>
            <div class="form-group">
              <label>Método de pagamento</label>
              <select name="metodo">
                <?php foreach ($metodos as $m): ?>
                <option value="<?= $m ?>"><?= $m ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Data do pagamento</label>
              <input type="datetime-local" name="pago_em" value="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <button type="submit" class="btn-primary-sm" style="width:100%;">💾 Guardar registo</button>
          </div>
        </form>
      </div>

      <!-- Breakdown by package -->
      <?php if (!empty($breakdown)): ?>
      <div class="breakdown-card">
        <h4>📊 Por pacote</h4>
        <?php foreach ($breakdown as $b): ?>
        <div class="bd-row">
          <span class="bd-label">
            <span class="pacote-badge pacote-<?= strtolower($b['pacote']) ?>"><?= htmlspecialchars($b['pacote']) ?></span>
          </span>
          <span class="bd-val"><?= $b['n'] ?>x</span>
          <span class="bd-amt">$<?= number_format($b['total'], 0) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<script>
function autoFillValor(sel) {
  const opt = sel.options[sel.selectedIndex];
  const val = opt.dataset.valor;
  if (val) document.getElementById('valorInput').value = val;
}
function toggleForm(header) {
  const form = document.getElementById('addForm');
  const icon = document.getElementById('formToggleIcon');
  const hidden = form.style.display === 'none';
  form.style.display = hidden ? '' : 'none';
  icon.textContent = hidden ? '▾' : '▸';
}
</script>
</body>
</html>
