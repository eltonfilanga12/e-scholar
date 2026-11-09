<?php
require_once __DIR__ . '/../../config/database.php';

class Pagamento
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    // Listar pagamentos
    public function listarPagamentos()
    {
        $sql = "SELECT 
                    p.id_pagamento,
                    p.data_pagamento,
                    p.valor_pago,
                    p.forma_pagamento,
                    p.situacao,
                    a.nome_completo AS aluno_nome,
                    pr.referencia
                FROM pagamentos p
                INNER JOIN propinas pr ON pr.id_propinas = p.id_propina
                INNER JOIN aluno a ON a.id = p.id_aluno
                ORDER BY p.data_pagamento DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salvar pagamento
    public function salvarPagamento($dados)
    {
        $this->conn->beginTransaction();

        try {
            // 1. Registrar pagamento
            $sql = "INSERT INTO pagamentos (id_propina, id_aluno, data_pagamento, valor_pago, forma_pagamento, situacao) 
                VALUES (:id_propina, :id_aluno, :data_pagamento, :valor_pago, :forma_pagamento, :situacao)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':id_propina' => $dados['id_propina'],
                ':id_aluno' => $dados['id_aluno'],
                ':data_pagamento' => $dados['data_pagamento'],
                ':valor_pago' => $dados['valor_pago'],
                ':forma_pagamento' => $dados['forma_pagamento'],
                ':situacao' => $dados['situacao']
            ]);

            // 2. Atualizar situação na tabela propinas
            $sqlUpdate = "UPDATE propinas SET situacao = :situacao WHERE id_propinas = :id_propina";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':situacao' => $dados['situacao'],
                ':id_propina' => $dados['id_propina']
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
