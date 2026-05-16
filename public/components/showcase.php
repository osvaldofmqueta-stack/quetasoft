<section class="showcase" id="showcase">
  <div class="container">
    <div class="section-header">
      <div class="section-badge">Tour pelo sistema</div>
      <h2 class="section-title">Veja o Super Escola em ação</h2>
      <p class="section-desc">Explore cada funcionalidade e veja como o sistema simplifica o dia a dia da sua escola.</p>
    </div>

    <div class="showcase-tabs">
      <div class="showcase-nav">
        <button class="stab active" onclick="switchTab(this, 'alunos')">
          <span class="stab-icon">👥</span>
          <span class="stab-label">Alunos</span>
        </button>
        <button class="stab" onclick="switchTab(this, 'financeiro')">
          <span class="stab-icon">💰</span>
          <span class="stab-label">Financeiro</span>
        </button>
        <button class="stab" onclick="switchTab(this, 'horarios')">
          <span class="stab-icon">📅</span>
          <span class="stab-label">Horários</span>
        </button>
        <button class="stab" onclick="switchTab(this, 'notas')">
          <span class="stab-icon">📝</span>
          <span class="stab-label">Notas & Boletins</span>
        </button>
        <button class="stab" onclick="switchTab(this, 'notificacoes')">
          <span class="stab-icon">🔔</span>
          <span class="stab-label">Notificações</span>
        </button>
        <button class="stab" onclick="switchTab(this, 'relatorios')">
          <span class="stab-icon">📊</span>
          <span class="stab-label">Relatórios</span>
        </button>
      </div>

      <div class="showcase-content">

        <!-- ALUNOS -->
        <div class="spanel active" id="tab-alunos">
          <div class="spanel-info">
            <h3>Gestão completa de alunos</h3>
            <p>Registe todos os alunos com ficha completa: dados pessoais, contactos, encarregados de educação, turma, documentos e histórico académico — tudo num só lugar.</p>
            <ul class="spanel-features">
              <li><span class="sf-check">✓</span> Ficha completa do aluno</li>
              <li><span class="sf-check">✓</span> Gestão de documentos e contratos</li>
              <li><span class="sf-check">✓</span> Histórico de matrículas</li>
              <li><span class="sf-check">✓</span> Pesquisa e filtros avançados</li>
              <li><span class="sf-check">✓</span> Importação em massa via Excel</li>
            </ul>
            <a href="https://wa.me/244926219731?text=Quero+ver+uma+demo+da+gestão+de+alunos+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir demonstração →</a>
          </div>
          <div class="spanel-visual">
            <div class="screen-mock">
              <div class="screen-topbar">
                <span class="screen-title">👥 Gestão de Alunos</span>
                <div class="screen-actions">
                  <span class="screen-btn">+ Novo Aluno</span>
                  <span class="screen-btn outline">Exportar</span>
                </div>
              </div>
              <div class="screen-search">
                <div class="screen-searchbox">🔍 Pesquisar aluno...</div>
                <div class="screen-filter">Turma ▾</div>
                <div class="screen-filter">Estado ▾</div>
              </div>
              <div class="screen-table">
                <div class="screen-table-header">
                  <span>Nome</span><span>Turma</span><span>Estado</span><span>Encarregado</span><span>Ações</span>
                </div>
                <div class="screen-row">
                  <div class="screen-cell"><div class="screen-avatar">AN</div> Ana Neto</div>
                  <div class="screen-cell"><span class="screen-badge blue">10ª A</span></div>
                  <div class="screen-cell"><span class="screen-badge green">Ativo</span></div>
                  <div class="screen-cell">Maria Neto</div>
                  <div class="screen-cell"><span class="screen-action">Ver ✏️</span></div>
                </div>
                <div class="screen-row alt">
                  <div class="screen-cell"><div class="screen-avatar purple">JS</div> João Silva</div>
                  <div class="screen-cell"><span class="screen-badge blue">10ª B</span></div>
                  <div class="screen-cell"><span class="screen-badge green">Ativo</span></div>
                  <div class="screen-cell">Pedro Silva</div>
                  <div class="screen-cell"><span class="screen-action">Ver ✏️</span></div>
                </div>
                <div class="screen-row">
                  <div class="screen-cell"><div class="screen-avatar orange">CM</div> Clara Matos</div>
                  <div class="screen-cell"><span class="screen-badge purple">11ª A</span></div>
                  <div class="screen-cell"><span class="screen-badge yellow">Pendente</span></div>
                  <div class="screen-cell">Rosa Matos</div>
                  <div class="screen-cell"><span class="screen-action">Ver ✏️</span></div>
                </div>
                <div class="screen-row alt">
                  <div class="screen-cell"><div class="screen-avatar">LF</div> Luís Ferreira</div>
                  <div class="screen-cell"><span class="screen-badge purple">11ª B</span></div>
                  <div class="screen-cell"><span class="screen-badge green">Ativo</span></div>
                  <div class="screen-cell">Carlos Ferreira</div>
                  <div class="screen-cell"><span class="screen-action">Ver ✏️</span></div>
                </div>
                <div class="screen-row">
                  <div class="screen-cell"><div class="screen-avatar orange">BL</div> Beatriz Lopes</div>
                  <div class="screen-cell"><span class="screen-badge blue">10ª A</span></div>
                  <div class="screen-cell"><span class="screen-badge red">Inativo</span></div>
                  <div class="screen-cell">Sofia Lopes</div>
                  <div class="screen-cell"><span class="screen-action">Ver ✏️</span></div>
                </div>
              </div>
              <div class="screen-footer">Mostrando 1-5 de 248 alunos</div>
            </div>
          </div>
        </div>

        <!-- FINANCEIRO -->
        <div class="spanel" id="tab-financeiro">
          <div class="spanel-info">
            <h3>Controlo financeiro total</h3>
            <p>Gere mensalidades, emita cobranças automáticas e acompanhe todos os pagamentos em tempo real. Reduza a inadimplência com lembretes automáticos por WhatsApp.</p>
            <ul class="spanel-features">
              <li><span class="sf-check">✓</span> Cobranças automáticas mensais</li>
              <li><span class="sf-check">✓</span> Lembretes por WhatsApp e email</li>
              <li><span class="sf-check">✓</span> Relatório de inadimplência</li>
              <li><span class="sf-check">✓</span> Registo de pagamentos</li>
              <li><span class="sf-check">✓</span> Recibos automáticos</li>
            </ul>
            <a href="https://wa.me/244926219731?text=Quero+ver+uma+demo+do+módulo+financeiro+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir demonstração →</a>
          </div>
          <div class="spanel-visual">
            <div class="screen-mock">
              <div class="screen-topbar">
                <span class="screen-title">💰 Financeiro</span>
                <div class="screen-actions">
                  <span class="screen-btn">+ Nova Cobrança</span>
                </div>
              </div>
              <div class="fin-summary">
                <div class="fin-card green">
                  <div class="fin-label">Recebido (Maio)</div>
                  <div class="fin-val">450.000 Kz</div>
                  <div class="fin-sub">↑ 12% vs mês anterior</div>
                </div>
                <div class="fin-card red">
                  <div class="fin-label">Em atraso</div>
                  <div class="fin-val">85.000 Kz</div>
                  <div class="fin-sub">23 alunos</div>
                </div>
                <div class="fin-card blue">
                  <div class="fin-label">A vencer</div>
                  <div class="fin-val">210.000 Kz</div>
                  <div class="fin-sub">Próximos 7 dias</div>
                </div>
              </div>
              <div class="screen-table">
                <div class="screen-table-header">
                  <span>Aluno</span><span>Valor</span><span>Vencimento</span><span>Estado</span>
                </div>
                <div class="screen-row">
                  <div class="screen-cell">Ana Neto</div>
                  <div class="screen-cell">15.000 Kz</div>
                  <div class="screen-cell">05/05/2026</div>
                  <div class="screen-cell"><span class="screen-badge green">Pago</span></div>
                </div>
                <div class="screen-row alt">
                  <div class="screen-cell">João Silva</div>
                  <div class="screen-cell">15.000 Kz</div>
                  <div class="screen-cell">05/05/2026</div>
                  <div class="screen-cell"><span class="screen-badge red">Em atraso</span></div>
                </div>
                <div class="screen-row">
                  <div class="screen-cell">Clara Matos</div>
                  <div class="screen-cell">15.000 Kz</div>
                  <div class="screen-cell">10/05/2026</div>
                  <div class="screen-cell"><span class="screen-badge yellow">Pendente</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- HORARIOS -->
        <div class="spanel" id="tab-horarios">
          <div class="spanel-info">
            <h3>Quadro de horários visual</h3>
            <p>Monte os horários das turmas e professores de forma visual e intuitiva. O sistema deteta automaticamente conflitos de horário e sala.</p>
            <ul class="spanel-features">
              <li><span class="sf-check">✓</span> Vista semanal por turma ou professor</li>
              <li><span class="sf-check">✓</span> Deteção de conflitos automática</li>
              <li><span class="sf-check">✓</span> Gestão de salas</li>
              <li><span class="sf-check">✓</span> Exportação em PDF para afixar</li>
              <li><span class="sf-check">✓</span> Visível no portal do aluno</li>
            </ul>
            <a href="https://wa.me/244926219731?text=Quero+ver+uma+demo+do+quadro+de+horários+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir demonstração →</a>
          </div>
          <div class="spanel-visual">
            <div class="screen-mock">
              <div class="screen-topbar">
                <span class="screen-title">📅 Quadro de Horários — 10ª A</span>
              </div>
              <div class="schedule-grid">
                <div class="sch-header"></div>
                <div class="sch-header">Segunda</div>
                <div class="sch-header">Terça</div>
                <div class="sch-header">Quarta</div>
                <div class="sch-header">Quinta</div>
                <div class="sch-header">Sexta</div>

                <div class="sch-time">07:00</div>
                <div class="sch-class blue">Matemática<small>Prof. Santos</small></div>
                <div class="sch-class green">Português<small>Prof. Lima</small></div>
                <div class="sch-class purple">Física<small>Prof. Costa</small></div>
                <div class="sch-class blue">Matemática<small>Prof. Santos</small></div>
                <div class="sch-class orange">História<small>Prof. Dias</small></div>

                <div class="sch-time">08:30</div>
                <div class="sch-class green">Português<small>Prof. Lima</small></div>
                <div class="sch-class orange">História<small>Prof. Dias</small></div>
                <div class="sch-class blue">Matemática<small>Prof. Santos</small></div>
                <div class="sch-class green">Português<small>Prof. Lima</small></div>
                <div class="sch-class purple">Física<small>Prof. Costa</small></div>

                <div class="sch-time">10:00</div>
                <div class="sch-class purple">Física<small>Prof. Costa</small></div>
                <div class="sch-class blue">Matemática<small>Prof. Santos</small></div>
                <div class="sch-class orange">História<small>Prof. Dias</small></div>
                <div class="sch-class purple">Física<small>Prof. Costa</small></div>
                <div class="sch-class blue">Matemática<small>Prof. Santos</small></div>
              </div>
            </div>
          </div>
        </div>

        <!-- NOTAS -->
        <div class="spanel" id="tab-notas">
          <div class="spanel-info">
            <h3>Notas, presenças e boletins</h3>
            <p>Registe notas por disciplina e trimestre, controle faltas e gere boletins automáticos em PDF prontos para entregar aos encarregados.</p>
            <ul class="spanel-features">
              <li><span class="sf-check">✓</span> Lançamento de notas por professor</li>
              <li><span class="sf-check">✓</span> Controlo de presenças e faltas</li>
              <li><span class="sf-check">✓</span> Cálculo automático de médias</li>
              <li><span class="sf-check">✓</span> Boletim em PDF personalizado</li>
              <li><span class="sf-check">✓</span> Aprovação/reprovação automática</li>
            </ul>
            <a href="https://wa.me/244926219731?text=Quero+ver+uma+demo+das+notas+e+boletins+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir demonstração →</a>
          </div>
          <div class="spanel-visual">
            <div class="screen-mock">
              <div class="screen-topbar">
                <span class="screen-title">📝 Boletim — Ana Neto · 10ª A</span>
                <div class="screen-actions"><span class="screen-btn">📄 Gerar PDF</span></div>
              </div>
              <div class="boletim">
                <div class="boletim-header">
                  <div class="boletim-avatar">AN</div>
                  <div>
                    <div class="boletim-name">Ana Neto</div>
                    <div class="boletim-sub">10ª A · 2025/2026</div>
                  </div>
                  <div class="boletim-badge green">Aprovada</div>
                </div>
                <div class="boletim-table">
                  <div class="boletim-th"><span>Disciplina</span><span>1º Tri</span><span>2º Tri</span><span>3º Tri</span><span>Média</span></div>
                  <div class="boletim-tr"><span>Matemática</span><span>14</span><span>16</span><span>15</span><span class="nota-good">15.0</span></div>
                  <div class="boletim-tr alt"><span>Português</span><span>17</span><span>18</span><span>16</span><span class="nota-good">17.0</span></div>
                  <div class="boletim-tr"><span>Física</span><span>12</span><span>13</span><span>14</span><span class="nota-ok">13.0</span></div>
                  <div class="boletim-tr alt"><span>História</span><span>16</span><span>15</span><span>17</span><span class="nota-good">16.0</span></div>
                  <div class="boletim-tr"><span>Inglês</span><span>18</span><span>19</span><span>18</span><span class="nota-good">18.3</span></div>
                </div>
                <div class="boletim-footer">
                  <span>Média Geral: <strong>15.9</strong></span>
                  <span>Faltas: <strong>3</strong></span>
                  <span>Situação: <strong class="green-text">Aprovada</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- NOTIFICACOES -->
        <div class="spanel" id="tab-notificacoes">
          <div class="spanel-info">
            <h3>Comunicação multicanal</h3>
            <p>Envie mensagens para alunos e encarregados por WhatsApp, SMS ou email. Automatize lembretes de pagamento, faltas e comunicados da escola.</p>
            <ul class="spanel-features">
              <li><span class="sf-check">✓</span> WhatsApp integrado</li>
              <li><span class="sf-check">✓</span> SMS e email em massa</li>
              <li><span class="sf-check">✓</span> Lembretes de pagamento automáticos</li>
              <li><span class="sf-check">✓</span> Avisos de faltas aos encarregados</li>
              <li><span class="sf-check">✓</span> Comunicados da direção</li>
            </ul>
            <a href="https://wa.me/244926219731?text=Quero+ver+uma+demo+das+notificações+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir demonstração →</a>
          </div>
          <div class="spanel-visual">
            <div class="screen-mock notif-mock">
              <div class="screen-topbar">
                <span class="screen-title">🔔 Notificações</span>
                <div class="screen-actions"><span class="screen-btn">+ Nova mensagem</span></div>
              </div>
              <div class="notif-channels">
                <div class="notif-channel active">💬 WhatsApp</div>
                <div class="notif-channel">📧 Email</div>
                <div class="notif-channel">📱 SMS</div>
              </div>
              <div class="notif-compose">
                <div class="notif-label">Para:</div>
                <div class="notif-chip">Todos os encarregados</div>
                <div class="notif-chip outline">+ Filtrar</div>
              </div>
              <div class="notif-msg-preview">
                <div class="notif-bubble">
                  <div class="notif-bubble-header">📌 Super Escola</div>
                  Olá *Maria*! Lembramos que a mensalidade de *João Silva* referente a *Maio 2026* no valor de *15.000 Kz* vence em *05/05/2026*. Obrigado!
                  <div class="notif-bubble-footer">✓✓ Enviado · 248 destinatários</div>
                </div>
              </div>
              <div class="notif-stats">
                <div class="nstat"><div class="nstat-n green">248</div><div class="nstat-l">Enviados</div></div>
                <div class="nstat"><div class="nstat-n blue">231</div><div class="nstat-l">Entregues</div></div>
                <div class="nstat"><div class="nstat-n purple">189</div><div class="nstat-l">Lidos</div></div>
              </div>
            </div>
          </div>
        </div>

        <!-- RELATORIOS -->
        <div class="spanel" id="tab-relatorios">
          <div class="spanel-info">
            <h3>Relatórios e dashboard</h3>
            <p>Tome decisões com base em dados reais. O dashboard mostra a situação da escola em tempo real — financeiro, académico e operacional.</p>
            <ul class="spanel-features">
              <li><span class="sf-check">✓</span> Dashboard em tempo real</li>
              <li><span class="sf-check">✓</span> Relatório de inadimplência</li>
              <li><span class="sf-check">✓</span> Relatório de presenças</li>
              <li><span class="sf-check">✓</span> Exportação em Excel e PDF</li>
              <li><span class="sf-check">✓</span> Comparativo mensal</li>
            </ul>
            <a href="https://wa.me/244926219731?text=Quero+ver+uma+demo+dos+relatórios+do+Super+Escola." class="btn btn-primary" target="_blank">Pedir demonstração →</a>
          </div>
          <div class="spanel-visual">
            <div class="screen-mock">
              <div class="screen-topbar">
                <span class="screen-title">📊 Dashboard — Maio 2026</span>
              </div>
              <div class="dash-kpis">
                <div class="dash-kpi"><div class="dash-kpi-n blue">248</div><div class="dash-kpi-l">Alunos Ativos</div></div>
                <div class="dash-kpi"><div class="dash-kpi-n green">94%</div><div class="dash-kpi-l">Taxa Pagamento</div></div>
                <div class="dash-kpi"><div class="dash-kpi-n purple">18</div><div class="dash-kpi-l">Turmas</div></div>
                <div class="dash-kpi"><div class="dash-kpi-n orange">97%</div><div class="dash-kpi-l">Presença Média</div></div>
              </div>
              <div class="dash-chart-area">
                <div class="dash-chart-label">Receita mensal (Kz)</div>
                <div class="dash-bars">
                  <div class="dash-bar-wrap"><div class="dash-bar" style="height:55%"></div><span>Jan</span></div>
                  <div class="dash-bar-wrap"><div class="dash-bar" style="height:65%"></div><span>Fev</span></div>
                  <div class="dash-bar-wrap"><div class="dash-bar" style="height:72%"></div><span>Mar</span></div>
                  <div class="dash-bar-wrap"><div class="dash-bar" style="height:60%"></div><span>Abr</span></div>
                  <div class="dash-bar-wrap active"><div class="dash-bar" style="height:90%"></div><span>Mai</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.showcase-content -->
    </div><!-- /.showcase-tabs -->
  </div>
</section>
