<?php
require_once __DIR__ . '/../models/Disciplina.php';
class DisciplinaController {
    private $conn;

    public function __construct() {
        // Incluir a configuração do banco de dados
        require_once __DIR__ . '../../../config/database.php';
        $this->conn = (new Database())->getConnection();
    }

    // Método para buscar todas as disciplinas
    public function buscarTodasDisciplinas() {
        $query = "SELECT id, nome_disciplina FROM disciplinas";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        // Retornar as disciplinas como um array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarDisciplinas() {
        $notaModel = new Disciplina();
        return $notaModel->buscarDisciplinas();
    }
}
