<?php
session_start();
if (empty($_SESSION['admin_logged'])) {
    header('Location: /admin/login.php');
    exit;
}
require_once '../../database/init.php';
$db = getDb();

$total     = $db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$novos     = $db->query("SELECT COUNT(*) FROM leads WHERE estado='novo'")->fetchColumn();
$contactados = $db->query("SELECT COUNT(*) FROM leads WHERE estado='contactado'")->fetchColumn();
$convertidos = $db->query("SELECT COUNT(*) FROM leads WHERE estado='convertido'")->fetchColumn();
$cancelados  = $db->query("SELECT COUNT(*) FROM leads WHERE estado='cancelado'")->fetchColumn();

$taxa_conv = $total > 0 ? round(($convertidos / $total) * 100, 1) : 0;
$taxa_cont = $total > 0 ? round((($contactados + $convertidos) / $total) * 100, 1) : 0;

$por_mes = $db->query("
    SELECT strftime('%m/%Y', criado_em) as mes, COUNT(*) as total,
           SUM(CASE WHEN estado='convertido' THEN 1 ELSE 0 END) as conv
    FROM leads
    GROUP BY strftime('%Y-%m', criado_em)
    ORDER BY criado_em DESC
    LIMIT 6
")->fetchAll();

$recentes = $db->query("SELECT * FROM leads ORDER BY criado_em DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estatísticas — Super Escola Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 24px 32px; }
    .stats-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:24px; }
    .stats-card h3 { font-size:14px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; }
    .big-stat { font-size:52px; font-weight:900; line-height:1; }
    .big-stat.blue { color:#4f46e5; }
    .big-stat.green { color:#10b981; }
    .big-stat.yellow { color:#f59e0b; }
    .big-stat-label { font-size:14px; color:#64748b; margin-top:8px; }
    .progress-bar { height:10px; background:#f1f5f9; border-radius:5px; margin:8px 0 4px; overflow:hidden; }
    .progress-fill { height:100%; border-radius:5px; transition:.5s; }
    .bar-row { margin-bottom:14px; }
    .bar-label { display:flex; justify-content:space-between; font-size:13px; color:#374151; font-weight:500; }
    .mes-chart { display:flex; align-items:flex-end; gap:8px; height:120px; margin-top:12px; }
    .mes-col { flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; height:100%; justify-content:flex-end; }
    .mes-bar { width:100%; border-radius:4px 4px 0 0; }
    .mes-label { font-size:9px; color:#94a3b8; }
    .mes-val { font-size:10px; font-weight:700; color:#64748b; }
    .recent-list { display:flex; flex-direction:column; gap:10px; }
    .recent-item { display:flex; align-items:center; gap:12px; padding:10px; background:#f8fafc; border-radius:8px; }
    .recent-av { width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#818cf8);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .recent-info { flex:1; }
    .recent-name { font-size:13px; font-weight:600; }
    .recent-meta { font-size:11px; color:#94a3b8; }
    .recent-estado { font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .funil { display:flex; flex-direction:column; gap:8px; margin-top:8px; }
    .funil-item { display:flex; align-items:center; gap:12px; }
    .funil-bar { flex:1; height:28px; border-radius:6px; display:flex; align-items:center; padding:0 10px; font-size:12px; font-weight:700; color:#fff; }
    .funil-n { font-size:18px; font-weight:800; width:36px; text-align:right; }
    .funil-l { font-size:12px; color:#64748b; width:80px; }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">🎓 <strong>Super</strong>Escola</div>
    <nav class="sidebar-nav">
      <a href="/admin/index.php" class="snav-item">📋 Pedidos / Leads</a>
      <a href="/admin/stats.php" class="snav-item active">📊 Estatísticas</a>
      <a href="/admin/schools.php" class="snav-item">🏫 Escolas & Testemunhos</a>
      <a href="/admin/posts.php" class="snav-item">📝 Artigos do Blog</a>
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
        <h1 class="admin-title">Estatísticas & Conversão</h1>
        <p class="admin-subtitle">Acompanhe o desempenho dos seus pedidos de demonstração</p>
      </div>
    </header>

    <div class="kpi-row">
      <div class="kpi-card"><div class="kpi-n"><?= $total ?></div><div class="kpi-l">Total de Pedidos</div></div>
      <div class="kpi-card blue"><div class="kpi-n"><?= $novos ?></div><div class="kpi-l">Novos (por contactar)</div></div>
      <div class="kpi-card yellow"><div class="kpi-n"><?= $contactados ?></div><div class="kpi-l">Contactados</div></div>
      <div class="kpi-card green"><div class="kpi-n"><?= $convertidos ?></div><div class="kpi-l">Convertidos (clientes)</div></div>
    </div>

    <div class="stats-grid">

      <!-- Taxa de conversão -->
      <div class="stats-card">
        <h3>Taxa de Conversão</h3>
        <div class="big-stat green"><?= $taxa_conv ?>%</div>
        <div class="big-stat-label">dos pedidos viraram clientes</div>
        <div style="margin-top:20px;" class="bar-row">
          <div class="bar-label"><span>🔵 Contactados</span><span><?= $taxa_cont ?>%</span></div>
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $taxa_cont ?>%;background:#3b82f6;"></div></div>
        </div>
        <div class="bar-row">
          <div class="bar-label"><span>🟢 Convertidos</span><span><?= $taxa_conv ?>%</span></div>
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $taxa_conv ?>%;background:#10b981;"></div></div>
        </div>
        <div class="bar-row">
          <div class="bar-label"><span>🔴 Cancelados</span><span><?= $total > 0 ? round(($cancelados/$total)*100,1) : 0 ?>%</span></div>
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $total > 0 ? round(($cancelados/$total)*100,1) : 0 ?>%;background:#ef4444;"></div></div>
        </div>
      </div>

      <!-- Funil de vendas -->
      <div class="stats-card">
        <h3>Funil de Pedidos</h3>
        <div class="funil">
          <div class="funil-item">
            <div class="funil-n"><?= $total ?></div>
            <div class="funil-bar" style="background:#4f46e5;width:100%;">📨 Total recebidos</div>
          </div>
          <div class="funil-item">
            <div class="funil-n"><?= $contactados + $convertidos ?></div>
            <div class="funil-bar" style="background:#3b82f6;width:<?= $total > 0 ? (($contactados+$convertidos)/$total)*100 : 0 ?>%;">📞 Contactados</div>
          </div>
          <div class="funil-item">
            <div class="funil-n"><?= $convertidos ?></div>
            <div class="funil-bar" style="background:#10b981;width:<?= $total > 0 ? ($convertidos/$total)*100 : 0 ?>%;">✅ Convertidos</div>
          </div>
        </div>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;">
          <?php if ($total > 0): ?>
          Cada <strong><?= $taxa_conv > 0 ? round(100/$taxa_conv) : '∞' ?> pedidos</strong> gera em média <strong>1 cliente</strong>.
          <?php else: ?>
          Ainda sem dados suficientes para calcular.
          <?php endif; ?>
        </div>
      </div>

      <!-- Pedidos por mês -->
      <div class="stats-card">
        <h3>Pedidos por Mês</h3>
        <?php if (empty($por_mes)): ?>
        <div style="text-align:center;color:#94a3b8;padding:40px 0;">Sem dados ainda</div>
        <?php else: ?>
        <div class="mes-chart">
          <?php foreach (array_reverse($por_mes) as $m): ?>
          <div class="mes-col">
            <div class="mes-val"><?= $m['total'] ?></div>
            <div class="mes-bar" style="height:<?= $m['total'] > 0 ? ($m['total'] / max(array_column($por_mes,'total'))) * 90 : 5 ?>px;background:<?= $m['conv'] > 0 ? '#10b981' : '#4f46e5' ?>;"></div>
            <div class="mes-label"><?= $m['mes'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:16px;margin-top:12px;font-size:11px;color:#64748b;">
          <span><span style="color:#10b981;">■</span> Com conversão</span>
          <span><span style="color:#4f46e5;">■</span> Sem conversão</span>
        </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Atividade recente -->
    <div style="margin:0 32px 32px;">
      <div class="stats-card">
        <h3>Últimos 5 Pedidos Recebidos</h3>
        <?php if (empty($recentes)): ?>
        <div style="text-align:center;color:#94a3b8;padding:32px;">Sem pedidos ainda. <a href="/" target="_blank" style="color:#4f46e5;">Partilhe o link do site</a> para começar a receber.</div>
        <?php else: ?>
        <div class="recent-list" style="margin-top:16px;">
          <?php
          $estado_colors = ['novo'=>'#3b82f6','contactado'=>'#f59e0b','convertido'=>'#10b981','cancelado'=>'#ef4444'];
          $estado_labels = ['novo'=>'🔵 Novo','contactado'=>'🟡 Contactado','convertido'=>'🟢 Convertido','cancelado'=>'🔴 Cancelado'];
          foreach ($recentes as $r):
          ?>
          <div class="recent-item">
            <div class="recent-av"><?= mb_strtoupper(mb_substr($r['nome'],0,2)) ?></div>
            <div class="recent-info">
              <div class="recent-name"><?= htmlspecialchars($r['nome']) ?> — <?= htmlspecialchars($r['escola']) ?></div>
              <div class="recent-meta">📞 <?= htmlspecialchars($r['telefone']) ?> · <?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></div>
            </div>
            <span class="recent-estado" style="background:<?= $estado_colors[$r['estado']] ?? '#4f46e5' ?>22;color:<?= $estado_colors[$r['estado']] ?? '#4f46e5' ?>;">
              <?= $estado_labels[$r['estado']] ?? $r['estado'] ?>
            </span>
            <a href="lead.php?id=<?= $r['id'] ?>" style="margin-left:8px;font-size:13px;color:#4f46e5;text-decoration:none;font-weight:600;">Ver →</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
