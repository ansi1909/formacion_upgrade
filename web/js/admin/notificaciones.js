$(document).ready(function() {

	var root_site = $('#root_site').val();
    $('#select_empresa_id').change(function(){
    	console.log('Usuario admin');
    	var empresa_id = $(this).val();
		getListadoNotificaciones(empresa_id);
		$('#history_programation').hide();
		
	});
	$('#guardar').click(function(){
		saveNotificacion();
	});

	$('#form').submit(function(e)
	{
		e.preventDefault();
		saveNotificacion();
	});

	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.delete').click(function(){
		var rol_id = $(this).attr('data');
		sweetAlertDelete(rol_id, 'AdminNotificacion');
	});

	CKEDITOR.replace( 'mensaje', {
		filebrowserBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/notificaciones',
		filebrowserUploadUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=2&editor=ckeditor&fldr=recursos/notificaciones',
		filebrowserImageBrowseUrl : root_site+'/jq/ResponsiveFilemanager/filemanager/dialog.php?type=1&editor=ckeditor&fldr=recursos/notificaciones',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.mensaje.getData();
				var elem = document.getElementById("mensaje");
				//elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
				elem.value = editor_data;
			},
			key: function() {
				var editor_data = CKEDITOR.instances.mensaje.getData();
				var elem = document.getElementById("mensaje");
				//elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
				elem.value = editor_data;
			}
		}
	} );

	$('.new').click(function(){
		$('label.error').hide();
		$('#form').show();
		$('#alert-success').hide();
		$('#detail').hide();
		$('#aceptar').hide();
		$('#guardar').show();
		$('#cancelar').show();
		$('#notificacion_id').val("");
		$('#asunto').val("");
		$('#mensaje').val("");
		CKEDITOR.instances.mensaje.setData('')
		$('#form_empresa_id').val("");
		$('#tipo_notificacion_id').val("");
		$('#div-alert').hide();
	});

	afterPaginate();
	observe();

});

function observe(){
	$('.edit').click(function(){
		var notificacion_id = $(this).attr('data');
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
			data: { notificacion_id: notificacion_id },
			dataType: "json",
			success: function(data) {
				$('#notificacion_id').val(notificacion_id);
				$('#asunto').val(data.asunto);
				$('#mensaje').html(data.mensaje);
				CKEDITOR.instances.mensaje.setData(data.mensaje)
				$('#form_empresa_id').html(data.form_empresa_id);
				$('#tipo_notificacion_id').html(data.tipo_notificacion_id);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});
	$('.see').click(function(){
		var notificacion_id = $(this).attr('data');
		$('#div-active-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_programation').val(),
			async: true,
			data: { notificacion_id: notificacion_id },
			dataType: "json",
			success: function(data) {
				$('#tbody_history_programation').html(data.html);
				$('#notificacionTitle').html(data.notificacion);
				$('#history_programation').show();
			},
			error: function(){
				$('#active-error').html($('#error_msg_history').val());
				$('#div-active-alert').show();
				$('#history_programation').hide();
			}
		});
	});
}

function getListadoNotificaciones(empresa_id){
	$.ajax({
		type: "GET",
		url: $('#url_notificaciones').val(),
		async: true,
		data: { empresa_id: empresa_id },
		dataType: "json",
		success: function(data) {
			$('#list_notificaciones').html(data.notificaciones);
			observe();
		},
		error: function(){
			$('#active-error').html($('#error_msg-filter').val());
			$('#div-active-alert').show();
		}
	});
}

function afterPaginate(){
	$('.see').click(function(){
		var notificacion_id = $(this).attr('data');
		$('#div-active-alert').hide();
		observe();
	});
	$('.edit').click(function(){
		var notificacion_id = $(this).attr('data');
		$('#div-active-alert').hide();
		observe();
	});
}

function saveNotificacion()
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
					$('#notificacion_id').html(data.notificacion_id);
					$('#p-nombre').html(data.asunto);
					$('#p-des').html(data.mensaje);
					$('#p_empresa').html(data.empresa);
					$('#p_notificacion').html(data.tipo_notificacion);
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
							sweetAlertDelete(rol_id, 'AdminNotificacion');
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