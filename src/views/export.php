<?php
require_once __DIR__ . '/../../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Parâmetros obrigatórios
$tabela = $_GET['tabela'] ?? null;
if (!$tabela) {
    die('Parâmetro "tabela" é obrigatório.');
}

// Colunas a exportar (opcional)
$colunas = isset($_GET['colunas']) ? explode(',', $_GET['colunas']) : [];

// Filtros opcionais
$filtros = $_GET['filtros'] ?? []; // ex: filtros[classe_id]=1&filtros[turma_id]=2

$params = [];
$where = [];

// Montar cláusula WHERE dinamicamente
if (!empty($filtros) && is_array($filtros)) {
    foreach ($filtros as $campo => $valor) {
        $where[] = "$campo = ?";
        $params[] = $valor;
    }
}

// Preparar SQL
if (empty($colunas)) {
    $sql = "SELECT * FROM $tabela";
} else {
    $colunasList = implode(',', $colunas);
    $sql = "SELECT $colunasList FROM $tabela";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

// Executar query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$dados) {
    die('Nenhum dado encontrado.');
}

// Cabeçalhos CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $tabela . '_' . date('Ymd_His') . '.csv"');

// BOM UTF-8 para Excel
echo "\xEF\xBB\xBF";

// Abrir output
$output = fopen('php://output', 'w');

// Cabeçalho da tabela
fputcsv($output, array_keys($dados[0]));

// Conteúdo
foreach ($dados as $linha) {
    fputcsv($output, $linha);
}

fclose($output);
exit;
