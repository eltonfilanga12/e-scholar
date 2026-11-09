<?php
// models/DashboardModel.php

require_once __DIR__ . '../../../config/database.php';

class DashboardModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getTotalAlunos()
    {
        $sql = "SELECT COUNT(*) AS total FROM aluno";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    public function getTotalProfessores()
    {
        $sql = "SELECT COUNT(*) AS total FROM professores";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    public function getTotalDisciplinas()
    {
        $sql = "SELECT COUNT(*) AS total FROM disciplinas";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    public function getTotalFuncionarios()
    {
        $sql = "SELECT COUNT(*) AS total FROM funcionarios";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    public function getPropinasArrecadadasPorMes($ano)
    {
        $sql = "SELECT mes, 
                   SUM(custo) AS total_arrecadado 
            FROM propinas 
            WHERE situacao = 'Paga' AND YEAR(prazo_doc) = ? 
            GROUP BY mes 
            ORDER BY FIELD(mes, 'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFrequenciaAlunosPorMes($ano)
    {
        $sql = "SELECT 
                MONTH(data) AS mes_num, 
                COUNT(*) AS presencas 
            FROM frequencias 
            WHERE presenca = 1 AND YEAR(data) = ? 
            GROUP BY mes_num
            ORDER BY mes_num";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ano]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarLogs()
    {
        $stmt = $this->conn->prepare("SELECT * FROM logs ORDER BY data_hora DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstatisticasPresencas()
    {
        $sql = "SELECT
                COUNT(*) AS total_aulas,
                SUM(CASE WHEN presenca = 1 THEN 1 ELSE 0 END) AS presencas,
                SUM(CASE WHEN presenca = 0 THEN 1 ELSE 0 END) AS faltas
            FROM frequencias";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalSalas()
    {
        $sql = "SELECT COUNT(*) AS total FROM salas";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    function getPropinasEmAtraso()
    {
        $sql = "SELECT SUM(custo) as total_atraso FROM propinas WHERE situacao = 'Não Paga'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_atraso'] ?? 0;
    }

    // Buscar todos os avisos
    public function getAvisos()
    {
        $sql = "SELECT * FROM avisos WHERE status = 'Ativo' ORDER BY data_publicacao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar tarefas administrativas com o nome do funcionário responsável
    public function getTarefasAdministrativas()
    {
        $sql = "SELECT 
                    t.id, 
                    t.titulo, 
                    t.status, 
                    t.prazo, 
                    f.nome AS responsavel
                FROM tarefas_administrativas t
                LEFT JOIN funcionarios f ON t.funcionario_id  = f.id
                ORDER BY t.prazo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
