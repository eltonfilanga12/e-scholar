<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/partials/functions.php';
require_once __DIR__ . '/../helpers/LogHelper.php';

session_start();

// Verifica o tipo de usuário
$tipoUsuario = $_SESSION['tipo'] ?? 'Professor';
$nomeProf = $_SESSION['nome'] ?? 'Professor';

if ($tipoUsuario !== 'Professor') {
    $_SESSION['status'] = "Apenas professores podem solicitar permissão.";
    header("Location: lancar_notas.php");
    exit;
}

// Somente POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura e valida os campos recebidos
    $professor_id  = intval($_POST['professor_id'] ?? 0);
    $aluno_id      = intval($_POST['aluno_id'] ?? 0);
    $disciplina_id = intval($_POST['disciplina_id'] ?? 0);
    $campo_nota    = trim($_POST['campo_nota'] ?? '');
    $motivo        = trim($_POST['motivo'] ?? '');

    // Verifica se os campos obrigatórios estão preenchidos
    if (!$professor_id || !$aluno_id || !$disciplina_id || empty($campo_nota) || empty($motivo)) {
        $_SESSION['status'] = "Todos os campos são obrigatórios. Verifique e tente novamente.";
        header("Location: lancar_notas.php");
        exit;
    }

    try {
        // Conexão com o banco
        $db = new Database();
        $pdo = $db->getConnection();

        // Insere solicitação
        $stmt = $pdo->prepare("
            INSERT INTO solicitacoes_nota 
                (professor_id, aluno_id, disciplina_id, campo_nota, motivo, status)
            VALUES 
                (:professor_id, :aluno_id, :disciplina_id, :campo_nota, :motivo, 'pendente')
        ");
        $stmt->execute([
            ':professor_id' => $professor_id,
            ':aluno_id' => $aluno_id,
            ':disciplina_id' => $disciplina_id,
            ':campo_nota' => $campo_nota,
            ':motivo' => $motivo
        ]);

        // Registra log de auditoria
        LogHelper::registrar(
            $pdo,
            $nomeProf,
            'Solicitação de alteração de nota',
            "Professor ID $professor_id solicitou alteração de '$campo_nota' do aluno ID $aluno_id na disciplina ID $disciplina_id. Motivo: $motivo"
        );

        $_SESSION['status'] = "Solicitação enviada ao administrador com sucesso!";
        header("Location: lancar_notas.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['status'] = "Erro ao enviar solicitação: " . $e->getMessage();
        header("Location: lancar_notas.php");
        exit;
    }
} else {
    // Caso acessem o arquivo diretamente
    $_SESSION['status'] = "Acesso inválido.";
    header("Location: lancar_notas.php");
    exit;
}
