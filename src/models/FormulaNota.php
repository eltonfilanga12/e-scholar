<?php
require_once __DIR__ . '../../../config/database.php';

class FormulaNota
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Listar todas as fórmulas
    public function getAll()
    {
        $sql = "SELECT f.*, n.nome AS nivel_nome 
                FROM formulas_notas f
                LEFT JOIN niveis n ON f.nivel_id = n.id
                ORDER BY f.nome_formula ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar fórmula por ID
    public function getById($id)
    {
        $sql = "SELECT * FROM formulas_notas WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Criar nova fórmula
    public function create($nome, $nivel_id, $num_testes, $peso_testes, $num_trabalhos, $peso_trabalhos)
    {
        $sql = "INSERT INTO formulas_notas (nome_formula, nivel_id, num_testes, peso_testes, num_trabalhos, peso_trabalhos)
                VALUES (:nome, :nivel_id, :num_testes, :peso_testes, :num_trabalhos, :peso_trabalhos)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':nivel_id', $nivel_id, PDO::PARAM_INT);
        $stmt->bindParam(':num_testes', $num_testes, PDO::PARAM_INT);
        $stmt->bindParam(':peso_testes', $peso_testes, PDO::PARAM_INT);
        $stmt->bindParam(':num_trabalhos', $num_trabalhos, PDO::PARAM_INT);
        $stmt->bindParam(':peso_trabalhos', $peso_trabalhos, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Atualizar fórmula
    public function update($id, $nome, $nivel_id, $num_testes, $peso_testes, $num_trabalhos, $peso_trabalhos)
    {
        $sql = "UPDATE formulas_notas
                SET nome_formula = :nome,
                    nivel_id = :nivel_id,
                    num_testes = :num_testes,
                    peso_testes = :peso_testes,
                    num_trabalhos = :num_trabalhos,
                    peso_trabalhos = :peso_trabalhos
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':nivel_id', $nivel_id, PDO::PARAM_INT);
        $stmt->bindParam(':num_testes', $num_testes, PDO::PARAM_INT);
        $stmt->bindParam(':peso_testes', $peso_testes, PDO::PARAM_INT);
        $stmt->bindParam(':num_trabalhos', $num_trabalhos, PDO::PARAM_INT);
        $stmt->bindParam(':peso_trabalhos', $peso_trabalhos, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Excluir fórmula
    public function delete($id)
    {
        $sql = "DELETE FROM formulas_notas WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
