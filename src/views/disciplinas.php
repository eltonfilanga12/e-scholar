<?php

require_once __DIR__ . '/../controllers/DisciplinaControllerr.php';

$controller = new DisciplinaController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $controller->atualizarDisciplina($_POST);
        $_SESSION['mensagem'] = 'Disciplina atualizada com sucesso!';
    } else {
        $controller->criarDisciplina($_POST);
        $_SESSION['mensagem'] = 'Disciplina adicionada com sucesso!';
    }
    header("Location: disciplinas.php");
    exit;
}

if (isset($_GET['delete'])) {
    $controller->deletarDisciplina($_GET['delete']);
    $_SESSION['mensagem'] = 'Disciplina removida com sucesso!';
    header("Location: disciplinas.php");
    exit;
}

$disciplinas = $controller->listarDisciplinas();

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
                    <h3 class="page-title">Disciplinas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Disciplinas</li>
                    </ul>
                </div>

                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalDisciplina"
                        onclick="abrirModalAdicionar()"> <i class="fa fa-plus"></i> Adicionar Disciplina</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Table -->
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Disciplinas</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Código</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $index = 1;
                                    while ($row = $disciplinas->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>
                                            <td>{$index}</td>
                                            <td>{$row['nome']}</td>
                                            <td>{$row['codigo']}</td>
                                            <td><span class='badge bg-inverse-" . ($row['status'] === 'Ativa' ? 'success' : 'danger') . "'>{$row['status']}</span></td>
                                            <td class='text-center'>
                                                <div class='dropdown dropdown-action'>
                                                    <a href='#' class='action-icon dropdown-toggle' data-toggle='dropdown'><i class='material-icons'>more_vert</i></a>
                                                    <div class='dropdown-menu dropdown-menu-right'>
                                                        <a class='dropdown-item' href='#' 
                                                            data-toggle='modal' data-target='#modalDisciplina'
                                                            onclick=\"abrirModalEditar(
                                                                '{$row['id']}', 
                                                                '{$row['nome']}', 
                                                                '{$row['codigo']}', 
                                                                '{$row['status']}'
                                                            )\">
                                                            <i class='fa fa-pencil m-r-5'></i> Editar
                                                            </a>
                                                        <a class='dropdown-item' href='?delete={$row['id']}'><i class='fa fa-trash-o m-r-5'></i> Remover</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>";
                                        $index++;
                                    }
                                    ?>

                                    <!-- Outras disciplinas aqui -->
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

<!-- Modal Adicionar Disciplina -->
<!-- Modal Adicionar/Editar Disciplina -->
<div class="modal fade" id="modalDisciplina" tabindex="-1" role="dialog" aria-labelledby="modalDisciplinaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDisciplinaLabel">Adicionar Disciplina</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="disciplinaId">

                    <div class="form-group">
                        <label for="nome">Nome da Disciplina</label>
                        <input type="text" name="nome" id="disciplinaNome" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="codigo">Código da Disciplina</label>
                        <input type="text" name="codigo" id="disciplinaCodigo" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="disciplinaStatus" class="form-control" required>
                            <option value="Ativa">Ativa</option>
                            <option value="Inativa">Inativa</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<?php if (isset($_SESSION['mensagem'])): ?>
    <!-- Modal de Sucesso -->
    <div class="modal fade" id="modalMensagem" tabindex="-1" role="dialog" aria-labelledby="mensagemLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="mensagemLabel">Sucesso</h5>
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




