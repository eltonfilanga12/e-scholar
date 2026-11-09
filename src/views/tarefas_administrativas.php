<?php
require_once __DIR__ . '/../controllers/TarefaController.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$controller = new TarefaController();

// Buscar funcionários para o <select>
$db = new Database();
$pdo = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->salvar($_POST);
    $_SESSION['mensagem'] = 'Tarefa salva com sucesso!';
    
}

if (isset($_GET['delete'])) {
    $controller->deletar($_GET['delete']);
    $_SESSION['mensagem'] = 'Tarefa removida com sucesso!';
   
}

$tarefas = $controller->listar();


$funcionarios = $pdo->query("SELECT id, nome FROM funcionarios")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Tarefas Administrativas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Painel</a></li>
                        <li class="breadcrumb-item active">Tarefas</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalTarefa"><i class="fa fa-plus"></i> Nova Tarefa</a>
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
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Funcionário</th>
                                <th>Status</th>
                                <th>Prazo</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarefas as $i => $t): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($t['titulo']) ?></td>
                                <td><?= htmlspecialchars($t['descricao']) ?></td>
                                <td><?= htmlspecialchars($t['nome']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $t['status'] === 'Concluído' ? 'success' : ($t['status'] === 'Atrasado' ? 'danger' : 'warning') ?>">
                                        <?= $t['status'] ?>
                                    </span>
                                </td>
                                <td><?= $t['prazo'] ?></td>
                                <td class="text-right">
                                    <a href="#" class="btn btn-sm btn-info" onclick='editarTarefa(<?= json_encode($t) ?>)'><i class="fa fa-edit"></i></a>
                                    <a href="?delete=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover esta tarefa?')"><i class="fa fa-trash"></i></a>
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

<!-- Modal de Cadastro/Edição de Tarefa -->
<div class="modal fade" id="modalTarefa" tabindex="-1" role="dialog" aria-labelledby="modalTarefaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="POST" action="tarefas_administrativas.php">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTarefaLabel">Cadastrar/Editar Tarefa</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="tarefaId">

                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" id="tarefaTitulo" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" id="tarefaDescricao" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Funcionário Responsável</label>
                    <select name="funcionario_id" id="tarefaFuncionario" class="form-control" required>
                        <option value="">Selecione</option>
                        <?php foreach ($funcionarios as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= $f['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="tarefaStatus" class="form-control">
                        <option value="Pendente">Pendente</option>
                        <option value="Concluído">Concluído</option>
                        <option value="Atrasado">Atrasado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Prazo</label>
                    <input type="date" name="prazo" id="tarefaPrazo" class="form-control">
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
function editarTarefa(tarefa) {
    document.getElementById('tarefaId').value = tarefa.id;
    document.getElementById('tarefaTitulo').value = tarefa.titulo;
    document.getElementById('tarefaDescricao').value = tarefa.descricao;
    document.getElementById('tarefaFuncionario').value = tarefa.funcionario_id;
    document.getElementById('tarefaStatus').value = tarefa.status;
    document.getElementById('tarefaPrazo').value = tarefa.prazo;
    $('#modalTarefa').modal('show');
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
