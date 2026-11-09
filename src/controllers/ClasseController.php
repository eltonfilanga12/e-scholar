<?php
require_once __DIR__ . '/../models/Classe.php';

class ControllerClasse
{
    private $classeModel;

    public function __construct()
    {
        $this->classeModel = new Classe();
    }

    public function listar()
    {
        return $this->classeModel->listarClasses();
    }

    public function buscarPorId($id)
    {
        return $this->classeModel->buscarPorId($id);
    }

    public function cadastrar($dados)
    {
        $nome = $dados['nome_classe'] ?? '';
        $status = $dados['status'] ?? 'Ativa';
        $descricao = $dados['descricao'] ?? '';

        if (empty($nome)) {
            return false;
        }

        return $this->classeModel->criar($nome, $status, $descricao);
    }

    public function editar($id, $dados)
    {
        $nome = $dados['nome_classe'] ?? '';
        $status = $dados['status'] ?? 'Ativa';
        $descricao = $dados['descricao'] ?? '';

        if (empty($nome) || empty($id)) {
            return false;
        }

        return $this->classeModel->atualizar($id, $nome, $status, $descricao);
    }

    public function deletar($id)
    {
        if (empty($id)) {
            return false;
        }

        return $this->classeModel->deletar($id);
    }
}
