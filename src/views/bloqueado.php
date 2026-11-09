<?php
session_start();
if (!isset($_SESSION['bloqueado']) || $_SESSION['bloqueado'] !== true) {
    // Se não estiver bloqueado, redireciona para homea.php
    header("Location: homea.php");
    exit;
}
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12 text-center">
                    <h3 class="text-danger">Acesso Bloqueado</h3>
                    <p class="text-muted">Você possui propinas em atraso. Regularize sua situação para continuar usando o sistema.</p>
                    <button class="btn btn-danger mt-3" data-toggle="modal" data-target="#modalBloqueado">Ver Detalhes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

<!-- Modal de Bloqueio -->
<div class="modal fade" id="modalBloqueado" tabindex="-1" role="dialog" aria-labelledby="modalBloqueadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content text-center">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title w-100" id="modalBloqueadoLabel">Acesso Bloqueado</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Você possui propinas em atraso e está temporariamente impedido de acessar o sistema.</p>
        <p>Por favor, dirija-se ao setor financeiro para regularizar sua situação.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<!-- Script para bloquear todos os links do sidebar se estiver bloqueado -->
<?php if (isset($_SESSION['bloqueado']) && $_SESSION['bloqueado'] === true): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebarLinks = document.querySelectorAll('#sidebar a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // Impede a navegação
                $('#modalBloqueado').modal('show'); // Mostra o modal
            });
        });
    });
</script>
<?php endif; ?>
