<?php
require_once __DIR__ . '/../models/FormulaNota.php';
require_once __DIR__ . '/../models/Nivel.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class FormulaNotaController
{
    private $model;
    private $nivelModel;

    public function __construct()
    {
        $this->model = new FormulaNota();
        $this->nivelModel = new Nivel();
    }

    public function listar()
    {
        return $this->model->getAll();
    }

    public function listarNiveis()
    {
        return $this->nivelModel->getAll();
    }

    public function salvar()
    {
        if (isset($_POST['salvar_formula'])) {
            $id = $_POST['id'] ?? null;
            $nivel_id = $_POST['nivel_id'];
            $nome_formula = $_POST['nome_formula'];
            $num_testes = $_POST['num_testes'];
            $peso_testes = $_POST['peso_testes'];
            $num_trabalhos = $_POST['num_trabalhos'];
            $peso_trabalhos = $_POST['peso_trabalhos'];

            try {
                if ($id) {
                    // Atualizar
                    $this->model->update($id, $nome_formula, $nivel_id, $num_testes, $peso_testes, $num_trabalhos, $peso_trabalhos);
                    $_SESSION['msg_sucesso'] = "Fórmula atualizada com sucesso!";
                } else {
                    // Criar
                    $this->model->create($nome_formula, $nivel_id, $num_testes, $peso_testes, $num_trabalhos, $peso_trabalhos);
                    $_SESSION['msg_sucesso'] = "Fórmula cadastrada com sucesso!";
                }
            } catch (Exception $e) {
                $_SESSION['msg_erro'] = "Erro ao salvar a fórmula: " . $e->getMessage();
            }

            header("Location: ../views/configuracoes_notas");
            exit;
        }
    }

    public function excluir($id)
    {
        try {
            $this->model->delete($id);
            $_SESSION['msg_sucesso'] = "Fórmula excluída com sucesso!";
        } catch (Exception $e) {
            $_SESSION['msg_erro'] = "Erro ao excluir a fórmula: " . $e->getMessage();
        }

        header("Location: ../views/configuracoes_notas");
        exit;
    }

    public function carregar($id)
    {
        return $this->model->getById($id);
    }
}

// --------------------
// Fora da classe, tratar POST e GET
// --------------------
$controller = new FormulaNotaController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->salvar();
} elseif (isset($_GET['action'])) {
    if ($_GET['action'] === 'excluir' && isset($_GET['id'])) {
        $controller->excluir($_GET['id']);
    } elseif ($_GET['action'] === 'editar' && isset($_GET['id'])) {
        $formula = $controller->carregar($_GET['id']);
        // Você pode carregar a página ou retornar JSON para preencher o modal
    }
}
