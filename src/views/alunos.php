<?php
// Incluindo o controlador de alunos
require_once __DIR__ . '/../controllers/AlunoController.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Alunos</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Alunos</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAluno" onclick="limparFormulario()">
                        <i class="fa fa-plus"></i> Adicionar Aluno
                    </button>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Filter Form -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" class="form-control rounded-pill shadow-sm" placeholder="Pesquisar por nome" id="filtroNome">
            </div>
            <div class="col-md-3">
                <select class="form-control rounded-pill shadow-sm" id="filtroTurma">
                    <option value="">Todas as Turmas</option>
                    <option>Turma A</option>
                    <option>Turma B</option>
                    <option>Turma C</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control rounded-pill shadow-sm" id="filtroClasse">
                    <option value="">Todas as Classes</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-success rounded-pill shadow-sm" onclick="filtrarAlunos()">Filtrar</button>
                <button class="btn btn-secondary rounded-pill shadow-sm" onclick="limparFiltros()">Limpar</button>
            </div>
        </div>
        <!-- /Filter Form -->

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Alunos
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome Completo</th>
                                        <th>Data de Nascimento</th>
                                        <th>Classe</th>
                                        <th>Turma</th>
                                        <th>Data da Matrícula</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $index => $student) : ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?php echo htmlspecialchars($student['nome_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($student['data_nascimento']); ?></td>
                                            <td><?php echo htmlspecialchars($student['classe']); ?></td>
                                            <td><?php echo htmlspecialchars($student['turma_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($student['data_inscricao']); ?></td>
                                            <td class="text-center">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href='ediAluno.php?id=<?php echo htmlspecialchars($student['id']); ?>'><i class="fa fa-pencil m-r-5"></i> Editar</a>
                                                        <a class="dropdown-item" href='eliminar.php?id=<?php echo htmlspecialchars($student['id']); ?>' onclick='return confirm("Tem certeza que deseja eliminar este aluno?");'><i class="fa fa-trash-o m-r-5"></i> Excluir</a>
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
        <!-- /Table -->

    </div>
    <!-- /Page Content -->

    <!-- /Page Wrapper -->
</div>
</div>


<?php require_once __DIR__ . '/partials/footer.php'; ?>