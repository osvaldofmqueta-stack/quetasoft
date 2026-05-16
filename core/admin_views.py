import json
from django.shortcuts import render, redirect, get_object_or_404
from django.conf import settings
from django.db.models import Count, Sum
from django.db.models.functions import TruncMonth
from django.utils import timezone
from .models import Lead, Escola, Post, Pagamento, Setting


def admin_required(view_func):
    def wrapper(request, *args, **kwargs):
        if not request.session.get('admin_logged'):
            return redirect('admin_login')
        return view_func(request, *args, **kwargs)
    wrapper.__name__ = view_func.__name__
    return wrapper


def admin_login(request):
    if request.session.get('admin_logged'):
        return redirect('admin_leads')
    error = ''
    if request.method == 'POST':
        user = request.POST.get('username', '').strip()
        pwd = request.POST.get('password', '').strip()
        if user == settings.ADMIN_USER and pwd == settings.ADMIN_PASS:
            request.session['admin_logged'] = True
            return redirect('admin_leads')
        error = 'Credenciais incorretas. Tente novamente.'
    return render(request, 'admin/login.html', {'erro': error})


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
