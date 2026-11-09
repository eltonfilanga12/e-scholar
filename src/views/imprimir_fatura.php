<?php
require_once __DIR__ . '/../controllers/PropinaController.php';

if (!isset($_GET['id'])) {
    die('ID da fatura não especificado.');
}

$id = $_GET['id'];
$controller = new PropinaController();
$fatura = $controller->getFaturaById($id); // Deve incluir JOIN com alunos

if (!$fatura) {
    die('Fatura não encontrada.');
}

// Dados
$nomeAluno     = $fatura['nome_aluno'] ?? '---';
$codigoAluno   = $fatura['codigo_aluno'] ?? '---';
$curso         = $fatura['curso'] ?? '---';
$regime        = $fatura['regime'] ?? '---';
$tipoDoc       = strtoupper($fatura['abreviatura'] ?? '---');
$entidade      = $fatura['entidade'] ?? '---';
$referencia    = $fatura['referencia'] ?? '---';
$montante      = number_format($fatura['custo'], 2, ',', '.');
$dataDoc       = isset($fatura['prazo_doc']) ? date('d/m/Y', strtotime($fatura['prazo_doc'])) : '---';
$dataImpressao = date('d/m/Y \à\s H:i:s');
$numeroFatura  = str_pad($fatura['id_propinas'], 7, '0', STR_PAD_LEFT) . ' / ' . date('Y');
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Fatura <?= $numeroFatura ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            padding: 20px;
        }

        .recibo {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 20px;
            margin-bottom: 40px;
        }

        .cabecalho {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .secao {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .titulo-secao {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .linha {
            margin-bottom: 4px;
        }

        .label {
            font-weight: bold;
        }

        .duas-colunas {
            display: flex;
            justify-content: space-between;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

<?php for ($i = 0; $i < 2; $i++): ?>
    <div class="recibo">
        <div class="cabecalho">DIRECÇÃO DOS SERVIÇOS CENTRAIS DE ADMINISTRAÇÃO E FINANÇAS</div>
        <div class="linha"><span class="label">FACTURA Nº:</span> <?= $numeroFatura ?></div>

        <div class="secao">
            <div class="titulo-secao">DADOS DO ALUNO</div>
            <div class="linha"><span class="label">NOME:</span> <?= $nomeAluno ?> &nbsp;&nbsp; <span class="label"></div>
            <div class="linha"><span class="label"> &nbsp;&nbsp; <span class="label"></div>
        </div>

        <div class="secao">
            <div class="titulo-secao">DADOS DE PAGAMENTO</div>
            <div class="linha"><span class="label">TIPO DE DOCUMENTO:</span> <?= $tipoDoc ?></div>
            <div class="linha"><span class="label">ENTIDADE:</span> <?= $entidade ?></div>
            <div class="linha"><span class="label">REFERÊNCIA:</span> <?= $referencia ?></div>
            <div class="linha"><span class="label">MONTANTE:</span> <?= $montante ?> MT</div>
            <div class="linha"><span class="label">DATA DOC.:</span> <?= $dataDoc ?></div>
            <div class="linha"><span class="label">Data de impressão:</span> <?= $dataImpressao ?></div>
        </div>
    </div>
<?php endfor; ?>

</body>
</html>
