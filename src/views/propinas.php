<?php
require_once __DIR__ . '/../controllers/PropinasResumoController.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$propinaController = new PropinasResumoController();
$alunos = $propinaController->getResumoPorAluno();
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Gestão de Propinas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Propinas</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Propinas por Aluno</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Aluno</th>
                                        <th>Classe</th>
                                        <th>Propinas a Dever</th>
                                        <th>Multas</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($alunos)):
                                        $i = 1;
                                        foreach ($alunos as $dados): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($dados['nome_aluno']) ?></td>
                                                <td><?= htmlspecialchars($dados['classe']) ?></td>
                                                <td><?= $dados['propinas_dever'] ?></td>
                                                <td><?= $dados['multas'] ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-info btn-sm detalhes-aluno"
                                                        data-aluno_id="<?= $dados['id_aluno'] ?>"
                                                        title="Ver Detalhes">
                                                        <i class="la la-eye" style="font-size:18px;"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="6">Nenhum registro encontrado.</td>
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

<!-- Modal Detalhes do Aluno -->
<div class="modal fade" id="detalhesModal" tabindex="-1" role="dialog" aria-labelledby="detalhesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesModalLabel">Detalhes das Propinas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detalhes-conteudo">
                    <p>Carregando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/partials/footer.php';
?>

