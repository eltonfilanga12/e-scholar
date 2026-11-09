<?php

require_once __DIR__ . '/../models/Classe.php';
require_once __DIR__ . '/../controllers/turmaController.php';

$classeModel = new Classe();
$classes = $classeModel->listarClasses();


$controller = new ControllerTurma();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'criar';

    if ($acao === 'editar' && !empty($_POST['id'])) {
        $controller->editar($_POST['id'], $_POST);
        $_SESSION['mensagem'] = 'Turma atualizada com sucesso!';
    } else {
        $controller->cadastrar($_POST);
        $_SESSION['mensagem'] = 'Turma adicionada com sucesso!';
    }

    header('Location: turmas.php');
    exit;
}

if (isset($_GET['delete'])) {
    $controller->deletar($_GET['delete']);
    $_SESSION['mensagem'] = 'Turma removida com sucesso!';
    header('Location: turmas.php');
    exit;
}

$turmas = $controller->listar();

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Lista de Turmas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Turmas</li>
                    </ul>
                </div>

                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modalTurma" onclick="abrirModalAdicionar()">
                        <i class="fa fa-plus"></i> Adicionar Turma
                    </a>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Turmas</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome da Turma</th>
                                        <th>Classe</th>
                                        <th>Turno</th>
                                        <th>Ano Letivo</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($turmas as $index => $turma): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($turma['nome_turma']) ?></td>
                                            <td><?= htmlspecialchars($turma['nome_classe']) ?></td>
                                            <td><?= htmlspecialchars($turma['nome_turno']) ?></td>
                                            <td><?= htmlspecialchars($turma['ano_letivo']) ?></td>
                                            <td><span class="badge bg-inverse-<?= $turma['status'] === 'Ativa' ? 'success' : 'danger' ?>">
                                                    <?= htmlspecialchars($turma['status']) ?>
                                                </span></td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item btn-editar-turma"
                                                            href="#"
                                                            data-id="<?= $turma['id_turma'] ?>"
                                                            data-nome="<?= $turma['nome_turma'] ?>"
                                                            data-classe="<?= $turma['classe_id'] ?>"
                                                            data-turno="<?= $turma['turno_id'] ?>"
                                                            data-ano="<?= $turma['ano_letivo'] ?>"
                                                            data-status="<?= $turma['status'] ?>"
                                                            data-toggle="modal"
                                                            data-target="#modalTurma">
                                                            <i class="fa fa-pencil m-r-5"></i> Editar
                                                        </a>
                                                        <a class="dropdown-item" href="turmas.php?delete=<?= $turma['id_turma'] ?>" onclick="return confirm('Tem certeza que deseja remover esta turma?');">
                                                            <i class="fa fa-trash-o m-r-5"></i> Remover
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Table -->

    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->

<!-- Modal Turma -->
<div class="modal fade" id="modalTurma" tabindex="-1" role="dialog" aria-labelledby="modalTurmaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="turmas.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTurmaLabel">Nova Turma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="turmaId">
                <input type="hidden" name="acao" value="criar" id="turmaAcao">


                <div class="form-group">
                    <label for="nome">Nome da Turma</label>
                    <input type="text" name="nome" class="form-control" id="turmaNome" required>
                </div>

                <div class="form-group">
                    <label for="classe_id">Classe</label>
                    <select name="classe_id" class="form-control" id="turmaClasse" required>
                        <option value="">Selecione</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= htmlspecialchars($classe['id_classe']) ?>">
                                <?= htmlspecialchars($classe['nome_classe']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </div>

                <div class="form-group">
                    <label for="turno">Turno</label>
                    <select name="turno" class="form-control" id="turmaTurno" required>
                        <option value="1">Manhã</option>
                        <option value="2">Tarde</option>
                        <option value="3">Noite</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ano_letivo">Ano Letivo</label>
                    <input type="number" name="ano_letivo" class="form-control" id="turmaAno" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" class="form-control" id="turmaStatus" required>
                        <option value="Ativa">Ativa</option>
                        <option value="Inativa">Inativa</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<?php if (isset($_SESSION['mensagem'])): ?>
    <!-- Modal de Sucesso -->
    <div class="modal fade" id="modalMensagem" tabindex="-1" role="dialog" aria-labelledby="mensagemLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="mensagemLabel">Mensagem</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <?= $_SESSION['mensagem']; ?>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Fechar</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#modalMensagem').modal('show');
        });
    </script>

    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>
