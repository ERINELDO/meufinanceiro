<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/conexao.php';

$id_usuario = $_SESSION['id_usuario'];
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

$sql = "SELECT * FROM registro_saque_investimento WHERE id_usuario = :id_usuario";
$params = ['id_usuario' => $id_usuario];

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND data_saque BETWEEN :data_inicio AND :data_fim";
    $params['data_inicio'] = $data_inicio;
    $params['data_fim'] = $data_fim;
}

$sql .= " ORDER BY data_saque DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalSaque = 0;
foreach ($registros as $registro) {
    $totalSaque += $registro['valor_saque'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Histórico de Saques</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="layout has-sidebar fixed-sidebar fixed-header" style="display: flex; height: 100vh;">
  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content" style="flex-grow: 1; overflow-y: auto; padding: 20px;">
    <div class="container mt-4">
      <h3 class="mb-4 text-primary"><i class="ri-history-line"></i> Histórico de Saques</h3>

      <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="data_inicio" class="form-label">Data Início:</label>
          <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>
        <div class="col-md-4">
          <label for="data_fim" class="form-label">Data Fim:</label>
          <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
        </div>
        <div class="col-md-4 align-self-end">
          <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
      </form>

      <?php if (count($registros) > 0): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>ID Lançamento</th>
                <th>Data do Saque</th>
                <th>Valor</th>
                <th>Observação</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($registros as $r): ?>
                <tr>
                  <td><?= $r['id_saque'] ?></td>
                  <td><?= $r['id_lancamento'] ?></td>
                  <td><?= date('d/m/Y', strtotime($r['data_saque'])) ?></td>
                  <td>R$ <?= number_format($r['valor_saque'], 2, ',', '.') ?></td>
                  <td><?= htmlspecialchars($r['observacao']) ?></td>
                  <td><?= $r['ip_usuario'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="3"><strong>Total de Saques</strong></td>
                <td colspan="3"><strong>R$ <?= number_format($totalSaque, 2, ',', '.') ?></strong></td>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">Nenhum registro encontrado para o filtro aplicado.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sidebar.js"></script>
</body>
</html>