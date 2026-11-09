<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/partials/functions.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/../models/SolicitacaoNota.php';
$solicitacaoModel = new SolicitacaoNota();

// Dentro do loop dos alunos...
//$solicitacaoAtiva = $solicitacaoModel->existeSolicitacaoAprovadaAtiva($aluno['id'], $disciplinaSelecionada, $campo);
//$liberado = $solicitacaoAtiva ? true : false;

$db = new Database();
$pdo = $db->getConnection();

$id_prof = $_SESSION['nome'] ?? null;
$tipoUsuario = $_SESSION['tipo'] ?? 'Professor';

$anoLectivoAtual = date('Y');
$idTrimestreAtual = getTrimestreAtualPDO($pdo, $anoLectivoAtual);

// Buscar dados do professor logado (se existir)
$queryProf = $pdo->prepare("
    SELECT p.id_professor, f.nome AS nome_funcionario
    FROM professores p
    INNER JOIN funcionarios f ON p.funcionario_id = f.id
    WHERE f.nome = ?
");
$queryProf->execute([$id_prof]);
$prof = $queryProf->fetch();

$nomeProf = $prof['nome_funcionario'] ?? $id_prof ?? 'Administrador';
$id_professor = $prof['id_professor'] ?? null;

// Filtros selecionados (GET)
$turmaSelecionada = $_GET['turma'] ?? '';
$classeSelecionada = $_GET['classe'] ?? '';
$disciplinaSelecionada = $_GET['disciplina_id'] ?? '';
$turnoSelecionado = $_GET['turno'] ?? '';

$turmas = [];
$classes = [];
$disciplinas = [];
$turnos = [];

// Carregar turnos
$stmtTurnos = $pdo->query("SELECT id, nome FROM turnos");
while ($t = $stmtTurnos->fetch(PDO::FETCH_ASSOC)) {
    $turnos[$t['id']] = $t['nome'];
}

// ADMIN: vê tudo
if ($tipoUsuario === 'Administrador') {
    $stmt = $pdo->query("SELECT id_turma, nome_turma, classe_id FROM turmas");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $turmas[$row['id_turma']] = $row['nome_turma'];
    }

    $stmtClasse = $pdo->query("SELECT id_classe, nome_classe FROM classe");
    while ($classe = $stmtClasse->fetch(PDO::FETCH_ASSOC)) {
        $classes[$classe['id_classe']] = $classe['nome_classe'];
    }

    $stmtDisciplinas = $pdo->query("SELECT id, nome FROM disciplinas");
    while ($row = $stmtDisciplinas->fetch(PDO::FETCH_ASSOC)) {
        $disciplinas[$row['id']] = $row['nome'];
    }
}
// PROFESSOR: só vê suas turmas/disciplinas
else {
    if ($id_professor) {
        $sqlTurmas = "
            SELECT DISTINCT t.id_turma, t.nome_turma, c.id_classe, c.nome_classe
            FROM professor_turmas pt
            JOIN turmas t ON pt.turma_id = t.id_turma
            JOIN classe c ON t.classe_id = c.id_classe
            WHERE pt.professor_id = ?
        ";
        $params = [$id_professor];
        if ($turnoSelecionado) {
            $sqlTurmas .= " AND t.turno_id = ?";
            $params[] = $turnoSelecionado;
        }
        $stmtTurmas = $pdo->prepare($sqlTurmas);
        $stmtTurmas->execute($params);
        $dadosTurmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dadosTurmas as $linha) {
            $turmas[$linha['id_turma']] = $linha['nome_turma'];
            if (!isset($classes[$linha['id_classe']])) {
                $classes[$linha['id_classe']] = $linha['nome_classe'];
            }
        }

        $stmtDisciplinas = $pdo->prepare("
            SELECT d.id, d.nome
            FROM professor_disciplinas pd
            JOIN disciplinas d ON pd.disciplina_id = d.id
            WHERE pd.professor_id = ?
        ");
        $stmtDisciplinas->execute([$id_professor]);
        $dadosDisciplinas = $stmtDisciplinas->fetchAll(PDO::FETCH_ASSOC);
        foreach ($dadosDisciplinas as $disciplina) {
            $disciplinas[$disciplina['id']] = $disciplina['nome'];
        }
    }
}

// Buscar alunos (quando todos os filtros GET estão presentes)
$alunos = [];
if ($turnoSelecionado && $classeSelecionada && $turmaSelecionada && $disciplinaSelecionada) {
    $sqlAlunos = "
        SELECT a.id, a.nome_completo, a.turma_id, a.classe_id
        FROM aluno a
        JOIN turmas t ON a.turma_id = t.id_turma
        WHERE a.turma_id = ? AND a.classe_id = ? AND t.turno_id = ?
    ";
    $stmtAlunos = $pdo->prepare($sqlAlunos);
    $stmtAlunos->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado]);
    $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);
}

// ---------- SALVAR NOTAS ----------
// ---------- SALVAR OU ATUALIZAR NOTA INDIVIDUAL ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['campo_nota'])) {

    $aluno_id = intval($_POST['aluno_id']);
    $disciplina_id = intval($_POST['disciplina_id']);
    $campo_nota = $_POST['campo_nota'];
    $valor = ($_POST[$campo_nota] === '' || $_POST[$campo_nota] === null) ? null : floatval($_POST[$campo_nota]);


    // Buscar turma/classe do aluno
    $stmtAluno = $pdo->prepare("SELECT turma_id, classe_id FROM aluno WHERE id = ?");
    $stmtAluno->execute([$aluno_id]);
    $alunoInfo = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if (!$alunoInfo) {
        $_SESSION['status'] = "Aluno não encontrado.";
        header("Location: lancar_notas.php");
        exit;
    }

    $turma_id_aluno = $alunoInfo['turma_id'];
    $classe_id_aluno = $alunoInfo['classe_id'];

    // Determinar professor responsável
    $id_prof_registro = null;
    if ($tipoUsuario === 'Administrador') {
        $stmt_prof = $pdo->prepare("
            SELECT pd.professor_id AS id_professor
            FROM professor_disciplinas pd
            INNER JOIN professor_turmas pt ON pd.professor_id = pt.professor_id
            INNER JOIN professor_classe pc ON pd.professor_id = pc.professor_id
            WHERE pd.disciplina_id = ? 
              AND pt.turma_id = ? 
              AND pc.classe_id = ?
            LIMIT 1
        ");
        $stmt_prof->execute([$disciplina_id, $turma_id_aluno, $classe_id_aluno]);
        $profData = $stmt_prof->fetch(PDO::FETCH_ASSOC);

        if ($profData) {
            $id_prof_registro = (int)$profData['id_professor'];
        } else {
            $_SESSION['status'] = "Professor não encontrado para disciplina/turma/classe.";
        }
    } else {
        $id_prof_registro = (int)$id_professor;
    }

    // Verificar se nota já existe
    $stmtCheck = $pdo->prepare("SELECT * FROM nota WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ?");
    $stmtCheck->execute([$aluno_id, $disciplina_id, $anoLectivoAtual]);
    $notaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($notaExistente) {
        // Atualizar apenas o campo alterado
        $sqlUpdate = "UPDATE nota SET $campo_nota = ?, professor_id = ?, data_lancamento = NOW() WHERE id_notas = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$valor, $id_prof_registro, $notaExistente['id_notas']]);
    } else {
        // Inserir nota com apenas o campo alterado
        $sqlInsert = "
            INSERT INTO nota (aluno_id, disciplina_id, professor_id, $campo_nota, ano_lectivo, id_trimestre, data_lancamento)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$aluno_id, $disciplina_id, $id_prof_registro, $valor, $anoLectivoAtual, $idTrimestreAtual]);
    }

    // Recalcular média após atualização
    $stmtNotas = $pdo->prepare("SELECT teste1, teste2, teste3, trabalho1, trabalho2, trabalho3 FROM nota WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ?");
    $stmtNotas->execute([$aluno_id, $disciplina_id, $anoLectivoAtual]);
    $notas = $stmtNotas->fetch(PDO::FETCH_ASSOC);

    if ($notas) {
        // 1) Recuperar informações do aluno
        $stmtAluno = $pdo->prepare("SELECT classe_id FROM aluno WHERE id = ?");
        $stmtAluno->execute([$aluno_id]);
        $classe_id_aluno = $stmtAluno->fetchColumn();

        // 2) Buscar nível e fórmula
        $stmtNivel = $pdo->prepare("SELECT nivel_id FROM classe WHERE id_classe = ?");
        $stmtNivel->execute([$classe_id_aluno]);
        $nivel_id = $stmtNivel->fetchColumn();

        $formula = null;
        if ($nivel_id) {
            $stmtFormula = $pdo->prepare("SELECT * FROM formulas_notas WHERE nivel_id = ? AND ativo = 1 LIMIT 1");
            $stmtFormula->execute([$nivel_id]);
            $formula = $stmtFormula->fetch(PDO::FETCH_ASSOC);
        }

        // 3) Calcular a média
        $teste1 = $notas['teste1'] ?? 0;
        $teste2 = $notas['teste2'] ?? 0;
        $teste3 = $notas['teste3'] ?? 0;
        $trabalho1 = $notas['trabalho1'] ?? 0;
        $trabalho2 = $notas['trabalho2'] ?? 0;
        $trabalho3 = $notas['trabalho3'] ?? 0;

        $media = 0;
        if ($formula) {
            $numTestes = (int)$formula['num_testes'];
            $numTrabalhos = (int)$formula['num_trabalhos'];
            $pesoTestes = (float)$formula['peso_testes'];
            $pesoTrabalhos = (float)$formula['peso_trabalhos'];

            $testes = [$teste1, $teste2, $teste3];
            $trabalhos = [$trabalho1, $trabalho2, $trabalho3];

            $somaTestes = array_sum(array_slice($testes, 0, $numTestes));
            $somaTrabalhos = array_sum(array_slice($trabalhos, 0, $numTrabalhos));

            $totalPeso = $pesoTestes + $pesoTrabalhos;
            if ($totalPeso > 0) {
                $media = (($somaTestes / max(1, $numTestes)) * $pesoTestes +
                    ($somaTrabalhos / max(1, $numTrabalhos)) * $pesoTrabalhos) / $totalPeso;
            }
            $media = round($media, 2);
        } else {
            $notasArray = [$teste1, $teste2, $teste3, $trabalho1, $trabalho2, $trabalho3];
            $validas = array_filter($notasArray, fn($n) => $n !== '' && is_numeric($n));
            $media = round(array_sum($validas) / max(1, count($validas)), 2);
        }

        // 4) Atualizar média no banco
        $stmtMedia = $pdo->prepare("UPDATE nota SET media = ? WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ?");
        $stmtMedia->execute([$media, $aluno_id, $disciplina_id, $anoLectivoAtual]);
    }


    LogHelper::registrar($pdo, $nomeProf, 'Atualização de Nota', "Campo '$campo_nota' atualizado para aluno ID $aluno_id.");
    $solicitacaoModel->marcarComoUsada($aluno_id, $disciplina_id, $campo_nota);

    $_SESSION['status'] = "Nota atualizada com sucesso!";

    $qs = http_build_query([
        'classe' => $classeSelecionada,
        'turma' => $turmaSelecionada,
        'disciplina_id' => $disciplinaSelecionada,
        'turno' => $turnoSelecionado
    ]);
}

?>

<!-- HTML e JS continuam exatamente como no teu código -->



<!-- HTML -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Notas de Frequência</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Home</a></li>
                        <li class="breadcrumb-item active">Notas dos alunos</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="export/export_notas.php?
                        turma=<?= $turmaSelecionada ?>&
                        classe=<?= $classeSelecionada ?>&
                        disciplina_id=<?= $disciplinaSelecionada ?>&
                        turno=<?= $turnoSelecionado ?>"
                        class="btn btn-success">
                        Exportar Notas CSV
                    </a>

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
                                <form method="GET" action="lancar_notas.php">
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
                                        <div class="col-md-3">
                                            <label>Turno:</label>
                                            <select name="turno" class="form-control" onchange="this.form.submit()">
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
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th data-field="nome" data-editable="false">Nome do Aluno</th>
                                        <th data-field="turma" data-editable="false">Turma</th>
                                        <th data-field="classe" data-editable="false">Classe</th>
                                        <th data-field="teste1" data-editable="false">Teste 1</th>
                                        <th data-field="teste2" data-editable="false">Teste 2</th>
                                        <th data-field="teste3" data-editable="false">Teste 3</th>
                                        <th data-field="trab1" data-editable="false">Trabalho 1</th>
                                        <th data-field="trab2" data-editable="false">Trabalho 2</th>
                                        <th data-field="trab3" data-editable="false">Trabalho 3</th>
                                        <th data-field="media" data-editable="false">Média</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <?php
                                        $stmtNotas = $pdo->prepare("
                                            SELECT teste1, teste2, teste3, trabalho1, trabalho2, trabalho3, media 
                                            FROM nota 
                                            WHERE aluno_id = ? AND disciplina_id = ? AND ano_lectivo = ?
                                            LIMIT 1
                                        ");
                                        $stmtNotas->execute([$aluno['id'], $disciplinaSelecionada, $anoLectivoAtual]);
                                        $nota = $stmtNotas->fetch(PDO::FETCH_ASSOC);

                                        $camposNotas = ['teste1', 'teste2', 'teste3', 'trabalho1', 'trabalho2', 'trabalho3'];
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" name="selecionado[]" value="<?= $aluno['id'] ?>"></td>
                                            <td><?= htmlspecialchars($aluno['nome_completo']) ?></td>
                                            <td><?= $turmas[$aluno['turma_id']] ?? '' ?></td>
                                            <td><?= $classes[$aluno['classe_id']] ?? '' ?></td>

                                            <?php foreach ($camposNotas as $campo): ?>
                                                <?php
                                                // Bloquear apenas se a nota daquele campo já existir e for numérica
                                                $bloquearCampo = false;
                                                // Bloqueia se for professor, a nota já existir e NÃO houver solicitação aprovada
                                                if ($tipoUsuario === 'Professor' && isset($nota[$campo]) && is_numeric($nota[$campo])) {
                                                    $solicitacaoAtiva = $solicitacaoModel->existeSolicitacaoAprovadaAtiva($aluno['id'], $disciplinaSelecionada, $campo);
                                                    $bloquearCampo = !$solicitacaoAtiva; // bloqueia se NÃO estiver aprovado
                                                }

                                                ?>
                                                <td>
                                                    <form method="POST" style="display:flex; gap:4px; align-items:center;" class="nota-form">
                                                        <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">
                                                        <input type="hidden" name="disciplina_id" value="<?= $disciplinaSelecionada ?>">
                                                        <input type="hidden" name="campo_nota" value="<?= $campo ?>">

                                                        <input type="text" name="<?= $campo ?>" value="<?= $nota[$campo] ?? '' ?>"
                                                            class="form-control nota-input" style="width:80px;text-align:center;"
                                                            <?= $bloquearCampo ? 'readonly' : '' ?>>

                                                        <?php if ($bloquearCampo): ?>
                                                            <button type="button" class="btn btn-warning btn-sm"
                                                                data-toggle="modal" data-target="#modalPermissao"
                                                                data-aluno="<?= htmlentities($aluno['nome_completo'], ENT_QUOTES, 'UTF-8') ?>"
                                                                data-alunoid="<?= $aluno['id'] ?>"
                                                                data-campo="<?= $campo ?>"
                                                                data-disciplina="<?= $disciplinaSelecionada ?>"
                                                                data-professor="<?= $id_professor ?>"
                                                                title="Solicitar permissão">
                                                                <i class="fa fa-lock"></i>
                                                            </button>

                                                        <?php else: ?>
                                                            <button type="submit" class="btn btn-success btn-sm" title="Salvar <?= ucfirst($campo) ?>">
                                                                <i class="fa fa-save"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </form>
                                                </td>
                                            <?php endforeach; ?>

                                            <td class="text-center"><?= $nota['media'] ?? '-' ?></td>
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

<!-- Modal de permissão para alterar nota -->
<!-- Modal de permissão para alterar nota -->
<div class="modal fade" id="modalPermissao" tabindex="-1" role="dialog" aria-labelledby="modalPermissaoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="solicitar_permissao.php">
            <input type="hidden" name="redirect_url"
                value="lancar_notas.php?classe=<?= $classeSelecionada ?>&turma=<?= $turmaSelecionada ?>&disciplina_id=<?= $disciplinaSelecionada ?>&turno=<?= $turnoSelecionado ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPermissaoLabel">Solicitar Permissão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Campos ocultos -->
                    <input type="hidden" name="aluno_id" id="alunoIdModal">
                    <input type="hidden" name="campo_nota" id="campoNotaModal">
                    <input type="hidden" name="disciplina_id" id="disciplinaModal">
                    <input type="hidden" name="professor_id" id="professorIdModal">

                    <!-- Campos visuais -->
                    <div class="form-group">
                        <label>Aluno:</label>
                        <input type="text" id="nomeAlunoModal" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Motivo para alteração da nota:</label>
                        <textarea name="motivo" class="form-control" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </form>
    </div>
</div>




<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script>
    $('#modalPermissao').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var aluno = button.data('aluno');
        var alunoId = button.data('alunoid');
        var campo = button.data('campo');
        var disciplinaId = button.data('disciplina');
        var professorId = button.data('professor');

        var modal = $(this);
        modal.find('#nomeAlunoModal').val(aluno);
        modal.find('#alunoIdModal').val(alunoId);
        modal.find('#campoNotaModal').val(campo);
        modal.find('#disciplinaModal').val(disciplinaId);
        modal.find('#professorIdModal').val(professorId);
    });




    document.querySelectorAll('.nota-input').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value;

            // Substitui vírgula por ponto
            value = value.replace(',', '.');

            // Remove letras e caracteres inválidos, exceto ponto
            value = value.replace(/[^\d.]/g, '');

            // Limita valor máximo a 20
            let floatValue = parseFloat(value);
            if (!isNaN(floatValue) && floatValue > 20) {
                value = "20";
            }

            e.target.value = value;
        });
    });
</script>