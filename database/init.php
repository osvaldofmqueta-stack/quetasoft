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

    return $db;
}
