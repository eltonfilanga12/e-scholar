<?php
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '/../models/Funcionario.php';
require_once __DIR__ . '/../models/Professor.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new UsuarioController();
$alunoModel = new Student($conn);
$funcionarioModel = new Funcionario($conn);
$professorModel = new Professor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salvo = $controller->salvar($_POST);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $salvo]);
        exit;
    }

    $_SESSION['status'] = $salvo ? 'Usuário salvo com sucesso.' : 'Erro ao salvar usuário.';
}

$usuarios = $controller->listarUser();
$alunos = $alunoModel->getAllStudents();
$funcionarios = $funcionarioModel->listar();
$professores = $professorModel->listar();
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Usuários do Sistema</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Usuários</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddUsuario">
                        <i class="fa fa-plus"></i> Adicionar Usuário
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['status']) && $_SESSION['status'] != '') : ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>e-Scholar, </strong><?= $_SESSION['status'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php unset($_SESSION['status']);
        endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Usuários do Sistema</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th class="text-center">Status</th>
                                        <th>Data de Criação</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($usuarios && count($usuarios) > 0) : ?>
                                        <?php $contador = 1; ?>
                                        <?php foreach ($usuarios as $row) : ?>
                                            <?php
                                            $nomeReferente = '';
                                            if ($row['tipo_usuario'] === 'Aluno') {
                                                foreach ($alunos as $a) {
                                                    if ($a['id_usuario'] == $row['id']) {
                                                        $nomeReferente = $a['nome_completo'];
                                                        break;
                                                    }
                                                }
                                            } elseif ($row['tipo_usuario'] === 'Funcionario') {
                                                foreach ($funcionarios as $f) {
                                                    if ($f['id_usuario'] == $row['id']) {
                                                        $nomeReferente = $f['nome'];
                                                        break;
                                                    }
                                                }
                                            } elseif ($row['tipo_usuario'] === 'Professor') {
                                                foreach ($professores as $p) {
                                                    if ($p['id_usuario'] == $row['id']) {
                                                        $nomeReferente = $p['nome_completo'];
                                                        break;
                                                    }
                                                }
                                            }

                                            ?>

                                            <tr>
                                                <td><?= $contador++ ?></td>
                                                <td><?= htmlspecialchars($nomeReferente ?: $row['nome']) ?></td>
                                                <td><?= $row['tipo_usuario'] ?></td>
                                                <td class='text-center'><span class='badge badge-<?= ($row['user_status'] == 'Ativo' ? 'success' : 'danger') ?>'><?= $row['user_status'] ?></span></td>
                                                <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                                                <td class='text-center'>
                                                    <div class='dropdown dropdown-action'>
                                                        <a href='#' class='action-icon dropdown-toggle' data-toggle='dropdown'>
                                                            <i class='material-icons'>more_vert</i>
                                                        </a>
                                                        <div class='dropdown-menu dropdown-menu-right'>
                                                            <a class='dropdown-item btn-editar' href='#'
                                                                data-toggle='modal' data-target='#modalAddUsuario'
                                                                data-id='<?= $row['id'] ?>'
                                                                data-nome='<?= $nomeReferente ?>'
                                                                data-tipo='<?= $row['tipo_usuario'] ?>'
                                                                data-status='<?= $row['user_status'] ?>'>
                                                                <i class='fa fa-pencil m-r-5'></i> Editar
                                                            </a>
                                                            <a href="#"
                                                                class="btn-confirmar-exclusao btn btn-sm"
                                                                data-id="<?= $row['id'] ?>">
                                                                <i class="fa fa-trash-o"></i> Excluir
                                                            </a>

                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                    <?php else : ?>
                                        <tr>
                                            <td colspan='6' class='text-center'>Nenhum usuário encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddUsuario" tabindex="-1" role="dialog" aria-labelledby="modalAddUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="usuarios.php">
            <input type="hidden" name="id" id="usuario_id">
            <input type="hidden" name="referencia_id" id="referencia_id">
            <input type="hidden" name="tipo_origem" id="tipo_origem">
            <input type="hidden" name="nome" id="nome_real">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddUsuarioLabel">Adicionar Novo Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome_select">Nome do Usuário</label>
                        <select class="form-control selectpicker" id="nome_select" data-live-search="true" required>
                            <option value="">Selecione um usuário...</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?= $aluno['id'] ?>" data-nome="<?= $aluno['nome_completo'] ?>" data-tipo="Aluno">
                                    <?= htmlspecialchars($aluno['nome_completo']) ?> (Aluno)
                                </option>
                            <?php endforeach; ?>
                            <?php foreach ($funcionarios as $funcionario): ?>
                                <option value="<?= $funcionario['id'] ?>" data-nome="<?= $funcionario['nome'] ?>" data-tipo="Professor">
                                    <?= $funcionario['nome'] ?> (<?= $funcionario['nome_cargo'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha">
                    </div>
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo de Usuário</label>
                        <select class="form-control" name="tipo_usuario" id="tipo_usuario" required>
                            <option value="">Selecione...</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Aluno">Aluno</option>
                            <option value="Professor">Professor</option>
                            <option value="Secretário">Secretário</option>
                            <option value="Pedagógico">Pedagógico</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_status">Status</label>
                        <select class="form-control" name="user_status" id="user_status" required>
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formExcluirUsuario" method="POST" action="excluir-usuario.php">
            <input type="hidden" name="id" id="idParaExcluir" value="">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalConfirmarExclusaoLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este usuário?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        const inputId = document.getElementById('idParaExcluir');
        document.querySelectorAll('.btn-confirmar-exclusao').forEach(botao => {
            botao.addEventListener('click', function(e) {
                e.preventDefault(); // evita link padrão
                const id = this.getAttribute('data-id');
                document.getElementById('idParaExcluir').value = id;
                $('#modalConfirmarExclusao').modal('show');
            });
        });


        document.querySelectorAll('.btn-editar').forEach(botao => {
            botao.addEventListener('click', function() {
                const id = this.dataset.id;
                const nome = this.dataset.nome;
                const tipo = this.dataset.tipo;
                const status = this.dataset.status;

                document.getElementById('usuario_id').value = id;
                document.getElementById('nome_real').value = nome;
                document.getElementById('tipo_usuario').value = tipo;
                document.getElementById('user_status').value = status;

                // Limpa seleção anterior e seleciona o option correspondente
                const select = document.getElementById('nome_select');
                const options = select.options;

                for (let i = 0; i < options.length; i++) {
                    if (
                        options[i].getAttribute('data-nome') === nome &&
                        options[i].getAttribute('data-tipo') === tipo
                    ) {
                        select.selectedIndex = i;
                        document.getElementById('referencia_id').value = options[i].value;
                        document.getElementById('tipo_origem').value = tipo;
                        break;
                    }
                }
            });
        });


        const selectNome = document.getElementById('nome_select');
        selectNome.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            document.getElementById('nome_real').value = selected.getAttribute('data-nome');
            document.getElementById('tipo_origem').value = selected.getAttribute('data-tipo');
            document.getElementById('referencia_id').value = selected.value;
        });
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>