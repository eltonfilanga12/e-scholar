<?php
// controllers/AvisosController.php
require_once __DIR__ . '/../models/Aviso.php';

$avisoModel = new Aviso();
$tipoUsuario = $_SESSION['tipo_usuario'] ?? null;

// Atualizar automaticamente avisos vencidos
$avisoModel->atualizarExpirados();

// DELETE
if (isset($_GET['delete'])) {
    $avisoModel->deletar($_GET['delete']);
    $_SESSION['mensagem'] = "Aviso removido com sucesso!";
}

// POST salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $avisoModel->salvar($_POST);
    $_SESSION['mensagem'] = "Aviso salvo com sucesso!";
    $avisoModel->atualizarExpirados();
}

// LISTAR
$avisos = $avisoModel->listar($tipoUsuario);
