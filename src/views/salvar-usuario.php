<?php
session_start();
require_once __DIR__ . '/../controllers/UsuarioController.php';

$controller = new UsuarioController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salvo = $controller->salvar($_POST);
    $_SESSION['status'] = $salvo ? 'Usuário salvo com sucesso.' : 'Erro ao salvar usuário.';
    header('Location: ../views/usuarios.php');
}
