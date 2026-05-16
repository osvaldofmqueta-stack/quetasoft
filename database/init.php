<?php
function getDb(): PDO {
    $dbPath = __DIR__ . '/superescola.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec("PRAGMA journal_mode=WAL;");

    $db->exec("CREATE TABLE IF NOT EXISTS leads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT,
        telefone TEXT NOT NULL,
        escola TEXT NOT NULL,
        mensagem TEXT,
        estado TEXT DEFAULT 'novo',
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS escolas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_escola TEXT NOT NULL,
        cidade TEXT NOT NULL,
        nome_diretor TEXT NOT NULL,
        cargo TEXT NOT NULL DEFAULT 'Diretor(a)',
        iniciais TEXT NOT NULL,
        cor_avatar TEXT NOT NULL DEFAULT '#4f46e5',
        foto_url TEXT,
        depoimento TEXT NOT NULL,
        estrelas INTEGER NOT NULL DEFAULT 5,
        ativo INTEGER NOT NULL DEFAULT 1,
        ordem INTEGER NOT NULL DEFAULT 0,
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $count = $db->query("SELECT COUNT(*) FROM escolas")->fetchColumn();
    if ($count == 0) {
        $depoimentos = [
            ['Colégio Estrela', 'Luanda', 'Maria Domingos', 'Diretora', 'MD', '#7c3aed',
             '"O Super Escola transformou completamente a nossa gestão. Antes perdíamos horas com planilhas, agora temos tudo automatizado e os encarregados ficam muito mais satisfeitos."', 5, 1],
            ['Academia Saber', 'Benguela', 'João Pedro', 'Diretor Geral', 'JP', '#0369a1',
             '"Implementámos o sistema em 2 dias e a equipa ficou a usar sem dificuldades logo na primeira semana. O suporte é excelente e sempre disponível."', 5, 2],
            ['Instituto Global', 'Luanda', 'Ana Sofia', 'Diretora Financeira', 'AS', '#059669',
             '"A funcionalidade de cobranças automáticas por WhatsApp reduziu a nossa inadimplência em mais de 50%. Não consigo imaginar gerir a escola sem o Super Escola."', 5, 3],
            ['Escola Futuro', 'Huambo', 'Carlos Manuel', 'Diretor Pedagógico', 'CM', '#b45309',
             '"A gestão de horários e notas ficou muito mais simples. Os professores adoraram e os pais conseguem acompanhar o progresso dos filhos em tempo real."', 5, 4],
        ];
        $stmt = $db->prepare("INSERT INTO escolas (nome_escola, cidade, nome_diretor, cargo, iniciais, cor_avatar, depoimento, estrelas, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($depoimentos as $d) {
            $stmt->execute($d);
        }
    }

    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        categoria TEXT NOT NULL DEFAULT 'Outros',
        resumo TEXT,
        conteudo TEXT,
        imagem_url TEXT,
        video_url TEXT,
        autor TEXT DEFAULT 'Equipa Super Escola',
        publicado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
        ativo INTEGER DEFAULT 1
    )");

    $count_posts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    if ($count_posts == 0) {
        $posts = [
            [
                'titulo' => 'Cinco técnicas de fecho para aumentar matrículas facilmente',
                'categoria' => 'Vendas',
                'resumo' => 'Descubra 5 técnicas práticas de fecho para aumentar a captação de alunos sem pressão e otimizar suas matrículas. Resultados comprovados por escolas angolanas.',
                'conteudo' => '<p>Aumentar as matrículas é o objetivo de toda instituição de ensino. Com as técnicas certas, é possível converter mais contactos em alunos sem pressão ou desconforto.</p><h2>1. Apresente o valor antes do preço</h2><p>Antes de falar em propinas, mostre ao encarregado o que a escola oferece: qualidade de ensino, sistema de gestão moderno, comunicação constante. Quando o valor é claro, o preço é justificado.</p><h2>2. Use depoimentos de outros encarregados</h2><p>Nada vende melhor do que a experiência real. Mostre testemunhos de pais satisfeitos, vídeos curtos de directores ou estatísticas de desempenho dos alunos.</p><h2>3. Crie urgência com vagas limitadas</h2><p>Informe que as vagas para a turma são limitadas. "Restam apenas 3 vagas para o 7.º ano" cria urgência real e incentiva a decisão mais rápida.</p><h2>4. Ofereça uma visita guiada</h2><p>Convide o encarregado e o aluno para conhecer a escola pessoalmente. O ambiente, os professores e as instalações fecham matrículas que nenhum panfleto conseguiria.</p><h2>5. Simplifique o processo de inscrição</h2><p>Com o Super Escola, a matrícula pode ser feita online em minutos. Menos burocracia significa mais conversões. O encarregado preenche os dados, assina digitalmente e pronto.</p><p>Aplicando estas técnicas em conjunto com uma ferramenta de gestão moderna, as escolas parceiras do Super Escola registaram um aumento médio de 35% nas matrículas no primeiro semestre.</p>',
                'imagem_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=500&fit=crop',
                'video_url' => '',
                'autor' => 'Equipa Super Escola',
                'publicado_em' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'titulo' => '5 vantagens da automatização de pagamentos em cursos livres',
                'categoria' => 'Finanças',
                'resumo' => 'Descubra como automatizar pagamentos recorrentes em cursos livres, reduzindo inadimplência e agilizando a gestão financeira da sua escola.',
                'conteudo' => '<p>A gestão financeira manual consome tempo, gera erros e aumenta a inadimplência. A automatização de pagamentos é a solução que milhares de escolas já adoptaram.</p><h2>1. Cobranças automáticas por WhatsApp</h2><p>Com o Super Escola, no dia do vencimento o sistema envia automaticamente uma mensagem de WhatsApp ao encarregado com o valor, a referência de pagamento e o link para pagar online. Sem precisar de ligar ou enviar mensagens manualmente.</p><h2>2. Redução da inadimplência em até 50%</h2><p>Escolas que implementaram lembretes automáticos registaram uma queda de 40 a 60% na inadimplência. O lembrete chega no momento certo, antes de o encarregado esquecer.</p><h2>3. Relatórios financeiros em tempo real</h2><p>Saiba a qualquer momento quanto recebeu, quanto está em aberto e qual a previsão de receita para os próximos meses. Tudo num dashboard visual e simples.</p><h2>4. Histórico completo de pagamentos</h2><p>Cada aluno tem um historial completo de pagamentos, com data, valor e método. Perfeito para resolver qualquer dúvida do encarregado em segundos.</p><h2>5. Menos trabalho para a secretaria</h2><p>Sem cobranças manuais, a secretaria pode focar-se no atendimento de qualidade. O sistema faz o trabalho repetitivo, a equipa faz o trabalho que realmente importa.</p>',
                'imagem_url' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&h=500&fit=crop',
                'video_url' => '',
                'autor' => 'Equipa Super Escola',
                'publicado_em' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'titulo' => 'Captação de Alunos: 9 Estratégias Práticas para Escolas',
                'categoria' => 'Vendas',
                'resumo' => 'Descubra estratégias para captar alunos em escolas, desde marketing digital até automação e análise de resultados para maximizar as inscrições.',
                'conteudo' => '<p>Captar alunos num mercado competitivo exige mais do que boa reputação. É preciso estratégia, presença digital e um processo de atendimento eficiente.</p><h2>1. Marque presença no Google</h2><p>Crie e optimize o perfil da sua escola no Google Meu Negócio. Encarregados pesquisam "melhor escola em [cidade]" — aparece nos resultados é fundamental.</p><h2>2. Invista em conteúdo nas redes sociais</h2><p>Partilhe fotos de actividades, depoimentos de pais, conquistas de alunos e bastidores da escola. Conteúdo autêntico gera confiança e atrai novos encarregados.</p><h2>3. Programa de indicação entre encarregados</h2><p>Ofereça um benefício (desconto numa mensalidade, material escolar) para cada aluno novo indicado por um encarregado actual. A boca a boca ainda é o canal mais poderoso.</p><h2>4. Jornadas abertas na escola</h2><p>Organize dias de portas abertas onde famílias possam conhecer a escola, os professores e as instalações. A experiência presencial fecha matrículas.</p><h2>5. WhatsApp como canal de atendimento rápido</h2><p>Responda dúvidas de encarregados em menos de 1 hora via WhatsApp. A rapidez do atendimento é um factor decisivo na escolha da escola.</p><h2>6. Processo de matrícula 100% digital</h2><p>Com o Super Escola, a matrícula pode ser feita em qualquer dispositivo, a qualquer hora. Menos barreiras no processo = mais matrículas concluídas.</p><h2>7. Anúncios segmentados no Facebook e Instagram</h2><p>Crie campanhas direcionadas para pais e encarregados na sua cidade. O custo por lead em escolas costuma ser muito acessível nestes canais.</p><h2>8. Parcerias com empresas locais</h2><p>Feche acordos com empresas locais para oferecer condições especiais aos filhos dos colaboradores. Uma fonte de matrículas constante e de qualidade.</p><h2>9. Acompanhe as métricas</h2><p>Use o dashboard do Super Escola para saber de onde vêm os seus leads, qual a taxa de conversão por canal e onde pode melhorar. Dados reais para decisões certas.</p>',
                'imagem_url' => 'https://images.unsplash.com/photo-1543269664-56d93c1b41a6?w=800&h=500&fit=crop',
                'video_url' => '',
                'autor' => 'Equipa Super Escola',
                'publicado_em' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'titulo' => 'Como organizar matrículas online e eliminar filas na secretaria',
                'categoria' => 'Gestão Escolar',
                'resumo' => 'Aprenda como digitalizar o processo de matrículas da sua escola, reduzindo filas, erros e o tempo de atendimento na secretaria.',
                'conteudo' => '<p>As filas na secretaria no período de matrículas são um dos maiores problemas das escolas. Com a digitalização do processo, é possível eliminar este problema e oferecer uma experiência muito melhor às famílias.</p><h2>Porquê digitalizar as matrículas?</h2><p>A matrícula presencial obriga as famílias a deslocar-se à escola, aguardar na fila, preencher formulários em papel e aguardar confirmação. Com a matrícula online, tudo é feito em casa, a qualquer hora, em poucos minutos.</p><h2>Como funciona no Super Escola?</h2><p>O processo é simples: o encarregado recebe um link de matrícula, preenche os dados do aluno, faz o upload dos documentos necessários e confirma. A secretaria recebe tudo organizado e apenas precisa de validar.</p><h2>Vantagens para a escola</h2><ul><li>Redução de 80% no tempo de atendimento na secretaria</li><li>Eliminação de erros de transcrição</li><li>Documentos organizados digitalmente</li><li>Histórico completo de cada matrícula</li></ul><h2>Vantagens para os encarregados</h2><ul><li>Sem deslocações desnecessárias</li><li>Processo rápido e intuitivo</li><li>Confirmação instantânea</li><li>Acesso ao comprovativo a qualquer momento</li></ul><p>Com o Super Escola, escolas que implementaram a matrícula online reportam uma satisfação dos encarregados 90% superior ao processo presencial.</p>',
                'imagem_url' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=500&fit=crop',
                'video_url' => '',
                'autor' => 'Equipa Super Escola',
                'publicado_em' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'titulo' => 'Gestão de notas e avaliações: elimine o papel da sua escola',
                'categoria' => 'Pedagógico',
                'resumo' => 'Como fazer o lançamento digital de notas, gerar pautas automáticas e partilhar resultados com encarregados directamente pelo sistema.',
                'conteudo' => '<p>O lançamento manual de notas em papel é um processo lento, sujeito a erros e difícil de auditar. A digitalização das avaliações transforma completamente o trabalho pedagógico.</p><h2>Lançamento de notas pelo professor</h2><p>Cada professor tem acesso ao seu portal no Super Escola, onde lança as notas directamente no sistema. Sem papéis, sem folhas de cálculo, sem reenvios por WhatsApp.</p><h2>Cálculo automático de médias</h2><p>O sistema calcula automaticamente as médias de acordo com a grelha de avaliação configurada pela escola. Seja qual for o modelo de avaliação, o Super Escola adapta-se.</p><h2>Pautas instantâneas</h2><p>Com um clique, a secretaria gera a pauta da turma em PDF, pronta para afixar ou partilhar. Fim das longas tardes a preparar documentos manualmente.</p><h2>Boletins para os encarregados</h2><p>No final de cada período, o sistema envia automaticamente o boletim do aluno para o encarregado por WhatsApp ou email. O pai acompanha o progresso do filho sem precisar de ir à escola.</p><h2>Historial académico completo</h2><p>Todas as notas ficam guardadas no sistema, criando um historial académico completo que pode ser consultado a qualquer momento, por qualquer utilizador autorizado.</p>',
                'imagem_url' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=500&fit=crop',
                'video_url' => '',
                'autor' => 'Equipa Super Escola',
                'publicado_em' => date('Y-m-d H:i:s', strtotime('-14 days')),
            ],
            [
                'titulo' => 'WhatsApp na escola: como automatizar cobranças e comunicados',
                'categoria' => 'Tecnologia Educacional',
                'resumo' => 'O WhatsApp é a ferramenta de comunicação mais usada em Angola. Saiba como a sua escola pode usar o WhatsApp para automatizar cobranças, enviar comunicados e melhorar o relacionamento.',
                'conteudo' => '<p>Com mais de 2 mil milhões de utilizadores no mundo e uma penetração altíssima em Angola, o WhatsApp é o canal de comunicação preferido das famílias. Usar esta ferramenta estrategicamente pode transformar a relação escola-família.</p><h2>Cobranças automáticas</h2><p>O Super Escola envia lembretes de pagamento automaticamente via WhatsApp. No dia do vencimento, o encarregado recebe uma mensagem com o valor, a referência e a forma de pagamento. Simples e eficiente.</p><h2>Comunicados instantâneos</h2><p>Envie comunicados para todos os encarregados de uma turma ou de toda a escola com um clique. Avisos de reuniões, eventos, feriados ou emergências chegam em segundos.</p><h2>Resposta rápida às famílias</h2><p>Com o histórico de cada aluno disponível no sistema, a secretaria responde qualquer dúvida do encarregado em segundos, sem precisar de procurar em papéis ou perguntar ao professor.</p><h2>Notificações de presença</h2><p>Configure o sistema para notificar automaticamente o encarregado quando o aluno falta às aulas. A comunicação proactiva previne problemas e mostra cuidado da escola.</p><h2>Como implementar no Super Escola?</h2><p>A integração com WhatsApp está incluída no Super Escola. Basta configurar o número da escola e definir os modelos de mensagem. O sistema trata do resto automaticamente, 24 horas por dia.</p>',
                'imagem_url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=500&fit=crop',
                'video_url' => '',
                'autor' => 'Equipa Super Escola',
                'publicado_em' => date('Y-m-d H:i:s', strtotime('-18 days')),
            ],
        ];
        $stmt = $db->prepare("INSERT INTO posts (titulo,categoria,resumo,conteudo,imagem_url,video_url,autor,publicado_em) VALUES (?,?,?,?,?,?,?,?)");
        foreach ($posts as $p) {
            $stmt->execute([$p['titulo'],$p['categoria'],$p['resumo'],$p['conteudo'],$p['imagem_url'],$p['video_url'],$p['autor'],$p['publicado_em']]);
        }
    }

    return $db;
}
