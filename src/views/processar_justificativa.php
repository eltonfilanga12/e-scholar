<?php
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frequencia_id = $_POST['frequencia_id'] ?? null;
    $acao = $_POST['acao'] ?? null;

    if ($frequencia_id && in_array($acao, ['Aprovada', 'Rejeitada'])) {
        $sql = "UPDATE frequencias SET status_justificativa = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$acao, $frequencia_id]);
        $_SESSION['status'] = "Justificativa {$acao} com sucesso.";
    }
}

header('Location: responder_justif');
exit;
