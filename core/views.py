import json
from django.shortcuts import render, get_object_or_404, redirect
from django.http import JsonResponse
from django.views.decorators.http import require_POST
from django.views.decorators.csrf import csrf_exempt
from .models import Lead, Escola, Post, Pagamento, Setting, Manual


def index(request):
    import re as _re
    escolas = Escola.objects.filter(ativo=1).order_by('ordem', 'id')
    posts = Post.objects.filter(ativo=1).order_by('-publicado_em')[:9]
    total_installs = Pagamento.objects.count()
    toast_payments = list(Pagamento.objects.order_by('-pago_em').values(
        'escola_nome', 'cidade', 'pacote', 'metodo', 'pago_em'
    )[:8])
    for p in toast_payments:
        if p['pago_em']:
            p['pago_em'] = str(p['pago_em'])
    categorias = ['Todos', 'Finanças', 'Gestão Escolar', 'Pedagógico', 'Tecnologia Educacional', 'Vendas', 'Outros']
    co = Setting.get('company', {})
    hero_stat_escolas = co.get('hero_escolas', '+500')
    hero_stat_alunos = co.get('hero_alunos', '+10k')
    hero_stat_disponibilidade = co.get('hero_disponibilidade', '99.9%')
    demo_video_url = co.get('demo_video_url', '')
    demo_embed = ''
    if demo_video_url:
        m = _re.search(r'(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})', demo_video_url)
        if m:
            demo_embed = f'https://www.youtube.com/embed/{m.group(1)}?rel=0'
    return render(request, 'index.html', {
        'escolas': escolas,
        'posts': posts,
        'total_installs': total_installs,
        'toast_payments_json': json.dumps(toast_payments, ensure_ascii=False),
        'categorias': categorias,
        'hero_stat_escolas': hero_stat_escolas,
        'hero_stat_alunos': hero_stat_alunos,
        'hero_stat_disponibilidade': hero_stat_disponibilidade,
        'demo_embed': demo_embed,
    })


def empresa(request):
    co = Setting.get('company', {})
    return render(request, 'empresa.html', {'co': co})


def cv(request):
    dev = Setting.get('developer', {})
    return render(request, 'cv.html', {'dev': dev})


def post_detail(request, post_id):
    post = get_object_or_404(Post, id=post_id, ativo=1)
    related = Post.objects.filter(ativo=1, categoria=post.categoria).exclude(id=post_id).order_by('-publicado_em')[:3]
    embed_url = post.youtube_embed()
    has_structured = bool(post.intro and post.intro.strip())
    pontos = post.get_pontos()
    return render(request, 'post.html', {
        'post': post,
        'related': related,
        'embed_url': embed_url,
        'has_structured': has_structured,
        'pontos': pontos,
    })


def manuais_list(request):
    busca = request.GET.get('q', '').strip()
    cat_filter = request.GET.get('cat', '').strip()
    qs = Manual.objects.filter(ativo=1)
    if busca:
        qs = qs.filter(titulo__icontains=busca) | Manual.objects.filter(ativo=1, resumo__icontains=busca)
    if cat_filter:
        qs = qs.filter(categoria=cat_filter)
    todos_manuais = qs.order_by('categoria', '-publicado_em')
    categorias_disponiveis = list(Manual.objects.filter(ativo=1).values_list('categoria', flat=True).distinct().order_by('categoria'))
    grupos = {}
    for m in todos_manuais:
        grupos.setdefault(m.categoria, []).append(m)
    recentes = Manual.objects.filter(ativo=1).order_by('-publicado_em')[:5]
    return render(request, 'manuais.html', {
        'grupos': grupos,
        'categorias': categorias_disponiveis,
        'recentes': recentes,
        'busca': busca,
        'cat_filter': cat_filter,
        'total': todos_manuais.count(),
    })


def manual_detail(request, manual_id):
    manual = get_object_or_404(Manual, id=manual_id, ativo=1)
    passos = manual.get_passos()
    recentes = Manual.objects.filter(ativo=1).exclude(id=manual_id).order_by('-publicado_em')[:5]
    relacionados = Manual.objects.filter(ativo=1, categoria=manual.categoria).exclude(id=manual_id).order_by('-publicado_em')[:4]
    from django.db.models.functions import TruncMonth
    from django.db.models import Count
    arquivos = Manual.objects.filter(ativo=1).annotate(mes=TruncMonth('publicado_em')).values('mes').annotate(total=Count('id')).order_by('-mes')[:6]
    return render(request, 'manual.html', {
        'manual': manual,
        'passos': passos,
        'recentes': recentes,
        'relacionados': relacionados,
        'arquivos': arquivos,
    })


@csrf_exempt
@require_POST
def submit_lead(request):
    nome = request.POST.get('nome', '').strip()
    email = request.POST.get('email', '').strip()
    telefone = request.POST.get('telefone', '').strip()
    escola = request.POST.get('escola', '').strip()
    mensagem = request.POST.get('mensagem', '').strip()
    if not nome or not telefone or not escola:
        return JsonResponse({'success': False, 'message': 'Preencha os campos obrigatórios.'})
    try:
        Lead.objects.create(nome=nome, email=email, telefone=telefone, escola=escola, mensagem=mensagem)
        return JsonResponse({'success': True, 'message': 'Pedido enviado com sucesso!'})
    except Exception:
        return JsonResponse({'success': False, 'message': 'Erro ao guardar. Tente novamente.'})
