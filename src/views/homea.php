<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../models/Aviso.php';
// Corrija o caminho relativo do require_once para database.php conforme sua estrutura.
// Abaixo é um exemplo, ajuste conforme o caminho real
require_once __DIR__ . '../../../config/database.php';

// Instancia o objeto e obtém a conexão PDO
$db = new Database();
$pdo = $db->getConnection();

$aluno_id = $_SESSION['nome'] ?? 1;



$avisosModel = new Aviso();
$tipoUsuario = 'Aluno'; // ou pegar da sessão se tiver algo como $_SESSION['tipo_usuario']
$avisos = $avisosModel->listarPorUsuario($tipoUsuario);


// Buscar dados do aluno (nome, turma, classe e turno)
$queryAluno = $pdo->prepare("SELECT turma_id, classe_id, turno_id, nome_completo, id FROM aluno WHERE nome_completo = ?");
$queryAluno->execute([$aluno_id]);
$aluno = $queryAluno->fetch();

$nomeAluno = $aluno['nome_completo'] ?? 'Aluno';
$turma_id = $aluno['turma_id'];
$classe_id = $aluno['classe_id'];
$turno_id = $aluno['turno_id'];
$id_aluno = $aluno['id'];


// Total de aulas
$total_aulas = $pdo->query("SELECT COUNT(*) FROM frequencias WHERE aluno_id = $id_aluno")->fetchColumn();

// Total de faltas
$total_faltas = $pdo->query("SELECT COUNT(*) FROM frequencias WHERE aluno_id = $id_aluno AND presenca = 0")->fetchColumn();

// Porcentagem
$percent_faltas = $total_aulas > 0 ? ($total_faltas / $total_aulas) * 100 : 0;

// Total esperado pode ser fixo ou baseado nas disciplinas
$notas_lancadas = $pdo->query("SELECT COUNT(*) FROM nota WHERE aluno_id = $id_aluno")->fetchColumn();
$total_notas_esperadas = 48; // valor fixo ou calculado
$percent_notas = ($notas_lancadas / $total_notas_esperadas) * 100;

$pagas = $pdo->query("SELECT COUNT(*) FROM propinas WHERE id_aluno = $id_aluno AND situacao = 'Paga'")->fetchColumn();
$total_meses = 10; // ou baseado no ano lectivo
$percent_pagamento = ($pagas / $total_meses) * 100;


// Total de disciplinas
$stmt = $pdo->query("SELECT COUNT(*) FROM disciplinas");
$total_disciplinas = $stmt->fetchColumn();

// Média geral do aluno logado
$stmt = $pdo->prepare("SELECT ROUND(AVG(media), 1) FROM nota WHERE aluno_id = ?");
$stmt->execute([$id_aluno]);
$media_geral = $stmt->fetchColumn();
if ($media_geral === null) $media_geral = 0;

// Propinas pendentes do aluno logado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM propinas WHERE id_aluno = ? AND situacao = 'Não Paga'");
$stmt->execute([$id_aluno]);
$pendentes = $stmt->fetchColumn();

// Buscar lista de dias da semana ordenados
$dias = [];
$stmtDias = $pdo->query("SELECT id, nome FROM dias_semana ORDER BY id ASC");
while ($d = $stmtDias->fetch()) {
    $dias[$d['id']] = $d['nome'];
}

// Buscar lista de horários (tempo e faixa de hora)
$tempos = [];
$stmtTempos = $pdo->query("SELECT id, tempo, hora_inicio, hora_fim FROM horarios ORDER BY tempo ASC");
while ($t = $stmtTempos->fetch()) {
    $tempos[$t['tempo']] = ['id' => $t['id'], 'hora_inicio' => $t['hora_inicio'], 'hora_fim' => $t['hora_fim']];
}

// Inicializar matriz de horário vazia
$horario = [];
foreach ($tempos as $tempo => $dadosTempo) {
    foreach ($dias as $diaId => $diaNome) {
        $horario[$tempo][$diaId] = '---'; // padrão vazio
    }
}

// Buscar grade horária do aluno
if ($turma_id && $classe_id && $turno_id) {
    $query = $pdo->prepare("
        SELECT gh.horario_id, h.tempo, gh.dia_semana_id, dis.nome AS disciplina
        FROM grade_horaria gh
        INNER JOIN horarios h ON gh.horario_id = h.id
        INNER JOIN dias_semana d ON gh.dia_semana_id = d.id
        LEFT JOIN disciplinas dis ON gh.disciplina_id = dis.id
        WHERE gh.turma_id = ? AND gh.classe_id = ? AND gh.turno_id = ?
    ");
    $query->execute([$turma_id, $classe_id, $turno_id]);

    while ($linha = $query->fetch()) {
        $tempo = $linha['tempo'];
        $dia_id = $linha['dia_semana_id'];
        $disciplina = $linha['disciplina'] ?? '---';
        $horario[$tempo][$dia_id] = $disciplina;
    }
}
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Bem-vindo/a, <?= htmlspecialchars($nomeAluno) ?>!</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Painel do Aluno</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-book"></i></span>
                        <div class="dash-widget-info">
                            <h3><?= $total_disciplinas ?></h3>
                            <span>Disciplinas</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-graduation-cap"></i></span>
                        <div class="dash-widget-info">
                            <h3><?= $media_geral ?></h3>
                            <span>Média Geral</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-money"></i></span>
                        <div class="dash-widget-info">
                            <h3><?= $pendentes ?></h3>
                            <span>Propinas Pendentes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avisos -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Avisos Ativos</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php if (!empty($avisos)): ?>
                                <?php
                                $temAtivo = false;
                                foreach ($avisos as $aviso):
                                    if ($aviso['status'] === 'Ativo'):
                                        $temAtivo = true;
                                ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars($aviso['conteudo']) ?>
                                            <br>
                                            <small class="text-muted">
                                                Validade: <?= $aviso['data_validade'] ? date('d/m/Y', strtotime($aviso['data_validade'])) : 'Sem data' ?>
                                            </small>
                                        </li>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                                <?php if (!$temAtivo): ?>
                                    <li class="list-group-item text-muted">Nenhum aviso ativo.</li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li class="list-group-item text-muted">Nenhum aviso disponível.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


        <!-- Estatísticas adicionais -->
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-4 d-flex">
                <div class="card flex-fill dash-statistics">
                    <div class="card-body">
                        <h5 class="card-title">Resumo</h5>
                        <div class="stats-list">
                            <div class="stats-info">
                                <p>Aulas Faltadas <strong><?= $total_faltas ?> <small>/ <?= $total_aulas ?></small></strong></p>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" style="width: <?= round($percent_faltas, 1) ?>%"></div>
                                </div>
                            </div>
                            <div class="stats-info">
                                <p>Notas Lançadas <strong><?= $notas_lancadas ?> <small>/ <?= $total_notas_esperadas ?></small></strong></p>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?= round($percent_notas, 1) ?>%"></div>
                                </div>
                            </div>
                            <div class="stats-info">
                                <p>Propinas Pagas <strong><?= $pagas ?> <small>/ <?= $total_meses ?></small></strong></p>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: <?= round($percent_pagamento, 1) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <h4 class="card-title">Horário Semanal</h4>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="thead-light">
                    <tr>
                        <th>Horário</th>
                        <?php foreach ($dias as $nome): ?>
                            <th><?= ucfirst($nome) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tempos as $tempo => $dados): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($dados['hora_inicio']) ?> - <?= htmlspecialchars($dados['hora_fim']) ?></small></td>
                            <?php foreach (array_keys($dias) as $diaId): ?>
                                <?php $disciplina = $horario[$tempo][$diaId]; ?>
                                <td class="<?= $disciplina === '---' ? 'text-muted' : 'text-dark' ?>">
                                    <?= ucfirst($disciplina) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>