<?php
// /src/models/Nota.php
require_once __DIR__ . '../../../config/database.php';

class Nota
{
    private $conn;
    private $table = 'notas';

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function buscarNotasPorUsuario($usuario)
    {
        // Consulta SQL para buscar notas e nome da disciplina
        $sql = "SELECT notas.*, disciplinas.nome_disciplina 
                FROM notas 
                INNER JOIN disciplinas ON notas.disciplina_id = disciplinas.id
                WHERE notas.estudante_id = (SELECT id FROM usuarios WHERE nome = ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario]);

        return $stmt->fetchAll();
    }

    // Função para atualizar as notas no banco de dados
    public function atualizarNota($id, $campo, $valor)
    {
        $sql = "UPDATE notas SET $campo = :valor WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':id', $id);

        return $stmt->execute(); // Retorna true se a atualização foi bem-sucedida
    }

    public function buscarNotasPorDisciplina($disciplina_id)
    {
        $sql = "SELECT notas.*, aluno.nome_completo, aluno.turma_id, aluno.classe 
            FROM notas 
            INNER JOIN aluno ON notas.estudante_id = aluno.id
            WHERE notas.disciplina_id = :disciplina_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':disciplina_id', $disciplina_id);
        if (!$stmt->execute()) {
            error_log("Erro ao executar a consulta: " . implode(" ", $stmt->errorInfo()));
        }

        return $stmt->fetchAll();
    }

    public function getNotasPorAlunoEDisciplina($studentId, $disciplineId)
    {
        $sql = "SELECT * FROM notas WHERE estudante_id = :student_id AND disciplina_id = :discipline_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':discipline_id', $disciplineId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarNotas($studentId, $disciplineId, $teste1, $teste2, $teste3)
    {
        try {
            $sql = "UPDATE notas 
                    SET teste1 = :teste1, teste2 = :teste2, teste3 = :teste3 
                    WHERE estudante_id = :student_id AND disciplina_id = :discipline_id";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':teste1', $teste1, PDO::PARAM_INT);
            $stmt->bindParam(':teste2', $teste2, PDO::PARAM_INT);
            $stmt->bindParam(':teste3', $teste3, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':discipline_id', $disciplineId, PDO::PARAM_INT);

            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar nota: " . $e->getMessage());
            return false;
        }
    }


    public function buscarAlunosPorClasseETurma($classeId, $turmaId)
    {
        $sql = "SELECT a.id, a.nome_completo, a.turma_id, c.nome_classe AS classe, 
                   n.teste1, n.teste2, n.teste3
            FROM aluno a
            JOIN classe c ON a.classe_id = c.id_classe
            JOIN nota n ON n.aluno_id = a.id
            WHERE a.classe_id = :classe_id AND a.turma_id = :turma_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':classe_id', $classeId);
        $stmt->bindParam(':turma_id', $turmaId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarNotasPorDisciplinaETurmaEClasse($disciplinaId, $turmaId, $classeId)
    {
        // Recupera o nome do professor logado a partir da sessão
        if (!isset($_SESSION['nome'])) {
            return []; // Evita erro caso a sessão não esteja definida
        }

        $nomeProfessor = $_SESSION['nome'];

        // Verifica se o professor leciona na combinação informada
        $stmtVerifica = $this->conn->prepare("
            SELECT * FROM professores 
            WHERE nome_completo = :nome 
              AND disciplina_id = :disciplina_id 
              AND turma_id = :turma_id 
              AND classe_id = :classe_id
              AND status = 'Ativo'
        ");
        $stmtVerifica->execute([
            ':nome' => $nomeProfessor,
            ':disciplina_id' => $disciplinaId,
            ':turma_id' => $turmaId,
            ':classe_id' => $classeId
        ]);

        if ($stmtVerifica->rowCount() === 0) {
            return []; // O professor não tem permissão para acessar essas notas
        }

        // Buscar os alunos com notas para essa disciplina/turma/classe
        $stmt = $this->conn->prepare("
            SELECT n.*, a.nome_completo, a.id, a.turma_id, c.nome_classe AS classe
            FROM nota n
            INNER JOIN aluno a ON a.id = n.aluno_id
            INNER JOIN classe c ON c.id_classe = :classe_id
            WHERE n.disciplina_id = :disciplina_id 
              AND a.turma_id = :turma_id 
              AND a.classe_id = :classe_id
        ");
        $stmt->execute([
            ':disciplina_id' => $disciplinaId,
            ':turma_id' => $turmaId,
            ':classe_id' => $classeId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
