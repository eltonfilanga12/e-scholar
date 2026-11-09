<?php

class Sala
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function listar()
    {
        $sql = "SELECT * FROM salas ORDER BY nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function salvar($dados)
    {
        if (isset($dados['id']) && !empty($dados['id'])) {
            $sql = "UPDATE salas SET nome = :nome, capacidade = :capacidade, estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $dados['id']);
        } else {
            $sql = "INSERT INTO salas (nome, capacidade, estado) VALUES (:nome, :capacidade, :estado)";
            $stmt = $this->conn->prepare($sql);
        }

        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':capacidade', $dados['capacidade']);
        $stmt->bindParam(':estado', $dados['estado']);

        return $stmt->execute();
    }

    public function deletar($id)
    {
        $sql = "DELETE FROM salas WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
