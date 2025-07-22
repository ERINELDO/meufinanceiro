<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/conexao.php'; // seu arquivo PDO

$id_usuario = $_SESSION['id_usuario'];
$msg = '';

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ip_usuario = getUserIP();

// Busca investimentos ativos do usuário com filtro tipo_lancamento = 'Investimentos'
try {
    $sql_investimentos = "SELECT id_lancamento, observacao_lancamento, valor_lancamento 
                          FROM lancamento_financeiro 
                          WHERE id_usuario = :id_usuario 
                            AND valor_lancamento > 0 
                            AND tipo_lancamento = 'Investimentos'";
    $stmt = $pdo->prepare($sql_investimentos);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $investimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar investimentos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lancamento = intval($_POST['id_lancamento'] ?? 0);
    $valor_saque = floatval(str_replace(',', '.', $_POST['valor_saque'] ?? '0'));
    $observacao = trim($_POST['observacao'] ?? '');

    if ($id_lancamento <= 0 || $valor_saque <= 0) {
        $msg = "Selecione um investimento válido e informe um valor de saque maior que zero.";
    } else {
        try {
            $pdo->beginTransaction();

            $sql_saldo = "SELECT valor_lancamento FROM lancamento_financeiro WHERE id_lancamento = :id_lancamento AND id_usuario = :id_usuario FOR UPDATE";
            $stmt_saldo = $pdo->prepare($sql_saldo);
            $stmt_saldo->execute(['id_lancamento' => $id_lancamento, 'id_usuario' => $id_usuario]);
            $row_saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

            if (!$row_saldo) {
                throw new Exception("Investimento não encontrado ou não pertence ao usuário.");
            }

            $saldo_atual = floatval($row_saldo['valor_lancamento']);

            if ($valor_saque > $saldo_atual) {
                throw new Exception("Saldo insuficiente para esse saque.");
            }

            $novo_saldo = $saldo_atual - $valor_saque;
            $sql_update = "UPDATE lancamento_financeiro SET valor_lancamento = :novo_saldo WHERE id_lancamento = :id_lancamento AND id_usuario = :id_usuario";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                'novo_saldo' => $novo_saldo,
                'id_lancamento' => $id_lancamento,
                'id_usuario' => $id_usuario,
            ]);

            $sql_insert = "INSERT INTO registro_saque_investimento (id_lancamento, data_saque, valor_saque, observacao, id_usuario, ip_usuario) VALUES (:id_lancamento, NOW(), :valor_saque, :observacao, :id_usuario, :ip_usuario)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                'id_lancamento' => $id_lancamento,
                'valor_saque' => $valor_saque,
                'observacao' => $observacao,
                'id_usuario' => $id_usuario,
                'ip_usuario' => $ip_usuario,
            ]);

            $pdo->commit();
            $msg = "Saque de R$ " . number_format($valor_saque, 2, ',', '.') . " realizado com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Erro ao realizar saque: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Carteira de Investimentos - Registro de Saque </title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/sidebar.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
</head>
<body>
  <div class="layout has-sidebar fixed-sidebar fixed-header" style="display: flex; height: 100vh;">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content" style="flex-grow: 1; overflow-y: auto; padding: 20px;">
      <div class="container mt-4">
        <h3 class="mb-4 text-primary">Carteira de Investimentos - Registro de Saque</h3>

        <?php if ($msg): ?>
          <div class="alert <?= strpos($msg, 'Erro') === false ? 'alert-success' : 'alert-danger' ?>" role="alert">
            <?= htmlspecialchars($msg) ?>
          </div>
        <?php endif; ?>

        <div class="card p-4 shadow-sm">
          <form method="post" action="">
            <div class="mb-3">
              <label for="id_lancamento" class="form-label">Selecione o investimento:</label>
              <select name="id_lancamento" id="id_lancamento" class="form-select" required>
                <option value="">-- Escolha um investimento --</option>
                <?php foreach ($investimentos as $inv): ?>
                  <option value="<?= $inv['id_lancamento'] ?>">
                    <?= htmlspecialchars($inv['observacao_lancamento']) ?> - Saldo: R$ <?= number_format($inv['valor_lancamento'], 2, ',', '.') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="valor_saque" class="form-label">Valor do saque:</label>
              <input type="text" name="valor_saque" id="valor_saque" class="form-control" required pattern="^\d+(\,\d{1,2})?$" placeholder="Ex: 150,00" />
            </div>

            <div class="mb-3">
              <label for="observacao" class="form-label">Observação (opcional):</label>
              <textarea name="observacao" id="observacao" rows="3" class="form-control" placeholder="Digite uma observação"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Realizar Saque</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebar.js"></script>
</body>
</html>
