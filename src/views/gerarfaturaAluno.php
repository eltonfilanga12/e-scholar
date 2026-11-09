<?php
// Inclua o controlador ou modelo para obter dados do aluno
require_once __DIR__ . '/../controllers/AlunoController.php';
require_once __DIR__ . '/../controllers/PropinaController.php';

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';


$nomeAluno = isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '';
$idAluno = isset($_GET['aluno_id']) ? intval($_GET['aluno_id']) : null;
$alunoController = new AlunoController();
$aluno = $alunoController->getAlunoByName($nomeAluno);
$alun = $alunoController->getAlunoById($idAluno);

if ($aluno && isset($aluno['classe_id'])) {
    $classe = $aluno['classe_id']; // Define a classe do aluno
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
    $tipoFatura = htmlspecialchars($_POST['tipo_fatura']);
    $serieAno = htmlspecialchars($_POST['serie_ano']);
    $mes = htmlspecialchars($_POST['mes']);
    $custo = floatval($_POST['custo']);

    // Gerar uma referência automática com 11 números
    $referencia = sprintf('%011d', rand(0, 99999999999)); // Gera uma referência com 11 dígitos

    // Calcular a data de vencimento com base na data atual e prazo de 15 dias
    $prazoDoc = date('Y-m-d', strtotime('+15 days'));

    $propinaController = new PropinaController();
    $success = $propinaController->createPropina($idAluno, $classe, $tipoFatura, $serieAno, $mes, $custo, '30400', $referencia, $prazoDoc);

    if ($success) {
        $_SESSION['status'] =" Fatura gerada com sucesso.";
    } else {
        $_SESSION['status'] =" Erro ao gerar fatura.";
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
                    <h3 class="page-title">Gestão de Propinas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a >Dashboard</a></li>
                        <li class="breadcrumb-item active">Gerar Factura</li>
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

        <!-- Table -->

        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="review-content-section">
                    <div id="dropzone1" class="pro-ad addcoursepro">
                        <form action="gerarfaturaAluno" method="post" class=" needsclick addcourse" id="demo1-upload">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="nome_aluno">
                                            <i class="fa fa-user"></i> Nome do Aluno
                                        </label>
                                        <input type="hidden" id="aluno_id" name="aluno_id" value="<?php echo htmlspecialchars($idAluno); ?>">
                                        <input name="nome_aluno" type="text" class="form-control" value="<?php echo $nomeAluno; ?>" readonly>
                                    </div>
                                    <input type="hidden" id="classe_id" name="classe_id" value="<?php echo $classe; ?>">
                                    <div class="form-group">
                                        <label for="tipo_fatura">
                                            <i class="fa fa-file"></i> Tipo de Fatura
                                            <span style="color: red; font-size: 12px;">(*Campo Obrigatório)</span>
                                        </label>
                                        <select name="tipo_fatura" class="form-control" id="tipo_fatura">
                                            <option value="" disabled selected>Selecione o Tipo de Fatura</option>
                                            <option value="Mensalidade">Mensalidade</option>
                                            <option value="Camiseta">Camiseta</option>
                                            <option value="Camisa">Camisa</option>
                                            <option value="Meias">Meias</option>
                                            <option value="Calcoes">Calções</option>
                                        </select>
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

                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
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

                                <!-- Select Mês com Ícone -->
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="mes">
                                            <i class="fa fa-calendar"></i> Mês
                                        </label>
                                        <select name="mes" class="form-control" required>
                                            <option value="" disabled>Selecione o Mês</option>
                                            <?php
                                            foreach ($meses as $numero => $mes) {
                                                $selected = ($numero == $mesCorrente) ? 'selected' : '';
                                                echo "<option value=\"$numero\" $selected>$mes</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label for="custo">
                                            <i class="fa fa-money"></i> Custo
                                        </label>
                                        <input name="custo" id="custo" type="number" class="form-control" placeholder="Custo" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="payment-adress">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            <i class="fa fa-save"></i> Enviar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Table -->

    </div>
    <!-- /Page Content -->

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script>
    document.getElementById('tipo_fatura').addEventListener('change', function() {
        var tipoFatura = this.value;
        var classe = document.getElementById('classe_id').value;

        // Verificar se os valores estão definidos
        if (classe && tipoFatura) {
            // Fazer a solicitação AJAX para buscar o preço
            fetch('get_preco.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        classe_id: classe,
                        tipo_fatura: tipoFatura
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar o campo 'custo' com o preço retornado
                        document.getElementById('custo').value = data.preco;
                    } else {
                        alert('Erro ao buscar o preço.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }
    });
</script>