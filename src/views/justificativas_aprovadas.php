<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Verifica se é admin (ajuste conforme sua lógica de login)
if ($_SESSION['tipo'] !== 'Administrador') {
    echo "Acesso negado.";
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
    f.status_justificativa,
    f.presenca
FROM frequencias f
JOIN aluno a ON f.aluno_id = a.id
JOIN disciplinas d ON f.disciplina_id = d.id
WHERE f.presenca = 0
ORDER BY f.data DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$faltasAprovadas = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Faltas Aprovadas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Faltas Justificadas</li>
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
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($faltasAprovadas as $falta): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($falta['aluno']) ?></td>
                                            <td><?= htmlspecialchars($falta['disciplina']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($falta['data'])) ?></td>
                                            <td><?= nl2br(htmlspecialchars($falta['justificativa_texto'])) ?></td>
                                            <td>
                                                <?php if ($falta['comprovativo']): ?>
                                                    <a href="../uploads/comprovativos/<?= htmlspecialchars($falta['comprovativo']) ?>" target="_blank">
                                                        <img src="../uploads/comprovativos/<?= htmlspecialchars($falta['comprovativo']) ?>" width="40" height="40" alt="Comprovativo">
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Sem imagem</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- STATUS -->
                                            <td>
                                                <?php if (strtolower($falta['status_justificativa']) === 'rejeitada'): ?>
                                                    <span class="badge badge-danger">Rejeitada</span>
                                                <?php elseif (strtolower($falta['status_justificativa']) === 'aprovada'): ?>
                                                    <span class="badge badge-success">Aprovada</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary"><?= htmlspecialchars($falta['status_justificativa']) ?></span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- AÇÕES -->
                                            <td>
                                                <?php if (strtolower($falta['status_justificativa'])): ?>

                                                    <!-- Ícone Marcar como Presente -->
                                                    <button class="btn btn-link text-success" data-toggle="modal" data-target="#modalPresenca<?= $falta['id'] ?>" title="Marcar como Presente">
                                                        <i class="fa fa-check-circle fa-lg"></i>
                                                    </button>

                                                    <!-- Modal de confirmação Presença -->
                                                    <div class="modal fade" id="modalPresenca<?= $falta['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalPresencaLabel<?= $falta['id'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <form method="POST" action="remover_falta.php">
                                                                <input type="hidden" name="frequencia_id" value="<?= $falta['id'] ?>">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-success text-white">
                                                                        <h5 class="modal-title" id="modalPresencaLabel<?= $falta['id'] ?>">Confirmar Presença</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Tem certeza que deseja <strong>marcar como presente</strong> este aluno?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-success">Sim, Confirmar</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <!-- Ícone Manter Falta -->
                                                    <button class="btn btn-link text-danger" data-toggle="modal" data-target="#modalFalta<?= $falta['id'] ?>" title="Manter Falta">
                                                        <i class="fa fa-times-circle fa-lg"></i>
                                                    </button>

                                                    <!-- Modal de confirmação Falta -->
                                                    <div class="modal fade" id="modalFalta<?= $falta['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalFaltaLabel<?= $falta['id'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <form method="POST" action="manter_falta.php">
                                                                <input type="hidden" name="frequencia_id" value="<?= $falta['id'] ?>">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-danger text-white">
                                                                        <h5 class="modal-title" id="modalFaltaLabel<?= $falta['id'] ?>">Confirmar Falta</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Tem certeza que deseja <strong>manter a falta</strong> deste aluno?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                        <button type="submit" class="btn btn-danger">Sim, Manter Falta</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                <?php else: ?>
                                                    <span class="text-muted">Sem ações</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (count($faltasAprovadas) == 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Nenhuma falta aprovada encontrada.</td>
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