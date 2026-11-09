<?php
// Incluindo o controlador de alunos
require_once __DIR__ . '/../controllers/AlunoController.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';


?>


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
                                        <th>ID</th>
                                        <th>Nome Completo</th>
                                        <th>Classe</th>
                                        <th>Turma</th>
                                        <th>Data da Matrícula</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['nome_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($student['nome_classe']); ?></td>
                                            <td><?php echo htmlspecialchars($student['turma_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($student['data_inscricao']); ?></td>
                                            <td>
                                                <!-- Links para editar e eliminar com o ID do aluno -->
                                                <a href='gerarfaturaAluno.php?id=<?php echo htmlspecialchars($student['id']); ?>&nome=<?php echo urlencode($student['nome_completo']); ?>' class="btn btn-primary"><i class='fa fa-edit'></i> Gerar</a>
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


<?php
require_once __DIR__ . '/partials/footer.php';
?>