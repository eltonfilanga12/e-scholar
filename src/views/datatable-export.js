/**
 * datatable-export.js
 * Inicializa DataTables com botões de exportação
 * @param tableId = ID da tabela HTML
 */
function initDataTableExport(tableId) {
    $('#' + tableId).DataTable({
        dom: 'Bfrtip', // exibe botões
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        buttons: [
            {
                extend: 'csv',
                text: 'Exportar CSV',
                bom: true, // garante UTF-8 no Excel
                exportOptions: {
                    columns: ':visible' // exporta apenas colunas visíveis
                }
            },
            {
                extend: 'excel',
                text: 'Exportar Excel',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: 'Exportar PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ]
    });
}
