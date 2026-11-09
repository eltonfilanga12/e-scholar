<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '../../../config/database.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $database = new Database();
        $conn = $database->getConnection();
        $this->usuario = new Usuario($conn);
    }

    public function listarUser() {
        return $this->usuario->getAll();
    }

    public function salvar($data) {
        $this->usuario->id = $data['id'] ?? null;
        $this->usuario->nome = $data['nome'];
        $this->usuario->senha = $data['senha'] ?? null;
        $this->usuario->tipo_usuario = $data['tipo_usuario'];
        $this->usuario->user_status = $data['user_status'];
        $this->usuario->referencia_id = $data['referencia_id'] ?? null;
        return $this->usuario->save();
    }

    public function excluir($id) {
        return $this->usuario->delete($id);
    }
}
