<?php
require_once '../../../config/database.php';
require_once '../models/Aluno.php';

$database = new Database();
$pdo = $database->getConnection();
$aluno = new Aluno($pdo);

// SALVAR ou EDITAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = $_POST;
    $aluno->salvar($dados);
    header("Location: ../alunos.php?msg=salvo");
    exit;
}

// EXCLUIR
if (isset($_GET['acao']) && $_GET['acao'] === 'eliminar' && isset($_GET['id'])) {
    $aluno->eliminar($_GET['id']);
    header("Location: ../alunos.php?msg=excluido");
    exit;
}
?>
