<?php
require_once __DIR__ . '../../../config/database.php';
class Horario {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function listar() {
        $stmt = $this->pdo->query("
            SELECT h.*, t.nome as turno 
            FROM horarios h
            JOIN turnos t ON h.turno_id = t.id
            ORDER BY t.nome, h.tempo
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTurnos() {
        $stmt = $this->pdo->query("SELECT * FROM turnos ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($dados) {
        if (!empty($dados['id'])) {
            $sql = "UPDATE horarios SET turno_id=?, tempo=?, hora_inicio=?, hora_fim=? WHERE id=?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$dados['turno_id'], $dados['tempo'], $dados['hora_inicio'], $dados['hora_fim'], $dados['id']]);
        } else {
            $sql = "INSERT INTO horarios (turno_id, tempo, hora_inicio, hora_fim) VALUES (?,?,?,?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$dados['turno_id'], $dados['tempo'], $dados['hora_inicio'], $dados['hora_fim']]);
        }
    }

    public function deletar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM horarios WHERE id=?");
        return $stmt->execute([$id]);
    }
}
