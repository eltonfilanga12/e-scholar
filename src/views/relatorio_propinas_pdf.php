<?php 
require_once __DIR__ . '/../controllers/PropinasResumoController.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use Dompdf\Dompdf;

// --- Função para converter mês numérico para texto ---
function mesExtenso($mes) {
    $meses = [
        '1' => 'Janeiro', '2' => 'Fevereiro', '3' => 'Março',    '4' => 'Abril',
        '5' => 'Maio',    '6' => 'Junho',     '7' => 'Julho',    '8' => 'Agosto',
        '9' => 'Setembro','10' => 'Outubro',   '11' => 'Novembro', '12' => 'Dezembro'
    ];
    return $meses[$mes] ?? $mes;
}

try {
    // Verificação dos parâmetros
    if (!isset($_GET['aluno_id'], $_GET['inicio'], $_GET['fim'])) {
        echo "<p>Parâmetros ausentes. Tente novamente.</p>";
        exit;
    }

    $aluno_id = intval($_GET['aluno_id']);
    $data_inicio = $_GET['inicio'];
    $data_fim = $_GET['fim'];

    // Obter detalhes do aluno
    $controller = new PropinasResumoController();
    $detalhes = $controller->getDetalhesAluno($aluno_id);

    if (!$detalhes) {
        echo "<p>Aluno não encontrado.</p>";
        exit;
    }

    // Banco de dados
    $db = new Database();
    $pdo = $db->getConnection();

    // Dados da instituição
    $stmtInst = $pdo->query("SELECT * FROM instituicoes LIMIT 1");
    $instituicao = $stmtInst->fetch(PDO::FETCH_ASSOC);

    // Buscar propinas
    $stmtPropinas = $pdo->prepare("
        SELECT * FROM propinas 
        WHERE id_aluno = ? AND data BETWEEN ? AND ?
    ");
    $stmtPropinas->execute([$aluno_id, $data_inicio, $data_fim]);
    $propinas = $stmtPropinas->fetchAll(PDO::FETCH_ASSOC);

    // Início do HTML
    $dados = "<!DOCTYPE html><html><head>";
    $dados .= "<meta charset='UTF-8'>";
    $dados .= "<title>Extrato de Propinas</title>";
    $dados .= "</head><body>";

    // Cabeçalho
    $dados .= "<table width='100%' cellpadding='5' style='border-collapse: collapse; font-size:12px;'>";
    $dados .= "<tr><td width='65%'>";

    // LOGO DA INSTITUIÇÃO (Base64)
    if (!empty($instituicao['foto'])) {
        $filePath = __DIR__ . "/../uploads/" . $instituicao['foto'];

        if (file_exists($filePath)) {
            $tipoImg = pathinfo($filePath, PATHINFO_EXTENSION);
            $imgBase64 = base64_encode(file_get_contents($filePath));
            $dados .= "<img src='data:image/$tipoImg;base64,$imgBase64' style='width:200px; height:60px;'><br>";
        }
    }

    // Dados da instituição
    $dados .= "<b>{$instituicao['nome']}</b><br>";
    $dados .= "<b>Endereço:</b> {$instituicao['endereco']}<br>";
    $dados .= "<b>Tel.:</b> {$instituicao['telefone']}<br>";
    $dados .= "<b>NUIT:</b> {$instituicao['nuit']}<br>";
    $dados .= "<b>E-mail:</b> {$instituicao['email']}<br>";
    $dados .= "<b>Website:</b> {$instituicao['website']}<br>";

    $dados .= "</td><td width='35%'>";
    $dados .= "Exmo.(s) Sr.(s)<br>";
    $dados .= "<b>" . htmlspecialchars($detalhes['nome_aluno']) . "</b><br>";
    $dados .= "<b>Classe:</b> {$detalhes['classe']} <br>";
    $dados .= "<b>Turma:</b> {$detalhes['turma']} <br>";
    $dados .= "</td></tr></table><hr>";

    // TABELA DAS PROPINAS
    $dados .= "<table width='100%' border='1' cellpadding='5' style='border-collapse: collapse; font-size:13px;'>";
    $dados .= "<thead><tr>
                <th>Mês</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Data Doc</th>
                <th>Prazo</th>
                <th>Data Pag.</th>
                <th>Situação</th>
               </tr></thead><tbody>";

    $totalDivida = 0;
    $totalPago   = 0;

    foreach ($propinas as $f) {

        // MÊS POR EXTENSO
        $mesTexto = mesExtenso($f['mes']);

        // Tipo da propina
        $tipo = (strpos(strtolower($f['abreviatura']), 'multa') !== false) ? 'Multa' : 'Mensalidade';

        // Pagamento da propina
        $stmtPag = $pdo->prepare("SELECT * FROM pagamentos WHERE id_propina = ?");
        $stmtPag->execute([$f['id_propinas']]);
        $pagamento = $stmtPag->fetch(PDO::FETCH_ASSOC);

        $data_pag = ($pagamento && $pagamento['situacao'] === 'Paga')
            ? $pagamento['data_pagamento'] 
            : "-";

        // Acumuladores
        if ($f['situacao'] === 'Não Paga') {
            $totalDivida += $f['custo'];
        } else {
            $totalPago += $f['custo'];
        }

        // Linha da tabela
        $dados .= "<tr>
                    <td>$mesTexto</td>
                    <td>" . number_format($f['custo'], 2, ',', '.') . "</td>
                    <td>$tipo</td>
                    <td>{$f['data']}</td>
                    <td>{$f['prazo_doc']}</td>
                    <td>{$data_pag}</td>
                    <td>{$f['situacao']}</td>
                   </tr>";
    }

    $dados .= "</tbody><tfoot>";
    $dados .= "<tr><td colspan='6' align='right'><b>Total da Dívida</b></td>
               <td>" . number_format($totalDivida, 2, ',', '.') . "</td></tr>";
    $dados .= "<tr><td colspan='6' align='right'><b>Total Pago</b></td>
               <td>" . number_format($totalPago, 2, ',', '.') . "</td></tr>";
    $dados .= "</tfoot></table>";

    $dados .= "</body></html>";

    // GERAR PDF
    $dompdf = new Dompdf(["enable_remote" => true]);
    $dompdf->loadHtml($dados);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Extrato_Propinas_" . str_replace(" ", "_", $detalhes['nome_aluno']) . ".pdf");

} catch (Exception $e) {
    echo "Ocorreu um erro. Contacte o administrador.";
}
?>
