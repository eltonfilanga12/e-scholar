<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();


$id_prof = $_SESSION['nome'] ?? null;

// Buscar dados do professor
$queryProf = $conn->prepare("
    SELECT 
        p.id_professor, 
        f.nome AS nome_funcionario
    FROM 
        professores p
    INNER JOIN 
        funcionarios f ON p.funcionario_id = f.id
    WHERE 
        f.nome = ?
");

$queryProf->execute([$id_prof]);
$prof = $queryProf->fetch();

$nomeProf = $prof['nome_funcionario'] ?? 'Professor';
$professor_id = $prof['id_professor'] ?? null;

if (!$professor_id) {
    echo "Professor não autenticado.";
    exit;
}

$sql = "
SELECT 
    f.id,
    a.nome_completo AS aluno,
    d.nome AS disciplina,
    f.data,
    f.justificativa_texto,
    f.comprovativo,
    f.status_justificativa
FROM frequencias f
JOIN aluno a ON f.aluno_id = a.id
JOIN disciplinas d ON f.disciplina_id = d.id
JOIN professor_disciplinas dp ON dp.disciplina_id = d.id
WHERE dp.professor_id = ? AND f.status_justificativa = 'Pendente' AND f.presenca = 0
ORDER BY f.data DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute([$professor_id]);
$justificativas = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Justificativas Pendentes</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Justificativas</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Aluno</th>
                                        <th>Disciplina</th>
                                        <th>Data</th>
                                        <th>Justificativa</th>
                                        <th>Comprovativo</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($justificativas as $falta): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($falta['aluno']) ?></td>
                                            <td><?= htmlspecialchars($falta['disciplina']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($falta['data'])) ?></td>
                                            <td><?= nl2br(htmlspecialchars($falta['justificativa_texto'])) ?></td>
                                            <td>
                                                <?php if ($falta['comprovativo']): ?>
                                                    <a href="../uploads/comprovativos/<?= $falta['comprovativo'] ?>" target="_blank">
                                                        <img src="../uploads/comprovativos/<?= $falta['comprovativo'] ?>" width="40" height="40" alt="Comprovativo">
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Sem imagem</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-warning"><?= $falta['status_justificativa'] ?></span></td>
                                            <td>
                                                <form action="processar_justificativa.php" method="post" class="d-inline">
                                                    <input type="hidden" name="frequencia_id" value="<?= $falta['id'] ?>">
                                                    <button type="submit" name="acao" value="Aprovada" class="btn btn-success btn-sm">Aprovar</button>
                                                    <button type="submit" name="acao" value="Rejeitada" class="btn btn-danger btn-sm">Rejeitar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($justificativas) == 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Nenhuma justificativa pendente.</td>
                                        </tr>
                                    <?php endif; ?>
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