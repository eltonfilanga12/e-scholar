</div>
<!-- /Main Wrapper -->

<!-- jQuery -->
<script src="../../public/js/jquery-3.5.1.min.js"></script>

<!-- Bootstrap Core JS -->
<script src="../../public/js/popper.min.js"></script>
<script src="../../public/js/bootstrap.min.js"></script>

<!-- Slimscroll JS -->
<script src="../../public/js/jquery.slimscroll.min.js"></script>

<!-- Chart JS -->
<script src="../../public/plugins/morris/morris.min.js"></script>
<script src="../../public/plugins/raphael/raphael.min.js"></script>
<script src="../../public/js/chart.js"></script>

<!-- wow JS
		============================================ -->
<script src="../../public/js/wow.min.js"></script>
<!-- meanmenu JS
		============================================ -->
<script src="../../public/js/jquery.meanmenu.js"></script>
<!-- tab JS
		============================================ -->
<script src="../../public/js/tab.js"></script>
<!-- plugins JS
		============================================ -->
<script src="../../public/js/plugins.js"></script>
<!-- main JS
		============================================ -->
<script src="../../public/js/main.js"></script>

<!-- dropzone JS
		============================================ -->
<script src="../../public/js/dropzone/dropzone.js"></script>


</script>


<script>


    $(document).ready(function() {
        $('.detalhes-aluno').on('click', function() {
            var aluno_id = $(this).data('aluno_id');
            $('#detalhesModal').modal('show');
            $('#detalhes-conteudo').html('<p>Carregando...</p>');

            $.ajax({
                url: 'detalhes_aluno.php',
                type: 'GET',
                data: {
                    aluno_id: aluno_id
                },
                success: function(response) {
                    $('#detalhes-conteudo').html(response);
                },
                error: function() {
                    $('#detalhes-conteudo').html('<p>Erro ao carregar dados.</p>');
                }
            });
        });
    });
</script>
<!-- Datatable JS -->

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('toggle_btn');
    const logoImg = document.getElementById('logo-img');

    const originalLogo = "../../public/img/logoSchol.png"; // imagem normal
    const alternateLogo = "../../public/img/graduado.png"; // imagem alternativa (adicione essa imagem na pasta!)

    let isAltLogo = false;

    toggleBtn.addEventListener('click', () => {
      if (isAltLogo) {
        logoImg.src = originalLogo;
      } else {
        logoImg.src = alternateLogo;
      }
      isAltLogo = !isAltLogo;
    });
  });
</script>


<script>
  $(document).ready(function() {
    $("#basic-datatables").DataTable({});

    $("#multi-filter-select").DataTable({
      pageLength: 5,
      initComplete: function() {
        this.api()
          .columns()
          .every(function() {
            var column = this;
            var select = $(
                '<select class="form-select"><option value=""></option></select>'
              )
              .appendTo($(column.footer()).empty())
              .on("change", function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());

                column
                  .search(val ? "^" + val + "$" : "", true, false)
                  .draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function(d, j) {
                select.append(
                  '<option value="' + d + '">' + d + "</option>"
                );
              });
          });
      },
    });

    // Add Row
    $("#add-row").DataTable({
      pageLength: 5,
    });

    var action =
      '<td> <div class="form-button-action"> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"> <i class="fa fa-edit"></i> </button> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove"> <i class="fa fa-times"></i> </button> </div> </td>';

    $("#addRowButton").click(function() {
      $("#add-row")
        .dataTable()
        .fnAddData([
          $("#addName").val(),
          $("#addPosition").val(),
          $("#addOffice").val(),
          action,
        ]);
      $("#addRowModal").modal("hide");
    });
  });
</script>

<script>
  $(document).ready(function() {
    // Ao clicar no botão Adicionar
    $('[data-target="#modalAddUsuario"]').on('click', function() {
      const isEdit = $(this).hasClass('btn-editar');

      if (isEdit) {
        // Editar
        const id = $(this).data('id');
        const nome = $(this).data('nome');
        const tipo = $(this).data('tipo');
        const status = $(this).data('status');

        $('#modalAddUsuarioLabel').text('Editar Usuário');
        $('#formUsuario').attr('action', 'editar_usuario.php');
        $('#usuario_id').val(id);
        $('[name="nome"]').val(nome);
        $('[name="tipo_usuario"]').val(tipo);
        $('[name="user_status"]').val(status);
        $('[name="senha"]').val(''); // limpa campo senha
      } else {
        // Adicionar
        $('#modalAddUsuarioLabel').text('Adicionar Novo Usuário');
        $('#formUsuario').attr('action', 'adduser.php');
        $('#usuario_id').val('');
        $('[name="nome"]').val('');
        $('[name="tipo_usuario"]').val('');
        $('[name="user_status"]').val('Ativo');
        $('[name="senha"]').val('');
      }
    });

    // Ao fechar o modal, limpa tudo
    $('#modalAddUsuario').on('hidden.bs.modal', function() {
      $('#formUsuario')[0].reset();
      $('#usuario_id').val('');
      $('#modalAddUsuarioLabel').text('Adicionar Novo Usuário');
      $('#formUsuario').attr('action', 'adduser.php');
    });
  });
</script>

<script src="../../public/js/plugin/datatables/datatables.min.js"></script>
<script src="../../public/js/dataTables.bootstrap4.min.js"></script>



<!-- Custom JS -->
<script src="../../public/js/app.js"></script>

<script>
  function abrirModalAdicionar() {
    document.getElementById('modalDisciplinaLabel').innerText = 'Adicionar Nova Disciplina';
    document.getElementById('disciplinaId').value = '';
    document.getElementById('disciplinaNome').value = '';
    document.getElementById('disciplinaCodigo').value = '';
    document.getElementById('disciplinaStatus').value = 'Ativa';
  }

  function abrirModalEditar(id, nome, codigo, status) {
    document.getElementById('modalDisciplinaLabel').innerText = 'Editar Disciplina';
    document.getElementById('disciplinaId').value = id;
    document.getElementById('disciplinaNome').value = nome;
    document.getElementById('disciplinaCodigo').value = codigo;
    document.getElementById('disciplinaStatus').value = status;
  }

  document.querySelectorAll('.btn-editar-turma').forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById('turmaId').value = button.getAttribute('data-id');
      document.getElementById('turmaNome').value = button.getAttribute('data-nome');
      document.getElementById('turmaClasse').value = button.getAttribute('data-classe');
      document.getElementById('turmaTurno').value = button.getAttribute('data-turno');
      document.getElementById('turmaAno').value = button.getAttribute('data-ano');
      document.getElementById('turmaStatus').value = button.getAttribute('data-status');
      document.getElementById('turmaAcao').value = 'editar';
    });
  });
</script>

<script>
  function editarTurma(id, nome, classeId, turno, ano, status) {
    document.getElementById('modalTurmaLabel').innerText = 'Editar Turma';
    document.getElementById('turmaId').value = id;
    document.getElementById('turmaNome').value = nome;
    document.getElementById('turmaClasse').value = classeId;
    document.getElementById('turmaTurno').value = turno;
    document.getElementById('turmaAno').value = ano;
    document.getElementById('turmaStatus').value = status;
    document.getElementById('turmaAcao').value = 'editar';
    $('#modalTurma').modal('show');
  }
</script>




</body>

</html>