<?php 
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
include 'conexao.php';

// Buscar categorias do usuário com ID
$id_usuario = $_SESSION['id_usuario'];
$stmt = $pdo->prepare("SELECT id_categoria, nome_categoria FROM categoria_lancamento WHERE id_usuario = :id_usuario ORDER BY nome_categoria ASC");
$stmt->execute(['id_usuario' => $id_usuario]);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera mensagens e dados salvos na sessão (após erro ou sucesso)
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagem_erro = $_SESSION['mensagem_erro'] ?? null;
$erros_campos = $_SESSION['erros_campos'] ?? [];
$valores_form = $_SESSION['valores_form'] ?? [];

// Limpa as sessões após capturar
unset($_SESSION['mensagem_sucesso'], $_SESSION['mensagem_erro'], $_SESSION['erros_campos'], $_SESSION['valores_form']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Novo Lançamento</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/sidebar.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
</head>
<body>
<div class="layout has-sidebar fixed-sidebar fixed-header" style="display: flex; height: 100vh;">
  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content" style="flex-grow: 1; overflow-y: auto; padding: 20px;">
    <div class="container mt-4">
      <h3 class="mb-4 text-primary">Novo Lançamento Financeiro</h3>

      <?php if ($mensagem_sucesso): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong>Sucesso!</strong> <?= htmlspecialchars($mensagem_sucesso) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
      <?php endif; ?>

      <?php if ($mensagem_erro): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Erro!</strong> <?= htmlspecialchars($mensagem_erro) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
      <?php endif; ?>

      <form action="salvar_lancamento.php" method="POST" enctype="multipart/form-data" class="p-4 rounded shadow bg-white" novalidate>
        
        <div class="row mb-3 align-items-end">
          <div class="col-md-4">
            <label for="tipo_lancamento" class="form-label">Tipo</label>
            <select 
              id="tipo_lancamento" 
              name="tipo_lancamento" 
              class="form-select <?= isset($erros_campos['tipo_lancamento']) ? 'is-invalid' : '' ?>"
            >
              <option value="">Selecione</option>
              <option value="Despesa" <?= (isset($valores_form['tipo_lancamento']) && $valores_form['tipo_lancamento'] === 'Despesa') ? 'selected' : '' ?>>Despesa</option>
              <option value="Receita" <?= (isset($valores_form['tipo_lancamento']) && $valores_form['tipo_lancamento'] === 'Receita') ? 'selected' : '' ?>>Receita</option>
              <option value="Investimentos" <?= (isset($valores_form['tipo_lancamento']) && $valores_form['tipo_lancamento'] === 'Investimentos') ? 'selected' : '' ?>>Investimentos</option>
            </select>
            <div class="invalid-feedback"><?= $erros_campos['tipo_lancamento'] ?? '' ?></div>
          </div>

          <div class="col-md-4">
            <label for="pago" class="form-label">Pago?</label>
            <select 
              id="pago" 
              name="pago" 
              class="form-select"
              onchange="toggleDataPagamento()"
            >
              <option value="nao" <?= (isset($valores_form['pago']) && $valores_form['pago'] === 'nao') ? 'selected' : '' ?>>Não</option>
              <option value="sim" <?= (isset($valores_form['pago']) && $valores_form['pago'] === 'sim') ? 'selected' : '' ?>>Sim</option>
            </select>
          </div>

          <div class="col-md-4" id="divDataPagamento" style="display: none;">
            <label for="data_pagamento" class="form-label">Confirmar Data de Pagamento</label>
            <input 
              type="date" 
              id="data_pagamento" 
              name="data_pagamento" 
              class="form-control <?= isset($erros_campos['data_pagamento']) ? 'is-invalid' : '' ?>"
              value="<?= htmlspecialchars($valores_form['data_pagamento'] ?? '') ?>"
            >
            <div class="invalid-feedback"><?= $erros_campos['data_pagamento'] ?? '' ?></div>
          </div>
        </div>

        <div class="row mb-3 align-items-end">
          <!-- Campo Data da Aquisição, escondido inicialmente -->
          <div class="col-md-6" id="divDataInvestimento" style="display:none;">
            <label for="data_investimento" class="form-label">Data da Aquisição</label>
            <input 
              type="date" 
              name="data_investimento" 
              id="data_investimento"
              class="form-control <?= isset($erros_campos['data_investimento']) ? 'is-invalid' : '' ?>" 
              value="<?= htmlspecialchars($valores_form['data_investimento'] ?? '') ?>"
            >
            <div class="invalid-feedback"><?= $erros_campos['data_investimento'] ?? '' ?></div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6" id="divDataVencimento">
            <label for="data_venc" class="form-label">Data de Vencimento</label>
            <input 
              type="date" 
              name="data_venc" 
              id="data_venc"
              class="form-control <?= isset($erros_campos['data_venc']) ? 'is-invalid' : '' ?>" 
              value="<?= htmlspecialchars($valores_form['data_venc'] ?? '') ?>"
            >
            <div class="invalid-feedback"><?= $erros_campos['data_venc'] ?? '' ?></div>
          </div>

          <div class="col-md-6">
            <label for="categoria_lancamento" class="form-label">Categoria</label>
            <select 
              name="categoria_lancamento" 
              id="categoria_lancamento"
              class="form-select <?= isset($erros_campos['categoria_lancamento']) ? 'is-invalid' : '' ?>"
            >
              <option value="">Selecione</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= (isset($valores_form['categoria_lancamento']) && $valores_form['categoria_lancamento'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nome_categoria']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= $erros_campos['categoria_lancamento'] ?? '' ?></div>
          </div>
        </div>

        <div class="mb-3">
          <label for="valor_lancamento" class="form-label">Valor (R$)</label>
          <input 
            type="number" 
            name="valor_lancamento" 
            step="0.01" 
            class="form-control <?= isset($erros_campos['valor_lancamento']) ? 'is-invalid' : '' ?>" 
            value="<?= htmlspecialchars($valores_form['valor_lancamento'] ?? '') ?>"
          >
          <div class="invalid-feedback"><?= $erros_campos['valor_lancamento'] ?? '' ?></div>
        </div>

        <div class="mb-3">
          <label for="observacao_lancamento" class="form-label">Observação</label>
          <textarea 
            name="observacao_lancamento" 
            class="form-control" 
            rows="3"
          ><?= htmlspecialchars($valores_form['observacao_lancamento'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label for="arquivo_lancamento" class="form-label">Arquivo Comprovante</label>
          <input type="file" name="arquivo_lancamento" class="form-control">
        </div>

        <div class="form-check form-switch mb-3">
          <input 
            class="form-check-input" 
            type="checkbox" 
            id="recorrenteCheck" 
            name="recorrente" 
            <?= (isset($valores_form['recorrente']) && $valores_form['recorrente']) ? 'checked' : '' ?>
          >
          <label class="form-check-label" for="recorrenteCheck">Lançamento Recorrente?</label>
        </div>

        <div class="mb-3" id="quantidadeRecorrente" style="display: <?= (isset($valores_form['recorrente']) && $valores_form['recorrente']) ? 'block' : 'none' ?>;">
          <label for="quantidade_recorrente" class="form-label">Quantidade de Repetições</label>
          <input 
            type="number" 
            name="quantidade_recorrente" 
            class="form-control <?= isset($erros_campos['quantidade_recorrente']) ? 'is-invalid' : '' ?>" 
            min="1"
            value="<?= htmlspecialchars($valores_form['quantidade_recorrente'] ?? '') ?>"
          >
          <div class="invalid-feedback"><?= $erros_campos['quantidade_recorrente'] ?? '' ?></div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary me-2">Salvar</button>
          <button type="reset" class="btn btn-outline-secondary">Limpar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Categoria (existente) -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCategoriaLabel">Cadastrar Nova Categoria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body" id="modalCategoriaBody">
        <div class="text-center text-muted">Carregando...</div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sidebar.js"></script>
<script>
document.getElementById('recorrenteCheck').addEventListener('change', function () {
  document.getElementById('quantidadeRecorrente').style.display = this.checked ? 'block' : 'none';
});

const tipoLancamentoSelect = document.getElementById('tipo_lancamento');
const divDataInvestimento = document.getElementById('divDataInvestimento');
const divDataVencimento = document.getElementById('divDataVencimento');

function toggleFields() {
  if (tipoLancamentoSelect.value === 'Investimentos') {
    divDataInvestimento.style.display = 'block';
    divDataVencimento.style.display = 'none';
  } else {
    divDataInvestimento.style.display = 'none';
    divDataVencimento.style.display = 'block';
  }
}

toggleFields();

tipoLancamentoSelect.addEventListener('change', toggleFields);

function toggleDataPagamento() {
  const pago = document.getElementById('pago').value;
  const divDataPagamento = document.getElementById('divDataPagamento');
  const inputDataPagamento = document.getElementById('data_pagamento');
  if (pago === 'sim') {
    divDataPagamento.style.display = 'block';
    inputDataPagamento.required = true;
  } else {
    divDataPagamento.style.display = 'none';
    inputDataPagamento.required = false;
    inputDataPagamento.value = '';
  }
}

// Chama no carregamento para setar o estado correto do campo Data Pagamento
toggleDataPagamento();

// Carrega conteúdo da modal categoria via AJAX
document.getElementById('modalCategoria').addEventListener('show.bs.modal', function () {
  const modalBody = document.getElementById('modalCategoriaBody');
  fetch('nova_categoria.php')
    .then(response => response.text())
    .then(html => modalBody.innerHTML = html)
    .catch(() => modalBody.innerHTML = '<div class="text-danger">Erro ao carregar o formulário.</div>');
});
</script>
</body>
</html>
