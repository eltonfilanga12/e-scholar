<?php

// /src/controllers/AlunoController.php
require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '../../../config/database.php';

$db = new Database();
$conn = $db->getConnection();
$student = new Student($conn);

$students = $student->getAllStudents();

class AlunoController
{
    private $alunoModel;

    // Construtor que inicializa a conexão com o modelo Aluno
    public function __construct()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $this->alunoModel = new Student($conn); // Passa a conexão para o modelo Aluno
    }

    // Método para pegar todos os alunos
    public function getAllAlunos()
    {
        return $this->alunoModel->getAllStudents();
    }

    // Método para pegar os detalhes de um aluno específico pelo nome
    public function getAlunoByName($nome)
    {
        return $this->alunoModel->findByName($nome);
    }

    public function getAlunoById($id)
    {
        return $this->alunoModel->getStudentById($id);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'carregar') {
    $classe_id = $_GET['classe_id'];
    $ano = $_GET['ano_lectivo'];

    $alunos = $alunoModel->listarPorClasseEAno($classe_id, $ano);
    $turmas = $alunoModel->listarTurmasPorClasse($classe_id);

    $alunosDistribuidos = $alunoModel->distribuirAlunosEquilibrado($alunos, $turmas);

    echo json_encode(['alunos' => $alunosDistribuidos, 'turmas' => $turmas]);
    exit;
}

// Salvar alterações manuais
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alunos = $_POST['aluno_id'];
    $turmas = $_POST['turma_id'];

    foreach ($alunos as $i => $aluno_id) {
        $alunoModel->atualizarTurma($aluno_id, $turmas[$i]);
    }

    $_SESSION['status'] = "Turmas atribuídas com sucesso!";
    header("Location: ../views/atribuir_turma.php");
    exit;
}
