<?php
require_once __DIR__ . '/../controllers/AlunoController.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $db = new Database();
    $conn = $db->getConnection();
    $student = new Student($conn);

    // Obter os detalhes do aluno
    $studentDetails = $student->getStudentById($id);

    // Verificar se os detalhes foram retornados
    if (!$studentDetails) {
        echo "Aluno não encontrado.";
        exit;
    }
}

// Atualizar aluno
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_completo = $_POST['nome_completo'];
    $data_nascimento = $_POST['data_nascimento'];
    $classe = $_POST['classe'];
    $turma_id = $_POST['turma_id'];
    $data_inscricao = $_POST['data_inscricao'];

    $nome_pai = $_POST['nome_pai'];
    $profissao_pai = $_POST['profissao_pai'];
    $contacto_pai = $_POST['contacto_pai'];
    $nome_mae = $_POST['nome_mae'];
    $profissao_mae = $_POST['profissao_mae'];
    $contacto_mae = $_POST['contacto_mae'];
    $nome_encarregado = $_POST['nome_encarregado'];
    $profissao_encarregado = $_POST['profissao_encarregado'];
    $contacto_encarregado = $_POST['contacto_encarregado'];
    $email_encarregado = $_POST['email_encarregado'];

    $result = $student->updateStudent($id, $nome_completo, $data_nascimento, $classe, $turma_id, $data_inscricao, $nome_pai, $profissao_pai, $contacto_pai, $nome_mae, $profissao_mae, $contacto_mae, $nome_encarregado, $profissao_encarregado, $contacto_encarregado, $email_encarregado);
    if ($result) {
        header("Location: alunos.php?success=1");
        exit;
    } else {
        echo "Erro ao atualizar aluno.";
    }
}
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Gestão de Propinas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Propinas</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="add-propina.php" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Registrar Pagamento
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs de Formulário -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="formTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab">Informações Básicas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="account-tab" data-toggle="tab" href="#account" role="tab">Informações da Conta</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="formTabsContent">
                            <!-- Aba de Informações Básicas -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <form action="../models/Matricula.php" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <!-- Dados do Aluno -->
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <!-- Linha 1 -->
                                            <div class="row">
                                                <div class="col-lg-12 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="full_name" type="text" class="form-control" placeholder="Nome Completo">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <select name="sex" class="form-control">
                                                            <option value="none" selected disabled>Sexo</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Feminino">Feminino</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="birth_date" type="date" class="form-control" placeholder="Data de Nascimento">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 2 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="age" type="number" class="form-control" placeholder="Idade">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="religion" type="text" class="form-control" placeholder="Religião Praticada">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <select name="document_type" class="form-control">
                                                            <option value="none" selected disabled>Tipo de Documento</option>
                                                            <option value="bi">BI</option>
                                                            <option value="cedula">Cédula Pessoal</option>
                                                            <option value="dire">DIRE</option>
                                                            <option value="passport">Passaporte</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="birth_place" type="text" class="form-control" placeholder="Naturalidade">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 3 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="nationality" type="text" class="form-control" placeholder="Nacionalidade">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="residence" type="text" class="form-control" placeholder="Residente em">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 4 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="neighborhood" type="text" class="form-control" placeholder="Bairro">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <select name="classe" class="form-control">
                                                            <option value="none" selected disabled>Classe</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <select name="repetente" class="form-control">
                                                            <option value="none" selected disabled>Repetente</option>
                                                            <option value="0">Sim</option>
                                                            <option value="1">Nao</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Dados dos Pais -->
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <!-- Linha 5 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="father_name" type="text" class="form-control" placeholder="Nome do Pai (conforme vem no documento)">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="father_occupation" type="text" class="form-control" placeholder="Profissão do Pai">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 6 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="father_contact" type="text" class="form-control" placeholder="Contacto do Pai">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="father_email" type="email" class="form-control" placeholder="Email do Pai">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 7 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="mother_name" type="text" class="form-control" placeholder="Nome da Mãe (conforme vem no documento)">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="mother_occupation" type="text" class="form-control" placeholder="Profissão da Mãe">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 8 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="mother_contact" type="text" class="form-control" placeholder="Contacto da Mãe">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="mother_email" type="email" class="form-control" placeholder="Email da Mãe">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 9 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="education_officer_name" type="text" class="form-control" placeholder="Carregado de Educação (Nome)">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="education_officer_occupation" type="text" class="form-control" placeholder="Profissão do Carregado de Educação">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 10 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="education_officer_contact" type="text" class="form-control" placeholder="Contacto do Carregado de Educação">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="education_officer_email" type="email" class="form-control" placeholder="Email do Carregado de Educação">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 11 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input name="education_officer_residence" type="text" class="form-control" placeholder="Residente em (Carregado de Educação)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Botões -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="payment-adress">
                                                <button type="submit" class="btn btn-primary waves-effect waves-light">Enviar</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>

                            <!-- Aba de Informações da Conta (futura expansão) -->
                            <div class="tab-pane fade" id="account" role="tabpanel">
                                <p class="mt-3">Esta seção será preenchida posteriormente com informações financeiras relacionadas à conta do aluno.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<?php require_once __DIR__ . '/partials/footer.php'; ?>