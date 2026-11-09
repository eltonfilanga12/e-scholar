<?php
session_start();
require_once __DIR__ . '/../controllers/UsuarioController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
        $controller = new UsuarioController();
        $apagado = $controller->excluir($_POST['id']);
        $_SESSION['status'] = $apagado ? 'Usuário excluído com sucesso.' : 'Erro ao excluir usuário.';
    } else {
        $_SESSION['status'] = 'ID inválido para exclusão.';
    }
    header('Location: usuarios');
    exit;
} else {
    // Acesso indevido se não for POST
    header('Location: usuarios.php');
    exit;
}
