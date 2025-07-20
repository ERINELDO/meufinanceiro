<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
include 'conexao.php';

$id_usuario = $_SESSION['id_usuario'];

$anoFiltrado = isset($_GET['anoFiltrado']) ? (int)$_GET['anoFiltrado'] : null;
$mesSelecionado = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$anoSelecionado = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];

function filtroData($tipo) {
    global $anoFiltrado, $mesSelecionado, $anoSelecionado;
    $coluna = $tipo === 'invest' ? 'data_investimento' : 'data_venc';
    return $anoFiltrado ? "YEAR($coluna) = $anoFiltrado" : "MONTH($coluna) = $mesSelecionado AND YEAR($coluna) = $anoSelecionado";
}
$whereVenc = filtroData('venc');
$whereInvest = filtroData('invest');

$totalReceitas = $pdo->query("SELECT SUM(valor_lancamento) FROM lancamento_financeiro WHERE tipo_lancamento='Receita' AND id_usuario=$id_usuario AND $whereVenc")->fetchColumn() ?: 0;
$totalDespesas = $pdo->query("SELECT SUM(valor_lancamento) FROM lancamento_financeiro WHERE tipo_lancamento='Despesa' AND id_usuario=$id_usuario AND $whereVenc")->fetchColumn() ?: 0;
$totalInvestimentos = $pdo->query("SELECT SUM(valor_lancamento) FROM lancamento_financeiro WHERE tipo_lancamento='Investimentos' AND id_usuario=$id_usuario AND $whereInvest")->fetchColumn() ?: 0;

$stmt1 = $pdo->query("SELECT c.nome_categoria, SUM(l.valor_lancamento) total FROM lancamento_financeiro l JOIN categoria_lancamento c ON l.categoria_lancamento = c.id_categoria WHERE l.tipo_lancamento='Despesa' AND l.id_usuario=$id_usuario AND $whereVenc GROUP BY c.nome_categoria");
$dadosPizza = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->query("SELECT data_venc, valor_lancamento FROM lancamento_financeiro WHERE status_lancamento='Pendente' AND id_usuario=$id_usuario AND $whereVenc ORDER BY data_venc ASC");
$dadosBarra = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$datas = []; $valores = []; $totalBarra = 0;
foreach ($dadosBarra as $d) {
    $datas[] = date('d/m', strtotime($d['data_venc']));
    $valores[] = floatval($d['valor_lancamento']);
    $totalBarra += $d['valor_lancamento'];
}

$stmt3 = $pdo->query("SELECT c.nome_categoria nome_investimento, SUM(l.valor_lancamento) total FROM lancamento_financeiro l JOIN categoria_lancamento c ON l.categoria_lancamento = c.id_categoria WHERE l.tipo_lancamento='Investimentos' AND l.id_usuario=$id_usuario AND $whereInvest GROUP BY c.nome_categoria");
$dadosInvestimento = $stmt3->fetchAll(PDO::FETCH_ASSOC);
$labelsInvest = array_column($dadosInvestimento, 'nome_investimento');
$valoresInvest = array_map('floatval', array_column($dadosInvestimento, 'total'));

// ** Cálculo dos novos cards **
$saldoPrevisto = $totalReceitas - $totalDespesas;

$previstoInvestimentos = $saldoPrevisto * 0.10;

$previstoLazer = ($saldoPrevisto - $previstoInvestimentos) * 0.10;

$reservaEmergencia = $saldoPrevisto - $previstoInvestimentos - $previstoLazer;

// Função para definir cor de fundo do card com base no valor do saldo (positivo verde, negativo vermelho, zero cinza)
function corSaldoCard($valor) {
    if ($valor > 0) return 'bg-success text-white';
    if ($valor < 0) return 'bg-danger text-white';
    return 'bg-secondary text-white';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Financeiro</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/sidebar.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .bg-success-custom { background-color: #145214 !important; }
    .text-white-bold { color: white !important; font-weight: 700; }
    #filtroData select { width: 120px !important; }
  </style>
</head>
<body>
<div class="layout has-sidebar fixed-sidebar fixed-header" style="display: flex; height: 100vh;">
  <?php include 'includes/sidebar.php'; ?>
  <div class="main-content" style="flex-grow: 1; overflow-y: auto; padding: 20px;">
    <div class="container mt-4">
      <h3 class="mb-4 text-primary">Dashboard Financeiro</h3>

      <form id="filtroData" method="get" class="d-flex gap-2 flex-wrap align-items-center mb-4">
        <label>Mês:</label>
        <select name="mes" class="form-select form-select-sm" <?= $anoFiltrado ? 'disabled' : '' ?>>
          <?php foreach ($meses as $num => $nome): ?>
            <option value="<?= $num ?>" <?= ($num == $mesSelecionado && !$anoFiltrado) ? 'selected' : '' ?>><?= $nome ?></option>
          <?php endforeach; ?>
        </select>
        <label>Ano:</label>
        <select name="ano" class="form-select form-select-sm" <?= $anoFiltrado ? 'disabled' : '' ?>>
          <?php for ($i = 2022; $i <= date('Y') + 5; $i++): ?>
            <option value="<?= $i ?>" <?= ($i == $anoSelecionado && !$anoFiltrado) ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm" <?= $anoFiltrado ? 'disabled' : '' ?>>Filtrar Mês/Ano</button>
        <label>Ano (total):</label>
        <select name="anoFiltrado" class="form-select form-select-sm">
          <option value="">--</option>
          <?php for ($i = 2022; $i <= date('Y') + 5; $i++): ?>
            <option value="<?= $i ?>" <?= ($i == $anoFiltrado) ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filtrar Ano</button>
        <a href="<?= basename(__FILE__) ?>" class="btn btn-outline-danger btn-sm">Limpar filtro</a>
      </form>

      <!-- NOVOS CARDS DE INTELIGÊNCIA - saldo previsto, investimentos, lazer e emergência -->
      <div class="row mb-4 g-3">
        <div class="col-md-3">
          <div class="card <?= corSaldoCard($saldoPrevisto) ?>">
            <div class="card-header">Saldo Previsto</div>
            <div class="card-body">
              <h3>R$ <?= number_format($saldoPrevisto, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card <?= corSaldoCard($previstoInvestimentos) ?>">
            <div class="card-header">Previsto para Investimentos</div>
            <div class="card-body">
              <h3>R$ <?= number_format($previstoInvestimentos, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card <?= corSaldoCard($previstoLazer) ?>">
            <div class="card-header">Previsto para Lazer</div>
            <div class="card-body">
              <h3>R$ <?= number_format($previstoLazer, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card <?= corSaldoCard($reservaEmergencia) ?>">
            <div class="card-header">Reserva para Emergência</div>
            <div class="card-body">
              <h3>R$ <?= number_format($reservaEmergencia, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
      </div>

      <!-- CARDS EXISTENTES -->
      <div class="row mb-4 g-3">
        <div class="col-md-4">
          <div class="card bg-success-custom text-white-bold">
            <div class="card-header">Total de Receitas</div>
            <div class="card-body">
              <h3>R$ <?= number_format($totalReceitas, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-success-custom text-white-bold">
            <div class="card-header">Total de Despesas</div>
            <div class="card-body">
              <h3>R$ <?= number_format($totalDespesas, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card bg-success-custom text-white-bold">
            <div class="card-header">Total de Investimentos</div>
            <div class="card-body">
              <h3>R$ <?= number_format($totalInvestimentos, 2, ',', '.') ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <div class="card">
            <div class="card-header bg-primary text-white">Gráfico de Despesas</div>
            <div class="card-body">
              <canvas id="graficoPizza"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-header bg-primary text-white">Contas a Vencer</div>
            <div class="card-body">
              <canvas id="graficoBarra"></canvas>
              <p class="fw-bold text-center">Total: R$ <?= number_format($totalBarra, 2, ',', '.') ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-header bg-primary text-white">Gráfico de Investimentos</div>
            <div class="card-body">
              <canvas id="graficoInvestimento"></canvas>
              <p class="fw-bold text-center">Total Investido: R$ <?= number_format($totalInvestimentos, 2, ',', '.') ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sidebar-toggle.js"></script>
<script>
  new Chart(document.getElementById('graficoPizza'), {
    type: 'pie',
    data: {
      labels: <?= json_encode(array_column($dadosPizza, 'nome_categoria')) ?>,
      datasets: [{
        data: <?= json_encode(array_map('floatval', array_column($dadosPizza, 'total'))) ?>,
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#9CCC65', '#BA68C8', '#FF7043', '#4DD0E1']
      }]
    },
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.label}: R$ ${ctx.raw.toFixed(2).replace('.', ',')}`
          }
        }
      }
    }
  });
  new Chart(document.getElementById('graficoBarra'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($datas) ?>,
      datasets: [{
        label: 'Contas Pendentes',
        data: <?= json_encode($valores) ?>,
        backgroundColor: '#007bff'
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => 'R$ ' + value.toFixed(2).replace('.', ',')
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: ctx => 'R$ ' + ctx.raw.toFixed(2).replace('.', ',')
          }
        }
      }
    }
  });
  new Chart(document.getElementById('graficoInvestimento'), {
    type: 'pie',
    data: {
      labels: <?= json_encode($labelsInvest) ?>,
      datasets: [{
        data: <?= json_encode($valoresInvest) ?>,
        backgroundColor: ['#17a2b8', '#20c997', '#6610f2', '#ffc107', '#dc3545', '#fd7e14']
      }]
    },
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const percent = ((ctx.raw / total) * 100).toFixed(1);
              return `${ctx.label}: R$ ${ctx.raw.toFixed(2).replace('.', ',')} (${percent}%)`;
            }
          }
        }
      }
    }
  });
</script>
</body>
</html>
