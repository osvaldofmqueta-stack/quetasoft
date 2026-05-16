<section class="trial" id="pedido-gratuito">
  <div class="container">
    <div class="trial-inner">
      <div class="trial-info">
        <div class="section-badge">Grátis para começar</div>
        <h2 class="section-title">Experimente o Super Escola <span class="gradient-text">sem compromisso</span></h2>
        <p class="section-desc">Preencha o formulário e a nossa equipa entra em contacto em menos de 24 horas para agendar a sua demonstração gratuita e personalizada.</p>
        <div class="trial-promises">
          <div class="trial-promise"><span class="tp-icon">✓</span><span>Demonstração gratuita e personalizada</span></div>
          <div class="trial-promise"><span class="tp-icon">✓</span><span>Sem contrato — experimente sem risco</span></div>
          <div class="trial-promise"><span class="tp-icon">✓</span><span>Configuração e formação incluídas</span></div>
          <div class="trial-promise"><span class="tp-icon">✓</span><span>Resposta em menos de 24 horas</span></div>
        </div>
      </div>

      <div class="trial-form-wrap">
        <form class="trial-form" id="trialForm" onsubmit="submitLead(event)">
          <h3>Pedir demonstração gratuita</h3>

          <div class="form-group">
            <label>Nome completo <span class="required">*</span></label>
            <input type="text" name="nome" placeholder="O seu nome" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Telefone / WhatsApp <span class="required">*</span></label>
              <input type="tel" name="telefone" placeholder="+244 9XX XXX XXX" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="email@escola.ao">
            </div>
          </div>
          <div class="form-group">
            <label>Nome da escola <span class="required">*</span></label>
            <input type="text" name="escola" placeholder="Ex: Colégio Estrela" required>
          </div>
          <div class="form-group">
            <label>Mensagem (opcional)</label>
            <textarea name="mensagem" rows="3" placeholder="Diga-nos mais sobre a sua escola e o que precisa..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-full" id="submitBtn">
            🚀 Quero a minha demonstração gratuita
          </button>
          <p class="form-note">Os seus dados são confidenciais e nunca serão partilhados com terceiros.</p>

          <div class="form-success" id="formSuccess" style="display:none">
            <div class="success-icon">🎉</div>
            <h4>Pedido enviado com sucesso!</h4>
            <p>A nossa equipa irá entrar em contacto em breve pelo WhatsApp ou email. Obrigado!</p>
          </div>
          <div class="form-error" id="formError" style="display:none"></div>
        </form>
      </div>
    </div>
  </div>
</section>
