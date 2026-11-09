<?php
require_once __DIR__ . '/../models/Propina.php';

class PropinaController
{
    private $model;

    public function __construct()
    {
        $this->model = new Propina();
    }

    // Listar todas propinas
    public function getAllPropinas()
    {
        return $this->model->getAllPropinas();
    }

    // Criação de propina
    public function createPropina($idAluno, $classe, $tipoFatura, $serieAno, $mes, $custo, $entidade, $referencia, $prazoDoc)
    {
        return $this->model->create($idAluno, $classe, $tipoFatura, $serieAno, $mes, $custo, $entidade, $referencia, $prazoDoc);
    }

    // Propinas de um aluno
    public function getPropinasByAluno($idAluno)
    {
        return $this->model->getPropinasByAluno($idAluno);
    }

    public function getPropinasByNomeAluno($nomeAluno)
    {
        return $this->model->getPropinasByNomeAluno($nomeAluno);
    }

    public function getFaturaById($id)
    {
        return $this->model->getFaturaById($id);
    }

    // Para modal: detalhes de um aluno
    public function getDetalhesAluno($idAluno)
    {
        return $this->model->getPropinasByAluno($idAluno);
    }
}
