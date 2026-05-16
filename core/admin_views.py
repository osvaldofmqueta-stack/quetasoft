import json
from django.shortcuts import render, redirect, get_object_or_404
from django.conf import settings
from django.db.models import Count, Sum
from django.db.models.functions import TruncMonth
from django.utils import timezone
from django.views.decorators.http import require_POST
from .models import Lead, Escola, Post, Pagamento, Setting, Manual, ChatSession, ChatMensagem


def admin_required(view_func):
    def wrapper(request, *args, **kwargs):
        if not request.session.get('admin_logged'):
            return redirect('admin_login')
        return view_func(request, *args, **kwargs)
    wrapper.__name__ = view_func.__name__
    return wrapper


def _check_credentials(user, pwd):
    creds = Setting.get('admin_credentials', {})
    custom_user = creds.get('username', '').strip()
    custom_pass = creds.get('password', '').strip()
    if custom_user and custom_pass:
        return user == custom_user and pwd == custom_pass
    return user == settings.ADMIN_USER and pwd == settings.ADMIN_PASS


def admin_login(request):
    if request.session.get('admin_logged'):
        return redirect('admin_leads')
    error = ''
    if request.method == 'POST':
        user = request.POST.get('username', '').strip()
        pwd = request.POST.get('password', '').strip()
        if _check_credentials(user, pwd):
            request.session['admin_logged'] = True
            return redirect('admin_leads')
        error = 'Credenciais incorretas. Tente novamente.'
    return render(request, 'admin/login.html', {'erro': error})


def admin_forgot(request):
    creds = Setting.get('admin_credentials', {})
    has_custom = bool(creds.get('username') and creds.get('password'))
    return render(request, 'admin/forgot.html', {'has_custom': has_custom})


@admin_required
def change_password(request):
    msg_type, msg_text = '', ''
    creds = Setting.get('admin_credentials', {})
    current_user = creds.get('username') or settings.ADMIN_USER

    if request.method == 'POST':
        senha_atual = request.POST.get('senha_atual', '').strip()
        novo_user = request.POST.get('novo_username', '').strip()
        nova_senha = request.POST.get('nova_senha', '').strip()
        confirmar = request.POST.get('confirmar_senha', '').strip()

        if not _check_credentials(
            creds.get('username') or settings.ADMIN_USER,
            senha_atual
        ):
            msg_type, msg_text = 'error', 'Senha atual incorreta.'
        elif not novo_user or not nova_senha:
            msg_type, msg_text = 'error', 'Utilizador e nova senha são obrigatórios.'
        elif nova_senha != confirmar:
            msg_type, msg_text = 'error', 'A nova senha e a confirmação não coincidem.'
        elif len(nova_senha) < 6:
            msg_type, msg_text = 'error', 'A senha deve ter pelo menos 6 caracteres.'
        else:
            Setting.save_setting('admin_credentials', {
                'username': novo_user,
                'password': nova_senha,
            })
            request.session['admin_logged'] = True
            msg_type, msg_text = 'success', 'Credenciais actualizadas com sucesso!'
            current_user = novo_user

    return render(request, 'admin/change_password.html', {
        'msg_type': msg_type,
        'msg_text': msg_text,
        'current_user': current_user,
        'active': 'change_password',
    })


def admin_logout(request):
    request.session.flush()
    return redirect('admin_login')


@admin_required
def leads_list(request):
    estado = request.GET.get('estado', '')
    busca = request.GET.get('busca', '').strip()
    qs = Lead.objects.all()
    if estado:
        qs = qs.filter(estado=estado)
    if busca:
        qs = qs.filter(nome__icontains=busca) | Lead.objects.filter(
            telefone__icontains=busca) | Lead.objects.filter(
            escola__icontains=busca) | Lead.objects.filter(email__icontains=busca)
        if estado:
            qs = qs.filter(estado=estado)
    leads = qs.order_by('-criado_em')

    # Handle actions
    acao = request.GET.get('acao', '')
    lead_id = request.GET.get('id', '')
    if acao and lead_id:
        lead_id = int(lead_id)
        if acao in ['novo', 'contactado', 'convertido', 'cancelado']:
            Lead.objects.filter(id=lead_id).update(estado=acao)
        elif acao == 'apagar':
            Lead.objects.filter(id=lead_id).delete()
        return redirect(request.path + ('?estado=' + estado if estado else ''))

    totais = {row['estado']: row['total'] for row in Lead.objects.values('estado').annotate(total=Count('id'))}
    total_all = Lead.objects.count()
    return render(request, 'admin/leads.html', {
        'leads': leads,
        'totais': totais,
        'total_all': total_all,
        'estado': estado,
        'busca': busca,
        'active': 'leads',
    })


@admin_required
def lead_detail(request, lead_id):
    lead = get_object_or_404(Lead, id=lead_id)
    if request.method == 'POST':
        novo_estado = request.POST.get('estado', '')
        if novo_estado in ['novo', 'contactado', 'convertido', 'cancelado']:
            lead.estado = novo_estado
            lead.save()
        return redirect('admin_lead_detail', lead_id=lead_id)
    import re
    wa_num = re.sub(r'\D', '', lead.telefone)
    estado_choices = [
        ('novo', 'Novo', '🔵'),
        ('contactado', 'Contactado', '🟡'),
        ('convertido', 'Convertido', '🟢'),
        ('cancelado', 'Cancelado', '🔴'),
    ]
    return render(request, 'admin/lead_detail.html', {
        'lead': lead,
        'wa_num': wa_num,
        'estado_choices': estado_choices,
        'active': 'leads',
    })


@admin_required
def stats(request):
    total = Lead.objects.count()
    novos = Lead.objects.filter(estado='novo').count()
    contactados = Lead.objects.filter(estado='contactado').count()
    convertidos = Lead.objects.filter(estado='convertido').count()
    cancelados = Lead.objects.filter(estado='cancelado').count()
    taxa_conv = round((convertidos / total) * 100, 1) if total > 0 else 0
    taxa_cont = round(((contactados + convertidos) / total) * 100, 1) if total > 0 else 0
    taxa_cancel = round((cancelados / total) * 100, 1) if total > 0 else 0
    funil_pct = round(((contactados + convertidos) / total) * 100, 0) if total > 0 else 0
    conv_pct = round((convertidos / total) * 100, 0) if total > 0 else 0
    recentes = Lead.objects.order_by('-criado_em')[:5]
    return render(request, 'admin/stats.html', {
        'total': total,
        'novos': novos,
        'contactados': contactados,
        'convertidos': convertidos,
        'cancelados': cancelados,
        'taxa_conv': taxa_conv,
        'taxa_cont': taxa_cont,
        'taxa_cancel': taxa_cancel,
        'funil_pct': funil_pct,
        'conv_pct': conv_pct,
        'funil_cont': contactados + convertidos,
        'recentes': recentes,
        'active': 'stats',
    })


@admin_required
def schools(request):
    msg_type, msg_text = '', ''
    edit = None
    if request.method == 'POST':
        action = request.POST.get('action', '')
        if action in ('create', 'edit'):
            data = {
                'nome_escola': request.POST.get('nome_escola', '').strip(),
                'cidade': request.POST.get('cidade', '').strip(),
                'nome_diretor': request.POST.get('nome_diretor', '').strip(),
                'cargo': request.POST.get('cargo', 'Diretor(a)').strip(),
                'iniciais': request.POST.get('iniciais', '').strip().upper(),
                'cor_avatar': request.POST.get('cor_avatar', '#4f46e5').strip(),
                'foto_url': request.POST.get('foto_url', '').strip(),
                'depoimento': request.POST.get('depoimento', '').strip(),
                'estrelas': int(request.POST.get('estrelas', 5)),
                'ativo': 1 if request.POST.get('ativo') else 0,
                'ordem': int(request.POST.get('ordem', 0)),
            }
            if not data['nome_escola'] or not data['cidade'] or not data['nome_diretor'] or not data['depoimento']:
                msg_type, msg_text = 'error', 'Preencha todos os campos obrigatórios.'
            else:
                if action == 'create':
                    Escola.objects.create(**data)
                    msg_type, msg_text = 'success', 'Escola adicionada com sucesso!'
                else:
                    eid = int(request.POST.get('id', 0))
                    Escola.objects.filter(id=eid).update(**data)
                    msg_type, msg_text = 'success', 'Escola actualizada com sucesso!'
        elif action == 'delete':
            Escola.objects.filter(id=int(request.POST.get('id', 0))).delete()
            msg_type, msg_text = 'success', 'Escola removida.'
        elif action == 'toggle':
            eid = int(request.POST.get('id', 0))
            e = Escola.objects.get(id=eid)
            e.ativo = 0 if e.ativo else 1
            e.save()
            return redirect('admin_schools')
    if request.GET.get('edit'):
        edit = get_object_or_404(Escola, id=int(request.GET['edit']))
    escolas = Escola.objects.all().order_by('ordem', 'id')
    return render(request, 'admin/schools.html', {
        'escolas': escolas,
        'edit': edit,
        'msg_type': msg_type,
        'msg_text': msg_text,
        'active': 'schools',
    })


@admin_required
def posts(request):
    msg_type, msg_text = '', ''
    edit = None
    cats = ['Finanças', 'Gestão Escolar', 'Pedagógico', 'Tecnologia Educacional', 'Vendas', 'Outros']
    if request.method == 'POST':
        action = request.POST.get('action', '')
        if action in ('create', 'edit'):
            titulo = request.POST.get('titulo', '').strip()
            categoria = request.POST.get('categoria', 'Outros').strip()
            resumo = request.POST.get('resumo', '').strip()
            intro = request.POST.get('intro', '').strip()
            pontos_raw = [p.strip() for p in request.POST.getlist('pontos') if p.strip()]
            pontos_json = json.dumps(pontos_raw, ensure_ascii=False)
            autor = request.POST.get('autor', 'Equipa Super Escola').strip()
            ativo = 1 if request.POST.get('ativo') else 0
            media_type = request.POST.get('media_type', 'imagem')
            imagem = request.POST.get('imagem_url', '').strip() if media_type == 'imagem' else ''
            video = request.POST.get('video_url', '').strip() if media_type == 'video' else ''
            conteudo = ''
            if intro:
                for para in intro.split('\n'):
                    p = para.strip()
                    if p:
                        conteudo += f'<p>{p}</p>'
            if pontos_raw:
                conteudo += '<ul>' + ''.join(f'<li>{pt}</li>' for pt in pontos_raw) + '</ul>'
            if not titulo:
                msg_type, msg_text = 'error', 'O título é obrigatório.'
            else:
                data = dict(titulo=titulo, categoria=categoria, resumo=resumo,
                            conteudo=conteudo, intro=intro, pontos=pontos_json,
                            imagem_url=imagem, video_url=video, autor=autor, ativo=ativo)
                if action == 'create':
                    Post.objects.create(**data)
                    msg_type, msg_text = 'success', 'Artigo publicado com sucesso!'
                else:
                    pid = int(request.POST.get('id', 0))
                    Post.objects.filter(id=pid).update(**data)
                    msg_type, msg_text = 'success', 'Artigo actualizado com sucesso!'
        elif action == 'delete':
            Post.objects.filter(id=int(request.POST.get('id', 0))).delete()
            msg_type, msg_text = 'success', 'Artigo removido.'
        elif action == 'toggle':
            pid = int(request.POST.get('id', 0))
            p = Post.objects.get(id=pid)
            p.ativo = 0 if p.ativo else 1
            p.save()
            return redirect('admin_posts')
    if request.GET.get('edit'):
        edit = get_object_or_404(Post, id=int(request.GET['edit']))
    all_posts = Post.objects.all().order_by('-publicado_em')
    return render(request, 'admin/posts.html', {
        'posts': all_posts,
        'edit': edit,
        'cats': cats,
        'msg_type': msg_type,
        'msg_text': msg_text,
        'active': 'posts',
    })


@admin_required
def manuais(request):
    msg_type, msg_text = '', ''
    edit = None
    cats = ['Matrículas', 'Financeiro', 'Pedagógico', 'Comunicação', 'Relatórios', 'Configurações', 'Outros']
    if request.method == 'POST':
        action = request.POST.get('action', '')
        if action in ('create', 'edit'):
            titulo = request.POST.get('titulo', '').strip()
            categoria = request.POST.get('categoria', 'Outros').strip()
            resumo = request.POST.get('resumo', '').strip()
            intro = request.POST.get('intro', '').strip()
            passos_raw = [p.strip() for p in request.POST.getlist('passos') if p.strip()]
            passos_json = json.dumps(passos_raw, ensure_ascii=False)
            autor = request.POST.get('autor', 'Equipa Super Escola').strip()
            imagem_url = request.POST.get('imagem_url', '').strip()
            ativo = 1 if request.POST.get('ativo') else 0
            if not titulo:
                msg_type, msg_text = 'error', 'O título é obrigatório.'
            else:
                data = dict(titulo=titulo, categoria=categoria, resumo=resumo,
                            intro=intro, passos=passos_json, autor=autor,
                            imagem_url=imagem_url, ativo=ativo)
                if action == 'create':
                    Manual.objects.create(**data)
                    msg_type, msg_text = 'success', 'Manual publicado com sucesso!'
                else:
                    mid = int(request.POST.get('id', 0))
                    Manual.objects.filter(id=mid).update(**data)
                    msg_type, msg_text = 'success', 'Manual actualizado com sucesso!'
        elif action == 'delete':
            Manual.objects.filter(id=int(request.POST.get('id', 0))).delete()
            msg_type, msg_text = 'success', 'Manual removido.'
        elif action == 'toggle':
            mid = int(request.POST.get('id', 0))
            m = Manual.objects.get(id=mid)
            m.ativo = 0 if m.ativo else 1
            m.save()
            return redirect('admin_manuais')
    if request.GET.get('edit'):
        edit = get_object_or_404(Manual, id=int(request.GET['edit']))
    all_manuais = Manual.objects.all().order_by('-publicado_em')
    return render(request, 'admin/manuais.html', {
        'manuais': all_manuais,
        'edit': edit,
        'cats': cats,
        'msg_type': msg_type,
        'msg_text': msg_text,
        'active': 'manuais',
    })


@admin_required
def pagamentos(request):
    msg_type, msg_text = '', ''
    pacotes = {'Básico': 299, 'Profissional': 499, 'Completo': 799}
    metodos = ['Transferência Bancária', 'TPA', 'Multicaixa', 'Referência Multicaixa', 'Outro']
    if request.method == 'POST':
        action = request.POST.get('action', '')
        if action == 'create':
            escola = request.POST.get('escola_nome', '').strip()
            cidade = request.POST.get('cidade', '').strip()
            pacote = request.POST.get('pacote', '').strip()
            valor = float(request.POST.get('valor', 0) or 0)
            metodo = request.POST.get('metodo', 'Transferência Bancária').strip()
            if escola and pacote:
                Pagamento.objects.create(escola_nome=escola, cidade=cidade, pacote=pacote, valor=valor, metodo=metodo)
                msg_type, msg_text = 'success', 'Pagamento registado com sucesso!'
            else:
                msg_type, msg_text = 'error', 'Escola e pacote são obrigatórios.'
        elif action == 'delete':
            Pagamento.objects.filter(id=int(request.POST.get('id', 0))).delete()
            msg_type, msg_text = 'success', 'Registo removido.'
    total_recebido = Pagamento.objects.aggregate(t=Sum('valor'))['t'] or 0
    count_total = Pagamento.objects.count()
    breakdown = list(Pagamento.objects.values('pacote').annotate(n=Count('id'), total=Sum('valor')).order_by('-total'))
    all_pagamentos = Pagamento.objects.all().order_by('-pago_em')
    return render(request, 'admin/pagamentos.html', {
        'pagamentos': all_pagamentos,
        'total_recebido': total_recebido,
        'count_total': count_total,
        'breakdown': breakdown,
        'pacotes': pacotes,
        'metodos': metodos,
        'msg_type': msg_type,
        'msg_text': msg_text,
        'active': 'pagamentos',
    })


@admin_required
def empresa_edit(request):
    msg_type, msg_text = '', ''
    co = Setting.get('company', {})
    if request.method == 'POST':
        co = {
            'nome': request.POST.get('nome', '').strip(),
            'slogan': request.POST.get('slogan', '').strip(),
            'logo_url': request.POST.get('logo_url', '').strip(),
            'descricao': request.POST.get('descricao', '').strip(),
            'morada': request.POST.get('morada', '').strip(),
            'telefone': request.POST.get('telefone', '').strip(),
            'email': request.POST.get('email', '').strip(),
            'website': request.POST.get('website', '').strip(),
            'facebook': request.POST.get('facebook', '').strip(),
            'instagram': request.POST.get('instagram', '').strip(),
            'linkedin': request.POST.get('linkedin', '').strip(),
            'ano_fundacao': request.POST.get('ano_fundacao', '').strip(),
            'missao': request.POST.get('missao', '').strip(),
            'visao': request.POST.get('visao', '').strip(),
            'valores': request.POST.get('valores', '').strip(),
            'hero_escolas': request.POST.get('hero_escolas', '+500').strip(),
            'hero_alunos': request.POST.get('hero_alunos', '+10k').strip(),
            'hero_disponibilidade': request.POST.get('hero_disponibilidade', '99.9%').strip(),
            'demo_video_url': request.POST.get('demo_video_url', '').strip(),
        }
        Setting.save_setting('company', co)
        msg_type, msg_text = 'success', 'Dados da empresa actualizados com sucesso!'
    return render(request, 'admin/empresa.html', {
        'co': co,
        'msg_type': msg_type,
        'msg_text': msg_text,
        'active': 'empresa',
    })


@admin_required
def developer_edit(request):
    msg_type, msg_text = '', ''
    dev = Setting.get('developer', {})
    if request.method == 'POST':
        skills = [s.strip() for s in request.POST.get('skills', '').split(',') if s.strip()]
        exps = []
        cargos = request.POST.getlist('exp_cargo')
        empresas = request.POST.getlist('exp_empresa')
        periodos = request.POST.getlist('exp_periodo')
        descs = request.POST.getlist('exp_descricao')
        for i in range(len(cargos)):
            if cargos[i].strip():
                exps.append({'cargo': cargos[i].strip(), 'empresa': empresas[i].strip() if i < len(empresas) else '',
                             'periodo': periodos[i].strip() if i < len(periodos) else '',
                             'descricao': descs[i].strip() if i < len(descs) else ''})
        projs = []
        pnomes = request.POST.getlist('proj_nome')
        purls = request.POST.getlist('proj_url')
        pdescs = request.POST.getlist('proj_descricao')
        for i in range(len(pnomes)):
            if pnomes[i].strip():
                projs.append({'nome': pnomes[i].strip(), 'url': purls[i].strip() if i < len(purls) else '',
                              'descricao': pdescs[i].strip() if i < len(pdescs) else ''})
        dev = {
            'nome': request.POST.get('nome', '').strip(),
            'cargo': request.POST.get('cargo', '').strip(),
            'foto_url': request.POST.get('foto_url', '').strip(),
            'bio': request.POST.get('bio', '').strip(),
            'localizacao': request.POST.get('localizacao', '').strip(),
            'whatsapp': request.POST.get('whatsapp', '').strip(),
            'email': request.POST.get('email', '').strip(),
            'linkedin': request.POST.get('linkedin', '').strip(),
            'github': request.POST.get('github', '').strip(),
            'skills': skills,
            'experiencias': exps,
            'projetos': projs,
        }
        Setting.save_setting('developer', dev)
        msg_type, msg_text = 'success', 'Currículo actualizado com sucesso!'
    return render(request, 'admin/developer.html', {
        'dev': dev,
        'msg_type': msg_type,
        'msg_text': msg_text,
        'active': 'developer',
    })


# ─────────────────────────────────────────────
# CHAT ADMIN VIEWS
# ─────────────────────────────────────────────

@admin_required
def chat_list(request):
    sessions = ChatSession.objects.prefetch_related('mensagens').order_by('-ultima_atividade')
    total_nao_lidas = sum(s.nao_lidas() for s in sessions)
    return render(request, 'admin/chat.html', {
        'sessions': sessions,
        'total_nao_lidas': total_nao_lidas,
        'active': 'chat',
    })


@admin_required
def chat_room(request, session_key):
    session = get_object_or_404(ChatSession, session_key=session_key)
    session.mensagens.filter(eh_admin=False, lido=False).update(lido=True)
    mensagens = session.mensagens.order_by('criado_em')
    return render(request, 'admin/chat_room.html', {
        'session': session,
        'mensagens': mensagens,
        'active': 'chat',
    })


@admin_required
@require_POST
def chat_admin_send(request, session_key):
    session = get_object_or_404(ChatSession, session_key=session_key)
    try:
        data = json.loads(request.body)
        texto = data.get('texto', '').strip()
    except Exception:
        texto = request.POST.get('texto', '').strip()
    if texto:
        msg = ChatMensagem.objects.create(sessao=session, texto=texto, eh_admin=True, lido=True)
        session.save()
        return JsonResponse({'success': True, 'id': msg.id, 'hora': msg.criado_em.strftime('%H:%M')})
    return JsonResponse({'success': False})


@admin_required
def chat_admin_poll(request, session_key):
    session = get_object_or_404(ChatSession, session_key=session_key)
    since = int(request.GET.get('since', 0))
    msgs = session.mensagens.filter(id__gt=since)
    msgs.filter(eh_admin=False, lido=False).update(lido=True)
    data = []
    for m in msgs:
        data.append({
            'id': m.id,
            'texto': m.texto,
            'eh_admin': m.eh_admin,
            'hora': m.criado_em.strftime('%H:%M'),
        })
    return JsonResponse({'mensagens': data})


@admin_required
def chat_badge(request):
    total = sum(s.nao_lidas() for s in ChatSession.objects.prefetch_related('mensagens'))
    return JsonResponse({'total': total})
