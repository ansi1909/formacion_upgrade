$(document).ready(function() {
    
    $('.delete').click(function(){
        var noticia_id = $(this).attr('data');
        sweetAlertDelete(noticia_id, 'AdminNoticia');
    });

    $('.iframe-btn').fancybox({ 
        'width'     : 900,
        'height'    : 900,
        'type'      : 'iframe',
        'autoScale' : false,
        'autoSize'  : false
    });

    activarDataTable();
    paginateScroll();
    
});

function paginateScroll() 
{
    $('html, body').animate({
       scrollTop: $(".card-block").offset().top
    }, 100);
    console.log('pagination button clicked');
    $(".paginate_button").unbind('click', paginateScroll);
    $(".paginate_button").bind('click', paginateScroll);
}

function activarDataTable() 
{
    $('#listado_noticias').DataTable({
        responsive: true,
        pageLength:10,
        sPaginationType: "full_numbers",
        oLanguage: {
            "sProcessing":    "Procesando...",
            "sLengthMenu":    "Mostrar _MENU_ registros",
            "sZeroRecords":   "No se encontraron resultados",
            "sEmptyTable":    "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_.",
            "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "sSearch":        "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            oPaginate: {
                sFirst: "<<",
                sPrevious: "<",
                sNext: ">", 
                sLast: ">>" 
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
}