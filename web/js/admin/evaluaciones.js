$(document).ready(function() {
	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true,
        responsive: false,
        pageLength:10,
        sPaginationType: "full_numbers",
        lengthChange: false,
        info: false,
        fnDrawCallback: function(){
         observe();
        },
        oLanguage: {
            "sProcessing":    "Procesando...",
            "sLengthMenu":    "'Mostrar _MENU_ registros",
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
    } );





});

function observe(){
    $('.view').unbind().click(function(e) {
        e.preventDefault();
        var prueba_id = $(this).attr('data');
        $("#view"+prueba_id).hide();
        $("#prueba-loader"+prueba_id).show();
        $.ajax({
           type:"POST",
           url: $('#url_preguntas').val(),
           async: true,
           data: { prueba_id: prueba_id },
           dataType: "json",
           success: function(data){
                enableSubmit();
                $("#view"+prueba_id).show();
                $("#prueba-loader"+prueba_id).hide()
                $("#formPreguntaLabel").text(data.evaluacion);
                $('#lista-preguntas').html(data.html);
                $('#modalPreguntas').modal("show");
           },
           error: function(){
                $('#alert-error').html($('#error_msg-edit').val());
                $('#div-alert').show();
           }
        });
    });

	$('.delete').click(function(){
		var prueba_id = $(this).attr('data');
		sweetAlertDelete(prueba_id, 'CertiPrueba');
	});
}
