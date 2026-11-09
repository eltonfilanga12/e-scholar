<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '/../controllers/TurnosHorariosController.php';
// $turnos e $horarios são enviados pelo controller
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Horários por Turno</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Painel</a></li>
                        <li class="breadcrumb-item active">Horários por Turno</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalHorario">
                        <i class="fa fa-plus"></i> Novo Tempo
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

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Turno</th>
                                <th>Tempo</th>
                                <th>Hora Início</th>
                                <th>Hora Fim</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horarios as $i => $h): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($h['turno']) ?></td>
                                    <td><?= htmlspecialchars($h['tempo']) ?></td>
                                    <td><?= htmlspecialchars($h['hora_inicio']) ?></td>
                                    <td><?= htmlspecialchars($h['hora_fim']) ?></td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-sm btn-info" onclick='editarHorario(<?= json_encode($h) ?>)'><i class="fa fa-edit"></i></a>
                                        <a href="?delete=<?= $h['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover este horário?')"><i class="fa fa-trash"></i></a>
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

<!-- Modal de Cadastro/Edição de Horário -->
<div class="modal fade" id="modalHorario" tabindex="-1" role="dialog" aria-labelledby="modalHorarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="POST" action="turnos_horarios">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHorarioLabel">Cadastrar/Editar Horário</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="horarioId">

                <div class="form-group">
                    <label>Turno</label>
                    <select name="turno_id" id="horarioTurno" class="form-control" required>
                        <?php foreach ($turnos as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tempo</label>
                    <input type="number" name="tempo" id="horarioTempo" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Hora Início</label>
                    <input type="time" name="hora_inicio" id="horarioInicio" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Hora Fim</label>
                    <input type="time" name="hora_fim" id="horarioFim" class="form-control" required>
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
    function editarHorario(h) {
        document.getElementById("horarioId").value = h.id;
        document.getElementById("horarioTurno").value = h.turno_id;
        document.getElementById("horarioTempo").value = h.tempo;
        document.getElementById("horarioInicio").value = h.hora_inicio;
        document.getElementById("horarioFim").value = h.hora_fim;
        $('#modalHorario').modal('show');
    }
    
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>