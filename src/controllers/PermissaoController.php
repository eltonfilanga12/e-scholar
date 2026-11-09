<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/../models/Permissao.php';

$permissaoModel = new Permissao();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_usuario'])) {
    $tipo = $_POST['tipo_usuario'];

    foreach ($_POST['paginas'] as $pagina => $permitido) {
        // Atualiza a permissão e registra o log
        $permissaoModel->atualizarPermissao($tipo, $pagina, $permitido === '1' ? 1 : 0);
    }

    $_SESSION['mensagem'] = "Permissões de $tipo atualizadas com sucesso!";
    // Agora, usamos o método getPdo() para acessar a conexão PDO
    LogHelper::registrar($permissaoModel->getPdo(), $_SESSION['nome'], 'Atualização de Permissões', "Permissões de '$tipo' foram atualizadas.");

    header("Location: ../views/permissao.php");
    exit;
}

function listarTipos() {
    global $permissaoModel;
    return $permissaoModel->listarTipos();
}

function listarPaginas() {
    global $permissaoModel;
    return $permissaoModel->listarPaginasDisponiveis();
}

function listarPermissoes($tipo) {
    global $permissaoModel;
    $dados = $permissaoModel->listarPorTipo($tipo);
    $resultado = [];
    foreach ($dados as $p) {
        $resultado[$p['pagina']] = $p['permitido'];
    }
    return $resultado;
}
