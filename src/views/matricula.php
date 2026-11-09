<?php
require_once __DIR__ . '../../../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Buscar classes
$stmtClasse = $conn->prepare("SELECT id_classe, nome_classe FROM classe ORDER BY nome_classe");
$stmtClasse->execute();
$classes = $stmtClasse->fetchAll(PDO::FETCH_ASSOC);

// Buscar turnos
$stmtTurno = $conn->prepare("SELECT id, nome FROM turnos ORDER BY nome");
$stmtTurno->execute();
$turnos = $stmtTurno->fetchAll(PDO::FETCH_ASSOC);


$mensagem = '';
$mensagem_tipo = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $mensagem = "Aluno matriculado com sucesso!";
        $mensagem_tipo = "success";
    } elseif ($_GET['status'] === 'error') {
        $mensagem = "Erro ao matricular aluno.";
        $mensagem_tipo = "danger";
    }
}

require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';

?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Gestão de Alunos</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a>Dashboard</a></li>
                        <li class="breadcrumb-item active">Realizar Matricula</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mensagens de feedback -->
        <?php if (!empty($mensagem)): ?>
            <div id="mensagem-feedback" class="alert alert-<?= $mensagem_tipo ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>

            <script>
                // Remove a mensagem após 5 segundos
                setTimeout(function() {
                    const msg = document.getElementById('mensagem-feedback');
                    if (msg) msg.remove();
                }, 5000); // 5000ms = 5 segundos
            </script>
        <?php endif; ?>

        <!-- Tabs de Formulário -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="formTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab">Informações Básicas</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="formTabsContent">
                            <!-- Aba de Informações Básicas -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <form action="../models/Matricula.php" method="POST" enctype="multipart/form-data" id="formMatricula">
                                    <style>
                                        .form-floating {
                                            position: relative;
                                            margin-bottom: 1.5rem;
                                        }

                                        .form-floating input,
                                        .form-floating select,
                                        .form-floating textarea {
                                            height: calc(2.5rem + 2px);
                                            padding: 1rem 0.75rem 0 0.75rem;
                                        }

                                        .form-floating label {
                                            position: absolute;
                                            top: 1rem;
                                            left: 0.75rem;
                                            transition: all 0.2s;
                                            pointer-events: none;
                                            color: #6c757d;
                                        }

                                        .form-floating input:focus+label,
                                        .form-floating input:not(:placeholder-shown)+label,
                                        .form-floating select:focus+label,
                                        .form-floating select:not([value=""])+label,
                                        .form-floating textarea:focus+label,
                                        .form-floating textarea:not(:placeholder-shown)+label {
                                            top: -0.5rem;
                                            left: 0.65rem;
                                            font-size: 0.85rem;
                                            color: #495057;
                                            background: #fff;
                                            padding: 0 0.25rem;
                                        }
                                    </style>
                                    <div class="row">
                                        <!-- Dados do Aluno -->
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <!-- Linha 1 -->
                                            <div class="row">
                                                <div class="col-lg-12 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <div class="form-group position-relative">
                                                            <input name="nome_completo" id="nome_completo" type="text" class="form-control" autocomplete="off" required>
                                                            <label for="nome_completo">Nome Completo</label>
                                                            <div id="suggestions" class="list-group position-absolute w-100" style="z-index:1000; display:none;"></div>
                                                        </div>
                                                        <input type="hidden" name="aluno_id" id="aluno_id">

                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <select name="sex" class="form-control" required>
                                                            <option value="" disabled selected>Selecione o sexo</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Feminino">Feminino</option>
                                                        </select>
                                                        <label>Sexo</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input id="birth_date" name="birth_date" type="date" class="form-control" required>
                                                        <label for="birth_date">Data de Nascimento</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 2 -->
                                            <div class="row">
                                                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input id="age" name="age" type="number" class="form-control" min="3" max="25" required>
                                                        <label for="age">Idade</label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <select name="classe" class="form-control" required>
                                                            <option value="" disabled selected>Selecione a classe</option>
                                                            <?php foreach ($classes as $classe): ?>
                                                                <option value="<?= $classe['id_classe'] ?>"><?= htmlspecialchars($classe['nome_classe']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label>Classe</label>
                                                    </div>

                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="religion" type="text" class="form-control">
                                                        <label>Religião Praticada</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Documentos -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <select name="document_type" class="form-control" required>
                                                            <option value="" disabled selected>Selecione</option>
                                                            <option value="BI">BI</option>
                                                            <option value="Cédula Pessoal">Cédula Pessoal</option>
                                                            <option value="DIRE">DIRE</option>
                                                            <option value="Passaporte">Passaporte</option>
                                                        </select>
                                                        <label>Tipo de Documento</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="numero_doc" type="text" class="form-control" required>
                                                        <label>Número do Documento</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 3 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="nationality" type="text" class="form-control" required>
                                                        <label>Nacionalidade</label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="birth_place" type="text" class="form-control" required>
                                                        <label>Naturalidade</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 4 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="neighborhood" type="text" class="form-control" required>
                                                        <label>Bairro</label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <select name="turno_id" class="form-control" required>
                                                            <option value="" disabled selected>Selecione o turno</option>
                                                            <?php foreach ($turnos as $turno): ?>
                                                                <option value="<?= $turno['id'] ?>"><?= htmlspecialchars($turno['nome']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label>Turno</label>
                                                    </div>

                                                </div>
                                            </div>

                                            <!-- Linha adicional para Certificado e Escola de Proveniência -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <select name="tem_certificado" class="form-control">
                                                            <option value="" selected>Selecione</option>
                                                            <option value="Sim">Sim</option>
                                                            <option value="Não">Não</option>
                                                        </select>
                                                        <label>Possui Certificado?</label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="escola_proveniencia" type="text" class="form-control">
                                                        <label>Escola de Proveniência</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Dados dos Pais -->
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <!-- Linha 5 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="father_name" type="text" class="form-control">
                                                        <label>Nome do Pai</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="father_occupation" type="text" class="form-control">
                                                        <label>Profissão do Pai</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 6 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="father_contact" type="tel" class="form-control">
                                                        <label>Contacto do Pai</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="father_workplace" type="text" class="form-control">
                                                        <label>Local de Trabalho do Pai</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 7 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="mother_name" type="text" class="form-control">
                                                        <label>Nome da Mãe</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="mother_occupation" type="text" class="form-control">
                                                        <label>Profissão da Mãe</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 8 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="mother_contact" type="tel" class="form-control">
                                                        <label>Contacto da Mãe</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="mother_workplace" type="text" class="form-control">
                                                        <label>Local de Trabalho da Mãe</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 9 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="education_officer_name" type="text" class="form-control">
                                                        <label>Encarregado de Educação (Nome)</label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="education_officer_contact" type="tel" class="form-control">
                                                        <label>Contacto do Encarregado</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Linha 11 -->
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="form-floating">
                                                        <input name="education_officer_residence" type="text" class="form-control">
                                                        <label>Residência do Encarregado</label>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <!-- Botões -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="payment-adress">
                                                <button type="submit" class="btn btn-primary waves-effect waves-light">Enviar</button>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById("nome_completo");
        const suggestions = document.getElementById("suggestions");

        input.addEventListener("keyup", function() {
            const query = this.value.trim();
            if (query.length < 2) {
                suggestions.style.display = "none";
                return;
            }

            fetch("../models/buscar_aluno.php?q=" + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    suggestions.innerHTML = "";
                    if (data.length === 0) {
                        suggestions.style.display = "none";
                        return;
                    }

                    data.forEach(aluno => {
                        const item = document.createElement("a");
                        item.className = "list-group-item list-group-item-action";
                        item.textContent = aluno.nome_completo;
                        item.href = "#";

                        item.addEventListener("mousedown", function(e) {
                            // usa mousedown em vez de click para funcionar antes do blur
                            e.preventDefault();
                            preencherCampos(aluno);
                            suggestions.style.display = "none"; // oculta imediatamente
                        });

                        suggestions.appendChild(item);
                    });
                    suggestions.style.display = "block";
                });
        });

        // Ocultar sugestões ao clicar fora do input
        document.addEventListener("click", function(e) {
            if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.style.display = "none";
            }
        });

        function preencherCampos(aluno) {
            document.getElementById("aluno_id").value = aluno.id;
            input.value = aluno.nome_completo;
            document.querySelector("select[name='sex']").value = aluno.sexo;
            document.querySelector("input[name='birth_date']").value = aluno.data_nascimento;
            document.querySelector("input[name='age']").value = aluno.idade;
            document.querySelector("select[name='classe']").value = aluno.classe_id;
            document.querySelector("select[name='turno_id']").value = aluno.turno_id;
            document.querySelector("input[name='religion']").value = aluno.religiao || '';
            document.querySelector("input[name='numero_doc']").value = aluno.numero_doc || '';
            document.querySelector("select[name='tipo_documento']").value = aluno.tipo_documento || '';
            document.querySelector("input[name='naturalidade']").value = aluno.naturalidade || '';
            document.querySelector("input[name='nacionalidade']").value = aluno.nacionalidade || '';
            document.querySelector("input[name='residente_em']").value = aluno.residente_em || '';
            document.querySelector("input[name='bairro']").value = aluno.bairro || '';
            document.querySelector("input[name='nome_pai']").value = aluno.nome_pai || '';
            document.querySelector("input[name='profissao_pai']").value = aluno.profissao_pai || '';
            document.querySelector("input[name='contacto_pai']").value = aluno.contacto_pai || '';
            document.querySelector("input[name='local_trabalho_pai']").value = aluno.local_trabalho_pai || '';
            document.querySelector("input[name='nome_mae']").value = aluno.nome_mae || '';
            document.querySelector("input[name='profissao_mae']").value = aluno.profissao_mae || '';
            document.querySelector("input[name='contacto_mae']").value = aluno.contacto_mae || '';
            document.querySelector("input[name='local_trabalho_mae']").value = aluno.local_trabalho_mae || '';
            document.querySelector("input[name='nome_encarregado']").value = aluno.nome_encarregado || '';
            document.querySelector("input[name='profissao_encarregado']").value = aluno.profissao_encarregado || '';
            document.querySelector("input[name='contacto_encarregado']").value = aluno.contacto_encarregado || '';
            document.querySelector("input[name='residencia_encarregado']").value = aluno.residencia_encarregado || '';
        }
    });



    document.addEventListener("DOMContentLoaded", function() {
        const birthDateInput = document.getElementById("birth_date");
        const ageInput = document.getElementById("age");

        birthDateInput.addEventListener("change", function() {
            const birthDate = new Date(this.value);
            if (!isNaN(birthDate)) {
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageInput.value = age;
            } else {
                ageInput.value = "";
            }
        });
    });

    if (window.history.replaceState) {
        // Remove o ?status= da URL sem recarregar a página
        window.history.replaceState(null, null, window.location.pathname);
    }
</script>


<?php require_once __DIR__ . '/partials/footer.php'; ?>