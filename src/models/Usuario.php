<?php
class Usuario
{
    private $conn;
    public $id, $nome, $senha, $tipo_usuario, $user_status, $data_criacao;
    public $referencia_id; // novo campo para saber qual ID de aluno ou funcionário está sendo relacionado

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save()
    {
        if ($this->id) {
            // Atualiza dados do usuário
            $sql = "UPDATE usuarios SET nome=?, tipo_usuario=?, user_status=? WHERE id=?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$this->nome, $this->tipo_usuario, $this->user_status, $this->id]);
        } else {
            // Cria novo usuário
            $sql = "INSERT INTO usuarios (nome, senha, tipo_usuario, user_status, data_criacao) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $this->nome,
                password_hash($this->senha, PASSWORD_DEFAULT),
                $this->tipo_usuario,
                $this->user_status
            ]);

            if ($result) {
                $this->id = $this->conn->lastInsertId();
                $this->atualizarRelacionamento(); // Atualiza id_usuario na tabela aluno ou professores
            }

            return $result;
        }
    }

    private function atualizarRelacionamento()
    {
        if ($this->tipo_usuario === 'Aluno') {
            $stmt = $this->conn->prepare("UPDATE aluno SET id_usuario = ? WHERE id = ?");
            $stmt->execute([$this->id, $this->referencia_id]);
        } elseif ($this->tipo_usuario === 'Professor') {
            $stmt = $this->conn->prepare("UPDATE professores SET id_usuario = ? WHERE funcionario_id = ?");
            $stmt->execute([$this->id, $this->referencia_id]);
        }
    }

    public function delete($id)
    {
        // Primeiro, remover o vínculo em professores (ou alunos, se aplicável)
        $stmtCheckProf = $this->conn->prepare("SELECT * FROM professores WHERE id_usuario = ?");
        $stmtCheckProf->execute([$id]);
        $professor = $stmtCheckProf->fetch(PDO::FETCH_ASSOC);

        if ($professor) {
            // Você pode excluir o professor ou apenas desfazer o vínculo
            $stmtDesvincula = $this->conn->prepare("UPDATE professores SET id_usuario = NULL WHERE id_usuario = ?");
            $stmtDesvincula->execute([$id]);
        }

        // (Opcional) repetir o mesmo para alunos
        $stmtCheckAluno = $this->conn->prepare("SELECT * FROM aluno WHERE id_usuario = ?");
        $stmtCheckAluno->execute([$id]);
        $aluno = $stmtCheckAluno->fetch(PDO::FETCH_ASSOC);

        if ($aluno) {
            $stmtDesvincula = $this->conn->prepare("UPDATE aluno SET id_usuario = NULL WHERE id_usuario = ?");
            $stmtDesvincula->execute([$id]);
        }

        // Agora sim, excluir o usuário
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
