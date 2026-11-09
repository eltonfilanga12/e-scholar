<?php
require_once __DIR__ . '/../controllers/PropinaController.php'; // Certifique-se de que o caminho para o controller está correto

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';


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



if (!isset($_SESSION['nome'])) {
    // redirecionar para login ou mostrar erro
    header('Location: login.php');
    exit;
}

$nomeAluno = $_SESSION['nome'];

$propinaController = new PropinaController();
$faturas = $propinaController->getPropinasByNomeAluno($nomeAluno);



?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title"></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Principal</a></li>
                        <li class="breadcrumb-item active">Pagamento</li>
                        <li class="breadcrumb-item active">Facturas</li>
                    </ul>
                </div>

            </div>
        </div>
        <!-- /Page Header -->

        <!-- Table -->
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Minhas Facturas</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>NOME DO ALUNO</th>
                                        <th>MÊS</th>
                                        <th>CUSTO</th>
                                        <th>ENTIDADE</th>
                                        <th>REFERÊNCIA</th>
                                        <th>SITUAÇÃO</th>
                                        <th>PRAZO DOC</th>
                                        <th>IMPRIMIR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($faturas)) {
                                        foreach ($faturas as $index => $fatura) { ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= $fatura['nome_aluno'] ?></td>
                                                <td><?php echo htmlspecialchars($meses[$fatura['mes']] ?? $row['mes']); ?></td>
                                                <td><?= number_format($fatura['custo'], 2, ',', '.') ?> MT</td>
                                                <td><?= $fatura['entidade'] ?></td>
                                                <td><?= $fatura['referencia'] ?></td>
                                                <td>
                                                    <!-- Cor para a situação -->
                                                    <?php if ($fatura['situacao'] == 'Paga') { ?>
                                                        <span style="color: green; font-weight: bold;">Paga</span>
                                                    <?php } else { ?>
                                                        <span style="color: red; font-weight: bold;">Não Paga</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?= $fatura['prazo_doc'] ?></td>
                                                <td class="text-center">
                                                    <a href="imprimir_fatura.php?id=<?= $fatura['id_propinas'] ?>" target="_blank" title="Imprimir"><i class="fa fa-print"></i></a>
                                                </td>
                                            </tr>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="8">Nenhuma fatura encontrada.</td>
                                        </tr>
                                    <?php } ?>
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


<?php require_once __DIR__ . '/partials/footer.php'; ?>