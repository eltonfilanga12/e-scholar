<?php
require_once __DIR__ . '/../../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Recebe filtros via GET
$turma = $_GET['turma'] ?? '';
$classe = $_GET['classe'] ?? '';
$turno = $_GET['turno'] ?? '';
$disciplina = $_GET['disciplina_id'] ?? '';

// Cabeçalhos CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="export_geral_' . date('Ymd_His') . '.csv"');
echo "\xEF\xBB\xBF"; // BOM UTF-8

$output = fopen('php://output', 'w');

$header = [
    'Aluno', 'Sexo', 'Data Nascimento', 'Idade', 'Classe', 'Turma', 'Turno',
    'Nome Pai', 'Contato Pai', 'Email Pai',
    'Nome Mãe', 'Contato Mãe', 'Email Mãe',
    'Nome Encarregado', 'Contato Encarregado', 'Email Encarregado'
];
fputcsv($output, $header);

// Query com filtros
$sql = "
SELECT 
    a.nome_completo AS aluno,
    a.sexo,
    a.data_nascimento,
    a.idade,
    c.nome_classe AS classe,
    t.nome_turma AS turma,
    tr.nome AS turno,
    a.nome_pai, a.contacto_pai, a.email_pai,
    a.nome_mae, a.contacto_mae, a.email_mae,
    a.nome_encarregado, a.contacto_encarregado, a.email_encarregado
FROM aluno a
LEFT JOIN turmas t ON a.turma_id = t.id_turma
LEFT JOIN classe c ON a.classe_id = c.id_classe
LEFT JOIN turnos tr ON a.turno_id = tr.id
WHERE 1=1
";


$params = [];

// Aplica filtros
if ($turma) {
    $sql .= " AND a.turma_id = ?"; $params[] = $turma;
}
if ($classe) {
    $sql .= " AND a.classe_id = ?"; $params[] = $classe;
}
if ($turno) {
    $sql .= " AND a.turno_id = ?"; $params[] = $turno;
}
if ($disciplina) {
    $sql .= " AND d.id = ?"; $params[] = $disciplina;
}

$sql .= " ORDER BY a.nome_completo";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
