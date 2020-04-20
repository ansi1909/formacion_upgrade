$(document).ready(function() {
	

    $('#empresa_id').change(function(){
    	var empresa_id = $(this).val();
    	var pagina_id = 0;
    	var usuario_id = $('#usuario_id').val();
    	$('#div-active-alert').hide();
    	$('#loader').show();
		$('#list_comentarios').hide();
        $('#tbody_history_programation').hide();
        $('#loader2').show();
    	window.location.replace($('#url_auto').val()+'/'+$('#empresa_id').val());
	});

	$('.tree').jstree();

	$('.tree').on("select_node.jstree", function (e, data) {
		var usuario_id = $('#usuario_id').val();
		var empresa_id = $('#empresa_id').val();
		var id = data.node.id;
		var pagina_id = $('#'+id).attr('p_id');
		var pagina_str = $('#'+id).attr('p_str');
		$('#pagina_str').val(pagina_str);
		$('#pagina_id').val(pagina_id);
		getListadoComentarios(empresa_id,pagina_id,usuario_id)
	});


	$('#guardar').click(function(){
		$('#guardar').hide();
		$('#cancelar').hide();
		saveComentario();
	});

	$('#form').submit(function(e)
	{
		var muro_id = $('#comentario_padre_muro_id').val();
		$('#muro_id').val(muro_id);
		$('#guardar').hide();
		$('#cancelar').hide();
		e.preventDefault();
		saveComentario();
	});

	$('.delete').click(function(){
		var comentario_id = $(this).attr('data');
		sweetAlertDelete(comentario_id, 'CertiMuro');
	});

	observe();
	editComentario();
	segundaTabla();
});



function getListadoComentarios(empresa_id,pagina_id,usuario_id){
	$('#loader').show();
	$('#list_comentarios').hide();
	$('#div-active-alert').hide();
    $('#panel_muro').hide();
    $('#tbody_history_programation').hide();
    $('#loading').show();
	$.ajax({
		type: "GET",
		url: $('#url_comentarios_muro').val(),
		async: true,
		data: { pagina_id: pagina_id, empresa_id: empresa_id, usuario_id: usuario_id },
		dataType: "json",
		success: function(data) {
			$('#list_comentarios').html(data.html);
			aplicarDataTable();
			observe();
			clearTimeout( timerId );
			$('#loader').hide();
			$('#list_comentarios').show();
			observe();
		
		},
		error: function(){
			$('#loader').hide();
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

	$('.add').unbind('click');
	$('.add').click(function(){
		var muro_id = $(this).attr('data');
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
        $('#muro_id').val(muro_id);
        $('#respuesta').val('');
        $('#comentario_id').val('');
        $('#exampleModalLongTitle').html('Responder comentario');
		$('#asunto').html('Respuesta');
		$('#guardar').prop('disabled', false);
    });

	$('.edit').unbind('click');
	$('.edit').click(function(){
        var comentario_id = $(this).attr('data');
        var respuesta = $('.respuesta' + comentario_id).html();
        $('#comentario_id').val(comentario_id);
        $('#respuesta').val(respuesta);
        $('#exampleModalLongTitle').html('Editar comentario');
        $('#asunto').html('Comentario');
    });

	$('.delete').unbind('click');
	$('.delete').click(function(){
		var comentario_id = $(this).attr('data');
		sweetAlertDelete(comentario_id, 'CertiMuro');
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
            reordenar(id, 'CertiMuro', diff[i].newData);
        } 
    });

}

function afterPaginate(){

	editComentario();

	$('.see').unbind('click');
    $('.see').click(function(){
        var muro_id = $(this).attr('data');
        var usuario_id = $('#usuario_id').val();
        $('#div-active-alert').hide();
        $('#panel_muro').hide();
        $('#tbody_history_programation').hide();
        $('#loader2').show();
        $.ajax({
            type: "GET",
            url: $('#url_respuestas_comentarios_muro').val(),
            async: true,
            data: { muro_id: muro_id, usuario_id: usuario_id },
            dataType: "json",
            success: function(data) {
            	$('#panel_muro').html(data.panel);
                $('#panel_muro').show();
                $('#tbody_history_programation').html(data.html);
                $('#tbody_history_programation').show();
                $('#loader2').hide();
                segundaTabla();
                editComentario();
                clearTimeout( timerId );
            },
            error: function(){
            	$('#loader2').hide();
                $('#active-error').html($('#error_msg_history').val());
                $('#div-active-alert').show();
            }
        });
        
    });

}

function aplicarDataTable(){
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
}