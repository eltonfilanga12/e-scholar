<?php

// models/Instituicao.php
require_once __DIR__ . '../../../config/database.php';

class Instituicao
{
    private $conn;
    private $table = "instituicoes";

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function getInstituicao()
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarInstituicao($dados)
{
    $sql = "UPDATE $this->table SET 
                nome = :nome,
                responsavel = :responsavel,
                endereco = :endereco,
                pais = :pais,
                cidade = :cidade,
                provincia = :provincia,
                email = :email,
                telefone = :telefone,
                telemovel = :telemovel,
                fax = :fax,
                website = :website,
                foto = :foto,
                nuit = :nuit,
                tipo_instituicao = :tipo_instituicao
            WHERE id_instituicoes = :id_instituicoes";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($dados);
}

}
