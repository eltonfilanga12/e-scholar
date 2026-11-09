<?php
require_once __DIR__ . '../../../config/database.php';

class TarefaAdministrativa
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listar()
    {
        $sql = "SELECT t.*, f.nome FROM tarefas_administrativas t
                LEFT JOIN funcionarios f ON t.funcionario_id = f.id
                ORDER BY t.prazo ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar($titulo, $descricao, $funcionario_id, $status, $prazo)
    {
        $sql = "INSERT INTO tarefas_administrativas (titulo, descricao, funcionario_id, status, prazo)
                VALUES (:titulo, :descricao, :funcionario_id, :status, :prazo)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(compact('titulo', 'descricao', 'funcionario_id', 'status', 'prazo'));
    }

    public function atualizar($id, $titulo, $descricao, $funcionario_id, $status, $prazo)
    {
        $sql = "UPDATE tarefas_administrativas 
                SET titulo = :titulo, descricao = :descricao, funcionario_id = :funcionario_id,
                    status = :status, prazo = :prazo 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(compact('id', 'titulo', 'descricao', 'funcionario_id', 'status', 'prazo'));
    }

    public function deletar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM tarefas_administrativas WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tarefas_administrativas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
