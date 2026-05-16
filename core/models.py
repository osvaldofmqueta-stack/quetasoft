from django.db import models
import json


class Lead(models.Model):
    nome = models.TextField()
    email = models.TextField(blank=True, default='')
    telefone = models.TextField()
    escola = models.TextField()
    mensagem = models.TextField(blank=True, default='')
    estado = models.TextField(default='novo')
    criado_em = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'leads'
        ordering = ['-criado_em']

    def initials(self):
        return self.nome[:2].upper() if self.nome else '??'

    def wa_number(self):
        import re
        return re.sub(r'\D', '', self.telefone)


class Escola(models.Model):
    nome_escola = models.TextField()
    cidade = models.TextField()
    nome_diretor = models.TextField()
    cargo = models.TextField(default='Diretor(a)')
    iniciais = models.TextField()
    cor_avatar = models.TextField(default='#4f46e5')
    foto_url = models.TextField(blank=True, default='')
    depoimento = models.TextField()
    estrelas = models.IntegerField(default=5)
    ativo = models.IntegerField(default=1)
    ordem = models.IntegerField(default=0)
    criado_em = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'escolas'
        ordering = ['ordem', 'id']

    def stars_display(self):
        return '★' * self.estrelas + '☆' * (5 - self.estrelas)


class Post(models.Model):
    titulo = models.TextField()
    categoria = models.TextField(default='Outros')
    resumo = models.TextField(blank=True, default='')
    conteudo = models.TextField(blank=True, default='')
    intro = models.TextField(blank=True, default='')
    pontos = models.TextField(blank=True, default='[]')
    imagem_url = models.TextField(blank=True, default='')
    video_url = models.TextField(blank=True, default='')
    autor = models.TextField(default='Equipa Super Escola')
    publicado_em = models.DateTimeField(auto_now_add=True)
    ativo = models.IntegerField(default=1)

    class Meta:
        db_table = 'posts'
        ordering = ['-publicado_em']

    def get_pontos(self):
        try:
            return [p for p in json.loads(self.pontos or '[]') if p]
        except Exception:
            return []

    def resumo_curto(self):
        return self.resumo[:130] if self.resumo else ''

    def youtube_embed(self):
        import re
        if not self.video_url:
            return ''
        m = re.search(r'(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})', self.video_url)
        return f'https://www.youtube.com/embed/{m.group(1)}?rel=0' if m else ''


class Pagamento(models.Model):
    escola_nome = models.TextField()
    cidade = models.TextField(default='Angola')
    pacote = models.TextField()
    valor = models.FloatField(default=0)
    metodo = models.TextField(default='Transferência Bancária')
    pago_em = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'pagamentos'
        ordering = ['-pago_em']


class Manual(models.Model):
    titulo = models.TextField()
    categoria = models.TextField(default='Geral')
    resumo = models.TextField(blank=True, default='')
    intro = models.TextField(blank=True, default='')
    passos = models.TextField(blank=True, default='[]')
    conteudo = models.TextField(blank=True, default='')
    imagem_url = models.TextField(blank=True, default='')
    autor = models.TextField(default='Equipa Super Escola')
    publicado_em = models.DateTimeField(auto_now_add=True)
    ativo = models.IntegerField(default=1)

    class Meta:
        db_table = 'manuais'
        ordering = ['-publicado_em']

    def get_passos(self):
        try:
            return [p for p in json.loads(self.passos or '[]') if p]
        except Exception:
            return []

    def resumo_curto(self):
        return self.resumo[:130] if self.resumo else ''


class Setting(models.Model):
    chave = models.TextField(primary_key=True)
    valor = models.TextField(blank=True, default='')

    class Meta:
        db_table = 'settings'

    @classmethod
    def get(cls, key, default=None):
        try:
            obj = cls.objects.get(chave=key)
            return json.loads(obj.valor) if obj.valor else (default or {})
        except cls.DoesNotExist:
            return default or {}

    @classmethod
    def save_setting(cls, key, value):
        cls.objects.update_or_create(
            chave=key,
            defaults={'valor': json.dumps(value, ensure_ascii=False)}
        )
