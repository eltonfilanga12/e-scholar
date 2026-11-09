<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

$msg_sucesso = $msg_erro = '';

$tipoUsuario = $_SESSION['tipo'] ?? 'Administrador';
$usuario = usuarioLogado();

// --- Filtros selecionados ---
$classeSelecionada = $_GET['classe'] ?? '';
$turnoSelecionado  = $_GET['turno'] ?? '';
$turmaSelecionada  = $_GET['turma'] ?? '';
$anoLectivo        = $_GET['ano'] ?? '';

// --- Aplicar atribuição ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aplicar'])) {
    $turmaId = $_POST['turma_id'] ?? null;
    $alunosSelecionados = $_POST['alunos'] ?? [];

    if ($turmaId && !empty($alunosSelecionados)) {
        // Buscar classe e turno da turma selecionada
        $stmtInfo = $pdo->prepare("SELECT classe_id, turno_id FROM turmas WHERE id_turma = ?");
        $stmtInfo->execute([$turmaId]);
        $turmaInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($turmaInfo) {
            $classeId = $turmaInfo['classe_id'];
            $turnoId  = $turmaInfo['turno_id'];

            $stmt = $pdo->prepare("UPDATE aluno SET turma_id = ?, classe_id = ?, turno_id = ? WHERE id = ?");
            $atualizados = 0;
            foreach ($alunosSelecionados as $idAluno) {
                $stmt->execute([$turmaId, $classeId, $turnoId, $idAluno]);
                $atualizados++;
            }

            $msg_sucesso = "{$atualizados} aluno(s) atribuídos com sucesso à nova turma.";
            // 🔹 Registrar log
            $descricaoLog = "{$atualizados} aluno(s) atribuídos à turma {$nomeTurma} (Classe ID: {$classeId}, Turno ID: {$turnoId})";
            LogHelper::registrar($pdo, $usuario, 'Atribuição de Turmas', $descricaoLog);
        }
    } else {
        $msg_erro = "Selecione a turma de destino e pelo menos um aluno.";
    }
}

// --- Listas principais ---
$classes = $pdo->query("SELECT id_classe, nome_classe FROM classe ORDER BY nome_classe")->fetchAll(PDO::FETCH_ASSOC);
$turnos  = $pdo->query("SELECT id, nome FROM turnos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$turmas  = $pdo->query("SELECT id_turma, nome_turma, classe_id, turno_id FROM turmas ORDER BY nome_turma")->fetchAll(PDO::FETCH_ASSOC);

// --- Buscar alunos de acordo com filtros ---
$alunos = [];
if ($classeSelecionada && $turnoSelecionado) {
    $query = "
        SELECT a.id, a.nome_completo, t.nome_turma
        FROM aluno a
        LEFT JOIN turmas t ON a.turma_id = t.id_turma
        WHERE a.classe_id = ? AND a.turno_id = ?
    ";
    $params = [$classeSelecionada, $turnoSelecionado];

    if (!empty($turmaSelecionada)) {
        $query .= " AND a.turma_id = ?";
        $params[] = $turmaSelecionada;
    }

    $query .= " ORDER BY a.nome_completo";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Atribuição de Turmas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Atribuir Turmas</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Exibe mensagens -->
        <?php if ($msg_sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $msg_sucesso ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($msg_erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $msg_erro ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- FILTROS -->
        <form method="get" id="filtroForm" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label>Classe</label>
                    <select name="classe" class="form-control filtro-auto">
                        <option value="">Selecione</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id_classe'] ?>" <?= $c['id_classe'] == $classeSelecionada ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome_classe']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Turno</label>
                    <select name="turno" class="form-control filtro-auto">
                        <option value="">Selecione</option>
                        <?php foreach ($turnos as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['id'] == $turnoSelecionado ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Turma (opcional)</label>
                    <select name="turma" class="form-control filtro-auto">
                        <option value="">Todas</option>
                        <?php foreach ($turmas as $t): ?>
                            <option value="<?= $t['id_turma'] ?>" <?= $t['id_turma'] == $turmaSelecionada ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nome_turma']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Ano Lectivo</label>
                    <select name="ano" class="form-control filtro-auto">
                        <option value="">Selecione</option>
                        <?php for ($i = 2020; $i <= date('Y') + 1; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == $anoLectivo ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- LISTA DE ALUNOS E ATRIBUIÇÃO -->
        <?php if ($classeSelecionada && $turnoSelecionado): ?>
            <form method="post">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="form-inline">
                            <label for="turma" class="mr-2 mb-0"><strong>Nova Turma:</strong></label>
                            <select name="turma_id" id="turma" class="form-control" required>
                                <option value="">Selecione a turma</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="aplicar" class="btn btn-success">
                            <i class="fa fa-check"></i> Aplicar
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>ID</th>
                                        <th>Nome Completo</th>
                                        <th>Turma Atual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($alunos) > 0): ?>
                                        <?php foreach ($alunos as $a): ?>
                                            <tr>
                                                <td><input type="checkbox" name="alunos[]" value="<?= $a['id'] ?>"></td>
                                                <td><?= $a['id'] ?></td>
                                                <td><?= htmlspecialchars($a['nome_completo']) ?></td>
                                                <td><?= $a['nome_turma'] ?: 'Sem turma' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">Nenhum aluno encontrado para os filtros selecionados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // Envia automaticamente o formulário ao mudar qualquer select
    document.querySelectorAll('.filtro-auto').forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filtroForm').submit();
        });
    });

    // Selecionar todos os alunos
    document.getElementById('selectAll')?.addEventListener('click', function() {
        document.querySelectorAll('input[name="alunos[]"]').forEach(cb => cb.checked = this.checked);
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>