<?php
require_once __DIR__ . '/../models/Horario.php';

$model = new Horario();

// Adicionar ou editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id' => $_POST['id'] ?? null,
        'turno_id' => $_POST['turno_id'],
        'tempo' => $_POST['tempo'],
        'hora_inicio' => $_POST['hora_inicio'],
        'hora_fim' => $_POST['hora_fim']
    ];
    $model->salvar($dados);
    $_SESSION['mensagem'] = "Horário salvo com sucesso!";
}

// Excluir
if (isset($_GET['delete'])) {
    $model->deletar($_GET['delete']);
    $_SESSION['mensagem'] = "Horário removido!";
}

// Listagem
$turnos = $model->listarTurnos();
$horarios = $model->listar();

require_once __DIR__ . '/../views/turnos_horarios.php';
