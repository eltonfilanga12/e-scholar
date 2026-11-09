<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../models/Professor.php';

$professorModel = new Professor();
$professores = $professorModel->listar();
$classes = $professorModel->listarClasses();
$turmas = $professorModel->listarTurmas();
$atribuicoes = $professorModel->listarAtribuicoes();
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Atribuir Turmas e Classes aos Professores</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active">Atribuições</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="#" class="btn btn-primary add-btn" data-toggle="modal" data-target="#modalAtribuicao" onclick="abrirModalAdicionar()">
                        <i class="fa fa-plus"></i> Nova Atribuição
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabela de atribuições -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Professores e suas Atribuições</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="basic-datatables" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Professor</th>
                                <th>Turma</th>
                                <th>Classe</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($atribuicoes)): ?>
                                <?php foreach ($atribuicoes as $i => $atr): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($atr['nome_professor']) ?></td>
                                        <td><?= htmlspecialchars($atr['nome_turma'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($atr['nome_classe'] ?: '-') ?></td>
                                        <td class="text-center">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown">
                                                    <i class="material-icons">more_vert</i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <!-- Editar: chama JS com o objeto atribuição -->
                                                    <a class="dropdown-item" href="#"
                                                        onclick='editarAtribuicao(<?= json_encode($atr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                                                        <i class="fa fa-pencil m-r-5"></i> Editar
                                                    </a>

                                                    <!-- Remover: abre modal de confirmação -->
                                                    <a class="dropdown-item" href="#"
                                                        data-toggle="modal" data-target="#modalEliminar"
                                                        data-id="<?= htmlspecialchars($atr['id_professor']) ?>"
                                                        data-professor="<?= htmlspecialchars($atr['nome_professor']) ?>">
                                                        <i class="fa fa-trash-o m-r-5"></i> Remover
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhuma atribuição cadastrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Adicionar / Editar -->
<div class="modal fade" id="modalAtribuicao" tabindex="-1" aria-labelledby="modalAtribuicaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formAtribuicao" action="../controllers/ProfessorController.php" method="POST">
                <input type="hidden" name="acao" id="acao" value="salvarAtribuicao">
                <input type="hidden" name="id_professor" id="id_professor" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">Nova Atribuição</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Professor</label>
                        <select name="professor_id" id="professor_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($professores as $p): ?>
                                <option value="<?= $p['id_professor'] ?? $p['id'] ?? $p['id_professor'] ?>"><?= htmlspecialchars($p['nome_completo'] ?? $p['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Turma</label>
                        <select name="turma_id" id="turma_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($turmas as $t): ?>
                                <option value="<?= $t['id_turma'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Classe</label>
                        <select name="classe_id" id="classe_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id_classe'] ?>"><?= htmlspecialchars($c['nome_classe']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="salvarBtn">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../controllers/ProfessorController.php" method="POST">
                <input type="hidden" name="acao" value="eliminarAtribuicoes">
                <input type="hidden" name="professor_id" id="professor_id_excluir">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Atribuições</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja eliminar todas as atribuições deste professor?</p>
                    <p><strong id="professor_nome_excluir"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Abre modal em modo "adicionar"
    function abrirModalAdicionar() {
        document.getElementById('tituloModal').innerText = "Nova Atribuição";
        document.getElementById('acao').value = "salvarAtribuicao";
        document.getElementById('id_professor').value = "";
        document.getElementById('professor_id').value = "";
        document.getElementById('turma_id').value = "";
        document.getElementById('classe_id').value = "";
        $('#modalAtribuicao').modal('show');
    }

    // Preenche modal para edição
    function editarAtribuicao(atr) {
        document.getElementById('tituloModal').innerText = "Editar Atribuição de " + (atr.nome_professor || '');
        document.getElementById('acao').value = "editarAtribuicao";
        // id_professor é a PK do professor (usada caso queira guardar a PK da linha)
        document.getElementById('id_professor').value = atr.id_professor || "";
        // professor_id (select) também aponta para o professor
        document.getElementById('professor_id').value = atr.id_professor || "";
        document.getElementById('turma_id').value = atr.id_turma || "";
        document.getElementById('classe_id').value = atr.id_classe || "";
        $('#modalAtribuicao').modal('show');
    }

    // Lógica para preencher modalEliminar
    $('#modalEliminar').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const nome = button.data('professor');
        $('#professor_id_excluir').val(id);
        $('#professor_nome_excluir').text(nome);
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>