<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
include 'conexao.php';

$id_usuario = $_SESSION['id_usuario'];

// Filtros
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$tipo_filtro = $_GET['tipo_lancamento'] ?? '';
$categoria_filtro = $_GET['categoria_lancamento'] ?? '';
$status_filtro = $_GET['status_lancamento'] ?? '';

// Buscar categorias para filtro
$stmtCat = $pdo->prepare("SELECT id_categoria, nome_categoria FROM categoria_lancamento WHERE id_usuario = :id_usuario ORDER BY nome_categoria ASC");
$stmtCat->execute(['id_usuario' => $id_usuario]);
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Monta SQL com filtros
$sql = "SELECT 
            lf.*, 
            c.nome_categoria
        FROM lancamento_financeiro lf
        JOIN categoria_lancamento c ON c.id_categoria = lf.categoria_lancamento
        WHERE lf.id_usuario = :id_usuario";

$params = ['id_usuario' => $id_usuario];

if ($data_inicio) {
    $sql .= " AND lf.data_venc >= :data_inicio";
    $params['data_inicio'] = $data_inicio;
}
if ($data_fim) {
    $sql .= " AND lf.data_venc <= :data_fim";
    $params['data_fim'] = $data_fim;
}
if ($tipo_filtro) {
    $sql .= " AND lf.tipo_lancamento = :tipo_lancamento";
    $params['tipo_lancamento'] = $tipo_filtro;
}
if ($categoria_filtro) {
    $sql .= " AND lf.categoria_lancamento = :categoria_lancamento";
    $params['categoria_lancamento'] = $categoria_filtro;
}
if ($status_filtro) {
    $sql .= " AND lf.status_lancamento = :status_lancamento";
    $params['status_lancamento'] = $status_filtro;
}

$sql .= " ORDER BY lf.data_venc DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lancamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular soma total dos valores filtrados
$sqlSum = "SELECT SUM(valor_lancamento) as total_valor FROM lancamento_financeiro WHERE id_usuario = :id_usuario";
$paramsSum = ['id_usuario' => $id_usuario];

if ($data_inicio) {
    $sqlSum .= " AND data_venc >= :data_inicio";
    $paramsSum['data_inicio'] = $data_inicio;
}
if ($data_fim) {
    $sqlSum .= " AND data_venc <= :data_fim";
    $paramsSum['data_fim'] = $data_fim;
}
if ($tipo_filtro) {
    $sqlSum .= " AND tipo_lancamento = :tipo_lancamento";
    $paramsSum['tipo_lancamento'] = $tipo_filtro;
}
if ($categoria_filtro) {
    $sqlSum .= " AND categoria_lancamento = :categoria_lancamento";
    $paramsSum['categoria_lancamento'] = $categoria_filtro;
}
if ($status_filtro) {
    $sqlSum .= " AND status_lancamento = :status_lancamento";
    $paramsSum['status_lancamento'] = $status_filtro;
}

$stmtSum = $pdo->prepare($sqlSum);
$stmtSum->execute($paramsSum);
$total = $stmtSum->fetchColumn();

function corStatus($status) {
    return match($status) {
        'Pago' => 'bg-success',
        'Cancelado' => 'bg-danger',
        'Pendente' => 'bg-warning text-dark',
        default => 'bg-secondary'
    };
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Listar Lançamentos</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/sidebar.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
</head>
<body>
<div class="layout has-sidebar fixed-sidebar fixed-header" style="display: flex; height: 100vh;">
  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content" style="flex-grow: 1; overflow-y: auto; padding: 20px;">
    <div class="container mt-4">
      <h3 class="mb-4 text-primary">Lançamentos Financeiros</h3>

      <!-- Filtros -->
      <form method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-2">
          <label class="form-label">Data Início</label>
          <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Data Fim</label>
          <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Tipo</label>
          <select name="tipo_lancamento" class="form-select">
            <option value="">Todos</option>
            <option value="Despesa" <?= $tipo_filtro == 'Despesa' ? 'selected' : '' ?>>Despesa</option>
            <option value="Receita" <?= $tipo_filtro == 'Receita' ? 'selected' : '' ?>>Receita</option>
            <option value="Investimentos" <?= $tipo_filtro == 'Investimentos' ? 'selected' : '' ?>>Investimentos</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Categoria</label>
          <select name="categoria_lancamento" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id_categoria'] ?>" <?= $categoria_filtro == $cat['id_categoria'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nome_categoria']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select name="status_lancamento" class="form-select">
            <option value="">Todos</option>
            <option value="Pendente" <?= $status_filtro == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
            <option value="Pago" <?= $status_filtro == 'Pago' ? 'selected' : '' ?>>Pago</option>
            <option value="Cancelado" <?= $status_filtro == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
          </select>
        </div>
        <div class="col-md-2 d-flex">
          <button type="submit" class="btn btn-primary me-2">Filtrar</button>
          <a href="listar_lancamentos.php" class="btn btn-outline-secondary">Limpar</a>
        </div>
      </form>

      <?php if ($lancamentos): ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Pagamento</th>
                <th>Observação</th>
                <th>Status</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lancamentos as $l): ?>
                <tr>
                  <td><?= htmlspecialchars($l['nome_categoria']) ?></td>
                  <td><?= htmlspecialchars($l['tipo_lancamento']) ?></td>
                  <td>R$ <?= number_format($l['valor_lancamento'], 2, ',', '.') ?></td>
                  <td><?= date('d/m/Y', strtotime($l['data_venc'])) ?></td>
                  <td>
                    <?php
                      $data_pg = $l['data_pagamento'];
                      echo ($data_pg && $data_pg !== '0000-00-00' && $data_pg !== '1970-01-01') 
                          ? date('d/m/Y', strtotime($data_pg)) 
                          : '';
                    ?>
                  </td>
                  <td><?= nl2br(htmlspecialchars($l['observacao_lancamento'])) ?></td>
                  <td><span class="badge <?= corStatus($l['status_lancamento']) ?>"><?= $l['status_lancamento'] ?></span></td>
                  <td>
                    <?php if (!empty($l['arquivo_lancamento'])): ?>
                      <a href="<?= htmlspecialchars($l['arquivo_lancamento']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Comprovante</a>
                    <?php endif; ?>
                    <?php if ($l['status_lancamento'] === 'Pendente'): ?>
                      <button class="btn btn-sm btn-success mt-1" data-bs-toggle="modal" data-bs-target="#modalBaixa" data-id="<?= $l['id_lancamento'] ?>">Baixar</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="2" class="text-end">Total:</th>
                <th>R$ <?= number_format($total ?? 0, 2, ',', '.') ?></th>
                <th colspan="5"></th>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-info">Nenhum lançamento encontrado com os filtros aplicados.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal de Baixa -->
<div class="modal fade" id="modalBaixa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="baixar_lancamento.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Baixar Lançamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_lancamento" id="modalIdLancamento">
          <div class="mb-3">
            <label class="form-label">Data do Pagamento</label>
            <input type="date" name="data_pagamento" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Confirmar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sidebar.js"></script>
<script>
document.getElementById('modalBaixa').addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const idLancamento = button.getAttribute('data-id');
  document.getElementById('modalIdLancamento').value = idLancamento;
});
</script>
</body>
</html>
