<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
require_once __DIR__ . '../../../config/database.php';

$nomeAluno = $_SESSION['nome'] ?? null;
$mensagem = "";

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $nomeAluno) {
    $senhaAtualInput = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    try {
        $db = new Database();
        $conn = $db->getConnection();

        // Obter senha atual do banco
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE nome = :nome LIMIT 1");
        $stmt->bindParam(':nome', $nomeAluno);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senhaAtualInput, $usuario['senha'])) {
            if (strlen($novaSenha) < 8) {
                $mensagem = "<div class='alert alert-danger'>A nova senha deve ter no mínimo 8 caracteres.</div>";
            } elseif ($novaSenha !== $confirmarSenha) {
                $mensagem = "<div class='alert alert-danger'>A nova senha e a confirmação não coincidem.</div>";
            } else {
                // Atualizar senha (criptografada)
                $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE usuarios SET senha = :novaSenha WHERE nome = :nome");
                $updateStmt->bindParam(':novaSenha', $novaSenhaHash);
                $updateStmt->bindParam(':nome', $nomeAluno);
                $updateStmt->execute();

                $mensagem = "<div class='alert alert-success'>Senha alterada com sucesso!</div>";
            }
        } else {
            $mensagem = "<div class='alert alert-danger'>Senha atual incorreta.</div>";
        }
    } catch (Exception $e) {
        $mensagem = "<div class='alert alert-danger'>Erro ao atualizar a senha.</div>";
    }
}
?>


<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Mudar Senha</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Principal</a></li>
                        <li class="breadcrumb-item active">Mudar Senha</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php if (!empty($mensagem)) echo $mensagem; ?>

        <div class="row">
            <div class="col-12">
                <div class="product-payment-inner-st">
                    <div id="myTabContent" class="tab-content custom-product-edit">
                        <div class="product-tab-list tab-pane fade show active" id="description">
                            <div class="review-content-section">
                                <div id="dropzone1" class="pro-ad addcoursepro">
                                    <form action="" method="POST" class="dropzone-custom addcourse">
                                        <div class="row">

                                            <!-- Nome do Usuário -->
                                            <div class="col-6 mb-3">
                                                <div class="form-group">
                                                    <label><i class="fa fa-user"></i> Nome do Usuário:</label>
                                                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($nomeAluno) ?>">
                                                </div>
                                            </div>

                                            <!-- Senha Atual (mascarada) -->
                                            <div class="col-6 mb-3">
                                                <div class="form-group">
                                                    <label><i class="fa fa-lock"></i> Senha Atual:</label>
                                                    <input type="password" name="senha_atual" class="form-control" required>
                                                </div>
                                            </div>

                                            <!-- Nova Senha -->
                                            <div class="col-6 mb-3">
                                                <div class="form-group">
                                                    <label><i class="fa fa-lock"></i> Nova Senha:</label>
                                                    <input type="password" name="nova_senha" class="form-control" required>
                                                </div>
                                            </div>

                                            <!-- Confirmar Nova Senha -->
                                            <div class="col-6 mb-3">
                                                <div class="form-group">
                                                    <label><i class="fa fa-lock"></i> Confirmar Nova Senha:</label>
                                                    <input type="password" name="confirmar_senha" class="form-control" required>
                                                </div>
                                            </div>

                                        </div>

                                        <!-- Botão de Submissão -->
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="payment-adress">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-save"></i> Guardar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>