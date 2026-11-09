<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$nome_aluno = $_SESSION['nome'] ?? '';

$sql = "SELECT 
            a.nome_completo, 
            a.data_nascimento, 
            a.residente_em, 
            a.bairro, 
            a.nome_encarregado, 
            a.contacto_encarregado,
            a.email_pai,
            a.sexo,
            a.tipo_documento,
            a.nacionalidade,
            a.religiao,
            a.turma_id,
            t.nome_turma, 
            tu.nome AS turno, 
            c.nome_classe 
        FROM aluno a 
        JOIN turmas t ON a.turma_id = t.id_turma 
        JOIN classe c ON t.classe_id = c.id_classe 
        JOIN turnos tu ON t.turno_id = tu.id
        WHERE a.nome_completo = ?";


$stmt = $conn->prepare($sql);
$stmt->execute([$nome_aluno]);
$aluno = $stmt->fetch();

if (!$aluno) {
    echo "Aluno não encontrado.";
    exit();
}

// Criar endereço completo
$aluno['endereco'] = $aluno['residente_em'] . ', Bairro ' . $aluno['bairro'];

$dadosAcademicos = [
    'classe' => $aluno['nome_classe'],
    'turma'  => $aluno['nome_turma'],
    'turno'  => $aluno['turno']
];
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Meus dados</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Perfil</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">

                <!-- Perfil Header -->
                <div class="card profile-header mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col ml-md-n2 profile-user-info">
                                <h4 class="user-name mb-0"><?= htmlspecialchars($aluno['nome_completo']) ?></h4>
                                <div class="user-location text-muted">
                                    <i class="fa fa-map-marker"></i> <?= htmlspecialchars($aluno['endereco']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Perfil Header -->

                <!-- Menu de abas -->
                <div class="profile-menu mt-3">
                    <ul class="nav nav-tabs nav-tabs-solid">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#per_details_tab">Sobre</a>
                        </li>
                    </ul>
                </div>
                <!-- /Menu de abas -->

                <!-- Conteúdo das abas -->
                <div class="tab-content profile-tab-cont">
                    <div class="tab-pane fade show active" id="per_details_tab">
                        <div class="row">

                            <!-- Dados Pessoais -->
                            <div class="col-lg-9">
                                <div class="card">
                                    <div class="card-body">

                                        <h5 class="card-title">Dados Pessoais</h5>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Nome</p>
                                                <p><?= htmlspecialchars($aluno['nome_completo']) ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Nascimento</p>
                                                <p><?= date('d/m/Y', strtotime($aluno['data_nascimento'])) ?></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Sexo</p>
                                                <p><?= htmlspecialchars($aluno['sexo']) ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Tipo de Documento</p>
                                                <p><?= htmlspecialchars($aluno['tipo_documento']) ?></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Nacionalidade</p>
                                                <p><?= htmlspecialchars($aluno['nacionalidade']) ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Endereço</p>
                                                <p><?= htmlspecialchars($aluno['endereco']) ?></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Bairro</p>
                                                <p><?= htmlspecialchars($aluno['bairro']) ?></p>
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="card-subtitle mb-2">Dados do Encarregado</h6>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Nome do Encarregado</p>
                                                <p><?= htmlspecialchars($aluno['nome_encarregado']) ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="text-muted mb-0">Contacto do Encarregado</p>
                                                <p><?= htmlspecialchars($aluno['contacto_encarregado']) ?></p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- /Dados Pessoais -->

                            <!-- Dados Acadêmicos -->
                            <div class="col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Dados Acadêmicos</h5>
                                        <ul class="list-unstyled mb-3">
                                            <li><strong>Classe:</strong> <?= htmlspecialchars($dadosAcademicos['classe']) ?></li>
                                            <li><strong>Turma:</strong> <?= htmlspecialchars($dadosAcademicos['turma']) ?></li>
                                            <li><strong>Turno:</strong> <?= htmlspecialchars($dadosAcademicos['turno']) ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- /Dados Acadêmicos -->



                        </div> <!-- /row -->
                    </div>
                </div>
                <!-- /Conteúdo das abas -->

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
                <?php
                // Buscar faltas do aluno logado
                $sqlFaltas = "
                                SELECT f.id, d.nome, f.data, f.status_justificativa, f.comprovativo
                                FROM frequencias f
                                JOIN disciplinas d ON f.disciplina_id = d.id
                                JOIN aluno a ON f.aluno_id = a.id
                                WHERE a.nome_completo = ? AND f.presenca = 0
                                ORDER BY f.data DESC
                            ";
                $stmtFaltas = $conn->prepare($sqlFaltas);
                $stmtFaltas->execute([$nome_aluno]);
                $faltas = $stmtFaltas->fetchAll();
                ?>

                <!-- Tabela de Faltas -->
                <div class="row mt-4 mb-5">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Minhas Faltas</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Disciplina</th>
                                                <th>Data</th>
                                                <th>Justificativa</th>
                                                <th>Comprovativo</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($faltas) > 0): ?>
                                                <?php foreach ($faltas as $falta): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($falta['nome']) ?></td>
                                                        <td><?= date('d/m/Y', strtotime($falta['data'])) ?></td>
                                                        <td>
                                                            <?php if ($falta['status_justificativa']): ?>
                                                                <span class="badge badge-<?=
                                                                                            $falta['status_justificativa'] == 'Aprovada' ? 'success' : (
                                                                                                $falta['status_justificativa'] == 'Rejeitada' ? 'danger' : 'warning'
                                                                                            ) ?>">
                                                                    <?= $falta['status_justificativa'] ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">Não enviada</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($falta['comprovativo']): ?>
                                                                <a href="../uploads/comprovativos/<?= $falta['comprovativo'] ?>" target="_blank">Ver</a>
                                                            <?php else: ?>
                                                                <em>Sem foto</em>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!$falta['comprovativo']): ?>
                                                                <button class="btn btn-sm btn-primary" onclick="abrirModalJustificar(<?= $falta['id'] ?>)">
                                                                    Justificar
                                                                </button>
                                                            <?php else: ?>
                                                                <em>Enviado</em>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Sem faltas registradas.</td>
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

    </div>
</div>

<!-- Modal Justificar Falta -->
<div class="modal fade" id="modalJustificar" tabindex="-1" role="dialog" aria-labelledby="modalJustificarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="justificar_falta.php" enctype="multipart/form-data">
            <input type="hidden" name="falta_id" id="falta_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Justificar Falta</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="justificativa_texto" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Comprovativo (imagem)</label>
                        <input type="file" name="comprovativo" accept="image/*" class="form-control-file" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </form>
    </div>
</div>


<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
    function abrirModalJustificar(faltaId) {
        document.getElementById('falta_id').value = faltaId;
        $('#modalJustificar').modal('show');
    }
</script>