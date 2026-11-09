<?php
require_once __DIR__ . '/../../config/database.php';

class Permissao {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function listarPorTipo($tipo_usuario) {
        $stmt = $this->pdo->prepare("SELECT * FROM permissoes WHERE tipo_usuario = ?");
        $stmt->execute([$tipo_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTipos() {
        return [ 'Professor', 'Secretário', 'Aluno', 'Pedagógico'];
    }

    public function listarPaginasDisponiveis() {
        return [
            'lista_aluno' => 'Acesso a Lista de Alunos',
            'lancar_notas' => 'Lançamento de Notas de Frequência',
            'lancar_notas_exame' => 'Lançamento de Notas de Exame',
            'matricula' => 'Efectuar Matrícula',
            'atribuir_turma' => 'Atribuir Turma a alunos',
            'professores' => 'Cadastrar Professores',
            'gerir_solicitacoes' => 'Gerir Solicitações',
            'pagamentos' => 'Efectuar Pagamentos',
            'usuarios' => 'Cadastrar Usuários',
            'funcionarios' => 'Cadastrar Funcionários',
            'turmas' => 'Cadastrar Turmas',
            'disciplinas' => 'Cadastrar Disciplinas',
            'classes' => 'Cadastrar Classes',
            'salas' => 'Cadastrar Salas',
            'configuracoes_notas' => 'Fórmula de Notas',
            'turnos_horarios' => 'Horários por Turno',
            'horarios' => 'Gestão de horários',
            'precos' => 'Gestão de Preços',
            'tarefas_administrativas' => 'Dar tarefas Administrativas',
            'avisos' => 'Enviar Avisos',
            'propinas' => 'Gestão de Propinas',
            'gerarfatura' => 'Gerar Faturas',
            'permissao' => 'Gestão de Permissões',
            'configuracoes' => 'Configuração do Sistema',
        ];
    }
    

    public function atualizarPermissao($tipo_usuario, $pagina, $permitido) {
        $stmt = $this->pdo->prepare("SELECT id FROM permissoes WHERE tipo_usuario = ? AND pagina = ?");
        $stmt->execute([$tipo_usuario, $pagina]);
        $id = $stmt->fetchColumn();
    
        $usuario = $_SESSION['nome'];  // Pega o nome do usuário da sessão
        $acao = $id ? 'Atualizar Permissão' : 'Adicionar Permissão';  // Define a ação (adicionar ou atualizar)
        $descricao = "Permissão para '$pagina' foi " . ($permitido ? 'permitida' : 'revogada') . " para o tipo de usuário '$tipo_usuario'.";
    
        // Registrando o log
        LogHelper::registrar($this->pdo, $usuario, $acao, $descricao);
    
        if ($id) {
            $update = $this->pdo->prepare("UPDATE permissoes SET permitido = ? WHERE id = ?");
            return $update->execute([$permitido, $id]);
        } else {
            $insert = $this->pdo->prepare("INSERT INTO permissoes (tipo_usuario, pagina, permitido) VALUES (?, ?, ?)");
            return $insert->execute([$tipo_usuario, $pagina, $permitido]);
        }
    }
    
}
