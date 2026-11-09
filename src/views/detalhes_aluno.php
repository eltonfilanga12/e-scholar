<?php
require_once __DIR__ . '/../controllers/PropinasResumoController.php';

if (!isset($_GET['aluno_id'])) {
    echo "<p>Aluno não encontrado.</p>";
    exit;
}

$aluno_id = intval($_GET['aluno_id']);
$controller = new PropinasResumoController();
$detalhes = $controller->getDetalhesAluno($aluno_id);

if (empty($detalhes)) {
    echo "<p>Não há propinas registradas para este aluno.</p>";
    exit;
}

// Informações do aluno
$nomeAluno = $detalhes['nome_aluno'] ?? '';
$propinasDever = $detalhes['propinas_dever'] ?? 0;
$multas = $detalhes['multas'] ?? 0;

?>

<h5>Aluno: <?= htmlspecialchars($nomeAluno) ?></h5>
<p>Propinas a Dever: <?= $propinasDever ?></p>
<p>Multas a Dever: <?= $multas ?></p>


<div class="form-row mb-3">
    <div class="col">
        <label>Data Início</label>
        <input type="date" id="data_inicio" class="form-control" required>
    </div>
    <div class="col">
        <label>Data Fim</label>
        <input type="date" id="data_fim" class="form-control" required>
    </div>
    <div class="col d-flex align-items-end">
        <button class="btn btn-primary" id="gerar_pdf" data-aluno_id="<?= $aluno_id ?>">Gerar PDF</button>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Mês</th>
            <th>Valor</th>
            <th>Tipo</th>
            <th>Prazo</th>
            <th>Situação</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalDivida = 0;
        $totalMultas = 0;
        foreach ($detalhes['faturas'] as $f):
            if ($f['situacao'] === 'Não Paga'):
                $tipo = (strpos(strtolower($f['abreviatura']), 'multa') !== false) ? 'Multa' : 'Mensalidade';
                $multa = ($tipo === 'Multa') ? $f['custo'] * 0.1 : 0;

                if ($tipo === 'Mensalidade') {
                    $totalDivida += $f['custo'];
                }
                if ($tipo === 'Multa') {
                    $totalDivida += $multa;
                    $totalMultas += $multa;
                }
        ?>
            <tr>
                <td><?= htmlspecialchars($f['mes']) ?></td>
                <td><?= htmlspecialchars($f['custo']) ?></td>
                <td><?= $tipo ?></td>
                <td><?= htmlspecialchars($f['prazo_doc']) ?></td>
                <td><?= htmlspecialchars($f['situacao']) ?></td>
            </tr>
        <?php
            endif;
        endforeach;
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4">Total da Dívida</th>
            <th><?= number_format($totalDivida, 2, ',', '.') ?> (Propinas + Multas)</th>
        </tr>
        <tr>
            <th colspan="4">Total das Multas</th>
            <th><?= number_format($totalMultas, 2, ',', '.') ?></th>
        </tr>
    </tfoot>
</table>



<script>
$('#gerar_pdf').on('click', function() {
    var aluno_id = $(this).data('aluno_id');
    var inicio = $('#data_inicio').val();
    var fim = $('#data_fim').val();
    if (!inicio || !fim) {
        alert('Selecione as datas de início e fim.');
        return;
    }
    window.open('relatorio_propinas_pdf.php?aluno_id=' + aluno_id + '&inicio=' + inicio + '&fim=' + fim, '_blank');
});
</script>