from django.urls import path
from django.views.generic import RedirectView
from . import views, admin_views

urlpatterns = [
    # Compatibilidade com URL antiga do PHP
    path('admin/', RedirectView.as_view(url='/painel/', permanent=True)),
    path('admin/<path:rest>', RedirectView.as_view(url='/painel/', permanent=True)),

    # Public pages
    path('', views.index, name='index'),
    path('empresa/', views.empresa, name='empresa'),
    path('cv/', views.cv, name='cv'),
    path('artigo/<int:post_id>/', views.post_detail, name='post_detail'),

    # API
    path('api/submit-lead/', views.submit_lead, name='submit_lead'),

    # Admin panel
    path('painel/login/', admin_views.admin_login, name='admin_login'),
    path('painel/logout/', admin_views.admin_logout, name='admin_logout'),
    path('painel/', admin_views.leads_list, name='admin_leads'),
    path('painel/lead/<int:lead_id>/', admin_views.lead_detail, name='admin_lead_detail'),
    path('painel/stats/', admin_views.stats, name='admin_stats'),
    path('painel/schools/', admin_views.schools, name='admin_schools'),
    path('painel/posts/', admin_views.posts, name='admin_posts'),
    path('painel/pagamentos/', admin_views.pagamentos, name='admin_pagamentos'),
    path('painel/empresa/', admin_views.empresa_edit, name='admin_empresa'),
    path('painel/developer/', admin_views.developer_edit, name='admin_developer'),
    path('painel/alterar-senha/', admin_views.change_password, name='admin_change_password'),
    path('painel/recuperar-acesso/', admin_views.admin_forgot, name='admin_forgot'),
]
