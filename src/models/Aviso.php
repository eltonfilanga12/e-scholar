<?php
// models/Aviso.php
require_once __DIR__ . '/../../config/database.php';

class Aviso
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Atualiza todos os avisos expirados no banco
    public function atualizarExpirados()
    {
        // Expira os avisos vencidos
        $sql1 = "UPDATE avisos 
             SET status = 'Expirado' 
             WHERE data_validade IS NOT NULL 
               AND data_validade < NOW() 
               AND status != 'Expirado'";
        $this->conn->query($sql1);

        // Reativa avisos que estavam expirados mas tiveram validade estendida
        $sql2 = "UPDATE avisos 
             SET status = 'Ativo' 
             WHERE (data_validade IS NULL OR data_validade >= NOW()) 
               AND status != 'Ativo'";
        $this->conn->query($sql2);
    }


    public function listar()
    {
        $sql = "SELECT * 
            FROM avisos
            ORDER BY data_publicacao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function salvar($dados)
    {
        if (!empty($dados['id'])) {
            $sql = "UPDATE avisos 
                    SET conteudo = :conteudo, data_validade = :data_validade, tipo_usuario = :tipo_usuario 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                'conteudo' => $dados['conteudo'],
                'data_validade' => $dados['data_validade'] ?: null,
                'tipo_usuario' => $dados['tipo_usuario'],
                'id' => $dados['id']
            ]);
        } else {
            $sql = "INSERT INTO avisos (conteudo, data_validade, tipo_usuario, status) 
                    VALUES (:conteudo, :data_validade, :tipo_usuario, 'Ativo')";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                'conteudo' => $dados['conteudo'],
                'data_validade' => $dados['data_validade'] ?: null,
                'tipo_usuario' => $dados['tipo_usuario']
            ]);
        }
    }

    public function deletar($id)
    {
        $sql = "DELETE FROM avisos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function listarPorUsuario($tipoUsuario)
    {
        $sql = "SELECT * 
            FROM avisos
            WHERE tipo_usuario = :tipoUsuario OR tipo_usuario = 'Todos'
            ORDER BY data_publicacao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['tipoUsuario' => $tipoUsuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
