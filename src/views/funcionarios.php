<?php

require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/../models/Funcionario.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$db = new Database();
$pdo = $db->getConnection();

$funcionarioModel = new Funcionario($pdo);
$funcionarios = $funcionarioModel->listar();
$cargos = $funcionarioModel->listarCargos();
$departamentos = $funcionarioModel->listarDepartamentos();
$turnos = $funcionarioModel->listarTurnos();

?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Lista de Funcionários</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Funcionários</li>
                    </ul>
                </div>

                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modalFuncionario" onclick="abrirModalAdicionar()">
                        <i class="fa fa-plus"></i> Adicionar Funcionário
                    </a>
                </div>
                <div>
                    <a href="export/export_funcionarios.php" class="btn btn-success">
                        Exportar Funcionários CSV
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['mensagem'];
                unset($_SESSION['mensagem']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Funcionários</h4>
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
                                        <th>Cargo</th>
                                        <th>Data de Admissão</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($funcionarios as $index => $func): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($func['nome']) ?></td>
                                            <td><?= htmlspecialchars($func['email']) ?></td>
                                            <td><?= htmlspecialchars($func['telefone']) ?></td>
                                            <td><?= htmlspecialchars($func['nome_cargo']) ?></td>
                                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($func['data_admissao']))) ?></td>
                                            <td>
                                                <span class="badge bg-inverse-<?= $func['status'] === 'Ativo' ? 'success' : 'danger' ?>">
                                                    <?= htmlspecialchars($func['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown">
                                                        <i class="material-icons">more_vert</i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item btn-editar-funcionario"
                                                            href="#"
                                                            data-id="<?= $func['id'] ?>"
                                                            data-nome_completo="<?= htmlspecialchars($func['nome_completo'] ?? $func['nome']) ?>"
                                                            data-email="<?= htmlspecialchars($func['email']) ?>"
                                                            data-telefone="<?= htmlspecialchars($func['telefone']) ?>"
                                                            data-cargo_id="<?= $func['cargo_id'] ?>"
                                                            data-sexo="<?= $func['sexo'] ?>"
                                                            data-departamento_id="<?= $func['departamento_id'] ?>"
                                                            data-turno_id="<?= $func['turno_id'] ?>"
                                                            data-endereco="<?= htmlspecialchars($func['endereco']) ?>"
                                                            data-data_nascimento="<?= $func['data_nascimento'] ?>"
                                                            data-idade="<?= $func['idade'] ?>"
                                                            data-nacionalidade="<?= htmlspecialchars($func['nacionalidade']) ?>"
                                                            data-naturalidade="<?= htmlspecialchars($func['naturalidade']) ?>"
                                                            data-documento_tipo="<?= htmlspecialchars($func['documento_tipo']) ?>"
                                                            data-numero_documento="<?= htmlspecialchars($func['numero_documento']) ?>"
                                                            data-data_admissao="<?= $func['data_admissao'] ?>"
                                                            data-salario="<?= $func['salario'] ?>"
                                                            data-tipo_salario="<?= $func['tipo_salario'] ?>"
                                                            data-valor_hora="<?= $func['valor_hora'] ?>"
                                                            data-status="<?= $func['status'] ?>"
                                                            data-toggle="modal"
                                                            data-target="#modalFuncionario">
                                                            <i class="fa fa-pencil m-r-5"></i> Editar
                                                        </a>

                                                        <a class="dropdown-item" href="funcionarios.php?delete=<?= $func['id'] ?>" onclick="return confirm('Tem certeza que deseja remover este funcionário?');">
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
        <!-- /Table -->

    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->

<!-- Modal Funcionário -->
<div class="modal fade" id="modalFuncionario" tabindex="-1" role="dialog" aria-labelledby="modalFuncionarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="../controllers/FuncionarioController.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFuncionarioLabel">Cadastro de Funcionário</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Campos ocultos para edição ou FK do usuário -->
                <input type="hidden" name="id" id="funcionarioId">
                <input type="hidden" name="acao" value="criar" id="funcionarioAcao">
                <input type="hidden" name="usuario_id" id="funcionarioUsuarioId">

                <!-- Linha 1: Nome e Sexo -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nome">Nome Completo</label>
                        <input type="text" name="nome_completo" class="form-control" id="funcionarioNome" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="sexo">Sexo</label>
                        <select name="sexo" class="form-control" id="funcionarioSexo" required>
                            <option value="">Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                        </select>
                    </div>
                </div>

                <!-- Linha 2: Data Nascimento, Idade, Nacionalidade, Naturalidade -->
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="form-control" id="funcionarioDataNasc" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="idade">Idade</label>
                        <input type="number" name="idade" class="form-control" id="funcionarioIdade" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="nacionalidade">Nacionalidade</label>
                        <input type="text" name="nacionalidade" class="form-control" id="funcionarioNacionalidade" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="naturalidade">Naturalidade</label>
                        <input type="text" name="naturalidade" class="form-control" id="funcionarioNaturalidade" required>
                    </div>
                </div>

                <!-- Linha 3: Documento e Contato -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="documento_tipo">Tipo Documento</label>
                        <select name="documento_tipo" class="form-control" id="funcionarioDocTipo" required>
                            <option value="">Selecione</option>
                            <option value="BI">BI</option>
                            <option value="Passaporte">Passaporte</option>
                            <option value="Cédula Pessoal">Cédula Pessoal</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numero_documento">Número Documento</label>
                        <input type="text" name="numero_documento" class="form-control" id="funcionarioNumDoc" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="telefone">Telefone</label>
                        <input type="text" name="telefone" class="form-control" id="funcionarioTelefone" required>
                    </div>
                </div>

                <!-- Linha 4: Email e Endereço -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" id="funcionarioEmail" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="endereco">Endereço</label>
                        <input type="text" name="endereco" class="form-control" id="funcionarioEndereco" required>
                    </div>
                </div>

                <!-- Linha 5: Cargo, Departamento, Turno -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="cargo_id">Cargo</label>
                        <select name="cargo_id" class="form-control" id="funcionarioCargo" required>
                            <option value="">Selecione</option>
                            <?php foreach ($cargos as $cargo): ?>
                                <option value="<?= htmlspecialchars($cargo['id']) ?>"><?= htmlspecialchars($cargo['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="departamento_id">Departamento</label>
                        <select name="departamento_id" class="form-control" id="funcionarioDepartamento">
                            <option value="">Selecione</option>
                            <?php foreach ($departamentos as $dep): ?>
                                <option value="<?= htmlspecialchars($dep['id']) ?>"><?= htmlspecialchars($dep['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="turno">Turno</label>
                        <select name="turno_id" class="form-control" id="funcionarioTurno">
                            <option value="">Selecione</option>
                            <?php foreach ($turnos as $turno): ?>
                                <option value="<?= htmlspecialchars($turno['id']) ?>"><?= htmlspecialchars($turno['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Linha 6: Data Admissão, Salário e Status -->
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="data_admissao">Data de Admissão</label>
                        <input type="date" name="data_admissao" class="form-control" id="funcionarioDataAdmissao" required>
                    </div>
                    
                    
                        <div class="form-group col-md-4">
                            <label for="tipo_salario">Tipo de Salário</label>
                            <select name="tipo_salario" class="form-control" id="funcionarioTipoSalario" required>
                                <option value="">Selecione</option>
                                <option value="Mensal">Mensal</option>
                                <option value="Hora">Por Hora</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4" id="campoSalarioMensal" style="display:none;">
                            <label for="salario">Salário Mensal</label>
                            <input type="number" name="salario" class="form-control" id="funcionarioSalario" step="0.01">
                        </div>

                        <div class="form-group col-md-4" id="campoValorHora" style="display:none;">
                            <label for="valor_hora">Valor por Hora</label>
                            <input type="number" name="valor_hora" class="form-control" id="funcionarioValorHora" step="0.01">
                        </div>





                    <div class="form-group col-md-4">
                        <label for="status">Status</label>
                        <select name="status" class="form-control" id="funcionarioStatus" required>
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>


<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>

        function atualizarCamposSalario() {
    let tipo = $("#funcionarioTipoSalario").val();

    if (tipo === "") {
        $("#campoSalarioMensal").hide();
        $("#campoValorHora").hide();
        $("#funcionarioSalario").val("");
        $("#funcionarioValorHora").val("");
    } 
    else if (tipo === "Mensal") {
        $("#campoSalarioMensal").show();
        $("#campoValorHora").hide();
        $("#funcionarioValorHora").val("");
    } 
    else if (tipo === "Hora") {
        $("#campoSalarioMensal").hide();
        $("#campoValorHora").show();
        $("#funcionarioSalario").val("");
    }

    // Quando selecionar
    $("#funcionarioTipoSalario").on("change", atualizarCamposSalario);

    // Quando abrir modal de edição, aplica os estados corretos
    atualizarCamposSalario();
}


    $(document).ready(function() {

        // Função para abrir o modal para adicionar funcionário
        function abrirModalAdicionar() {
            $('#modalFuncionarioLabel').text('Novo Funcionário');
            $('#funcionarioAcao').val('criar');
            $('#funcionarioId').val('');
            $('#funcionarioUsuarioId').val('');
            $('#funcionarioNome').val('');
            $('#funcionarioSexo').val('');
            $('#funcionarioDataNasc').val('');
            $('#funcionarioIdade').val('');
            $('#funcionarioNacionalidade').val('');
            $('#funcionarioNaturalidade').val('');
            $('#funcionarioDocTipo').val('');
            $('#funcionarioNumDoc').val('');
            $('#funcionarioTelefone').val('');
            $('#funcionarioEmail').val('');
            $('#funcionarioEndereco').val('');
            $('#funcionarioCargo').val('');
            $('#funcionarioDepartamento').val('');
            $('#funcionarioTurno').val('');
            $('#funcionarioDataAdmissao').val('');
            $('#funcionarioTipoSalario').val('');
            $('#funcionarioSalario').val('');
            $('#funcionarioValorHora').val('');
            $('#funcionarioStatus').val('Ativo');
            atualizarCamposSalario();
        }

        // Função para abrir o modal para editar funcionário
        $(document).on('click', '.btn-editar-funcionario', function() {
    $('#modalFuncionarioLabel').text('Editar Funcionário');
    $('#funcionarioAcao').val('editar');
    $('#funcionarioId').val($(this).data('id'));
    $('#funcionarioUsuarioId').val($(this).data('usuario_id'));
    $('#funcionarioNome').val($(this).data('nome_completo'));
    $('#funcionarioSexo').val($(this).data('sexo'));
    $('#funcionarioDataNasc').val($(this).data('data_nascimento'));
    calcularIdade($(this).data('data_nascimento'));
    $('#funcionarioNacionalidade').val($(this).data('nacionalidade'));
    $('#funcionarioNaturalidade').val($(this).data('naturalidade'));
    $('#funcionarioDocTipo').val($(this).data('documento_tipo'));
    $('#funcionarioNumDoc').val($(this).data('numero_documento'));
    $('#funcionarioTelefone').val($(this).data('telefone'));
    $('#funcionarioEmail').val($(this).data('email'));
    $('#funcionarioEndereco').val($(this).data('endereco'));
    $('#funcionarioCargo').val($(this).data('cargo_id'));
    $('#funcionarioDepartamento').val($(this).data('departamento_id'));
    $('#funcionarioTurno').val($(this).data('turno_id'));
    $('#funcionarioDataAdmissao').val($(this).data('data_admissao'));
    $('#funcionarioTipoSalario').val($(this).data('tipo_salario'));
    $('#funcionarioSalario').val($(this).data('salario'));
    $('#funcionarioValorHora').val($(this).data('valor_hora'));
    $('#funcionarioStatus').val($(this).data('status'));
    atualizarCamposSalario();
});


        // Calcula a idade automaticamente ao alterar a data de nascimento
        $('#funcionarioDataNasc').on('change', function() {
            var dataNascimento = $(this).val();
            calcularIdade(dataNascimento);
        });

        function calcularIdade(dataNascimento) {
            if (!dataNascimento) {
                $('#funcionarioIdade').val('');
                return;
            }
            var hoje = new Date();
            var nascimento = new Date(dataNascimento);
            var idade = hoje.getFullYear() - nascimento.getFullYear();
            var m = hoje.getMonth() - nascimento.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) {
                idade--;
            }
            $('#funcionarioIdade').val(idade);
        }

        // Expor função global para abrir modal de adição
        window.abrirModalAdicionar = abrirModalAdicionar;

    });



</script>

