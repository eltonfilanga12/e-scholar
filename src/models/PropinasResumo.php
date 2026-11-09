<?php
require_once __DIR__ . '../../../config/database.php';

class PropinasResumo
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Retorna resumo das propinas por aluno
     * - nome do aluno
     * - classe
     * - quantidade de propinas a dever
     * - quantidade de multas
     */
    public function getResumoPorAluno()
    {
        $query = "
            SELECT 
                a.id AS id_aluno,
                a.nome_completo AS nome_aluno,
                c.nome_classe AS classe,
                SUM(CASE WHEN p.situacao = 'Não Paga' THEN 1 ELSE 0 END) AS propinas_dever,
                SUM(CASE WHEN p.situacao = 'Não Paga' AND UPPER(p.abreviatura) LIKE '%MULTA%' THEN 1 ELSE 0 END) AS multas
            FROM propinas p
            INNER JOIN aluno a ON p.id_aluno = a.id
            INNER JOIN classe c ON p.classe_id = c.id_classe
            GROUP BY a.id, a.nome_completo, c.nome_classe
            ORDER BY a.nome_completo ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFaturasAluno($idAluno)
    {
        $sql = "SELECT 
                    p.*,
                    a.nome_completo AS nome_aluno,
                    c.nome_classe AS classe
                FROM propinas p
                JOIN aluno a ON p.id_aluno = a.id
                JOIN classe c ON p.classe_id = c.id_classe
                WHERE p.id_aluno = :id_aluno
                ORDER BY p.prazo_doc DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_aluno', $idAluno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
