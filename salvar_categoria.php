<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_categoria = trim($_POST['nome_categoria'] ?? '');
    $id_usuario = $_SESSION['id_usuario'];

    if (!empty($nome_categoria)) {
        $stmt = $pdo->prepare("INSERT INTO categoria_lancamento (nome_categoria, id_usuario) VALUES (:nome, :id_usuario)");
        $stmt->execute([
            'nome' => $nome_categoria,
            'id_usuario' => $id_usuario
        ]);
        $id_categoria = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'id_categoria' => $id_categoria,
            'nome_categoria' => $nome_categoria
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Nome da categoria é obrigatório.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
exit;
