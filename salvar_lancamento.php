<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include 'conexao.php';

$id_usuario = $_SESSION['id_usuario'];

$erros_campos = [];
$valores_form = [];

// Captura e limpa valores do POST
$tipo_lancamento = trim($_POST['tipo_lancamento'] ?? '');
$data_investimento = trim($_POST['data_investimento'] ?? '');
$data_venc = trim($_POST['data_venc'] ?? '');
$categoria_lancamento = trim($_POST['categoria_lancamento'] ?? '');
$valor_lancamento = trim($_POST['valor_lancamento'] ?? '');
$observacao_lancamento = trim($_POST['observacao_lancamento'] ?? '');
$recorrente = isset($_POST['recorrente']) ? true : false;

$pago = trim($_POST['pago'] ?? 'nao');
$data_pagamento = trim($_POST['data_pagamento'] ?? '');

// Guardar valores para repopular
$valores_form = [
    'tipo_lancamento' => $tipo_lancamento,
    'data_investimento' => $data_investimento,
    'data_venc' => $data_venc,
    'categoria_lancamento' => $categoria_lancamento,
    'valor_lancamento' => $valor_lancamento,
    'observacao_lancamento' => $observacao_lancamento,
    'recorrente' => $recorrente,
    'pago' => $pago,
    'data_pagamento' => $data_pagamento,
];

// Validações básicas
if ($tipo_lancamento === '') {
    $erros_campos['tipo_lancamento'] = 'Selecione o tipo de lançamento.';
}

if ($valor_lancamento === '' || !is_numeric($valor_lancamento) || $valor_lancamento <= 0) {
    $erros_campos['valor_lancamento'] = 'Informe um valor válido.';
}

if ($tipo_lancamento === 'Investimentos') {
    if ($data_investimento === '') {
        $erros_campos['data_investimento'] = 'Informe a data da aquisição.';
    }
} else {
    if ($data_venc !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_venc)) {
        $erros_campos['data_venc'] = 'Data de vencimento inválida.';
    }
}

if ($categoria_lancamento !== '' && !ctype_digit($categoria_lancamento)) {
    $erros_campos['categoria_lancamento'] = 'Categoria inválida.';
}

// Validação específica para o campo Pago e Data de Pagamento
if ($pago === 'sim') {
    if ($data_pagamento === '') {
        $erros_campos['data_pagamento'] = 'Informe a data de pagamento.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_pagamento)) {
        $erros_campos['data_pagamento'] = 'Data de pagamento inválida.';
    }
} else {
    // Se não pago, limpar data_pagamento para inserir NULL no banco
    $data_pagamento = null;
}

if (!empty($erros_campos)) {
    $_SESSION['mensagem_erro'] = 'Por favor, corrija os erros abaixo.';
    $_SESSION['erros_campos'] = $erros_campos;
    $_SESSION['valores_form'] = $valores_form;
    header('Location: lancamento.php');
    exit;
}

// Preparar dados para inserção no banco
$data_investimento_db = ($tipo_lancamento === 'Investimentos') ? $data_investimento : null;
$data_venc_db = ($tipo_lancamento !== 'Investimentos') ? $data_venc : null;

// Definir status_lancamento conforme pago
$status_lancamento = ($pago === 'sim') ? 'Pago' : 'Pendente';

try {
    $sql = "INSERT INTO lancamento_financeiro 
        (id_usuario, tipo_lancamento, data_investimento, data_venc, categoria_lancamento, valor_lancamento, observacao_lancamento, recorrente, data_pagamento, status_lancamento) 
        VALUES 
        (:id_usuario, :tipo_lancamento, :data_investimento, :data_venc, :categoria_lancamento, :valor_lancamento, :observacao_lancamento, :recorrente, :data_pagamento, :status_lancamento)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':tipo_lancamento' => $tipo_lancamento,
        ':data_investimento' => $data_investimento_db,
        ':data_venc' => $data_venc_db,
        ':categoria_lancamento' => $categoria_lancamento !== '' ? $categoria_lancamento : null,
        ':valor_lancamento' => $valor_lancamento,
        ':observacao_lancamento' => $observacao_lancamento !== '' ? $observacao_lancamento : null,
        ':recorrente' => $recorrente ? 1 : 0,
        ':data_pagamento' => $data_pagamento,
        ':status_lancamento' => $status_lancamento,
    ]);

    $_SESSION['mensagem_sucesso'] = 'Lançamento salvo com sucesso!';
    header('Location: lancamento.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = 'Erro ao salvar no banco: ' . $e->getMessage();
    $_SESSION['valores_form'] = $valores_form;
    header('Location: lancamento.php');
    exit;
}
