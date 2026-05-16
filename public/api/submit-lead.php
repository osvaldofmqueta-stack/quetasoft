<?php
header('Content-Type: application/json');
require_once '../../database/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$escola   = trim($_POST['escola'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');

if (!$nome || !$telefone || !$escola) {
    echo json_encode(['success' => false, 'message' => 'Preencha os campos obrigatórios.']);
    exit;
}

try {
    $db = getDb();
    $stmt = $db->prepare("INSERT INTO leads (nome, email, telefone, escola, mensagem) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $telefone, $escola, $mensagem]);
    echo json_encode(['success' => true, 'message' => 'Pedido enviado com sucesso!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao guardar. Tente novamente.']);
}
