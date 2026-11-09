<?php
require_once __DIR__ . '/../controllers/AlunoController.php';
require_once __DIR__ . '/../controllers/PropinaController.php'; // Verifique se o caminho está correto

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';


$db = new Database();
$pdo = $db->getConnection();

$nomeAluno = $_SESSION['nome'];


// Buscar dados do professor
$queryAluno = $pdo->prepare("SELECT id, nome_completo FROM aluno WHERE nome_completo = ?");
$queryAluno->execute([$nomeAluno]);
$alun = $queryAluno->fetch();

$aluno_id = $alun['id'] ?? null;

$classe_id = null;

if ($aluno_id) {
    $stmt = $pdo->prepare("SELECT classe_id FROM aluno WHERE id = ?");
    $stmt->execute([$aluno_id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    $classe_id = $aluno['classe_id'] ?? null;
}

$precos = [];

if ($classe_id) {
    $stmt = $pdo->prepare("SELECT * FROM precos WHERE classe_id = ?");
    $stmt->execute([$classe_id]);
    $precos = $stmt->fetch(PDO::FETCH_ASSOC);
}


$nomeAlun = isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '';
$alunoController = new AlunoController();
$aluno = $alunoController->getAlunoByName($nomeAlun);

if ($aluno && isset($aluno['classe'])) {
    $classe = $aluno['classe']; // Define a classe do aluno
    $idAluno = isset($aluno['id']) ? $aluno['id'] : '';
    // Agora você pode usar $classe para definir os preços com base na classe e no tipo de fatura
} else {
    $classe = ''; // Define uma classe padrão caso o aluno não seja encontrado
    //echo "<div class='alert alert-warning'>Aluno não encontrado ou sem classe definida.</div>";
} // Se você tiver um campo para o nome da classe

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeAluno = htmlspecialchars($_POST['nome_aluno']);
    $idAluno = intval($_POST['aluno_id']);
    $classe = intval($_POST['classe_id']);
    $tipoPagamento = htmlspecialchars($_POST['tipo_pagamento']); // novo
    $abreviatura = 'FP' . strtoupper(substr($tipoPagamento, 0, 3)); // gerar automaticamente
    $mes = htmlspecialchars($_POST['mes']);
    $custo = floatval($_POST['custo']);

    $referencia = sprintf('%011d', rand(0, 99999999999));
    $prazoDoc = date('Y-m-d', strtotime('+15 days'));
    $dataEmissao = date('Y-m-d');
    $situacao = 'Não Paga';

    $stmt = $pdo->prepare("INSERT INTO propinas (
    id_aluno, abreviatura, mes, classe_id, custo, entidade,
    referencia, prazo_doc, data, situacao
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $success = $stmt->execute([
        $idAluno,
        $abreviatura,
        $mes,
        $classe,
        $custo,
        '30400',
        $referencia,
        $prazoDoc,
        $dataEmissao,
        $situacao
    ]);


    if ($success) {
        $_SESSION['status'] = "Fatura gerada com sucesso.";
    } else {
        $_SESSION['status'] = "Erro ao gerar fatura.";
    }
}

?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title"></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Principal</a></li>
                        <li class="breadcrumb-item active">Pagamento</li>
                        <li class="breadcrumb-item active">Requisitar Fatura</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <?php
        if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>e-Scholar, </strong><?php echo $_SESSION['status']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria_hidden="true">&times;</span>
                </button>
            </div>
        <?php
            unset($_SESSION['status']);
        }

        ?>


        <!-- Formulário Requisitar Fatura -->
        <div class="row">
            <div class="col-12">
                <div class="product-payment-inner-st">

                    <div id="myTabContent" class="tab-content custom-product-edit">
                        <div class="product-tab-list tab-pane fade show active" id="description">

                            <div class="review-content-section">
                                <div id="dropzone1" class="pro-ad addcoursepro">

                                    <form method="POST" class="dropzone dropzone-custom needsclick addcourse" id="demo1-upload">
                                        <div class="row">

                                            <!-- Nome do Aluno -->
                                            <div class="col-6 mb-3">
                                                <div class="form-group">
                                                    <label for="nome_aluno">
                                                        <i class="fa fa-user"></i> Nome do Aluno
                                                    </label>
                                                    <input type="hidden" name="aluno_id" value="<?= $aluno_id ?>">
                                                    <input name="nome_aluno" type="text" class="form-control" placeholder="Nome do Aluno" readonly value="<?= htmlspecialchars($nomeAluno) ?>">
                                                </div>
                                            </div>

                                            <!-- Tipo de Fatura -->
                                            <div class="col-6 mb-3">
                                                <input type="hidden" id="classe_id" name="classe_id" value="<?php echo $classe_id; ?>">
                                                <div class="form-group">

                                                    <label for="tipo_pagamento">
                                                        <i class="fa fa-file"></i> Tipo de Fatura
                                                        <span style="color: red; font-size: 12px;">(*Campo Obrigatório)</span>
                                                    </label>
                                                    <select name="tipo_pagamento" id="tipo_pagamento" class="form-control" onchange="atualizarCusto()">
                                                        <option value="" disabled selected>Selecione o Tipo de Fatura</option>
                                                        <?php if ($precos): ?>
                                                            <option value="inscricao" data-valor="<?= $precos['inscricao'] ?>">Inscrição - <?= number_format($precos['inscricao'], 2, ',', '.') ?> MZN</option>
                                                            <option value="mensalidade" data-valor="<?= $precos['mensalidade'] ?>">Mensalidade - <?= number_format($precos['mensalidade'], 2, ',', '.') ?> MZN</option>
                                                            <option value="camisa" data-valor="<?= $precos['camisa'] ?>">Camisa - <?= number_format($precos['camisa'], 2, ',', '.') ?> MZN</option>
                                                            <option value="camiseta" data-valor="<?= $precos['camiseta'] ?>">Camiseta - <?= number_format($precos['camiseta'], 2, ',', '.') ?> MZN</option>
                                                            <option value="calcoes" data-valor="<?= $precos['calcoes'] ?>">Calções - <?= number_format($precos['calcoes'], 2, ',', '.') ?> MZN</option>
                                                            <option value="meia" data-valor="<?= $precos['meia'] ?>">Meia - <?= number_format($precos['meia'], 2, ',', '.') ?> MZN</option>
                                                    </select>
                                                <?php else: ?>
                                                    <p class="text-danger">Não foram encontrados preços para a sua classe.</p>
                                                <?php endif; ?>

                                                </div>
                                            </div>

                                            <?php
                                            // Obter o ano e o mês corrente
                                            $anoCorrente = date('Y'); // Ano corrente
                                            $mesCorrente = date('n'); // Número do mês corrente (1 a 12)
                                            $meses = [
                                                '1' => 'Janeiro',
                                                '2' => 'Fevereiro',
                                                '3' => 'Março',
                                                '4' => 'Abril',
                                                '5' => 'Maio',
                                                '6' => 'Junho',
                                                '7' => 'Julho',
                                                '8' => 'Agosto',
                                                '9' => 'Setembro',
                                                '10' => 'Outubro',
                                                '11' => 'Novembro',
                                                '12' => 'Dezembro'
                                            ];
                                            ?>

                                            <!-- Série/Ano -->
                                            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
                                                <div class="form-group">
                                                    <label for="serie_ano">
                                                        <i class="fa fa-calendar"></i> Série/Ano
                                                    </label>
                                                    <select name="serie_ano" class="form-control" required>
                                                        <option value="" disabled>Selecione a Série/Ano</option>
                                                        <option value="<?php echo $anoCorrente; ?>" selected><?php echo $anoCorrente; ?></option>
                                                        <option value="<?php echo $anoCorrente + 1; ?>"><?php echo $anoCorrente + 1; ?></option>
                                                    </select>
                                                </div>
                                            </div>


                                            <!-- Mês -->
                                            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
                                                <div class="form-group">
                                                    <label for="mes">
                                                        <i class="fa fa-calendar"></i> Mês
                                                    </label>
                                                    <select name="mes" class="form-control" required>
                                                        <option value="" disabled selected>Selecione o Mês</option>
                                                        <?php
                                                        foreach ($meses as $numero => $mes) {
                                                            $selected = ($numero == $mesCorrente) ? 'selected' : '';
                                                            echo "<option value=\"$numero\" $selected>$mes</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Custo -->
                                            <div class="col-4 mb-3">
                                                <div class="form-group">
                                                    <label for="custo">
                                                        <i class="fa fa-money"></i> Custo
                                                    </label>
                                                    <input name="custo" id="custo" type="number" step="0.01" class="form-control" placeholder="Custo" required readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botão de Submissão -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="payment-adress">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        <i class="fa fa-save"></i> Requisitar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- /Formulário -->

    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->

<script>
    function atualizarCusto() {
        const select = document.getElementById("tipo_pagamento");
        const selectedOption = select.options[select.selectedIndex];
        const valor = selectedOption.getAttribute("data-valor");

        // Preenche o campo de custo com o valor formatado (opcional: com 2 casas decimais)
        document.getElementById("custo").value = valor ? parseFloat(valor).toFixed(2) : '';
    }
</script>


<?php require_once __DIR__ . '/partials/footer.php'; ?>