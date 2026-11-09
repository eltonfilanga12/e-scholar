<?php

require_once __DIR__ . '/../models/Classe.php';
require_once __DIR__ . '/../controllers/ClasseController.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';

$db = new Database();
$pdo = $db->getConnection();

$classeModel = new Classe();
$controller = new ControllerClasse();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'criar';

    if ($acao === 'editar' && !empty($_POST['id'])) {
        $controller->editar($_POST['id'], $_POST);
        $_SESSION['mensagem'] = 'Classe atualizada com sucesso!';
        LogHelper::registrar($pdo, $_SESSION['nome'], 'Alteração', 'Atualizou a classe: ' . ($_POST['nome_classe'] ?? 'Sem nome'));
    } else {
        $controller->cadastrar($_POST);
        $_SESSION['mensagem'] = 'Classe adicionada com sucesso!';
        LogHelper::registrar($pdo, $_SESSION['nome'], 'Cadastro', 'Cadastrou nova classe: ' . ($_POST['nome_classe'] ?? 'Sem nome'));
    }

    header('Location: classes');
    exit;
}

if (isset($_GET['delete'])) {
    $controller->deletar($_GET['delete']);
    $_SESSION['mensagem'] = 'Classe removida com sucesso!';
    LogHelper::registrar($pdo, $_SESSION['nome'], 'Exclusão', 'Removeu a classe com ID: ' . $_GET['delete']);
    header('Location: classes');
    exit;
}

$classes = $classeModel->listarClasses();

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
                    <h3 class="page-title">Lista de Classes</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Classes</li>
                    </ul>
                </div>

                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modalClasse" onclick="abrirModalClasse()">
                        <i class="fa fa-plus"></i> Adicionar Classe
                    </a>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Classes</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome da Classe</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $index => $classe): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($classe['nome_classe']) ?></td>
                                            <td><?= htmlspecialchars($classe['descricao']) ?></td>
                                            <td>
                                                <span class="badge bg-inverse-<?= $classe['status'] === 'Ativa' ? 'success' : 'danger' ?>">
                                                    <?= $classe['status'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item btn-editar-classe"
                                                            href="#"
                                                            data-id="<?= $classe['id_classe'] ?>"
                                                            data-nome="<?= $classe['nome_classe'] ?>"
                                                            data-descricao="<?= $classe['descricao'] ?>"
                                                            data-status="<?= $classe['status'] ?>"
                                                            data-toggle="modal"
                                                            data-target="#modalClasse">
                                                            <i class="fa fa-pencil m-r-5"></i> Editar
                                                        </a>
                                                        <a class="dropdown-item" href="classes.php?delete=<?= $classe['id_classe'] ?>" onclick="return confirm('Tem certeza que deseja remover esta classe?');">
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

    </div>
</div>

<!-- Modal Classe -->
<div class="modal fade" id="modalClasse" tabindex="-1" role="dialog" aria-labelledby="modalClasseLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="classes" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalClasseLabel">Nova Classe</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="classeId">
                <input type="hidden" name="acao" value="criar" id="classeAcao">

                <div class="form-group">
                    <label for="nome_classe">Nome da Classe</label>
                    <input type="text" name="nome_classe" class="form-control" id="classeNome" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" class="form-control" id="classeDescricao" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" class="form-control" id="classeStatus" required>
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

<script>
    function abrirModalClasse() {
        document.getElementById('classeId').value = '';
        document.getElementById('classeNome').value = '';
        document.getElementById('classeDescricao').value = '';
        document.getElementById('classeStatus').value = 'Ativa';
        document.getElementById('classeAcao').value = 'criar';
    }

    document.querySelectorAll('.btn-editar-classe').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('classeId').value = this.dataset.id;
            document.getElementById('classeNome').value = this.dataset.nome;
            document.getElementById('classeDescricao').value = this.dataset.descricao;
            document.getElementById('classeStatus').value = this.dataset.status;
            document.getElementById('classeAcao').value = 'editar';
        });
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<?php if (isset($_SESSION['mensagem'])): ?>
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
        window.addEventListener('DOMContentLoaded', () => {
            $('#modalMensagem').modal('show');
        });
    </script>

    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>