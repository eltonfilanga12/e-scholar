<?php
// /classes/DistribuidorTurmas.php

class DistribuidorTurmas {
    private $pdo;
    private $limitePorTurma = 30;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function encontrarMelhorTurma(int $classe_id, int $turno_id, string $nomeAluno): ?int {
        $turmas = $this->obterTurmasDisponiveis($classe_id, $turno_id);
        
        if (empty($turmas)) {
            return null;
        }

        // Ordenar por turmas com menos alunos
        usort($turmas, function($a, $b) {
            return $a['quantidade_alunos'] <=> $b['quantidade_alunos'];
        });

        // Se empate, distribuir alfabeticamente
        if (count($turmas) > 1 && $turmas[0]['quantidade_alunos'] == $turmas[1]['quantidade_alunos']) {
            return $this->escolherTurmaPorOrdemAlfabetica($turmas, $nomeAluno);
        }

        return $turmas[0]['id_turma'];
    }

    private function obterTurmasDisponiveis(int $classe_id, int $turno_id): array {
        $anoAtual = date('Y');
        
        $sql = "SELECT t.id_turma, t.nome_turma, 
                (SELECT COUNT(*) FROM alunos WHERE turma_id = t.id_turma) as quantidade_alunos
                FROM turmas t
                WHERE t.classe_id = :classe_id 
                AND t.turno_id = :turno_id
                AND t.ano_letivo = :ano_letivo
                AND t.status = 'Ativa'
                AND (SELECT COUNT(*) FROM alunos WHERE turma_id = t.id_turma) < :limite
                ORDER BY quantidade_alunos ASC, t.nome_turma ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':classe_id' => $classe_id,
            ':turno_id' => $turno_id,
            ':ano_letivo' => $anoAtual,
            ':limite' => $this->limitePorTurma
        ]);
        
        return $stmt->fetchAll();
    }

    private function escolherTurmaPorOrdemAlfabetica(array $turmas, string $nomeAluno): int {
        $primeiraLetra = strtoupper(substr($nomeAluno, 0, 1));
        $codigoLetra = ord($primeiraLetra) - ord('A');
        return $turmas[$codigoLetra % count($turmas)]['id_turma'];
    }

    public function distribuirAlunosNaoAlocados(): array {
        $anoAtual = date('Y');
        
        // Obter alunos não alocados
        $sqlAlunos = "SELECT a.id, a.nome_completo, a.classe_id, a.turno_id 
                     FROM alunos a
                     WHERE a.turma_id IS NULL
                     ORDER BY a.nome_completo ASC";
        
        $alunos = $this->pdo->query($sqlAlunos)->fetchAll();

        $resultados = [];
        
        foreach ($alunos as $aluno) {
            $turmaId = $this->encontrarMelhorTurma(
                $aluno['classe_id'],
                $aluno['turno_id'],
                $aluno['nome_completo']
            );

            if ($turmaId) {
                $stmt = $this->pdo->prepare("UPDATE alunos SET turma_id = :turma_id WHERE id = :aluno_id");
                $stmt->execute([':turma_id' => $turmaId, ':aluno_id' => $aluno['id']]);
                
                $resultados[] = [
                    'aluno_id' => $aluno['id'],
                    'turma_id' => $turmaId,
                    'status' => 'alocado'
                ];
            } else {
                $resultados[] = [
                    'aluno_id' => $aluno['id'],
                    'status' => 'sem_turma_disponivel'
                ];
            }
        }
        
        return $resultados;
    }
}