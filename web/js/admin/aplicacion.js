$(document).ready(function() {

	$('#guardar').click(function(){
		$("#form").valid();
	});

	$('.edit').click(function(){
		var app_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: false,
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

	jQuery.validator.addMethod("noSpace", function(value, element) { 
	 	return value.indexOf(" ") < 0 && value != ""; 
	}, "No se permiten espacios en blanco");

	$("#form").validate({
		rules: {
			'nombre': {
				required: true,
				minlength: 3
			},
			'url': {
				noSpace: true
			},
			'icono': {
				noSpace: true
			}
		},
		messages: {
			'nombre': {
				required: "El nombre de la aplicación es requerido.",
				minlength: "El nombre de la aplicación debe ser mínimo de 3 caracteres."
			}
		},
		submitHandler: function(form, event) {

			var error = 0;
			event.preventDefault();
			$('#btn').hide();
			
			if (usuario_id == '')
			{
				var login = $('#login').val();
				$.ajax({
					type: "GET",
					url: url_validUser,
					async: false,
					data: { login: login },
					dataType: "json",
					success: function(data) {
						if (data.ok != 1)
						{
							error = 1;
							$('#usuario-error').html('Usuario existente');
							$('#usuario-error').show();
							$('#btn').show();
						}
					},
					error: function(){
						error = 1;
						$('#usuario-error').html('Ha ocurrido un error validando el usuario. Contacte con el Administrador.');
						$('#usuario-error').show();
						$('#btn').show();
					}
				});
			}
			
			if (error == 0)
			{
				form.submit();
			}
			
		}
	});

});
