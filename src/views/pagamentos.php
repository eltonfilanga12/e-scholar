<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';

require_once __DIR__ . '/../controllers/PagamentoController.php';

$controller = new PagamentoController();
$controller->salvar(); // processa o POST se for o caso
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../../config/database.php';


$db = new Database();
$pdo = $db->getConnection();

// Filtros selecionados
$turmaSelecionada      = $_GET['turma'] ?? '';
$classeSelecionada     = $_GET['classe'] ?? '';
$disciplinaSelecionada = $_GET['disciplina_id'] ?? '';
$turnoSelecionado      = $_GET['turno'] ?? '';
$mesAnoSelecionado = $_GET['mes_ano'] ?? date('Y-m'); // Se não selecionado, pega o mês/ano atual

// Arrays de filtros
$turmas = [];
$classes = [];
$turnos = [];
$mesesAnos = [];

$meses = [
    '1' => 'Janeiro',
    '2' => 'Fevereiro',
    '3' => 'Março',
    '4' => 'Abril',
    '5' => 'Maio',
    '6' => 'Junho',
    '7' => 'Julho',
    '8' => 'Agosto',
    '9' => 'Setembro',
    '10' => 'Outubro',
    '11' => 'Novembro',
    '12' => 'Dezembro'
];

// Buscar meses e anos disponíveis na tabela propinas
$stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(data, '%Y-%m') AS mes_ano FROM propinas");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
    $mesesAnos[$linha['mes_ano']] = $linha['mes_ano'];
}

// Todas as turmas
$stmt = $pdo->query("SELECT id_turma, nome_turma FROM turmas");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
    $turmas[$linha['id_turma']] = $linha['nome_turma'];
}

// Todas as classes
$stmt = $pdo->query("SELECT id_classe, nome_classe FROM classe");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
    $classes[$linha['id_classe']] = $linha['nome_classe'];
}

// Todos os turnos
$stmt = $pdo->query("SELECT id, nome FROM turnos");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
    $turnos[$linha['id']] = $linha['nome'];
}

// Buscar alunos com propinas pendentes
$alunos = [];

if ($turmaSelecionada && $classeSelecionada && $turnoSelecionado) {
    // Ajustar a consulta para considerar o filtro de Mes/Ano
    $sql = "SELECT a.id AS aluno_id, a.nome_completo, p.id_propinas,p.abreviatura, p.custo, p.situacao, p.mes
            FROM aluno a
            LEFT JOIN propinas p ON p.id_aluno = a.id
            WHERE a.turma_id = ? AND a.classe_id = ? AND a.turno_id = ?";

    // Se o filtro de mes/ano for selecionado, incluir na consulta
    if ($mesAnoSelecionado) {
        $sql .= " AND DATE_FORMAT(p.data, '%Y-%m') = ?";
    }

    $stmt = $pdo->prepare($sql);

    if ($mesAnoSelecionado) {
        $stmt->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado, $mesAnoSelecionado]);
    } else {
        $stmt->execute([$turmaSelecionada, $classeSelecionada, $turnoSelecionado]);
    }

    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Pagamentos de Propinas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Pagamentos</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="gerarfaturames.php" class="btn add-btn btn-primary">
                        <i class="fa fa-plus"></i> Requisitar Faturas
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <?php if (!empty($_SESSION['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['mensagem'];
                unset($_SESSION['mensagem']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Pagamentos Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Pagamentos</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form method="get" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="turma">Turma</label>
                                    <select name="turma" id="turma" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione a turma</option>
                                        <?php foreach ($turmas as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= $id == $turmaSelecionada ? 'selected' : '' ?>>
                                                <?= $nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="classe">Classe</label>
                                    <select name="classe" id="classe" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione a classe</option>
                                        <?php foreach ($classes as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= $id == $classeSelecionada ? 'selected' : '' ?>>
                                                <?= $nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="turno">Turno</label>
                                    <select name="turno" id="turno" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione o turno</option>
                                        <?php foreach ($turnos as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= $id == $turnoSelecionado ? 'selected' : '' ?>>
                                                <?= $nome ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="mes_ano">Mês/Ano</label>
                                    <select name="mes_ano" id="mes_ano" class="form-control" onchange="this.form.submit()">
                                        <option value="">Selecione Mês/Ano</option>
                                        <?php
                                        // Define o valor atual de Mês/Ano (YYYY-MM)
                                        $mesAnoAtual = date('Y-m');
                                        foreach ($mesesAnos as $mesAno): ?>
                                            <option value="<?= $mesAno ?>" <?= $mesAno == $mesAnoSelecionado || (!$mesAnoSelecionado && $mesAno == $mesAnoAtual) ? 'selected' : '' ?>>
                                                <?= $mesAno ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>


                            </div>
                        </form>

                        <!-- Tabela de Alunos com Propinas Pendentes -->
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Aluno</th>
                                        <th>Mês</th>
                                        <th>Tipo de Fatura</th>
                                        <th>Custo</th>
                                        <th>Situação</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($aluno['nome_completo']) ?></td>
                                            <td><?php echo htmlspecialchars($meses[$aluno['mes']]); ?></td>
                                            <!-- Exibindo o Tipo de Fatura (abreviatura) -->
                                            <td><?= htmlspecialchars($aluno['abreviatura']) ?></td>
                                            <td><?= number_format($aluno['custo'], 2, ',', '.') ?></td>
                                            <td>
                                                <!-- Cor para a situação -->
                                                <?php if ($aluno['situacao'] == 'Paga') { ?>
                                                    <span style="color: green; font-weight: bold;">Paga</span>
                                                <?php } else { ?>
                                                    <span style="color: red; font-weight: bold;">Não Paga</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <!-- Registrar pagamento -->
                                                <a href="javascript:void(0)"
                                                    data-id_propina="<?= $aluno['id_propinas'] ?>"
                                                    data-id_aluno="<?= $aluno['aluno_id'] ?>"
                                                    data-custo="<?= $aluno['custo'] ?>"
                                                    data-abreviatura="<?= $aluno['abreviatura'] ?>"
                                                    class="btn btn-success btn-sm"
                                                    title="Registrar Pagamento"
                                                    data-toggle="modal"
                                                    data-target="#pagamentosModal">
                                                    <i class="la la-money-bill-wave" style="font-size: 20px;"></i>
                                                </a>
                                                

                                                <!-- Baixar PDF -->
                                                <?php
                                                $stmtPagamento = $pdo->prepare("
                                                    SELECT id_pagamento 
                                                    FROM pagamentos 
                                                    WHERE id_propina = ? AND id_aluno = ? AND situacao = 'Paga'
                                                    ORDER BY data_pagamento DESC
                                                    LIMIT 1
                                                ");
                                                $stmtPagamento->execute([$aluno['id_propinas'], $aluno['aluno_id']]);
                                                $pagamentoPago = $stmtPagamento->fetch(PDO::FETCH_ASSOC);
                                                ?>
                                                <?php if ($pagamentoPago): ?>
                                                    <a href="baixar_comprovativo.php?id=<?= $pagamentoPago['id_pagamento'] ?>"
                                                        class="btn btn-primary btn-sm" target="_blank"
                                                        data-toggle="tooltip"
                                                        title="Baixar Comprovativo PDF">
                                                        <i class="la la-file-pdf" style="font-size: 20px;"></i>
                                                    </a>
                                                <?php endif; ?>
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
        <!-- /Pagamentos Table -->

        <!-- Modal para registrar pagamento -->
        <div class="modal fade" id="pagamentosModal" tabindex="-1" role="dialog" aria-labelledby="pagamentoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pagamentoModalLabel">Registrar Pagamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="salvar_pagamento" value="1">
                        <!-- Campos hidden para manter os filtros -->
                        <input type="hidden" name="turma" value="<?= $turmaSelecionada ?>">
                        <input type="hidden" name="classe" value="<?= $classeSelecionada ?>">
                        <input type="hidden" name="turno" value="<?= $turnoSelecionado ?>">
                        <input type="hidden" name="mes_ano" value="<?= $mesAnoSelecionado ?>">
                        <div class="modal-body">
                            <input type="hidden" name="id_propina" id="id_propina">
                            <input type="hidden" name="id_aluno" id="id_aluno">
                            <div class="form-group">
                                <label for="valor_pago">Valor Pago</label>
                                <input type="number" class="form-control" name="valor_pago" id="valor_pago" required>
                            </div>
                            <!-- Preencher automaticamente o campo de tipo de fatura -->
                            <div class="form-group">
                                <label for="tipo_fatura">Tipo de Fatura</label>
                                <input type="text" class="form-control" name="tipo_fatura" id="tipo_fatura" readonly>
                            </div>
                            <div class="form-group">
                                <label for="forma_pagamento">Forma de Pagamento</label>
                                <select class="form-control" name="forma_pagamento" required>
                                    <option value="Escola">Escola</option>
                                    <option value="Banco">Banco</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_pagamento">Data do Pagamento</label>
                                <input type="date" class="form-control" name="data_pagamento" id="data_pagamento" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="situacao">Situação</label>
                                <select class="form-control" name="situacao" required>
                                    <option value="Paga">Paga</option>
                                    <option value="Não Paga">Não Paga</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar Pagamento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
    // Preenche os campos do modal com os dados do aluno, propina, o custo e tipo de fatura
    $('#pagamentosModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var idPropina = button.data('id_propina');
        var idAluno = button.data('id_aluno');
        var custo = button.data('custo');
        var abreviatura = button.data('abreviatura'); // Pega a abreviatura da propina

        // Preenche os campos do modal
        $('#id_propina').val(idPropina);
        $('#id_aluno').val(idAluno);
        $('#valor_pago').val(custo); // Preenche o valor pago com o custo da propina
        $('#tipo_fatura').val(abreviatura); // Preenche o tipo de fatura com a abreviatura
    });

    //$('#pagamentosModal').modal('show');
</script>