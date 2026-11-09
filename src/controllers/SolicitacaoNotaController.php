<?php
// controllers/SolicitacaoNotaController.php
require_once __DIR__ . '/../models/SolicitacaoNota.php';
require_once __DIR__ . '/../helpers/LogHelper.php';

$solicitacaoModel = new SolicitacaoNota();


// Atualizar status (se houver POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $solicitacaoModel->atualizarStatus($id, $status);
    $redirect = $_POST['redirect_url'] ?? '../views/lancar_notas.php';
    
    $_SESSION['status'] = "Solicitação atualizada com sucesso!";
    header("Location: " . $redirect);
    exit;
}

function listarSolicitacoes()
{
    global $solicitacaoModel;
    return $solicitacaoModel->listarTodas();
}

