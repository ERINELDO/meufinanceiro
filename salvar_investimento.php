<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

include 'conexao.php';

$id_usuario = $_SESSION['id_usuario'];
$nome_investimento = trim($_POST['nome_investimento'] ?? '');

if (!$nome_investimento) {
    echo json_encode(['erro' => 'Nome do investimento é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO categoria_investimentos (nome_categoria, id_usuario) VALUES (:nome_categoria, :id_usuario)");
    $stmt->execute([
        'nome_categoria' => $nome_investimento,
        'id_usuario' => $id_usuario
    ]);
    $id_investimento = $pdo->lastInsertId();

    echo json_encode(['sucesso' => true, 'id_investimento' => $id_investimento]);
} catch (Exception $e) {
    echo json_encode(['erro' => 'Erro ao salvar investimento: ' . $e->getMessage()]);
}
