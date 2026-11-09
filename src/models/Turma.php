<?php
// src/models/Turma.php



class Turma
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Listar todas as turmas com nome da classe
    public function listarTurmas()
    {
        $sql = "SELECT t.*, 
               c.nome_classe AS nome_classe,
               tu.nome AS nome_turno
        FROM turmas t
        JOIN classe c ON t.classe_id = c.id_classe
        JOIN turnos tu ON t.turno_id = tu.id
        ORDER BY t.id_turma DESC";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar turma por ID
    public function getTurmaById($id)
    {
        $sql = "SELECT * FROM turmas WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cadastrar nova turma
    public function cadastrarTurma($nome, $classe_id, $turno, $ano_letivo, $status)
    {
        $sql = "INSERT INTO turmas (nome_turma, classe_id, turno_id, ano_letivo, status) 
                VALUES (:nome_turma, :classe_id, :turno, :ano_letivo, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome_turma', $nome);
        $stmt->bindParam(':classe_id', $classe_id);
        $stmt->bindParam(':turno', $turno);
        $stmt->bindParam(':ano_letivo', $ano_letivo);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Editar turma existente
    public function editarTurma($id, $nome, $classe_id, $turno, $ano_letivo, $status)
    {
        $sql = "UPDATE turmas 
                SET nome_turma = :nome_turma, classe_id = :classe_id, turno_id = :turno, ano_letivo = :ano_letivo, status = :status 
                WHERE id_turma = :id_turma";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome_turma', $nome);
        $stmt->bindParam(':classe_id', $classe_id);
        $stmt->bindParam(':turno', $turno);
        $stmt->bindParam(':ano_letivo', $ano_letivo);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id_turma', $id);
        return $stmt->execute();
    }

    // Deletar turma
    public function deletarTurma($id)
    {
        $sql = "DELETE FROM turmas WHERE id_turma = :id_turma";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_turma', $id);
        return $stmt->execute();
    }
}
