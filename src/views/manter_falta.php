<?php
require_once '../../config/database.php';
require_once __DIR__ . '/../helpers/LogHelper.php';

session_start();

if ($_SESSION['tipo'] !== 'Administrador') {
    echo "Acesso negado.";
    exit;
}

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['frequencia_id'] ?? null;

    if ($id) {
        // Atualiza para manter como falta (presenca = 0)
        $sql = "UPDATE frequencias SET presenca = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        // Busca dados para log
        $infoSql = "
            SELECT a.nome_completo AS aluno, d.nome AS disciplina, f.data
            FROM frequencias f
            JOIN aluno a ON f.aluno_id = a.id
            JOIN disciplinas d ON f.disciplina_id = d.id
            WHERE f.id = ?
        ";
        $infoStmt = $conn->prepare($infoSql);
        $infoStmt->execute([$id]);
        $dados = $infoStmt->fetch();

        $descricao = "Falta do aluno '{$dados['aluno']}' na disciplina '{$dados['disciplina']}' em {$dados['data']} foi confirmada (mantida como falta)";

        // Registra no log
        LogHelper::registrar($conn, $_SESSION['nome'], 'Confirmação de Falta', $descricao);

        $_SESSION['status'] = "Falta mantida com sucesso.";
    }
}

header('Location: justificativas_aprovadas.php');
exit;
