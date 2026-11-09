<?php

require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '/../models/Salas.php';

class SalaController
{
    private $sala;

    public function __construct()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $this->sala = new Sala($conn);
    }

    public function listar()
    {
        return $this->sala->listar();
    }

    public function salvar($dados)
    {
        return $this->sala->salvar($dados);
    }

    public function deletar($id)
    {
        return $this->sala->deletar($id);
    }
}

