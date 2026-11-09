<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo_usuario'];
    $status = $_POST['user_status'];

    $database = new Database();
    $conn = $database->getConnection();

    $sql = "INSERT INTO usuarios (nome, senha, tipo_usuario, user_status) VALUES (:nome, :senha, :tipo, :status)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        $_SESSION['status'] = "Usuário cadastrado com sucesso!";
    } else {
        $_SESSION['status'] = "Erro ao cadastrar o usuário.";
    }

    header("Location: usuarios");
    exit;
}
