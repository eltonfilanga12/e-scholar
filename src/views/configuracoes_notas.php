<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

// Carrega o controller
require_once __DIR__ . '/../controllers/FormulaNotaController.php';
$controller = new FormulaNotaController();


$msg_sucesso = $_SESSION['msg_sucesso'] ?? null;
$msg_erro = $_SESSION['msg_erro'] ?? null;
unset($_SESSION['msg_sucesso'], $_SESSION['msg_erro']);

// Ações de editar ou excluir via GET
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'excluir' && isset($_GET['id'])) {
        $controller->excluir($_GET['id']);
        // redireciona já dentro do método
    }

    if ($_GET['action'] === 'editar' && isset($_GET['id'])) {
        $formulaEditar = $controller->carregar($_GET['id']);
    }
}


// Lista fórmulas e níveis
$formulas = $controller->listar();
$niveis = $controller->listarNiveis();
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Fórmula de Notas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Fórmula de Notas</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" onclick="abrirModalAdicionar()">
                        <i class="fa fa-plus"></i> Nova Fórmula
                    </a>
                </div>
            </div>
        </div>

        <!-- Exibe mensagens -->
        <?php if ($msg_sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $msg_sucesso ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if ($msg_erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $msg_erro ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Tabela de fórmulas -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Fórmulas de Cálculo</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nível</th>
                                        <th>Nome</th>
                                        <th>Qtd. Testes</th>
                                        <th>Peso Testes (%)</th>
                                        <th>Qtd. Trabalhos</th>
                                        <th>Peso Trabalhos (%)</th>
                                        <th>Fórmula</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($formulas as $f): ?>
                                        <tr>
                                            <td><?= $f['id'] ?></td>
                                            <td><?= $f['nivel_nome'] ?></td>
                                            <td><?= $f['nome_formula'] ?></td>
                                            <td><?= $f['num_testes'] ?></td>
                                            <td><?= $f['peso_testes'] ?>%</td>
                                            <td><?= $f['num_trabalhos'] ?></td>
                                            <td><?= $f['peso_trabalhos'] ?>%</td>
                                            <td><?= $f['num_testes'] ?> Testes (<?= $f['peso_testes'] ?>%) +
                                                <?= $f['num_trabalhos'] ?> Trabalhos (<?= $f['peso_trabalhos'] ?>%)
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-warning" title="Editar" onclick='editarFormula(<?= json_encode($f) ?>)'>
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="?action=excluir&id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Deseja realmente excluir?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
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

    <!-- Modal Configuração da Fórmula -->
    <div class="modal fade" id="modalFormula" tabindex="-1" role="dialog" aria-labelledby="modalFormulaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formFormula" method="post" action="../controllers/FormulaNotaController.php">
                <input type="hidden" name="id" id="formula_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFormulaLabel">Configurar Fórmula de Notas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nivel_id">Nível</label>
                            <select class="form-control" name="nivel_id" id="nivel_id" required>
                                <option value="">Selecione o nível</option>
                                <?php foreach ($niveis as $n): ?>
                                    <option value="<?= $n['id'] ?>"><?= $n['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nome">Nome da Fórmula</label>
                            <input type="text" class="form-control" name="nome_formula" id="nome" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="num_testes">Quantidade de Testes</label>
                                <input type="number" class="form-control" name="num_testes" id="num_testes" min="0" max="10" required>
                            </div>
                            <div class="col-md-6">
                                <label for="peso_testes">Peso dos Testes (%)</label>
                                <input type="number" class="form-control" name="peso_testes" id="peso_testes" min="0" max="100" required>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="num_trabalhos">Quantidade de Trabalhos</label>
                                <input type="number" class="form-control" name="num_trabalhos" id="num_trabalhos" min="0" max="10" required>
                            </div>
                            <div class="col-md-6">
                                <label for="peso_trabalhos">Peso dos Trabalhos (%)</label>
                                <input type="number" class="form-control" name="peso_trabalhos" id="peso_trabalhos" min="0" max="100" required>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <b>Regra:</b> A soma dos pesos deve ser 100% e o cálculo final será convertido para um máximo de <b>20 valores</b>.<br>
                            <small>Exemplo: (Média dos Testes × 60%) + (Média dos Trabalhos × 40%)</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" name="salvar_formula">Salvar Fórmula</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
    function editarFormula(f) {
        document.getElementById('formula_id').value = f.id;
        document.getElementById('nivel_id').value = f.nivel_id;
        document.getElementById('nome').value = f.nome_formula;
        document.getElementById('num_testes').value = f.num_testes;
        document.getElementById('peso_testes').value = f.peso_testes;
        document.getElementById('num_trabalhos').value = f.num_trabalhos;
        document.getElementById('peso_trabalhos').value = f.peso_trabalhos;
        $('#modalFormula').modal('show');
    }

    function abrirModalAdicionar() {
        document.getElementById('formula_id').value = '';
        document.getElementById('nivel_id').value = '';
        document.getElementById('nome').value = '';
        document.getElementById('num_testes').value = '';
        document.getElementById('peso_testes').value = '';
        document.getElementById('num_trabalhos').value = '';
        document.getElementById('peso_trabalhos').value = '';

        // Abre o modal
        $('#modalFormula').modal('show');
    }
</script>