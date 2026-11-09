<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Professor.php';


$db = new Database();
$pdo = $db->getConnection();

$professorModel = new Professor();
$professores = $professorModel->listar();

// Cabeçalhos CSV para Excel
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="professores_' . date('Ymd_His') . '.csv"');

// BOM UTF-8 para Excel
echo "\xEF\xBB\xBF";

// Abrir output
$output = fopen('php://output', 'w');

// Cabeçalho
$header = ['Nome', 'Email', 'Telefone', 'Grau Acadêmico', 'Especialidade', 'Gênero', 'Status', 'Disciplinas'];
fputcsv($output, $header);

// Escrever dados
foreach ($professores as $prof) {
    // Se for array, implode; se for string, deixa como está
    $disciplinas = is_array($prof['disciplinas']) ? implode(', ', $prof['disciplinas']) : $prof['disciplinas'] ?? '';

    fputcsv($output, [
        $prof['nome_completo'],
        $prof['email'],
        $prof['contacto'],
        $prof['grau_academico'] ?? '',
        $prof['especialidade'] ?? '',
        $prof['genero'] ?? '',
        $prof['status'] ?? '',
        $disciplinas
    ]);
}

fclose($output);
exit;
