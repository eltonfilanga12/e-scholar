<?php
session_start();
require_once __DIR__ . '../../../../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$ano = $data['ano_lectivo'] ?? null;
$trimestre = $data['trimestre'] ?? null;
$alunoNome = $_SESSION['nome'] ?? null;

if (!$ano || !$trimestre || !$alunoNome) {
    echo json_encode(['success' => false, 'message' => 'Ano letivo, trimestre ou aluno não definido.']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Buscar o ID do aluno pelo nome
    $stmt = $conn->prepare("SELECT id FROM aluno WHERE nome_completo = :nome LIMIT 1");
    $stmt->bindParam(':nome', $alunoNome);
    $stmt->execute();
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        echo json_encode(['success' => false, 'message' => 'Aluno não encontrado.']);
        exit;
    }

    $alunoId = $aluno['id'];

    // Buscar as notas de exame
    $query = "
    SELECT d.nome AS disciplina,
           n.media AS mediafreq,
           n.examenormal,
           n.examerecorrencia,
           n.mediafinal
    FROM nota n
    INNER JOIN disciplinas d ON d.id = n.disciplina_id
        WHERE n.ano_lectivo = :ano AND n.id_trimestre = :trimestre AND n.aluno_id = :aluno_id
";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':ano', $ano);
    $stmt->bindParam(':trimestre', $trimestre);
    $stmt->bindParam(':aluno_id', $alunoId, PDO::PARAM_INT);
    $stmt->execute();
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'notas' => $notas]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
