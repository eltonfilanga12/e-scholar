<?php
require_once __DIR__ . '/../../../config/database.php';

session_start();

$db = new Database();
$pdo = $db->getConnection();

$id_prof = $_SESSION['nome'] ?? null;
$tipoUsuario = $_SESSION['tipo'] ?? 'Professor';
$anoLectivoAtual = date('Y');

// Buscar dados do professor
$queryProf = $pdo->prepare("
    SELECT p.id_professor, f.nome AS nome_funcionario
    FROM professores p
    INNER JOIN funcionarios f ON p.funcionario_id = f.id
    WHERE f.nome = ?
");
$queryProf->execute([$id_prof]);
$prof = $queryProf->fetch();
$id_professor = $prof['id_professor'] ?? null;

// Filtrar alunos e notas
$turmaSelecionada = $_GET['turma'] ?? '';
$classeSelecionada = $_GET['classe'] ?? '';
$disciplinaSelecionada = $_GET['disciplina_id'] ?? '';
$turnoSelecionado = $_GET['turno'] ?? '';

if (!$disciplinaSelecionada || !$turmaSelecionada || !$classeSelecionada) {
    die("Selecione Turma, Classe e Disciplina para exportar.");
}

// Buscar alunos
$sqlAlunos = "
    SELECT a.id, a.nome_completo, a.turma_id, a.classe_id, t.turno_id
    FROM aluno a
    JOIN turmas t ON a.turma_id = t.id_turma
    WHERE a.turma_id = ? AND a.classe_id = ?
";
$stmtAlunos = $pdo->prepare($sqlAlunos);
$stmtAlunos->execute([$turmaSelecionada, $classeSelecionada]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

// Buscar turmas, classes, disciplinas e turno
$turmas = [];
$classes = [];
$disciplinas = [];
$turnos = [];

$stmtTurmas = $pdo->query("SELECT id_turma, nome_turma FROM turmas");
while ($t = $stmtTurmas->fetch(PDO::FETCH_ASSOC)) $turmas[$t['id_turma']] = $t['nome_turma'];

$stmtClasses = $pdo->query("SELECT id_classe, nome_classe FROM classe");
while ($c = $stmtClasses->fetch(PDO::FETCH_ASSOC)) $classes[$c['id_classe']] = $c['nome_classe'];

$stmtDisciplina = $pdo->prepare("SELECT nome FROM disciplinas WHERE id = ?");
$stmtDisciplina->execute([$disciplinaSelecionada]);
$disciplina = $stmtDisciplina->fetchColumn();

$stmtTurno = $pdo->prepare("SELECT nome FROM turnos WHERE id = ?");
$stmtTurno->execute([$turnoSelecionado]);
$turnoNome = $stmtTurno->fetchColumn();

// Preparar CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=notas_frequencia.csv');

$output = fopen('php://output', 'w');

// Adicionar BOM UTF-8 para compatibilidade com Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


// Cabeçalho CSV
fputcsv($output, [
    'Nome do Aluno',
    'Turma',
    'Classe',
    'Turno',
    'Disciplina',
    'Teste 1',
    'Teste 2',
    'Teste 3',
    'Trabalho 1',
    'Trabalho 2',
    'Trabalho 3',
    'Média',
    'Professor'
]);

// Preencher dados
foreach ($alunos as $aluno) {
    // Buscar nota do aluno
    $stmtNota = $pdo->prepare("
        SELECT n.*, f.nome AS professor_nome
        FROM nota n
        LEFT JOIN professores p ON n.professor_id = p.id_professor
        LEFT JOIN funcionarios f ON p.funcionario_id = f.id
        WHERE n.aluno_id = ? AND n.disciplina_id = ? AND n.ano_lectivo = ?
    ");
    $stmtNota->execute([$aluno['id'], $disciplinaSelecionada, $anoLectivoAtual]);
    $nota = $stmtNota->fetch(PDO::FETCH_ASSOC);

    $linha = [
        $aluno['nome_completo'],
        $turmas[$aluno['turma_id']] ?? '',
        $classes[$aluno['classe_id']] ?? '',
        $turnoNome ?? '',
        $disciplina,
        $nota['teste1'] ?? '',
        $nota['teste2'] ?? '',
        $nota['teste3'] ?? '',
        $nota['trabalho1'] ?? '',
        $nota['trabalho2'] ?? '',
        $nota['trabalho3'] ?? '',
        $nota['media'] ?? '',
        $nota['professor_nome'] ?? $prof['nome_funcionario'] ?? ''
    ];

    fputcsv($output, $linha);
}

fclose($output);
exit;
