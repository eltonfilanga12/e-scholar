<?php
require_once __DIR__ . '/../models/Preco.php';

class PrecoController
{
    private $preco;

    public function __construct()
    {
        $this->preco = new Preco();
    }

    public function listar()
    {
        return $this->preco->listar();
    }

    public function salvar($dados)
    {
        return $this->preco->salvar($dados);
    }

    public function editar($dados)
    {
        return $this->preco->editar($dados);
    }

    public function buscarPorId($id)
    {
        return $this->preco->buscarPorId($id);
    }

    public function deletar($id)
    {
        return $this->preco->deletar($id);
    }
}
