$(document).ready(function() {

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
					console.log('Formulario enviado');
				},
				error: function(){
					console.log('Error al enviar el formulario');
				}
			});
		}
	});

	$('.edit').click(function(){
		var app_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$('#guardar').prop('disabled', false);
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { app_id: app_id },
			dataType: "json",
			success: function(data) {
				$('#app_id').val(app_id);
				$('#nombre').val(data.nombre);
				$('#url').val(data.url);
				$('#icono').val(data.icono);
				$('#activo').prop('checked', data.activo);
				$('#subaplicacion_id').html(data.subaplicaciones);
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

	$('.new').click(function(){
		$('#app_id').val("");
		$('#nombre').val("");
		$('#url').val("");
		$('#icono').val("");
		$('#subaplicacion_id').html($('#aplicaciones_str').val());
	});

});
