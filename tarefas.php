<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
include 'conexao.php';

$id_usuario = $_SESSION['id_usuario'];

// Inserir ou editar tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $data_tarefa = $_POST['data_tarefa'] ?? null;
    $id_tarefa = $_POST['id_tarefa'] ?? null;

    $anexo = null;
    if (!empty($_FILES['anexo']['name'])) {
        $pasta = 'uploads/';
        if (!is_dir($pasta)) mkdir($pasta);
        $nome_anexo = uniqid() . '_' . $_FILES['anexo']['name'];
        $caminho = $pasta . $nome_anexo;
        move_uploaded_file($_FILES['anexo']['tmp_name'], $caminho);
        $anexo = $caminho;
    }

    if ($id_tarefa) {
        $sql = "UPDATE tarefas SET titulo = :titulo, descricao = :descricao, data_tarefa = :data_tarefa";
        if ($anexo) $sql .= ", anexo = :anexo";
        $sql .= " WHERE id = :id AND id_usuario = :id_usuario";

        $params = [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'data_tarefa' => $data_tarefa,
            'id' => $id_tarefa,
            'id_usuario' => $id_usuario
        ];
        if ($anexo) $params['anexo'] = $anexo;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare("INSERT INTO tarefas (id_usuario, titulo, descricao, data_tarefa, status, anexo) VALUES (:id, :titulo, :descricao, :data_tarefa, 'Pendente', :anexo)");
        $stmt->execute([
            'id' => $id_usuario,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'data_tarefa' => $data_tarefa,
            'anexo' => $anexo
        ]);
    }
}

// Excluir tarefa
if (isset($_GET['excluir'])) {
    $idExcluir = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = :id AND id_usuario = :id_usuario");
    $stmt->execute(['id' => $idExcluir, 'id_usuario' => $id_usuario]);
}

// Buscar tarefa para edição
$tarefa_editar = null;
if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM tarefas WHERE id = :id AND id_usuario = :id_usuario");
    $stmt->execute(['id' => $idEditar, 'id_usuario' => $id_usuario]);
    $tarefa_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar tarefas
$stmt1 = $pdo->prepare("SELECT * FROM tarefas WHERE id_usuario = :id_usuario AND status = 'Pendente' ORDER BY data_tarefa ASC");
$stmt1->execute(['id_usuario' => $id_usuario]);
$tarefas_pendentes = $stmt1->fetchAll();

$stmt2 = $pdo->prepare("SELECT * FROM tarefas WHERE id_usuario = :id_usuario AND status = 'Concluída' ORDER BY data_tarefa ASC");
$stmt2->execute(['id_usuario' => $id_usuario]);
$tarefas_concluidas = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Tarefas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/sidebar.css" />
  <style>
    .kanban-col { min-height: 300px; background: #f8f9fa; border-radius: 8px; padding: 15px; }
    .kanban-card { cursor: grab; }
    .form-google-style input, .form-google-style textarea, .form-google-style select {
      border: none;
      border-bottom: 2px solid #ccc;
      border-radius: 0;
      background: none;
      outline: none;
      box-shadow: none;
    }
    .form-google-style input:focus, .form-google-style textarea:focus {
      border-color: #0d6efd;
    }
  </style>
</head>
<body>
<div class="layout has-sidebar fixed-sidebar fixed-header" style="display: flex; height: 100vh;">

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content" style="flex-grow: 1; overflow-y: auto; padding: 20px;">
    <div class="container py-4">
      <h3 class="text-primary mb-4">Minhas Tarefas</h3>

      <!-- Formulário -->
      <form method="POST" enctype="multipart/form-data" class="form-google-style mb-5">
        <input type="hidden" name="id_tarefa" value="<?= $tarefa_editar['id'] ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <input type="text" name="titulo" class="form-control" placeholder="Título da tarefa" required value="<?= htmlspecialchars($tarefa_editar['titulo'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <input type="date" name="data_tarefa" class="form-control" value="<?= $tarefa_editar['data_tarefa'] ?? '' ?>">
          </div>
          <div class="col-md-4">
            <input type="file" name="anexo" class="form-control">
          </div>
          <div class="col-12">
            <textarea name="descricao" class="form-control" rows="2" placeholder="Descrição"><?= htmlspecialchars($tarefa_editar['descricao'] ?? '') ?></textarea>
          </div>
          <div class="col-12 text-end">
            <button type="submit" class="btn btn-success"><?= $tarefa_editar ? 'Atualizar' : 'Adicionar' ?></button>
            <?php if ($tarefa_editar): ?>
              <a href="tarefas.php" class="btn btn-outline-secondary">Cancelar</a>
            <?php endif; ?>
          </div>
        </div>
      </form>

      <!-- Kanban -->
      <div class="row g-4">
        <div class="col-md-6">
          <h5 class="text-warning">Pendentes</h5>
          <div class="kanban-col" id="pendentes">
            <?php foreach ($tarefas_pendentes as $t): ?>
              <div class="card mb-2 kanban-card" data-id="<?= $t['id'] ?>">
                <div class="card-body">
                  <strong><?= htmlspecialchars($t['titulo']) ?></strong><br>
                  <small class="text-muted"><?= $t['data_tarefa'] ? date('d/m/Y', strtotime($t['data_tarefa'])) : '' ?></small>
                  <p><?= nl2br(htmlspecialchars($t['descricao'])) ?></p>
                  <?php if ($t['anexo']): ?>
                    <a href="<?= $t['anexo'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Anexo</a>
                  <?php endif; ?>
                  <div class="mt-2">
                    <a href="?editar=<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                    <a href="?excluir=<?= $t['id'] ?>" onclick="return confirm('Excluir esta tarefa?')" class="btn btn-sm btn-outline-danger">Excluir</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-md-6">
          <h5 class="text-success">Concluídas</h5>
          <div class="kanban-col" id="concluidas">
            <?php foreach ($tarefas_concluidas as $t): ?>
              <div class="card mb-2 kanban-card border-success" data-id="<?= $t['id'] ?>">
                <div class="card-body">
                  <strong><?= htmlspecialchars($t['titulo']) ?></strong><br>
                  <small class="text-muted"><?= $t['data_tarefa'] ? date('d/m/Y', strtotime($t['data_tarefa'])) : '' ?></small>
                  <p><?= nl2br(htmlspecialchars($t['descricao'])) ?></p>
                  <?php if ($t['anexo']): ?>
                    <a href="<?= $t['anexo'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Anexo</a>
                  <?php endif; ?>
                  <div class="mt-2">
                    <a href="?editar=<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                    <a href="?excluir=<?= $t['id'] ?>" onclick="return confirm('Excluir esta tarefa?')" class="btn btn-sm btn-outline-danger">Excluir</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sidebar.js"></script>
<script>
// Atualizar status ao arrastar
function atualizarStatus(id, novoStatus) {
  fetch('atualizar_status_tarefa.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `id=${id}&status=${novoStatus}`
  });
}

['pendentes', 'concluidas'].forEach((id) => {
  new Sortable(document.getElementById(id), {
    group: 'kanban',
    animation: 150,
    onAdd: function (evt) {
      const idTarefa = evt.item.getAttribute('data-id');
      const novoStatus = evt.to.id === 'concluidas' ? 'Concluída' : 'Pendente';
      atualizarStatus(idTarefa, novoStatus);
    }
  });
});
</script>
</body>
</html>
