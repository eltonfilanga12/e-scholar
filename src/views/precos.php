<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../controllers/PrecoController.php';
require_once __DIR__ . '/../models/Classe.php';

$controller = new PrecoController();
$classeModel = new Classe();
$classes = $classeModel->listarClasses();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = $_POST;
    if (isset($_POST['id_precos']) && !empty($_POST['id_precos'])) {
        $controller->editar($dados);
        $_SESSION['mensagem'] = 'Preço atualizado com sucesso!';
    } else {
        $controller->salvar($dados);
        $_SESSION['mensagem'] = 'Preço cadastrado com sucesso!';
    }
    header('Location: precos');
    exit;
}

if (isset($_GET['delete'])) {
    $controller->deletar($_GET['delete']);
    $_SESSION['mensagem'] = 'Preço removido com sucesso!';
    header('Location: precos');
    exit;
}

$precos = $controller->listar();
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Gestão de Preços</h3>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalPreco">+ Novo Preço</a>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-nowrap custom-table datatable">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Inscrição</th>
                            <th>Mensalidade</th>
                            <th>Camisa</th>
                            <th>Camiseta</th>
                            <th>Calções</th>
                            <th>Meia</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($precos as $preco): ?>
                            <tr>
                                <td><?= $preco['nome_classe'] ?></td>
                                <td><?= number_format($preco['inscricao'], 2) ?> MZN</td>
                                <td><?= number_format($preco['mensalidade'], 2) ?> MZN</td>
                                <td><?= number_format($preco['camisa'], 2) ?> MZN</td>
                                <td><?= number_format($preco['camiseta'], 2) ?> MZN</td>
                                <td><?= number_format($preco['calcoes'], 2) ?> MZN</td>
                                <td><?= number_format($preco['meia'], 2) ?> MZN</td>
                                <td>
                                    <button class="btn btn-sm btn-info" 
                                            data-toggle="modal" 
                                            data-target="#modalPreco"
                                            onclick='editarPreco(<?= json_encode($preco) ?>)'>Editar</button>
                                    <a href="?delete=<?= $preco['id_precos'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover este preço?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preço -->
<div id="modalPreco" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastro de Preços</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_precos" id="id_precos">
                <div class="form-group">
                    <label>Classe</label>
                    <select name="classe_id" id="classe_id" class="form-control" required>
                        <option value="">Selecione</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?= $classe['id_classe'] ?>"><?= $classe['nome_classe'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php
                $campos = ['inscricao', 'mensalidade', 'camisa', 'camiseta', 'calcoes', 'meia'];
                foreach ($campos as $campo): ?>
                    <div class="form-group">
                        <label><?= ucfirst($campo) ?> (MZN)</label>
                        <input type="number" step="0.01" name="<?= $campo ?>" id="<?= $campo ?>" class="form-control" required>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarPreco(preco) {
    document.getElementById('id_precos').value = preco.id_precos;
    document.getElementById('classe_id').value = preco.classe_id;
    document.getElementById('inscricao').value = preco.inscricao;
    document.getElementById('mensalidade').value = preco.mensalidade;
    document.getElementById('camisa').value = preco.camisa;
    document.getElementById('camiseta').value = preco.camiseta;
    document.getElementById('calcoes').value = preco.calcoes;
    document.getElementById('meia').value = preco.meia;
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>