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
					$('#p-nombre').html(data.nombre);
					$('#p-url').html(data.url);
					$('#p-icono').html(data.icono);
					$('#p-activo').html(data.activo);
					$('#p-subaplicacion').html(data.subaplicacion);
					if (data.subaplicacion_id)
					{
						$('#div-subaplicacion').show();
					}
					else {
						$('#div-subaplicacion').hide();
					}
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

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.edit').click(function(){
		var app_id = $(this).attr('data');
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
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#app_id').val("");
		$('#nombre').val("");
		$('#url').val("");
		$('#icono').val("");
		$('#subaplicacion_id').html($('#aplicaciones_str').val());
	});

	$('.cb_activo').click(function(){
		var checked = $(this).is(':checked') ? 1 : 0;
		var id = $(this).attr('id');
		var id_arr = id.split('f');
		var app_id = id_arr[1];
		$.ajax({
			type: "POST",
			url: $('#url_active').val(),
			async: true,
			data: { app_id: app_id, checked: checked },
			dataType: "json",
			success: function(data) {
				console.log('Activación/Desactivación realizada. Id '+data.id);
			},
			error: function(){
				console.log('Error al activar o desactivar la aplicación');
			}
		});
	});

});
