<?php
require_once __DIR__ . '/../controllers/PagamentoController.php';

$controller = new PagamentoController();

if (!empty($_GET['id'])) {
    $controller->gerarComprovativo($_GET['id']);
} else {
    die("ID do pagamento não informado.");
}
