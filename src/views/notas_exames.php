<?php


require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Minhas Notas</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Principal</a></li>
                        <li class="breadcrumb-item active"> Notas de Exame</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"> Notas de Exame</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="ano_lectivo">Ano Letivo</label>
                                <select id="ano_lectivo" class="form-control">
                                    <option value="">Selecione o ano</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="trimestre">Trimestre</label>
                                <select id="trimestre" class="form-control">
                                    <option value="">Selecione o trimestre</option>
                                    <option value="1">1º Trimestre</option>
                                    <option value="2">2º Trimestre</option>
                                    <option value="3">3º Trimestre</option>
                                </select>
                            </div>
                        </div>


                        <div class="table-responsive">
                            <table id="notas-table" class="display table table-striped table-hover">
                                <thead id="exame-thead"></thead>
                                <tbody id="exame-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Table -->

    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->

<!-- ... seu código anterior permanece igual até o script ... -->
<script>
    // Preencher anos automaticamente e selecionar o atual
    const anoSelect = document.getElementById('ano_lectivo');
    const anoAtual = new Date().getFullYear();

    anoSelect.innerHTML = '<option value="">Selecione o ano</option>';
    for (let ano = anoAtual; ano >= 2020; ano--) {
        const option = document.createElement('option');
        option.value = ano;
        option.textContent = ano;
        if (ano === anoAtual) {
            option.selected = true;
        }
        anoSelect.appendChild(option);
    }

    // Disparar o evento de change automaticamente
    anoSelect.dispatchEvent(new Event('change'));
</script>

<script>


    function carregarNotas() {
        const ano = document.getElementById('ano_lectivo').value;
        const trimestre = document.getElementById('trimestre').value;

        if (ano && trimestre) {
            fetch('api/carregar_notas_exame.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ano_lectivo: ano,
                        trimestre: trimestre
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const thead = document.getElementById('exame-thead');
                    const tbody = document.getElementById('exame-tbody');
                    thead.innerHTML = '';
                    tbody.innerHTML = '';

                    if (data.success) {
                        thead.innerHTML = `
                        <tr>
                            <th>Disciplina</th>
                            <th>Média Freq</th>
                            <th>Exame Normal</th>
                            <th>Exame Recor.</th>
                            <th>Média Final</th>
                            <th>Resultado Final</th>
                        </tr>
                    `;

                        data.notas.forEach(nota => {
                            let resultado = 'Reprovado';
                            if (nota.mediafinal !== null && nota.mediafinal >= 10) {
                                resultado = 'Aprovado';
                            }

                            tbody.innerHTML += `
                            <tr>
                                <td>${nota.disciplina}</td>
                                <td>${nota.mediafreq ?? '-'}</td>
                                <td>${nota.examenormal ?? '-'}</td>
                                <td>${nota.examerecorrencia ?? '-'}</td>
                                <td>${nota.mediafinal ?? '-'}</td>
                                <td>${resultado}</td>
                            </tr>
                        `;
                        });
                    } else {
                        thead.innerHTML = '<tr><th>Erro</th></tr>';
                        tbody.innerHTML = `<tr><td>${data.message}</td></tr>`;
                    }
                });
        }
    }

    document.getElementById('ano_lectivo').addEventListener('change', carregarNotas);
    document.getElementById('trimestre').addEventListener('change', carregarNotas);
</script>



<?php require_once __DIR__ . '/partials/footer.php'; ?>