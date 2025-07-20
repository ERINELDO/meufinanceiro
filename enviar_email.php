<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = ?");
    $stmt->execute([$login]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE id_usuario = ?");
        $stmt->execute([$token, $expira, $usuario['id_usuario']]);

        $link = "http://localhost/nova_senha.php?token=$token"; // ajuste para sua URL real
        $mensagem = "Olá, clique no link para recuperar sua senha: $link (válido por 1 hora)";

        // Simulação de envio de e-mail
        file_put_contents("log_envio_emails.txt", "Para: $login\n$link\n\n", FILE_APPEND);

        header("Location: recuperar_senha.php?msg=Link enviado para seu e-mail");
        exit;
    } else {
        header("Location: recuperar_senha.php?msg=E-mail não encontrado");
        exit;
    }
} else {
    header("Location: recuperar_senha.php");
    exit;
}
?>
