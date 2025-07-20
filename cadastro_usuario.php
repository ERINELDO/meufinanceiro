<?php
include 'conexao.php';

$success = false;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    if ($senha !== $senha2) {
        $msg = "As senhas não coincidem.";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (login, nome, email, senha, saldo_inicial) VALUES (?, ?, ?, ?, 0)");
        if ($stmt->execute([$email, $nome, $email, $hash])) {
            $success = true;
        } else {
            $msg = "Erro ao cadastrar. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Usuário</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 bg-white p-4 rounded shadow">
        <h3 class="mb-4">Criar Conta</h3>
        <?php if ($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label>Seu Nome</label>
            <input type="text" name="nome" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Seu E-mail</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Escolha a Senha</label>
            <input type="password" name="senha" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Repita a Senha</label>
            <input type="password" name="senha2" class="form-control" required>
          </div>
          <button class="btn btn-success w-100">Cadastrar</button>
        </form>
        <div class="mt-3 text-center">
          <a href="login.php">Voltar para o login</a>
        </div>
      </div>
    </div>
  </div>

  <?php if ($success): ?>
  <!-- Modal de sucesso -->
  <div class="modal fade" id="sucessoModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Cadastro realizado</h5>
        </div>
        <div class="modal-body">
          Seu cadastro foi realizado com sucesso! Você já pode fazer login.
        </div>
        <div class="modal-footer">
          <a href="login.php" class="btn btn-primary">Ir para o Login</a>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    var modal = new bootstrap.Modal(document.getElementById('sucessoModal'));
    modal.show();
  </script>
  <?php endif; ?>
</body>
</html>
