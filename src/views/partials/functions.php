<?php
require_once __DIR__ . '/../../../config/database.php';
function getTrimestreAtualPDO($pdo, $ano_lectivo)
{
    $hoje = date('Y-m-d');

    $sql = "SELECT id_trimestre
            FROM trimestre
            WHERE data_inicio <= :hoje 
              AND data_fim >= :hoje
              AND ano_lectivo = :ano
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hoje' => $hoje,
        ':ano' => $ano_lectivo
    ]);

    $trimestre = $stmt->fetch(PDO::FETCH_ASSOC);

    return $trimestre ? (int)$trimestre['id_trimestre'] : null;
}


/**
 * Verifica se alguma nota já foi lançada para o aluno/disciplina/ano.
 * Retorna true somente se houver pelo menos uma nota **preenchida** (não nula ou vazia).
 */
function notaLancada($pdo, $aluno_id, $disciplina_id, $anoLectivo) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM nota 
        WHERE aluno_id = ? 
          AND disciplina_id = ? 
          AND ano_lectivo = ?
        LIMIT 1
    ");
    $stmt->execute([$aluno_id, $disciplina_id, $anoLectivo]);
    $nota = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$nota) return false;

    $camposNotas = ['teste1','teste2','teste3','trabalho1','trabalho2','trabalho3'];
    foreach ($camposNotas as $campo) {
        if (isset($nota[$campo]) && $nota[$campo] !== null && $nota[$campo] !== '') {
            return true;
        }
    }
    return false;
}





