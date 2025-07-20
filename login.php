<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
}

include 'conexao.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nome'] = $usuario['nome'];
        header("Location: dashboard.php");
        exit;
    } else {
        $msg = 'E-mail ou senha inválidos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistema Financeiro</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container-fluid vh-100 d-flex">
    <div class="row w-100">
      <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center bg-primary text-white">
        <h1 class="display-4">Bem-vindo<br>ao Sistema Financeiro</h1>
      </div>
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <form method="POST" class="w-75">
          <h2 class="mb-4">Acesso ao Sistema</h2>
          <?php if ($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>
          <div class="mb-3">
            <label for="email" class="form-label">Seu E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" required>
          </div>
          <div class="mb-3 d-flex justify-content-between">
            <a href="recuperar_senha.php">Esqueceu a senha?</a>
            <a href="cadastro_usuario.php">Cadastrar-se</a>
          </div>
          <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
