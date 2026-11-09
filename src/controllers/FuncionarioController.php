<?php
session_start();
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/../models/Funcionario.php';

$db = new Database();
$pdo = $db->getConnection();
$funcionarioModel = new Funcionario($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idade = !empty($_POST['data_nascimento']) ? (date('Y') - date('Y', strtotime($_POST['data_nascimento']))) : 0;
    $salario = 0;
    $valor_hora = 0;

    if (!empty($_POST['tipo_salario'])) {
        if ($_POST['tipo_salario'] === 'Mensal') {
            $salario = $_POST['salario'] ?? 0;
        } elseif ($_POST['tipo_salario'] === 'Hora') {
            $valor_hora = $_POST['valor_hora'] ?? 0;
        }
    }

    $dados = [
        'id' => $_POST['id'] ?? null,
        'nome_completo' => $_POST['nome_completo'] ?? '',
        'sexo' => $_POST['sexo'] ?? '',
        'data_nascimento' => $_POST['data_nascimento'] ?? '',
        'idade' => $idade,
        'nacionalidade' => $_POST['nacionalidade'] ?? '',
        'naturalidade' => $_POST['naturalidade'] ?? '',
        'documento_tipo' => $_POST['documento_tipo'] ?? '',
        'numero_documento' => $_POST['numero_documento'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'endereco' => $_POST['endereco'] ?? '',
        'cargo_id' => $_POST['cargo_id'] ?? null,
        'departamento_id' => $_POST['departamento_id'] ?? null,
        'data_admissao' => $_POST['data_admissao'] ?? null,
        'turno_id' => $_POST['turno_id'] ?? null,
        'tipo_salario' => $_POST['tipo_salario'] ?? '',
        'salario' => $salario,
        'valor_hora' => $valor_hora,

        'usuario_id' => $_POST['usuario_id'] ?? null,
        'status' => $_POST['status'] ?? 'Ativo',
    ];

    if ($funcionarioModel->salvar($dados)) {
        $_SESSION['mensagem'] = 'Funcionário salvo com sucesso!';
    } else {
        $_SESSION['mensagem'] = 'Erro ao salvar funcionário.';
    }

    header('Location: ../views/funcionarios.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($funcionarioModel->excluir($id)) {
        $_SESSION['mensagem'] = 'Funcionário removido com sucesso!';
    } else {
        $_SESSION['mensagem'] = 'Erro ao remover funcionário.';
    }
    header('Location: ../views/funcionarios.php');
    exit;
}
