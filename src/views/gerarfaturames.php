<?php
// Inclui a classe Database
require_once '../../config/database.php'; // Ajuste o caminho conforme a sua estrutura

// Instancia e obtém conexão
$database = new Database();
$conn = $database->getConnection();

// Verifica se é dia 25
//if (date('d') != '22') {
//  exit("Hoje não é dia 25.\n");
//}

$mesAtual = date('m');
$anoAtual = date('Y');
$numeroMes = (int)date('m'); // Mês como número (1 a 12)
$prazo = date('Y-m-05', strtotime('+1 month')); // Dia 05 do mês seguinte

// Busca os alunos e classes
$sql = "SELECT a.id, a.classe_id 
        FROM aluno a
        JOIN classe c ON a.classe_id = c.id_classe";
$stmt = $conn->prepare($sql);
$stmt->execute();
$alunos = $stmt->fetchAll();

foreach ($alunos as $row) {
    $id_aluno = $row['id'];
    $classe_id = $row['classe_id'];
    $abreviatura = 'FPM';
    $custo = '1900';
    $entidade = '30400';
    $referencia = uniqid();

    // Insere fatura
    $inserir = "INSERT INTO propinas 
        (id_aluno, abreviatura, mes, classe_id, custo, entidade, referencia, prazo_doc, situacao)
        VALUES (:id_aluno, :abreviatura, :mes, :classe_id, :custo, :entidade, :referencia, :prazo, 'Não Paga')";

    $stmtInsert = $conn->prepare($inserir);
    $stmtInsert->execute([
        ':id_aluno' => $id_aluno,
        ':abreviatura' => $abreviatura,
        ':mes' => $numeroMes,
        ':classe_id' => $classe_id,
        ':custo' => $custo,
        ':entidade' => $entidade,
        ':referencia' => $referencia,
        ':prazo' => $prazo,
    ]);
}

//echo "Faturas geradas com sucesso para $numeroMes/$anoAtual.\n";
header("Location: faturaAluno.php?status=sucesso");
exit;