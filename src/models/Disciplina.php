<?php
require_once __DIR__ . '../../../config/database.php';

class Disciplina {
    private $conn;
    private $table = "disciplinas";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listar() {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function criar($nome, $codigo, $status) {
        $query = "INSERT INTO {$this->table} (nome, codigo, status) VALUES (:nome, :codigo, :status)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':nome' => $nome,
            ':codigo' => $codigo,
            ':status' => $status
        ]);
    }

    public function deletar($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // Adicione ao final da classe Disciplina

    public function buscarPorId($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $codigo, $status)
    {
        $query = "UPDATE {$this->table} SET nome = :nome, codigo = :codigo, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':nome' => $nome,
            ':codigo' => $codigo,
            ':status' => $status,
            ':id' => $id
        ]);
    }

    public function buscarDisciplinas() {
        $stmt = $this->conn->query("SELECT id, nome FROM disciplinas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Adicione métodos de edição, busca, etc., conforme necessário
}
