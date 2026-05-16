<?php
session_start();
if (empty($_SESSION['admin_logged'])) {
    header('Location: /admin/login.php');
    exit;
}
require_once '../../database/init.php';
$db = getDb();

$estado = $_GET['estado'] ?? '';
$busca  = trim($_GET['busca'] ?? '');

$where = [];
$params = [];

if ($estado) {
    $where[] = "estado = ?";
    $params[] = $estado;
}
if ($busca) {
    $where[] = "(nome LIKE ? OR telefone LIKE ? OR escola LIKE ? OR email LIKE ?)";
    $params = array_merge($params, ["%$busca%", "%$busca%", "%$busca%", "%$busca%"]);
}

$sql = "SELECT * FROM leads" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY criado_em DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

$totais = $db->query("SELECT estado, COUNT(*) as total FROM leads GROUP BY estado")->fetchAll(PDO::FETCH_KEY_PAIR);
$total_all = array_sum($totais);

if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $acao = $_GET['acao'];
    $estados_validos = ['novo', 'contactado', 'convertido', 'cancelado'];
    if (in_array($acao, $estados_validos)) {
        $db->prepare("UPDATE leads SET estado = ? WHERE id = ?")->execute([$acao, $id]);
    }
    if ($acao === 'apagar') {
        $db->prepare("DELETE FROM leads WHERE id = ?")->execute([$id]);
    }
    header('Location: /admin/index.php' . ($_GET['estado'] ? '?estado=' . $_GET['estado'] : ''));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Admin — Super Escola</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">🎓 <strong>Super</strong>Escola</div>
    <nav class="sidebar-nav">
      <a href="/admin/index.php" class="snav-item active">📋 Pedidos / Leads</a>
      <a href="/admin/index.php?estado=novo" class="snav-item <?= $estado === 'novo' ? 'active' : '' ?>">
        🔵 Novos <span class="badge badge-blue"><?= $totais['novo'] ?? 0 ?></span>
      </a>
      <a href="/admin/index.php?estado=contactado" class="snav-item <?= $estado === 'contactado' ? 'active' : '' ?>">
        🟡 Contactados <span class="badge badge-yellow"><?= $totais['contactado'] ?? 0 ?></span>
      </a>
      <a href="/admin/index.php?estado=convertido" class="snav-item <?= $estado === 'convertido' ? 'active' : '' ?>">
        🟢 Convertidos <span class="badge badge-green"><?= $totais['convertido'] ?? 0 ?></span>
      </a>
      <a href="/admin/index.php?estado=cancelado" class="snav-item <?= $estado === 'cancelado' ? 'active' : '' ?>">
        🔴 Cancelados <span class="badge badge-red"><?= $totais['cancelado'] ?? 0 ?></span>
      </a>
      <a href="/admin/stats.php" class="snav-item">📊 Estatísticas</a>
      <a href="/admin/schools.php" class="snav-item">🏫 Escolas & Testemunhos</a>
      <a href="/admin/posts.php" class="snav-item">📝 Artigos do Blog</a>
    </nav>
    <div class="sidebar-footer">
      <a href="/" class="snav-item" target="_blank">🌐 Ver site</a>
      <a href="/admin/logout.php" class="snav-item snav-logout">🚪 Sair</a>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <div>
        <h1 class="admin-title">Pedidos de Demonstração</h1>
        <p class="admin-subtitle">Total: <strong><?= $total_all ?></strong> pedidos registados</p>
      </div>
      <form class="search-form" method="GET">
        <?php if ($estado): ?><input type="hidden" name="estado" value="<?= htmlspecialchars($estado) ?>"><?php endif; ?>
        <input type="text" name="busca" placeholder="🔍 Pesquisar nome, escola, telefone..." value="<?= htmlspecialchars($busca) ?>">
        <button type="submit">Pesquisar</button>
      </form>
    </header>

    <div class="kpi-row">
      <div class="kpi-card">
        <div class="kpi-n"><?= $total_all ?></div>
        <div class="kpi-l">Total de Pedidos</div>
      </div>
      <div class="kpi-card blue">
        <div class="kpi-n"><?= $totais['novo'] ?? 0 ?></div>
        <div class="kpi-l">Novos</div>
      </div>
      <div class="kpi-card yellow">
        <div class="kpi-n"><?= $totais['contactado'] ?? 0 ?></div>
        <div class="kpi-l">Contactados</div>
      </div>
      <div class="kpi-card green">
        <div class="kpi-n"><?= $totais['convertido'] ?? 0 ?></div>
        <div class="kpi-l">Convertidos</div>
      </div>
    </div>

    <div class="table-wrap">
      <?php if (empty($leads)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <h3>Nenhum pedido encontrado</h3>
          <p>Quando alguém preencher o formulário no site, aparece aqui.</p>
        </div>
      <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nome</th>
            <th>Escola</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Estado</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $lead): ?>
          <tr>
            <td class="td-id">#<?= $lead['id'] ?></td>
            <td class="td-name">
              <div class="lead-avatar"><?= mb_strtoupper(mb_substr($lead['nome'], 0, 2)) ?></div>
              <?= htmlspecialchars($lead['nome']) ?>
            </td>
            <td><?= htmlspecialchars($lead['escola']) ?></td>
            <td>
              <a href="https://wa.me/<?= preg_replace('/\D/', '', $lead['telefone']) ?>?text=Olá+<?= urlencode($lead['nome']) ?>!+Sou+da+equipa+Super+Escola." target="_blank" class="wa-link">
                💬 <?= htmlspecialchars($lead['telefone']) ?>
              </a>
            </td>
            <td><?= $lead['email'] ? '<a href="mailto:' . htmlspecialchars($lead['email']) . '">' . htmlspecialchars($lead['email']) . '</a>' : '—' ?></td>
            <td>
              <span class="estado-badge estado-<?= $lead['estado'] ?>">
                <?php
                $labels = ['novo' => '🔵 Novo', 'contactado' => '🟡 Contactado', 'convertido' => '🟢 Convertido', 'cancelado' => '🔴 Cancelado'];
                echo $labels[$lead['estado']] ?? $lead['estado'];
                ?>
              </span>
            </td>
            <td class="td-date"><?= date('d/m/Y H:i', strtotime($lead['criado_em'])) ?></td>
            <td>
              <div class="action-btns">
                <a href="lead.php?id=<?= $lead['id'] ?>" class="abtn abtn-view" title="Ver detalhe">👁</a>
                <a href="https://wa.me/<?= preg_replace('/\D/', '', $lead['telefone']) ?>?text=Olá+<?= urlencode($lead['nome']) ?>!+Sou+da+equipa+Super+Escola.+Gostaria+de+agendar+a+sua+demonstração+gratuita." target="_blank" class="abtn abtn-wa" title="WhatsApp">💬</a>
                <?php if ($lead['estado'] !== 'contactado'): ?>
                <a href="?acao=contactado&id=<?= $lead['id'] ?>" class="abtn abtn-yellow" title="Marcar contactado">🟡</a>
                <?php endif; ?>
                <?php if ($lead['estado'] !== 'convertido'): ?>
                <a href="?acao=convertido&id=<?= $lead['id'] ?>" class="abtn abtn-green" title="Marcar convertido">✅</a>
                <?php endif; ?>
                <a href="?acao=apagar&id=<?= $lead['id'] ?>" class="abtn abtn-red" title="Apagar" onclick="return confirm('Apagar este pedido?')">🗑</a>
              </div>
            </td>
          </tr>
          <?php if ($lead['mensagem']): ?>
          <tr class="tr-msg">
            <td colspan="8"><span class="msg-label">💬 Mensagem:</span> <?= htmlspecialchars($lead['mensagem']) ?></td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
