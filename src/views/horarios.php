<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$db = new Database();
$pdo = $db->getConnection();

// Carregar dados fixos
$turmas = $pdo->query("SELECT * FROM turmas")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT * FROM classe")->fetchAll(PDO::FETCH_ASSOC);
$dias = $pdo->query("SELECT * FROM dias_semana ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
//$horarios = $pdo->query("SELECT * FROM horarios ORDER BY hora_inicio LIMIT 6")->fetchAll(PDO::FETCH_ASSOC); // 6 tempos
$disciplinas = $pdo->query("SELECT * FROM disciplinas WHERE status = 'Ativo'")->fetchAll(PDO::FETCH_ASSOC);
$turnos = $pdo->query("SELECT * FROM turnos")->fetchAll(PDO::FETCH_ASSOC);


// Mensagens
$mensagem = '';
$mensagem_tipo = '';

// Seleção turma e classe via GET
$turmaSelecionada = $_GET['turma_id'] ?? null;
$classeSelecionada = $_GET['classe_id'] ?? null;
$turnoSelecionado = $_GET['turno_id'] ?? null;
$horarios = [];
if ($turnoSelecionado) {
    $stmtHorarios = $pdo->prepare("SELECT * FROM horarios WHERE turno_id = ? ORDER BY hora_inicio");
    $stmtHorarios->execute([$turnoSelecionado]);
    $horarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
}

// Carregar grade existente da turma+classe selecionada
$gradeExistente = [];
if ($turmaSelecionada && $classeSelecionada && $turnoSelecionado) {
    $stmt = $pdo->prepare("SELECT dia_semana_id, horario_id, disciplina_id FROM grade_horaria WHERE turma_id = ? AND classe_id = ? AND turno_id = ?");
    $stmt->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $linha) {
        $gradeExistente[$linha['dia_semana_id']][$linha['horario_id']] = $linha['disciplina_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_tipo']) && $_POST['form_tipo'] === 'grade_turma_classe') {
    $turma_id = $_POST['turma_id'];
    $classe_id = $_POST['classe_id'];
    $turno_id = $_POST['turno_id'] ?? null;
    $grade = $_POST['grade'] ?? [];

    // Validar turma e classe
    $stmtValida = $pdo->prepare("SELECT COUNT(*) FROM turmas WHERE id_turma = ?");
    $stmtValida->execute([$turma_id]);
    $turmaValida = $stmtValida->fetchColumn() > 0;

    $stmtValidaClasse = $pdo->prepare("SELECT COUNT(*) FROM classe WHERE id_classe = ?");
    $stmtValidaClasse->execute([$classe_id]);
    $classeValida = $stmtValidaClasse->fetchColumn() > 0;

    $stmtValidaTurno = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE id = ?");
    $stmtValidaTurno->execute([$turno_id]);
    $turnoValido = $stmtValidaTurno->fetchColumn() > 0;


    if (!$turmaValida || !$classeValida || !$turnoValido) {
        $mensagem = "Turma, Classe ou Turno inválido.";
        $mensagem_tipo = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            // Apagar grade antiga
            $delete = $pdo->prepare("DELETE FROM grade_horaria WHERE turma_id = ? AND classe_id = ? AND turno_id = ?");
            $delete->execute([$turma_id, $classe_id, $turno_id]);


            foreach ($grade as $dia_id => $horarios_array) {
                foreach ($horarios_array as $horario_id => $disciplina_id) {
                    if ($disciplina_id == '') continue;

                    // 🔍 Buscar o professor que ensina essa disciplina nessa turma e classe
                    $stmtProf = $pdo->prepare("
            SELECT p.id_professor
            FROM professores p
            INNER JOIN professor_disciplinas pd ON p.id_professor = pd.professor_id
            INNER JOIN professor_turmas pt ON p.id_professor = pt.professor_id
            INNER JOIN professor_classe pc ON p.id_professor = pc.professor_id
            WHERE pd.disciplina_id = ?
              AND pt.turma_id = ?
              AND pc.classe_id = ?
              AND p.status = 'Ativo'
            LIMIT 1
        ");
                    $stmtProf->execute([$disciplina_id, $turma_id, $classe_id]);
                    $professor_id = $stmtProf->fetchColumn();

                  /* * if (!$professor_id) {
    // Buscar nome da disciplina
    $stmtDisc = $pdo->prepare("SELECT nome FROM disciplinas WHERE id = ?");
    $stmtDisc->execute([$disciplina_id]);
    $nomeDisciplina = $stmtDisc->fetchColumn() ?? "Desconhecida";

    
    $stmtTurma = $pdo->prepare("SELECT nome_turma FROM turmas WHERE id_turma = ?");
    $stmtTurma->execute([$turma_id]);
    $nomeTurma = $stmtTurma->fetchColumn() ?? "Desconhecida";

    
    $stmtClasse = $pdo->prepare("SELECT nome_classe FROM classe WHERE id_classe = ?");
    $stmtClasse->execute([$classe_id]);
    $nomeClasse = $stmtClasse->fetchColumn() ?? "Desconhecida";

    throw new Exception("Professor não encontrado para disciplina '$nomeDisciplina', turma '$nomeTurma' e classe '$nomeClasse'.");
}**/


                    // ⚠️ Verificar se o professor já está ocupado neste horário em outra turma
                    $stmtConflito = $pdo->prepare("
            SELECT gh.id
            FROM grade_horaria gh
            INNER JOIN professor_disciplinas pd ON gh.disciplina_id = pd.disciplina_id
            INNER JOIN professor_turmas pt ON pd.professor_id = pt.professor_id
            INNER JOIN professor_classe pc ON pd.professor_id = pc.professor_id
            WHERE gh.dia_semana_id = ? AND gh.horario_id = ?
              AND NOT (gh.turma_id = ? AND gh.classe_id = ?)
              AND pd.professor_id = ?
        ");
                    $stmtConflito->execute([$dia_id, $horario_id, $turma_id, $classe_id, $professor_id]);

                    if ($stmtConflito->rowCount() > 0) {
                        throw new Exception("Conflito: o professor (ID $professor_id) já está ocupado no dia {$dia_id}, horário {$horario_id}.");
                    }

                    // ✅ Inserir grade horária
                    $insert = $pdo->prepare("
            INSERT INTO grade_horaria (turma_id, classe_id, turno_id, dia_semana_id, horario_id, disciplina_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
                    $insert->execute([$turma_id, $classe_id, $turno_id, $dia_id, $horario_id, $disciplina_id]);
                }
            }

            $pdo->commit();
            $mensagem = "Grade horária salva com sucesso!";
            $mensagem_tipo = 'success';

            // Atualizar a grade exibida
            $gradeExistente = [];
            $stmt = $pdo->prepare("SELECT dia_semana_id, horario_id, disciplina_id 
                       FROM grade_horaria 
                       WHERE turma_id = ? AND classe_id = ? AND turno_id = ?");
            $stmt->execute([$turma_id, $classe_id, $turno_id]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($resultados as $linha) {
                $gradeExistente[$linha['dia_semana_id']][$linha['horario_id']] = $linha['disciplina_id'];
            }

            $turmaSelecionada = $turma_id;
            $classeSelecionada = $classe_id;
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensagem = "Erro ao salvar: " . $e->getMessage();
            $mensagem_tipo = 'danger';
        }
    }
}

?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Cabeçalho -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Cadastrar Grade Horária</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Grade Horária</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $mensagem_tipo ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <!-- Form para selecionar Turma e Classe -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="classe_id" class="form-label">Classe</label>
                <select name="classe_id" id="classe_id" class="form-control" required onchange="this.form.submit()">
                    <option value="">-- Selecione a classe --</option>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= $classe['id_classe'] ?>" <?= ($classe['id_classe'] == $classeSelecionada) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($classe['nome_classe']) ?? $classe['id_classe'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="turma_id" class="form-label">Turma</label>
                <select name="turma_id" id="turma_id" class="form-control" required onchange="this.form.submit()" <?= $classeSelecionada ? '' : 'disabled' ?>>
                    <option value="">-- Selecione a turma --</option>
                    <?php
                    // Se quiser filtrar turmas por classe, adapte aqui. Por enquanto mostra tudo.
                    foreach ($turmas as $t): ?>
                        <option value="<?= $t['id_turma'] ?>" <?= ($t['id_turma'] == $turmaSelecionada) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nome_turma']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="turno_id" class="form-label">Turno</label>
                <select name="turno_id" id="turno_id" class="form-control" required onchange="this.form.submit()">
                    <option value="">-- Selecione o turno --</option>
                    <?php foreach ($turnos as $turno): ?>
                        <option value="<?= $turno['id'] ?>" <?= ($turno['id'] == $turnoSelecionado) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($turno['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </form>

        <?php if ($turmaSelecionada && $classeSelecionada): ?>
            <!-- Formulário da Grade Horária -->
            <form method="POST" id="gradeForm">
                <input type="hidden" name="form_tipo" value="grade_turma_classe">
                <input type="hidden" name="turma_id" value="<?= $turmaSelecionada ?>">
                <input type="hidden" name="classe_id" value="<?= $classeSelecionada ?>">
                <input type="hidden" name="turno_id" value="<?= $turnoSelecionado ?>">


                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Horário \ Dia</th>
                            <?php foreach ($dias as $dia): ?>
                                <th><?= htmlspecialchars($dia['nome']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horarios as $horario): ?>
                            <tr>
                                <td>
                                    <?= date("H:i", strtotime($horario['hora_inicio'])) ?> - <?= date("H:i", strtotime($horario['hora_fim'])) ?>
                                </td>
                                <?php foreach ($dias as $dia): ?>
                                    <td>
                                        <select name="grade[<?= $dia['id'] ?>][<?= $horario['id'] ?>]" class="form-select">
                                            <option value="">-</option>
                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                <?php
                                                $selected = '';
                                                if (isset($gradeExistente[$dia['id']][$horario['id']]) && $gradeExistente[$dia['id']][$horario['id']] == $disciplina['id']) {
                                                    $selected = 'selected';
                                                }
                                                ?>
                                                <option value="<?= $disciplina['id'] ?>" <?= $selected ?>>
                                                    <?= htmlspecialchars($disciplina['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary">Salvar Grade</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info">Selecione a Classe e a Turma para configurar a grade horária.</div>
        <?php endif; ?>

    </div>
</div>

<?php
require_once __DIR__ . '/partials/footer.php';
?>