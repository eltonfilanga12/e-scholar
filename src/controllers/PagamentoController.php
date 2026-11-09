<?php 
require_once __DIR__ . '/../models/Pagamento.php'; 
require_once __DIR__ . '/../helpers/LogHelper.php'; 
require_once __DIR__ . '/../vendor/autoload.php';

// Instância do objeto FPDF para geração do PDF
$pdf = new FPDF(); 
$pdf->AddPage(); // Adiciona uma nova página ao PDF
$pdf->SetFont('Arial', 'B', 16); // Define a fonte para o título
$pdf->Cell(0, 10, 'Comprovativo de Pagamento', 0, 1, 'C'); // Exibe o título centralizado
$pdf->Ln(10); // Adiciona uma linha em branco

class PagamentoController { 
    private $pagamento;

    // Construtor da classe, inicializa a instância de Pagamento
    public function __construct() { 
        $this->pagamento = new Pagamento(); 
    }

    // Função para listar os pagamentos
    public function listar() { 
        $pagamentos = $this->pagamento->listarPagamentos(); 
        require_once __DIR__ . '/../views/pagamentos.php';  // Passa os pagamentos para a view 
    }

    // Função para processar e salvar um novo pagamento ou atualizar um existente
    public function salvar() { 
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_pagamento'])) { 
            $id_propina = $_POST['id_propina']; 
            $id_aluno = $_POST['id_aluno']; 
            $data_pagamento = $_POST['data_pagamento']; 
            $valor_pago = $_POST['valor_pago']; 
            $forma_pagamento = $_POST['forma_pagamento']; 
            $situacao = $_POST['situacao'];

            // Estabelece conexão com o banco de dados
            $db = new Database(); 
            $pdo = $db->getConnection();

            // Busca a abreviatura da propina
            $stmtPropina = $pdo->prepare("SELECT abreviatura FROM propinas WHERE id_propinas = ?"); 
            $stmtPropina->execute([$id_propina]); 
            $abreviatura = $stmtPropina->fetchColumn(); 

            // Verifica se a propina foi encontrada
            if (!$abreviatura) { 
                $_SESSION['mensagem'] = "Propina não encontrada!"; 
                header("Location: pagamentos.php"); 
                exit; 
            }

            $mesAno = date('Y-m', strtotime($data_pagamento)); 

            // Verifica se já existe um pagamento para o mesmo aluno, mês e abreviatura
            $stmtCheck = $pdo->prepare(" 
                SELECT pg.id_pagamento 
                FROM pagamentos pg 
                INNER JOIN propinas p ON p.id_propinas = pg.id_propina 
                WHERE pg.id_aluno = :id_aluno 
                  AND p.abreviatura = :abreviatura 
                  AND DATE_FORMAT(pg.data_pagamento, '%Y-%m') = :mes_ano 
            "); 
            $stmtCheck->execute([ 
                'id_aluno' => $id_aluno, 
                'abreviatura' => $abreviatura, 
                'mes_ano' => $mesAno 
            ]); 
            $pagamentoExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC); 

            // Busca o nome do aluno para log
            $stmtAluno = $pdo->prepare("SELECT nome_completo FROM aluno WHERE id = ?"); 
            $stmtAluno->execute([$id_aluno]); 
            $nomeAluno = $stmtAluno->fetchColumn() ?: "Desconhecido"; 

            try { 
                if ($pagamentoExistente) { 
                    // Atualiza o pagamento existente
                    $stmtUpdate = $pdo->prepare(" 
                        UPDATE pagamentos 
                        SET valor_pago = :valor_pago, 
                            forma_pagamento = :forma_pagamento, 
                            data_pagamento = :data_pagamento, 
                            situacao = :situacao 
                        WHERE id_pagamento = :id_pagamento 
                    "); 
                    $stmtUpdate->execute([ 
                        'valor_pago' => $valor_pago, 
                        'forma_pagamento' => $forma_pagamento, 
                        'data_pagamento' => $data_pagamento, 
                        'situacao' => $situacao, 
                        'id_pagamento' => $pagamentoExistente['id_pagamento'] 
                    ]); 

                    LogHelper::registrar( 
                        $pdo, 
                        $_SESSION['nome'] ?? 'Desconhecido', 
                        'Atualizou pagamento', 
                        "Aluno: {$nomeAluno}, Propina: {$abreviatura}, Mês/Ano: {$mesAno}" 
                    ); 
                    $_SESSION['mensagem'] = "Pagamento atualizado com sucesso!"; 
                } else { 
                    // Caso não exista, insere um novo pagamento
                    $stmtInsert = $pdo->prepare(" 
                        INSERT INTO pagamentos 
                        (id_propina, id_aluno, data_pagamento, valor_pago, forma_pagamento, situacao) 
                        VALUES (:id_propina, :id_aluno, :data_pagamento, :valor_pago, :forma_pagamento, :situacao) 
                    "); 
                    $stmtInsert->execute([ 
                        'id_propina' => $id_propina, 
                        'id_aluno' => $id_aluno, 
                        'data_pagamento' => $data_pagamento, 
                        'valor_pago' => $valor_pago, 
                        'forma_pagamento' => $forma_pagamento, 
                        'situacao' => $situacao 
                    ]); 

                    LogHelper::registrar( 
                        $pdo, 
                        $_SESSION['nome'] ?? 'Desconhecido', 
                        'Inseriu pagamento', 
                        "Aluno: {$nomeAluno}, Propina: {$abreviatura}, Mês/Ano: {$mesAno}" 
                    ); 
                    $_SESSION['mensagem'] = "Pagamento registrado com sucesso!"; 
                }

                // Atualiza a situação da propina para "Paga" caso necessário
                if ($situacao === 'Paga') { 
                    // Atualiza a situação da propina
                    $stmtUpdatePropina = $pdo->prepare("UPDATE propinas SET situacao = 'Paga' WHERE id_propinas = ?"); 
                    $stmtUpdatePropina->execute([$id_propina]); 
                }

                // Mantém filtros e redireciona para a página de pagamentos
                $queryString = http_build_query([ 
                    'turma' => $_POST['turma'] ?? '', 
                    'classe' => $_POST['classe'] ?? '', 
                    'turno' => $_POST['turno'] ?? '', 
                    'mes_ano' => $_POST['mes_ano'] ?? '' 
                ]); 
                header("Location: pagamentos.php?$queryString"); 
                exit; 

            } catch (PDOException $e) { 
                $_SESSION['mensagem'] = "Erro ao processar pagamento: " . $e->getMessage(); 
                header("Location: pagamentos.php?$queryString"); 
                exit; 
            } 
        } 
    }

    // Função para gerar o comprovativo de pagamento em PDF
    public function gerarComprovativo($id_pagamento) { 
        $db = new Database(); 
        $pdo = $db->getConnection(); 

        // Busca os dados do pagamento
        $stmt = $pdo->prepare(" 
        SELECT pg.*, p.abreviatura, a.nome_completo 
        FROM pagamentos pg 
        INNER JOIN propinas p ON p.id_propinas = pg.id_propina 
        INNER JOIN aluno a ON a.id = pg.id_aluno 
        WHERE pg.id_pagamento = ? 
    "); 
        $stmt->execute([$id_pagamento]); 
        $pagamento = $stmt->fetch(PDO::FETCH_ASSOC); 

        if (!$pagamento || $pagamento['situacao'] !== 'Paga') { 
            die("Pagamento não encontrado ou não está pago."); 
        }

        // Criação do PDF
        $pdf = new FPDF(); 
        $pdf->AddPage(); 
        $pdf->SetFont('Arial', 'B', 16); 
        $pdf->Cell(0, 10, 'Comprovativo de Pagamento', 0, 1, 'C'); 
        $pdf->Ln(10); 
        $pdf->SetFont('Arial', '', 12); 
        $pdf->Cell(50, 10, 'Aluno:', 0, 0); 
        $pdf->Cell(0, 10, $pagamento['nome_completo'], 0, 1); 
        $pdf->Cell(50, 10, 'Tipo de Fatura:', 0, 0); 
        $pdf->Cell(0, 10, $pagamento['abreviatura'], 0, 1); 
        $pdf->Cell(50, 10, 'Valor Pago:', 0, 0); 
        $pdf->Cell(0, 10, number_format($pagamento['valor_pago'], 2, ',', '.'), 0, 1); 
        $pdf->Cell(50, 10, 'Forma de Pagamento:', 0, 0); 
        $pdf->Cell(0, 10, $pagamento['forma_pagamento'], 0, 1); 
        $pdf->Cell(50, 10, 'Data do Pagamento:', 0, 0); 
        $pdf->Cell(0, 10, $pagamento['data_pagamento'], 0, 1); 
        $pdf->Ln(20); 
        $pdf->Cell(0, 10, 'Assinatura: _________________________', 0, 1); 

        // Gera o PDF e faz o download automático
        $pdf->Output('D', 'Comprovativo_' . $pagamento['nome_completo'] . '_' . $pagamento['id_pagamento'] . '.pdf'); 
        exit; 
    } 
}