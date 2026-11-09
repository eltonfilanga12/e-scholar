<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../controllers/AvisosController.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Avisos</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Avisos</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAviso">
                        <i class="fa fa-plus"></i> Novo Aviso
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['mensagem'];
                unset($_SESSION['mensagem']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Lista de Avisos</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Conteúdo</th>
                                    <th>Usuário</th>
                                    <th>Validade</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avisos as $index => $aviso): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($aviso['conteudo']) ?></td>
                                        <td><?= $aviso['tipo_usuario'] ?></td>
                                        <td><?= $aviso['data_validade'] ? date('d/m/Y', strtotime($aviso['data_validade'])) : '-' ?></td>
                                        <td>
                                            <span class="badge bg-<?= $aviso['status'] === 'Ativo' ? 'success' : 'danger' ?>">
                                                <?= $aviso['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-sm btn-info" onclick='editarAviso(<?= json_encode($aviso) ?>)'><i class="fa fa-edit"></i></a>
                                            <a href="?delete=<?= $aviso['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover este aviso?')"><i class="fa fa-trash"></i></a>
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

<!-- Modal Aviso -->
<div class="modal fade" id="modalAviso" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="POST" action="avisos.php">
            <div class="modal-header">
                <h5 class="modal-title">Cadastrar/Editar Aviso</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="avisoId">
                <div class="form-group">
                    <label>Conteúdo</label>
                    <textarea name="conteudo" class="form-control" id="avisoConteudo" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Data de Validade</label>
                    <input type="date" name="data_validade" class="form-control" id="avisoValidade">
                </div>
                <div class="form-group">
                    <label>Tipo de Usuário</label>
                    <select name="tipo_usuario" class="form-control" id="avisoTipoUsuario" required>
                        <option value="Todos">Todos</option>
                        <option value="Aluno">Aluno</option>
                        <option value="Professor">Professor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editarAviso(aviso) {
        document.getElementById('avisoId').value = aviso.id;
        document.getElementById('avisoConteudo').value = aviso.conteudo;

        if (aviso.data_validade) {
            // Pega só a parte da data (YYYY-MM-DD) do campo datetime
            document.getElementById('avisoValidade').value = aviso.data_validade.split(' ')[0];
        } else {
            document.getElementById('avisoValidade').value = '';
        }

        document.getElementById('avisoTipoUsuario').value = aviso.tipo_usuario;
        $('#modalAviso').modal('show');
    }
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>