<?php
require_once __DIR__ . '../../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Verifica se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $falta_id = $_POST['falta_id'] ?? null;
    $justificativa_texto = $_POST['justificativa_texto'] ?? null;
    $comprovativo = $_FILES['comprovativo'] ?? null;

    // Validação básica
    if (!$falta_id || !$justificativa_texto || !$comprovativo) {
        $_SESSION['erro'] = 'Todos os campos são obrigatórios.';
        header('Location: meusdados');
        exit;
    }

    // Diretório de upload
    $uploadDir = '../uploads/comprovativos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Processar o arquivo
    $extensao = pathinfo($comprovativo['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid('comprovativo_') . '.' . $extensao;
    $caminhoDestino = $uploadDir . $nomeArquivo;

    if (move_uploaded_file($comprovativo['tmp_name'], $caminhoDestino)) {
        // Atualiza a frequência com justificativa
        $sql = "UPDATE frequencias SET 
                    justificativa_texto = ?, 
                    comprovativo = ?, 
                    status_justificativa = 'Pendente' 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$justificativa_texto, $nomeArquivo, $falta_id]);

        $_SESSION['status'] = 'Justificativa enviada com sucesso!';
    } else {
        $_SESSION['status'] = 'Erro ao fazer upload do comprovativo.';
    }
} else {
    $_SESSION['status'] = 'Requisição inválida.';
}

header('Location: meusdados');
exit;
