$(document).ready(function() {

    $('#empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
		getListadoGrupos(empresa_id);
	});

	$('.new').click(function(){
		var empresa_id =$('#empresa_id').val();
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#grupo_id').val("");
		$('#nombre').val("");
		$('#id_empresa').val(empresa_id);
		$('#div-alert').hide();
	});

	$('#guardar').click(function(){
		saveGrupo();
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var grupo_id = $(this).attr('data');
		sweetAlertDelete(grupo_id, 'CertiGrupo');
	});

	var table = $('#dt').DataTable( {
		destroy: true,
        rowReorder: true

    } );

	table.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'CertiGrupo', diff[i].newData);
        }
 
    }); 

    $('.edit').click(function(){
		var grupo_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$('#guardar').prop('disabled', false);
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#div-alert').hide();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { grupo_id: grupo_id },
			dataType: "json",
			success: function(data) {
				$('#grupo_id').val(grupo_id);
				$('#nombre').val(data.nombre);
				$('#id_empresa').val(data.empresa_id);
				$('#orden').val(data.orden);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var grupo_id = $(this).attr('data');
		sweetAlertDelete(grupo_id, 'CertiGrupo');
	});

});


function getListadoGrupos(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_grupos').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#lpe').html(data.grupos);
			$('#id_empresa').val(empresa_id);
			$('#new').removeClass("ocultar");

			var table = $('#dt').DataTable( {
				destroy: true,
		        rowReorder: true

		    } );

			table.on( 'row-reorder', function ( e, diff, edit ) {
		        
		        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
		            var rowData = table.row( diff[i].node ).data();
		            // Id del registro está en la segunda columna
		        	id = rowData[1];
		            reordenar(id, 'CertiGrupo', diff[i].newData);
		        }
		 
		    }); 

		    $( ".columorden" )
		          .mouseover(function() {
		            $( '.columorden' ).css( 'cursor','move' );
		          })
		          .mouseout(function() {
		            $( '.columorden' ).css( 'cursor','auto' );
		    });

				},
				error: function(){
					$('#active-error').html($('#error_msg-filter').val());
					$('#div-active-alert').show();
				}
			});

}

function saveGrupo()
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
					$('#p-nombre').html(data.nombre);
					console.log('Formulario enviado. Id '+data.id);
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
						$('.delete').click(function()
						{
							var grupo_id= $(this).attr('data');
							sweetAlertDelete(grupo_id, 'CertiGrupo');
						});
					}
					$('#form').hide();
					$('#alert-success').show();
					$('#detail').show();
					$('#aceptar').show();
					$('#guardar').hide();
					$('#cancelar').hide();
				},
				error: function(){
					$('#alert-error').html($('#error_msg-save').val());
					$('#div-alert').show();
				}
			});
		}
}

