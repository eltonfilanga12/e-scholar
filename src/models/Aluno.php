<?php
// /src/models/Student.php
require_once __DIR__ . '../../../config/database.php';

class Student
{
    private $conn;
    private $table = 'aluno'; // Nome da tabela de alunos

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllStudents()
    {
        $query = "SELECT 
            a.id, 
            a.id_usuario, 
            a.nome_completo, 
            a.data_nascimento,
            c.nome_classe AS nome_classe,
            a.data_inscricao, 
            t.nome_turma AS turma_nome
          FROM " . $this->table . " a
          LEFT JOIN turmas t ON a.turma_id = t.id_turma
          LEFT JOIN classe c ON a.classe_id = c.id_classe";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // /src/models/Student.php
    public function getStudentById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTurmas()
    {
        $query = "SELECT id, nome FROM turma";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteStudent($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateStudent($id, $nome_completo, $data_nascimento, $classe, $turma_id, $data_inscricao, $nome_pai, $profissao_pai, $contacto_pai, $nome_mae, $profissao_mae, $contacto_mae, $nome_encarregado, $profissao_encarregado, $contacto_encarregado, $email_encarregado)
    {
        $query = "UPDATE " . $this->table . " a
              JOIN pais p ON a.id = p.aluno_id
              SET a.nome_completo = :nome_completo, a.data_nascimento = :data_nascimento, a.classe_id = :classe, a.turma_id = :turma_id, a.data_inscricao = :data_inscricao,
                  p.nome_pai = :nome_pai, p.profissao_pai = :profissao_pai, p.contacto_pai = :contacto_pai,
                  p.nome_mae = :nome_mae, p.profissao_mae = :profissao_mae, p.contacto_mae = :contacto_mae,
                  p.nome_encarregado = :nome_encarregado, p.profissao_encarregado = :profissao_encarregado, p.contacto_encarregado = :contacto_encarregado, p.email_encarregado = :email_encarregado
              WHERE a.id = :id";
        $stmt = $this->conn->prepare($query);

        // Bind dos valores
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome_completo', $nome_completo);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':classe', $classe);
        $stmt->bindParam(':turma_id', $turma_id);
        $stmt->bindParam(':data_inscricao', $data_inscricao);
        $stmt->bindParam(':nome_pai', $nome_pai);
        $stmt->bindParam(':profissao_pai', $profissao_pai);
        $stmt->bindParam(':contacto_pai', $contacto_pai);
        $stmt->bindParam(':nome_mae', $nome_mae);
        $stmt->bindParam(':profissao_mae', $profissao_mae);
        $stmt->bindParam(':contacto_mae', $contacto_mae);
        $stmt->bindParam(':nome_encarregado', $nome_encarregado);
        $stmt->bindParam(':profissao_encarregado', $profissao_encarregado);
        $stmt->bindParam(':contacto_encarregado', $contacto_encarregado);
        $stmt->bindParam(':email_encarregado', $email_encarregado);

        // Executar a query
        return $stmt->execute();
    }

    public function findByName($nome)
    {
        $query = "SELECT id, nome_completo, classe_id FROM " . $this->table . " WHERE nome_completo = :nome";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Listar alunos por classe e ano, ordenados alfabeticamente
    // Listar alunos por classe e ano letivo
    public function listarPorClasseEAno($classe_id, $ano_lectivo)
    {
        $stmt = $this->conn->prepare("SELECT id, nome_completo, turma_id FROM aluno WHERE classe_id = ? AND ano_lectivo = ? ORDER BY nome_completo ASC");
        $stmt->execute([$classe_id, $ano_lectivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar todas as classes
    public function listarClasses()
    {
        $stmt = $this->conn->prepare("SELECT id_classe AS id, nome_classe AS nome FROM classe ORDER BY nome_classe ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Listar turmas de uma classe
    public function listarTurmasPorClasse($classe_id)
    {
        $stmt = $this->conn->prepare("SELECT id, nome FROM turma WHERE classe_id = ?");
        $stmt->execute([$classe_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Atualizar turma de um aluno
    public function atualizarTurma($aluno_id, $turma_id)
    {
        $stmt = $this->conn->prepare("UPDATE aluno SET turma_id = ? WHERE id = ?");
        $stmt->execute([$turma_id, $aluno_id]);
    }

    // Distribuição automática equilibrada por ordem alfabética
    public function distribuirAlunosEquilibrado($alunos, $turmas)
    {
        $resultado = [];
        $i = 0;
        foreach ($alunos as $aluno) {
            $turma = $turmas[$i % count($turmas)];
            $resultado[] = [
                'aluno_id' => $aluno['id'],
                'turma_id' => $turma['id'],
                'nome' => $aluno['nome_completo']
            ];
            $i++;
        }
        return $resultado;
    }
}
