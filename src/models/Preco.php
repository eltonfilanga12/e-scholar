<?php
require_once __DIR__ . '../../../config/database.php';

class Preco
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listar()
    {
        $sql = "SELECT p.*, c.nome_classe 
                FROM precos p
                JOIN classe c ON p.classe_id = c.id_classe";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function salvar($dados)
    {
        $sql = "INSERT INTO precos (classe_id, inscricao, mensalidade, camisa, camiseta, calcoes, meia) 
                VALUES (:classe_id, :inscricao, :mensalidade, :camisa, :camiseta, :calcoes, :meia)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($dados);
    }

    public function editar($dados)
    {
        $sql = "UPDATE precos SET 
                    classe_id = :classe_id,
                    inscricao = :inscricao,
                    mensalidade = :mensalidade,
                    camisa = :camisa,
                    camiseta = :camiseta,
                    calcoes = :calcoes,
                    meia = :meia
                WHERE id_precos = :id_precos";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($dados);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM precos WHERE id_precos = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deletar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM precos WHERE id_precos = ?");
        return $stmt->execute([$id]);
    }
}
