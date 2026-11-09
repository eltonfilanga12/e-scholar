<?php
require_once __DIR__ . '/../controllers/AlunoController.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $student = new Student($conn);

    // Eliminar o aluno
    $result = $student->deleteStudent($id);
    if ($result) {
        header("Location: alunos.php?success=1");
    } else {
        echo "Erro ao eliminar aluno";
    }
}
?>
