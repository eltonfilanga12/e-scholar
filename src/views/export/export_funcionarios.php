<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Funcionario.php';

$db = new Database();
$pdo = $db->getConnection();

$funcionarioModel = new Funcionario($pdo);
$funcionarios = $funcionarioModel->listar();
$cargosArray = $funcionarioModel->listarCargos();

// Transformar em array associativo [id => nome]
$cargos = [];
foreach ($cargosArray as $c) {
    $cargos[$c['id']] = $c['nome'];
}

// Cabeçalho CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=funcionarios.csv');

// Abrir saída padrão
$output = fopen('php://output', 'w');

// Escrever cabeçalho
$header = ['Nome', 'Email', 'Telefone', 'Cargo', 'Data de Admissão', 'Status'];
fputcsv($output, $header);

// Escrever dados
foreach ($funcionarios as $func) {
    $cargoNome = $cargos[$func['cargo_id']] ?? '';
    fputcsv($output, [
        $func['nome'],
        $func['email'],
        $func['telefone'],
        $cargoNome,
        date('d/m/Y', strtotime($func['data_cadastro'])),
        $func['status']
    ]);
}

fclose($output);
exit;
