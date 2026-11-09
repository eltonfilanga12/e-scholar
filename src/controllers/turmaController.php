<?php
// src/controllers/controllerTurma.php

require_once __DIR__ . '/../models/Turma.php';

class ControllerTurma
{
    private $model;

    public function __construct()
    {
        $this->model = new Turma();
    }

    // Listar turmas
    public function listar()
    {
        return $this->model->listarTurmas();
    }

    // Cadastrar turma via POST
    public function cadastrar($data)
    {
        if (isset($data['nome'], $data['classe_id'], $data['turno'], $data['ano_letivo'], $data['status'])) {
            return $this->model->cadastrarTurma(
                $data['nome'],
                $data['classe_id'],
                $data['turno'],
                $data['ano_letivo'],
                $data['status']
            );
        }
        return false;
    }

    // Editar turma via POST
    public function editar($id, $data)
    {
        if (isset($data['nome'], $data['classe_id'], $data['turno'], $data['ano_letivo'], $data['status'])) {
            return $this->model->editarTurma(
                $id,
                $data['nome'],
                $data['classe_id'],
                $data['turno'],
                $data['ano_letivo'],
                $data['status']
            );
        }
        return false;
    }

    // Deletar turma por ID
    public function deletar($id)
    {
        return $this->model->deletarTurma($id);
    }

    // Buscar turma por ID
    public function getById($id)
    {
        return $this->model->getTurmaById($id);
    }
}
