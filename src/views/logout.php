<?php
session_start();

require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/../vendor/autoload.php'; // DOMPDF + PHPMailer

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$usuario = $_SESSION['nome'];
$tipoUsuario = $_SESSION['tipo'] ?? '';
$dataHoje = date('Y-m-d');

/**
 * Função para gerar PDF do relatório diário
 */
function gerarPDF($conn, $usuario, $dataHoje)
{
    $stmt = $conn->prepare("
        SELECT * FROM logs 
        WHERE usuario = ? AND DATE(data_hora) = ? 
        ORDER BY data_hora ASC
    ");
    $stmt->execute([$usuario, $dataHoje]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$logs) return null;

    $html = "<h2>Relatório Diário - $usuario</h2>";
    $html .= "<p>Data: " . date('d/m/Y') . "</p>";
    $html .= "<table border='1' width='100%' cellspacing='0' cellpadding='5'>
                <tr><th>Hora</th><th>Ação</th><th>Descrição</th><th>IP</th></tr>";

    foreach ($logs as $log) {
        $hora = date('H:i:s', strtotime($log['data_hora']));
        $html .= "<tr>
                    <td>$hora</td>
                    <td>" . htmlspecialchars($log['acao']) . "</td>
                    <td>" . htmlspecialchars($log['descricao']) . "</td>
                    <td>" . htmlspecialchars($log['ip']) . "</td>
                  </tr>";
    }
    $html .= "</table>";

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = "relatorio_{$usuario}_" . date('Ymd') . ".pdf";
    $pdfOutput = $dompdf->output();

    // Salva na pasta relatorios
    file_put_contents(__DIR__ . "/../relatorios/$filename", $pdfOutput);

    return ['nome' => $filename, 'conteudo' => $pdfOutput];
}

// Se for Aluno, apenas logout normal
if (strtolower($tipoUsuario) === 'aluno') {
    LogHelper::registrar($conn, $usuario, 'Logout', 'Aluno saiu do sistema');
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Para outros tipos de usuário: perguntar sobre fecho do dia
$stmt = $conn->prepare("
    SELECT * FROM logs 
    WHERE usuario = ? AND acao = 'Fecho de Sistema' AND DATE(data_hora) = ?
");
$stmt->execute([$usuario, $dataHoje]);
$fechado = $stmt->fetch();

if (!$fechado) {
    if (!isset($_POST['fechar_dia'])) {
        echo "<form method='POST'>
                <p>Deseja fechar o dia antes de sair e gerar relatório?</p>
                <button type='submit' name='fechar_dia' value='1'>Sim, Fechar Dia</button>
                <button type='submit' name='fechar_dia' value='0'>Não, Sair</button>
              </form>";
        exit;
    }

    if ($_POST['fechar_dia'] == '1') {
        $pdf = gerarPDF($conn, $usuario, $dataHoje);
        if ($pdf) {
            LogHelper::registrar($conn, $usuario, 'Fecho de Sistema', "Relatório do dia gerado: " . $pdf['nome']);

            // Envia headers para download automático
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $pdf['nome'] . '"');
            header('Content-Length: ' . strlen($pdf['conteudo']));
            echo $pdf['conteudo'];

            // Registrar logout
            LogHelper::registrar($conn, $usuario, 'Logout', 'Usuário saiu do sistema');

            // Encerrar sessão
            session_unset();
            session_destroy();
            exit; // importante para não executar o header de redirecionamento
        } else {
            echo "<p>Não há logs para gerar relatório.</p>";
        }
    }
}

// Se escolheu não fechar o dia, apenas logout normal
LogHelper::registrar($conn, $usuario, 'Logout', 'Usuário saiu do sistema');
session_unset();
session_destroy();
header("Location: login.php");
exit;
