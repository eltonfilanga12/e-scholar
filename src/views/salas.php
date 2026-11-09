<?php
require_once __DIR__ . '/../controllers/SalaController.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$controller = new SalaController();

// Salvar nova sala ou atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->salvar($_POST);
    $_SESSION['mensagem'] = 'Sala salva com sucesso!';
}

// Deletar sala
if (isset($_GET['delete'])) {
    $controller->deletar($_GET['delete']);
    $_SESSION['mensagem'] = 'Sala removida com sucesso!';
}

$salas = $controller->listar();
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Salas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Painel</a></li>
                        <li class="breadcrumb-item active">Salas</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalSala"><i class="fa fa-plus"></i> Nova Sala</a>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Capacidade</th>
                                <th>Estado</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salas as $i => $sala): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($sala['nome']) ?></td>
                                    <td><?= htmlspecialchars($sala['capacidade']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $sala['estado'] === 'Livre' ? 'success' : 'danger' ?>">
                                            <?= $sala['estado'] ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-sm btn-info" onclick='editarSala(<?= json_encode($sala) ?>)'><i class="fa fa-edit"></i></a>
                                        <a href="?delete=<?= $sala['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover esta sala?')"><i class="fa fa-trash"></i></a>
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

<!-- Modal de Cadastro/Edição de Sala -->
<div class="modal fade" id="modalSala" tabindex="-1" role="dialog" aria-labelledby="modalSalaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="POST" action="salas">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSalaLabel">Cadastrar/Editar Sala</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="salaId">

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" id="salaNome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Capacidade</label>
                    <input type="number" name="capacidade" id="salaCapacidade" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" id="salaEstado" class="form-control">
                        <option value="Livre">Livre</option>
                        <option value="Ocupada">Ocupada</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarSala(sala) {
    document.getElementById('salaId').value = sala.id;
    document.getElementById('salaNome').value = sala.nome;
    document.getElementById('salaCapacidade').value = sala.capacidade;
    document.getElementById('salaEstado').value = sala.estado;
    $('#modalSala').modal('show');
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
