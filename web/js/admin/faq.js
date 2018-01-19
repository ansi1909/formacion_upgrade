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
		$('#faq_id').val("");
		$('#pregunta').val("");
		$('#respuesta').val("");
		$('#tipo_pregunta_id').val("");
		$('#div-alert').hide();
	});

	afterPaginate();

	$('#guardar').click(function(){
		saveFaq();
	});

	$('#form').submit(function(e)
	{
		e.preventDefault();
		saveRol();
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
		$('#div-alert').hide();
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
				$('alert-error').html($('#error_msg_edit').val());
				$('#div-alert').show();
			}
		});
	});
	 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var rol_id = $(this).attr('data');
		sweetAlertDelete(rol_id, 'AdminRol');
	});

});

function observe()
{

	$('.edit').click(function(){
		var faq_id = $(this).attr('data');
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
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var app_id = $(this).attr('data');
		sweetAlertDelete(app_id, 'AdminAplicacion');
	});

}

function saveFaq()
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
					$('#p-pregunta').html(data.pregunta);
					$('#p-respuesta').html(data.respuesta);
					$('#p-t').html(data.tipo_pregunta);
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
							var rol_id= $(this).attr('data');
							sweetAlertDelete(rol_id, 'AdminRol');
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

function afterPaginate(){
	$('.see').click(function(){
		var faq_id = $(this).attr('data');
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_respuesta').val(),
			async: true,
			data: { faq_id: faq_id },
			dataType: "json",
			success: function(data) {
				$('#respuesta').html(data.respuesta);
				$('#div-respuesta').show();
				observe();
			},
			error: function(){
				$('#active-error').html($('#error_msg-subapps').val());
				$('#div-active-alert').show();
				$('#div-respuesta').hide();
			}
		});
	});