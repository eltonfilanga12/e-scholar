<?php
require_once __DIR__ . '../../../config/database.php';

class Nivel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Listar todos os níveis (Primário, Secundário, etc.)
    public function getAll()
    {
        $sql = "SELECT * FROM niveis ORDER BY nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar nível por ID
    public function getById($id)
    {
        $sql = "SELECT * FROM niveis WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Criar novo nível
    public function create($nome)
    {
        $sql = "INSERT INTO niveis (nome) VALUES (:nome)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        return $stmt->execute();
    }

    // Atualizar nível
    public function update($id, $nome)
    {
        $sql = "UPDATE niveis SET nome = :nome WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Excluir nível
    public function delete($id)
    {
        $sql = "DELETE FROM niveis WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
