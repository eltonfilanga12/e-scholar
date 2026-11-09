<?php
require_once __DIR__ . '/../models/PropinasResumo.php';
require_once __DIR__ . '/../../config/database.php';

class PropinasResumoController
{
    private $model;
    private $pdo;

    public function __construct()
    {
        $this->model = new PropinasResumo();

        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Resumo geral usado na página principal
     */
    public function getResumoPorAluno()
    {
        return $this->model->getResumoPorAluno();
    }

    /**
     * Retorna dados completos do aluno + faturas
     * (nome, classe, turma, e todas as faturas)
     */
    public function getDetalhesAluno($idAluno)
    {
        // 🔍 Buscar dados do aluno, classe e turma
        $sql = "
            SELECT 
                a.nome_completo AS nome_aluno,
                c.nome_classe AS classe,
                t.nome_turma AS turma
            FROM aluno a
            LEFT JOIN classe c ON a.classe_id = c.id_classe
            LEFT JOIN turmas t ON a.turma_id = t.id_turma
            WHERE a.id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAluno]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$aluno) {
            return null;
        }

        // 🔍 Buscar faturas do aluno
        $faturas = $this->model->getFaturasAluno($idAluno);

        // 🔄 Montar resumo final
        $resumo = [
            'nome_aluno'     => $aluno['nome_aluno'],
            'classe'         => $aluno['classe'],
            'turma'          => $aluno['turma'],
            'propinas_dever' => 0,
            'multas'         => 0,
            'faturas'        => $faturas
        ];

        foreach ($faturas as $f) {
            if ($f['situacao'] === 'Não Paga') {
                $resumo['propinas_dever']++;

                if (str_contains(strtoupper($f['abreviatura']), 'MULTA')) {
                    $resumo['multas']++;
                }
            }
        }

        return $resumo;
    }
}
