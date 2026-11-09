<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

$tipoUsuario = $_SESSION['tipo'] ?? 'Professor';
$id_prof = $_SESSION['nome'] ?? null;

$prof = null;
$id_professor = null;

if ($tipoUsuario === 'Professor') {
    $queryProf = $pdo->prepare("
        SELECT p.id_professor, f.nome AS nome_completo
        FROM professores p
        INNER JOIN funcionarios f ON p.funcionario_id = f.id
        WHERE f.nome = ?
    ");
    $queryProf->execute([$id_prof]);
    $prof = $queryProf->fetch();
    $id_professor = $prof['id_professor'] ?? null;
}

$turmaSelecionada  = $_GET['turma'] ?? '';
$classeSelecionada = $_GET['classe'] ?? '';
$turnoSelecionado  = $_GET['turno'] ?? '';

$turmas = $classes = $turnos = [];

if ($tipoUsuario === 'Professor' && $id_professor) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            t.id_turma, t.nome_turma, 
            c.id_classe, c.nome_classe,
            tr.id, tr.nome
        FROM professor_turmas pt
        JOIN turmas t ON pt.turma_id = t.id_turma
        JOIN classe c ON t.classe_id = c.id_classe
        JOIN turnos tr ON t.turno_id = tr.id
        WHERE pt.professor_id = ?
    ");
    $stmt->execute([$id_professor]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $turmas[$linha['id_turma']] = $linha['nome_turma'];
        $classes[$linha['id_classe']] = $linha['nome_classe'];
        $turnos[$linha['id']] = $linha['nome'];
    }
} elseif ($tipoUsuario === 'Administrador') {
    foreach ($pdo->query("SELECT id_turma, nome_turma FROM turmas") as $linha) {
        $turmas[$linha['id_turma']] = $linha['nome_turma'];
    }
    foreach ($pdo->query("SELECT id_classe, nome_classe FROM classe") as $linha) {
        $classes[$linha['id_classe']] = $linha['nome_classe'];
    }
    foreach ($pdo->query("SELECT id, nome FROM turnos") as $linha) {
        $turnos[$linha['id']] = $linha['nome'];
    }
}

// Buscar alunos
$alunos = [];
if ($turmaSelecionada && $classeSelecionada && $turnoSelecionado) {
    $sql = "SELECT id, nome_completo, turma_id, classe_id, turno_id 
            FROM aluno 
            WHERE turma_id = ? AND classe_id = ? AND turno_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado]);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Contar faltas do aluno
function contarFaltas($pdo, $alunoId)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM frequencias WHERE aluno_id = ? AND presenca = 0");
    $stmt->execute([$alunoId]);
    return $stmt->fetchColumn();
}

// Detalhes das faltas
function buscarDetalhesFaltas($pdo, $alunoId)
{
    $stmt = $pdo->prepare("
        SELECT f.data, d.nome AS disciplina, f.justificativa_texto, f.status_justificativa
        FROM frequencias f
        LEFT JOIN disciplinas d ON f.disciplina_id = d.id
        WHERE f.aluno_id = ? AND f.presenca = 0
        ORDER BY f.data DESC
    ");
    $stmt->execute([$alunoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h3 class="page-title">Faltas dos Alunos</h3>
        </div>

        <!-- Filtros -->
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label for="turma">Turma</label>
                    <select name="turma" id="turma" class="form-control" onchange="this.form.submit()">
                        <option value="">Selecione a turma</option>
                        <?php foreach ($turmas as $id => $nome): ?>
                            <option value="<?= $id ?>" <?= $id == $turmaSelecionada ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="classe">Classe</label>
                    <select name="classe" id="classe" class="form-control" onchange="this.form.submit()">
                        <option value="">Selecione a classe</option>
                        <?php foreach ($classes as $id => $nome): ?>
                            <option value="<?= $id ?>" <?= $id == $classeSelecionada ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="turno">Turno</label>
                    <select name="turno" id="turno" class="form-control" onchange="this.form.submit()">
                        <option value="">Selecione o turno</option>
                        <?php foreach ($turnos as $id => $nome): ?>
                            <option value="<?= $id ?>" <?= $id == $turnoSelecionado ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table id="basic-datatables" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Turma</th>
                        <th>Classe</th>
                        <th>Turno</th>
                        <th>Nº Faltas</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                        <?php $numFaltas = contarFaltas($pdo, $aluno['id']); ?>
                        <tr>
                            <td><?= $aluno['id'] ?></td>
                            <td><?= $aluno['nome_completo'] ?></td>
                            <td><?= $turmas[$aluno['turma_id']] ?? '' ?></td>
                            <td><?= $classes[$aluno['classe_id']] ?? '' ?></td>
                            <td><?= $turnos[$aluno['turno_id']] ?? '' ?></td>
                            <td><?= $numFaltas ?></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalFaltas<?= $aluno['id'] ?>">Detalhes</button>

                                <!-- Modal -->
                                <div class="modal fade" id="modalFaltas<?= $aluno['id'] ?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Faltas de <?= $aluno['nome_completo'] ?></h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <table id="basic-datatables" class="display table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Data</th>
                                                            <th>Disciplina</th>
                                                            <th>Justificativa</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $faltasDetalhes = buscarDetalhesFaltas($pdo, $aluno['id']);
                                                        foreach ($faltasDetalhes as $f): ?>
                                                            <tr>
                                                                <td><?= $f['data'] ?></td>
                                                                <td><?= $f['disciplina'] ?? '-' ?></td>
                                                                <td><?= $f['justificativa_texto'] ?? '-' ?></td>
                                                                <td><?= $f['status_justificativa'] ?? '-' ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>