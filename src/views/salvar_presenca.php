<?php
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = $_POST['aluno_id'] ?? null;
    $disciplina_id = $_POST['disciplina_id'] ?? null;
    $presenca = $_POST['presenca'] ?? null;

    $data = date('Y-m-d');

    if ($aluno_id && $disciplina_id !== null && $presenca !== null) {
        // Verifica se já existe
        $check = $pdo->prepare("SELECT id FROM frequencias WHERE aluno_id = ? AND disciplina_id = ? AND data = ?");
        $check->execute([$aluno_id, $disciplina_id, $data]);
        $registro = $check->fetch();

        if ($registro) {
            $stmt = $pdo->prepare("UPDATE frequencias SET presenca = ? WHERE id = ?");
            $stmt->execute([$presenca, $registro['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO frequencias (aluno_id, disciplina_id, data, presenca) VALUES (?, ?, ?, ?)");
            $stmt->execute([$aluno_id, $disciplina_id, $data, $presenca]);
        }

        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos']);
    }
}
