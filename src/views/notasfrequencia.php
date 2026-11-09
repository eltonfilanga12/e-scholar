<?php
session_start();

// Verificar se o usuário está logado
//if (!isset($_SESSION['nome'])) {
   // header('Location: /public/login.php'); // Redirecionar se não estiver logado
   // exit();
//}

// Incluir o controlador

require_once __DIR__ . '/../controllers/NotaController.php';

// Pegar o nome do usuário logado da sessão
$usuario_logado = $_SESSION['nome'];

// Instanciar o controlador
$notaController = new NotaController();

// Verificar se o aluno tem propinas não pagas


// Buscar as notas
$notas = $notaController->mostrarNotas($usuario_logado);

// Incluir os arquivos de layout
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="data-table-area mg-b-15">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="sparkline13-list">
                    <div class="sparkline13-hd">
                        <div class="main-sparkline13-hd">
                            <h1>Notas de Frequência</h1>
                        </div>
                    </div>
                    <div class="sparkline13-graph">
                        <div class="datatable-dashv1-list custom-datatable-overright">
                            <table id="table" data-toggle="table" data-pagination="true" data-search="true">
                                <thead>
                                    <tr>
                                        <th>Disciplina</th>
                                        <th>Teste 1</th>
                                        <th>Teste 2</th>
                                        <th>Teste 3</th>
                                        <th>Média</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notas as $nota): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($nota['nome_disciplina']); ?></td>
                                            <td><?php echo htmlspecialchars($nota['teste1']); ?></td>
                                            <td><?php echo htmlspecialchars($nota['teste2']); ?></td>
                                            <td><?php echo htmlspecialchars($nota['teste3']); ?></td>
                                            <td><?php
                                                $media = ($nota['teste1'] + $nota['teste2'] + $nota['teste3']) / 3;
                                                $mediaArredondada = round($media); // Arredonda para o inteiro mais próximo
                                                echo $mediaArredondada; // Exibe como número inteiro
                                                ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>