$(document).ready(function() {

	$('.new').click(function(){
			$('label.error').hide();
			$('#form').show();
			$('#alert-success').hide();
			$('#detail').hide();
			$('#aceptar').hide();
			$('#guardar').show();
			$('#cancelar').show();
			$('#rol_id').val("");
			$('#rol').val("");
			$('#descripcion').val("");
			});


	$('#guardar').click(function(){
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
					$('#p-des').html(data.descripcion);
					console.log('Formulario enviado. Id '+data.id);
					$( "#detail-edit" ).attr( "data", data.id );
					$( "#detail-delete" ).attr( "data", data.id );
					$('#form').hide();
					$('#alert-success').show();
					$('#detail').show();
					$('#aceptar').show();
					$('#guardar').hide();
					$('#cancelar').hide();
				},
				error: function(){
					console.log('Error al enviar el formulario');
				}
			});
		}
	});

	$('.edit').click(function(){
		var rol_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$('#guardar').prop('disabled', false);
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { rol_id: rol_id },
			dataType: "json",
			success: function(data) {
				$('#rol_id').val(rol_id);
				$('#rol').val(data.nombre);
				$('#descripcion').val(data.descripcion);
			},
			error: function(){
				console.log('Error al editar');
				/*$( "#dialog-delete" ).dialog( "close" );
				$("#texto-msg").html('Ha ocurrido un error al momento de eliminar el servicio. Contacte al Administrador del Sistema.');
				$( "#dialog" ).dialog('option', 'width', 400);
				$( "#dialog" ).dialog('option', 'title', 'Mensaje del Servidor');
				$( "#dialog" ).dialog( "open" );*/
			}
		});
	});
	 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

});