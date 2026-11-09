<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once '../../config/database.php';

if (!isset($_GET['turma_id'])) {
    echo "Turma não especificada.";
    exit();
}

$turma_id = (int)$_GET['turma_id'];

$db = new Database();
$conn = $db->getConnection();

// Query para buscar horários da turma com nome da disciplina, classe e turma
$sql = "SELECT 
            h.dia_semana,
            h.horario_inicio,
            h.horario_fim,
            d.nome,
            h.sala,
            h.professor,
            t.nome_turma,
            c.nome_classe
        FROM horarios h
        JOIN disciplinas d ON h.disciplina_id = d.id
        JOIN turmas t ON h.turma_id = t.id_turma
        JOIN classe c ON t.classe_id = c.id_classe
        WHERE h.turma_id = ?
        ORDER BY 
            FIELD(h.dia_semana, 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'),
            h.horario_inicio";

$stmt = $conn->prepare($sql);
$stmt->execute([$turma_id]);
$horarios = $stmt->fetchAll();

?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <h3>Horário da Turma</h3>

        <?php if (count($horarios) === 0): ?>
            <p>Nenhum horário encontrado para esta turma.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Dia da Semana</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Disciplina</th>
                        <th>Sala</th>
                        <th>Professor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $horario): ?>
                    <tr>
                        <td><?= htmlspecialchars($horario['dia_semana']) ?></td>
                        <td><?= htmlspecialchars(substr($horario['horario_inicio'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars(substr($horario['horario_fim'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars($horario['nome']) ?></td>
                        <td><?= htmlspecialchars($horario['sala']) ?></td>
                        <td><?= htmlspecialchars($horario['professor']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="meusdados" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
