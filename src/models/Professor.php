<?php
require_once __DIR__ . '../../../config/database.php';

class Professor
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function listar()
    {
        $sql = "SELECT 
                p.*, 
                f.nome AS nome_completo, 
                f.email, 
                f.telefone AS contacto,
                d.id AS disciplina_id,
                d.nome AS nome_disciplina
            FROM professores p
            INNER JOIN funcionarios f ON f.id = p.funcionario_id
            LEFT JOIN professor_disciplinas pd ON pd.professor_id = p.id_professor
            LEFT JOIN disciplinas d ON d.id = pd.disciplina_id
            GROUP BY p.id_professor";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    public function listarFuncionariosProfessores()
    {
        $sql = "SELECT * FROM funcionarios WHERE cargo_id = 1";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function listarDisciplinas()
    {
        $stmt = $this->conn->query("SELECT id, nome FROM disciplinas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarClasses()
    {
        $stmt = $this->conn->query("SELECT id_classe, nome_classe FROM classe");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTurmas()
    {
        $stmt = $this->conn->query("SELECT id_turma, nome_turma FROM turmas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Salvar novo professor
    public function salvar($dados)
    {
        $sql = "INSERT INTO professores (
                    funcionario_id, email, contacto, genero, data_nascimento,
                    grau_academico, especialidade, status
                ) VALUES (
                    :funcionario_id, :email, :telefone, :genero, :data_nascimento,
                    :grau_academico, :especialidade, :status
                )";

        $stmt = $this->conn->prepare($sql);
        $success = $stmt->execute([
            ':funcionario_id' => $dados['funcionario_id'],
            ':email' => $dados['email'],
            ':telefone' => $dados['telefone'],
            ':genero' => $dados['genero'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':grau_academico' => $dados['grau_academico'],
            ':especialidade' => $dados['especialidade'],
            ':status' => $dados['status']
        ]);

        if ($success && !empty($dados['disciplina_id'])) {
            $professor_id = $this->conn->lastInsertId();
            $stmt = $this->conn->prepare("INSERT INTO professor_disciplinas (professor_id, disciplina_id) VALUES (?, ?)");
            $stmt->execute([$professor_id, $dados['disciplina_id']]);
        }

        return $success;
    }

    // Atualizar professor existente
    public function atualizar($dados)
    {
        $sql = "UPDATE professores SET
                    email = :email,
                    contacto = :telefone,
                    genero = :genero,
                    data_nascimento = :data_nascimento,
                    grau_academico = :grau_academico,
                    especialidade = :especialidade,
                    status = :status
                WHERE id_professor = :id";
        $stmt = $this->conn->prepare($sql);
        $success = $stmt->execute([
            ':email' => $dados['email'],
            ':telefone' => $dados['telefone'],
            ':genero' => $dados['genero'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':grau_academico' => $dados['grau_academico'],
            ':especialidade' => $dados['especialidade'],
            ':status' => $dados['status'],
            ':id' => $dados['id']
        ]);

        if ($success && !empty($dados['disciplina_id'])) {
            $stmt = $this->conn->prepare("REPLACE INTO professor_disciplinas (professor_id, disciplina_id) VALUES (?, ?)");
            $stmt->execute([$dados['id'], $dados['disciplina_id']]);
        }

        return $success;
    }

    // Excluir professor
    public function eliminar($id)
    {
        // Apagar disciplinas vinculadas
        $stmt = $this->conn->prepare("DELETE FROM professor_disciplinas WHERE professor_id = ?");
        $stmt->execute([$id]);

        // Depois apagar o professor
        $stmt = $this->conn->prepare("DELETE FROM professores WHERE id_professor = ?");
        return $stmt->execute([$id]);
    }


    // Buscar professor por ID
    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM professores WHERE id_professor = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // --- NOVO: Atribuir turma e classe ---
    public function atribuirTurmaClasse($professor_id, $classe_id, $turma_id)
    {
        // Inserir turma
        if (!empty($turma_id)) {
            $stmt = $this->conn->prepare("INSERT INTO professor_turmas (professor_id, turma_id) VALUES (?, ?)");
            $stmt->execute([$professor_id, $turma_id]);
        }

        // Inserir classe
        if (!empty($classe_id)) {
            $stmt = $this->conn->prepare("INSERT INTO professor_classe (professor_id, classe_id) VALUES (?, ?)");
            $stmt->execute([$professor_id, $classe_id]);
        }
    }

    // --- LISTAR TODAS AS ATRIBUIÇÕES ---
    public function listarAtribuicoes()
    {
        $sql = "SELECT 
                    p.id_professor,
                    f.nome AS nome_professor,
                    c.id_classe,
                    c.nome_classe,
                    t.id_turma,
                    t.nome_turma
                FROM professores p
                INNER JOIN funcionarios f ON f.id = p.funcionario_id
                LEFT JOIN professor_classe pc ON pc.professor_id = p.id_professor
                LEFT JOIN classe c ON c.id_classe = pc.classe_id
                LEFT JOIN professor_turmas pt ON pt.professor_id = p.id_professor
                LEFT JOIN turmas t ON t.id_turma = pt.turma_id
                ORDER BY f.nome ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- REMOVER TODAS AS ATRIBUIÇÕES DE UM PROFESSOR ---
    public function removerTodasAtribuicoes($professor_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM professor_turmas WHERE professor_id = ?");
        $stmt->execute([$professor_id]);

        $stmt = $this->conn->prepare("DELETE FROM professor_classe WHERE professor_id = ?");
        $stmt->execute([$professor_id]);
    }

    // --- EDITAR ATRIBUIÇÃO ---
    public function editarAtribuicao($professor_id, $classe_id, $turma_id)
    {
        // Primeiro, apaga o que já existe
        $this->removerTodasAtribuicoes($professor_id);

        // Depois insere as novas ligações
        $this->atribuirTurmaClasse($professor_id, $classe_id, $turma_id);
    }

    // --- ELIMINAR ATRIBUIÇÕES ---
    public function eliminarAtribuicao($professor_id)
    {
        $this->removerTodasAtribuicoes($professor_id);
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
