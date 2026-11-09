<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '../../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redireciona para a página de matrícula caso alguém acesse diretamente
    header("Location: ../views/matricula.php");
    exit;
}


// Dados do formulário
$nome = $_POST['nome_completo'];
$sexo = $_POST['sex'];
$data_nasc = $_POST['birth_date'];
$idade = $_POST['age'];
$classe = $_POST['classe'];
$religiao = $_POST['religion'];
$tipo_doc = $_POST['document_type'];
$numero_doc = $_POST['numero_doc'];
$nacionalidade = $_POST['nationality'];
$naturalidade = $_POST['birth_place'];
$bairro = $_POST['neighborhood'];
$turno_id = $_POST['turno_id'];

$pai_nome = $_POST['father_name'];
$pai_profissao = $_POST['father_occupation'];
$pai_contacto = $_POST['father_contact'];
$pai_workplace = $_POST['father_workplace'] ?? null;

$mae_nome = $_POST['mother_name'];
$mae_profissao = $_POST['mother_occupation'];
$mae_contacto = $_POST['mother_contact'];
$mae_workplace = $_POST['mother_workplace'] ?? null;

$encarregado_nome = $_POST['education_officer_name'];
$encarregado_contacto = $_POST['education_officer_contact'];
$encarregado_residencia = $_POST['education_officer_residence'];
$tem_certificado = $_POST['tem_certificado'] ?? null;
$escola_proveniencia = $_POST['escola_proveniencia'] ?? null;


// Inserir aluno (sem turma)
$query = "INSERT INTO aluno (
    nome_completo, sexo, data_nascimento, idade, religiao, tipo_documento, numero_doc, 
    naturalidade, nacionalidade, bairro, classe_id, turno_id, tem_certificado, 
    escola_proveniencia, nome_pai, profissao_pai, contacto_pai, local_trabalho_pai, 
    nome_mae, profissao_mae, contacto_mae, local_trabalho_mae, nome_encarregado, 
    contacto_encarregado, residencia_encarregado
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

$stmt = $conn->prepare($query);
$ok = $stmt->execute([
    $nome,
    $sexo,
    $data_nasc,
    $idade,
    $religiao,
    $tipo_doc,
    $numero_doc,
    $naturalidade,
    $nacionalidade,
    $bairro,
    $classe,
    $turno_id,
    $tem_certificado,
    $escola_proveniencia,
    $pai_nome,
    $pai_profissao,
    $pai_contacto,
    $pai_workplace,
    $mae_nome,
    $mae_profissao,
    $mae_contacto,
    $mae_workplace,
    $encarregado_nome,
    $encarregado_contacto,
    $encarregado_residencia
]);


$usuario = usuarioLogado();
if ($ok) {
    LogHelper::registrar($conn, $usuario, 'Matricula', "Aluno '$nome' matriculado com sucesso.");
    header("Location: ../views/matricula.php?status=success");
    exit;
} else {
    LogHelper::registrar($conn, $usuario, 'Erro', "Falha ao matricular aluno '$nome'.");
    header("Location: ../views/matricula.php?status=error");
    exit;
}
