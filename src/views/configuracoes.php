<?php

require_once __DIR__ . '/../models/Instituicao.php';

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';


$instituicaoModel = new Instituicao();
$dados = $instituicaoModel->getInstituicao();
?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Configurações da Instituição</h3>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->

                <form action="../controllers/InstituicaoController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_instituicoes" value="<?= $dados['id_instituicoes'] ?? '' ?>">

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Nome da Instituição <span class="text-danger">*</span></label>
                                <input class="form-control" name="nome" type="text" value="<?= $dados['nome'] ?? '' ?>">
                            </div>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Responsável</label>
                                <input class="form-control" name="responsavel" type="text" value="<?= $dados['responsavel'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Endereço</label>
                                <input class="form-control" name="endereco" type="text" value="<?= $dados['endereco'] ?? '' ?>">
                            </div>
                        </div>
                        

                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>País</label>
                                <input class="form-control" name="pais" type="text" value="<?= $dados['pais'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Cidade</label>
                                <input class="form-control" name="cidade" type="text" value="<?= $dados['cidade'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Província</label>
                                <input class="form-control" name="provincia" type="text" value="<?= $dados['provincia'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" name="email" type="email" value="<?= $dados['email'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Telefone</label>
                                <input class="form-control" name="telefone" type="text" value="<?= $dados['telefone'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Telemóvel</label>
                                <input class="form-control" name="telemovel" type="text" value="<?= $dados['telemovel'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Website</label>
                                <input class="form-control" name="website" type="text" value="<?= $dados['website'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                            <label>Foto da Instituição</label>
                            <input type="hidden" name="foto_existente" value="<?= $dados['foto'] ?? '' ?>">
                            <input class="form-control" name="foto" type="file" id="foto" onchange="previewImage(event)">
                            <?php if (!empty($dados['foto'])): ?>
                                <img src="../uploads/<?= $dados['foto'] ?>" alt="Foto Instituição" style="width:100px;margin-top:10px;">
                            <?php endif; ?>
                            <img id="preview" src="#" alt="Preview" style="width: 100px; display: none; margin-top: 10px;">
                        </div>

                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Nuit</label>
                                <input class="form-control" name="nuit" type="text" value="<?= $dados['nuit'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tipo de Instituição</label>
                                <select class="form-control" name="tipo_instituicao">
                                    <option value="privada" <?= ($dados['tipo_instituicao'] ?? '') === 'privada' ? 'selected' : '' ?>>Privada</option>
                                    <option value="publica" <?= ($dados['tipo_instituicao'] ?? '') === 'publica' ? 'selected' : '' ?>>Pública</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Salvar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->


<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script>
    function previewImage(event) {
        var output = document.getElementById('preview');
        output.src = URL.createObjectURL(event.target.files[0]);
        output.style.display = 'block'; // Exibe o preview
    }
</script>