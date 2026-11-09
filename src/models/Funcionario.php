<?php
class Funcionario
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function listar()
    {
        $sql = "SELECT f.*, c.nome AS nome_cargo, d.nome AS nome_departamento, f.endereco, t.nome AS nome_turno
                FROM funcionarios f
                LEFT JOIN cargos c ON f.cargo_id = c.id
                LEFT JOIN departamentos d ON f.departamento_id = d.id
                LEFT JOIN turnos t ON f.turno_id = t.id";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM funcionarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar($dados)
{
    if (!empty($dados['id'])) {
        // Atualiza funcionário existente
        $stmt = $this->db->prepare("UPDATE funcionarios SET
            nome = ?, sexo = ?, data_nascimento = ?, idade = ?, nacionalidade = ?, naturalidade = ?,
            documento_tipo = ?, numero_documento = ?, telefone = ?, email = ?, endereco = ?,
            cargo_id = ?, departamento_id = ?, data_admissao = ?, turno_id = ?, tipo_salario = ?, salario = ?, valor_hora = ?, usuario_id = ?, status = ?
            WHERE id = ?");

        return $stmt->execute([
            $dados['nome_completo'], $dados['sexo'], $dados['data_nascimento'], $dados['idade'], $dados['nacionalidade'], $dados['naturalidade'],
            $dados['documento_tipo'], $dados['numero_documento'], $dados['telefone'], $dados['email'], $dados['endereco'],
            $dados['cargo_id'], $dados['departamento_id'], $dados['data_admissao'], $dados['turno_id'], $dados['tipo_salario'], $dados['salario'], $dados['valor_hora'], $dados['usuario_id'], $dados['status'],
            $dados['id']
        ]);
    } else {
        // Insere novo funcionário
        $stmt = $this->db->prepare("INSERT INTO funcionarios 
            (nome, sexo, data_nascimento, idade, nacionalidade, naturalidade, documento_tipo, numero_documento, telefone, email, endereco, 
            cargo_id, departamento_id, data_admissao, turno_id, tipo_salario, salario, valor_hora, usuario_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        return $stmt->execute([
            $dados['nome_completo'], $dados['sexo'], $dados['data_nascimento'], $dados['idade'], $dados['nacionalidade'], $dados['naturalidade'],
            $dados['documento_tipo'], $dados['numero_documento'], $dados['telefone'], $dados['email'], $dados['endereco'],
            $dados['cargo_id'], $dados['departamento_id'], $dados['data_admissao'], $dados['turno_id'], $dados['tipo_salario'], $dados['salario'], $dados['valor_hora'], $dados['usuario_id'], $dados['status']
        ]);
    }
}


    public function excluir($id)
    {
        $stmt = $this->db->prepare("DELETE FROM funcionarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function listarCargos()
    {
        return $this->db->query("SELECT * FROM cargos")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarDepartamentos()
    {
        return $this->db->query("SELECT * FROM departamentos")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTurnos()
    {
        return $this->db->query("SELECT * FROM turnos")->fetchAll(PDO::FETCH_ASSOC);
    }

    
}
