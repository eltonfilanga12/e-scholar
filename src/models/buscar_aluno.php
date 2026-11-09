<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();

if (isset($_GET['q'])) {
    $termo = "%" . trim($_GET['q']) . "%";
    $stmt = $conn->prepare("SELECT * FROM aluno WHERE nome_completo LIKE ? LIMIT 10");
    $stmt->execute([$termo]);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($alunos);
    exit;
}

echo json_encode([]);
