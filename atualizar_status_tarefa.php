<?php
session_start();
if (!isset($_SESSION['id_usuario'])) exit;

include 'conexao.php';

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$id_usuario = $_SESSION['id_usuario'];

if ($id && in_array($status, ['Pendente', 'Concluída'])) {
    $stmt = $pdo->prepare("UPDATE tarefas SET status = :status WHERE id = :id AND id_usuario = :id_usuario");
    $stmt->execute([
        'status' => $status,
        'id' => $id,
        'id_usuario' => $id_usuario
    ]);
}
