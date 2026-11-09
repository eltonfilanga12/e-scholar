<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

$tipoUsuario = $_SESSION['tipo'] ?? 'Professor'; // valores: 'professor' ou 'admin'
$id_prof = $_SESSION['nome'] ?? null;

$prof = null;
$id_professor = null;

// Se for professor → busca dados
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

// Filtros selecionados
$turmaSelecionada      = $_GET['turma'] ?? '';
$classeSelecionada     = $_GET['classe'] ?? '';
$disciplinaSelecionada = $_GET['disciplina_id'] ?? '';
$turnoSelecionado      = $_GET['turno'] ?? '';

$turmas = [];
$classes = [];
$disciplinas = [];
$turnos = [];

// -------- PROFESSOR --------
if ($tipoUsuario === 'Professor' && $id_professor) {
    // Turmas + classes + turnos do professor
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

    // Disciplinas do professor
    $stmtDisciplinas = $pdo->prepare("
        SELECT d.id, d.nome
        FROM professor_disciplinas pd
        JOIN disciplinas d ON pd.disciplina_id = d.id
        WHERE pd.professor_id = ?
    ");
    $stmtDisciplinas->execute([$id_professor]);
    foreach ($stmtDisciplinas->fetchAll(PDO::FETCH_ASSOC) as $disc) {
        $disciplinas[$disc['id']] = $disc['nome'];
    }

    // -------- ADMIN --------
} elseif ($tipoUsuario === 'Administrador') {
    // Todas as turmas
    $stmt = $pdo->query("SELECT id_turma, nome_turma FROM turmas");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $turmas[$linha['id_turma']] = $linha['nome_turma'];
    }

    // Todas as classes
    $stmt = $pdo->query("SELECT id_classe, nome_classe FROM classe");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $classes[$linha['id_classe']] = $linha['nome_classe'];
    }

    // Todos os turnos
    $stmt = $pdo->query("SELECT id, nome FROM turnos");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $turnos[$linha['id']] = $linha['nome'];
    }
}

// Buscar alunos
$alunos = [];

if ($tipoUsuario === 'Professor') {
    if ($turmaSelecionada && $classeSelecionada && $disciplinaSelecionada && $turnoSelecionado) {
        $sql = "SELECT id, nome_completo, turma_id, classe_id, turno_id 
                FROM aluno 
                WHERE turma_id = ? AND classe_id = ? AND turno_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado]);
        $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($tipoUsuario === 'Administrador') {
    if ($turmaSelecionada && $classeSelecionada && $turnoSelecionado) {
        $sql = "SELECT id, nome_completo, turma_id, classe_id, turno_id 
                FROM aluno 
                WHERE turma_id = ? AND classe_id = ? AND turno_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado]);
        $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>


<!-- HTML abaixo permanece igual -->
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Lista de alunos</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Principal</a></li>
                        <li class="breadcrumb-item active"> Alunos</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="export/export_lista_alunos.php?
                        turma=<?= urlencode($turmaSelecionada) ?>&
                        classe=<?= urlencode($classeSelecionada) ?>&
                        turno=<?= urlencode($turnoSelecionado) ?>&
                        disciplina_id=<?= urlencode($disciplinaSelecionada) ?>"
                                            class="btn btn-success">
                        Exportar Alunos CSV
                    </a>


                </div>




            </div>
        </div>



        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de alunos</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form method="get" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="turma">Turma</label>
                                    <select name="turma" id="turma" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione a turma</option>
                                        <?php foreach ($turmas as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= $id == $turmaSelecionada ? 'selected' : '' ?>>
                                                <?= $nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="classe">Classe</label>
                                    <select name="classe" id="classe" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione a classe</option>
                                        <?php foreach ($classes as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= $id == $classeSelecionada ? 'selected' : '' ?>>
                                                <?= $nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($tipoUsuario === 'Professor'): ?>
                                    <div class="col-md-3">
                                        <label for="disciplina">Disciplina</label>
                                        <select name="disciplina_id" id="disciplina" class="form-control" onchange="this.form.submit()">
                                            <option value="">Selecione a disciplina</option>
                                            <?php foreach ($disciplinas as $id => $nome): ?>
                                                <option value="<?= $id ?>" <?= $id == $disciplinaSelecionada ? 'selected' : '' ?>>
                                                    <?= $nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div class="col-md-3">
                                    <label for="turno">Turno</label>
                                    <select name="turno" id="turno" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione o turno</option>
                                        <?php foreach ($turnos as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= $id == $turnoSelecionado ? 'selected' : '' ?>>
                                                <?= $nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            </div>
                        </form>

                        <form method="post" action="">
                            <div class="table-responsive">
                                <table id="basic-datatables" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Turma</th>
                                            <th>Classe</th>
                                            <th>Turno</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($alunos) > 0): ?>
                                            <?php foreach ($alunos as $aluno): ?>
                                                <tr>
                                                    <td><?= $aluno['id'] ?></td>
                                                    <td><?= $aluno['nome_completo'] ?></td>
                                                    <td><?= $turmas[$aluno['turma_id']] ?? '' ?></td>
                                                    <td><?= $classes[$aluno['classe_id']] ?? '' ?></td>
                                                    <td><?= $turnos[$aluno['turno_id']] ?? '' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">Nenhum aluno encontrado.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /Table -->


</div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
    function salvarPresenca(selectElement, alunoId, disciplinaId) {
        const presenca = selectElement.value;

        fetch('salvar_presenca.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `aluno_id=${alunoId}&disciplina_id=${disciplinaId}&presenca=${presenca}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'ok') {
                    alert('Erro ao salvar presença!');
                }
            });
    }
</script>