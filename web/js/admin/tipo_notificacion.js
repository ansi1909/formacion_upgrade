$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.new').click(function(){
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#tipo_notificacion_id').val("");
		$('#tipo_notificacion').val("");
		$('#descripcion').val("");
		$('#div-alert').hide();
	});


	$('#guardar').click(function(){
		savetipo_notificacion();
	});

	$('#form').submit(function(e)
	{
		e.preventDefault();
		savetipo_notificacion();
	});

	$('.edit').click(function(){
		var tipo_notificacion_id = $(this).attr('data');
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
			data: { tipo_notificacion_id: tipo_notificacion_id },
			dataType: "json",
			success: function(data) {
				$('#tipo_notificacion_id').val(tipo_notificacion_id);
				$('#tipo_notificacion').val(data.nombre);
			},
			error: function(){
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});
	 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var tipo_notificacion_id = $(this).attr('data');
		sweetAlertDelete(tipo_notificacion_id, 'AdminTipoNotificacion');
	});

});

function savetipo_notificacion()
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
					$('#p-des').html(data.descripcion);
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
							var tipo_notificacion_id= $(this).attr('data');
							sweetAlertDelete(tipo_notificacion_id, 'AdminTipoNotificacion');
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