<?php
require_once __DIR__ . '../../../config/database.php'; // Ajuste o caminho conforme necessário

// Receber e decodificar a entrada JSON
$data = json_decode(file_get_contents('php://input'), true);

$classeId = $data['classe_id'];
$tipoFatura = $data['tipo_fatura'];
$preco = 0;

if ($classeId && $tipoFatura) {
    // Definir a consulta com base no tipo de fatura
    $query = "";
    switch ($tipoFatura) {
        case 'Mensalidade':
            $query = "SELECT mensalidade AS preco FROM precos WHERE classe_id = :classe_id";
            break;
        case 'Propina':
            $query = "SELECT propina AS preco FROM precos WHERE classe_id = :classe_id";
            break;
        case 'Camiseta':
            $query = "SELECT camiseta AS preco FROM precos WHERE classe_id = :classe_id";
            break;
        case 'Camisa':
            $query = "SELECT camisa AS preco FROM precos WHERE classe_id = :classe_id";
            break;
        case 'Meias':
            $query = "SELECT meia AS preco FROM precos WHERE classe_id = :classe_id";
            break;
        case 'Calcoes':
            $query = "SELECT calcoes AS preco FROM precos WHERE classe_id = :classe_id";
            break;
        default:
            $query = "SELECT 0 AS preco"; // Valor padrão caso o tipo de fatura não corresponda
    }

    // Preparar e executar a consulta
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':classe_id', $classeId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $preco = $result['preco'];
    }

    echo json_encode(['success' => true, 'preco' => $preco]);
} else {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
}
