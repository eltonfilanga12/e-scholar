<?php
require_once __DIR__ . '/../models/Professor.php';
$professorModel = new Professor();
$professores = $professorModel->listar();
$funcionarios = $professorModel->listarFuncionariosProfessores();
$disciplinas = $professorModel->listarDisciplinas();

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
                    <h3 class="page-title">Lista de Professores</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Professores</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn btn-primary " data-toggle="modal" data-target="#modalProfessor" onclick="abrirModalAdicionar()">
                        <i class="fa fa-plus"></i> Adicionar Professor
                    </a>
                </div>
                <div>
                    <a href="export/export_professores.php" class="btn btn-success">
                        Exportar Professores CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabela de Professores -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Professores</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Disciplina</th>
                                        <th>Status</th>
                                        <th class="text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($professores as $index => $prof): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= $prof['nome_completo'] ?></td>
                                            <td><?= $prof['email'] ?></td>
                                            <td><?= $prof['contacto'] ?></td>
                                            <td><?= $prof['nome_disciplina'] ?></td>
                                            <td>
                                                <span class="badge bg-inverse-<?= $prof['status'] == 'Ativo' ? 'success' : 'danger' ?>">
                                                    <?= $prof['status'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"><i class="material-icons">more_vert</i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="#" onclick='editarProfessor(<?= json_encode($prof) ?>)'>
                                                            <i class="fa fa-pencil m-r-5"></i> Editar
                                                        </a>
                                                        <a class="dropdown-item" href="../controllers/ProfessorController.php?eliminar=<?= $prof['id_professor'] ?>">
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

        <!-- Modal Cadastro / Edição -->
        <div class="modal fade" id="modalProfessor" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form action="../controllers/ProfessorController.php" method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Cadastro de Professor</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="professorId">

                            <!-- Seleção de Funcionário -->
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <select name="funcionario_id" id="nome" class="form-control" onchange="preencherDadosFuncionario()" required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($funcionarios as $func): ?>
                                        <option value="<?= $func['id'] ?>"
                                            data-nome="<?= $func['nome'] ?>"
                                            data-email="<?= $func['email'] ?>"
                                            data-telefone="<?= $func['telefone'] ?>">
                                            <?= $func['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Email e Telefone -->
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="telefone">Telefone</label>
                                        <input type="text" name="telefone" id="telefone" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Grau Acadêmico e Especialidade -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="grau_academico">Grau Acadêmico</label>
                                        <input type="text" name="grau_academico" id="grau_academico" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="especialidade">Especialidade</label>
                                        <input type="text" name="especialidade" id="especialidade" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Gênero, Data de Nascimento e Disciplina -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="genero">Gênero</label>
                                        <select name="genero" id="genero" class="form-control" required>
                                            <option value="">Selecione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="data_nascimento">Data de Nascimento</label>
                                        <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="disciplina_id">Disciplina</label>
                                        <select name="disciplina_id" id="disciplina_id" class="form-control" required>
                                            <option value="">Selecione</option>
                                            <?php foreach ($disciplinas as $disc): ?>
                                                <option value="<?= $disc['id'] ?>"><?= $disc['nome'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
    function editarProfessor(prof) {
        document.getElementById('professorId').value = prof.id_professor;
        document.getElementById('nome').value = prof.funcionario_id;
        document.getElementById('email').value = prof.email;
        document.getElementById('telefone').value = prof.contacto;
        document.getElementById('grau_academico').value = prof.grau_academico || '';
        document.getElementById('especialidade').value = prof.especialidade || '';
        document.getElementById('genero').value = prof.genero || '';
        document.getElementById('data_nascimento').value = prof.data_nascimento || '';
        document.getElementById('disciplina_id').value = prof.disciplina_id || '';
        document.getElementById('status').value = prof.status || 'Ativo';
        $('#modalProfessor').modal('show');
    }

    function abrirModalAdicionar() {
        document.getElementById('professorId').value = '';
        document.getElementById('nome').value = '';
        document.getElementById('email').value = '';
        document.getElementById('telefone').value = '';
        document.getElementById('grau_academico').value = '';
        document.getElementById('especialidade').value = '';
        document.getElementById('genero').value = '';
        document.getElementById('data_nascimento').value = '';
        document.getElementById('disciplina_id').value = '';
        document.getElementById('status').value = 'Ativo';
    }

    function preencherDadosFuncionario() {
        const select = document.getElementById('nome');
        const selectedOption = select.options[select.selectedIndex];
        document.getElementById('email').value = selectedOption.getAttribute('data-email') || '';
        document.getElementById('telefone').value = selectedOption.getAttribute('data-telefone') || '';
    }
</script>