<?php
require_once __DIR__ . '/../models/Disciplina.php';

class DisciplinaController
{
    private $disciplina;

    public function __construct()
    {
        $this->disciplina = new Disciplina();
    }

    public function listarDisciplinas()
    {
        return $this->disciplina->listar();
    }

    public function criarDisciplina($dados)
    {
        return $this->disciplina->criar($dados['nome'], $dados['codigo'], $dados['status']);
    }
    public function buscarDisciplina($id)
    {
        return $this->disciplina->buscarPorId($id);
    }

    public function atualizarDisciplina($dados)
    {
        return $this->disciplina->atualizar($dados['id'], $dados['nome'], $dados['codigo'], $dados['status']);
    }

    public function deletarDisciplina($id)
    {
        return $this->disciplina->deletar($id);
    }

   
} 
