<?php
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Senha</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h3 class="mb-4">Recuperação de Senha</h3>
        <?php if ($msg): ?><div class="alert alert-info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <form action="enviar_email.php" method="POST">
          <div class="mb-3">
            <label for="login" class="form-label">E-mail de cadastro</label>
            <input type="email" class="form-control" id="login" name="login" required>
          </div>
          <button type="submit" class="btn btn-primary">Enviar link de recuperação</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
