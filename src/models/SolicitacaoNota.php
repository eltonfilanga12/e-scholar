<?php
// models/SolicitacaoNota.php
require_once __DIR__ . '../../../config/database.php';

class SolicitacaoNota
{
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function listarTodas()
    {
        $sql = "
            SELECT sn.id, sn.campo_nota, sn.motivo, sn.status, sn.data_pedido,
                   f.nome AS professor,
                   a.nome_completo AS aluno,
                   d.nome AS disciplina
            FROM solicitacoes_nota sn
            INNER JOIN professores p ON sn.professor_id = p.id_professor
            INNER JOIN funcionarios f ON p.funcionario_id = f.id
            INNER JOIN aluno a ON sn.aluno_id = a.id
            INNER JOIN disciplinas d ON sn.disciplina_id = d.id
            ORDER BY sn.data_pedido DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes_nota WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE solicitacoes_nota SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function criar($professor_id, $aluno_id, $disciplina_id, $campo_nota, $motivo)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO solicitacoes_nota 
                (professor_id, aluno_id, disciplina_id, campo_nota, motivo, status)
            VALUES (?, ?, ?, ?, ?, 'pendente')
        ");
        return $stmt->execute([$professor_id, $aluno_id, $disciplina_id, $campo_nota, $motivo]);
    }

    public function existeSolicitacaoAprovadaAtiva($aluno_id, $disciplina_id, $campo_nota)
    {
        $sql = "SELECT COUNT(*) FROM solicitacoes_nota
                WHERE aluno_id = ? 
                  AND disciplina_id = ? 
                  AND campo_nota = ?
                  AND status = 'aprovado'
                  AND usado = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$aluno_id, $disciplina_id, $campo_nota]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Marca a solicitação como usada (depois que o professor altera a nota)
     */
    public function marcarComoUsada($aluno_id, $disciplina_id, $campo_nota)
    {
        $sql = "UPDATE solicitacoes_nota
                SET usado = 1
                WHERE aluno_id = ? AND disciplina_id = ? AND campo_nota = ?
                  AND status = 'aprovado' AND usado = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$aluno_id, $disciplina_id, $campo_nota]);
    }
}
