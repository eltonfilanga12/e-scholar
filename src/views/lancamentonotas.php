<?php

require_once __DIR__ . '/../controllers/NotaController.php';
require_once __DIR__ . '/../controllers/DisciplinaController.php';

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';


// Inicializar os controladores
$notaController = new NotaController();
$disciplinaController = new DisciplinaController();

$notas = [];
$disciplinas = $disciplinaController->buscarDisciplinas();

$editData = null;

// Verifica se a variável 'disciplina_id' está definida
if (isset($_GET['disciplina_id'])) {
    $disciplina_id = $_GET['disciplina_id'];

    try {
        $notas = $notaController->buscarNotasPorDisciplina($disciplina_id);
    } catch (Exception $e) {
        error_log("Erro ao buscar notas: " . $e->getMessage());
        $notas = [];
    }
}

// Processar atualizações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receba os dados da requisição POST
    $studentId = $_POST['student_id'] ?? null;
    $disciplineId = $_POST['discipline_id'] ?? null;
    $teste1 = $_POST['teste1'] ?? null;
    $teste2 = $_POST['teste2'] ?? null;
    $teste3 = $_POST['teste3'] ?? null;

    if ($studentId && $disciplineId && $teste1 !== null && $teste2 !== null && $teste3 !== null) {
        // Chame o método atualizarNota do controlador
        $atualizado = $notaController->atualizarNotas($studentId, $disciplineId, $teste1, $teste2, $teste3);

        if ($atualizado) {
            $_SESSION['status'] = "Sucesso ao atualizar as notas.";
            header('location: lancamentonotas.php');
        } else {
            $_SESSION['status'] = "Falha ao atualizar as notas.";
            header('location: lancamentonotas.php');
        }
    } else {
        $_SESSION['status'] = "Dados insuficientes para atualizar as notas.";
        header('location: lancamentonotas.php');
    }

    exit;
}

?>

<div class="breadcome-area">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcome-list single-page-breadcome">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12"></div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <ul class="breadcome-menu">
                                <li><a href="#">Home</a> <span class="bread-slash">/</span></li>
                                <li><span class="bread-blod">Lançamento de Notas</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>e-Scholar, </strong><?php echo $_SESSION['status']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria_hidden="true">&times;</span>
        </button>
    </div>
<?php
    unset($_SESSION['status']);
}

?>

<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Notas do Aluno</h1>
                        </div>
                    </div>
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <div id="toolbar">
                                <form method="get" action="lancamentonotas.php">
                                    <select class="form-control dt-tb" id="disciplina-select" name="disciplina_id">
                                        <option value="">Selecione uma Disciplina</option>
                                        <?php foreach ($disciplinas as $disciplina): ?>
                                            <option value="<?php echo htmlspecialchars($disciplina['id']); ?>" <?php echo (isset($_GET['disciplina_id']) && $_GET['disciplina_id'] == $disciplina['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($disciplina['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                            <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true"
                                data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th data-field="codigo" data-editable="false">Código do Aluno</th>
                                        <th data-field="nome" data-editable="false">Nome do Aluno</th>
                                        <th data-field="turma" data-editable="false">Turma</th>
                                        <th data-field="classe" data-editable="false">Classe</th>
                                        <th data-field="teste1" data-editable="false">Teste 1</th>
                                        <th data-field="teste2" data-editable="false">Teste 2</th>
                                        <th data-field="teste3" data-editable="false">Teste 3</th>
                                        <th data-field="media" data-editable="false">Média</th>
                                        <th data-field="actions" data-editable="false">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($notas)): ?>
                                        <?php foreach ($notas as $nota): ?>
                                            <tr>
                                                <td></td>
                                                <td class="id"><?php echo htmlspecialchars($nota['id']); ?></td>
                                                <td><?php echo htmlspecialchars($nota['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($nota['turma_id']); ?></td>
                                                <td><?php echo htmlspecialchars($nota['classe']); ?></td>
                                                <td><?php echo htmlspecialchars($nota['teste1']); ?></td>
                                                <td><?php echo htmlspecialchars($nota['teste2']); ?></td>
                                                <td><?php echo htmlspecialchars($nota['teste3']); ?></td>
                                                <td><?php
                                                    $media = ($nota['teste1'] + $nota['teste2'] + $nota['teste3']) / 3;
                                                    $mediaArredondada = round($media); // Arredonda para o inteiro mais próximo
                                                    echo $mediaArredondada; // Exibe como número inteiro
                                                    ?></td>

                                                <td>
                                                    <a href="#" class="btn btn-primary btn-edit" data-id="<?php echo $nota['id']; ?>" data-discipline-id="<?php echo $disciplina_id; ?>" data-teste1="<?php echo htmlspecialchars($nota['teste1']); ?>" data-teste2="<?php echo htmlspecialchars($nota['teste2']); ?>" data-teste3="<?php echo htmlspecialchars($nota['teste3']); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="12">Nenhuma nota encontrada para a disciplina selecionada.</td>
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

<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Notas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="modal-student-id">
                    <input type="hidden" id="modal-discipline-id">
                    <div class="form-group">
                        <label for="input-teste1">Teste 1</label>
                        <input type="number" class="form-control" id="input-teste1" required>
                    </div>
                    <div class="form-group">
                        <label for="input-teste2">Teste 2</label>
                        <input type="number" class="form-control" id="input-teste2" required>
                    </div>
                    <div class="form-group">
                        <label for="input-teste3">Teste 3</label>
                        <input type="number" class="form-control" id="input-teste3" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('.btn-edit').click(function() {
            var studentId = $(this).data('id');
            var disciplineId = $(this).data('discipline-id');
            var teste1 = $(this).data('teste1');
            var teste2 = $(this).data('teste2');
            var teste3 = $(this).data('teste3');

            $('#modal-student-id').val(studentId);
            $('#modal-discipline-id').val(disciplineId);
            $('#input-teste1').val(teste1);
            $('#input-teste2').val(teste2);
            $('#input-teste3').val(teste3);

            $('#editModal').modal('show');
        });

        $('#editForm').submit(function(e) {
            e.preventDefault();

            var studentId = $('#modal-student-id').val();
            var disciplineId = $('#modal-discipline-id').val();
            var teste1 = $('#input-teste1').val();
            var teste2 = $('#input-teste2').val();
            var teste3 = $('#input-teste3').val();

            $.ajax({
                url: 'lancamentonotas.php',
                type: 'POST',
                data: {
                    student_id: studentId,
                    discipline_id: disciplineId,
                    teste1: teste1,
                    teste2: teste2,
                    teste3: teste3
                },
                success: function(response) {
                    alert("Salvo com sucesso!");
                    $('#editModal').modal('hide');
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erro:', textStatus, errorThrown);
                }
            });
        });
    });

    document.getElementById('disciplina-select').addEventListener('change', function() {
        this.form.submit();
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>