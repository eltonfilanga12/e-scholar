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
// Instancia o Controller de Propina
$propinaController = new PropinaController();

// Chama o método do controller para listar todas as propinas
$faturas = $propinaController->getAllPropinas();
?>


<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Gestão de Propinas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Propinas</li>
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
                        <h4 class="card-title">Propinas</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>NOME DO ALUNO</th>
                                        <th>CLASSE</th>
                                        <th>MÊS</th>
                                        <th>CUSTO</th>
                                        <th>ENTIDADE</th>
                                        <th>REFERÊNCIA</th>
                                        <th>SITUAÇÃO</th>
                                        <th>PRAZO DOC</th>
                                        <th>AÇÕES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($faturas)) {
                                        foreach ($faturas as $index => $fatura) { ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= $fatura['nome_aluno'] ?></td>
                                                <td><?= $fatura['classe'] ?></td>
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
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown">
                                                            <i class="material-icons">more_vert</i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="edit.php?id=<?= $fatura['id_propinas'] ?>"><i class="fa fa-pencil m-r-5"></i> Editar</a>
                                                            <a class="dropdown-item" href="#"><i class="fa fa-trash-o m-r-5"></i> Excluir</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
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

<?php
require_once __DIR__ . '/partials/footer.php';
?>