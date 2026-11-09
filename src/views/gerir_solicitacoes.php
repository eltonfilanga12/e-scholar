<?php

require_once __DIR__ . '/../controllers/SolicitacaoNotaController.php';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

// Buscar solicitações
$solicitacoes = listarSolicitacoes();

?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Solicitações de Alteração de Notas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Alteração de Notas</li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Lista de Solicitações</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Professor</th>
                                        <th>Aluno</th>
                                        <th>Disciplina</th>
                                        <th>Campo</th>
                                        <th>Motivo</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitacoes as $s): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($s['professor']) ?></td>
                                            <td><?= htmlspecialchars($s['aluno']) ?></td>
                                            <td><?= htmlspecialchars($s['disciplina']) ?></td>
                                            <td><?= htmlspecialchars($s['campo_nota']) ?></td>
                                            <td><?= htmlspecialchars($s['motivo']) ?></td>
                                            <td>
                                                <span class="badge <?= $s['status'] == 'aprovado' ? 'bg-success' : ($s['status'] == 'rejeitado' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                    <?= ucfirst($s['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                    <button type="submit" name="status" value="aprovado" class="btn btn-success btn-sm">Aprovar</button>
                                                    <button type="submit" name="status" value="rejeitado" class="btn btn-danger btn-sm">Rejeitar</button>
                                                </form>
                                            </td>
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