<?php
session_start();
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/../helpers/LogHelper.php';
require_once __DIR__ . '/../vendor/autoload.php'; // DOMPDF + PHPMailer

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$usuario = $_SESSION['nome'];
$dataHoje = date('Y-m-d');

// Função para registrar abertura do dia
function abrirSistema($conn, $usuario)
{
    require_once __DIR__ . '/../helpers/LogHelper.php';
    LogHelper::registrar($conn, $usuario, 'Abertura de Sistema', 'Usuário abriu a sessão do dia');
}

// Função para gerar PDF do relatório diário
function gerarPDF($logs, $usuario)
{
    // Definindo o cabeçalho e a data
    $html = "<h2 style='text-align:center;'>Relatório Diário - $usuario</h2>";
    $html .= "<p style='text-align:center;'>Data: " . date('d/m/Y') . "</p>";

    // Iniciando a tabela de logs
    $html .= "<table style='width:100%; border-collapse: collapse;'>";
    $html .= "<thead>
                <tr>
                    <th style='border: 1px solid #000; padding: 5px; text-align: center;'>Hora</th>
                    <th style='border: 1px solid #000; padding: 5px; text-align: center;'>Ação</th>
                    <th style='border: 1px solid #000; padding: 5px; text-align: center;'>Descrição</th>
                </tr>
              </thead>
              <tbody>";

    // Preenchendo a tabela com os dados dos logs
    foreach ($logs as $log) {
        $hora = date('H:i:s', strtotime($log['data_hora']));
        $acao = htmlspecialchars($log['acao']);
        $descricao = htmlspecialchars($log['descricao']);
        $ip = htmlspecialchars($log['ip']);
        $html .= "<tr>
                    <td style='border: 1px solid #000; padding: 5px; text-align: center;'>$hora</td>
                    <td style='border: 1px solid #000; padding: 5px; text-align: center;'>$acao</td>
                    <td style='border: 1px solid #000; padding: 5px; text-align: center;'>$descricao</td>
                  </tr>";
    }

    $html .= "</tbody></table>";

    // Adicionando a página final com o rodapé
    $html .= "<p style='text-align: center; margin-top: 20px;'>Relatório gerado automaticamente pelo sistema.</p>";

    // Configurações do Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);  // Habilitando HTML5
    $options->set('isPhpEnabled', true);          // Habilitando PHP dentro do HTML (opcional)

    // Gerando o PDF
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // A4 em orientação retrato
    $dompdf->render();

    // Definindo o nome e caminho do arquivo PDF gerado
    $filename = "relatorio_{$usuario}_" . date('Ymd') . ".pdf";
    $path = __DIR__ . "/../relatorios/$filename";
    file_put_contents($path, $dompdf->output());

    return $filename;
}


// Função para enviar PDF por email
function enviarEmail($arquivo, $usuario)
{
    $path = __DIR__ . "/../relatorios/$arquivo";
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.seudominio.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seuemail@dominio.com';
        $mail->Password = 'senha';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('seuemail@dominio.com', 'Sistema Escolar');
        $mail->addAddress('destino@dominio.com', $usuario);

        $mail->isHTML(true);
        $mail->Subject = "Relatório Diário - $usuario";
        $mail->Body    = "Segue em anexo o relatório diário de atividades do sistema.";
        $mail->addAttachment($path);

        $mail->send();

        global $conn;
        LogHelper::registrar($conn, $usuario, 'Envio de Relatório', "PDF $arquivo enviado por email");
        return true;
    } catch (Exception $e) {
        return "Erro ao enviar email: {$mail->ErrorInfo}";
    }
}

// Checar se usuário já abriu o dia
$stmt = $conn->prepare("
    SELECT * FROM logs 
    WHERE usuario = ? AND acao = 'Abertura de Sistema' AND DATE(data_hora) = ?
");
$stmt->execute([$usuario, $dataHoje]);
$aberto = $stmt->fetch();

if (!$aberto) {
    abrirSistema($conn, $usuario);
    echo "<p>Sistema aberto para o dia. Todas ações serão registradas.</p>";
}

// Botão para fechar o dia
if (isset($_POST['fechar_dia'])) {
    // Buscar logs do usuário do dia
    $stmt = $conn->prepare("
        SELECT * FROM logs
        WHERE usuario = ? AND DATE(data_hora) = ?
        ORDER BY data_hora ASC
    ");
    $stmt->execute([$usuario, $dataHoje]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($logs) {
        $arquivo = gerarPDF($logs, $usuario);
        LogHelper::registrar($conn, $usuario, 'Fecho de Sistema', "Relatório do dia gerado: $arquivo");

        echo "<h3>Fecho realizado com sucesso!</h3>";
        echo "<p><a href='../relatorios/$arquivo' target='_blank'>Baixar PDF</a></p>";
        echo "<form method='POST'>
                <input type='hidden' name='arquivo' value='$arquivo'>
                <button type='submit' name='enviar_email'>Enviar por Email</button>
              </form>";
    } else {
        echo "<p>Não há registros para gerar relatório.</p>";
    }
}

// Botão enviar email
if (isset($_POST['enviar_email'])) {
    $arquivo = $_POST['arquivo'];
    $resultado = enviarEmail($arquivo, $usuario);
    if ($resultado === true) {
        echo "<p>Email enviado com sucesso!</p>";
    } else {
        echo "<p>$resultado</p>";
    }
}
?>

<!-- Formulário simples -->
<form method="POST">
    <button type="submit" name="fechar_dia">Fechar Dia</button>
</form>