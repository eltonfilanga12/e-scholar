<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/partials/functions.php';

$db = new Database();
$pdo = $db->getConnection();

$id_prof = $_SESSION['nome'] ?? null;
$tipoUsuario = $_SESSION['tipo'] ?? 'Professor';

$anoLectivoAtual = date('Y');
$idTrimestreAtual = getTrimestreAtualPDO($pdo, $anoLectivoAtual);

$queryProf = $pdo->prepare("SELECT p.id_professor, f.nome AS nome_funcionario FROM professores p INNER JOIN funcionarios f ON p.funcionario_id = f.id WHERE f.nome = ?");
$queryProf->execute([$id_prof]);
$prof = $queryProf->fetch();
$nomeProf = $prof['nome_funcionario'] ?? 'Professor';
$id_professor = $prof['id_professor'] ?? null;

$turmaSelecionada = $_GET['turma'] ?? '';
$classeSelecionada = $_GET['classe'] ?? '';
$disciplinaSelecionada = $_GET['disciplina_id'] ?? '';

$turmas = [];
$classes = [];
$disciplinas = [];


if ($tipoUsuario === 'Administrador') {
    $stmt = $pdo->query("SELECT id_turma, nome_turma, classe_id FROM turmas");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $turmas[$row['id_turma']] = $row['nome_turma'];

    $stmtClasse = $pdo->query("SELECT id_classe, nome_classe FROM classe");
    while ($classe = $stmtClasse->fetch(PDO::FETCH_ASSOC)) $classes[$classe['id_classe']] = $classe['nome_classe'];

    $stmtDisciplinas = $pdo->query("SELECT id, nome FROM disciplinas");
    while ($row = $stmtDisciplinas->fetch(PDO::FETCH_ASSOC)) $disciplinas[$row['id']] = $row['nome'];
} else {
    if ($id_professor) {
        $stmtTurmas = $pdo->prepare("SELECT DISTINCT t.id_turma, t.nome_turma, c.id_classe, c.nome_classe FROM professor_turmas pt JOIN turmas t ON pt.turma_id = t.id_turma JOIN classe c ON t.classe_id = c.id_classe WHERE pt.professor_id = ?");
        $stmtTurmas->execute([$id_professor]);
        foreach ($stmtTurmas->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $turmas[$linha['id_turma']] = $linha['nome_turma'];
            $classes[$linha['id_classe']] = $linha['nome_classe'];
        }

        $stmtDisciplinas = $pdo->prepare("SELECT d.id, d.nome FROM professor_disciplinas pd JOIN disciplinas d ON pd.disciplina_id = d.id WHERE pd.professor_id = ?");
        $stmtDisciplinas->execute([$id_professor]);
        foreach ($stmtDisciplinas->fetchAll(PDO::FETCH_ASSOC) as $disciplina)
            $disciplinas[$disciplina['id']] = $disciplina['nome'];
    }
}

$alunos = [];
if ($turmaSelecionada && $classeSelecionada && $disciplinaSelecionada) {
    $stmt = $pdo->prepare("SELECT id, nome_completo, turma_id, classe_id FROM aluno WHERE turma_id = ? AND classe_id = ?");
    $stmt->execute([$turmaSelecionada, $classeSelecionada]);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// if ($tipoUsuario === 'Administrador') {
//     $_SESSION['status'] = "Administradores não podem lançar ou editar notas.";
// }


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_nota'])) {
    $aluno_id = $_POST['aluno_id'];
    $disciplina_id = $_POST['disciplina_id'];
    $examenormal = isset($_POST['examenormal']) ? floatval($_POST['examenormal']) : 0;
    $examerecorrencia = isset($_POST['examerecorrencia']) ? floatval($_POST['examerecorrencia']) : 0;

    // Buscar a média anterior da tabela 'nota' (caso não esteja no formulário)
    $stmtMedia = $pdo->prepare("SELECT media FROM nota WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ? AND id_trimestre = ?");
    $stmtMedia->execute([$aluno_id, $disciplina_id, $anoLectivoAtual, $idTrimestreAtual]);
    $dadosNota = $stmtMedia->fetch(PDO::FETCH_ASSOC);
    $media = isset($dadosNota['media']) ? floatval($dadosNota['media']) : 0;

    if ($examenormal >= 10) {
        $mediafinal = ($media + $examenormal) / 2;
    } elseif ($examerecorrencia > 0) {
        $mediafinal = ($media + $examerecorrencia) / 2;
    } else {
        $mediafinal = $media;
    }

    $stmtCheck = $pdo->prepare("SELECT id_notas FROM nota WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ? AND id_trimestre = ?");
    $stmtCheck->execute([$aluno_id, $disciplina_id, $anoLectivoAtual, $idTrimestreAtual]);

    if ($stmtCheck->fetch()) {
        $stmtUpdate = $pdo->prepare("UPDATE nota SET examenormal = ?, examerecorrencia = ?, mediafinal = ?, data_lancamento = NOW(), professor_id = ? WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ? AND id_trimestre = ?");
        $stmtUpdate->execute([$examenormal, $examerecorrencia, $mediafinal, $id_professor, $aluno_id, $disciplina_id, $anoLectivoAtual, $idTrimestreAtual]);
        $_SESSION['status'] = "Notas de exame atualizadas com sucesso!";
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO nota (aluno_id, disciplina_id, professor_id, examenormal, examerecorrencia, mediafinal, ano_lectivo, data_lancamento, id_trimestre) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmtInsert->execute([$aluno_id, $disciplina_id, $id_professor, $examenormal, $examerecorrencia, $mediafinal, $anoLectivoAtual, $idTrimestreAtual]);
        $_SESSION['status'] = "Notas de exame lançadas com sucesso!";
    }
}
?>



<!-- HTML -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Notas de Exame</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Home</a></li>
                        <li class="breadcrumb-item active">Notas dos alunos</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php
        if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>e-Scholar, </strong><?php echo $_SESSION['status']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria_hidden="true">&times;</span>
                </button>
            </div>
        <?php
            unset($_SESSION['status']);
        }

        ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form method="GET" action="<?= basename($_SERVER['PHP_SELF']) ?>">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="classe">Classe:</label>
                                            <select name="classe" id="classe-select" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione a classe</option>
                                                <?php foreach ($classes as $id => $nome): ?>
                                                    <option value="<?= $id ?>" <?= $id == $classeSelecionada ? 'selected' : '' ?>>
                                                        <?= $nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="turma">Turma</label>
                                            <select name="turma" id="turma-select" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione a turma</option>
                                                <?php foreach ($turmas as $id => $nome): ?>
                                                    <option value="<?= $id ?>" <?= $id == $turmaSelecionada ? 'selected' : '' ?>>
                                                        <?= $nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Disciplina:</label>
                                            <select name="disciplina_id" id="disciplina-select" class="form-control" onchange="this.form.submit()">
                                                <option value="">Selecione a Disciplina</option>
                                                <?php foreach ($disciplinas as $id => $nome): ?>
                                                    <option value="<?= $id ?>" <?= $id == $disciplinaSelecionada ? 'selected' : '' ?>>
                                                        <?= $nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th>Nome do Aluno</th>
                                        <th>Turma</th>
                                        <th>Classe</th>
                                        <th>Média</th>
                                        <th>Exame Normal</th>
                                        <th>Exame de Recorrência</th>
                                        <th>Média Final</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <?php
                                        $stmtNotas = $pdo->prepare("SELECT * FROM nota WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = YEAR(CURDATE())");
                                        $stmtNotas->execute([$aluno['id'], $disciplinaSelecionada]);
                                        $nota = $stmtNotas->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <tr>
                                            <form method="POST" action="">
                                                <input type="hidden" name="salvar_nota" value="1">
                                                <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">
                                                <input type="hidden" name="disciplina_id" value="<?= $disciplinaSelecionada ?>">

                                                <td><input type="checkbox" name="selecionado[]" value="<?= $aluno['id'] ?>"></td>
                                                <td><?= htmlspecialchars($aluno['nome_completo']) ?></td>
                                                <td><?= $turmas[$aluno['turma_id']] ?? '' ?></td>
                                                <td><?= $classes[$aluno['classe_id']] ?? '' ?></td>
                                                <td><?= $nota['media'] ?? '-' ?></td>

                                                <td><input type="number" step="0.01" name="examenormal" value="<?= $nota['examenormal'] ?? '' ?>" class="form-control"></td>
                                                <td><input type="number" step="0.01" name="examerecorrencia" value="<?= $nota['examerecorrencia'] ?? '' ?>" class="form-control"></td>
                                                 <td><?= $nota['mediafinal'] ?? '-' ?></td>
                                                <td><button type="submit" class="btn btn-sm btn-success">Salvar</button></td>


                                               
                                            </form>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>


                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>