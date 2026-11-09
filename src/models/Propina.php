<?php
require_once __DIR__ . '../../../config/database.php';

class Propina
{
  private $conn;

  public function __construct()
  {
    // Conectar ao banco de dados
    $database = new Database();
    $this->conn = $database->getConnection();
  }

  public function verificarPagamentos($nomeAluno)
  {
    // Consulta para verificar se há propinas não pagas
    $query = "SELECT COUNT(*) as count 
            FROM propinas p
            JOIN aluno a ON p.id_aluno = a.id
            WHERE a.nome_completo = ? 
            AND p.situacao = 'Não Paga';
            ";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $nomeAluno);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] == 0;
  }

  public function getAllPropinas()
  {
    $query = "SELECT 
                    p.id_propinas,
                    a.nome_completo AS nome_aluno,
                    c.nome_classe AS classe,
                    p.mes,
                    p.custo,
                    p.entidade,
                    p.referencia,
                    p.situacao,
                    p.prazo_doc
                  FROM 
                    propinas p
                  JOIN 
                    aluno a ON p.id_aluno = a.id
                  JOIN 
                    classe c ON p.classe_id = c.id_classe";

    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function create($idAluno, $classe, $tipoFatura, $serieAno, $mes, $custo, $entidade, $referencia, $prazoDoc)
  {
    $query = "INSERT INTO propinas (id_aluno, abreviatura, mes, classe_id, custo, entidade, referencia, prazo_doc, situacao) 
                VALUES (:id_aluno, :abreviatura, :mes, :classe_id, :custo, :entidade, :referencia, :prazo_doc, 'Não Paga')";

    $stmt = $this->conn->prepare($query);
    $abreviatura = substr($tipoFatura, 0, 1) . $serieAno[0]; // Exemplo de abreviatura

    $stmt->bindParam(':id_aluno', $idAluno);
    $stmt->bindParam(':abreviatura', $abreviatura);
    $stmt->bindParam(':mes', $mes);
    $stmt->bindParam(':classe_id', $classe);
    $stmt->bindParam(':custo', $custo);
    $stmt->bindParam(':entidade', $entidade);
    $stmt->bindParam(':referencia', $referencia);
    $stmt->bindParam(':prazo_doc', $prazoDoc);

    return $stmt->execute();
  }

  public function getPropinasByAluno($idAluno)
  {
    $query = "SELECT 
                p.id_propinas,
                a.nome_completo AS nome_aluno,
                c.nome_classe AS classe,
                p.mes,
                p.custo,
                p.entidade,
                p.referencia,
                p.situacao,
                p.prazo_doc
              FROM 
                propinas p
              JOIN 
                aluno a ON p.id_aluno = a.id
              JOIN 
                classe c ON p.classe_id = c.id_classe
              WHERE p.id_aluno = :id_aluno
              ORDER BY p.prazo_doc DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_aluno', $idAluno, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPropinasByNomeAluno($nomeAluno)
  {
    $query = "SELECT 
                p.id_propinas,
                a.nome_completo AS nome_aluno,
                c.nome_classe AS classe,
                p.mes,
                p.custo,
                p.entidade,
                p.referencia,
                p.situacao,
                p.prazo_doc
              FROM 
                propinas p
              JOIN 
                aluno a ON p.id_aluno = a.id
              JOIN 
                classe c ON p.classe_id = c.id_classe
              WHERE a.nome_completo = :nome_aluno
              ORDER BY p.prazo_doc DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':nome_aluno', $nomeAluno, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getFaturaById($id)
  {
    $stmt = $this->conn->prepare("
        SELECT 
            p.*, 
            a.nome_completo AS nome_aluno
        FROM 
            propinas p
        INNER JOIN 
            aluno a ON p.id_aluno = a.id
        WHERE 
            p.id_propinas = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
