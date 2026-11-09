<?php
session_start();
require_once __DIR__ . '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];

    $db = new Database();
    $conn = $db->getConnection();

    $sql = "SELECT * FROM usuarios WHERE nome = :nome LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->execute();

    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['tipo'] = $user['tipo_usuario'];
        $_SESSION['instituicao_id'] = $user['instituicoes_id'];

        require_once __DIR__ . '/../helpers/LogHelper.php';
        LogHelper::registrar($conn, $user['nome'], 'Login', 'Login efetuado com sucesso');

        // Redirecionar conforme tipo de usuário
        switch ($user['tipo_usuario']) {
            case 'Administrador':
                header("Location: dashboard");
                break;
            case 'Aluno':
                header("Location: homea");
                break;
            case 'Professor':
                header("Location: homef");
                break;
            case 'Secretário':
            case 'Secretario': // Para evitar problemas com acento
                header("Location: dashboard_secretario");
                break;
                case 'Pedagógico':
                    case 'Pedagogico': // Para evitar problemas com acento
                        header("Location: dashboard");
                        break;
            default:
                echo "Tipo de usuário não reconhecido.";
        }
    } else {
        require_once __DIR__ . '/../helpers/LogHelper.php';
        LogHelper::registrar($conn, $nome, 'Falha Login', 'Tentativa de login com senha inválida');

        $_SESSION['status'] = "Nome de usuário ou senha inválidos.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Smarthr - Bootstrap Admin Template">
    <meta name="keywords" content="admin, professores, alunos, tecnologia, pagamento bloqueio">
    <meta name="author" content="Elton Langa">
    <title>e-Scholar</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="../../public/img/graduado.png">

    <!-- Font Family -->
    <link href="https://fonts.googleapis.com/css?family=Fira+Sans:400,500,600,700" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">

    <!--custom styles-->
    <link rel="stylesheet" href="assets/css/style.css">

    <!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
	<![endif]-->
</head>

<body>

    <!-- Main Wrapper -->
    <div class="main-wrapper account-wrapper">
        <div class="account-page">
            <div class="account-center">

                <div class="account-box">
                    <?php
                    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
                    ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>e-Scholar, </strong><?php echo $_SESSION['status']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria_hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php
                        unset($_SESSION['status']);
                    }

                    ?>
                    <form action="login.php" method="POST" class="form-signin">
                        <div class="account-logo">
                            <a href="#"><img src="../../public/img/logoSchol.png" alt="Preadmin"></a>
                        </div>
                        <div class="form-group">
                            <label>Nome ou Usuário</label>
                            <input type="text" name="nome" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" name="senha" required class="form-control">
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary account-btn">Login</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <!--/ Main Wrapper -->





    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>

</html>