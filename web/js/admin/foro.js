$(document).ready(function() {

    $('#select_empresa_id').change(function(){
    	var empresa_id = $(this).val();
    	var pagina_id = 0;
    	var usuario_id = $('#usuario_id').val();
		getPaginas(empresa_id);
		getListadoComentarios(empresa_id,pagina_id,usuario_id);
	});

	$('#select_pagina_id').change(function(){
		var pagina_id = $(this).val();
		var empresa_id = $('#select_empresa_id').val();
		var usuario_id = $('#usuario_id').val();
		getListadoComentarios(empresa_id,pagina_id,usuario_id);
	});

	$('#guardar').click(function(){
		var foro_id = $('#comentario_padre_foro_id').val();
		$('#foro_id').val(foro_id);
		$('#guardar').hide();
		$('#cancelar').hide();
		saveComentario();
	});

	$('#form').submit(function(e)
	{
		var foro_id = $('#comentario_padre_foro_id').val();
		$('#foro_id').val(foro_id);
		$('#guardar').hide();
		$('#cancelar').hide();
		e.preventDefault();
		saveComentario();
	});

	$('.delete').click(function(){
		var comentario_id = $(this).attr('data');
		sweetAlertDelete(comentario_id, 'CertiForo');
	});

	observe();
	editComentario();
	segundaTabla();
});

function getPaginas(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_paginas_foro').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#select_pagina_id').html(data.options);
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function getListadoComentarios(empresa_id,pagina_id,usuario_id){
	$.ajax({
		type: "GET",
		url: $('#url_comentarios_foro').val(),
		async: true,
		data: { pagina_id: pagina_id, empresa_id: empresa_id, usuario_id: usuario_id },
		dataType: "json",
		success: function(data) {
			$('#list_comentarios').html(data.html);
			applyDataTable();
			observe();
			clearTimeout( timerId );
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function observe(){

	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true

    } );

    table.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'AdminNotificacion', diff[i].newData);
        }
 
    });

    $( ".columorden" )
          .mouseover(function() {
            $( '.columorden' ).css( 'cursor','move' );
          })
          .mouseout(function() {
            $( '.columorden' ).css( 'cursor','auto' );
    });

    afterPaginate();

	$('.paginate_button').click(function(){
		afterPaginate();
	});

	editComentario();

}

function editComentario(){

	$('.add').click(function(){
        var foro_id = $(this).attr('data');
        $('#foro_id').val(foro_id);
        $('#respuesta').val('');
        $('#comentario_id').val('');

    });

	$('.edit').click(function(){
        var comentario_id = $(this).attr('data');
        var respuesta = $('.respuesta' + comentario_id).html();
        $('#comentario_id').val(comentario_id);
        $('#respuesta').val(respuesta);

    });

	$('.delete').click(function(){
		var comentario_id = $(this).attr('data');
		sweetAlertDelete(comentario_id, 'CertiForo');
	});
}

function saveComentario()
{
	$('#div-alert').hide();
		if ($("#form").valid())
		{
			$('#guardar').prop('disabled', true);
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$( "#detail-edit" ).attr( "data", data.id );
					if (data.delete_disabled != '') 
					{
						$("#detail-delete").hide();
						$("#detail-delete").removeClass( "delete" );
					}
					else
					{
						$( "#detail-delete" ).attr("data",data.id);
						$( "#detail-delete" ).addClass("delete");
						$( "#detail-delete" ).show();
					}
					$('#form').hide();
					$('#alert-success').show();
					$('#detail').show();
					$('#aceptar').show();
					$('#guardar').hide();
					$('#cancelar').hide();
					clearTimeout( timerId );
				},
				error: function(){
					$('#guardar').prop('disabled', false);
					$('#alert-error').html($('#error_msg-save').val());
					$('#div-alert').show();
				}
			});
		}
}

function segundaTabla()
{
	var table2 = $('#dtSub').DataTable( {
        responsive: false,
        pageLength:10,
        destroy: true,
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

    } );

    table2.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table2.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'CertiForo', diff[i].newData);
        } 
    });

}

function afterPaginate(){

	editComentario();

    $('.see').click(function(){
        var foro_id = $(this).attr('data');
        var usuario_id = $('#usuario_id').val();
        $('#div-active-alert').hide();
        $('#tbody_history_programation').hide();
        $('#loading').show();
        $.ajax({
            type: "GET",
            url: $('#url_respuestas_comentarios_foro').val(),
            async: true,
            data: { foro_id: foro_id, usuario_id: usuario_id },
            dataType: "json",
            success: function(data) {
                $('#tbody_history_programation').html(data.html);
                $('#tbody_history_programation').show();
                $('#loading').hide();
                segundaTabla();
                editComentario();
                clearTimeout( timerId );
            },
            error: function(){
                $('#active-error').html($('#error_msg_history').val());
                $('#div-active-alert').show();
            }
        });
        
    });
}