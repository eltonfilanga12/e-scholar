<?php

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../models/Aviso.php';
require_once __DIR__ . '/../../config/database.php';

// Instancia o objeto e obtém a conexão PDO
$db = new Database();
$pdo = $db->getConnection();


// Verifica se o professor está logado
$id_prof = $_SESSION['nome'] ?? null;


$avisosModel = new Aviso();
$tipoUsuario = 'Professor'; // Agora é para o professor
$avisos = $avisosModel->listarPorUsuario($tipoUsuario);


$queryProf = $pdo->prepare("
    SELECT 
        p.id_professor,
        f.nome AS nome_completo,
        p.email,
        p.contacto,
        p.genero,
        p.data_nascimento,
        p.grau_academico,
        p.especialidade,
        p.status,
        p.created_at,
        p.updated_at
    FROM professores p
    INNER JOIN funcionarios f ON p.funcionario_id = f.id
    WHERE f.nome = ?
");
$queryProf->execute([$id_prof]);
$prof = $queryProf->fetch();

$nomeProf = $prof['nome_completo'] ?? 'Professor';
$professor_id = $prof['id_professor'];


// Quantidade de turmas
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT turma_id) as total_turmas FROM professor_turmas WHERE professor_id = ?");
$stmt->execute([$professor_id]);
$total_turmas = $stmt->fetch()['total_turmas'] ?? 0;

// Quantidade de disciplinas
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT disciplina_id) as total_disciplinas FROM professor_disciplinas WHERE professor_id = ?");
$stmt->execute([$professor_id]);
$total_disciplinas = $stmt->fetch()['total_disciplinas'] ?? 0;

// Quantidade de aulas hoje
$stmt = $pdo->prepare("
    SELECT COUNT(*) as aulas_hoje
    FROM grade_horaria
    WHERE dia_semana_id = DAYOFWEEK(CURDATE())
      AND disciplina_id IN (
        SELECT disciplina_id FROM professor_disciplinas WHERE professor_id = ?
      )
      AND turma_id IN (
        SELECT turma_id FROM professor_turmas WHERE professor_id = ?
      )
");
$stmt->execute([$professor_id, $professor_id]);
$aulas_hoje = $stmt->fetch()['aulas_hoje'] ?? 0;

// Notas lançadas (supondo que há tabela 'notas' com campo 'professor_id')
$stmt = $pdo->prepare("SELECT COUNT(*) as total_notas FROM nota WHERE professor_id = ?");
$stmt->execute([$professor_id]);
$total_notas = $stmt->fetch()['total_notas'] ?? 0;

// Buscar dias da semana
$dias = [];
$stmtDias = $pdo->query("SELECT id, nome FROM dias_semana ORDER BY id ASC");
while ($d = $stmtDias->fetch()) {
    $dias[$d['id']] = $d['nome'];
}

// Buscar horários
$tempos = [];
$stmtTempos = $pdo->query("SELECT id, tempo, hora_inicio, hora_fim FROM horarios ORDER BY tempo ASC");
while ($t = $stmtTempos->fetch()) {
    $tempos[$t['tempo']] = ['id' => $t['id'], 'hora_inicio' => $t['hora_inicio'], 'hora_fim' => $t['hora_fim']];
}

// Inicializar grade vazia
$grade_professor = [];
foreach ($tempos as $tempo => $dados) {
    foreach ($dias as $dia_id => $dia_nome) {
        $grade_professor[$tempo][$dia_id] = '---';
    }
}

// Buscar grade horária do professor
$query = $pdo->prepare("
    SELECT 
        gh.horario_id, 
        h.tempo, 
        gh.dia_semana_id, 
        dis.nome AS disciplina, 
        t.nome_turma, 
        c.nome_classe
    FROM grade_horaria gh
    INNER JOIN horarios h ON gh.horario_id = h.id
    INNER JOIN dias_semana d ON gh.dia_semana_id = d.id
    INNER JOIN disciplinas dis ON gh.disciplina_id = dis.id
    INNER JOIN turmas t ON gh.turma_id = t.id_turma
    INNER JOIN classe c ON gh.classe_id = c.id_classe
    WHERE gh.disciplina_id IN (
        SELECT disciplina_id FROM professor_disciplinas WHERE professor_id = :prof_id
    )
    AND gh.turma_id IN (
        SELECT turma_id FROM professor_turmas WHERE professor_id = :prof_id
    )
");

$query->execute(['prof_id' => $professor_id]);

while ($linha = $query->fetch()) {
    $tempo = $linha['tempo'];
    $dia_id = $linha['dia_semana_id'];
    $disciplina = $linha['disciplina'];
    $turma = $linha['nome_turma'];
    $classe = $linha['nome_classe'];

    $grade_professor[$tempo][$dia_id] = "$disciplina<br><small>($turma - $classe)</small>";
}
?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Bem-vindo Professor!</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Painel do Professor</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-users"></i></span>
                        <div class="dash-widget-info">
                            <h3><?= $total_turmas ?></h3>
                            <span>Turmas</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
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
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-calendar"></i></span>
                        <div class="dash-widget-info">
                            <h3><?= $aulas_hoje ?></h3>
                            <span>Aulas Hoje</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-pencil"></i></span>
                        <div class="dash-widget-info">
                            <h3><?= $total_notas ?></h3>
                            <span>Notas Lançadas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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


        <!-- Resumo das disciplinas -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Resumo das Disciplinas</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Disciplina</th>
                                    <th>Turma</th>
                                    <th>Alunos</th>
                                    <th>Aulas Semanais</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT 
                                    d.nome AS disciplina, 
                                    t.nome_turma AS turma,
                                    c.nome_classe AS classe,
                                    (SELECT COUNT(*) 
                                    FROM aluno 
                                    WHERE turma_id = t.id_turma AND classe_id = c.id_classe) AS total_alunos,
                                    (SELECT COUNT(*) 
                                    FROM grade_horaria 
                                    WHERE disciplina_id = d.id 
                                    AND turma_id = t.id_turma 
                                    AND classe_id = c.id_classe) AS aulas_semanais
                                FROM professor_disciplinas pd
                                JOIN disciplinas d ON pd.disciplina_id = d.id
                                JOIN professor_turmas pt ON pt.professor_id = pd.professor_id
                                JOIN turmas t ON pt.turma_id = t.id_turma
                                JOIN classe c ON t.classe_id = c.id_classe
                                WHERE pd.professor_id = ?
                                GROUP BY d.id, t.id_turma, c.id_classe;
                                ");
                                $stmt->execute([$professor_id]);
                                $resumos = $stmt->fetchAll();

                                foreach ($resumos as $resumo) {
                                    echo "<tr>
                                            <td>{$resumo['disciplina']}</td>
                                            <td>{$resumo['turma']}</td>
                                            <td>{$resumo['total_alunos']}</td>
                                            <td>{$resumo['aulas_semanais']}</td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title">Grade Horária Semanal</h4>
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Horário</th>
                                <?php foreach ($dias as $dia): ?>
                                    <th><?= ucfirst($dia) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tempos as $tempo => $dados): ?>
                                <tr>
                                    <td><small><?= htmlspecialchars($dados['hora_inicio']) ?> - <?= htmlspecialchars($dados['hora_fim']) ?></small></td>
                                    <?php foreach (array_keys($dias) as $diaId): ?>
                                        <td class="<?= $grade_professor[$tempo][$diaId] === '---' ? 'text-muted' : 'text-dark' ?>">
                                            <?= $grade_professor[$tempo][$diaId] ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- /Page Wrapper -->

<?php require_once __DIR__ . '/partials/footer.php'; ?>