<?php
// conexao.php
$host = 'localhost';
$db = 'NOMEDOBANCO';
$user = 'USUARIODOBANCO';
$pass = 'SENHADOBANCO';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco: " . $e->getMessage());
}
?>
