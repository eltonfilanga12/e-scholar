<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../models/Professor.php';
require_once __DIR__ . '/../helpers/LogHelper.php';

$professor = new Professor();
$usuario = usuarioLogado();


// CADASTRAR / EDITAR PROFESSORES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['acao'])) {
    $dados = [
        'funcionario_id' => $_POST['funcionario_id'] ?? null,
        'email' => $_POST['email'] ?? null,
        'telefone' => $_POST['telefone'] ?? null,
        'genero' => $_POST['genero'] ?? null,
        'data_nascimento' => $_POST['data_nascimento'] ?? null,
        'grau_academico' => $_POST['grau_academico'] ?? null,
        'especialidade' => $_POST['especialidade'] ?? null,
        'disciplina_id' => $_POST['disciplina_id'] ?? null,
        'status' => $_POST['status'] ?? 'Ativo',
        'id' => $_POST['id'] ?? null,
    ];

    if (!empty($dados['id'])) {
        $professor->atualizar($dados);
        LogHelper::registrar($professor->getConnection(), $usuario, 'Editar Professor', "Atualizou dados do professor ID {$dados['id']}");
    } else {
        $professor->salvar($dados);
        LogHelper::registrar($professor->getConnection(), $usuario, 'Cadastrar Professor', "Cadastrou um novo professor ({$dados['email']})");
    }

    header('Location: ../views/professores.php');
    exit;
}

// EXCLUIR PROFESSOR
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $professor->eliminar($id);
    LogHelper::registrar($professor->getConnection(), $usuario, 'Eliminar Professor', "Eliminou o professor ID $id");
    header('Location: ../views/professores.php');
    exit;
}

// ATRIBUIÇÃO: criar
if (isset($_POST['acao']) && $_POST['acao'] === 'salvarAtribuicao') {
    $professor->atribuirTurmaClasse(
        $_POST['professor_id'],
        $_POST['classe_id'],
        $_POST['turma_id']
    );
    LogHelper::registrar($professor->getConnection(), $usuario, 'Atribuir Turma/Classe', "Atribuiu turma {$_POST['turma_id']} à classe {$_POST['classe_id']} para o professor {$_POST['professor_id']}");
    header('Location: ../views/atribuir_turma_classe.php');
    exit;
}

// ATRIBUIÇÃO: editar
if (isset($_POST['acao']) && $_POST['acao'] === 'editarAtribuicao') {
    $professor->editarAtribuicao(
        $_POST['professor_id'],
        $_POST['classe_id'],
        $_POST['turma_id']
    );
    LogHelper::registrar($professor->getConnection(), $usuario, 'Editar Atribuição', "Editou atribuição do professor {$_POST['professor_id']} para turma {$_POST['turma_id']}");
    header('Location: ../views/atribuir_turma_classe.php');
    exit;
}

// ATRIBUIÇÃO: eliminar
if (isset($_POST['acao']) && $_POST['acao'] === 'eliminarAtribuicoes' && !empty($_POST['professor_id'])) {
    $professor->eliminarAtribuicao($_POST['professor_id']);
    LogHelper::registrar($professor->getConnection(), $usuario, 'Eliminar Atribuição', "Removeu todas as atribuições do professor {$_POST['professor_id']}");
    header('Location: ../views/atribuir_turma_classe.php');
    exit;
}
