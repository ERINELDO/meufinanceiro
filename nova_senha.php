<?php
include 'conexao.php';

$token = $_GET['token'] ?? '';
$msg = '';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE token_recuperacao = ? AND token_expira >= NOW()");
$stmt->execute([$token]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Token inválido ou expirado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['senha'] ?? '';
    if (strlen($novaSenha) < 6) {
        $msg = "A senha deve ter ao menos 6 caracteres.";
    } else {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expira = NULL WHERE id_usuario = ?");
        $stmt->execute([$hash, $usuario['id_usuario']]);
        header("Location: login.php?msg=Senha redefinida com sucesso");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Nova Senha</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h3>Redefinir Senha</h3>
        <?php if ($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label for="senha" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" name="senha" id="senha" required>
          </div>
          <button type="submit" class="btn btn-success">Salvar nova senha</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
