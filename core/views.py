import json
from django.shortcuts import render, get_object_or_404, redirect
from django.http import JsonResponse
from django.views.decorators.http import require_POST
from django.views.decorators.csrf import csrf_exempt
from .models import Lead, Escola, Post, Pagamento, Setting


def index(request):
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
    return render(request, 'index.html', {
        'escolas': escolas,
        'posts': posts,
        'total_installs': total_installs,
        'toast_payments_json': json.dumps(toast_payments, ensure_ascii=False),
        'categorias': categorias,
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
