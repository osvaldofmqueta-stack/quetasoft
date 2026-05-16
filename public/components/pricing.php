<?php
// Load recent payment count for social proof
$pricing_db = getDb();
$total_installs = $pricing_db->query("SELECT COUNT(*) FROM pagamentos")->fetchColumn();
?>
<section class="pricing" id="precos">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Planos & Preços</span>
      <h2 class="section-title">Pagamento único. Sem mensalidades.</h2>
      <p class="section-subtitle">Escolha o pacote que melhor se adapta ao tamanho da sua escola. Instalação completa e suporte incluídos.</p>
    </div>

    <div class="pricing-grid">

      <!-- Básico -->
      <div class="pricing-card">
        <div class="pricing-header">
          <div class="pricing-icon">🏫</div>
          <h3 class="pricing-name">Básico</h3>
          <p class="pricing-desc">Ideal para escolas pequenas a começar a digitalizar</p>
        </div>
        <div class="pricing-price-wrap">
          <span class="pricing-currency">$</span>
          <span class="pricing-price">299</span>
          <span class="pricing-period">pagamento único</span>
        </div>
        <ul class="pricing-features">
          <li><span class="pf-check">✓</span> 1 campus / filial</li>
          <li><span class="pf-check">✓</span> Até 300 alunos</li>
          <li><span class="pf-check">✓</span> Matrículas e pagamentos</li>
          <li><span class="pf-check">✓</span> Cobranças automáticas WhatsApp</li>
          <li><span class="pf-check">✓</span> Gestão de turmas e notas</li>
          <li><span class="pf-check">✓</span> Suporte por email (48h)</li>
          <li class="pf-no"><span class="pf-cross">✗</span> Portal do encarregado</li>
          <li class="pf-no"><span class="pf-cross">✗</span> Relatórios avançados</li>
        </ul>
        <a href="https://wa.me/244926219731?text=Olá!+Tenho+interesse+no+Pacote+Básico+($299).+Pode+dar+mais+informações?" target="_blank" class="pricing-btn btn btn-outline">Adquirir agora</a>
      </div>

      <!-- Profissional (popular) -->
      <div class="pricing-card pricing-popular">
        <div class="pricing-badge">⭐ Mais popular</div>
        <div class="pricing-header">
          <div class="pricing-icon">🎓</div>
          <h3 class="pricing-name">Profissional</h3>
          <p class="pricing-desc">Para escolas em crescimento com múltiplas turmas</p>
        </div>
        <div class="pricing-price-wrap">
          <span class="pricing-currency">$</span>
          <span class="pricing-price">499</span>
          <span class="pricing-period">pagamento único</span>
        </div>
        <ul class="pricing-features">
          <li><span class="pf-check">✓</span> Até 3 campus / filiais</li>
          <li><span class="pf-check">✓</span> Alunos ilimitados</li>
          <li><span class="pf-check">✓</span> Tudo do Básico incluído</li>
          <li><span class="pf-check">✓</span> Portal do encarregado</li>
          <li><span class="pf-check">✓</span> Relatórios e estatísticas</li>
          <li><span class="pf-check">✓</span> Calendário académico</li>
          <li><span class="pf-check">✓</span> Suporte prioritário (24h)</li>
          <li class="pf-no"><span class="pf-cross">✗</span> Treinamento presencial</li>
        </ul>
        <a href="https://wa.me/244926219731?text=Olá!+Tenho+interesse+no+Pacote+Profissional+($499).+Pode+dar+mais+informações?" target="_blank" class="pricing-btn btn btn-primary">Adquirir agora</a>
      </div>

      <!-- Completo -->
      <div class="pricing-card">
        <div class="pricing-header">
          <div class="pricing-icon">🏆</div>
          <h3 class="pricing-name">Completo</h3>
          <p class="pricing-desc">Para grupos escolares e instituições de grande porte</p>
        </div>
        <div class="pricing-price-wrap">
          <span class="pricing-currency">$</span>
          <span class="pricing-price">799</span>
          <span class="pricing-period">pagamento único</span>
        </div>
        <ul class="pricing-features">
          <li><span class="pf-check">✓</span> Filiais ilimitadas</li>
          <li><span class="pf-check">✓</span> Alunos ilimitados</li>
          <li><span class="pf-check">✓</span> Tudo do Profissional</li>
          <li><span class="pf-check">✓</span> Personalização da marca</li>
          <li><span class="pf-check">✓</span> Treinamento completo da equipa</li>
          <li><span class="pf-check">✓</span> API de integração</li>
          <li><span class="pf-check">✓</span> Suporte dedicado WhatsApp 24/7</li>
          <li><span class="pf-check">✓</span> Actualizações gratuitas 1 ano</li>
        </ul>
        <a href="https://wa.me/244926219731?text=Olá!+Tenho+interesse+no+Pacote+Completo+($799).+Pode+dar+mais+informações?" target="_blank" class="pricing-btn btn btn-outline">Adquirir agora</a>
      </div>

    </div>

    <div class="pricing-footer">
      <div class="pricing-trust">
        <?php if ($total_installs > 0): ?>
        <span class="ptrust-item">✅ <strong><?= $total_installs ?>+ escolas</strong> já instalaram</span>
        <?php endif; ?>
        <span class="ptrust-item">🔒 <strong>Pagamento seguro</strong> via transferência ou TPA</span>
        <span class="ptrust-item">🛠 <strong>Instalação em 24h</strong> após confirmação</span>
        <span class="ptrust-item">📞 <strong>Suporte local</strong> em Angola</span>
      </div>
      <p class="pricing-note">Preços em USD. Aceitamos transferência bancária, TPA, Multicaixa e referência. <a href="https://wa.me/244926219731?text=Tenho+dúvidas+sobre+os+planos." target="_blank">Fale connosco</a> para condições especiais.</p>
    </div>
  </div>
</section>
