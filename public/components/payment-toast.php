<?php
$toast_db = getDb();
$toast_payments = $toast_db->query("SELECT * FROM pagamentos ORDER BY pago_em DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
if (empty($toast_payments)) return;

$toast_json = json_encode(array_values($toast_payments), JSON_UNESCAPED_UNICODE);
?>
<div id="paymentToast" class="payment-toast hidden" role="alert" aria-live="polite">
  <button class="toast-close" onclick="closeToast()" aria-label="Fechar">✕</button>
  <div class="toast-icon">🛍</div>
  <div class="toast-body">
    <div class="toast-school"></div>
    <div class="toast-pkg"></div>
    <div class="toast-meta">
      <span class="toast-time"></span>
      <span class="toast-verified">✅ Verificado por <span class="toast-method"></span></span>
    </div>
  </div>
</div>

<script>
(function() {
  const toasts = <?= $toast_json ?>;
  let current = 0;
  let timer = null;
  let dismissed = false;

  function timeAgo(dt) {
    const diff = (Date.now() - new Date(dt).getTime()) / 1000;
    if (diff < 120) return 'há 1 minuto';
    if (diff < 3600) return 'há ' + Math.round(diff / 60) + ' minutos';
    if (diff < 86400) return 'há ' + Math.round(diff / 3600) + ' horas';
    return 'há ' + Math.round(diff / 86400) + ' dias';
  }

  function showToast(idx) {
    if (dismissed) return;
    const t = toasts[idx];
    const el = document.getElementById('paymentToast');
    if (!el) return;

    el.querySelector('.toast-school').textContent = t.escola_nome + ' — ' + t.cidade;
    el.querySelector('.toast-pkg').textContent = 'adquiriu o Pacote ' + t.pacote;
    el.querySelector('.toast-time').textContent = timeAgo(t.pago_em);
    el.querySelector('.toast-method').textContent = t.metodo;

    el.classList.remove('hidden');

    timer = setTimeout(() => {
      el.classList.add('hiding');
      setTimeout(() => {
        el.classList.add('hidden');
        el.classList.remove('hiding');
        current = (current + 1) % toasts.length;
        if (!dismissed) {
          setTimeout(() => showToast(current), 4000);
        }
      }, 500);
    }, 5500);
  }

  window.closeToast = function() {
    dismissed = true;
    clearTimeout(timer);
    const el = document.getElementById('paymentToast');
    if (el) {
      el.classList.add('hiding');
      setTimeout(() => el.classList.add('hidden'), 400);
    }
  };

  // Start after 4s delay
  setTimeout(() => showToast(0), 4000);
})();
</script>
