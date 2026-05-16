# Super Escola — Landing Page

## Visão Geral
Landing page de marketing para o sistema de gestão académica "Super Escola", migrada para Django (Python).

## Stack Técnica
- **Framework**: Django 5.2.14 (Python 3.11)
- **Base de dados**: SQLite (`database/superescola.db`)
- **Static files**: WhiteNoise
- **Sessões**: File-based session engine

## Estrutura do Projeto
```
superescola/        — configuração Django (settings.py, urls.py)
core/
  models.py         — Lead, Escola, Post, Pagamento, Setting
  views.py          — páginas públicas
  admin_views.py    — painel administrativo
  urls.py           — rotas
  templates/        — templates HTML
    index.html      — landing page principal
    empresa.html    — página da empresa
    cv.html         — currículo do desenvolvedor
    post.html       — detalhe de artigo
    admin/          — templates do painel
  static/
    css/style.css   — estilos da landing page
    css/admin.css   — estilos do painel admin
    js/main.js      — JavaScript da landing page
database/
  superescola.db    — base de dados SQLite
```

## URLs
- `/` — Landing page
- `/empresa/` — Página da empresa
- `/cv/` — Currículo do desenvolvedor
- `/artigo/<id>/` — Detalhe de artigo do blog
- `/api/submit-lead/` — API POST para leads
- `/painel/login/` — Login do painel admin
- `/painel/` — Dashboard de leads
- `/painel/stats/` — Estatísticas
- `/painel/schools/` — Escolas & testemunhos
- `/painel/posts/` — Artigos do blog
- `/painel/pagamentos/` — Pagamentos
- `/painel/empresa/` — Dados da empresa
- `/painel/developer/` — Currículo

## Como Correr
O workflow "Start application" executa: `python3 manage.py runserver 0.0.0.0:5000`

## User Preferences
- Idioma: Português
- Contacto WhatsApp: +244 926 219 731
- Estilo: moderno, inspirado no site Traus (traus.com.br)
- Admin URL: `/painel/` (credenciais via variáveis de ambiente ADMIN_USER / ADMIN_PASS)
