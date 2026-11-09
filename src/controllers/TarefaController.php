<?php
require_once __DIR__ . '/../models/TarefaAdministrativa.php';

class TarefaController
{
    private $model;

    public function __construct()
    {
        $this->model = new TarefaAdministrativa();
    }

    public function listar()
    {
        return $this->model->listar();
    }

    public function salvar($dados)
    {
        $id = $dados['id'] ?? null;
        $titulo = $dados['titulo'] ?? '';
        $descricao = $dados['descricao'] ?? '';
        $funcionario_id = $dados['funcionario_id'] ?? null;
        $status = $dados['status'] ?? 'Pendente';
        $prazo = $dados['prazo'] ?? null;

        if ($id) {
            $this->model->atualizar($id, $titulo, $descricao, $funcionario_id, $status, $prazo);
        } else {
            $this->model->criar($titulo, $descricao, $funcionario_id, $status, $prazo);
        }
    }

    public function deletar($id)
    {
        $this->model->deletar($id);
    }
}
