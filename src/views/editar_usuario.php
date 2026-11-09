<?php
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];
    $nome = $_POST["nome"];
    $senha = $_POST["senha"];
    $tipo_usuario = $_POST["tipo_usuario"];
    $user_status = $_POST["user_status"];

    $database = new Database();
    $conn = $database->getConnection();

    if (!empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nome = ?, senha = ?, tipo_usuario = ?, user_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome, $senha_hash, $tipo_usuario, $user_status, $id]);
    } else {
        $sql = "UPDATE usuarios SET nome = ?, tipo_usuario = ?, user_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome, $tipo_usuario, $user_status, $id]);
    }

    $_SESSION['status'] = "Usuário atualizado com sucesso!";
    header("Location: usuarios");
    exit();
}
