<?php
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/../controllers/PermissaoController.php';
require_once __DIR__ . '/partials/sidebar.php';

$tipos = listarTipos();
$paginas = listarPaginas();
$tipoSelecionado = $_GET['tipo'] ?? $tipos[0];
$permissoes = listarPermissoes($tipoSelecionado);
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3 class="page-title">Gestão de Permissões</h3>
        </div>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['mensagem'];
                unset($_SESSION['mensagem']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <form method="get" class="mb-3">
            <label>Selecionar Tipo de Usuário:</label>
            <select name="tipo" class="form-select w-25 d-inline" onchange="this.form.submit()">
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= $tipo ?>" <?= $tipo === $tipoSelecionado ? 'selected' : '' ?>><?= $tipo ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="post" action="../controllers/PermissaoController.php">
            <input type="hidden" name="tipo_usuario" value="<?= $tipoSelecionado ?>">

            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Página / Função</th>
                        <th class="text-center">Permitido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginas as $slug => $nome): ?>
                        <tr>
                            <td><?= htmlspecialchars($nome) ?></td>
                            <td class="text-center">
                                <input type="hidden" name="paginas[<?= $slug ?>]" value="0">
                                <input type="checkbox" name="paginas[<?= $slug ?>]" value="1"
                                       <?= isset($permissoes[$slug]) && $permissoes[$slug] ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary mt-3">Salvar Permissões</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
