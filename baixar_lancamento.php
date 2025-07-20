<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lancamento = $_POST['id_lancamento'] ?? null;
    $data_pagamento = $_POST['data_pagamento'] ?? null;
    $id_usuario = $_SESSION['id_usuario'];

    if ($id_lancamento && $data_pagamento) {
        $stmt = $pdo->prepare("UPDATE lancamento_financeiro SET data_pagamento = :data_pagamento, status_lancamento = 'Pago' WHERE id_lancamento = :id AND id_usuario = :usuario");
        $stmt->execute([
            'data_pagamento' => $data_pagamento,
            'id' => $id_lancamento,
            'usuario' => $id_usuario
        ]);
        $_SESSION['mensagem_sucesso'] = 'Lançamento baixado com sucesso!';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao processar a baixa.';
    }
    header('Location: lancamento.php');
    exit;
}
