<?php
// src/models/Classe.php

require_once __DIR__ . '../../../config/database.php';

class Classe
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Listar todas as classes
    public function listarClasses()
    {
        $sql = "SELECT * FROM classe ORDER BY nome_classe ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar uma classe pelo ID
    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM classe WHERE id_classe = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Criar nova classe
    public function criar($nome, $status, $descricao)
    {
        $sql = "INSERT INTO classe (nome_classe, status, descricao) VALUES (:nome, :status, :descricao)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':descricao', $descricao);
        return $stmt->execute();
    }

    // Atualizar classe
    public function atualizar($id, $nome, $status, $descricao)
    {
        $sql = "UPDATE classe SET nome_classe = :nome, status = :status, descricao = :descricao WHERE id_classe = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Excluir classe
    public function deletar($id)
    {
        $sql = "DELETE FROM classe WHERE id_classe = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
